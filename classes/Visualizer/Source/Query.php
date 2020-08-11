<?php

// +----------------------------------------------------------------------+
// | Copyright 2013  Madpixels  (email : visualizer@madpixels.net)        |
// +----------------------------------------------------------------------+
// | This program is free software; you can redistribute it and/or modify |
// | it under the terms of the GNU General Public License, version 2, as  |
// | published by the Free Software Foundation.                           |
// |                                                                      |
// | This program is distributed in the hope that it will be useful,      |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of       |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the        |
// | GNU General Public License for more details.                         |
// |                                                                      |
// | You should have received a copy of the GNU General Public License    |
// | along with this program; if not, write to the Free Software          |
// | Foundation, Inc., 51 Franklin St, Fifth Floor, Boston,               |
// | MA 02110-1301 USA                                                    |
// +----------------------------------------------------------------------+
// | Author: Eugene Manuilov <eugene@manuilov.org>                        |
// +----------------------------------------------------------------------+
/**
 * Source manager for query builder.
 *
 * @category Visualizer
 * @package Source
 */
class Visualizer_Source_Query extends Visualizer_Source {

	/**
	 * The query.
	 *
	 * @access protected
	 * @var string
	 */
	protected $_query;

	/**
	 * The chart id.
	 *
	 * @access protected
	 * @var int
	 */
	protected $_chart_id;

	/**
	 * Any additional parameters (e.g. for connecting to a remote db).
	 *
	 * @access protected
	 * @var array
	 */
	protected $_params;

	/**
	 * Constructor.
	 *
	 * @access public
	 * @param string $query The query.
	 * @param int    $chart_id The chart id.
	 * @param array  $params Any additional parameters (e.g. for connecting to a remote db).
	 */
	public function __construct( $query = null, $chart_id = null, $params = null ) {
		$this->_query = $query;
		$this->_chart_id = $chart_id;
		$this->_params = $params;
	}

	/**
	 * Fetches information from source, parses it and builds series and data arrays.
	 *
	 * @param bool $as_html Should the result be fetched as an HTML table or as an object.
	 * @param bool $results_as_numeric_array Should the result be fetched as ARRAY_N instead of ARRAY_A.
	 * @param bool $raw_results Should the result be returned without processing.
	 * @access public
	 * @return boolean TRUE on success, otherwise FALSE.
	 */
	public function fetch( $as_html = false, $results_as_numeric_array = false, $raw_results = false ) {
		if ( empty( $this->_query ) ) {
			return false;
		}

		// only select queries allowed.
		if ( preg_match( '/^\s*(insert|delete|update|replace|create|alter|drop|truncate)\s/i', $this->_query ) ) {
			$this->_error = __( 'Only SELECT queries are allowed', 'visualizer' );
			return false;
		}

		// impose a limit if no limit clause is provided.
		if ( strpos( strtolower( $this->_query ), ' limit ' ) === false ) {
			$this->_query   .= ' LIMIT ' . apply_filters( 'visualizer_sql_query_limit', 1000, $this->_chart_id );
		}

		$this->_query = apply_filters( 'visualizer_db_query', $this->_query, $this->_chart_id, $this->_params );

		$results    = array();
		$headers    = array();

		// short circuit results for remote dbs.
		if ( false !== ( $remote_results = apply_filters( 'visualizer_db_query_execute', false, $this->_query, $as_html, $results_as_numeric_array, $raw_results, $this->_chart_id, $this->_params ) ) ) {
			$error = $remote_results['error'];
			if ( empty( $error ) ) {
				$results = $remote_results['results'];
				$headers = $remote_results['headers'];
			}

			$this->_error = $error;

			if ( $raw_results ) {
				return $results;
			}
		}

		if ( ! ( $results && $headers ) ) {
			global $wpdb;
			$wpdb->hide_errors();
			// @codingStandardsIgnoreStart
			$rows       = $wpdb->get_results( $this->_query, $results_as_numeric_array ? ARRAY_N : ARRAY_A );
			do_action( 'themeisle_log_event', Visualizer_Plugin::NAME, sprintf( 'Firing query %s to get results %s with error %s', $this->_query, print_r( $rows, true ), print_r( $wpdb->last_error, true ) ), 'debug', __FILE__, __LINE__ );
			// @codingStandardsIgnoreEnd
			$wpdb->show_errors();

			if ( $raw_results ) {
				return $rows;
			}

			if ( $rows ) {
				$results    = array();
				$headers    = array();
				if ( $rows ) {
					$row_num    = 0;
					foreach ( $rows as $row ) {
						$result     = array();
						$col_num    = 0;
						foreach ( $row as $k => $v ) {
							$result[]   = $v;
							if ( 0 === $row_num ) {
								$headers[]  = array( 'type' => $this->get_col_type( $col_num++ ), 'label' => $k );
							}
						}
						$results[] = $result;
						$row_num++;
					}
				}

				$this->_error = $wpdb->last_error;
			}
		}

		if ( $as_html ) {
			$results = $this->html( $headers, $results );
		} else {
			$results = $this->object( $headers, $results );
		}

		return apply_filters( 'visualizer_db_query_results', $results, $headers, $as_html, $results_as_numeric_array, $raw_results, $this->_query, $this->_chart_id, $this->_params );
	}

	/**
	 * Get the data type of the column.
	 *
	 * @param int $col_num The column index in the fetched result set.
	 * @access private
	 * @return int
	 */
	private function get_col_type( $col_num ) {
		global $wpdb;
		switch ( $wpdb->get_col_info( 'type', $col_num ) ) {
			case 0:
			case 5:
			case 4:
			case 9:
			case 3:
			case 2:
			case 246:
			case 8:
				// numeric.
				return 'number';
			case 10:
			case 12:
			case 14:
				// date.
				return 'date';
		}
		return 'string';
	}

	/**
	 * Returns the HTML output.
	 *
	 * @param array $headers The headers of the result set.
	 * @param array $results The data of the result set.
	 * @access private
	 * @return string
	 */
	private function html( $headers, $results ) {
		return Visualizer_Render_Layout::show( 'db-wizard-results', $headers, $results );
	}

	/**
	 * Sets the series and data.
	 *
	 * @param array $headers The headers of the result set.
	 * @param array $results The data of the result set.
	 * @access private
	 * @return bool
	 */
	private function object( $headers, $results ) {
		$series     = array();
		foreach ( $headers as $header ) {
			$series[]   = $header;
		}
		$this->_series = $series;

		$data       = array();
		foreach ( $results as $row ) {
			$data[] = $this->_normalizeData( $row );
		}
		$this->_data = $data;

		return true;
	}

	/**
	 * Returns the final query.
	 *
	 * @access public
	 * @return string
	 */
	public function get_query() {
		return $this->_query;
	}

	/**
	 * Returns source name.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return string The name of source.
	 */
	public function getSourceName() {
		return __CLASS__;
	}
}
