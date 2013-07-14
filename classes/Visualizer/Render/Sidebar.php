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
 * Base class for all chart sidebar groups.
 *
 * @category Visualizer
 * @package Render
 *
 * @since 1.0.0
 * @abstract
 */
abstract class Visualizer_Render_Sidebar extends Visualizer_Render {

	/**
	 * Renders chart area group.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _renderViewSettings() {
		echo '<li class="group">';
			echo '<h3 class="group-title">', esc_html__( 'View Settings', Visualizer_Plugin::NAME ), '</h3>';
			echo '<ul class="group-content">';
				echo '<li>';
					echo '<span class="section-title">', esc_html__( 'Layout', Visualizer_Plugin::NAME ), '</span>';
					echo '<div class="section-items">';
						echo '<div class="section-description visible section-group">';
							esc_html_e( 'Configure the total size of the chart. Two formats are supported: a number, or a number followed by %. A simple number is a value in pixels; a number followed by % is a percentage.', Visualizer_Plugin::NAME );
						echo '</div>';

						echo '<div class="section-group">';
							echo '<div class="section-item">';
								echo '<a class="more-info" href="javascript:;">[?]</a>';
								echo '<b>', esc_html__( 'Width And Height Of Chart', Visualizer_Plugin::NAME ), '</b>';

								echo '<table class="section-table" cellspacing="0" cellpadding="0" border="0">';
									echo '<tr>';
										echo '<td class="section-table-column">';
											echo '<input type="text" name="width" class="control-text control-onkeyup" value="" placeholder="100%">';
										echo '</td>';
										echo '<td class="section-table-column">';
											echo '<input type="text" name="height" class="control-text control-onkeyup" value="" placeholder="400">';
										echo '</td>';
									echo '</tr>';
								echo '</table>';

								echo '<p class="section-description">';
									esc_html_e( 'Determines the total width and height of the chart.', Visualizer_Plugin::NAME );
								echo '</p>';
							echo '</div>';
						echo '</div>';

						echo '<div class="section-delimiter"></div>';

						echo '<div class="section-description visible section-group">';
							esc_html_e( 'Configure the background color for the main area of the chart and the chart border width and color.', Visualizer_Plugin::NAME );
						echo '</div>';

						echo '<div class="section-group">';
							echo '<div class="section-item">';
								echo '<a class="more-info" href="javascript:;">[?]</a>';
								echo '<b>', esc_html__( 'Stroke Width', Visualizer_Plugin::NAME ), '</b>';
								echo '<input type="text" class="control-text control-onkeyup" name="backgroundColor[strokeWidth]" value="" placeholder="0">';
								echo '<p class="section-description">';
									esc_html_e( 'The chart border width in pixels.', Visualizer_Plugin::NAME );
								echo '</p>';
							echo '</div>';

							echo '<div class="section-item">';
								echo '<b>', esc_html__( 'Stroke Color', Visualizer_Plugin::NAME ), '</b>';
								echo '<div>';
									echo '<input type="text" class="color-picker-hex" name="backgroundColor[stroke]" maxlength="7" placeholder="<?php', esc_attr__( 'Hex Value' ), '" value="#666" data-default-color="#666">';
								echo '</div>';
							echo '</div>';

							echo '<div class="section-item">';
								echo '<b>', esc_html__( 'Background Color', Visualizer_Plugin::NAME ), '</b>';
								echo '<div>';
									echo '<input type="text" class="color-picker-hex" name="backgroundColor[fill]" maxlength="7" placeholder="<?php', esc_attr__( 'Hex Value' ), '" value="#fff" data-default-color="#fff">';
								echo '</div>';
							echo '</div>';
						echo '</div>';
					echo '</div>';
				echo '</li>';

				echo '<li>';
					echo '<span class="section-title">', esc_html__( 'Chart Area', Visualizer_Plugin::NAME ), '</span>';
					echo '<div class="section-items">';
						echo '<div class="section-description visible section-group">';
							esc_html_e( 'Configure the placement and size of the chart area (where the chart itself is drawn, excluding axis and legends). Two formats are supported: a number, or a number followed by %. A simple number is a value in pixels; a number followed by % is a percentage.', Visualizer_Plugin::NAME );
						echo '</div>';

						echo '<div class="section-group">';
							echo '<div class="section-item">';
								echo '<a class="more-info" href="javascript:;">[?]</a>';
								echo '<b>', esc_html__( 'Left And Top Margins', Visualizer_Plugin::NAME ), '</b>';

								echo '<table class="section-table" cellspacing="0" cellpadding="0" border="0">';
									echo '<tr>';
										echo '<td class="section-table-column">';
											echo '<input type="text" name="chartArea[left]" class="control-text control-onkeyup" value="" placeholder="20%">';
										echo '</td>';
										echo '<td class="section-table-column">';
											echo '<input type="text" name="chartArea[top]" class="control-text control-onkeyup" value="" placeholder="20%">';
										echo '</td>';
									echo '</tr>';
								echo '</table>';

								echo '<p class="section-description">';
									esc_html_e( 'Determines how far to draw the chart from the left and top borders.', Visualizer_Plugin::NAME );
								echo '</p>';
							echo '</div>';
						echo '</div>';

						echo '<div class="section-item">';
							echo '<a class="more-info" href="javascript:;">[?]</a>';
							echo '<b>', esc_html__( 'Width And Height Of Chart Area', Visualizer_Plugin::NAME ), '</b>';

							echo '<table class="section-table" cellspacing="0" cellpadding="0" border="0">';
								echo '<tr>';
									echo '<td class="section-table-column">';
										echo '<input type="text" name="chartArea[width]" class="control-text control-onkeyup" value="" placeholder="60%">';
									echo '</td>';
									echo '<td class="section-table-column">';
										echo '<input type="text" name="chartArea[height]" class="control-text control-onkeyup" value="" placeholder="60%">';
									echo '</td>';
								echo '</tr>';
							echo '</table>';

							echo '<p class="section-description">';
								esc_html_e( 'Determines the width and hight of the chart area.', Visualizer_Plugin::NAME );
							echo '</p>';
						echo '</div>';
					echo '</div>';
				echo '</li>';
			echo '</ul>';
		echo '</li>';
	}

}