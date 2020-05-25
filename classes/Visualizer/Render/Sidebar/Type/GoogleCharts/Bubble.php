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
 * Class for area chart sidebar settings.
 *
 * @category Visualizer
 * @package Render
 * @subpackage Sidebar
 *
 * @since 1.0.0
 */
class Visualizer_Render_Sidebar_Type_GoogleCharts_Bubble extends Visualizer_Render_Sidebar_Linear {

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
		$this->_includeCurveTypes = false;
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
		$this->_renderBubbleSettings();
		$this->_renderViewSettings();
		$this->_renderAdvancedSettings();
	}

	/**
	 * Renders bubble settings items.
	 *
	 * @since 3.4.0
	 *
	 * @access protected
	 */
	protected function _renderBubbleSettings() {
		self::_renderGroupStart( esc_html__( 'Bubble Settings', 'visualizer' ) );
			self::_renderSectionStart();
				self::_renderTextItem(
					esc_html__( 'Opacity', 'visualizer' ),
					'bubble[opacity]',
					isset( $this->bubble['opacity'] ) ? $this->bubble['opacity'] : 0.8,
					esc_html__( 'The default opacity of the bubbles, where 0.0 is fully transparent and 1.0 is fully opaque.', 'visualizer' ),
					0.8,
					'number',
					array( 'min' => 0.0, 'max' => 1.0, 'step' => 0.1 )
				);

				self::_renderColorPickerItem(
					esc_html__( 'Stroke Color', 'visualizer' ),
					'bubble[stroke]',
					isset( $this->bubble['stroke'] ) ? $this->bubble['stroke'] : null,
					null
				);

				self::_renderCheckboxItem(
					esc_html__( 'Sort Bubbles by Size', 'visualizer' ),
					'sortBubblesBySize',
					$this->sortBubblesBySize ? 1 : 0,
					1,
					esc_html__( 'If checked, sorts the bubbles by size so the smaller bubbles appear above the larger bubbles. If unchecked, bubbles are sorted according to their order in the table.', 'visualizer' )
				);

				self::_renderTextItem(
					esc_html__( 'Size (max)', 'visualizer' ),
					'sizeAxis[maxValue]',
					isset( $this->sizeAxis['maxValue'] ) ? $this->sizeAxis['maxValue'] : '',
					esc_html__( 'The size value (as appears in the chart data) to be mapped to sizeAxis.maxSize. Larger values will be cropped to this value.', 'visualizer' ),
					'',
					'number',
					array( 'step' => 1 )
				);

				self::_renderTextItem(
					esc_html__( 'Size (min)', 'visualizer' ),
					'sizeAxis[minValue]',
					isset( $this->sizeAxis['minValue'] ) ? $this->sizeAxis['minValue'] : '',
					esc_html__( 'The size value (as appears in the chart data) to be mapped to sizeAxis.minSize. Smaller values will be cropped to this value.', 'visualizer' ),
					'',
					'number',
					array( 'step' => 1 )
				);

			self::_renderSectionEnd();
		self::_renderGroupEnd();

	}


}
