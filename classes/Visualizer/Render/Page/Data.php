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
 * @subpackage Page
 *
 * @since 1.0.0
 */
class Visualizer_Render_Page_Data extends Visualizer_Render_Page {

	/**
	 * Renders page content.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _renderContent() {
		// Added by Ash/Upwork
		if ( defined( 'Visualizer_Pro' ) ) {
			global $Visualizer_Pro;
			$Visualizer_Pro->_addEditor();
		}
		// Added by Ash/Upwork
		echo '<div id="canvas">';
			echo '<img src="', VISUALIZER_ABSURL, 'images/ajax-loader.gif" class="loader">';
		echo '</div>';
	}

	/**
	 * Renders sidebar content.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _renderSidebarContent() {
		$upload_link = add_query_arg( array(
			'action' => Visualizer_Plugin::ACTION_UPLOAD_DATA,
			'nonce'  => wp_create_nonce(),
			'chart'  => $this->chart->ID,
		), admin_url( 'admin-ajax.php' ) );

				echo '<input type="button" name="back_button" class="return-settings-btn preview-btn hidden-setting" value="&laquo; Back">';
				echo '<div class="initial-screen">';
				echo '<iframe id="thehole" name="thehole"></iframe>';
				echo '<p class="group-description">';
					esc_html_e( 'Select and upload your data CSV file here. The first row of the CSV file should contain the column headings. The second one should contain series type (string, number, boolean, date, datetime, timeofday).', 'visualizer' );
				echo '</p>';

				echo '<p class="group-description">';
					esc_html_e( 'If you are unsure about how to format your data CSV then please take a look at this sample:', 'visualizer' );
					echo ' <a href="', VISUALIZER_ABSURL, 'samples/', $this->type, '.csv" target="_blank">', $this->type, '.csv</a> ';
					printf( esc_html__( 'or read how you can add Google spreadsheet in following %1$sarticle%1$s.', 'visualizer' ), '<a href="https://github.com/madpixelslabs/visualizer/wiki/How-can-I-populate-data-from-Google-Spreadsheet%3F" target="_blank">', '</a>' );
				echo '</p>';

				echo '<div>';
					echo '<form id="csv-form" action="', $upload_link, '" method="post" target="thehole" enctype="multipart/form-data">';
						echo '<input type="hidden" id="remote-data" name="remote_data">';
						echo '<div class="form-inline">';
						echo '<div class="button button-primary file-wrapper computer-btn">';
							echo '<input type="file" id="csv-file" class="file" name="local_data">';
							esc_attr_e( 'From Computer', 'visualizer' );
						echo '</div>';

						echo '<a id="remote-file" class="button from-web from-web-btn" href="javascript:;">', esc_html__( 'From Web', 'visualizer' ), '</a>';
						// Added by Ash/Upwork
		if ( defined( 'Visualizer_Pro' ) ) {
			global $Visualizer_Pro;
			$Visualizer_Pro->_addFormElements();
		} else {
			// Added by Ash/Upwork
			echo '<div class="just-on-pro"> </div>';
		}
						echo '</div>';
					echo '</form>';

					// added by Ash/Upwork
		if ( defined( 'Visualizer_Pro' ) ) {
			global $Visualizer_Pro;
			$Visualizer_Pro->_addEditorElements();
		} else {
?>
<a href="<?php echo Visualizer_Plugin::PRO_TEASER_URL;?>" title="<?php echo Visualizer_Plugin::PRO_TEASER_TITLE;?>" class="check-pro-btn" target="_new">
<input type="button" class="button preview preview-btn" id="existing-chart-free" value="<?php esc_attr_e( 'Check PRO Version ', 'visualizer' );?>">
</a>
<?php
		}

					echo'<input type="button" name="advanced_button" class="advanced-settings-btn preview-btn" value="' . __( 'Advanced', 'visualizer' ) . ' &raquo;">';
					// Added by Ash/Upwork
				echo '</div>';
			echo '</div>';

		// changed by Ash/Upwork
		echo '<div class= "second-screen hidden-setting">';
		echo '<form id="settings-form" action="', add_query_arg( 'nonce', wp_create_nonce() ), '" method="post">';
		echo $this->sidebar;
		echo '</form>';
		echo '</div>';
		// changed by Ash/Upwork
	}

	/**
	 * Renders toolbar content.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _renderToolbar() {
		// changed by Ash/Upwork
		echo '<div class="toolbar-div">';
		echo '<a class="button button-large" href="', add_query_arg( 'tab', 'types' ), '">';
			esc_html_e( 'Back', 'visualizer' );
		echo '</a>';
		echo '</div>';
		echo '<input type="submit" id="settings-button" class="button button-primary button-large push-right" value="', $this->button, '">';

		echo '</div>';

	}

}
