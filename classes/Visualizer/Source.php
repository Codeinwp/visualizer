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
 * The abstract class for source managers.
 *
 * @category Visualizer
 * @package Source
 *
 * @since 1.0.0
 * @abstract
 */
abstract class Visualizer_Source {

	/**
	 * The array of data.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @var array
	 */
	protected $_data = array();

	/**
	 * The array of series.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @var array
	 */
	protected $_series = array();

	/**
	 * Returns source name.
	 *
	 * @since 1.0.0
	 *
	 * @abstract
	 * @access public
	 * @return string The name of source.
	 */
	public abstract function getSourceName();

	/**
	 * Fetches information from source, parses it and builds series and data arrays.
	 *
	 * @since 1.0.0
	 *
	 * @abstract
	 * @access public
	 */
	public abstract function fetch();

	/**
	 * Returns series parsed from source.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return array The array of series.
	 */
	public function getSeries() {
		return $this->_series;
	}

	/**
	 * Returns data parsed from source.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return string The serialized array of data.
	 */
	public function getData() {
		return serialize( $this->_data );
	}

	/**
	 * Returns raw data array.
	 *
	 * @since 1.1.0
	 *
	 * @access public
	 * @return array
	 */
	public function getRawData() {
		return $this->_data;
	}

	/**
	 * Normalizes values according to series' type.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @param array $data The row of data.
	 * @return array Normalized row of data.
	 */
	protected function _normalizeData( $data ) {
		// normalize values
		//print_r($data);
		foreach ( $this->_series as $i => $series ) {
			// if no value exists for the seires, then add null
			if ( !isset( $data[$i] ) ) {
				$data[$i] = null;
			}

			if ( is_null( $data[$i] ) && !is_numeric($data[$i])) {
				continue;
			}

			switch ( $series['type'] ) {
				case 'number':
					$data[$i] = (  is_numeric($data[$i]) ) ? floatval( $data[$i] ) : null;
					break;
				case 'boolean':
					$data[$i] = !empty( $data[$i] ) ? filter_validate( $data[$i], FILTER_VALIDATE_BOOLEAN ) : null;
					break;
				case 'timeofday':
					$date = new DateTime( '1984-03-16T' . $data[$i] );
					if ( $date ) {
						$data[$i] = array(
							intval( $date->format( 'H' ) ),
							intval( $date->format( 'i' ) ),
							intval( $date->format( 's' ) ),
							0,
						);
					}
					break;
			}
		}
		return $data;
	}

	/**
	 * Validates series tyeps.
	 *
	 * @since 1.0.1
	 *
	 * @static
	 * @access protected
	 * @param array $types The icoming series types.
	 * @return boolean TRUE if sereis types are valid, otherwise FALSE.
	 */
	protected static function _validateTypes( $types ) {
		$allowed_types = array( 'string', 'number', 'boolean', 'date', 'datetime', 'timeofday' );
		foreach ( $types as $type ) {
			if ( !in_array( $type, $allowed_types ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Re populates series if the source is dynamic.
	 *
	 * @since 1.1.0
	 *
	 * @access public
	 * @param array $series The actual array of series.
	 * @param int $chart_id The chart id.
	 * @return array The re populated array of series or old one.
	 */
	public function repopulateSeries( $series, $chart_id ) {
		return $series;
	}

	/**
	 * Re populates data if the source is dynamic.
	 *
	 * @since 1.1.0
	 *
	 * @access public
	 * @param array $data The actual array of data.
	 * @param int $chart_id The chart id.
	 * @return array The re populated array of data or old one.
	 */
	public function repopulateData( $data, $chart_id ) {
		return $data;
	}

}