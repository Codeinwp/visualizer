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
 * Base class for all chart builder pages.
 *
 * @category Visualizer
 * @package Render
 *
 * @since 1.0.0
 */
class Visualizer_Render_Page extends Visualizer_Render {

	/**
	 * Renders the page.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _toHTML() {
		echo '<div id="content">';
			$this->_renderContent();
		echo '</div>';
		$this->_renderSidebar();
		echo '<div id="toolbar">';
			$this->_renderToolbar();
		echo '</div>';
	}

	/**
	 * Renders page content.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _renderContent() {}

	/**
	 * Renders page sidebar.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _renderSidebar() {
		echo '<div id="sidebar">';
			echo '<ul class="group-wrapper">';
				$this->_renderSidebarContent();
			echo '</ul>';

			echo '<div id="rate-the-plugin">';
				echo '<div><b>', esc_html__( 'Like the plugin? Show us your love!', Visualizer_Plugin::NAME ), '</b></div>';
				echo '<div id="rate-stars">&nbsp;</div>';
				echo '<a id="rate-link" href="http://wordpress.org/support/view/plugin-reviews/visualizer" target="_blank">';
					esc_html_e( 'Rate it on WordPress.org', Visualizer_Plugin::NAME );
				echo '</a>';
			echo '</div>';
		echo '</div>';
	}

	/**
	 * Renders sidebar content.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _renderSidebarContent() {}

	/**
	 * Renders toolbar content.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _renderToolbar() {}

}