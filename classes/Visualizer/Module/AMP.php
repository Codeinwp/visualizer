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
		$this->_addAction( 'amp_post_template_head', 'addToHeader' );
		$this->_addAction( 'query_vars', 'registerVar' );
		$this->_addAction( 'init', 'addVirtualPage', null, 9 );
		$this->_addAction( 'template_include', 'showOnlyChart' );
	}

	/**
	 * Returns the template that shows only the chart sans the header etc.
	 */
	public function showOnlyChart( $template ) {
		$chart  = get_query_var( '_chart' );
		if ( $chart ) {
			return VISUALIZER_ABSPATH . '/templates/visualizer-get-chart.php';
		}
		return $template;
	}

	/**
	 * Add the iframe component to the header.
	 */
	public function addToHeader() {
		echo '<script async custom-element="amp-iframe" src="https://cdn.ampproject.org/v0/amp-iframe-0.1.js"></script>';
	}

	/**
	 * Register the virtual page.
	 */
	public function addVirtualPage() {
		add_rewrite_rule( '^visualizer-get-chart/([0-9]+)/?', 'index.php?page=visualizer-get-chart&_chart=$matches[1]', 'top' );
	}

	/**
	 * Register the virtual page vars.
	 */
	public function registerVar( $vars ) {
		$vars[] = '_chart';
		return $vars;
	}

	/**
	 * Is this an AMP request?
	 */
	public static function is_amp() {
		return is_amp_endpoint();
	}

	/**
	 * Loads the iframe HTML.
	 */
	public static function get_iframe( $chart ) {
		ob_start();
		include VISUALIZER_ABSPATH . '/templates/amp-iframe.php';
		return ob_get_clean();
	}

}
