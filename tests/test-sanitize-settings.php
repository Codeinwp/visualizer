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
	 * Multi-line values keep their newlines (textarea sanitization, not text).
	 */
	public function test_newlines_are_preserved() {
		$result = $this->sanitize( array( 'description' => "Line one\nLine two" ) );
		$this->assertSame( "Line one\nLine two", $result['description'] );
	}
}
