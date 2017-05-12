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

		// add do_shortocde hook for widget_text filter
		if ( ! has_filter( 'widget_text', 'do_shortcode' ) ) {
			add_filter( 'widget_text', 'do_shortcode' );
		}

		// add do_shortcode hook for term_description filter
		if ( ! has_filter( 'term_description', 'do_shortcode' ) ) {
			add_filter( 'term_description', 'do_shortcode' );
		}
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
		wp_register_script( 'visualizer-google-jsapi-new', '//www.gstatic.com/charts/loader.js', array(), null, true );
		wp_register_script( 'visualizer-google-jsapi-old', '//www.google.com/jsapi', array( 'visualizer-google-jsapi-new' ), null, true );
		wp_register_script( 'visualizer-render', VISUALIZER_ABSURL . 'js/render.js', array( 'visualizer-google-jsapi-old', 'jquery' ), Visualizer_Plugin::VERSION, true );
	}

	/**
	 * Returns placeholder for chart according to visualizer shortcode attributes.
	 *
	 * @since 1.0.0
	 * @uses shortcode_atts() To parse income shortocdes.
	 * @uses apply_filters() To filter chart's data and series arrays.
	 * @uses get_post_meta() To fetch chart's meta information.
	 * @uses wp_enqueue_script() To enqueue charts render script.
	 * @uses wp_localize_script() To add chart data to the page inline script.
	 *
	 * @access public
	 * @param array $atts The array of shortcode attributes.
	 */
	public function renderChart( $atts ) {
		$atts = shortcode_atts( array(
			'id'     => false, // chart id
			'class'  => false, // chart class
			'series' => false, // series filter hook
			'data'   => false, // data filter hook
			'settings'   => false, // data filter hook
		), $atts );

		// if empty id or chart does not exists, then return empty string
		if ( ! $atts['id'] || ! ( $chart = get_post( $atts['id'] ) ) || $chart->post_type != Visualizer_Plugin::CPT_VISUALIZER ) {
			return '';
		}

		$id = 'visualizer-' . $atts['id'];
		$defaultClass   = 'visualizer-front';
		$class = apply_filters( Visualizer_Plugin::FILTER_CHART_WRAPPER_CLASS, $atts['class'], $atts['id'] );
		$class  = $defaultClass . ' ' . $class . ' ' . 'visualizer-front-' . $atts['id'];
		$class = ! empty( $class ) ? ' class="' . trim( $class ) . '"' : '';

		$type = get_post_meta( $chart->ID, Visualizer_Plugin::CF_CHART_TYPE, true );

		// faetch and update settings
		$settings = get_post_meta( $chart->ID, Visualizer_Plugin::CF_SETTINGS, true );
		if ( empty( $settings['height'] ) ) {
			$settings['height'] = '400';
		}

		// handle series filter hooks
		$series = apply_filters( Visualizer_Plugin::FILTER_GET_CHART_SERIES, get_post_meta( $chart->ID, Visualizer_Plugin::CF_SERIES, true ), $chart->ID, $type );
		if ( ! empty( $atts['series'] ) ) {
			$series = apply_filters( $atts['series'], $series, $chart->ID, $type );
		}
		// handle settings filter hooks
		$settings = apply_filters( Visualizer_Plugin::FILTER_GET_CHART_SETTINGS, $settings, $chart->ID, $type );
		if ( ! empty( $atts['settings'] ) ) {
			$settings = apply_filters( $atts['settings'], $settings, $chart->ID, $type );
		}

		// handle data filter hooks
		$data = apply_filters( Visualizer_Plugin::FILTER_GET_CHART_DATA, unserialize( $chart->post_content ), $chart->ID, $type );
		if ( ! empty( $atts['data'] ) ) {
			$data = apply_filters( $atts['data'], $data, $chart->ID, $type );
		}

		$id = $id . '-' . rand();

		// add chart to the array
		$this->_charts[ $id ] = array(
			'type'     => $type,
			'series'   => $series,
			'settings' => $settings,
			'data'     => $data,
		);

		// enqueue visualizer render and update render localizations
		wp_enqueue_script( 'visualizer-render' );
		wp_localize_script( 'visualizer-render', 'visualizer', array(
			'charts'        => $this->_charts,
			'map_api_key'   => get_option( 'visualizer-map-api-key' ),
		) );

		// return placeholder div
		return '<div id="' . $id . '"' . $class . '></div>';
	}

}
