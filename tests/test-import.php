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
	 * Testing cloing of chart.
	 *
	 * @access public
	 */
	public function test_clone_chart() {
		$this->create_chart();
		$this->_setRole( 'administrator' );
		$_GET  = array(
			'nonce' => wp_create_nonce( Visualizer_Plugin::ACTION_CLONE_CHART ),
			'chart' => $this->chart,
		);
		// swallow the output
		ob_start();
		try {
			$this->_handleAjax( 'visualizer-clone-chart' );
		} catch ( WPAjaxDieContinueException  $e ) {
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
				'post__not_in' => array( $this->chart ),
			)
		);

		$new_id = $query->posts[0];

		// all post meta existing in old chart should exist in new chart.
		$old_meta = $new_meta = array();
		$post_meta = get_post_meta( $this->chart );
		foreach ( $post_meta as $key => $value ) {
			if ( strpos( $key, 'visualizer-' ) !== false ) {
				$old_meta [ $key ] = $value;
			}
		}

		$post_meta = get_post_meta( $new_id );
		foreach ( $post_meta as $key => $value ) {
			if ( strpos( $key, 'visualizer-' ) !== false ) {
				$new_meta [ $key ] = $value;
			}
		}

		$this->assertEquals( $old_meta, $new_meta );
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
		$_POST = array(
			'remote_data' => $url,
		);
		$_GET  = array(
			'nonce' => wp_create_nonce( 'visualizer-upload-data' ),
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
		$series_new  = get_post_meta( $this->chart, 'visualizer-series', true );
		$chart       = get_post( $this->chart );
		$src         = get_post_meta( $this->chart, 'visualizer-source', true );
		$content_new = $chart->post_content;
		$this->assertEquals( 'Visualizer_Source_Csv_Remote', $src );
		$this->assertEquals( $content_new, serialize( $content ) );
		$this->assertEquals( $series_new, $series );
	}

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
	 * Testing XLSX file import feature.
	 *
	 * @access public
	 * @dataProvider xlsxFileProvider
	 */
	public function test_file_import_xlsx( $file, $content, $series ) {
		$this->create_chart();
		$this->_setRole( 'administrator' );
		$dest = dirname( __FILE__ ) . DIRECTORY_SEPARATOR . basename( $file );
		copy( $file, $dest );
		$_FILES = array(
			'local_data' => array(
				'tmp_name' => $dest,
				'name'     => basename( $file ),
				'error'    => 0,
			),
		);
		$_GET   = array(
			'nonce' => wp_create_nonce( 'visualizer-upload-data' ),
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
		$series_new  = get_post_meta( $this->chart, 'visualizer-series', true );
		$chart       = get_post( $this->chart );
		$src         = get_post_meta( $this->chart, 'visualizer-source', true );
		$content_new = $chart->post_content;
		$this->assertEquals( 'Visualizer_Source_Xlsx', $src );
		$this->assertEquals( $content_new, serialize( $content ) );
		$this->assertEquals( $series_new, $series );
	}

	/**
	 * Provide the XLSX file for uploading.
	 *
	 * @access public
	 */
	public function xlsxFileProvider() {
		$file = VISUALIZER_ABSPATH . DIRECTORY_SEPARATOR . 'samples' . DIRECTORY_SEPARATOR . 'bar.xlsx';
		$this->assertFileExists( $file, 'Fixture samples/bar.xlsx does not exist' );
		list( $content, $series ) = $this->parseXlsxFile( $file );
		return array(
			array( $file, $content, $series ),
		);
	}

	/**
	 * Parses an XLSX fixture file via OpenSpout and returns [ $content, $series ]
	 * in the same shape that Visualizer_Source_Xlsx::fetch() produces, so the
	 * test assertion is independent of the source class implementation.
	 *
	 * @access private
	 * @param  string $file Absolute path to the XLSX file.
	 * @return array        Two-element array: [ $content, $series ].
	 */
	private function parseXlsxFile( $file ) {
		$vendor_file = VISUALIZER_ABSPATH . 'vendor/autoload.php';
		if ( is_readable( $vendor_file ) ) {
			include_once $vendor_file;
		}

		$reader = \OpenSpout\Reader\Common\Creator\ReaderEntityFactory::createXLSXReader();
		$reader->open( $file );

		$all_rows = array();
		foreach ( $reader->getSheetIterator() as $sheet ) {
			foreach ( $sheet->getRowIterator() as $row ) {
				$row_data = array();
				foreach ( $row->getCells() as $cell ) {
					$value      = $cell->getValue();
					$row_data[] = is_null( $value ) ? null : (string) $value;
				}
				$all_rows[] = $row_data;
			}
			break; // first sheet only
		}
		$reader->close();

		// Row 1 = labels, Row 2 = types (mirrors Visualizer_Source_Xlsx convention).
		$label_values = array_values( $all_rows[0] );
		$type_values  = array_values( $all_rows[1] );
		$col_count    = count( $label_values );

		$_series = array();
		for ( $i = 0; $i < $col_count; $i++ ) {
			$default_type = ( $i === 0 ) ? 'string' : 'number';
			$_series[]    = array(
				'label' => isset( $label_values[ $i ] ) ? $label_values[ $i ] : '',
				'type'  => ( isset( $type_values[ $i ] ) && ! empty( $type_values[ $i ] ) ) ? trim( $type_values[ $i ] ) : $default_type,
			);
		}

		$_content = array();
		for ( $r = 2; $r < count( $all_rows ); $r++ ) {
			$row = $all_rows[ $r ];
			foreach ( $_series as $i => $col ) {
				if ( ! isset( $row[ $i ] ) ) {
					$row[ $i ] = null;
				}
				if ( is_null( $row[ $i ] ) ) {
					continue;
				}
				switch ( $col['type'] ) {
					case 'number':
						$row[ $i ] = is_numeric( $row[ $i ] ) ? floatval( $row[ $i ] ) : ( is_numeric( str_replace( ',', '', $row[ $i ] ) ) ? floatval( str_replace( ',', '', $row[ $i ] ) ) : null );
						break;
					case 'boolean':
						$datum     = trim( strval( $row[ $i ] ) );
						$row[ $i ] = in_array( $datum, array( 'true', 'yes', '1' ), true ) ? 'true' : 'false';
						break;
				}
			}
			$_content[] = $row;
		}

		return array( $_content, $_series );
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
		$dest = dirname( __FILE__ ) . DIRECTORY_SEPARATOR . basename( $file );
		copy( $file, $dest );
		$_FILES = array(
			'local_data' => array(
				'tmp_name' => $dest,
				'name'     => basename( $file ),
				'error'    => 0,
			),
		);
		$_GET   = array(
			'nonce' => wp_create_nonce( 'visualizer-upload-data' ),
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
		$series_new  = get_post_meta( $this->chart, 'visualizer-series', true );
		$chart       = get_post( $this->chart );
		$src         = get_post_meta( $this->chart, 'visualizer-source', true );
		$content_new = $chart->post_content;
		$this->assertEquals( 'Visualizer_Source_Csv', $src );
		$this->assertEquals( $content_new, serialize( $content ) );
		$this->assertEquals( $series_new, $series );
	}

	/**
	 * Testing database import feature.
	 *
	 * @access public
	 */
	public function test_db_import() {
		$this->markTestSkipped( 'this test is disabled till we can figure out why its not recognizing the new ajax methods' );
		$this->create_chart();
		$this->_setRole( 'administrator' );

		$ids = $this->factory->post->create_many( 10 );

		global $wpdb;

		$_GET   = array(
			'security' => wp_create_nonce( Visualizer_Plugin::ACTION_SAVE_DB_QUERY . Visualizer_Plugin::VERSION ),
			'chart' => $this->chart,
		);
		$_POST  = array(
			'params'    => array(
				'from'      => $wpdb->prefix . 'posts',
				'select'    => 'count(*), ' . $wpdb->prefix . 'posts.ID',
				'group'     => $wpdb->prefix . 'posts.ID',
				'limit'     => 5,
			),
			'refresh'   => 1,
		);

		try {
			$this->_handleAjax( Visualizer_Plugin::ACTION_SAVE_DB_QUERY );
		} catch ( WPAjaxDieContinueException  $e ) {
			// We expected this, do nothing.
		} catch ( WPAjaxDieStopException $ee ) {
			// We expected this, do nothing.
		}

		$post = get_post( $this->chart );
		echo $post->post_content;
	}

	/**
	 * Testing cron custom schedule.
	 *
	 * @return void
	 */
	public function test_custom_cron_schedule() {
		$schedules = wp_get_schedules();
		$this->assertArrayHasKey( 'visualizer_ten_minutes', $schedules );
	}

	/**
	 * Provide the fileURL for uploading the file
	 *
	 * @access public
	 */
	public function fileProvider() {
		$this->assertFileExists( VISUALIZER_ABSPATH . DIRECTORY_SEPARATOR . 'samples', 'Folder "samples" does not exist' );

		$file = VISUALIZER_ABSPATH . DIRECTORY_SEPARATOR . 'samples' . DIRECTORY_SEPARATOR . 'bar.csv';
		list( $content, $series ) = $this->parseFile( $file );

		return array(
			array( $file, $content, $series ),
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
			$default_type = $i === 0 ? 'string' : 'number';
			$_series[]    = array(
				'label' => $labels[ $i ],
				'type'  => isset( $types[ $i ] ) ? $types[ $i ] : $default_type,
			);
		}
		$_content = array();
		// phpcs:ignore WordPress.CodeAnalysis.AssignmentInCondition.FoundInWhileCondition
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
						$data[ $i ] = ! empty( $data[ $i ] ) ? filter_var( $data[ $i ], FILTER_VALIDATE_BOOLEAN ) : null;
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

	/**
	 * Provide the URL for uploading the file
	 *
	 * @access public
	 */
	public function urlProvider() {
		$this->assertFileExists( VISUALIZER_ABSPATH . DIRECTORY_SEPARATOR . 'samples', 'Folder "samples" does not exist' );

		$url  = 'https://demo.themeisle.com/wp-content/plugins/visualizer/samples/bar.csv';
		$file = download_url( $url );
		list( $content, $series ) = $this->parseFile( $file );
		unlink( $file );

		return array(
			array(
				$url,
				array(
					'source' => $url,
					'data'   => $content,
				),
				$series,
			),
		);
	}
}
