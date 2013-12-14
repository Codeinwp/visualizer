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
		self::_renderGroupStart( esc_html__( 'Bars Settings', Visualizer_Plugin::NAME ) );
			self::_renderSectionStart();
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

				echo '<div class="section-delimiter"></div>';

				self::_renderSelectItem(
					esc_html__( 'Is Stacked', Visualizer_Plugin::NAME ),
					'isStacked',
					$this->isStacked,
					$this->_yesno,
					esc_html__( 'If set to yes, series elements are stacked.', Visualizer_Plugin::NAME )
				);

				echo '<div class="section-delimiter"></div>';

				self::_renderTextItem(
					esc_html__( 'Bars Opacity', Visualizer_Plugin::NAME ),
					'dataOpacity',
					$this->dataOpacity,
					esc_html__( 'Bars transparency, with 1.0 being completely opaque and 0.0 fully transparent.', Visualizer_Plugin::NAME ),
					'1.0'
				);
			self::_renderSectionEnd();
		self::_renderGroupEnd();
	}

}