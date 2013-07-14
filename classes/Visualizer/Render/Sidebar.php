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
	 * The array of font families accepted by visualization API.
	 *
	 * @since 1.0.0
	 *
	 * @static
	 * @access protected
	 * @var array
	 */
	protected static $_fontFamilies = array(
		'Arial'         => 'Arial',
		'Sans Serif'    => 'Sans Serif',
		'serif'         => 'Serif',
		'Arial black'   => 'Wide',
		'Arial Narrow'  => 'Narrow',
		'Comic Sans MS' => 'Comic Sans MS',
		'Courier New'   => 'Courier New',
		'Garamond'      => 'Garamond',
		'Georgia'       => 'Georgia',
		'Tahoma'        => 'Tahoma',
		'Verdana'       => 'Verdana',
	);

	/**
	 * The array of available axis positions.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @var array
	 */
	protected $_axisPositions;

	/**
	 * The array of available legend positions.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @var array
	 */
	protected $_legendPositions;

	/**
	 * The array of available alignments.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @var array
	 */
	protected $_alignments;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param array $data The data what has to be associated with this render.
	 */
	public function __construct( $data = array( ) ) {
		parent::__construct( $data );

		$this->_axisPositions = array(
			''     => '',
			'in'   => esc_html__( 'Inside the chart area', Visualizer_Plugin::NAME ),
			'out'  => esc_html__( 'Outside the chart area', Visualizer_Plugin::NAME ),
			'none' => esc_html__( 'Omit the axis titles', Visualizer_Plugin::NAME ),
		);

		$this->_legendPositions = array(
			''       => '',
			'right'  => esc_html__( 'Right of the chart', Visualizer_Plugin::NAME ),
			'top'    => esc_html__( 'Above the chart', Visualizer_Plugin::NAME ),
			'bottom' => esc_html__( 'Below the chart', Visualizer_Plugin::NAME ),
			'in'     => esc_html__( 'Inside the chart', Visualizer_Plugin::NAME ),
			'none'   => esc_html__( 'Omit the legend', Visualizer_Plugin::NAME ),
		);

		$this->_alignments = array(
			''       => '',
			'start'  => esc_html__( 'Aligned to the start of the allocated area', Visualizer_Plugin::NAME ),
			'center' => esc_html__( 'Centered in the allocated area', Visualizer_Plugin::NAME ),
			'end'    => esc_html__( 'Aligned to the end of the allocated area', Visualizer_Plugin::NAME ),
		);
	}

	/**
	 * Renders chart general settings group.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _renderGeneralSettings() {
		echo '<li class="group">';
			echo '<h3 class="group-title">', esc_html__( 'General Settings', Visualizer_Plugin::NAME ), '</h3>';
			echo '<ul class="group-content">';
				echo '<li>';
					echo '<div class="section-items open">';
						echo '<div class="section-description visible section-group">';
							esc_html_e( 'Configure title, general font styles and elements positioning settings for the chart.', Visualizer_Plugin::NAME );
						echo '</div>';

						echo '<div class="section-item section-group">';
							echo '<a class="more-info" href="javascript:;">[?]</a>';
							echo '<b>', esc_html__( 'Chart Title', Visualizer_Plugin::NAME ), '</b>';
							echo '<input type="text" class="control-text control-onkeyup" name="title" value="">';
							echo '<p class="section-description">';
								esc_html_e( 'Text to display above the chart.', Visualizer_Plugin::NAME );
							echo '</p>';
						echo '</div>';

						echo '<div class="section-item section-group">';
							echo '<a class="more-info" href="javascript:;">[?]</a>';
							echo '<b>', esc_html__( 'Font Family And Size', Visualizer_Plugin::NAME ), '</b>';

							echo '<table class="section-table" cellspacing="0" cellpadding="0" border="0">';
								echo '<tr>';
									echo '<td class="section-table-column">';
										echo '<select name="fontName" class="control-select">';
											echo '<option></option>';
											foreach ( self::$_fontFamilies as $font => $label ) {
												echo '<option value="', $font, '">', $label, '</option>';
											}
										echo '</select>';
									echo '</td>';
									echo '<td class="section-table-column">';
										echo '<select name="fontSize" class="control-select">';
											echo '<option></option>';
											for	( $i = 7; $i <= 20; $i++ ) {
												echo '<option value="', $i, '">', $i, '</option>';
											}
										echo '</select>';
									echo '</td>';
								echo '</tr>';
							echo '</table>';

							echo '<p class="section-description">';
								esc_html_e( 'The default font family and size for all text in the chart.', Visualizer_Plugin::NAME );
							echo '</p>';
						echo '</div>';

						echo '<div class="section-item">';
							echo '<a class="more-info" href="javascript:;">[?]</a>';
							echo '<b>', esc_html__( 'Axes Titles Position', Visualizer_Plugin::NAME ), '</b>';
							echo '<select class="control-select" name="axisTitlesPosition">';
								foreach ( $this->_axisPositions as $position => $label ) {
									echo '<option value="', $position, '">', $label, '</option>';
								}
							echo '</select>';
							echo '<p class="section-description">';
								esc_html_e( 'Determines where to place the axis titles, compared to the chart area.', Visualizer_Plugin::NAME );
							echo '</p>';
						echo '</div>';
					echo '</div>';
				echo '</li>';
				echo '<li>';
					echo '<span class="section-title">', esc_html__( 'Legend', Visualizer_Plugin::NAME ), '</span>';
					echo '<div class="section-items">';
						echo '<div class="section-item">';
							echo '<a class="more-info" href="javascript:;">[?]</a>';
							echo '<b>', esc_html__( 'Position', Visualizer_Plugin::NAME ), '</b>';
							echo '<select class="control-select" name="legend[position]">';
								foreach ( $this->_legendPositions as $position => $label ) {
									echo '<option value="', $position, '">', $label, '</option>';
								}
							echo '</select>';
							echo '<p class="section-description">';
								esc_html_e( 'Determines where to place the legend, compared to the chart area.', Visualizer_Plugin::NAME );
							echo '</p>';
						echo '</div>';

						echo '<div class="section-item">';
							echo '<a class="more-info" href="javascript:;">[?]</a>';
							echo '<b>', esc_html__( 'Alignment', Visualizer_Plugin::NAME ), '</b>';
							echo '<select class="control-select" name="legend[alignment]">';
								foreach ( $this->_alignments as $position => $label ) {
									echo '<option value="', $position, '">', $label, '</option>';
								}
							echo '</select>';
							echo '<p class="section-description">';
								esc_html_e( 'Determines the alignment of the legend.', Visualizer_Plugin::NAME );
							echo '</p>';
						echo '</div>';
					echo '</div>';
				echo '</li>';
			echo '</ul>';
		echo '</li>';
	}

	/**
	 * Renders chart view settings group.
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