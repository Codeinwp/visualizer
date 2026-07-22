<?php
/**
 * WordPress unit test plugin.
 *
 * @package     visualizer
 * @subpackage  Tests
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/**
 * Test the chart settings sanitization used when saving chart settings.
 */
class Test_Visualizer_Sanitize_Settings extends WP_UnitTestCase {

	/**
	 * Invoke the private Visualizer_Module_Chart::sanitizeSettings().
	 *
	 * @param array $post_data The raw POST-like settings data.
	 * @return array The sanitized settings.
	 */
	private function sanitize( array $post_data ) {
		$module = new Visualizer_Module_Chart( Visualizer_Plugin::instance() );
		$method = new ReflectionMethod( Visualizer_Module_Chart::class, 'sanitizeSettings' );
		$method->setAccessible( true );
		return $method->invoke( $module, $post_data );
	}

	/**
	 * Script tags in settings values are stripped.
	 */
	public function test_strips_script_tags_from_values() {
		$result = $this->sanitize(
			array(
				'backend-title' => 'My Chart<script>alert(1)</script>',
			)
		);
		$this->assertSame( 'My Chart', $result['backend-title'] );
	}

	/**
	 * Sanitization is applied to nested arrays.
	 */
	public function test_sanitizes_nested_values() {
		$result = $this->sanitize(
			array(
				'title' => array(
					'text' => '<img src=x onerror=alert(1)>Sales',
				),
				'series' => array(
					array( 'label' => '<b>Q1</b>' ),
				),
			)
		);
		$this->assertSame( 'Sales', $result['title']['text'] );
		$this->assertSame( 'Q1', $result['series'][0]['label'] );
	}

	/**
	 * The chart-img value (a data URI) must pass through unsanitized.
	 */
	public function test_chart_img_is_preserved() {
		$img    = 'data:image/png;base64,iVBORw0KGgo=';
		$result = $this->sanitize(
			array(
				'chart-img'     => $img,
				'backend-title' => '<script>x</script>Title',
			)
		);
		$this->assertSame( $img, $result['chart-img'] );
		$this->assertSame( 'Title', $result['backend-title'] );
	}

	/**
	 * An empty chart-img is dropped, not re-added.
	 */
	public function test_empty_chart_img_is_dropped() {
		$result = $this->sanitize( array( 'chart-img' => '' ) );
		$this->assertArrayNotHasKey( 'chart-img', $result );
	}

	/**
	 * A chart-img that is not a base64 image data URI is dropped entirely.
	 */
	public function test_hostile_chart_img_is_dropped() {
		foreach ( array(
			'<script>alert(1)</script>',
			'data:text/html;base64,PHNjcmlwdD4=',
			'data:image/svg+xml;base64,PHN2Zz4=',
			'data:image/png;base64,abc"><script>alert(1)</script>',
			array( 'nested' => 'data:image/png;base64,iVBORw0KGgo=' ),
		) as $payload ) {
			$result = $this->sanitize( array( 'chart-img' => $payload ) );
			$this->assertArrayNotHasKey( 'chart-img', $result );
		}
	}

	/**
	 * save_chart_image() refuses to write anything whose bytes are not PNG.
	 */
	public function test_save_chart_image_rejects_non_png_payloads() {
		$module = new Visualizer_Module_Chart( Visualizer_Plugin::instance() );
		$chart  = self::factory()->post->create( array( 'post_type' => Visualizer_Plugin::CPT_VISUALIZER ) );
		foreach ( array(
			'data:image/png;base64,' . base64_encode( '<?php echo "pwn"; ?>' ),
			'data:image/png;base64,%%%not-base64%%%',
			'plain text',
		) as $payload ) {
			$this->assertSame( 0, $module->save_chart_image( $payload, $chart ) );
		}
	}

	/**
	 * Multi-line values keep their newlines (textarea sanitization, not text).
	 */
	public function test_newlines_are_preserved() {
		$result = $this->sanitize( array( 'description' => "Line one\nLine two" ) );
		$this->assertSame( "Line one\nLine two", $result['description'] );
	}
}
