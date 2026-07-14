<?php
/**
 * Tests for safe remote chart imports.
 *
 * @package Visualizer
 */

/**
 * Test the shared remote fetch policy.
 */
class Test_Visualizer_Remote_Fetch extends WP_UnitTestCase {

	/**
	 * Non-public destinations must be rejected before the HTTP transport runs.
	 *
	 * @dataProvider blocked_urls
	 * @param string $url URL under test.
	 */
	public function test_blocks_non_public_destinations_before_request( $url ) {
		$requests = 0;
		$filter   = function ( $preempt ) use ( &$requests ) {
			$requests++;
			return $preempt;
		};
		add_filter( 'pre_http_request', $filter );

		$response = Visualizer_Remote_Fetch::request( $url );

		remove_filter( 'pre_http_request', $filter );
		$this->assertWPError( $response );
		$this->assertSame( 0, $requests );
	}

	/**
	 * Non-public URL examples.
	 *
	 * @return array[]
	 */
	public function blocked_urls() {
		return array(
			'loopback'       => array( 'http://127.0.0.1/' ),
			'private'        => array( 'http://10.0.0.1/' ),
			'link-local'     => array( 'http://169.254.169.254/latest/meta-data/' ),
			'carrier-grade'  => array( 'http://100.64.0.1/' ),
			'documentation'  => array( 'http://192.0.2.1/' ),
			'multicast'      => array( 'http://224.0.0.1/' ),
			'reserved'       => array( 'http://240.0.0.1/' ),
			'blocked-port'   => array( 'http://93.184.216.34:8443/' ),
			'ipv4-mapped'    => array( 'http://[::ffff:169.254.169.254]/' ),
		);
	}

	/**
	 * A validated public destination reaches the WordPress HTTP transport.
	 */
	public function test_allows_public_destination() {
		$filter = function ( $preempt, $args, $url ) {
			$this->assertSame( 'http://93.184.216.34/data.json', $url );
			$this->assertTrue( $args['reject_unsafe_urls'] );
			return $this->response( 200, array(), '{"ok":true}' );
		};
		add_filter( 'pre_http_request', $filter, 10, 3 );

		$response = Visualizer_Remote_Fetch::request( 'http://93.184.216.34/data.json' );

		remove_filter( 'pre_http_request', $filter, 10 );
		$this->assertNotWPError( $response );
		$this->assertSame( '{"ok":true}', wp_remote_retrieve_body( $response ) );
	}

	/**
	 * Every redirect target must pass the same destination policy.
	 */
	public function test_blocks_redirect_to_link_local_destination() {
		$requests = 0;
		$filter   = function () use ( &$requests ) {
			$requests++;
			return $this->response( 302, array( 'location' => 'http://169.254.169.254/latest/meta-data/' ) );
		};
		add_filter( 'pre_http_request', $filter );

		$response = Visualizer_Remote_Fetch::request( 'http://93.184.216.34/start' );

		remove_filter( 'pre_http_request', $filter );
		$this->assertWPError( $response );
		$this->assertSame( 'visualizer_unsafe_remote_url', $response->get_error_code() );
		$this->assertSame( 1, $requests );
	}

	/**
	 * Sensitive headers must not follow a redirect to another origin.
	 */
	public function test_strips_sensitive_headers_on_cross_origin_redirect() {
		$requests = array();
		$filter   = function ( $preempt, $args, $url ) use ( &$requests ) {
			$requests[] = array( $url, $args['headers'] );
			if ( 1 === count( $requests ) ) {
				return $this->response( 302, array( 'location' => 'http://93.184.216.35/data' ) );
			}
			return $this->response( 200 );
		};
		add_filter( 'pre_http_request', $filter, 10, 3 );

		$response = Visualizer_Remote_Fetch::request(
			'http://93.184.216.34/start',
			array(
				'headers' => array(
					'Accept'        => 'application/json',
					'Authorization' => 'Bearer secret',
					'X-Api-Key'     => 'secret',
				),
			)
		);

		remove_filter( 'pre_http_request', $filter, 10 );
		$this->assertNotWPError( $response );
		$this->assertCount( 2, $requests );
		$this->assertSame( array( 'Accept' => 'application/json' ), $requests[1][1] );
	}

	/**
	 * Cookies must not follow a redirect to another origin.
	 */
	public function test_drops_cookies_on_cross_origin_redirect() {
		$requests = array();
		$filter   = function ( $preempt, $args, $url ) use ( &$requests ) {
			$requests[] = array( $url, $args['cookies'] );
			if ( 1 === count( $requests ) ) {
				return $this->response( 302, array( 'location' => 'http://93.184.216.35/data' ) );
			}
			return $this->response( 200 );
		};
		add_filter( 'pre_http_request', $filter, 10, 3 );

		$response = Visualizer_Remote_Fetch::request(
			'http://93.184.216.34/start',
			array( 'cookies' => array( 'session' => 'secret' ) )
		);

		remove_filter( 'pre_http_request', $filter, 10 );
		$this->assertNotWPError( $response );
		$this->assertCount( 2, $requests );
		$this->assertNotEmpty( $requests[0][1] );
		$this->assertSame( array(), $requests[1][1] );
	}

	/**
	 * The validated addresses are pinned on the cURL transport only while the request dispatches.
	 */
	public function test_pins_validated_addresses_during_dispatch() {
		$pinned_during = null;
		$filter        = function () use ( &$pinned_during ) {
			$pinned_during = has_action( 'http_api_curl' );
			return $this->response( 200 );
		};
		add_filter( 'pre_http_request', $filter );

		$response = Visualizer_Remote_Fetch::request( 'http://example.com/data.json' );

		remove_filter( 'pre_http_request', $filter );
		$this->assertNotWPError( $response );
		$this->assertTrue( $pinned_during );
		$this->assertFalse( has_action( 'http_api_curl' ) );
	}

	/**
	 * IPv6 addresses use cURL's bracketed CURLOPT_RESOLVE syntax.
	 */
	public function test_formats_ipv6_addresses_for_curl_resolve() {
		$method = new ReflectionMethod( Visualizer_Remote_Fetch::class, 'pin_validated_addresses' );
		$method->setAccessible( true );
		$pin = $method->invoke(
			null,
			'https://example.com/data.json',
			array( '93.184.216.34', '2606:2800:220:1:248:1893:25c8:1946' )
		);

		$this->assertIsCallable( $pin );
		$reflection = new ReflectionFunction( $pin );
		$this->assertSame(
			'example.com:443:93.184.216.34,[2606:2800:220:1:248:1893:25c8:1946]',
			$reflection->getStaticVariables()['entry']
		);
		remove_action( 'http_api_curl', $pin );
	}

	/**
	 * Header and method policy is applied before dispatch.
	 */
	public function test_rejects_unsupported_method_before_request() {
		$requests = 0;
		$filter   = function ( $preempt ) use ( &$requests ) {
			$requests++;
			return $preempt;
		};
		add_filter( 'pre_http_request', $filter );

		$response = Visualizer_Remote_Fetch::request( 'http://93.184.216.34/', array( 'method' => 'DELETE' ) );

		remove_filter( 'pre_http_request', $filter );
		$this->assertWPError( $response );
		$this->assertSame( 'visualizer_remote_method', $response->get_error_code() );
		$this->assertSame( 0, $requests );
	}

	/**
	 * URLs on the site's own host skip the public-address check, matching core.
	 */
	public function test_allows_same_host_destination_without_dns_check() {
		// A filter beats the WP_HOME constant pinned by some test configs (e.g. wp-env), which makes update_option() a no-op.
		add_filter(
			'option_home',
			function () {
				return 'http://visualizer.internal';
			},
			100
		);

		$requests = 0;
		$filter   = function ( $preempt ) use ( &$requests ) {
			$requests++;
			return $this->response( 200, array(), 'a,b' );
		};
		add_filter( 'pre_http_request', $filter );

		$response = Visualizer_Remote_Fetch::request( 'http://visualizer.internal/wp-content/uploads/data.csv' );

		remove_filter( 'pre_http_request', $filter );
		$this->assertNotWPError( $response );
		$this->assertSame( 1, $requests );
	}

	/**
	 * A same-origin redirect (relative Location) keeps request headers and resolves the target URL.
	 */
	public function test_same_origin_redirect_keeps_headers_and_resolves_relative_location() {
		$requests = array();
		$filter   = function ( $preempt, $args, $url ) use ( &$requests ) {
			$requests[] = array( $url, $args['headers'] );
			if ( 1 === count( $requests ) ) {
				return $this->response( 302, array( 'location' => '/next' ) );
			}
			return $this->response( 200 );
		};
		add_filter( 'pre_http_request', $filter, 10, 3 );

		$headers  = array(
			'Accept'        => 'application/json',
			'Authorization' => 'Bearer secret',
		);
		$response = Visualizer_Remote_Fetch::request( 'http://93.184.216.34/start', array( 'headers' => $headers ) );

		remove_filter( 'pre_http_request', $filter, 10 );
		$this->assertNotWPError( $response );
		$this->assertCount( 2, $requests );
		$this->assertSame( 'http://93.184.216.34/next', $requests[1][0] );
		$this->assertSame( $headers, $requests[1][1] );
	}

	/**
	 * The redirect chain must stop after MAX_REDIRECTS hops.
	 */
	public function test_stops_after_max_redirects() {
		$requests = 0;
		$filter   = function () use ( &$requests ) {
			$requests++;
			return $this->response( 302, array( 'location' => 'http://93.184.216.34/hop' . $requests ) );
		};
		add_filter( 'pre_http_request', $filter );

		$response = Visualizer_Remote_Fetch::request( 'http://93.184.216.34/start' );

		remove_filter( 'pre_http_request', $filter );
		$this->assertWPError( $response );
		$this->assertSame( 'visualizer_too_many_redirects', $response->get_error_code() );
		$this->assertSame( Visualizer_Remote_Fetch::MAX_REDIRECTS + 1, $requests );
	}

	/**
	 * A successful download streams to a temporary file and returns its path.
	 */
	public function test_download_returns_temp_file_with_body() {
		$filter = function ( $preempt, $args ) {
			$this->assertTrue( $args['stream'] );
			$this->assertNotEmpty( $args['filename'] );
			file_put_contents( $args['filename'], 'col1,col2' );
			return $this->response( 200 );
		};
		add_filter( 'pre_http_request', $filter, 10, 2 );

		$tmpfile = Visualizer_Remote_Fetch::download( 'http://93.184.216.34/data.csv' );

		remove_filter( 'pre_http_request', $filter, 10 );
		$this->assertNotWPError( $tmpfile );
		$this->assertSame( 'col1,col2', file_get_contents( $tmpfile ) );
		wp_delete_file( $tmpfile );
	}

	/**
	 * A failed download must not leave the temporary file behind.
	 */
	public function test_download_removes_temp_file_on_error_status() {
		$tmpfile = null;
		$filter  = function ( $preempt, $args ) use ( &$tmpfile ) {
			$tmpfile = $args['filename'];
			file_put_contents( $tmpfile, 'not found' );
			return $this->response( 404 );
		};
		add_filter( 'pre_http_request', $filter, 10, 2 );

		$response = Visualizer_Remote_Fetch::download( 'http://93.184.216.34/data.csv' );

		remove_filter( 'pre_http_request', $filter, 10 );
		$this->assertWPError( $response );
		$this->assertSame( 'visualizer_remote_status', $response->get_error_code() );
		$this->assertNotEmpty( $tmpfile );
		$this->assertFileDoesNotExist( $tmpfile );
	}

	/**
	 * Downloads are size-capped during transfer and possibly-truncated files are rejected.
	 */
	public function test_download_rejects_file_over_size_limit() {
		$tmpfile = null;
		$filter  = function ( $preempt, $args ) use ( &$tmpfile ) {
			$this->assertSame( Visualizer_Remote_Fetch::MAX_DOWNLOAD_BYTES + 1, $args['limit_response_size'] );
			$tmpfile = $args['filename'];
			file_put_contents( $tmpfile, str_repeat( 'a', Visualizer_Remote_Fetch::MAX_DOWNLOAD_BYTES + 1 ) );
			return $this->response( 200 );
		};
		add_filter( 'pre_http_request', $filter, 10, 2 );

		$response = Visualizer_Remote_Fetch::download( 'http://93.184.216.34/data.csv' );

		remove_filter( 'pre_http_request', $filter, 10 );
		$this->assertWPError( $response );
		$this->assertSame( 'visualizer_remote_size', $response->get_error_code() );
		$this->assertNotEmpty( $tmpfile );
		$this->assertFileDoesNotExist( $tmpfile );
	}

	/**
	 * A complete file exactly at the configured limit remains valid.
	 */
	public function test_download_accepts_file_at_size_limit() {
		$filter = function ( $preempt, $args ) {
			$this->assertSame( 5, $args['limit_response_size'] );
			file_put_contents( $args['filename'], '1234' );
			return $this->response( 200 );
		};
		add_filter( 'pre_http_request', $filter, 10, 2 );

		$tmpfile = Visualizer_Remote_Fetch::download(
			'http://93.184.216.34/data.csv',
			array( 'limit_response_size' => 4 )
		);

		remove_filter( 'pre_http_request', $filter, 10 );
		$this->assertNotWPError( $tmpfile );
		$this->assertSame( '1234', file_get_contents( $tmpfile ) );
		wp_delete_file( $tmpfile );
	}

	/**
	 * Callers may provide a smaller or larger download limit.
	 */
	public function test_download_honors_custom_size_limit() {
		$tmpfile = null;
		$filter  = function ( $preempt, $args ) use ( &$tmpfile ) {
			$this->assertSame( 5, $args['limit_response_size'] );
			$tmpfile = $args['filename'];
			file_put_contents( $tmpfile, '12345' );
			return $this->response( 200 );
		};
		add_filter( 'pre_http_request', $filter, 10, 2 );

		$response = Visualizer_Remote_Fetch::download(
			'http://93.184.216.34/data.csv',
			array( 'limit_response_size' => 4 )
		);

		remove_filter( 'pre_http_request', $filter, 10 );
		$this->assertWPError( $response );
		$this->assertSame( 'visualizer_remote_size', $response->get_error_code() );
		$this->assertNotEmpty( $tmpfile );
		$this->assertFileDoesNotExist( $tmpfile );
	}

	/**
	 * XLSX probes only need the four-byte ZIP magic number.
	 *
	 * @dataProvider xlsx_probe_callbacks
	 * @param string $class Probe owner.
	 */
	public function test_xlsx_probes_limit_response_to_magic_bytes( $class ) {
		$filter = function ( $preempt, $args ) {
			$this->assertSame( 4, $args['limit_response_size'] );
			file_put_contents( $args['filename'], "PK\x03\x04" );
			return $this->response( 200 );
		};
		add_filter( 'pre_http_request', $filter, 10, 2 );

		$method = new ReflectionMethod( $class, '_url_is_xlsx' );
		$method->setAccessible( true );
		$result = $method->invoke( null, 'http://93.184.216.34/download' );

		remove_filter( 'pre_http_request', $filter, 10 );
		$this->assertTrue( $result );
	}

	/**
	 * XLSX probe owners.
	 *
	 * @return array[]
	 */
	public function xlsx_probe_callbacks() {
		return array(
			'classic'    => array( Visualizer_Module_Chart::class ),
			'ai builder' => array( Visualizer_Module_AIBuilder::class ),
		);
	}

	/**
	 * The XLSX-specific filter controls the gateway download limit.
	 */
	public function test_xlsx_download_uses_filtered_size_limit() {
		$max_bytes = 12 * 1024 * 1024;
		$limit     = function () use ( $max_bytes ) {
			return $max_bytes;
		};
		add_filter( 'visualizer_xlsx_max_filesize', $limit );

		$filter = function ( $preempt, $args ) use ( $max_bytes ) {
			$this->assertSame( $max_bytes + 1, $args['limit_response_size'] );
			file_put_contents( $args['filename'], 'xlsx' );
			return $this->response( 200 );
		};
		add_filter( 'pre_http_request', $filter, 10, 2 );

		$source = new Visualizer_Source_Xlsx_Remote( 'http://93.184.216.34/data.xlsx' );
		$method = new ReflectionMethod( $source, '_get_file_path' );
		$method->setAccessible( true );
		$path = $method->invoke( $source );

		remove_filter( 'visualizer_xlsx_max_filesize', $limit );
		remove_filter( 'pre_http_request', $filter, 10 );
		$this->assertIsString( $path );
		wp_delete_file( $path );
	}

	/**
	 * Downloads enforce the same destination policy as plain requests.
	 */
	public function test_download_blocks_non_public_destination() {
		$requests = 0;
		$filter   = function ( $preempt ) use ( &$requests ) {
			$requests++;
			return $preempt;
		};
		add_filter( 'pre_http_request', $filter );

		$response = Visualizer_Remote_Fetch::download( 'http://169.254.169.254/latest/meta-data/' );

		remove_filter( 'pre_http_request', $filter );
		$this->assertWPError( $response );
		$this->assertSame( 0, $requests );
	}

	/**
	 * Builds a WordPress HTTP response fixture.
	 *
	 * @param int    $code    Status code.
	 * @param array  $headers Response headers.
	 * @param string $body    Response body.
	 * @return array
	 */
	private function response( $code, $headers = array(), $body = '' ) {
		return array(
			'headers'  => $headers,
			'body'     => $body,
			'response' => array(
				'code'    => $code,
				'message' => '',
			),
			'cookies'  => array(),
			'filename' => null,
		);
	}
}
