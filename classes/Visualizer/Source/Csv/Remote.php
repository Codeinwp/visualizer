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
 * Source manager for remote CSV files.
 *
 * @category Visualizer
 * @package Source
 *
 * @since 1.1.0
 */
class Visualizer_Source_Csv_Remote extends Visualizer_Source_Csv {

	/**
	 * Temporary file name used when allow_url_fopen option is disabled.
	 *
	 * @since 1.4.2
	 *
	 * @access private
	 * @var string
	 */
	private $_tmpfile = false;

	/**
	 * Returns data parsed from source.
	 *
	 * @since 1.1.0
	 *
	 * @access public
	 * @return string The serialized array of data.
	 */
	public function getData() {
		return serialize( array(
			'source' => $this->_filename,
			'data'   => $this->_data,
		) );
	}

	/**
	 * Re populates data and series.
	 *
	 * @since 1.1.0
	 *
	 * @access private
	 * @param int $chart_id The chart id.
	 * @return boolean TRUE on success, otherwise FALSE.
	 */
	private function _repopulate( $chart_id ) {
		// if it has been already populated, then just return true
		if ( !empty( $this->_data ) && !empty( $this->_series ) ) {
			return true;
		}

		// if filename is empty, extract it from chart content
		if ( empty( $this->_filename ) ) {
			$chart = get_post( $chart_id );
			$data = unserialize( $chart->post_content );
			if ( !isset( $data['source'] ) ) {
				return false;
			}

			$this->_filename = $data['source'];
		}

		// populate series and data information
		return $this->fetch();
	}

	/**
	 * Re populates data.
	 *
	 * @since 1.1.0
	 *
	 * @access public
	 * @param array $data The actual array of data.
	 * @param int $chart_id The chart id.
	 * @return array The re populated array of data or old one.
	 */
	public function repopulateData( $data, $chart_id ) {
		return $this->_repopulate( $chart_id ) ? $this->_data : $data;
	}

	/**
	 * Re populates series.
	 *
	 * @since 1.1.0
	 *
	 * @access public
	 * @param array $series The actual array of series.
	 * @param int $chart_id The chart id.
	 * @return array The re populated array of series or old one.
	 */
	public function repopulateSeries( $series, $chart_id ) {
		return $this->_repopulate( $chart_id ) ? $this->_series : $series;
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

	/**
	 * Returns file handle to fetch data from.
	 *
	 * @since 1.4.2
	 *
	 * @access protected
	 * @staticvar boolean $allow_url_fopen Determines whether or not allow_url_fopen option is enabled.
	 * @param string $filename Optional file name to get handle. If omitted, $_filename is used.
	 * @return resource File handle resource on success, otherwise FALSE.
	 */
	protected function _get_file_handle( $filename = false ) {
		static $allow_url_fopen = null;

		if ( $this->_tmpfile && is_readable( $this->_tmpfile ) ) {
			return parent::_get_file_handle( $this->_tmpfile );
		}

		if ( is_null( $allow_url_fopen ) ) {
			$allow_url_fopen = filter_var( ini_get( 'allow_url_fopen' ), FILTER_VALIDATE_BOOLEAN );
		}

		$scheme = parse_url( $this->_filename, PHP_URL_SCHEME );
		if ( $allow_url_fopen && in_array( $scheme, stream_get_wrappers() ) ) {
			return parent::_get_file_handle( $filename );
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';

		$this->_tmpfile = download_url( $this->_filename );

		return !is_wp_error( $this->_tmpfile ) ? parent::_get_file_handle( $this->_tmpfile ) : false;
	}

}