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
 * General module what setups all required environment.
 *
 * @category Visualizer
 * @package Module
 *
 * @since 1.0.0
 */
class Visualizer_Module_Setup extends Visualizer_Module {

	const NAME = __CLASS__;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param Visualizer_Plugin $plugin The instance of the plugin.
	 */
	public function __construct( Visualizer_Plugin $plugin ) {
		parent::__construct( $plugin );

		$this->_addAction( 'init', 'setupCustomPostTypes' );
		$this->_addAction( 'plugins_loaded', 'loadTextDomain' );
	}

	/**
	 * Registers custom post type for charts.
	 *
	 * @since 1.0.0
	 * @uses register_post_type() To register custom post type for charts.
	 *
	 * @access public
	 */
	public function setupCustomPostTypes() {
		register_post_type( Visualizer_Plugin::CPT_VISUALIZER, array(
			'label'  => 'Visualizer Charts',
			'public' => false,
		) );
	}

	/**
	 * Loads plugin text domain translations.
	 *
	 * @since 1.0.0
	 * @uses load_plugin_textdomain() To load translations for the plugin.
	 *
	 * @access public
	 */
	public function loadTextDomain() {
		load_plugin_textdomain( Visualizer_Plugin::NAME, false, dirname( plugin_basename( VISUALIZER_BASEFILE ) ) . '/languages/' );
	}

}
