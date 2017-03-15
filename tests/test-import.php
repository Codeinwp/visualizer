<?php

/**
 * Test class for the importing features.
 *
 * @package     Visualizer
 * @subpackage  Tests
 * @copyright   Copyright (c) 2017, Marius Cristea
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.0.0
 */
class Test_Import extends WP_Ajax_UnitTestCase {

	/**
	 * The chart id of the chart created
	 *
	 * @since 2.0.0
	 *
	 * @access private
	 * @var int
	 */
	private $chart;

	/**
	 * Create a chart
	 *
	 * @since 2.0.0
	 *
	 * @access private
	 */
	private function create_chart() {
		$id = wp_insert_post(array(
			'post_type'     => 'visualizer',
			'post_content'  => 'a:5:{i:0;a:2:{i:0;s:17:"Work";i:1;d:11;}i:1;a:2:{i:0;s:3:"Eat";i:1;d:2;}i:2;a:2:{i:0;s:7:"Commute";i:1;d:2;}i:3;a:2:{i:0;s:8:"Watch TV";i:1;d:2;}i:4;a:2:{i:0;s:5:"Sleep";i:1;d:7;}}',
			'post_status'   => 'publish',
		));

		update_post_meta( $id, 'visualizer-chart-type', 'pie' );
		update_post_meta( $id, 'visualizer-series', 'a:2:{i:0;a:2:{s:5:"label";s:4:"Task";s:4:"type";s:6:"string";}i:1;a:2:{s:5:"label";s:13:"Hours per Day";s:4:"type";s:6:"number";}}' );
		update_post_meta( $id, 'visualizer-default-data', 0 );
		update_post_meta( $id, 'visualizer-source', 'Visualizer_Source_Csv' );
		update_post_meta( $id, 'visualizer-settings', 'a:20:{s:5:"title";s:0:"";s:14:"titleTextStyle";a:1:{s:5:"color";s:4:"#000";}s:8:"fontName";s:0:"";s:8:"fontSize";s:0:"";s:6:"legend";a:3:{s:8:"position";s:4:"left";s:9:"alignment";s:0:"";s:9:"textStyle";a:1:{s:5:"color";s:4:"#000";}}s:7:"tooltip";a:3:{s:7:"trigger";s:0:"";s:13:"showColorCode";s:0:"";s:4:"text";s:0:"";}s:4:"is3D";s:0:"";s:17:"reverseCategories";s:0:"";s:12:"pieSliceText";s:0:"";s:7:"pieHole";s:0:"";s:13:"pieStartAngle";s:0:"";s:19:"pieSliceBorderColor";s:4:"#fff";s:24:"sliceVisibilityThreshold";s:0:"";s:20:"pieResidueSliceLabel";s:0:"";s:20:"pieResidueSliceColor";s:4:"#ccc";s:6:"slices";a:5:{i:0;a:2:{s:6:"offset";s:0:"";s:5:"color";s:0:"";}i:1;a:2:{s:6:"offset";s:0:"";s:5:"color";s:0:"";}i:2;a:2:{s:6:"offset";s:0:"";s:5:"color";s:0:"";}i:3;a:2:{s:6:"offset";s:0:"";s:5:"color";s:0:"";}i:4;a:2:{s:6:"offset";s:0:"";s:5:"color";s:0:"";}}s:5:"width";s:0:"";s:6:"height";s:0:"";s:15:"backgroundColor";a:3:{s:11:"strokeWidth";s:0:"";s:6:"stroke";s:4:"#666";s:4:"fill";s:4:"#fff";}s:9:"chartArea";a:4:{s:4:"left";s:0:"";s:3:"top";s:0:"";s:5:"width";s:0:"";s:6:"height";s:0:"";}}' );

		$this->chart    = $id;
	}

	/**
	 * Testing file import feature.
	 *
	 * @access public
	 * @dataProvider urlProvider
	 */
	public function test_url_import( $url ) {
		$this->create_chart();
		$this->_setRole( 'administrator' );

		$_POST  = array(
			'remote_data'   => $url,
		);
		$_GET  = array(
			'nonce'         => wp_create_nonce(),
			'chart'         => $this->chart,
		);

		// swallow the output
		ob_start();
		try {
			$this->_handleAjax( 'visualizer-upload-data' );
		} catch ( WPAjaxDieContinueException  $e ) {
			// We expected this, do nothing.
		} catch ( WPAjaxDieStopException $ee) {
			// We expected this, do nothing.
		}
		ob_end_clean();

		$series     = get_post_meta( $this->chart, 'visualizer-series', true );
		$chart      = get_post( $this->chart );
		$src        = get_post_meta( $this->chart, 'visualizer-source', true );
		$content    = $chart->post_content;

		$content_line   = 'a:2:{s:6:"source";s:66:"' . $url . '";s:4:"data";a:14:{i:0;a:4:{i:0;s:1:"A";i:1;d:1;i:2;d:1;i:3;d:0.5;}i:1;a:4:{i:0;s:1:"B";i:1;d:2;i:2;d:0.5;i:3;d:1;}i:2;a:4:{i:0;s:1:"C";i:1;d:4;i:2;d:1;i:3;d:0.5;}i:3;a:4:{i:0;s:1:"D";i:1;d:8;i:2;d:0.5;i:3;d:1;}i:4;a:4:{i:0;s:1:"E";i:1;d:7;i:2;d:1;i:3;d:0.5;}i:5;a:4:{i:0;s:1:"F";i:1;d:7;i:2;d:0.5;i:3;d:1;}i:6;a:4:{i:0;s:1:"G";i:1;d:8;i:2;d:1;i:3;d:0.5;}i:7;a:4:{i:0;s:1:"H";i:1;d:4;i:2;d:0.5;i:3;d:1;}i:8;a:4:{i:0;s:1:"I";i:1;d:2;i:2;d:1;i:3;d:0.5;}i:9;a:4:{i:0;s:1:"J";i:1;d:3.5;i:2;d:0.5;i:3;d:1;}i:10;a:4:{i:0;s:1:"K";i:1;d:3;i:2;d:1;i:3;d:0.5;}i:11;a:4:{i:0;s:1:"L";i:1;d:3.5;i:2;d:0.5;i:3;d:1;}i:12;a:4:{i:0;s:1:"M";i:1;d:1;i:2;d:1;i:3;d:0.5;}i:13;a:4:{i:0;s:1:"N";i:1;d:1;i:2;d:0.5;i:3;d:1;}}}';

		$series_line    = unserialize( 'a:4:{i:0;a:2:{s:5:"label";s:1:"x";s:4:"type";s:6:"string";}i:1;a:2:{s:5:"label";s:4:"Cats";s:4:"type";s:6:"number";}i:2;a:2:{s:5:"label";s:8:"Blanket1";s:4:"type";s:6:"number";}i:3;a:2:{s:5:"label";s:8:"Blanket2";s:4:"type";s:6:"number";}}' );

		$this->assertEquals( 'Visualizer_Source_Csv_Remote', $src );
		$this->assertEquals( $content, $content_line );
		$this->assertEquals( $series, $series_line );
	}


	/**
	 * Provide the URL for uploading the file
	 *
	 * @access public
	 */
	public function urlProvider() {
		return [ [ 'http://localhost/wp-content/plugins/wp-visualizer/samples/line.csv' ] ];
	}
}
