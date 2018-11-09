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
	 * The error message.
	 *
	 * @access protected
	 * @var string
	 */
	protected $_error;

	/**
	 * Constructor.
	 *
	 * @access public
	 * @param string $query The query.
	 */
	public function __construct( $query = null ) {
		$this->_query = $query;
	}

	/**
	 * Fetches information from source, parses it and builds series and data arrays.
	 *
	 * @param bool $as_html Should the result be fetched as an HTML table or as an object.
	 * @access public
	 * @return boolean TRUE on success, otherwise FALSE.
	 */
	public function fetch( $as_html = false ) {
		if ( empty( $this->_query ) ) {
			return false;
		}

		global $wpdb;
		$wpdb->hide_errors();
		// @codingStandardsIgnoreStart
		$rows       = $wpdb->get_results( $this->_query, ARRAY_A );
		// @codingStandardsIgnoreEnd
		$wpdb->show_errors();

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

			if ( $as_html ) {
				return $this->html( $headers, $results );
			}
			return $this->object( $headers, $results );
		}

		$this->_error = $wpdb->last_error;
		return null;
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
			case 8:
				// numeric.
				return 'number';
			case 12:
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
	 * Returns the error, if any.
	 *
	 * @access public
	 * @return string
	 */
	public function get_error() {
		return $this->_error;
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
