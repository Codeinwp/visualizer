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

		echo '<li class="group open">';
			echo '<h3 class="group-title">', esc_html__( 'Upload CSV File', Visualizer_Plugin::NAME ), '</h3>';
			echo '<div class="group-content">';
				echo '<iframe id="thehole" name="thehole"></iframe>';

				echo '<p class="group-description">';
					esc_html_e( "Select and upload your data CSV file here. The first row of the CSV file should contain the column headings. The second one should contain series type (string, number, boolean, date, datetime, timeofday).", Visualizer_Plugin::NAME );
				echo '</p>';

				echo '<p class="group-description">';
					esc_html_e( 'If you are unsure about how to format your data CSV then please take a look at this sample:', Visualizer_Plugin::NAME );
					echo ' <a href="', VISUALIZER_ABSURL, 'samples/', $this->type, '.csv" target="_blank">', $this->type, '.csv</a> ';
					printf( esc_html__( 'or read how you can add Google spreadsheet in following %sarticle%s.', Visualizer_Plugin::NAME ), '<a href="https://github.com/madpixelslabs/visualizer/wiki/How-can-I-populate-data-from-Google-Spreadsheet%3F" target="_blank">', '</a>' );
				echo '</p>';

				echo '<div>';
					echo '<form id="csv-form" action="', $upload_link, '" method="post" target="thehole" enctype="multipart/form-data">';
						echo '<input type="hidden" id="remote-data" name="remote_data">';
						echo '<div class="button button-primary file-wrapper">';
							echo '<input type="file" id="csv-file" class="file" name="local_data">';
							esc_attr_e( 'From Computer', Visualizer_Plugin::NAME );
						echo '</div>';

						echo '<div>';
							echo '<a id="remote-file" class="button" href="javascript:;">', esc_html__( 'From Web', Visualizer_Plugin::NAME ), '</a>';
						echo '</div>';
					echo '</form>';
				echo '</div>';
			echo '</div>';
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