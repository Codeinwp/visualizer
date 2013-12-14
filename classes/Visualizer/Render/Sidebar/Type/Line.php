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
 * Class for line chart sidebar settings.
 *
 * @category Visualizer
 * @package Render
 * @subpackage Sidebar
 *
 * @since 1.0.0
 */
class Visualizer_Render_Sidebar_Type_Line extends Visualizer_Render_Sidebar_Linear {

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
	 * Renders line settings items.
	 *
	 * @since 1.4.0
	 *
	 * @access protected
	 */
	protected function _renderLineSettingsItems() {
		parent::_renderLineSettingsItems();

		echo '<div class="section-delimiter"></div>';

		self::_renderSelectItem(
			esc_html__( 'Interpolate Nulls', Visualizer_Plugin::NAME ),
			'interpolateNulls',
			$this->interpolateNulls,
			$this->_yesno,
			esc_html__( 'Whether to guess the value of missing points. If yes, it will guess the value of any missing data based on neighboring points. If no, it will leave a break in the line at the unknown point.', Visualizer_Plugin::NAME )
		);
	}

}