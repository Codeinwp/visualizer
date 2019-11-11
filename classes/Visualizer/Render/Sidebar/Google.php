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

	/**
	 * The constructor.
	 */
	public function __construct( $data = array() ) {
		$this->_library = 'google';
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

	/**
	 * Loads the assets.
	 */
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

	/**
	 * Enqueue assets.
	 */
	public static function enqueue_assets( $deps = array() ) {
		wp_enqueue_script( 'visualizer-google-jsapi-new', '//www.gstatic.com/charts/loader.js', array(), null, true );
		wp_enqueue_script( 'visualizer-google-jsapi-old', '//www.google.com/jsapi', array( 'visualizer-google-jsapi-new' ), null, true );
		wp_enqueue_script( 'visualizer-render-google-lib', VISUALIZER_ABSURL . 'js/render-google.js', array_merge( $deps, array( 'visualizer-google-jsapi-old' ) ), Visualizer_Plugin::VERSION, true );
		return 'visualizer-render-google-lib';
	}

	/**
	 * Renders the role field.
	 *
	 * @since 3.4.0
	 *
	 * @access protected
	 */
	protected function _renderRoleField( $index ) {
		self::_renderSelectItem(
			esc_html__( 'Special Role', 'visualizer' ),
			'series[' . $index . '][role]',
			isset( $this->series[ $index ]['role'] ) ? $this->series[ $index ]['role'] : '',
			array(
				''  => esc_html__( 'Default (Data)', 'visualizer' ),
				'annotation'  => esc_html__( 'Annotation', 'visualizer' ),
				'annotationText' => esc_html__( 'Annotation Text', 'visualizer' ),
				'certainty' => esc_html__( 'Certainty', 'visualizer' ),
				'emphasis' => esc_html__( 'Emphasis', 'visualizer' ),
				'scope' => esc_html__( 'Scope', 'visualizer' ),
				'style' => esc_html__( 'Style', 'visualizer' ),
				'tooltip' => esc_html__( 'Tooltip', 'visualizer' ),
			),
			sprintf( esc_html__( 'Determines whether the series has to be used for a special role as mentioned in %1$shere%2$s. You can view a few examples %3$shere%4$s.', 'visualizer' ), '<a href="https://developers.google.com/chart/interactive/docs/roles#what-roles-are-available" target="_blank">', '</a>', '<a href="https://docs.themeisle.com/article/1160-roles-for-series-visualizer" target="_blank">', '</a>' )
		);
	}
}
