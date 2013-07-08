<?php

class Visualizer_Plugin {

	const NAME    = 'visualizer';
	const VERSION = '1.0.0.0';
	const CPT     = 'visualizer';

	private static $_instance = null;

	private $_modules = array();

	private function __construct() {}

	private function __clone() {}

	/** @return Visualizer_Plugin */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new Visualizer_Plugin();
		}

		return self::$_instance;
	}

	public function getModule( $name ) {
		return isset( $this->_modules[$name] ) ? $this->_modules[$name] : null;
	}

	public function hasModule( $name ) {
		return isset( $this->_modules[$name] );
	}

	public function setModule( $class ) {
		$this->_modules[$class] = new $class( $this );
	}

}