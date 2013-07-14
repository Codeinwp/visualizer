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
 * @subpackage Sidebar
 *
 * @since 1.0.0
 */
class Visualizer_Render_Sidebar_Line extends Visualizer_Render_Sidebar {

	/**
	 * The array of available axis positions.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @var array
	 */
	protected $_positions;

	/**
	 * The array of available curve types.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @var array
	 */
	protected $_curveTypes;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param array $data The data what has to be associated with this render.
	 */
	public function __construct( $data = array() ) {
		parent::__construct( $data );

		$this->_positions = array(
			''     => '',
			'in'   => esc_html__( 'Inside the chart', Visualizer_Plugin::NAME ),
			'out'  => esc_html__( 'Outside the chart', Visualizer_Plugin::NAME ),
			'none' => esc_html__( 'None', Visualizer_Plugin::NAME ),
		);

		$this->_curveTypes = array(
			''         => '',
			'none'     => esc_html__( 'Straight line without curve', Visualizer_Plugin::NAME ),
			'function' => esc_html__( 'The angles of the line will be smoothed', Visualizer_Plugin::NAME ),
		);
	}

	/**
	 * Renders template.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _toHTML() {
		$this->_renderGeneralSettings();
		$this->_renderAxesSettings();
		$this->_renderLineSettings();
		$this->_renderSeriesSettings();
		$this->_renderViewSettings();
	}

	/**
	 * Renders chart title settings.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _renderChartTitleSettings() {
		parent::_renderChartTitleSettings();

		echo '<div class="section-item section-group">';
			echo '<a class="more-info" href="javascript:;">[?]</a>';
			echo '<b>', esc_html__( 'Chart Titles Position', Visualizer_Plugin::NAME ), '</b>';
			echo '<select class="control-select" name="titlePosition">';
				foreach ( $this->_positions as $position => $label ) {
					echo '<option value="', $position, '">', $label, '</option>';
				}
			echo '</select>';
			echo '<p class="section-description">';
				esc_html_e( 'Where to place the chart title, compared to the chart area.', Visualizer_Plugin::NAME );
			echo '</p>';
		echo '</div>';
	}

	/**
	 * Renders chart axes settings.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _renderAxesSettings() {
		$directions = array(
			''   => '',
			'1'  => esc_html__( 'Identical Direction', Visualizer_Plugin::NAME ),
			'-1' => esc_html__( 'Reverse Direction', Visualizer_Plugin::NAME ),
		);

		echo '<li class="group">';
			echo '<h3 class="group-title">', esc_html__( 'Horizontal & Vertical Axes', Visualizer_Plugin::NAME ), '</h3>';
			echo '<ul class="group-content">';
				echo '<li>';
					echo '<div class="section-items open">';
						echo '<div class="section-item">';
							echo '<a class="more-info" href="javascript:;">[?]</a>';
							echo '<b>', esc_html__( 'Axes Titles Position', Visualizer_Plugin::NAME ), '</b>';
							echo '<select class="control-select" name="axisTitlesPosition">';
								foreach ( $this->_positions as $position => $label ) {
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
					echo '<span class="section-title">', esc_html__( 'Horizontal Axis', Visualizer_Plugin::NAME ), '</span>';
					echo '<div class="section-items">';
						echo '<div class="section-item">';
							echo '<a class="more-info" href="javascript:;">[?]</a>';
							echo '<b>', esc_html__( 'Axis Title', Visualizer_Plugin::NAME ), '</b>';
							echo '<input type="text" class="control-text control-onkeyup" name="hAxis[title]" value="">';
							echo '<p class="section-description">';
								esc_html_e( 'The title of the horizontal axis.', Visualizer_Plugin::NAME );
							echo '</p>';
						echo '</div>';

						echo '<div class="section-item">';
							echo '<a class="more-info" href="javascript:;">[?]</a>';
							echo '<b>', esc_html__( 'Text Position', Visualizer_Plugin::NAME ), '</b>';
							echo '<select class="control-select" name="vAxis[textPosition]">';
								foreach ( $this->_positions as $position => $label ) {
									echo '<option value="', $position, '">', $label, '</option>';
								}
							echo '</select>';
							echo '<p class="section-description">';
								esc_html_e( 'Position of the horizontal axis text, relative to the chart area.', Visualizer_Plugin::NAME );
							echo '</p>';
						echo '</div>';

						echo '<div class="section-item">';
							echo '<a class="more-info" href="javascript:;">[?]</a>';
							echo '<b>', esc_html__( 'Direction', Visualizer_Plugin::NAME ), '</b>';
							echo '<select class="control-select" name="hAxis[direction]">';
								foreach ( $directions as $direction => $label ) {
									echo '<option value="', $direction, '">', $label, '</option>';
								}
							echo '</select>';
							echo '<p class="section-description">';
								esc_html_e( 'The direction in which the values along the horizontal axis grow.', Visualizer_Plugin::NAME );
							echo '</p>';
						echo '</div>';

						echo '<div class="section-item">';
							echo '<a class="more-info" href="javascript:;">[?]</a>';
							echo '<b>', esc_html__( 'Grid Lines Count', Visualizer_Plugin::NAME ), '</b>';
							echo '<input type="text" class="control-text control-onkeyup" name="vAxis[gridlines][count]" value="" placeholder="5">';
							echo '<p class="section-description">';
								esc_html_e( 'The number of vertical gridlines inside the chart area. Minimum value is 2. Specify -1 to automatically compute the number of gridlines.', Visualizer_Plugin::NAME );
							echo '</p>';
						echo '</div>';

						echo '<div class="section-item">';
							echo '<b>', esc_html__( 'Grid Lines Color', Visualizer_Plugin::NAME ), '</b>';
							echo '<div>';
								echo '<input type="text" class="color-picker-hex" name="vAxis[gridlines][color]" maxlength="7" placeholder="', esc_attr__( 'Hex Value', Visualizer_Plugin::NAME ), '" value="#ccc" data-default-color="#ccc">';
							echo '</div>';
						echo '</div>';

						echo '<div class="section-item">';
							echo '<b>', esc_html__( 'Base Lines Color', Visualizer_Plugin::NAME ), '</b>';
							echo '<div>';
								echo '<input type="text" class="color-picker-hex" name="vAxis[baselineColor]" maxlength="7" placeholder="', esc_attr__( 'Hex Value', Visualizer_Plugin::NAME ), '" value="#000" data-default-color="#000">';
							echo '</div>';
						echo '</div>';
					echo '</div>';
				echo '</li>';

				echo '<li>';
					echo '<span class="section-title">', esc_html__( 'Vertical Axis', Visualizer_Plugin::NAME ), '</span>';
					echo '<div class="section-items">';
						echo '<div class="section-item">';
							echo '<a class="more-info" href="javascript:;">[?]</a>';
							echo '<b>', esc_html__( 'Axis Title', Visualizer_Plugin::NAME ), '</b>';
							echo '<input type="text" class="control-text control-onkeyup" name="vAxis[title]" value="">';
							echo '<p class="section-description">';
								esc_html_e( 'The title of the vertical axis.', Visualizer_Plugin::NAME );
							echo '</p>';
						echo '</div>';

						echo '<div class="section-item">';
							echo '<a class="more-info" href="javascript:;">[?]</a>';
							echo '<b>', esc_html__( 'Text Position', Visualizer_Plugin::NAME ), '</b>';
							echo '<select class="control-select" name="hAxis[textPosition]">';
								foreach ( $this->_positions as $position => $label ) {
									echo '<option value="', $position, '">', $label, '</option>';
								}
							echo '</select>';
							echo '<p class="section-description">';
								esc_html_e( 'Position of the horizontal axis text, relative to the chart area.', Visualizer_Plugin::NAME );
							echo '</p>';
						echo '</div>';

						echo '<div class="section-item">';
							echo '<a class="more-info" href="javascript:;">[?]</a>';
							echo '<b>', esc_html__( 'Direction', Visualizer_Plugin::NAME ), '</b>';
							echo '<select class="control-select" name="vAxis[direction]">';
								foreach ( $directions as $direction => $label ) {
									echo '<option value="', $direction, '">', $label, '</option>';
								}
							echo '</select>';
							echo '<p class="section-description">';
								esc_html_e( 'The direction in which the values along the vertical axis grow.', Visualizer_Plugin::NAME );
							echo '</p>';
						echo '</div>';

						echo '<div class="section-item">';
							echo '<a class="more-info" href="javascript:;">[?]</a>';
							echo '<b>', esc_html__( 'Grid Lines Count', Visualizer_Plugin::NAME ), '</b>';
							echo '<input type="text" class="control-text control-onkeyup" name="hAxis[gridlines][count]" value="" placeholder="5">';
							echo '<p class="section-description">';
								esc_html_e( 'The number of vertical gridlines inside the chart area. Minimum value is 2. Specify -1 to automatically compute the number of gridlines.', Visualizer_Plugin::NAME );
							echo '</p>';
						echo '</div>';

						echo '<div class="section-item">';
							echo '<b>', esc_html__( 'Grid Lines Color', Visualizer_Plugin::NAME ), '</b>';
							echo '<div>';
								echo '<input type="text" class="color-picker-hex" name="hAxis[gridlines][color]" maxlength="7" placeholder="', esc_attr__( 'Hex Value', Visualizer_Plugin::NAME ), '" value="#ccc" data-default-color="#ccc">';
							echo '</div>';
						echo '</div>';

						echo '<div class="section-item">';
							echo '<b>', esc_html__( 'Base Lines Color', Visualizer_Plugin::NAME ), '</b>';
							echo '<div>';
								echo '<input type="text" class="color-picker-hex" name="hAxis[baselineColor]" maxlength="7" placeholder="', esc_attr__( 'Hex Value', Visualizer_Plugin::NAME ), '" value="#000" data-default-color="#000">';
							echo '</div>';
						echo '</div>';
					echo '</div>';
				echo '</li>';
			echo '</ul>';
		echo '</li>';
	}

	/**
	 * Renders series settings group.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _renderSeriesSettings() {
		echo '<li class="group">';
			echo '<h3 class="group-title">', esc_html__( 'Series Settings', Visualizer_Plugin::NAME ), '</h3>';
			echo '<ul class="group-content">';
				for ( $i = 1, $cnt = count( $this->series ); $i < $cnt; $i++ ) {
					echo '<li>';
						echo '<span class="section-title">', esc_html( $this->series[$i]['label'] ), '</span>';
						echo '<div class="section-items">';
							$this->_renderSeries( $i - 1 );
						echo '</div>';
					echo '</li>';
				}
			echo '</ul>';
		echo '</li>';
	}

	/**
	 * Renders concreate series settings.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @param int $index The series index.
	 */
	protected function _renderSeries( $index ) {
		$visibility = array(
			''  => '',
			'0' => esc_html__( 'No', Visualizer_Plugin::NAME ),
			'1' => esc_html__( 'Yes', Visualizer_Plugin::NAME ),
		);

		echo '<div class="section-item">';
			echo '<a class="more-info" href="javascript:;">[?]</a>';
			echo '<b>', esc_html__( 'Visible In Legend', Visualizer_Plugin::NAME ), '</b>';
			echo '<select class="control-select" name="series[', $index, '][visibleInLegend]">';
				foreach ( $visibility as $key => $label ) {
					echo '<option value="', $key, '">', $label, '</option>';
				}
			echo '</select>';
			echo '<p class="section-description">';
				esc_html_e( 'Determines whether the series has to be presented in the legend or not.', Visualizer_Plugin::NAME );
			echo '</p>';
		echo '</div>';

		echo '<div class="section-item section-group">';
			echo '<a class="more-info" href="javascript:;">[?]</a>';
			echo '<b>', esc_html__( 'Line Width', Visualizer_Plugin::NAME ), '</b>';
			echo '<input type="text" class="control-text control-onkeyup" name="series[', $index, '][lineWidth]" value="" placeholder="2">';
			echo '<p class="section-description">';
				esc_html_e( 'Overrides the global line width value for this series.', Visualizer_Plugin::NAME );
			echo '</p>';
		echo '</div>';

		echo '<div class="section-item section-group">';
			echo '<a class="more-info" href="javascript:;">[?]</a>';
			echo '<b>', esc_html__( 'Point Size', Visualizer_Plugin::NAME ), '</b>';
			echo '<input type="text" class="control-text control-onkeyup" name="series[', $index, '][pointSize]" value="" placeholder="0">';
			echo '<p class="section-description">';
				esc_html_e( 'Overrides the global point size value for this series.', Visualizer_Plugin::NAME );
			echo '</p>';
		echo '</div>';

		echo '<div class="section-item">';
			echo '<a class="more-info" href="javascript:;">[?]</a>';
			echo '<b>', esc_html__( 'Curve Type', Visualizer_Plugin::NAME ), '</b>';
			echo '<select class="control-select" name="series[', $index, '][curveType]">';
				foreach ( $this->_curveTypes as $key => $label ) {
					echo '<option value="', $key, '">', $label, '</option>';
				}
			echo '</select>';
			echo '<p class="section-description">';
				esc_html_e( 'Determines whether the series has to be presented in the legend or not.', Visualizer_Plugin::NAME );
			echo '</p>';
		echo '</div>';

		echo '<div class="section-item">';
			echo '<b>', esc_html__( 'Color', Visualizer_Plugin::NAME ), '</b>';
			echo '<div>';
				echo '<input type="text" class="color-picker-hex" name="series[', $index, '][color]" maxlength="7" placeholder="', esc_attr__( 'Hex Value', Visualizer_Plugin::NAME ), '" value="" data-default-color="">';
			echo '</div>';
		echo '</div>';
	}

	/**
	 * Renders line settings.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _renderLineSettings() {
		echo '<li class="group">';
			echo '<h3 class="group-title">', esc_html__( 'General Line Settings', Visualizer_Plugin::NAME ), '</h3>';
			echo '<ul class="group-content">';
				echo '<li>';
					echo '<div class="section-items open">';
						echo '<div class="section-item section-group">';
							echo '<a class="more-info" href="javascript:;">[?]</a>';
							echo '<b>', esc_html__( 'Line Width', Visualizer_Plugin::NAME ), '</b>';
							echo '<input type="text" class="control-text control-onkeyup" name="lineWidth" value="" placeholder="2">';
							echo '<p class="section-description">';
								esc_html_e( 'Data line width in pixels. Use zero to hide all lines and show only the points.', Visualizer_Plugin::NAME );
							echo '</p>';
						echo '</div>';

						echo '<div class="section-item section-group">';
							echo '<a class="more-info" href="javascript:;">[?]</a>';
							echo '<b>', esc_html__( 'Point Size', Visualizer_Plugin::NAME ), '</b>';
							echo '<input type="text" class="control-text control-onkeyup" name="pointSize" value="" placeholder="0">';
							echo '<p class="section-description">';
								esc_html_e( 'Diameter of displayed points in pixels. Use zero to hide all points.', Visualizer_Plugin::NAME );
							echo '</p>';
						echo '</div>';

						echo '<div class="section-item section-group">';
							echo '<a class="more-info" href="javascript:;">[?]</a>';
							echo '<b>', esc_html__( 'Curve Type', Visualizer_Plugin::NAME ), '</b>';
							echo '<select class="control-select" name="curveType">';
								foreach ( $this->_curveTypes as $key => $label ) {
									echo '<option value="', $key, '">', $label, '</option>';
								}
							echo '</select>';
							echo '<p class="section-description">';
								esc_html_e( 'Determines whether the series has to be presented in the legend or not.', Visualizer_Plugin::NAME );
							echo '</p>';
						echo '</div>';

						$focus = array(
							''         => '',
							'datum'    => esc_html__( 'Focus on a single data point.', Visualizer_Plugin::NAME ),
							'category' => esc_html__( 'Focus on a grouping of all data points along the major axis.', Visualizer_Plugin::NAME ),
						);

						echo '<div class="section-item">';
							echo '<a class="more-info" href="javascript:;">[?]</a>';
							echo '<b>', esc_html__( 'Focus Target', Visualizer_Plugin::NAME ), '</b>';
							echo '<select class="control-select" name="focusTarget">';
								foreach ( $focus as $key => $label ) {
									echo '<option value="', $key, '">', $label, '</option>';
								}
							echo '</select>';
							echo '<p class="section-description">';
								esc_html_e( 'The type of the entity that receives focus on mouse hover. Also affects which entity is selected by mouse click.', Visualizer_Plugin::NAME );
							echo '</p>';
						echo '</div>';
					echo '</div>';
				echo '</li>';
			echo '</ul>';
		echo '</li>';
	}

}