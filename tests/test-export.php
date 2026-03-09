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

		$query          = new WP_Query(
			array(
				'post_type'     => Visualizer_Plugin::CPT_VISUALIZER,
				'post_status'   => 'auto-draft',
				'numberposts'   => 1,
				'fields'        => 'ids',
			)
		);
		$this->chart    = $query->posts[0];
	}

	/**
	 * Runs the export AJAX action and returns the decoded response.
	 *
	 * @since 3.11.0
	 *
	 * @access private
	 * @return object|null
	 */
	private function run_export() {
		$_GET = array(
			'security' => wp_create_nonce( Visualizer_Plugin::ACTION_EXPORT_DATA . Visualizer_Plugin::VERSION ),
			'chart'    => $this->chart,
		);

		ob_start();
		try {
			$this->_handleAjax( 'visualizer-export-data' );
		} catch ( WPAjaxDieContinueException $e ) {
			// We expected this, do nothing.
		} catch ( WPAjaxDieStopException $ee ) {
			// We expected this, do nothing.
		}
		ob_end_clean();

		return json_decode( $this->_last_response );
	}

	/**
	 * Testing export
	 *
	 * @since 2.0.0
	 *
	 * @access public
	 */
	public function test_download_export() {
		$this->create_chart();
		$this->_setRole( 'administrator' );

		$response = $this->run_export();
		$this->assertIsObject( $response );
		$this->assertTrue( property_exists( $response, 'success' ) );
		$this->assertTrue( property_exists( $response, 'data' ) );
		$this->assertTrue( $response->success );
	}

	/**
	 * CSV response contains the expected keys from _getCSV.
	 *
	 * @since 3.11.0
	 *
	 * @access public
	 */
	public function test_csv_export_response_structure() {
		$this->create_chart();
		$this->_setRole( 'administrator' );

		$response = $this->run_export();
		$this->assertIsObject( $response );
		$this->assertTrue( property_exists( $response, 'success' ) );
		$this->assertTrue( property_exists( $response, 'data' ) );
		$this->assertTrue( $response->success );

		$data = $response->data;
		$this->assertTrue( property_exists( $data, 'csv' ) );
		$this->assertTrue( property_exists( $data, 'name' ) );
		$this->assertTrue( property_exists( $data, 'string' ) );
		$this->assertIsString( $data->csv );
		$this->assertIsString( $data->name );
		$this->assertIsString( $data->string );
		// filename must end with .csv
		$this->assertStringEndsWith( '.csv', $data->name );
		// string is csv without the BOM
		$bom = chr( 0xEF ) . chr( 0xBB ) . chr( 0xBF );
		$this->assertStringNotContainsString( $bom, $data->string );
	}

	/**
	 * Export must fail gracefully for non-existent chart ids.
	 *
	 * @since 3.11.0
	 *
	 * @access public
	 */
	public function test_csv_export_invalid_chart_returns_no_success() {
		$this->_setRole( 'administrator' );

		$_GET = array(
			'security' => wp_create_nonce( Visualizer_Plugin::ACTION_EXPORT_DATA . Visualizer_Plugin::VERSION ),
			'chart'    => PHP_INT_MAX,
		);

		ob_start();
		try {
			$this->_handleAjax( 'visualizer-export-data' );
		} catch ( WPAjaxDieContinueException $e ) {
			// We expected this, do nothing.
		} catch ( WPAjaxDieStopException $ee ) {
			// We expected this, do nothing.
		}
		ob_end_clean();

		// No success JSON should have been emitted for a missing chart.
		$response = json_decode( $this->_last_response );
		$this->assertTrue( empty( $response ) || empty( $response->success ) );
	}
}
