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

	const MAX_DOWNLOAD_BYTES = 10485760;
	const MAX_REDIRECTS      = 5;

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
				'method'  => 'GET',
				'timeout' => 15,
			)
		);

		$redirects = isset( $args['redirection'] ) ? min( self::MAX_REDIRECTS, max( 0, (int) $args['redirection'] ) ) : self::MAX_REDIRECTS;
		unset( $args['redirection'] );

		$args = self::enforce_request_policy( $args );
		if ( is_wp_error( $args ) ) {
			return $args;
		}

		for ( $redirect = 0; $redirect <= $redirects; $redirect++ ) {
			$ips           = array();
			$validated_url = self::validate_url( $url, $ips );
			if ( is_wp_error( $validated_url ) ) {
				return $validated_url;
			}

			$request_args                       = $args;
			$request_args['redirection']        = 0;
			$request_args['reject_unsafe_urls'] = true;

			$pin      = self::pin_validated_addresses( $validated_url, $ips );
			if ( is_wp_error( $pin ) ) {
				return $pin;
			}
			$response = wp_safe_remote_request( $validated_url, $request_args );
			if ( $pin ) {
				remove_action( 'http_api_curl', $pin );
			}
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
				unset( $args['cookies'] );
			} else {
				$response_cookies = wp_remote_retrieve_cookies( $response );
				if ( ! empty( $response_cookies ) ) {
					$args['cookies'] = array_merge( isset( $args['cookies'] ) ? $args['cookies'] : array(), $response_cookies );
				}
			}

			if ( in_array( $status, array( 302, 303 ), true ) ) {
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

		$max_bytes = isset( $args['limit_response_size'] ) ? (int) $args['limit_response_size'] : self::MAX_DOWNLOAD_BYTES;
		if ( $max_bytes < 1 ) {
			return new WP_Error( 'visualizer_remote_size', 'The remote file size limit is invalid.' );
		}

		$tmpfile = wp_tempnam( (string) wp_parse_url( $url, PHP_URL_PATH ) );
		if ( ! $tmpfile ) {
			return new WP_Error( 'visualizer_temp_file', 'Could not create a temporary file.' );
		}

		$args['stream']              = true;
		$args['filename']            = $tmpfile;
		$args['limit_response_size'] = $max_bytes < PHP_INT_MAX ? $max_bytes + 1 : $max_bytes;
		$response                    = self::request( $url, $args );

		if ( is_wp_error( $response ) ) {
			wp_delete_file( $tmpfile );
			return $response;
		}

		if ( 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
			wp_delete_file( $tmpfile );
			return new WP_Error( 'visualizer_remote_status', 'The remote server returned an unexpected response.' );
		}

		// Download one extra byte so an exactly-at-limit file remains valid.
		clearstatcache( true, $tmpfile );
		if ( filesize( $tmpfile ) > $max_bytes ) {
			wp_delete_file( $tmpfile );
			return new WP_Error( 'visualizer_remote_size', 'The remote file is too large to import.' );
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
		if ( ! preg_match( '/^[!#$%&\'*+\-.^_`|~0-9A-Z]+$/', $args['method'] ) || in_array( $args['method'], array( 'CONNECT', 'TRACE' ), true ) ) {
			return new WP_Error( 'visualizer_remote_method', 'The remote request method is not allowed.' );
		}

		if ( isset( $args['headers'] ) && is_string( $args['headers'] ) ) {
			$headers = array();
			foreach ( preg_split( '/\r\n|\r|\n/', $args['headers'] ) as $header ) {
				if ( false === strpos( $header, ':' ) ) {
					continue;
				}
				list( $name, $value )                   = explode( ':', $header, 2 );
				$headers[ strtolower( trim( $name ) ) ] = trim( $value );
			}
			$args['headers'] = $headers;
		} elseif ( ! isset( $args['headers'] ) || ! is_array( $args['headers'] ) ) {
			$args['headers'] = array();
		}

		$blocked_headers = array( 'connection', 'content-length', 'host', 'proxy-authorization', 'proxy-connection', 'te', 'trailer', 'transfer-encoding', 'upgrade' );
		foreach ( $args['headers'] as $name => $value ) {
			if ( in_array( strtolower( (string) $name ), $blocked_headers, true ) ) {
				unset( $args['headers'][ $name ] );
			}
		}

		$args['timeout'] = min( 30, max( 1, (int) $args['timeout'] ) );

		return $args;
	}

	/**
	 * Binds the cURL transport to the addresses that passed validation.
	 *
	 * Without this the transport re-resolves the host on connect, letting a
	 * rebinding nameserver answer with a private address after validation
	 * passed. Hostname requests fail closed when cURL pinning is unavailable.
	 *
	 * @param string      $url          Validated URL.
	 * @param string[]    $ips          Validated addresses.
	 * @param string|null $curl_version Optional cURL version override.
	 * @return callable|WP_Error|null The registered hook to remove after dispatch, an error when pinning is unavailable, or null for an IP literal or exempt host.
	 */
	private static function pin_validated_addresses( $url, $ips, $curl_version = null ) {
		if ( empty( $ips ) ) {
			return null;
		}

		$parsed = wp_parse_url( $url );
		if ( filter_var( rtrim( $parsed['host'], '.' ), FILTER_VALIDATE_IP ) ) {
			return null;
		}

		if ( ! function_exists( 'curl_init' ) || ! function_exists( 'curl_exec' ) || ! defined( 'CURLOPT_RESOLVE' ) ) {
			return new WP_Error( 'visualizer_remote_transport', 'The remote host cannot be fetched securely on this server.' );
		}

		$curl = curl_version();
		if ( 'https' === strtolower( $parsed['scheme'] ) ) {
			if ( empty( $curl['features'] ) || ! defined( 'CURL_VERSION_SSL' ) || ! ( $curl['features'] & CURL_VERSION_SSL ) ) {
				return new WP_Error( 'visualizer_remote_transport', 'The remote host cannot be fetched securely on this server.' );
			}
		}
		if ( null === $curl_version ) {
			$curl_version = isset( $curl['version'] ) ? $curl['version'] : '0.0.0';
		}

		$proxy = new WP_HTTP_Proxy();
		if ( $proxy->is_enabled() && $proxy->send_through_proxy( $url ) ) {
			return new WP_Error( 'visualizer_remote_transport', 'The remote host cannot be fetched securely through the configured proxy.' );
		}

		if ( version_compare( $curl_version, '7.59.0', '<' ) ) {
			foreach ( $ips as $ip ) {
				if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) ) {
					$ips = array( $ip );
					break;
				}
			}
			$ips = array( reset( $ips ) );
		}

		if ( version_compare( $curl_version, '7.57.0', '<' ) && filter_var( reset( $ips ), FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 ) ) {
			return new WP_Error( 'visualizer_remote_transport', 'The remote host cannot be fetched securely on this server.' );
		}

		$addresses = array();
		foreach ( $ips as $ip ) {
			$addresses[] = filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 ) ? '[' . $ip . ']' : $ip;
		}

		$entry  = sprintf( '%s:%d:%s', strtolower( rtrim( $parsed['host'], '.' ) ), self::url_port( $parsed ), implode( ',', $addresses ) );
		$pin    = function ( $handle ) use ( $entry ) {
			curl_setopt( $handle, CURLOPT_RESOLVE, array( $entry ) );
		};
		add_action( 'http_api_curl', $pin );

		return $pin;
	}

	/**
	 * Validates URL syntax and every address returned by DNS.
	 *
	 * @param string   $url Remote URL.
	 * @param string[] $ips Filled with the validated addresses; stays empty when the host is exempt from the check.
	 * @return string|WP_Error
	 */
	private static function validate_url( $url, &$ips = array() ) {
		$ips           = array();
		$validated_url = wp_http_validate_url( $url );
		if ( false === $validated_url ) {
			return new WP_Error( 'visualizer_invalid_remote_url', 'The remote URL is not allowed.' );
		}

		$host = strtolower( rtrim( (string) wp_parse_url( $validated_url, PHP_URL_HOST ), '.' ) );

		// Mirror core's same-host exemption so media library URLs import on hosts that resolve internally.
		$home_host = strtolower( rtrim( (string) wp_parse_url( get_option( 'home' ), PHP_URL_HOST ), '.' ) );
		if ( $host === $home_host ) {
			return $validated_url;
		}

		$ips = self::resolve_host( $host );
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
