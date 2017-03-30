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
	public function test_url_import( $url, $content, $series ) {
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

		$series_new = get_post_meta( $this->chart, 'visualizer-series', true );
		$chart      = get_post( $this->chart );
		$src        = get_post_meta( $this->chart, 'visualizer-source', true );
		$content_new    = $chart->post_content;

		$this->assertEquals( 'Visualizer_Source_Csv_Remote', $src );
		$this->assertEquals( $content_new, serialize( $content ) );
		$this->assertEquals( $series_new, $series );
	}

	/**
	 * Testing file import feature.
	 *
	 * @access public
	 * @dataProvider fileProvider
	 */
	public function test_file_import( $file, $content, $series ) {
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

		$series_new = get_post_meta( $this->chart, 'visualizer-series', true );
		$chart      = get_post( $this->chart );
		$src        = get_post_meta( $this->chart, 'visualizer-source', true );
		$content_new    = $chart->post_content;

		$this->assertEquals( 'Visualizer_Source_Csv', $src );
		$this->assertEquals( $content_new, serialize( $content ) );
		$this->assertEquals( $series_new, $series );
	}

	/**
	 * Testing editor feature.
	 *
	 * @access public
	 * @dataProvider editorDataProvider
	 */
	public function test_pro_editor( $data, $content ) {
		if ( ! defined( 'VISUALIZER_PRO_VERSION' ) ) {
			$this->markTestSkipped( 'PRO not installed/available, skipping test' );
		}

		$this->create_chart();
		$this->_setRole( 'administrator' );

		$_POST  = array(
			'chart_data'    => $data,
		);
		$_GET   = array(
			'nonce'         => wp_create_nonce(),
			'chart'         => $this->chart,
		);
		$_FILES = array();

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

		$chart      = get_post( $this->chart );
		$content_new    = $chart->post_content;

		$this->assertEquals( $content_new, serialize( $content ) );
	}

	/**
	 * Testing fetch from chat feature. We only need to test fetching, because we already have a test case for uploading data
	 *
	 * @access public
	 */
	public function test_pro_fetch_from_chart() {
		if ( ! defined( 'VISUALIZER_PRO_VERSION' ) ) {
			$this->markTestSkipped( 'PRO not installed/available, skipping test' );
		}

		$this->create_chart();
		$this->_setRole( 'administrator' );

		$_GET   = array(
			'nonce'         => wp_create_nonce(),
			'chart_id'      => $this->chart,
		);

		// swallow the output
		ob_start();
		try {
			$this->_handleAjax( 'visualizer-fetch-data' );
		} catch ( WPAjaxDieContinueException  $e ) {
			// We expected this, do nothing.
		} catch ( WPAjaxDieStopException $ee) {
			// We expected this, do nothing.
		}
		ob_end_clean();

		$response = json_decode( $this->_last_response );
		$this->assertInternalType( 'object', $response );
		$this->assertObjectHasAttribute( 'success', $response );
		$this->assertObjectHasAttribute( 'data', $response );
		$this->assertTrue( $response->success );
	}

	/**
	 * Provide the "edited" data
	 *
	 * @access public
	 */
	public function editorDataProvider() {
		$data       = array();
		$file       = VISUALIZER_ABSPATH . DIRECTORY_SEPARATOR . 'samples' . DIRECTORY_SEPARATOR . 'line.csv';
		list($content, $series) = $this->parseFile( $file, 10 );

		if ( ($handle = fopen( $file, 'r' )) !== false ) {
			$row    = 0;
			while ( ($line = fgetcsv( $handle, 0, VISUALIZER_CSV_DELIMITER, VISUALIZER_CSV_ENCLOSURE )) !== false ) {
				if ( $row++ <= 1 ) {
					$cols   = count( $line );
					$datum  = array();
					for ( $col = 0; $col < $cols; $col++ ) {
						$datum[]    = '"' . $line[ $col ] . '"';
					}
				} else {
					$cols   = count( $line );
					$datum  = array();
					for ( $col = 0; $col < $cols; $col++ ) {
						if ( is_numeric( $line[ $col ] ) ) {
							// multiply all numbers by 10
							$datum[]    = $line[ $col ] * 10;
						} else {
							$datum[]    = '"' . $line[ $col ] . '"';
						}
					}
				}
				$data[] = $datum;
			}
		}

		$csv        = array();
		foreach ( $data as $row ) {
			$csv[]  = '[' . implode( ',', $row ) . ']';
		}
		$csv        = '[' . implode( ',', $csv ) . ']';
		return array(
			array( $csv, $content ),
		);
	}
	/**
	 * Provide the fileURL for uploading the file
	 *
	 * @access public
	 */
	public function fileProvider() {
		$file       = VISUALIZER_ABSPATH . DIRECTORY_SEPARATOR . 'samples' . DIRECTORY_SEPARATOR . 'bar.csv';
		list($content, $series) = $this->parseFile( $file );
		return array(
				array( $file, $content, $series ),
		);
	}

	/**
	 * Provide the URL for uploading the file
	 *
	 * @access public
	 */
	public function urlProvider() {
		$url        = 'http://localhost/wp-content/plugins/wp-visualizer/samples/bar.csv';
		$file       = download_url( $url );
		list($content, $series) = $this->parseFile( $file );
		unlink( $file );
		return array(
			array( $url, array( 'source' => $url, 'data' => $content ), $series ),
		);
	}

	/**
	 * Provide the parsed (and manipulated, if required) data for the specific data file
	 *
	 * @access private
	 */
	private function parseFile( $file, $multiplyValuesBy = 1 ) {
		$file           = $file;
		ini_set( 'auto_detect_line_endings', true );
		$handle         = fopen( $file, 'rb' );

		// read column titles
		$labels = fgetcsv( $handle, 0, VISUALIZER_CSV_DELIMITER, VISUALIZER_CSV_ENCLOSURE );
		// read series types
		$types = fgetcsv( $handle, 0, VISUALIZER_CSV_DELIMITER, VISUALIZER_CSV_ENCLOSURE );

		$_series = array();
		for ( $i = 0, $len = count( $labels ); $i < $len; $i++ ) {
			$default_type = $i == 0 ? 'string' : 'number';
			$_series[]   = array(
				'label' => $labels[ $i ],
				'type'  => isset( $types[ $i ] ) ? $types[ $i ] : $default_type,
			);
		}

		$_content    = array();
		while ( ( $data = fgetcsv( $handle, 0, VISUALIZER_CSV_DELIMITER, VISUALIZER_CSV_ENCLOSURE ) ) !== false ) {
			foreach ( $_series as $i => $series ) {
				// if no value exists for the seires, then add null
				if ( ! isset( $data[ $i ] ) ) {
					$data[ $i ] = null;
				}

				if ( is_null( $data[ $i ] ) ) {
					continue;
				}

				switch ( $series['type'] ) {
					case 'number':
						$data[ $i ] = (  is_numeric( $data[ $i ] ) ) ? floatval( $data[ $i ] * $multiplyValuesBy ) : (is_numeric( str_replace( ',', '', $data[ $i ] ) ) ?  floatval( ( str_replace( ',', '', $data[ $i ] ) ) * $multiplyValuesBy ) : null);
						break;
					case 'boolean':
						$data[ $i ] = ! empty( $data[ $i ] ) ? filter_validate( $data[ $i ], FILTER_VALIDATE_BOOLEAN ) : null;
						break;
					case 'timeofday':
						$date = new DateTime( '1984-03-16T' . $data[ $i ] );
						if ( $date ) {
							$data[ $i ] = array(
								intval( $date->format( 'H' ) ),
								intval( $date->format( 'i' ) ),
								intval( $date->format( 's' ) ),
								0,
							);
						}
						break;
				}
			}
			$_content[] = $data;
		}
		fclose( $handle );
		return array( $_content, $_series );
	}
}
