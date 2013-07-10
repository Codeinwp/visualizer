<?php

abstract class Visualizer_Render {

	/**
	 * The storage of all data associated with this render.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @var array
	 */
	protected $_data;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param array $data The data what has to be associated with this render.
	 */
	public function __construct( $data = array() ) {
		$this->_data = $data;
	}

	/**
	 * Returns property associated with the render.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param string $name The name of a property.
	 * @return mixed Returns mixed value of a property or NULL if a property doesn't exist.
	 */
	public function __get( $name ) {
		return array_key_exists( $name, $this->_data ) ? $this->_data[$name] : null;
	}

	/**
	 * Checks whether the render has specific property or not.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param string $name
	 * @return boolean TRUE if the property exists, otherwise FALSE.
	 */
	public function __isset( $name ) {
		return array_key_exists( $name, $this->_data );
	}

	/**
	 * Associates the render with specific property.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param string $name The name of a property to associate.
	 * @param mixed $value The value of a property.
	 */
	public function __set( $name, $value ) {
		$this->_data[$name] = $value;
	}

	/**
	 * Unassociates specific property from the render.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param string $name The name of the property to unassociate.
	 */
	public function __unset( $name ) {
		unset( $this->_data[$name] );
	}

	/**
	 * Renders template.
	 *
	 * @since 1.0.0
	 *
	 * @abstract
	 * @access protected
	 */
	protected abstract function _toHTML();

	/**
	 * Builds template and return it as string.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return string
	 */
	public function toHtml() {
		ob_start();
		$this->_toHTML();
		return ob_get_clean();
	}

	/**
	 * Returns built template as string.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return type
	 */
	public function __toString() {
		return $this->toHtml();
	}

	/**
	 * Renders the template.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function render() {
		$this->_toHTML();
	}

}