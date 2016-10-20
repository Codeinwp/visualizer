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
		self::_renderGroupStart( esc_html__( 'Candles Settings', 'visualizer' ) );
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

				echo '<div class="section-delimiter"></div>';

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
			self::_renderSectionEnd();

			self::_renderSectionStart( esc_html__( 'Failing Candles', 'visualizer' ), false );
				self::_renderTextItem(
					esc_html__( 'Stroke Width', 'visualizer' ),
					'candlestick[fallingColor][strokeWidth]',
					isset( $this->candlestick['fallingColor']['strokeWidth'] ) ? $this->candlestick['fallingColor']['strokeWidth'] : null,
					esc_html__( 'The stroke width of falling candles.', 'visualizer' ),
					'2'
				);

				self::_renderColorPickerItem(
					esc_html__( 'Stroke Color', 'visualizer' ),
					'candlestick[fallingColor][stroke]',
					! empty( $this->candlestick['fallingColor']['stroke'] ) ? $this->candlestick['fallingColor']['stroke'] : null,
					null
				);

				self::_renderColorPickerItem(
					esc_html__( 'Fill Color', 'visualizer' ),
					'candlestick[fallingColor][fill]',
					! empty( $this->candlestick['fallingColor']['fill'] ) ? $this->candlestick['fallingColor']['fill'] : null,
					null
				);
			self::_renderSectionEnd();

			self::_renderSectionStart( esc_html__( 'Rising Candles', 'visualizer' ), false );
				self::_renderTextItem(
					esc_html__( 'Stroke Width', 'visualizer' ),
					'candlestick[risingColor][strokeWidth]',
					isset( $this->candlestick['risingColor']['strokeWidth'] ) ? $this->candlestick['risingColor']['strokeWidth'] : null,
					esc_html__( 'The stroke width of rising candles.', 'visualizer' ),
					'2'
				);

				self::_renderColorPickerItem(
					esc_html__( 'Stroke Color', 'visualizer' ),
					'candlestick[risingColor][stroke]',
					! empty( $this->candlestick['risingColor']['stroke'] ) ? $this->candlestick['risingColor']['stroke'] : null,
					null
				);

				self::_renderColorPickerItem(
					esc_html__( 'Fill Color', 'visualizer' ),
					'candlestick[risingColor][fill]',
					! empty( $this->candlestick['risingColor']['fill'] ) ? $this->candlestick['risingColor']['fill'] : null,
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

		self::_renderColorPickerItem(
			esc_html__( 'Color', 'visualizer' ),
			'series[' . $index . '][color]',
			isset( $this->series[ $index ]['color'] ) ? $this->series[ $index ]['color'] : null,
			null
		);
	}

}
