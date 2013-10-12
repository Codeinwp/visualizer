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
			'none'     => esc_html__( 'Straight line without curve', Visualizer_Plugin::NAME ),
			'function' => esc_html__( 'The angles of the line will be smoothed', Visualizer_Plugin::NAME ),
		);
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
			echo '<b>', esc_html__( 'Line Width And Point Size', Visualizer_Plugin::NAME ), '</b>';

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
				esc_html_e( 'Data line width and diameter of displayed points in pixels. Use zero to hide all lines or points.', Visualizer_Plugin::NAME );
			echo '</p>';
		echo '</div>';

		if ( $this->_includeCurveTypes ) {
			self::_renderSelectItem(
				esc_html__( 'Curve Type', Visualizer_Plugin::NAME ),
				'curveType',
				$this->curveType,
				$this->_curveTypes,
				esc_html__( 'Determines whether the series has to be presented in the legend or not.', Visualizer_Plugin::NAME )
			);
		}

		if ( $this->_includeFocusTarget ) {
			self::_renderSelectItem(
				esc_html__( 'Focus Target', Visualizer_Plugin::NAME ),
				'focusTarget',
				$this->focusTarget,
				array(
					''         => '',
					'datum'    => esc_html__( 'Focus on a single data point.', Visualizer_Plugin::NAME ),
					'category' => esc_html__( 'Focus on a grouping of all data points along the major axis.', Visualizer_Plugin::NAME ),
				),
				esc_html__( 'The type of the entity that receives focus on mouse hover. Also affects which entity is selected by mouse click.', Visualizer_Plugin::NAME )
			);
		}
	}

	/**
	 * Renders lines settings.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _renderLineSettings() {
		self::_renderGroupStart( esc_html__( 'Lines Settings', Visualizer_Plugin::NAME ) );
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
			esc_html__( 'Visible In Legend', Visualizer_Plugin::NAME ),
			'series[' . $index . '][visibleInLegend]',
			isset( $this->series[$index]['visibleInLegend'] ) ? $this->series[$index]['visibleInLegend'] : '',
			array(
				''  => '',
				'0' => esc_html__( 'No', Visualizer_Plugin::NAME ),
				'1' => esc_html__( 'Yes', Visualizer_Plugin::NAME ),
			),
			esc_html__( 'Determines whether the series has to be presented in the legend or not.', Visualizer_Plugin::NAME )
		);

		echo '<div class="section-item">';
			echo '<a class="more-info" href="javascript:;">[?]</a>';
			echo '<b>', esc_html__( 'Line Width And Point Size', Visualizer_Plugin::NAME ), '</b>';

			echo '<table class="section-table" cellspacing="0" cellpadding="0" border="0">';
				echo '<tr>';
					echo '<td class="section-table-column">';
						$line_width = isset( $this->series[$index]['lineWidth'] ) ? $this->series[$index]['lineWidth'] : '';
						echo '<input type="text" name="series[', $index, '][lineWidth]" class="control-text" value="', esc_attr( $line_width ), '" placeholder="2">';
					echo '</td>';
					echo '<td class="section-table-column">';
						$point_size = isset( $this->series[$index]['pointSize'] ) ? $this->series[$index]['pointSize'] : '';
						echo '<input type="text" name="series[', $index, '][pointSize]" class="control-text" value="', esc_attr( $point_size ), '" placeholder="0">';
					echo '</td>';
				echo '</tr>';
			echo '</table>';

			echo '<p class="section-description">';
				esc_html_e( 'Overrides the global line width and point size values for this series.', Visualizer_Plugin::NAME );
			echo '</p>';
		echo '</div>';

		$this->_renderFormatField( $index );

		if ( $this->_includeCurveTypes ) {
			self::_renderSelectItem(
				esc_html__( 'Curve Type', Visualizer_Plugin::NAME ),
				'series[' . $index . '][curveType]',
				isset( $this->series[$index]['curveType'] ) ? $this->series[$index]['curveType'] : '',
				$this->_curveTypes,
				esc_html__( 'Determines whether the series has to be presented in the legend or not.', Visualizer_Plugin::NAME )
			);
		}

		self::_renderColorPickerItem(
			esc_html__( 'Color', Visualizer_Plugin::NAME ),
			'series[' . $index . '][color]',
			isset( $this->series[$index]['color'] ) ? $this->series[$index]['color'] : null,
			null
		);
	}

}