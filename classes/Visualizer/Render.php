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
 * Abstract render class implements all routine stuff required for template
 * rendering.
 *
 * @category Visualizer
 * @package Render
 *
 * @since 1.0.0
 * @abstract
 */
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
		return array_key_exists( $name, $this->_data ) ? $this->_data[ $name ] : null;
	}

	/**
	 * Checks whether the render has specific property or not.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param string $name The key name.
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
	 * @param mixed  $value The value of a property.
	 */
	public function __set( $name, $value ) {
		$this->_data[ $name ] = $value;
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
		unset( $this->_data[ $name ] );
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

	/**
	 * Checks if the request is from Gutenberg.
	 *
	 * This happens when the chart is being added to a gutenberg block by inserting a visualizer block.
	 *
	 * @since ?
	 *
	 * @access protected
	 * @return bool
	 */
	protected function is_request_from_gutenberg() {
		global $post;
		require_once ABSPATH . 'wp-admin/includes/post.php';
		if ( $post && function_exists( 'use_block_editor_for_post' ) && use_block_editor_for_post( $post ) ) {
			do_action( 'themeisle_log_event', Visualizer_Plugin::NAME, sprintf( 'This request appears to be from Gutenberg block. Ignoring for chart %d', $post->ID ), 'debug', __FILE__, __LINE__ );
			return true;
		}
		return false;
	}

	/**
	 * Gets the type of chart that is being rendered.
	 *
	 * This is useful if some type-specific functionality needs to be added.
	 *
	 * @since ?
	 *
	 * @access protected
	 * @return string
	 */
	protected function get_chart_type( $with_library = false ) {
		$lib_type = str_replace( 'Visualizer_Render_Sidebar_Type_', '', get_class( $this ) );
		if ( $with_library ) {
			return $lib_type;
		}

		$array = explode( '_', $lib_type );
		return end( $array );
	}

	/**
	 * Determines if the type of chart can have a particular action.
	 *
	 * @since ?
	 *
	 * @access protected
	 * @return bool
	 */
	protected function can_chart_have_action( $action, $chart_id = null ) {
		$type = null;
		if ( ! $chart_id ) {
			$type = $this->get_chart_type( false );
		} else {
			$type = get_post_meta( $chart_id, Visualizer_Plugin::CF_CHART_TYPE, true );
			$type = ucwords( $type );
		}

		switch ( $action ) {
			case 'image':
				return ! in_array( $type, array( 'Gauge', 'Tabular', 'DataTable', 'Table' ), true );
		}

		return true;
	}


}
