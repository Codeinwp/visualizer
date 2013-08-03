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
 * Source manager for local CSV files.
 *
 * @category Visualizer
 * @package Source
 *
 * @since 1.0.0
 */
class Visualizer_Source_Csv extends Visualizer_Source {

	/**
	 * The path to the file with data.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @var string
	 */
	protected $_filename;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param string $filename The path to the file.
	 */
	public function __construct( $filename = null ) {
		$this->_filename = trim( $filename );
	}

	/**
	 * Fetches series information.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @param resource $handle The file handle resource.
	 */
	private function _fetchSeries( &$handle ) {
		// read column titles
		$labels = fgetcsv( $handle );

		// read series types
		$types = fgetcsv( $handle );

		if ( !$labels || !$types ) {
			return false;
		}

		// if no types were setup, re read labels and empty types array
		if ( !self::_validateTypes( $types ) ) {
			// re open the file
			fclose( $handle );
			$handle = fopen( $this->_filename, 'rb' );

			// re read the labels and empty types array
			$labels = fgetcsv( $handle );
			$types = array();
		}

		for ( $i = 0, $len = count( $labels ); $i < $len; $i++ ) {
			$default_type = $i == 0 ? 'string' : 'number';
			$this->_series[] = array(
				'label' => $labels[$i],
				'type'  => isset( $types[$i] ) ? $types[$i] : $default_type,
			);
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
		// if filename is empty return false
		if ( empty( $this->_filename ) ) {
			return false;
		}

		// set line endings auto detect mode
		@ini_set( 'auto_detect_line_endings', true );

		// read file and fill arrays
		$handle = fopen( $this->_filename, 'rb' );
		if ( $handle ) {
			// fetch series
			if ( !$this->_fetchSeries( $handle ) ) {
				return false;
			}

			// fetch data
			while ( ( $data = fgetcsv( $handle ) ) !== false ) {
				$this->_data[] = $this->_normalizeData( $data );
			}

			// close file handle
			fclose( $handle );
		}

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