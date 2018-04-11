<?php

/**
 * Test class for the revisions.
 *
 * @package     Visualizer
 * @subpackage  Tests
 * @copyright   Copyright (c) 2017, Marius Cristea
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.0.0
 */
class Test_Revisions extends WP_Ajax_UnitTestCase {

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
		$_GET = array(
			'library' => 'yes',
			'tab'     => 'visualizer',
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
		$query       = new WP_Query(
			array(
				'post_type'   => Visualizer_Plugin::CPT_VISUALIZER,
				'post_status' => 'auto-draft',
				'numberposts' => 1,
				'fields'      => 'ids',
			)
		);
		$this->chart = $query->posts[0];
	}

	/**
	 * Testing revisions by cancelling edit.
	 *
	 * @access public
	 * @dataProvider fileProvider
	 */
	public function test_chart_edit_cancel( $file_orig, $file_new ) {
		wp_set_current_user( 1 );
		$this->_setRole( 'administrator' );
		$this->create_chart();

		// this is very important so that revisions are saved.
		wp_update_post( array('ID' => $this->chart, 'post_status' => 'publish') );

		$dest = dirname( __FILE__ ) . DIRECTORY_SEPARATOR . basename( $file_new );
		copy( $file_new, $dest );
		$_FILES = array(
			'local_data' => array(
				'tmp_name' => $dest,
				'error'    => 0,
			),
		);
		$_GET   = array(
			'nonce' => wp_create_nonce(),
			'chart' => $this->chart,
		);
		// swallow the output
		ob_start();
		try {
			$this->_handleAjax( 'visualizer-upload-data' );
		} catch ( WPAjaxDieContinueException  $e ) {
			// We expected this, do nothing.
		} catch ( WPAjaxDieStopException $ee ) {
			// We expected this, do nothing.
		}
		ob_end_clean();
		unlink( $dest );

		$revisions  = wp_get_post_revisions( $this->chart );
		$this->assertGreaterThan( 1, count( $revisions ) );
		$_GET       = array(
			'chart' => $this->chart,
		);
		$_POST      = array(
			'cancel'    => 1,
		);
		// swallow the output
		ob_start();
		try {
			$this->_handleAjax( 'visualizer-edit-chart' );
		} catch ( WPAjaxDieContinueException  $e ) {
			// We expected this, do nothing.
		} catch ( WPAjaxDieStopException $ee ) {
			// We expected this, do nothing.
		}
		ob_end_clean();

		$revisions  = wp_get_post_revisions( $this->chart );
		$this->assertEquals( 0, count( $revisions ) );

		list( $content, $series ) = $this->parseFile( $file_orig );

		$series_new  = get_post_meta( $this->chart, 'visualizer-series', true );
		$chart       = get_post( $this->chart );
		$src         = get_post_meta( $this->chart, 'visualizer-source', true );
		$content_new = $chart->post_content;
		$this->assertEquals( 'Visualizer_Source_Csv', $src );
		$this->assertEquals( $content_new, serialize( $content ) );
		$this->assertEquals( $series_new, $series );
	}

	/**
	 * Testing revisions by not cancelling edit but by implicitly editing it again.
	 *
	 * @access public
	 * @dataProvider fileProvider
	 */
	public function test_chart_edit_again( $file_orig, $file_new ) {
		wp_set_current_user( 1 );
		$this->_setRole( 'administrator' );
		$this->create_chart();

		// this is very important so that revisions are saved.
		wp_update_post( array('ID' => $this->chart, 'post_status' => 'publish') );

		$dest = dirname( __FILE__ ) . DIRECTORY_SEPARATOR . basename( $file_new );
		copy( $file_new, $dest );
		$_FILES = array(
			'local_data' => array(
				'tmp_name' => $dest,
				'error'    => 0,
			),
		);
		$_GET   = array(
			'nonce' => wp_create_nonce(),
			'chart' => $this->chart,
		);
		// swallow the output
		ob_start();
		try {
			$this->_handleAjax( 'visualizer-upload-data' );
		} catch ( WPAjaxDieContinueException  $e ) {
			// We expected this, do nothing.
		} catch ( WPAjaxDieStopException $ee ) {
			// We expected this, do nothing.
		}
		ob_end_clean();
		unlink( $dest );

		$revisions  = wp_get_post_revisions( $this->chart );
		$this->assertGreaterThan( 1, count( $revisions ) );

		// swallow the output
		ob_start();
		try {
			$this->_handleAjax( 'visualizer-edit-chart' );
		} catch ( WPAjaxDieContinueException  $e ) {
			// We expected this, do nothing.
		} catch ( WPAjaxDieStopException $ee ) {
			// We expected this, do nothing.
		}
		ob_end_clean();

		$revisions  = wp_get_post_revisions( $this->chart );
		$this->assertEquals( 0, count( $revisions ) );

		list( $content, $series ) = $this->parseFile( $file_orig );

		$series_new  = get_post_meta( $this->chart, 'visualizer-series', true );
		$chart       = get_post( $this->chart );
		$src         = get_post_meta( $this->chart, 'visualizer-source', true );
		$content_new = $chart->post_content;
		$this->assertEquals( 'Visualizer_Source_Csv', $src );
		$this->assertEquals( $content_new, serialize( $content ) );
		$this->assertEquals( $series_new, $series );
	}

	/**
	 * Testing revisions by saving edit.
	 *
	 * @access public
	 * @dataProvider fileProvider
	 */
	public function test_chart_edit_save( $file_orig, $file_new ) {
		wp_set_current_user( 1 );
		$this->_setRole( 'administrator' );
		$this->create_chart();

		// this is very important so that revisions are saved.
		wp_update_post( array('ID' => $this->chart, 'post_status' => 'publish') );

		$dest = dirname( __FILE__ ) . DIRECTORY_SEPARATOR . basename( $file_new );
		copy( $file_new, $dest );
		$_FILES = array(
			'local_data' => array(
				'tmp_name' => $dest,
				'error'    => 0,
			),
		);
		$_GET   = array(
			'nonce' => wp_create_nonce(),
			'chart' => $this->chart,
		);
		// swallow the output
		ob_start();
		try {
			$this->_handleAjax( 'visualizer-upload-data' );
		} catch ( WPAjaxDieContinueException  $e ) {
			// We expected this, do nothing.
		} catch ( WPAjaxDieStopException $ee ) {
			// We expected this, do nothing.
		}
		ob_end_clean();
		unlink( $dest );

		$revisions  = wp_get_post_revisions( $this->chart );
		$this->assertGreaterThan( 1, count( $revisions ) );
		$_GET       = array(
			'chart' => $this->chart,
		);
		$_POST      = array(
			'save'  => 1,
		);
		// swallow the output
		ob_start();
		try {
			$this->_handleAjax( 'visualizer-edit-chart' );
		} catch ( WPAjaxDieContinueException  $e ) {
			// We expected this, do nothing.
		} catch ( WPAjaxDieStopException $ee ) {
			// We expected this, do nothing.
		}
		ob_end_clean();

		$revisions  = wp_get_post_revisions( $this->chart );
		$this->assertEquals( 0, count( $revisions ) );

		list( $content, $series ) = $this->parseFile( $file_new );

		$series_new  = get_post_meta( $this->chart, 'visualizer-series', true );
		$chart       = get_post( $this->chart );
		$src         = get_post_meta( $this->chart, 'visualizer-source', true );
		$content_new = $chart->post_content;
		$this->assertEquals( 'Visualizer_Source_Csv', $src );
		$this->assertEquals( $content_new, serialize( $content ) );
		$this->assertEquals( $series_new, $series );
	}
	/**
	 * Provide the fileURL for uploading the file
	 *
	 * @access public
	 */
	public function fileProvider() {
		return array(
			array( VISUALIZER_ABSPATH . DIRECTORY_SEPARATOR . 'samples' . DIRECTORY_SEPARATOR . 'line.csv', VISUALIZER_ABSPATH . DIRECTORY_SEPARATOR . 'samples' . DIRECTORY_SEPARATOR . 'bar.csv' ),
		);
	}

	/**
	 * Provide the parsed (and manipulated, if required) data for the specific data file
	 *
	 * @access private
	 */
	private function parseFile( $file, $multiplyValuesBy = 1 ) {
		$file = $file;
		ini_set( 'auto_detect_line_endings', true );
		$handle = fopen( $file, 'rb' );
		// read column titles
		$labels = fgetcsv( $handle, 0, VISUALIZER_CSV_DELIMITER, VISUALIZER_CSV_ENCLOSURE );
		// read series types
		$types = fgetcsv( $handle, 0, VISUALIZER_CSV_DELIMITER, VISUALIZER_CSV_ENCLOSURE );
		$_series = array();
		for ( $i = 0, $len = count( $labels ); $i < $len; $i ++ ) {
			$default_type = $i == 0 ? 'string' : 'number';
			$_series[]    = array(
				'label' => $labels[ $i ],
				'type'  => isset( $types[ $i ] ) ? $types[ $i ] : $default_type,
			);
		}
		$_content = array();
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
						$data[ $i ] = ( is_numeric( $data[ $i ] ) ) ? floatval( $data[ $i ] * $multiplyValuesBy ) : ( is_numeric( str_replace( ',', '', $data[ $i ] ) ) ? floatval( ( str_replace( ',', '', $data[ $i ] ) ) * $multiplyValuesBy ) : null );
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
