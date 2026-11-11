<?php
/**
 * Chart data output sanitization tests.
 *
 * @package Visualizer
 */

/**
 * Verify chart data is safe for HTML-capable renderers.
 */
class Test_Visualizer_Chart_Data_Sanitization extends WP_UnitTestCase {

	/**
	 * Active markup is removed while safe formatting and plain text survive.
	 */
	public function test_chart_data_removes_active_markup() {
		$chart = (object) array(
			'ID'           => 123,
			'post_content' => serialize(
				array(
					array(
						'<strong>Revenue</strong>',
						'<img src="example.png" onerror="alert(1)">',
						'<script>alert(2)</script>',
						'Research & Development',
						'3 < 5',
					),
				)
			),
		);

		$result = Visualizer_Module::get_chart_data( $chart, 'table', false );

		$this->assertSame( '<strong>Revenue</strong>', $result[0][0] );
		$this->assertStringNotContainsString( 'onerror', $result[0][1] );
		$this->assertStringNotContainsString( '<script', $result[0][2] );
		$this->assertSame( 'Research & Development', $result[0][3] );
		$this->assertSame( '3 < 5', $result[0][4] );
	}
}
