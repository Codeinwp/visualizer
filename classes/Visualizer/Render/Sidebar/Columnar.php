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
					esc_html__( 'Is Stacked', Visualizer_Plugin::NAME ),
					'isStacked',
					$this->isStacked,
					$this->_yesno,
					esc_html__( 'If set to yes, series elements are stacked.', Visualizer_Plugin::NAME )
				);
			self::_renderSectionEnd();
		self::_renderGroupEnd();
	}
	
}