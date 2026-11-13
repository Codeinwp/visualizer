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

	/**
	 * The block editor reads chart data through its own path, which gets the same sanitization.
	 */
	public function test_block_editor_chart_data_removes_active_markup() {
		$author_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		// Stored as an administrator would, so post content filtering leaves the serialized data intact.
		wp_set_current_user( $author_id );
		$chart_id = self::factory()->post->create(
			array(
				'post_type'    => Visualizer_Plugin::CPT_VISUALIZER,
				'post_status'  => 'publish',
				'post_author'  => $author_id,
				'post_content' => wp_slash( serialize( array( array( '<strong>Revenue</strong>', '<script>alert(2)</script>' ) ) ) ),
			)
		);
		update_post_meta( $chart_id, Visualizer_Plugin::CF_CHART_TYPE, 'line' );
		update_post_meta( $chart_id, Visualizer_Plugin::CF_SETTINGS, array() );
		update_post_meta( $chart_id, Visualizer_Plugin::CF_SERIES, array( array( 'label' => 'Label', 'type' => 'string' ) ) );

		$result = Visualizer_Gutenberg_Block::get_instance()->get_visualizer_data( array( 'id' => $chart_id ) );

		$this->assertSame( '<strong>Revenue</strong>', $result['visualizer-data'][0][0] );
		$this->assertStringNotContainsString( '<script', $result['visualizer-data'][0][1] );
	}
}
