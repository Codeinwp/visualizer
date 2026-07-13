<?php
/**
 * Safe access to user-controlled remote resources.
 *
 * @package Visualizer
 */

/**
 * Centralizes the network policy for remote chart imports.
 */
class Visualizer_Remote_Fetch {

	const MAX_RESPONSE_SIZE = 10485760;
	const MAX_REDIRECTS = 5;

	/**
	 * Performs a request after validating every destination.
	 *
	 * @param string               $url  Remote URL.
	 * @param array<string, mixed> $args Optional WordPress HTTP arguments.
	 * @return array<string, mixed>|WP_Error
	 */
	public static function request( $url, $args = array() ) {
		$args = wp_parse_args(
			$args,
			array(
				'method'              => 'GET',
				'timeout'             => 15,
				'limit_response_size' => self::MAX_RESPONSE_SIZE,
			)
		);

		$redirects = isset( $args['redirection'] ) ? min( self::MAX_REDIRECTS, max( 0, (int) $args['redirection'] ) ) : self::MAX_REDIRECTS;
		unset( $args['redirection'] );

		$args = self::enforce_request_policy( $args );
		if ( is_wp_error( $args ) ) {
			return $args;
		}

		for ( $redirect = 0; $redirect <= $redirects; $redirect++ ) {
			$validated_url = self::validate_url( $url );
			if ( is_wp_error( $validated_url ) ) {
				return $validated_url;
			}

			$request_args                       = $args;
			$request_args['redirection']        = 0;
			$request_args['reject_unsafe_urls'] = true;
			$response                           = wp_safe_remote_request( $validated_url, $request_args );
			if ( is_wp_error( $response ) ) {
				return $response;
			}

			$status   = (int) wp_remote_retrieve_response_code( $response );
			$location = wp_remote_retrieve_header( $response, 'location' );
			if ( $status < 300 || $status > 399 || empty( $location ) ) {
				return $response;
			}

			if ( $redirect === $redirects ) {
				return new WP_Error( 'visualizer_too_many_redirects', 'The remote URL redirected too many times.' );
			}

			$next_url = WP_Http::make_absolute_url( $location, $validated_url );
			if ( ! self::same_origin( $validated_url, $next_url ) ) {
				$args['headers'] = self::headers_for_cross_origin_redirect( isset( $args['headers'] ) ? $args['headers'] : array() );
			}

			if ( 303 === $status || ( in_array( $status, array( 301, 302 ), true ) && 'POST' === $args['method'] ) ) {
				$args['method'] = 'GET';
				unset( $args['body'] );
			}

			$url = $next_url;
		}

		return new WP_Error( 'visualizer_remote_request', 'The remote request could not be completed.' );
	}

	/**
	 * Downloads a remote resource to a temporary file.
	 *
	 * The caller is responsible for deleting the returned file.
	 *
	 * @param string               $url  Remote URL.
	 * @param array<string, mixed> $args Optional WordPress HTTP arguments.
	 * @return string|WP_Error
	 */
	public static function download( $url, $args = array() ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';

		$tmpfile = wp_tempnam( (string) wp_parse_url( $url, PHP_URL_PATH ) );
		if ( ! $tmpfile ) {
			return new WP_Error( 'visualizer_temp_file', 'Could not create a temporary file.' );
		}

		$args['stream']   = true;
		$args['filename'] = $tmpfile;
		$response         = self::request( $url, $args );

		if ( is_wp_error( $response ) ) {
			wp_delete_file( $tmpfile );
			return $response;
		}

		if ( 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
			wp_delete_file( $tmpfile );
			return new WP_Error( 'visualizer_remote_status', 'The remote server returned an unexpected response.' );
		}

		return $tmpfile;
	}

	/**
	 * Applies method and header restrictions.
	 *
	 * @param array<string, mixed> $args HTTP arguments.
	 * @return array<string, mixed>|WP_Error
	 */
	private static function enforce_request_policy( $args ) {
		$args['method'] = strtoupper( (string) $args['method'] );
		if ( ! in_array( $args['method'], array( 'GET', 'POST' ), true ) ) {
			return new WP_Error( 'visualizer_remote_method', 'Only GET and POST remote requests are allowed.' );
		}

		$blocked_headers = array( 'connection', 'content-length', 'host', 'proxy-authorization', 'proxy-connection', 'te', 'trailer', 'transfer-encoding', 'upgrade' );
		foreach ( isset( $args['headers'] ) ? $args['headers'] : array() as $name => $value ) {
			if ( in_array( strtolower( (string) $name ), $blocked_headers, true ) ) {
				unset( $args['headers'][ $name ] );
			}
		}

		$args['timeout']             = min( 30, max( 1, (int) $args['timeout'] ) );
		$args['limit_response_size'] = min( self::MAX_RESPONSE_SIZE, max( 1, (int) $args['limit_response_size'] ) );

		return $args;
	}

	/**
	 * Validates URL syntax and every address returned by DNS.
	 *
	 * @param string $url Remote URL.
	 * @return string|WP_Error
	 */
	private static function validate_url( $url ) {
		$validated_url = wp_http_validate_url( $url );
		if ( false === $validated_url ) {
			return new WP_Error( 'visualizer_invalid_remote_url', 'The remote URL is not allowed.' );
		}

		$host = strtolower( rtrim( (string) wp_parse_url( $validated_url, PHP_URL_HOST ), '.' ) );
		$ips  = self::resolve_host( $host );
		if ( empty( $ips ) ) {
			return new WP_Error( 'visualizer_remote_dns', 'The remote host could not be resolved.' );
		}

		foreach ( $ips as $ip ) {
			if ( ! self::is_global_ip( $ip ) ) {
				return new WP_Error( 'visualizer_unsafe_remote_url', 'The remote URL resolves to a non-public address.' );
			}
		}

		return $validated_url;
	}

	/**
	 * Resolves every IPv4 and IPv6 address for a host.
	 *
	 * @param string $host Host name or IP literal.
	 * @return string[]
	 */
	private static function resolve_host( $host ) {
		if ( filter_var( $host, FILTER_VALIDATE_IP ) ) {
			return array( $host );
		}

		$ips = array();
		if ( function_exists( 'dns_get_record' ) ) {
			// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- DNS failures are handled below.
			$records = @dns_get_record( $host, DNS_A | DNS_AAAA );
			foreach ( is_array( $records ) ? $records : array() as $record ) {
				if ( ! empty( $record['ip'] ) ) {
					$ips[] = $record['ip'];
				} elseif ( ! empty( $record['ipv6'] ) ) {
					$ips[] = $record['ipv6'];
				}
			}
		}

		if ( empty( $ips ) ) {
			// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- DNS failures are handled by returning no addresses.
			$ipv4 = @gethostbynamel( $host );
			$ips  = is_array( $ipv4 ) ? $ipv4 : array();
		}

		return array_values( array_unique( $ips ) );
	}

	/**
	 * Whether an address is globally routable.
	 *
	 * @param string $ip IP address.
	 * @return bool
	 */
	private static function is_global_ip( $ip ) {
		if ( self::ip_in_range( $ip, '::ffff:0:0/96' ) ) {
			$packed = inet_pton( $ip );
			$ip     = inet_ntop( substr( $packed, 12 ) );
		}

		if ( false === filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) ) {
			return false;
		}

		$ranges = false !== strpos( $ip, ':' )
			? array( 'fc00::/7', 'fe80::/10', 'ff00::/8' )
			: array( '100.64.0.0/10', '192.0.0.0/24', '192.0.2.0/24', '198.18.0.0/15', '198.51.100.0/24', '203.0.113.0/24', '224.0.0.0/4' );

		foreach ( $ranges as $range ) {
			if ( self::ip_in_range( $ip, $range ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Checks whether an IP belongs to a CIDR range.
	 *
	 * @param string $ip   IP address.
	 * @param string $cidr CIDR range.
	 * @return bool
	 */
	private static function ip_in_range( $ip, $cidr ) {
		list( $network, $prefix ) = explode( '/', $cidr, 2 );
		$address                  = inet_pton( $ip );
		$network                  = inet_pton( $network );
		if ( false === $address || false === $network || strlen( $address ) !== strlen( $network ) ) {
			return false;
		}

		$bytes = intdiv( (int) $prefix, 8 );
		$bits  = (int) $prefix % 8;
		if ( substr( $address, 0, $bytes ) !== substr( $network, 0, $bytes ) ) {
			return false;
		}

		return 0 === $bits || ( ord( $address[ $bytes ] ) & ( 0xff << ( 8 - $bits ) ) ) === ( ord( $network[ $bytes ] ) & ( 0xff << ( 8 - $bits ) ) );
	}

	/**
	 * Whether two URLs share scheme, host, and port.
	 *
	 * @param string $first  First URL.
	 * @param string $second Second URL.
	 * @return bool
	 */
	private static function same_origin( $first, $second ) {
		$first  = wp_parse_url( $first );
		$second = wp_parse_url( $second );
		if ( ! is_array( $first ) || ! is_array( $second ) || empty( $first['scheme'] ) || empty( $first['host'] ) || empty( $second['scheme'] ) || empty( $second['host'] ) ) {
			return false;
		}

		return strtolower( $first['scheme'] ) === strtolower( $second['scheme'] )
			&& strtolower( $first['host'] ) === strtolower( $second['host'] )
			&& self::url_port( $first ) === self::url_port( $second );
	}

	/**
	 * Gets an explicit or scheme-default URL port.
	 *
	 * @param array<string, mixed> $url Parsed URL.
	 * @return int
	 */
	private static function url_port( $url ) {
		return isset( $url['port'] ) ? (int) $url['port'] : ( 'https' === strtolower( $url['scheme'] ) ? 443 : 80 );
	}

	/**
	 * Retains only non-sensitive headers across an origin change.
	 *
	 * @param array<string, mixed> $headers Request headers.
	 * @return array<string, mixed>
	 */
	private static function headers_for_cross_origin_redirect( $headers ) {
		$allowed = array( 'accept', 'accept-encoding', 'range', 'user-agent' );
		return array_filter(
			$headers,
			function ( $value, $name ) use ( $allowed ) {
				return in_array( strtolower( (string) $name ), $allowed, true );
			},
			ARRAY_FILTER_USE_BOTH
		);
	}
}
