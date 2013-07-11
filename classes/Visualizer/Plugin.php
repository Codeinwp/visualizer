<?php

class Visualizer_Plugin {

	const NAME    = 'visualizer';
	const VERSION = '1.0.0.0';

	// custom post types
	const CPT_VISUALIZER = 'visualizer';

	// custom meta fields
	const CF_CHART_TYPE = 'visualizer-chart-type';

	// custom actions
	const ACTION_GET_CHARTS   = 'visualizer-get-charts';
	const ACTION_CREATE_CHART = 'visualizer-create-chart';
	const ACTION_DELETE_CHART = 'visualizer-delete-chart';

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