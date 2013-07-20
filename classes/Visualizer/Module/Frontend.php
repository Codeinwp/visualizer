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
 * Frontend module class.
 *
 * @category Visualizer
 * @package Module
 *
 * @since 1.0.0
 */
class Visualizer_Module_Frontend extends Visualizer_Module {

	const NAME = __CLASS__;

	/**
	 * The array of charts to render.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @var array
	 */
	private $_charts = array();

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @uses add_filter() To add "do_shortcode" hook for "widget_text" and "term_description" filters.
	 *
	 * @access public
	 * @param Visualizer_Plugin $plugin The instance of the plugin.
	 */
	public function __construct( Visualizer_Plugin $plugin ) {
		parent::__construct( $plugin );

		$this->_addAction( 'wp_enqueue_scripts', 'enqueueScripts' );
		$this->_addShortcode( 'visualizer', 'renderChart' );

		add_filter( 'widget_text', 'do_shortcode' );
		add_filter( 'term_description', 'do_shortcode' );
	}

	/**
	 * Registers scripts for charts rendering.
	 *
	 * @since 1.0.0
	 * @uses wp_register_script To register javascript file.
	 *
	 * @access public
	 */
	public function enqueueScripts() {
		wp_register_script( 'google-jsapi', '//www.google.com/jsapi', array(), null, true );
		wp_register_script( 'visualizer-render', VISUALIZER_ABSURL . 'js/render.js', array( 'google-jsapi', 'jquery' ), Visualizer_Plugin::VERSION, true );
	}

	/**
	 * Returns placeholder for chart according to visualizer shortcode attributes.
	 *
	 * @since 1.0.0
	 * @uses shortcode_atts() To parse income shortocdes.
	 *
	 * @access public
	 * @param array $atts The array of shortcode attributes.
	 */
	public function renderChart( $atts ) {
		$atts = shortcode_atts( array( 'id' => false ), $atts );

		// if empty id or chart does not exists, then return empty string
		if ( !$atts['id'] || !( $chart = get_post( $atts['id'] ) ) || $chart->post_type != Visualizer_Plugin::CPT_VISUALIZER ) {
			return '';
		}

		$id = 'visualizer-' . $atts['id'];

		// faetch and update settings
		$settings = get_post_meta( $chart->ID, Visualizer_Plugin::CF_SETTINGS, true );
		if ( empty( $settings['height'] ) ) {
			$settings['height'] = '400';
		}

		// add chart to the array
		$this->_charts[$id] = array(
			'type'     => get_post_meta( $chart->ID, Visualizer_Plugin::CF_CHART_TYPE, true ),
			'series'   => get_post_meta( $chart->ID, Visualizer_Plugin::CF_SERIES, true ),
			'settings' => $settings,
			'data'     => unserialize( $chart->post_content ),
		);

		// enqueue visualizer render and update render localizations
		wp_enqueue_script( 'visualizer-render' );
		wp_localize_script( 'visualizer-render', 'visualizer', array( 'charts' => $this->_charts ) );

		// return placeholder div
		return '<div id="' . $id . '"></div>';
	}

}