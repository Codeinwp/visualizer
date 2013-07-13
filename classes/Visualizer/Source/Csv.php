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
 * Source manager for CSV files.
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
	 * @access private
	 * @var string
	 */
	private $_filename;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param string $filename The path to the file.
	 */
	public function __construct( $filename ) {
		$this->_filename = $filename;
	}

	/**
	 * Fetches series information.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @param resource $handle The file handle resource.
	 */
	private function _fetchSeries( $handle ) {
		// read column titles
		$labels = fgetcsv( $handle );

		// read series types
		$types = fgetcsv( $handle );

		if ( !$labels || !$types ) {
			return false;
		}

		for ( $i = 0, $len = count( $labels ); $i < $len; $i++ ) {
			$this->_series[] = array(
				'label' => $labels[$i],
				'type'  => isset( $types[$i] ) ? $types[$i] : 'string',
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