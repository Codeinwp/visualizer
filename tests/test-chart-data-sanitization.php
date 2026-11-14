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
						'<img src=x onerror="alert(3)"',
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
		$this->assertStringNotContainsString( '<img', $result[0][5] );
	}

	/**
	 * Cells added by a chart data filter are sanitized after the filter runs.
	 */
	public function test_chart_data_filter_output_is_sanitized() {
		$chart  = (object) array(
			'ID'           => 123,
			'post_content' => serialize( array( array( 'Revenue' ) ) ),
		);
		$filter = array( $this, 'add_script_cell' );
		add_filter( Visualizer_Plugin::FILTER_GET_CHART_DATA, $filter );

		$result = Visualizer_Module::get_chart_data( $chart, 'table' );

		remove_filter( Visualizer_Plugin::FILTER_GET_CHART_DATA, $filter );
		$this->assertSame( 'Revenue', $result[0][0] );
		$this->assertStringNotContainsString( '<script', $result[1][0] );
	}

	/**
	 * The block editor reads chart data through its own path, which gets the same sanitization.
	 */
	public function test_block_editor_chart_data_removes_active_markup() {
		$chart_id = $this->create_chart( array( array( '<strong>Revenue</strong>', '<script>alert(2)</script>' ) ) );
		$filter   = array( $this, 'add_script_cell' );
		add_filter( Visualizer_Plugin::FILTER_GET_CHART_DATA, $filter );

		$result = Visualizer_Gutenberg_Block::get_instance()->get_visualizer_data( array( 'id' => $chart_id ) );

		remove_filter( Visualizer_Plugin::FILTER_GET_CHART_DATA, $filter );
		$this->assertSame( '<strong>Revenue</strong>', $result['visualizer-data'][0][0] );
		$this->assertStringNotContainsString( '<script', $result['visualizer-data'][0][1] );
		$this->assertStringNotContainsString( '<script', $result['visualizer-data'][1][0] );
	}

	/**
	 * Exports and the data editor keep the stored cell text, while the print view is sanitized.
	 */
	public function test_exports_and_editor_keep_stored_cells() {
		$chart_id = $this->create_chart( array( array( 'Q1<Q2 and Q3>Q4', '<img src="x.png" onerror="alert(1)">' ) ) );
		$module   = Visualizer_Plugin::instance()->getModule( Visualizer_Module_Chart::NAME );

		$csv = $module->_getDataAs( $chart_id, 'csv' );
		$this->assertStringContainsString( 'Q1<Q2 and Q3>Q4', $csv['string'] );

		$print = $module->_getDataAs( $chart_id, 'print' );
		$this->assertStringNotContainsString( 'onerror', $print['csv'] );

		$editor = Visualizer_Render_Layout::show( 'editor-table', null, $chart_id, 'viz-html-table', false, true );
		$this->assertStringContainsString( esc_attr( 'Q1<Q2 and Q3>Q4' ), $editor );
	}

	/**
	 * Appends a row with a script cell.
	 *
	 * @param array<int, array<int, string>> $data The chart data.
	 * @return array<int, array<int, string>>
	 */
	public function add_script_cell( $data ) {
		$data[] = array( '<script>alert(4)</script>' );
		return $data;
	}

	/**
	 * Creates a line chart with the given rows, stored as an administrator would.
	 *
	 * @param array<int, array<int, string>> $rows The chart rows.
	 * @return int The chart ID.
	 */
	private function create_chart( $rows ) {
		$author_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		// Post content filtering leaves the serialized data intact for an administrator.
		wp_set_current_user( $author_id );
		$chart_id = self::factory()->post->create(
			array(
				'post_type'    => Visualizer_Plugin::CPT_VISUALIZER,
				'post_status'  => 'publish',
				'post_author'  => $author_id,
				'post_content' => wp_slash( serialize( $rows ) ),
			)
		);
		update_post_meta( $chart_id, Visualizer_Plugin::CF_CHART_TYPE, 'line' );
		update_post_meta( $chart_id, Visualizer_Plugin::CF_SETTINGS, array() );
		update_post_meta(
			$chart_id,
			Visualizer_Plugin::CF_SERIES,
			array(
				array(
					'label' => 'Label',
					'type'  => 'string',
				),
				array(
					'label' => 'Value',
					'type'  => 'string',
				),
			)
		);
		return $chart_id;
	}
}
