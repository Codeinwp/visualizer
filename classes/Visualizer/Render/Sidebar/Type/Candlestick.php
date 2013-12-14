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
 * Class for candlestick chart sidebar settings.
 *
 * @category Visualizer
 * @package Render
 * @subpackage Sidebar
 *
 * @since 1.0.0
 */
class Visualizer_Render_Sidebar_Type_Candlestick extends Visualizer_Render_Sidebar_Linear {

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
	 * Renders lines settings.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _renderLineSettings() {
		self::_renderGroupStart( esc_html__( 'Candles Settings', Visualizer_Plugin::NAME ) );
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
					esc_html__( 'Selection Mode', Visualizer_Plugin::NAME ),
					'selectionMode',
					$this->selectionMode,
					array(
						''         => '',
						'single'   => esc_html__( 'Single data point', Visualizer_Plugin::NAME ),
						'multiple' => esc_html__( 'Multiple data points', Visualizer_Plugin::NAME ),
					),
					esc_html__( 'Determines how many data points an user can select on a chart.', Visualizer_Plugin::NAME )
				);

				self::_renderSelectItem(
					esc_html__( 'Aggregation Target', Visualizer_Plugin::NAME ),
					'aggregationTarget',
					$this->aggregationTarget,
					array(
						''         => '',
						'category' => esc_html__( 'Group selected data by x-value', Visualizer_Plugin::NAME ),
						'series'   => esc_html__( 'Group selected data by series', Visualizer_Plugin::NAME ),
						'auto'     => esc_html__( 'Group selected data by x-value if all selections have the same x-value, and by series otherwise', Visualizer_Plugin::NAME ),
						'none'     => esc_html__( 'Show only one tooltip per selection', Visualizer_Plugin::NAME ),
					),
					esc_html__( 'Determines how multiple data selections are rolled up into tooltips. To make it working you need to set multiple selection mode and tooltip trigger to display it when an user selects an element.', Visualizer_Plugin::NAME )
				);
			self::_renderSectionEnd();

			self::_renderSectionStart( esc_html__( 'Failing Candles', Visualizer_Plugin::NAME ), false );
				self::_renderTextItem(
					esc_html__( 'Stroke Width', Visualizer_Plugin::NAME ),
					'candlestick[fallingColor][strokeWidth]',
					isset( $this->candlestick['fallingColor']['strokeWidth'] ) ? $this->candlestick['fallingColor']['strokeWidth'] : null,
					esc_html__( 'The stroke width of falling candles.', Visualizer_Plugin::NAME ),
					'2'
				);

				self::_renderColorPickerItem(
					esc_html__( 'Stroke Color', Visualizer_Plugin::NAME ),
					'candlestick[fallingColor][stroke]',
					!empty( $this->candlestick['fallingColor']['stroke'] ) ? $this->candlestick['fallingColor']['stroke'] : null,
					null
				);

				self::_renderColorPickerItem(
					esc_html__( 'Fill Color', Visualizer_Plugin::NAME ),
					'candlestick[fallingColor][fill]',
					!empty( $this->candlestick['fallingColor']['fill'] ) ? $this->candlestick['fallingColor']['fill'] : null,
					null
				);
			self::_renderSectionEnd();

			self::_renderSectionStart( esc_html__( 'Rising Candles', Visualizer_Plugin::NAME ), false );
				self::_renderTextItem(
					esc_html__( 'Stroke Width', Visualizer_Plugin::NAME ),
					'candlestick[risingColor][strokeWidth]',
					isset( $this->candlestick['risingColor']['strokeWidth'] ) ? $this->candlestick['risingColor']['strokeWidth'] : null,
					esc_html__( 'The stroke width of rising candles.', Visualizer_Plugin::NAME ),
					'2'
				);

				self::_renderColorPickerItem(
					esc_html__( 'Stroke Color', Visualizer_Plugin::NAME ),
					'candlestick[risingColor][stroke]',
					!empty( $this->candlestick['risingColor']['stroke'] ) ? $this->candlestick['risingColor']['stroke'] : null,
					null
				);

				self::_renderColorPickerItem(
					esc_html__( 'Fill Color', Visualizer_Plugin::NAME ),
					'candlestick[risingColor][fill]',
					!empty( $this->candlestick['risingColor']['fill'] ) ? $this->candlestick['risingColor']['fill'] : null,
					null
				);
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

		self::_renderColorPickerItem(
			esc_html__( 'Color', Visualizer_Plugin::NAME ),
			'series[' . $index . '][color]',
			isset( $this->series[$index]['color'] ) ? $this->series[$index]['color'] : null,
			null
		);
	}

}