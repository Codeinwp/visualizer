<?php
/**
 * WordPress unit test plugin.
 *
 * @package     visualizer
 * @subpackage  Tests
 */

/**
 * Test that the JSON data source headers cannot carry stored XSS into the chart editor.
 */
class Test_Visualizer_Json_Headers_Xss extends WP_UnitTestCase {

	/**
	 * A payload that breaks out of an HTML value attribute.
	 *
	 * @var string
	 */
	const PAYLOAD = 'x" autofocus onfocus=alert(document.domain) x="';

	/**
	 * Callbacks removed from the pro upsell filter for the duration of a test.
	 *
	 * @var array
	 */
	private $upsell_callbacks = array();

	/**
	 * The upsell markup calls into the themeisle SDK, which the test bootstrap does
	 * not load, so it is unhooked while the editor screen is rendered.
	 */
	public function set_up() {
		parent::set_up();
		global $wp_filter;
		if ( isset( $wp_filter['visualizer_pro_upsell'] ) ) {
			$this->upsell_callbacks = $wp_filter['visualizer_pro_upsell']->callbacks;
			remove_all_filters( 'visualizer_pro_upsell' );
		}
	}

	/**
	 * Restore the upsell filter so no state leaks into other tests.
	 */
	public function tear_down() {
		if ( ! empty( $this->upsell_callbacks ) ) {
			global $wp_filter;
			$wp_filter['visualizer_pro_upsell'] = new WP_Hook();
			$wp_filter['visualizer_pro_upsell']->callbacks = $this->upsell_callbacks;
			$this->upsell_callbacks = array();
		}
		parent::tear_down();
	}

	/**
	 * Invoke the private Visualizer_Module_Chart::sanitizeJsonHeaders().
	 *
	 * @param mixed $headers The raw headers.
	 * @return array The sanitized headers.
	 */
	private function sanitize( $headers ) {
		$module = new Visualizer_Module_Chart( Visualizer_Plugin::instance() );
		$method = new ReflectionMethod( Visualizer_Module_Chart::class, 'sanitizeJsonHeaders' );
		$method->setAccessible( true );
		return $method->invoke( $module, $headers );
	}

	/**
	 * Create a chart carrying the given JSON headers meta.
	 *
	 * @param array $headers The headers to store.
	 * @return int The chart id.
	 */
	private function create_chart_with_headers( array $headers ) {
		$chart_id = $this->factory->post->create(
			array(
				'post_type'   => Visualizer_Plugin::CPT_VISUALIZER,
				'post_status' => 'publish',
			)
		);
		update_post_meta( $chart_id, Visualizer_Plugin::CF_JSON_URL, 'https://example.com/api' );
		update_post_meta( $chart_id, Visualizer_Plugin::CF_JSON_HEADERS, $headers );
		return $chart_id;
	}

	/**
	 * Render the JSON parameters screen for a chart.
	 *
	 * @param int $chart_id The chart id.
	 * @return string The rendered markup.
	 */
	private function render_json_screen( $chart_id ) {
		ob_start();
		Visualizer_Render_Layout::show( 'json-screen', $chart_id );
		return ob_get_clean();
	}

	/**
	 * Tag payloads are stripped from the credential fields on save.
	 *
	 * The write boundary removes markup so the stored value is safe for consumers
	 * that are not HTML attributes (the REST field the block editor reads). The
	 * quote break-out itself is stopped by escaping at output, which is context
	 * specific and must not be done on the way into the database.
	 */
	public function test_sanitize_strips_tags_from_credentials() {
		$result = $this->sanitize(
			array(
				'method' => 'get',
				'auth'   => array(
					'username' => '<script>alert(document.domain)</script>admin',
					'password' => '<img src=x onerror=alert(document.cookie)>secret',
				),
			)
		);

		$this->assertStringNotContainsString( '<script', $result['auth']['username'] );
		$this->assertStringNotContainsString( 'alert(document.domain)', $result['auth']['username'] );
		$this->assertStringNotContainsString( '<img', $result['auth']['password'] );
		$this->assertStringNotContainsString( 'onerror', $result['auth']['password'] );
	}

	/**
	 * The authorization-string form of auth is sanitized too.
	 */
	public function test_sanitize_strips_tags_from_authorization_string() {
		$result = $this->sanitize(
			array(
				'method' => 'get',
				'auth'   => '"><script>alert(1)</script>',
			)
		);

		$this->assertStringNotContainsString( '<script', $result['auth'] );
		$this->assertStringNotContainsString( 'alert(1)', $result['auth'] );
	}

	/**
	 * Newlines cannot be smuggled into the stored credential values.
	 */
	public function test_sanitize_strips_newlines_from_credentials() {
		$result = $this->sanitize(
			array(
				'auth' => array(
					'username' => "admin\nX-Injected: 1",
					'password' => "secret\r\nX-Injected: 1",
				),
			)
		);

		$this->assertStringNotContainsString( "\n", $result['auth']['username'] );
		$this->assertStringNotContainsString( "\r", $result['auth']['password'] );
	}

	/**
	 * Legitimate credentials must survive sanitization byte for byte.
	 */
	public function test_sanitize_preserves_legitimate_credentials() {
		$headers = array(
			'method' => 'post',
			'auth'   => array(
				'username' => 'api_user-01@example.com',
				'password' => 'p@ssw0rd!#$%^&*()_+=[]{};:,.?/|~',
			),
		);

		$result = $this->sanitize( $headers );

		$this->assertSame( $headers['auth']['username'], $result['auth']['username'] );
		$this->assertSame( $headers['auth']['password'], $result['auth']['password'] );
		$this->assertSame( 'post', $result['method'] );
	}

	/**
	 * A shared-key authorization string must survive sanitization byte for byte.
	 */
	public function test_sanitize_preserves_shared_key_authorization() {
		$auth   = 'SharedKey myaccount:aGVsbG8gd29ybGQ=';
		$result = $this->sanitize( array( 'auth' => $auth ) );

		$this->assertSame( $auth, $result['auth'] );
	}

	/**
	 * Payloads already stored in the meta are escaped when the editor renders them.
	 *
	 * This covers the sites that stored a payload before the sanitizer existed, so it
	 * must keep passing independently of the write-side fix.
	 */
	public function test_stored_credential_payload_is_escaped_on_render() {
		$chart_id = $this->create_chart_with_headers(
			array(
				'method' => 'get',
				'auth'   => array(
					'username' => self::PAYLOAD,
					'password' => self::PAYLOAD,
				),
			)
		);

		$markup = $this->render_json_screen( $chart_id );

		$this->assertStringNotContainsString( self::PAYLOAD, $markup );
		$this->assertStringContainsString( 'x&quot; autofocus onfocus=', $markup );
	}

	/**
	 * A stored authorization string payload is escaped when the editor renders it.
	 */
	public function test_stored_authorization_payload_is_escaped_on_render() {
		$chart_id = $this->create_chart_with_headers(
			array(
				'method' => 'get',
				'auth'   => '"><img src=x onerror=alert(1)>',
			)
		);

		$markup = $this->render_json_screen( $chart_id );

		$this->assertStringNotContainsString( '<img src=x', $markup );
		$this->assertStringContainsString( '&lt;img src=x', $markup );
	}

	/**
	 * The editor renders without fatalling when auth is stored as a plain string.
	 *
	 * The string form used to reach array_key_exists(), which is a TypeError on
	 * PHP 8 and took the whole chart editor page down.
	 */
	public function test_authorization_string_renders_without_fatal() {
		$chart_id = $this->create_chart_with_headers(
			array(
				'method' => 'get',
				'auth'   => 'SharedKey myaccount:aGVsbG8=',
			)
		);

		$markup = $this->render_json_screen( $chart_id );

		$this->assertStringContainsString( 'value="SharedKey myaccount:aGVsbG8="', $markup );
		$this->assertStringContainsString( 'id="vz-import-json-username"', $markup );
	}

	/**
	 * A stored additional_headers payload is escaped in the textarea.
	 */
	public function test_stored_additional_headers_payload_is_escaped_on_render() {
		$chart_id = $this->create_chart_with_headers(
			array(
				'method'             => 'get',
				'additional_headers' => '</textarea><img src=x onerror=alert(1)>',
			)
		);

		$markup = $this->render_json_screen( $chart_id );

		$this->assertStringNotContainsString( '</textarea><img', $markup );
		$this->assertStringContainsString( '&lt;/textarea&gt;', $markup );
	}

	/**
	 * A stored JSON root payload is escaped in the root dropdown label.
	 */
	public function test_stored_json_root_payload_is_escaped_on_render() {
		$chart_id = $this->create_chart_with_headers( array( 'method' => 'get' ) );
		update_post_meta( $chart_id, Visualizer_Plugin::CF_JSON_ROOT, '<img src=x onerror=alert(1)>' );

		$markup = $this->render_json_screen( $chart_id );

		$this->assertStringNotContainsString( '<img src=x', $markup );
		$this->assertStringContainsString( '&lt;img src=x', $markup );
	}

	/**
	 * Legitimate credentials still render as usable values in the editor.
	 */
	public function test_legitimate_credentials_render_intact() {
		$chart_id = $this->create_chart_with_headers(
			array(
				'method' => 'get',
				'auth'   => array(
					'username' => 'api_user',
					'password' => 'secret123',
				),
			)
		);

		$markup = $this->render_json_screen( $chart_id );

		$this->assertStringContainsString( 'value="api_user"', $markup );
		$this->assertStringContainsString( 'value="secret123"', $markup );
	}
}
