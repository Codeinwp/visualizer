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
 * Renders data uploading respond.
 *
 * @category Visualizer
 * @package Render
 * @subpackage Page
 *
 * @since 1.0.0
 */
class Visualizer_Render_Page_Data_Update extends Visualizer_Render_Page {

	/**
	 * Enqueues scripts and styles what will be used in a page.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _enqueueScripts() {	}

	/**
	 * Renders page head.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _renderHead() {
		echo '<script type="text/javascript">';
			echo '(function() {';
				if ( empty( $this->message ) ) {
					echo 'var win = window.dialogArguments || opener || parent || top;';
					echo 'if (win.visualizer) {';
						echo 'win.visualizer.charts.canvas.series = ', $this->series, ';';
						echo 'win.visualizer.charts.canvas.data = ', $this->data, ';';
						echo 'win.visualizer.render();';
					echo '}';
				} else {
					echo 'alert("', $this->message, '");';
				}
			echo '})();';
		echo '</script>';
	}

	/**
	 * Renders page body.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _renderBody() {}

}