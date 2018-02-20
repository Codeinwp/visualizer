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
	 * Provide the fileURL for uploading the file
	 *
	 * @access public
	 */
	public function fileProvider() {
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

	/**
	 * Provide the URL for uploading the file
	 *
	 * @access public
	 */
	public function urlProvider() {
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
