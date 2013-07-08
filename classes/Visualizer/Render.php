<?php

abstract class Visualizer_Render {

	/** @var array */
	protected $_data;

	public function __construct( $data = array() ) {
		$this->_data = $data;
	}

	public function __get( $name ) {
		return array_key_exists( $name, $this->_data ) ? $this->_data[$name] : null;
	}

	public function __isset( $name ) {
		return array_key_exists( $name, $this->_data );
	}

	public function __set( $name, $value ) {
		$this->_data[$name] = $value;
	}

	public function __unset( $name ) {
		unset( $this->_data[$name] );
	}

	protected abstract function _toHTML();

	public function toHtml() {
		ob_start();
		$this->_toHTML();
		return ob_get_clean();
	}

	public function __toString() {
		return $this->toHtml();
	}

	public function render() {
		$this->_toHTML();
	}

}