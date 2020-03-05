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
class Visualizer_Render_Page_Update extends Visualizer_Render_Page {

	/**
	 * Renders page template.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _toHTML() {
		if ( $this->is_request_from_gutenberg() ) {
			return;
		}
		echo '<!DOCTYPE html>';
		echo '<html>';
			echo '<head>';
				echo '<script type="text/javascript">';
					echo '(function() {';
		if ( empty( $this->message ) ) {
			echo 'var win = window.dialogArguments || opener || parent || top;';
			echo 'if (win.visualizer) {';
			echo 'win.visualizer.charts.canvas.series = ', $this->series, ';';
			echo 'win.visualizer.charts.canvas.data = ', $this->data, ';';
			if ( $this->settings ) {
				echo 'win.visualizer.charts.canvas.settings = ', $this->settings, ';';
			}
			echo 'win.vizUpdateChartPreview();';

			echo $this->updateEditorAndSettings();

			echo '}';

			do_action( 'visualizer_add_update_hook', $this->series, $this->data );

			if ( Visualizer_Module::is_pro() && Visualizer_Module::is_pro_older_than( '1.9.0' ) ) {
				global $Visualizer_Pro;
				$Visualizer_Pro->_addUpdateHook( $this->series, $this->data );
			}
		} else {
			echo 'alert("', $this->message, '");';
		}
					echo '})();';
				echo '</script>';
			echo '</head>';
			echo '<body></body>';
		echo '</html>';
	}


	/**
	 * Update the hidden content in the LHS and the advanced settings
	 */
	private function updateEditorAndSettings() {
		$editor = '';
		if ( Visualizer_Module::can_show_feature( 'simple-editor' ) ) {
			ob_start();
			Visualizer_Render_Layout::show( 'simple-editor-screen', $this->id );
			$editor = ob_get_clean();
		}

		$sidebar = apply_filters( 'visualizer_get_sidebar', '', $this->id );

		return 'win.vizUpdateHTML(' . json_encode( array( 'html' => $editor ) ) . ', ' . json_encode( array( 'html' => $sidebar ) ) . ');';
	}

}
