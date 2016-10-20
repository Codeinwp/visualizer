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
 * Base class for sidebar settings of linear based charts.
 *
 * @category Visualizer
 * @package Render
 * @subpackage Sidebar
 *
 * @since 1.0.0
 * @abstract
 */
abstract class Visualizer_Render_Sidebar_Linear extends Visualizer_Render_Sidebar_Graph {

	/**
	 * Determines whether we need to render curve type option or not.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @var boolean
	 */
	protected $_includeCurveTypes;

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
	 * Determines whether we need to render focus target option or not.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @var boolean
	 */
	protected $_includeFocusTarget;

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

		$this->_includeCurveTypes = true;
		$this->_includeFocusTarget = true;

		$this->_curveTypes = array(
			''         => '',
			'none'     => esc_html__( 'Straight line without curve', 'visualizer' ),
			'function' => esc_html__( 'The angles of the line will be smoothed', 'visualizer' ),
		);
	}

	/**
	 * Renders general settings block for horizontal axis settings.
	 *
	 * @since 1.4.0
	 *
	 * @access protected
	 */
	protected function _renderHorizontalAxisGeneralSettings() {
		parent::_renderHorizontalAxisGeneralSettings();

		self::_renderColorPickerItem(
			esc_html__( 'Axis Text Color', 'visualizer' ),
			'vAxis[textStyle]',
			isset( $this->vAxis['textStyle'] ) ? $this->vAxis['textStyle'] : null,
			'#000'
		);

		$this->_renderHorizontalAxisFormatField();
	}

	/**
	 * Renders general settings block for vertical axis settings.
	 *
	 * @since 1.4.0
	 *
	 * @access protected
	 */
	protected function _renderVerticalAxisGeneralSettings() {
		parent::_renderVerticalAxisGeneralSettings();

		self::_renderColorPickerItem(
			esc_html__( 'Axis Text Color', 'visualizer' ),
			'hAxis[textStyle]',
			isset( $this->hAxis['textStyle'] ) ? $this->hAxis['textStyle'] : null,
			'#000'
		);

		$this->_renderVerticalAxisFormatField();
	}

	/**
	 * Renders line settings items.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _renderLineSettingsItems() {
		echo '<div class="section-item">';
			echo '<a class="more-info" href="javascript:;">[?]</a>';
			echo '<b>', esc_html__( 'Line Width And Point Size', 'visualizer' ), '</b>';

			echo '<table class="section-table" cellspacing="0" cellpadding="0" border="0">';
				echo '<tr>';
					echo '<td class="section-table-column">';
						echo '<input type="text" name="lineWidth" class="control-text" value="', esc_attr( $this->lineWidth ), '" placeholder="2">';
					echo '</td>';
					echo '<td class="section-table-column">';
						echo '<input type="text" name="pointSize" class="control-text" value="', esc_attr( $this->pointSize ), '" placeholder="0">';
					echo '</td>';
				echo '</tr>';
			echo '</table>';

			echo '<p class="section-description">';
				esc_html_e( 'Data line width and diameter of displayed points in pixels. Use zero to hide all lines or points.', 'visualizer' );
			echo '</p>';
		echo '</div>';

		if ( $this->_includeCurveTypes ) {
			self::_renderSelectItem(
				esc_html__( 'Curve Type', 'visualizer' ),
				'curveType',
				$this->curveType,
				$this->_curveTypes,
				esc_html__( 'Determines whether the series has to be presented in the legend or not.', 'visualizer' )
			);
		}

		echo '<div class="section-delimiter"></div>';

		if ( $this->_includeFocusTarget ) {
			self::_renderSelectItem(
				esc_html__( 'Focus Target', 'visualizer' ),
				'focusTarget',
				$this->focusTarget,
				array(
					''         => '',
					'datum'    => esc_html__( 'Focus on a single data point.', 'visualizer' ),
					'category' => esc_html__( 'Focus on a grouping of all data points along the major axis.', 'visualizer' ),
				),
				esc_html__( 'The type of the entity that receives focus on mouse hover. Also affects which entity is selected by mouse click.', 'visualizer' )
			);
		}

		self::_renderSelectItem(
			esc_html__( 'Selection Mode', 'visualizer' ),
			'selectionMode',
			$this->selectionMode,
			array(
				''         => '',
				'single'   => esc_html__( 'Single data point', 'visualizer' ),
				'multiple' => esc_html__( 'Multiple data points', 'visualizer' ),
			),
			esc_html__( 'Determines how many data points an user can select on a chart.', 'visualizer' )
		);

		self::_renderSelectItem(
			esc_html__( 'Aggregation Target', 'visualizer' ),
			'aggregationTarget',
			$this->aggregationTarget,
			array(
				''         => '',
				'category' => esc_html__( 'Group selected data by x-value', 'visualizer' ),
				'series'   => esc_html__( 'Group selected data by series', 'visualizer' ),
				'auto'     => esc_html__( 'Group selected data by x-value if all selections have the same x-value, and by series otherwise', 'visualizer' ),
				'none'     => esc_html__( 'Show only one tooltip per selection', 'visualizer' ),
			),
			esc_html__( 'Determines how multiple data selections are rolled up into tooltips. To make it working you need to set multiple selection mode and tooltip trigger to display it when an user selects an element.', 'visualizer' )
		);

		echo '<div class="section-delimiter"></div>';

		self::_renderTextItem(
			esc_html__( 'Point Opacity', 'visualizer' ),
			'dataOpacity',
			$this->dataOpacity,
			esc_html__( 'The transparency of data points, with 1.0 being completely opaque and 0.0 fully transparent.', 'visualizer' ),
			'1.0'
		);
	}

	/**
	 * Renders lines settings.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _renderLineSettings() {
		self::_renderGroupStart( esc_html__( 'Lines Settings', 'visualizer' ) );
			self::_renderSectionStart();
				$this->_renderLineSettingsItems();
			self::_renderSectionEnd();
		self::_renderGroupEnd();
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
		self::_renderSelectItem(
			esc_html__( 'Visible In Legend', 'visualizer' ),
			'series[' . $index . '][visibleInLegend]',
			isset( $this->series[ $index ]['visibleInLegend'] ) ? $this->series[ $index ]['visibleInLegend'] : '',
			array(
				''  => '',
				'0' => esc_html__( 'No', 'visualizer' ),
				'1' => esc_html__( 'Yes', 'visualizer' ),
			),
			esc_html__( 'Determines whether the series has to be presented in the legend or not.', 'visualizer' )
		);

		echo '<div class="section-item">';
			echo '<a class="more-info" href="javascript:;">[?]</a>';
			echo '<b>', esc_html__( 'Line Width And Point Size', 'visualizer' ), '</b>';

			echo '<table class="section-table" cellspacing="0" cellpadding="0" border="0">';
				echo '<tr>';
					echo '<td class="section-table-column">';
						$line_width = isset( $this->series[ $index ]['lineWidth'] ) ? $this->series[ $index ]['lineWidth'] : '';
						echo '<input type="text" name="series[', $index, '][lineWidth]" class="control-text" value="', esc_attr( $line_width ), '" placeholder="2">';
					echo '</td>';
					echo '<td class="section-table-column">';
						$point_size = isset( $this->series[ $index ]['pointSize'] ) ? $this->series[ $index ]['pointSize'] : '';
						echo '<input type="text" name="series[', $index, '][pointSize]" class="control-text" value="', esc_attr( $point_size ), '" placeholder="0">';
					echo '</td>';
				echo '</tr>';
			echo '</table>';

			echo '<p class="section-description">';
				esc_html_e( 'Overrides the global line width and point size values for this series.', 'visualizer' );
			echo '</p>';
		echo '</div>';

		$this->_renderFormatField( $index );

		if ( $this->_includeCurveTypes ) {
			self::_renderSelectItem(
				esc_html__( 'Curve Type', 'visualizer' ),
				'series[' . $index . '][curveType]',
				isset( $this->series[ $index ]['curveType'] ) ? $this->series[ $index ]['curveType'] : '',
				$this->_curveTypes,
				esc_html__( 'Determines whether the series has to be presented in the legend or not.', 'visualizer' )
			);
		}

		self::_renderColorPickerItem(
			esc_html__( 'Color', 'visualizer' ),
			'series[' . $index . '][color]',
			isset( $this->series[ $index ]['color'] ) ? $this->series[ $index ]['color'] : null,
			null
		);
	}

}
