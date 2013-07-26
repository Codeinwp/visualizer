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
 * Sources module class.
 *
 * @category Visualizer
 * @package Module
 *
 * @since 1.1.0
 */
class Visualizer_Module_Sources extends Visualizer_Module {

	const NAME = __CLASS__;

	/**
	 * The array of fetched sources.
	 *
	 * @since 1.1.0
	 *
	 * @access private
	 * @var array
	 */
	private $_sources = array();

	/**
	 * Constructor.
	 *
	 * @since 1.1.0
	 *
	 * @access public
	 * @param Visualizer_Plugin $plugin The instance of the plugin.
	 */
	public function __construct( Visualizer_Plugin $plugin ) {
		parent::__construct( $plugin );

		$this->_addFilter( Visualizer_Plugin::FILTER_GET_CHART_SERIES, 'filterChartSeries', 1, 2 );
		$this->_addFilter( Visualizer_Plugin::FILTER_GET_CHART_DATA, 'filterChartData', 1, 2 );
	}

	/**
	 * Returns appropriate source object for a chart.
	 *
	 * @since 1.1.0
	 *
	 * @access private
	 * @param int $chart_id The chart id.
	 * @return Visualizer_Source The source object if source exists, otherwise FALSE.
	 */
	private function _getSource( $chart_id ) {
		if ( !isset( $this->_sources[$chart_id] ) ) {
			$class = get_post_meta( $chart_id, Visualizer_Plugin::CF_SOURCE, true );
			if ( !class_exists( $class, true ) ) {
				return false;
			}

			$this->_sources[$chart_id] = new $class();
		}

		return $this->_sources[$chart_id];
	}

	/**
	 * Filters chart sereis.
	 *
	 * @since 1.1.0
	 *
	 * @access public
	 * @param array $series The array of chart series.
	 * @param int $chart_id The chart id.
	 * @return array The array of filtered series.
	 */
	public function filterChartSeries( $series, $chart_id ) {
		$source = $this->_getSource( $chart_id );
		if ( !$source ) {
			return $series;
		}

		return $source->repopulateSeries( $series, $chart_id );
	}

	/**
	 * Filters chart data.
	 *
	 * @since 1.1.0
	 *
	 * @access public
	 * @param array $data The array of chart data.
	 * @param int $chart_id The chart id.
	 * @return array The array of filtered data.
	 */
	public function filterChartData( $data, $chart_id ) {
		$source = $this->_getSource( $chart_id );
		if ( !$source ) {
			return $data;
		}

		return $source->repopulateData( $data, $chart_id );
	}

}