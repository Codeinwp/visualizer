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
 * Base class for sidebar settigns of graph based charts.
 *
 * @category Visualizer
 * @package Render
 * @subpackage Sidebar
 *
 * @since 1.0.0
 * @abstract
 */
abstract class Visualizer_Render_Sidebar_Google extends Visualizer_Render_Sidebar {

	public function __construct( $data = array() ) {
		$this->_library	= 'google';
		parent::__construct( $data );
	}

	/**
	 * Registers additional hooks.
	 *
	 * @access protected
	 */
	protected function hooks() {
		if ( $this->_library === 'google' ) {
			add_filter( 'visualizer_assets_render', array( $this, 'load_google_assets' ), 10, 2 );
		}
	}

	function load_google_assets( $deps, $is_frontend ) {
		wp_register_script( 'google-jsapi-new', '//www.gstatic.com/charts/loader.js', array(), null, true );
		wp_register_script( 'google-jsapi-old', '//www.google.com/jsapi', array( 'google-jsapi-new' ), null, true );
		wp_register_script(
			'visualizer-render-google-lib',
			VISUALIZER_ABSURL . 'js/render-google.js',
			array(
				'google-jsapi-old',
			),
			Visualizer_Plugin::VERSION,
			true
		);

		return array_merge( 
			$deps,
			array( 'visualizer-render-google-lib' )
		);

	}


}