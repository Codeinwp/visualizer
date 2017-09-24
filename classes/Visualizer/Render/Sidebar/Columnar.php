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
 * Base class for sidebar settings of columnar based charts.
 *
 * @category Visualizer
 * @package Render
 * @subpackage Sidebar
 *
 * @since 1.0.0
 * @abstract
 */
abstract class Visualizer_Render_Sidebar_Columnar extends Visualizer_Render_Sidebar_Graph {

	/**
	 * Renders columnar settings group.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _renderColumnarSettings() {
		self::_renderGroupStart( esc_html__( 'Bars Settings', 'visualizer' ) );
			self::_renderSectionStart();
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

				echo '<div class="viz-section-delimiter"></div>';

				self::_renderSelectItem(
					esc_html__( 'Is Stacked', 'visualizer' ),
					'isStacked',
					$this->isStacked,
					$this->_yesno,
					esc_html__( 'If set to yes, series elements are stacked.', 'visualizer' )
				);

				echo '<div class="viz-section-delimiter"></div>';

				self::_renderTextItem(
					esc_html__( 'Bars Opacity', 'visualizer' ),
					'dataOpacity',
					$this->dataOpacity,
					esc_html__( 'Bars transparency, with 1.0 being completely opaque and 0.0 fully transparent.', 'visualizer' ),
					'1.0'
				);
			self::_renderSectionEnd();
		self::_renderGroupEnd();
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
	}

	/**
	 * Renders general settings block for vertical axis settings.
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
	}

}
