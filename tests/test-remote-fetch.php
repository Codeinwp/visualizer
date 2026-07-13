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
			$this->assertSame( Visualizer_Remote_Fetch::MAX_RESPONSE_SIZE, $args['limit_response_size'] );
			return $this->response( 200, array(), '{"ok":true}' );
		};
		add_filter( 'pre_http_request', $filter, 10, 3 );

		$response = Visualizer_Remote_Fetch::request( 'http://93.184.216.34/data.json', array( 'limit_response_size' => PHP_INT_MAX ) );

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
