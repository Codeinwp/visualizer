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
 * Renders chart data setup page.
 *
 * @category Visualizer
 * @package Render
 *
 * @since 1.0.0
 */
class Visualizer_Render_Page_Data extends Visualizer_Render_Page {

	/**
	 * Enqueues scripts and styles what will be used in a page.
	 *
	 * @since 1.0.0
	 * @uses wp_enqueue_script() To enqueue chart rendering JS files.
	 *
	 * @access protected
	 */
	protected function _enqueueScripts() {
		parent::_enqueueScripts();
		wp_enqueue_script( 'visualizer-preview' );
	}

	/**
	 * Renders page head.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _renderHead() {
		parent::_renderHead();
		echo '<script type="text/javascript">';
			echo 'window.visualizer = {charts: {canvas: {';
				echo "type: '", $this->type, "', ";
				echo 'series: ', $this->series, ', ';
				echo 'data: ', $this->chart->post_content;
			echo '}}};';
		echo '</script>';
	}

	/**
	 * Renders page content.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _renderContent() {
		echo '<div id="canvas"></div>';
	}

	/**
	 * Renders sidebar content.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _renderSidebarContent() {
		echo '<li class="group">';
			echo '<h3 class="group-title">', esc_html__( 'CSV file', Visualizer_Plugin::NAME ), '</h3>';
			echo '<ul class="group-content">';
				echo '<p class="group-description">';
					esc_html_e( "Select and upload your data CSV file here. The first row of the CSV file should contain the column headings. If you are unsure about how to format your data CSV then please take a look at this sample:", Visualizer_Plugin::NAME );
					echo ' <a href="', VISUALIZER_ABSURL, 'samples/', $this->type, '.csv">', $this->type, '.csv</a>';
				echo '</p>';
			echo '</ul>';
		echo '</li>';
	}

	/**
	 * Renders toolbar content.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _renderToolbar() {
		echo '<a class="button button-large" href="', add_query_arg( 'tab', false ), '">';
			esc_html_e( 'Back', Visualizer_Plugin::NAME );
		echo '</a>';
		echo '<a class="button button-large button-primary push-right" href="', add_query_arg( 'tab', 'settings' ), '">';
			esc_html_e( 'Next', Visualizer_Plugin::NAME );
		echo '</a>';
	}

}