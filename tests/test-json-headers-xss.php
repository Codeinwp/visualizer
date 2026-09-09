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
	 * Percent-encoded octets in a stored credential survive to the rendered value.
	 *
	 * Credentials are base64-encoded into the Authorization header, so the stored
	 * bytes must be exact. sanitize_text_field() strips %XX octets and would turn
	 * abc%2Fdef into abcdef, breaking authentication.
	 */
	public function test_percent_encoded_credentials_render_intact() {
		$chart_id = $this->create_chart_with_headers(
			array(
				'method' => 'get',
				'auth'   => array(
					'username' => 'AKIA%2FEXAMPLE%2BKEY',
					'password' => 'abc%2Fdef',
				),
			)
		);

		$markup = $this->render_json_screen( $chart_id );

		$this->assertStringContainsString( 'value="AKIA%2FEXAMPLE%2BKEY"', $markup );
		$this->assertStringContainsString( 'value="abc%2Fdef"', $markup );
	}

	/**
	 * A percent-encoded authorization string survives to the rendered value.
	 */
	public function test_percent_encoded_authorization_renders_intact() {
		$chart_id = $this->create_chart_with_headers(
			array(
				'method' => 'get',
				'auth'   => 'SharedKey acct:aGVsbG8%3D',
			)
		);

		$markup = $this->render_json_screen( $chart_id );

		$this->assertStringContainsString( 'value="SharedKey acct:aGVsbG8%3D"', $markup );
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
