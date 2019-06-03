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

	const NAME = 'visualizer';
	const VERSION = '3.2.1';

	// custom post types
	const CPT_VISUALIZER = 'visualizer';

	// custom meta fields
	const CF_CHART_TYPE = 'visualizer-chart-type';
	const CF_SOURCE = 'visualizer-source';
	const CF_SERIES = 'visualizer-series';
	const CF_DEFAULT_DATA = 'visualizer-default-data';
	const CF_SETTINGS = 'visualizer-settings';

	const CF_SOURCE_FILTER = 'visualizer-source-filter';
	const CF_FILTER_CONFIG = 'visualizer-filter-config';

	// custom actions
	const ACTION_GET_CHARTS = 'visualizer-get-charts';
	const ACTION_CREATE_CHART = 'visualizer-create-chart';
	const ACTION_EDIT_CHART = 'visualizer-edit-chart';
	const ACTION_CLONE_CHART = 'visualizer-clone-chart';
	const ACTION_DELETE_CHART = 'visualizer-delete-chart';
	const ACTION_UPLOAD_DATA = 'visualizer-upload-data';
	const ACTION_EXPORT_DATA = 'visualizer-export-data';

	/**
	 *Action used for fetching specific users/roles for permissions.
	 */
	const ACTION_FETCH_PERMISSIONS_DATA = 'visualizer-fetch-permissions-data';

	/**
	 *Action used for fetching db import data.
	 */
	const ACTION_FETCH_DB_DATA = 'visualizer-fetch-db-data';
	const ACTION_SAVE_DB_QUERY = 'visualizer-save-db-query';

	const ACTION_JSON_GET_ROOTS = 'visualizer-json-get-roots';
	const ACTION_JSON_GET_DATA = 'visualizer-json-get-data';
	const ACTION_JSON_SET_DATA = 'visualizer-json-set-data';
	const ACTION_JSON_SET_SCHEDULE = 'visualizer-json-set-schedule';
	const CF_JSON_URL = 'visualizer-json-url';
	const CF_JSON_ROOT = 'visualizer-json-root';
	const CF_JSON_SCHEDULE = 'visualizer-json-schedule';
	const CF_JSON_PAGING = 'visualizer-json-paging';

	const ACTION_SAVE_FILTER_QUERY = 'visualizer-save-filter-query';

	// custom filters
	const FILTER_CHART_WRAPPER_CLASS = 'visualizer-chart-wrapper-class';
	const FILTER_GET_CHART_SERIES = 'visualizer-get-chart-series';
	const FILTER_GET_CHART_DATA = 'visualizer-get-chart-data';
	const FILTER_GET_CHART_SETTINGS = 'visualizer-get-chart-settings';
	const FILTER_UNDO_REVISIONS = 'visualizer-undo-revisions';
	const FILTER_HANDLE_REVISIONS = 'visualizer-handle-revisions';
	const FILTER_GET_CHART_DATA_AS = 'visualizer-get-chart-data-as';

	const CF_DB_SCHEDULE = 'visualizer-db-schedule';
	const CF_DB_QUERY = 'visualizer-db-query';

	const CF_CHART_URL = 'visualizer-chart-url';
	const CF_CHART_SCHEDULE = 'visualizer-chart-schedule';
	// Added by Ash/Upwork
	const PRO_TEASER_URL = 'http://themeisle.com/plugins/visualizer-charts-and-graphs-pro-addon/';
	const PRO_TEASER_TITLE = 'Check PRO version ';
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
	private function __construct() {
		if ( VISUALIZER_DEBUG ) {
			add_action( 'themeisle_log_event', array( $this, 'themeisle_log_event_debug' ), 10, 5 );
		}
	}

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
	 * Returns chart types.
	 *
	 * @since 1.0.0
	 *
	 * @static
	 * @access public
	 * @return array
	 */
	public static function getChartTypes() {
		$array = array_keys( Visualizer_Module_Admin::_getChartTypesLocalized() );

		return $array;
	}

	/**
	 * Returns a module if it was registered before. Otherwise NULL.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 *
	 * @param string $name The name of the module to return.
	 *
	 * @return Visualizer_Module|null Returns a module if it was registered or NULL.
	 */
	public function getModule( $name ) {
		return isset( $this->_modules[ $name ] ) ? $this->_modules[ $name ] : null;
	}

	/**
	 * Determines whether the module has been registered or not.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 *
	 * @param string $name The name of a module to check.
	 *
	 * @return boolean TRUE if the module has been registered. Otherwise FALSE.
	 */
	public function hasModule( $name ) {
		return isset( $this->_modules[ $name ] );
	}

	/**
	 * Register new module in the plugin.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 *
	 * @param string $class The name of the module to use in the plugin.
	 */
	public function setModule( $class ) {
		$this->_modules[ $class ] = new $class( $this );
	}

	/**
	 * Private clone method.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 */
	private function __clone() {
	}

	/**
	 * For local testing, overrides the 'themeisle_log_event' hook and redirects to error.log.
	 */
	final function themeisle_log_event_debug( $name, $message, $type, $file, $line ) {
		if ( Visualizer_Plugin::NAME !== $name ) {
			return;
		}
		error_log( sprintf( '%s (%s): %s in %s:%s', $name, $type, $message, $file, $line ) );
	}

}
