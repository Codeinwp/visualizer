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
		$this->_setRole( 'administrator' );

		$_GET   = array(
			'library'       => 'yes',
			'tab'           => 'visualizer',
		);

		// swallow the output
		ob_start();
		try {
			$this->_handleAjax( 'visualizer-create-chart' );
		} catch ( WPAjaxDieContinueException $e ) {
			// We expected this, do nothing.
		} catch ( WPAjaxDieStopException $ee ) {
			// We expected this, do nothing.
		}
		ob_end_clean();

		$query          = new WP_Query(array(
			'post_type'     => Visualizer_Plugin::CPT_VISUALIZER,
			'post_status'   => 'auto-draft',
			'numberposts'   => 1,
			'fields'        => 'ids',
		));
		$this->chart    = $query->posts[0];
	}

	/**
	 * Testing url import feature.
	 *
	 * @access public
	 * @dataProvider urlProvider
	 */
	public function test_url_import( $url ) {
		$this->markTestSkipped( 'this test is disabled till we can figure out how to provide a "local" url' );
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

		$content_line   = 'a:2:{s:6:"source";s:' . strlen( $url ) . ':"' . $url . '";s:4:"data";a:6:{i:0;a:5:{i:0;s:4:"2003";i:1;d:1336060;i:2;d:400361;i:3;d:1001582;i:4;d:997974;}i:1;a:5:{i:0;s:4:"2004";i:1;d:1538156;i:2;d:366849;i:3;d:1119450;i:4;d:941795;}i:2;a:5:{i:0;s:4:"2005";i:1;d:1576579;i:2;d:440514;i:3;d:993360;i:4;d:930593;}i:3;a:5:{i:0;s:4:"2006";i:1;d:1600652;i:2;d:434552;i:3;d:1004163;i:4;d:897127;}i:4;a:5:{i:0;s:4:"2007";i:1;d:1968113;i:2;d:393032;i:3;d:979198;i:4;d:1080887;}i:5;a:5:{i:0;s:4:"2008";i:1;d:1901067;i:2;d:517206;i:3;d:916965;i:4;d:1056036;}}}';

		$series_line    = unserialize( 'a:5:{i:0;a:2:{s:5:"label";s:4:"Year";s:4:"type";s:6:"string";}i:1;a:2:{s:5:"label";s:7:"Austria";s:4:"type";s:6:"number";}i:2;a:2:{s:5:"label";s:8:"Bulgaria";s:4:"type";s:6:"number";}i:3;a:2:{s:5:"label";s:7:"Denmark";s:4:"type";s:6:"number";}i:4;a:2:{s:5:"label";s:6:"Greece";s:4:"type";s:6:"number";}}' );

		$this->assertEquals( 'Visualizer_Source_Csv_Remote', $src );
		$this->assertEquals( $content, $content_line );
		$this->assertEquals( $series, $series_line );
	}

	/**
	 * Testing file import feature.
	 *
	 * @access public
	 * @dataProvider fileProvider
	 */
	public function test_file_import( $file ) {
		$this->create_chart();
		$this->_setRole( 'administrator' );

		$dest       = dirname( __FILE__ ) . DIRECTORY_SEPARATOR . basename( $file );
		copy( $file, $dest );

		$_FILES = array(
			'local_data'    => array(
				'tmp_name'  => $dest,
				'error'     => 0,
			),
		);
		$_GET   = array(
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
		unlink( $dest );

		$series     = get_post_meta( $this->chart, 'visualizer-series', true );
		$chart      = get_post( $this->chart );
		$src        = get_post_meta( $this->chart, 'visualizer-source', true );
		$content    = $chart->post_content;

		$content_line   = 'a:6:{i:0;a:5:{i:0;s:4:"2003";i:1;d:1336060;i:2;d:400361;i:3;d:1001582;i:4;d:997974;}i:1;a:5:{i:0;s:4:"2004";i:1;d:1538156;i:2;d:366849;i:3;d:1119450;i:4;d:941795;}i:2;a:5:{i:0;s:4:"2005";i:1;d:1576579;i:2;d:440514;i:3;d:993360;i:4;d:930593;}i:3;a:5:{i:0;s:4:"2006";i:1;d:1600652;i:2;d:434552;i:3;d:1004163;i:4;d:897127;}i:4;a:5:{i:0;s:4:"2007";i:1;d:1968113;i:2;d:393032;i:3;d:979198;i:4;d:1080887;}i:5;a:5:{i:0;s:4:"2008";i:1;d:1901067;i:2;d:517206;i:3;d:916965;i:4;d:1056036;}}';

		$series_line    = unserialize( 'a:5:{i:0;a:2:{s:5:"label";s:4:"Year";s:4:"type";s:6:"string";}i:1;a:2:{s:5:"label";s:7:"Austria";s:4:"type";s:6:"number";}i:2;a:2:{s:5:"label";s:8:"Bulgaria";s:4:"type";s:6:"number";}i:3;a:2:{s:5:"label";s:7:"Denmark";s:4:"type";s:6:"number";}i:4;a:2:{s:5:"label";s:6:"Greece";s:4:"type";s:6:"number";}}' );

		$this->assertEquals( 'Visualizer_Source_Csv', $src );
		$this->assertEquals( $content, $content_line );
		$this->assertEquals( $series, $series_line );
	}

	/**
	 * Provide the fileURL for uploading the file
	 *
	 * @access public
	 */
	public function fileProvider() {
		return [ [ VISUALIZER_ABSPATH . DIRECTORY_SEPARATOR . 'samples' . DIRECTORY_SEPARATOR . 'bar.csv' ] ];
	}

	/**
	 * Provide the URL for uploading the file
	 *
	 * @access public
	 */
	public function urlProvider() {
		return [ [ 'http://localhost/wp-content/plugins/wp-visualizer/samples/bar.csv' ] ];
	}
}
