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
 * The core plugin class.
 *
 * @category Visualizer
 *
 * @since 1.0.0
 */
class Visualizer_Plugin {

	const NAME    = 'visualizer';
	const VERSION = '1.5.2';

	// custom post types
	const CPT_VISUALIZER = 'visualizer';

	// custom meta fields
	const CF_CHART_TYPE   = 'visualizer-chart-type';
	const CF_SOURCE       = 'visualizer-source';
	const CF_SERIES       = 'visualizer-series';
	const CF_DEFAULT_DATA = 'visualizer-default-data';
	const CF_SETTINGS     = 'visualizer-settings';

	// custom actions
	const ACTION_GET_CHARTS   = 'visualizer-get-charts';
	const ACTION_CREATE_CHART = 'visualizer-create-chart';
	const ACTION_EDIT_CHART   = 'visualizer-edit-chart';
	const ACTION_CLONE_CHART  = 'visualizer-clone-chart';
	const ACTION_DELETE_CHART = 'visualizer-delete-chart';
	const ACTION_UPLOAD_DATA  = 'visualizer-upload-data';

	// custom filters
	const FILTER_CHART_WRAPPER_CLASS = 'visualizer-chart-wrapper-class';
	const FILTER_GET_CHART_SERIES    = 'visualizer-get-chart-series';
	const FILTER_GET_CHART_DATA      = 'visualizer-get-chart-data';
	const FILTER_GET_CHART_SETTINGS      = 'visualizer-get-chart-settings';

    // Added by Ash/Upwork
    const PRO_TEASER_URL    = "http://themeisle.com/plugins/visualizer-charts-and-graphs-pro-addon/";
    const PRO_TEASER_TITLE  = "Check PRO version ";
    // Added by Ash/Upwork

	/**
	 * Singletone instance of the plugin.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @var Visualizer_Plugin
	 */
	private static $_instance = null;

	/**
	 * The array of registered modules.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @var array
	 */
	private $_modules = array();

	/**
	 * Private constructor.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 */
	private function __construct() {}

	/**
	 * Private clone method.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 */
	private function __clone() {}

	/**
	 * Returns singletone instance of the plugin.
	 *
	 * @since 1.0.0
	 *
	 * @static
	 * @access public
	 * @return Visualizer_Plugin
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new Visualizer_Plugin();
		}

		return self::$_instance;
	}

	/**
	 * Returns a module if it was registered before. Otherwise NULL.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param string $name The name of the module to return.
	 * @return Visualizer_Module|null Returns a module if it was registered or NULL.
	 */
	public function getModule( $name ) {
		return isset( $this->_modules[$name] ) ? $this->_modules[$name] : null;
	}

	/**
	 * Determines whether the module has been registered or not.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param string $name The name of a module to check.
	 * @return boolean TRUE if the module has been registered. Otherwise FALSE.
	 */
	public function hasModule( $name ) {
		return isset( $this->_modules[$name] );
	}

	/**
	 * Register new module in the plugin.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param string $module The name of the module to use in the plugin.
	 */
	public function setModule( $class ) {
		$this->_modules[$class] = new $class( $this );
	}

	/**
	 * Returns chart types.
	 *
	 * @since 1.0.0
	 *
	 * @static
	 * @access public
	 * @return array
	 */
	public static function getChartTypes() {
		return array( 'line', 'area', 'bar', 'column', 'pie', 'geo', 'scatter', 'candlestick', 'gauge' );
	}

}