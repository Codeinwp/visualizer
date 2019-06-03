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
 * The module for all AMP stuff.
 *
 * @category Visualizer
 * @package Module
 *
 * @since 1.0.0
 */
class Visualizer_Module_AMP extends Visualizer_Module {

	const NAME = __CLASS__;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 *
	 * @param Visualizer_Plugin $plugin The instance of the plugin.
	 */
	public function __construct( Visualizer_Plugin $plugin ) {
		$this->_addFilter( 'amp_post_template_data', 'addToHeader' );
	}

	/**
	 * Add the iframe component to the header.
	 */
	public function addToHeader( $data ) {
		$data['amp_component_scripts'] = array_merge(
			$data['amp_component_scripts'],
			array(
				'amp-iframe' => 'https://cdn.ampproject.org/v0/amp-iframe-latest.js',
			)
		);
		return $data;
	}

	/**
	 * Is this an AMP request?
	 */
	public static function is_amp() {
		return function_exists( 'is_amp_endpoint' ) && is_amp_endpoint();
	}

	/**
	 * Loads the alterview view of the chart.
	 */
	public function get_chart( $chart, $data, $series, $settings ) {
		$view = apply_filters( 'visualizer_amp_view', null, $chart, $data, $series, $settings );
		if ( ! is_null( $view ) ) {
			return $view;
		}
		$output = $this->_getDataAs( $chart->ID, 'print' );
		return $output['csv'];
	}

}
