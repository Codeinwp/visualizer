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
	 * The paging element.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @var string
	 */
	protected $_paging;

	/**
	 * The array that contains the definition of the data.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @var array
	 */
	protected $_args;

	const TAG_SEPARATOR = '>';
	const TAG_SEPARATOR_VIEW = ' &#x27A4; ';

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
		if ( isset( $this->_args['paging'] ) ) {
			$this->_paging = trim( $this->_args['paging'] );
		}

		do_action( 'themeisle_log_event', Visualizer_Plugin::NAME, sprintf( 'Constructor called for params = %s', print_r( $params, true ) ), 'debug', __FILE__, __LINE__ );
	}

	/**
	 * Get the root elements for JSON-endpoint.
	 *
	 * @since ?
	 *
	 * @access public
	 */
	public function fetchRoots() {
		$roots  = apply_filters( 'visualizer_json_get_root_elements', false, $this->_url );
		if ( false !== $roots ) {
			return $roots;
		}
		return $this->getRootElements( 'root', '', array(), $this->getJSON() );
	}

	/**
	 * Get the name of the elements that are likely to contain the paginated URLs.
	 *
	 * @since ?
	 *
	 * @access public
	 */
	public function getPaginationElements() {
		$pages = array();
		$root   = explode( self::TAG_SEPARATOR, $this->_root );
		// the pagination element is usually one level above the root element we are going to use
		// e.g. if the data is in root > results, the pagination element (let's call it "next") would be root > next and not root > results > next.
		array_pop( $root );

		// base of the next element.
		$base = implode( self::TAG_SEPARATOR, $root );
		// get rid of the first element as that is the faux root element indicator
		array_shift( $root );

		$array  = $this->getJSON();
		if ( is_null( $array ) ) {
			return $pages;
		}

		$leaf   = $array;
		if ( ! empty( $root ) ) {
			foreach ( $root as $tag ) {
				if ( array_key_exists( $tag, $leaf ) ) {
					$leaf = $array[ $tag ];
				} else {
					// if the tag does not exist, we assume it is present in the 0th element of the current array.
					$leaf = $leaf[0][ $tag ];
				}
			}
		}

		$paging = array();
		foreach ( $leaf as $key => $value ) {
			// the paging element's value will most probably contain the url of the feed.
			if ( is_string( $value ) && 0 === stripos( $value, $this->_url ) ) {
				$paging[] = $key;
			}
		}

		foreach ( array_filter( array_unique( $paging ) ) as $page ) {
			$pages[] = $base . self::TAG_SEPARATOR . $page;
		}
		return $pages;
	}

	/**
	 * Parse the JSON-endpoint from the chosen root as the base.
	 *
	 * @since ?
	 *
	 * @access public
	 */
	public function parse() {
		$url    = $this->_url;
		$data   = array();
		$page   = 1;

		while ( ! is_null( $url ) && $page++ < apply_filters( 'visualizer_json_fetch_pages', 5, $this->_url ) ) {
			$array  = $this->getJSON( $url );
			if ( is_null( $array ) ) {
				return $data;
			}

			$root   = explode( self::TAG_SEPARATOR, $this->_root );
			// get rid of the first element as that is the faux root element indicator
			array_shift( $root );
			$leaf   = $array;
			foreach ( $root as $tag ) {
				if ( array_key_exists( $tag, $leaf ) ) {
					$leaf = $leaf[ $tag ];
				} else {
					// if the tag does not exist, we assume it is present in the 0th element of the current array.
					// TODO: we may want to change this to a filter later.
					$leaf = $leaf[0][ $tag ];
				}
			}

			// now that we have got the final array we need to operate on, we will use this as the collection of series.
			// lets check if the series is a flat-series e.g. https://api.exchangeratesapi.io/latest
			// in this, all values of `$leaf` would be a string, not an array.
			$values = array_values( $leaf );
			if ( ! is_array( $values[0] ) ) {
				do_action( 'themeisle_log_event', Visualizer_Plugin::NAME, sprintf( 'Looks like a flat-series = %s', print_r( $leaf, true ) ), 'debug', __FILE__, __LINE__ );
				$inner_data = array();
				foreach ( $leaf as $datum => $value ) {
					$inner_data[ $datum ] = $value;
				}
				$data[] = $inner_data;
			} else {
				// we will filter out all elements of this array that have array as a value.
				foreach ( $leaf as $datum ) {
					$inner_data = array();
					foreach ( $datum as $key => $value ) {
						if ( is_array( $value ) ) {
							continue;
						}
						$inner_data[ $key ] = $value;
					}
					// if we want to exclude entire rows on the basis of some data/key.
					if ( apply_filters( 'visualizer_json_include_row', true, $inner_data, $this->_root, $this->_url ) ) {
						$data[] = $inner_data;
					}
				}
			}

			$url    = $this->getNextPage( $array );
		}

		do_action( 'themeisle_log_event', Visualizer_Plugin::NAME, sprintf( 'Parsed data endpoint %s with rooot %s is %s = ', $this->_url, $this->_root, print_r( $data, true ) ), 'debug', __FILE__, __LINE__ );

		return $data;
	}

	/**
	 * Get the next page URL.
	 *
	 * @since ?
	 *
	 * @access private
	 */
	private function getNextPage( $array ) {
		if ( empty( $this->_paging ) ) {
			return null;
		}

		$root   = explode( self::TAG_SEPARATOR, $this->_paging );
		// get rid of the first element as that is the faux root element indicator
		array_shift( $root );
		$leaf   = $array;
		foreach ( $root as $tag ) {
			if ( array_key_exists( $tag, $leaf ) ) {
				$leaf = $array[ $tag ];
			} else {
				// if the tag does not exist, we assume it is present in the 0th element of the current array.
				// TODO: we may want to change this to a filter later.
				$leaf = $leaf[0][ $tag ];
			}
		}

		do_action( 'themeisle_log_event', Visualizer_Plugin::NAME, sprintf( 'Got next page as %s for paging element %s', $leaf, $this->_paging ), 'debug', __FILE__, __LINE__ );

		return $leaf;
	}

	/**
	 * Get the root elements for JSON-endpoint.
	 *
	 * @since ?
	 *
	 * @access private
	 */
	private function getRootElements( $parent, $now, $root, $array ) {
		if ( is_null( $array ) ) {
			return array();
		}

		foreach ( $array as $key => $value ) {
			if ( is_array( $value ) && ! empty( $value ) ) {
				if ( ! is_numeric( $key ) ) {
					$now = sprintf( '%s%s%s', $parent, self::TAG_SEPARATOR, $key );
					$root[] = $now;
				} else {
					$now = $parent;
				}
				$root = $this->getRootElements( $now, $key, $root, $value );
			}
		}
		$roots = array_filter( array_unique( $root ) );
		do_action( 'themeisle_log_event', Visualizer_Plugin::NAME, sprintf( 'Roots found for %s = ', $this->_url, print_r( $roots, true ) ), 'debug', __FILE__, __LINE__ );
		return $roots;
	}

	/**
	 * Get the JSON for the JSON-endpoint.
	 *
	 * @since ?
	 *
	 * @access private
	 */
	private function getJSON( $url = null ) {
		if ( is_null( $url ) ) {
			$url = $this->_url;
		}
		// allow hooks to use any other args such as method=POST.
		$response = wp_remote_request( $url, apply_filters( 'visualizer_json_args', array( 'method' => 'GET' ), $url ) );
		if ( is_wp_error( $response ) ) {
			do_action( 'themeisle_log_event', Visualizer_Plugin::NAME, sprintf( 'Error while fetching JSON endpoint %s = ', $url, print_r( $response, true ) ), 'error', __FILE__, __LINE__ );
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

		do_action( 'themeisle_log_event', Visualizer_Plugin::NAME, sprintf( 'Series found for %s = ', $this->_url, print_r( $this->_series, true ) ), 'debug', __FILE__, __LINE__ );

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
		$data       = $this->parse();
		foreach ( $data as $line ) {
			$data_row = array();
			foreach ( $line as $header => $value ) {
				if ( in_array( $header, $headers, true ) ) {
					$data_row[] = $value;
				}
			}
			$this->_data[] = $this->_normalizeData( $data_row );
		}

		do_action( 'themeisle_log_event', Visualizer_Plugin::NAME, sprintf( 'Data found for %s = ', $this->_url, print_r( $this->_data, true ) ), 'debug', __FILE__, __LINE__ );

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
