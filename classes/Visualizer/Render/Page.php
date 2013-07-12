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
	 * Enqueues scripts and styles what will be used in a page.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _enqueueScripts() {
		wp_enqueue_style( 'visualizer-frame', VISUALIZER_ABSURL . 'css/frame.css', array( 'buttons' ), Visualizer_Plugin::VERSION );
		wp_enqueue_script( 'visualizer-frame', VISUALIZER_ABSURL . 'js/frame.js', array( 'jquery' ), Visualizer_Plugin::VERSION, true );
	}

	/**
	 * Renders a page.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _toHTML() {
		$this->_enqueueScripts();

		echo '<!DOCTYPE html>';
		echo '<html>';
			echo '<head>';
				$this->_renderHead();
				wp_print_styles();
				wp_print_head_scripts();
			echo '</head>';
			echo '<body class="', $this->_getBodyClasses(), '">';
				$this->_renderBody();
				wp_print_footer_scripts();
			echo '</body>';
		echo '</html>';
	}

	/**
	 * Renders page head.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _renderHead() {
		echo '<meta charset="', get_bloginfo( 'charset' ), '">';
		echo '<title>Visualizer Chart Builder</title>';
	}

	/**
	 * Renders page body.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _renderBody() {
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

	/**
	 * Renturns page body classes.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @return string The classes string for page body.
	 */
	protected function _getBodyClasses() {
		return 'wp-core-ui has-sidebar';
	}

}