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
	 * The array of allowed types.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @var array
	 */
	protected static $allowed_types = array( 'string', 'number', 'boolean', 'date', 'datetime', 'timeofday' );
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
	 * The error message.
	 *
	 * @access protected
	 * @var string
	 */
	protected $_error;

	/**
	 * Return allowed types
	 *
	 * @since 1.0.1
	 *
	 * @static
	 * @access public
	 * @return array the allowed types
	 */
	public static function getAllowedTypes() {
		return self::$allowed_types;
	}

	/**
	 * Validates series tyeps.
	 *
	 * @since 1.0.1
	 *
	 * @static
	 * @access protected
	 *
	 * @param array $types The icoming series types.
	 *
	 * @return boolean TRUE if sereis types are valid, otherwise FALSE.
	 */
	protected static function _validateTypes( $types ) {
		foreach ( $types as $type ) {
			if ( ! in_array( $type, self::$allowed_types, true ) ) {
				return false;
			}
		}

		return true;
	}

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
	 * Re populates series if the source is dynamic.
	 *
	 * @since 1.1.0
	 *
	 * @access public
	 *
	 * @param array $series The actual array of series.
	 * @param int   $chart_id The chart id.
	 *
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
	 *
	 * @param array $data The actual array of data.
	 * @param int   $chart_id The chart id.
	 *
	 * @return array The re populated array of data or old one.
	 */
	public function repopulateData( $data, $chart_id ) {
		return $data;
	}

	/**
	 * Normalizes values according to series' type.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 *
	 * @param array $data The row of data.
	 *
	 * @return array Normalized row of data.
	 */
	protected function _normalizeData( $data ) {
		// normalize values
		foreach ( $this->_series as $i => $series ) {
			// if no value exists for the seires, then add null
			if ( ! isset( $data[ $i ] ) ) {
				$data[ $i ] = null;
			}
			if ( is_null( $data[ $i ] ) ) {
				continue;
			}
			switch ( $series['type'] ) {
				case 'number':
					$data[ $i ] = ( is_numeric( $data[ $i ] ) ) ? floatval( $data[ $i ] ) : ( is_numeric( str_replace( ',', '', $data[ $i ] ) ) ? floatval( str_replace( ',', '', $data[ $i ] ) ) : null );
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
				case 'datetime':
					// let's check if the date is a Unix epoch
					$value = DateTime::createFromFormat( 'U', $data[ $i ] );
					if ( $value !== false && ! is_wp_error( $value ) ) {
						$data[ $i ] = $value->format( 'Y-m-d H:i:s' );
					}
					break;
				case 'string':
					$data[ $i ] = $this->toUTF8( $data[ $i ] );
					break;
			}
		}

		return apply_filters( 'visualizer_format_data', $data, $this->_series );
	}


	/**
	 * Converts values to UTF8, if required.
	 *
	 * @access protected
	 *
	 * @param string $datum The data to convert.
	 *
	 * @return string The converted data.
	 */
	protected final function toUTF8( $datum ) {
		if ( ! function_exists( 'mb_detect_encoding' ) || mb_detect_encoding( $datum ) !== 'ASCII' ) {
			$datum = \ForceUTF8\Encoding::toUTF8( $datum );
		}
		return $datum;
	}

	/**
	 * Determines the formats of date/time columns.
	 *
	 * @access public
	 *
	 * @param array $series The actual array of series.
	 * @param array $data The actual array of data.
	 *
	 * @return array
	 */
	public static final function get_date_formats_if_exists( $series, $data ) {
		$date_formats = array();
		$types = array();
		$index = 0;
		foreach ( $series as $column ) {
			if ( in_array( $column['type'], array( 'date', 'datetime', 'timeofday' ), true ) ) {
				$types[] = array( 'index' => $index, 'type' => $column['type'] );
			}
			$index++;
		}

		if ( ! $types ) {
			return $date_formats;
		}

		$random = $data;
		// let's randomly pick 5 data points instead of cycling through the entire data set.
		if ( count( $data ) > 5 ) {
			$random = array();
			for ( $x = 0; $x < 5; $x++ ) {
				$random[] = $data[ rand( 0, count( $data ) - 1 ) ];
			}
		}

		foreach ( $types as $type ) {
			$formats = array();
			foreach ( $random as $datum ) {
				$f = self::determine_date_format( $datum[ $type['index'] ], $type['type'] );
				if ( $f ) {
					$formats[] = $f;
				}
			}
			// if there are multiple formats, use the most frequent format.
			$formats = array_filter( $formats );
			if ( $formats ) {
				$formats = array_count_values( $formats );
				arsort( $formats );
				$formats = array_keys( $formats );
				$final_format = reset( $formats );
				// we have determined the PHP format; now we have to change this into the JS format where m = MM, d = DD etc.
				$date_formats[] = array( 'index' => $type['index'], 'format' => str_replace( array( 'Y', 'm', 'd', 'H', 'i', 's' ), array( 'YYYY', 'MM', 'DD', 'HH', 'mm', 'ss' ), $final_format ) );
			}
		}
		return $date_formats;
	}

	/**
	 * Determines the date/time format of the given string.
	 *
	 * @access private
	 *
	 * @param string $value The string.
	 * @param string $type 'date', 'timeofday' or 'datetime'.
	 *
	 * @return string|null
	 */
	private static final function determine_date_format( $value, $type ) {
		if ( version_compare( phpversion(), '5.3.0', '<' ) ) {
			return null;
		}

		$formats = array(
			'Y/m/d',
			'Y-m-d',
			'm/d/Y',
			'm-d-Y',
			'd-m-Y',
			'd/m/Y',
		);

		switch ( $type ) {
			case 'datetime':
				$formats = array_merge(
					$formats, array(
						'U',
						'Y/m/d H:i:s',
						'Y-m-d H:i:s',
						'm/d/Y H:i:s',
						'm-d-Y H:i:s',
					)
				);
				break;
			case 'timeofday':
				$formats = array_merge(
					$formats, array(
						'H:i:s',
						'H:i',
					)
				);
				break;
		}

		$formats = apply_filters( 'visualizer_date_formats', $formats, $type );

		foreach ( $formats as $format ) {
			$return = DateTime::createFromFormat( $format, $value );
			if ( $return !== false && ! is_wp_error( $return ) ) {
				return $format;
			}
		}
		// invalid format
		return null;
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


}
