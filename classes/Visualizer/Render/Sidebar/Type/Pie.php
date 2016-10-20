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
 * Class for pie chart sidebar settings.
 *
 * @category Visualizer
 * @package Render
 * @subpackage Sidebar
 *
 * @since 1.0.0
 */
class Visualizer_Render_Sidebar_Type_Pie extends Visualizer_Render_Sidebar {

	/**
	 * Renders template.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _toHTML() {
		$this->_renderGeneralSettings();
		$this->_renderPieSettings();
		$this->_renderResidueSettings();
		$this->_renderSlicesSettings();
		$this->_renderViewSettings();
	}

	/**
	 * Renders pie settings group.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _renderPieSettings() {
		self::_renderGroupStart( esc_html__( 'Pie Settings', 'visualizer' ) );
			self::_renderSectionStart();
				self::_renderSelectItem(
					esc_html__( 'Is 3D', 'visualizer' ),
					'is3D',
					$this->is3D,
					$this->_yesno,
					esc_html__( 'If set to yes, displays a three-dimensional chart.', 'visualizer' )
				);

				self::_renderSelectItem(
					esc_html__( 'Reverse Categories', 'visualizer' ),
					'reverseCategories',
					$this->reverseCategories,
					$this->_yesno,
					esc_html__( 'If set to yes, will draw slices counterclockwise.', 'visualizer' )
				);

				self::_renderSelectItem(
					esc_html__( 'Slice Text', 'visualizer' ),
					'pieSliceText',
					$this->pieSliceText,
					array(
						''           => '',
						'percentage' => esc_html__( 'The percentage of the slice size out of the total', 'visualizer' ),
						'value'      => esc_html__( 'The quantitative value of the slice', 'visualizer' ),
						'label'      => esc_html__( 'The name of the slice', 'visualizer' ),
						'none'       => esc_html__( 'No text is displayed', 'visualizer' ),
					),
					esc_html__( 'The content of the text displayed on the slice.', 'visualizer' )
				);

				self::_renderTextItem(
					esc_html__( 'Pie Hole', 'visualizer' ),
					'pieHole',
					$this->pieHole,
					esc_html__( 'If between 0 and 1, displays a donut chart. The hole with have a radius equal to number times the radius of the chart. Only applicable when the chart is two-dimensional.', 'visualizer' ),
					'0.0'
				);

				self::_renderTextItem(
					esc_html__( 'Start Angle', 'visualizer' ),
					'pieStartAngle',
					$this->pieStartAngle,
					esc_html__( 'The angle, in degrees, to rotate the chart by. The default of 0 will orient the leftmost edge of the first slice directly up.', 'visualizer' ),
					0
				);

				self::_renderColorPickerItem(
					esc_html__( 'Slice Border Color', 'visualizer' ),
					'pieSliceBorderColor',
					$this->pieSliceBorderColor,
					'#fff'
				);
			self::_renderSectionEnd();
		self::_renderGroupEnd();
	}

	/**
	 * Renders residue settings group.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _renderResidueSettings() {
		self::_renderGroupStart( esc_html__( 'Residue Settings', 'visualizer' ) );
			self::_renderSectionStart();
				self::_renderTextItem(
					esc_html__( 'Visibility Threshold', 'visualizer' ),
					'sliceVisibilityThreshold',
					$this->sliceVisibilityThreshold,
					esc_html__( 'The slice relative part, below which a slice will not show individually. All slices that have not passed this threshold will be combined to a single slice, whose size is the sum of all their sizes. Default is not to show individually any slice which is smaller than half a degree.', 'visualizer' ),
					'0.001388889'
				);

				self::_renderTextItem(
					esc_html__( 'Residue Slice Label', 'visualizer' ),
					'pieResidueSliceLabel',
					$this->pieResidueSliceLabel,
					esc_html__( 'A label for the combination slice that holds all slices below slice visibility threshold.', 'visualizer' ),
					esc_html__( 'Other', 'visualizer' )
				);

				self::_renderColorPickerItem(
					esc_html__( 'Residue Slice Color', 'visualizer' ),
					'pieResidueSliceColor',
					$this->pieResidueSliceColor,
					'#ccc'
				);
			self::_renderSectionEnd();
		self::_renderGroupEnd();
	}

	/**
	 * Renders slices settings.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _renderSlicesSettings() {
		self::_renderGroupStart( esc_html__( 'Slices Settings', 'visualizer' ) );
		for ( $i = 0, $cnt = count( $this->__data ); $i < $cnt; $i++ ) {
			self::_renderSectionStart( esc_html( $this->__data[ $i ][0] ), false );
			self::_renderTextItem(
				esc_html__( 'Slice Offset', 'visualizer' ),
				'slices[' . $i . '][offset]',
				isset( $this->slices[ $i ]['color'] ) ? $this->slices[ $i ]['color'] : null,
				esc_html__( "How far to separate the slice from the rest of the pie, from 0.0 (not at all) to 1.0 (the pie's radius).", 'visualizer' ),
				'0.0'
			);

			self::_renderColorPickerItem(
				esc_html__( 'Slice Color', 'visualizer' ),
				'slices[' . $i . '][color]',
				isset( $this->slices[ $i ]['color'] ) ? $this->slices[ $i ]['color'] : null,
				null
			);
			self::_renderSectionEnd();
		}
		self::_renderGroupEnd();
	}

	/**
	 * Renders tooltip settings section.
	 *
	 * @since 1.4.0
	 *
	 * @access protected
	 */
	protected function _renderTooltipSettigns() {
		parent::_renderTooltipSettigns();

		self::_renderSelectItem(
			esc_html__( 'Text', 'visualizer' ),
			'tooltip[text]',
			isset( $this->tooltip['text'] ) ? $this->tooltip['text'] : null,
			array(
				''           => '',
				'both'       => esc_html__( 'Display both the absolute value of the slice and the percentage of the whole', 'visualizer' ),
				'value'      => esc_html__( 'Display only the absolute value of the slice', 'visualizer' ),
				'percentage' => esc_html__( 'Display only the percentage of the whole represented by the slice', 'visualizer' ),
			),
			esc_html__( 'Determines what information to display when the user hovers over a pie slice.', 'visualizer' )
		);
	}

}
