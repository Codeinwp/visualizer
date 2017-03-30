<?php

/**
 * Test class for the exporting features.
 *
 * @package     Visualizer
 * @subpackage  Tests
 * @copyright   Copyright (c) 2017, Marius Cristea
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.0.0
 */
class Test_Export extends WP_Ajax_UnitTestCase {

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
	 * Testing export
	 *
	 * @access public
	 */
	public function test_download_export() {
		$this->create_chart();
		$this->_setRole( 'administrator' );

		$_GET  = array(
			'security'      => wp_create_nonce( Visualizer_Plugin::ACTION_EXPORT_DATA . Visualizer_Plugin::VERSION ),
			'chart'         => $this->chart,
		);

		// swallow the output
		ob_start();
		try {
			$this->_handleAjax( 'visualizer-export-data' );
		} catch ( WPAjaxDieContinueException  $e ) {
			// We expected this, do nothing.
		} catch ( WPAjaxDieStopException $ee ) {
			// We expected this, do nothing.
		}
		ob_end_clean();

		$response = json_decode( $this->_last_response );
		$this->assertInternalType( 'object', $response );
		$this->assertObjectHasAttribute( 'success', $response );
		$this->assertObjectHasAttribute( 'data', $response );
		$this->assertTrue( $response->success );
	}
}
