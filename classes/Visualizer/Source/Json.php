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
 * Source manager for JSON URLs.
 *
 * @category Visualizer
 * @package Source
 *
 * @since 1.0.0
 */
class Visualizer_Source_Json extends Visualizer_Source {

	/**
	 * The url to the data.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @var string
	 */
	protected $_url;

	/**
	 * The root to the data.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @var string
	 */
	protected $_root;

	/**
	 * The array that contains the definition of the data.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @var array
	 */
	protected $_args;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param array $params The array that contains the definition of the data.
	 */
	public function __construct( $params = null ) {
		$this->_args = $params;
		if ( isset( $this->_args['url'] ) ) {
			$this->_url = trim( $this->_args['url'] );
		}
		if ( isset( $this->_args['root'] ) ) {
			$this->_root = trim( $this->_args['root'] );
		}
	}

	/**
	 * Get the root elements for JSON-endpoint.
	 *
	 * @since ?
	 *
	 * @access public
	 */
	public function fetchRoots() {
		return $this->getRootElements( '', '', array(), $this->getJSON() );
	}

	/**
	 * Parse the JSON-endpoint from the chosen root as the base.
	 *
	 * @since ?
	 *
	 * @access public
	 */
	public function parse() {
		$array  = $this->getJSON();
		$root   = array_filter( explode( '>', $this->_root ) );
		$leaf   = $array;
		foreach ( $root as $tag ) {
			if ( array_key_exists( $tag, $leaf ) ) {
				$leaf = $array[ $tag ];
			} else {
				// if the tag does not exist, we assume it is present in the 0th element of the current array.
				$leaf = $leaf[0][ $tag ];
			}
		}

		// now that we have got the final array we need to operate on, we will use this as the collection of series.
		// but we will filter out all elements of this array that have array as a value.
		$data = array();
		foreach ( $leaf as $datum ) {
			$inner_data = array();
			foreach ( $datum as $key => $value ) {
				if ( is_array( $value ) ) {
					continue;
				}
				$inner_data[ $key ] = $value;
			}
			$data[] = $inner_data;
		}

		do_action( 'themeisle_log_event', Visualizer_Plugin::NAME, sprintf( 'Parsed data endpoint %s with rooot %s is %s = ', $this->_url, $this->_root, print_r( $data, true ) ), 'debug', __FILE__, __LINE__ );

		return $data;
	}

	/**
	 * Get the root elements for JSON-endpoint.
	 *
	 * @since ?
	 *
	 * @access private
	 */
	private function getRootElements( $parent, $now, $root, $array ) {
		foreach ( $array as $key => $value ) {
			if ( is_array( $value ) && ! empty( $value ) ) {
				if ( ! is_numeric( $key ) ) {
					$now = sprintf( '%s>%s', $parent, $key );
					$root[] = $now;
				} else {
					$now = $parent;
				}
				$root = $this->getRootElements( $now, $key, $root, $value );
			}
		}
		return array_filter( array_unique( $root ) );
	}

	/**
	 * Get the JSON for the JSON-endpoint.
	 *
	 * @since ?
	 *
	 * @access private
	 */
	private function getJSON() {
		$response = wp_remote_get( $this->_url, apply_filters( 'visualizer_json_args', array() ) );
		if ( is_wp_error( $response ) ) {
			do_action( 'themeisle_log_event', Visualizer_Plugin::NAME, sprintf( 'Error while fetching JSON endpoint %s = ', $this->_url, print_r( $response, true ) ), 'error', __FILE__, __LINE__ );
			return null;
		}

		$array  = json_decode( wp_remote_retrieve_body( $response ), true );

		do_action( 'themeisle_log_event', Visualizer_Plugin::NAME, sprintf( 'JSON array for the endpoint is %s = ', print_r( $array, true ) ), 'debug', __FILE__, __LINE__ );

		return $array;
	}


	/**
	 * Fetches series information. This is fetched only through the UI and not while refreshing the chart data.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 */
	private function _fetchSeries() {
		$params = $this->_args;
		$headers = array_filter( $params['header'] );
		$types = array_filter( $params['type'] );
		$header_row = $type_row = array();
		if ( $headers ) {
			foreach ( $headers as $header ) {
				if ( ! empty( $types[ $header ] ) ) {
					$this->_series[] = array(
						'label' => $header,
						'type'  => $types[ $header ],
					);
				}
			}
		}

		return true;
	}

	/**
	 * Fetches data information.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 */
	private function _fetchData() {
		$params = $this->_args;

		$headers    = wp_list_pluck( $this->_series, 'label' );
		$data   = $this->parse();
		foreach ( $data as $line ) {
			$data_row = array();
			foreach ( $line as $header => $value ) {
				if ( in_array( $header, $headers, true ) ) {
					$data_row[] = $value;
				}
			}
			$this->_data[] = $this->_normalizeData( $data_row );
		}

		return true;
	}

	/**
	 * Fetches information from source, parses it and builds series and data arrays.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return boolean TRUE on success, otherwise FALSE.
	 */
	public function fetch() {
		$params = $this->_args;
		$this->_fetchSeries();
		$this->_fetchData();

		return true;
	}

	/**
	 * Refresh the data for the provided series.
	 *
	 * @since ?
	 *
	 * @access public
	 */
	public function refresh( $series ) {
		$this->_series = $series;
		$this->_fetchData();
		return true;
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
