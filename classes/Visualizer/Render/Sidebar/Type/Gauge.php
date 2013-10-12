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
 * Class for gauge chart sidebar settings.
 *
 * @category Visualizer
 * @package Render
 * @subpackage Sidebar
 *
 * @since 1.0.0
 */
class Visualizer_Render_Sidebar_Type_Gauge extends Visualizer_Render_Sidebar {

	/**
	 * Renders template.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _toHTML() {
		$this->_renderGeneralSettings();
		$this->_renderGreenColorSettings();
		$this->_renderYellowColorSettings();
		$this->_renderRedColorSettings();
		$this->_renderViewSettings();
	}

	/**
	 * Renders chart general settings group.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _renderGeneralSettings() {
		self::_renderGroupStart( esc_html__( 'General Settings', Visualizer_Plugin::NAME ) );
			self::_renderSectionStart();

				echo '<div class="section-item">';
					echo '<a class="more-info" href="javascript:;">[?]</a>';
					echo '<b>', esc_html__( 'Min And Max Values', Visualizer_Plugin::NAME ), '</b>';

					echo '<table class="section-table" cellspacing="0" cellpadding="0" border="0">';
						echo '<tr>';
							echo '<td class="section-table-column">';
								echo '<input type="text" name="min" class="control-text" value="', esc_attr( $this->min ), '" placeholder="0">';
							echo '</td>';
							echo '<td class="section-table-column">';
								echo '<input type="text" name="max" class="control-text" value="', esc_attr( $this->max ), '" placeholder="100">';
							echo '</td>';
						echo '</tr>';
					echo '</table>';

					echo '<p class="section-description">';
						esc_html_e( 'The maximal and minimal values of the gauge.', Visualizer_Plugin::NAME );
					echo '</p>';
				echo '</div>';

				self::_renderTextItem(
					esc_html__( 'Minor Ticks', Visualizer_Plugin::NAME ),
					'minorTicks',
					$this->minorTicks,
					esc_html__( 'The number of minor tick section in each major tick section.', Visualizer_Plugin::NAME ),
					2
				);

				$this->_renderFormatField();

			self::_renderSectionEnd();
		self::_renderGroupEnd();
	}

	/**
	 * Renders green color settings group.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _renderGreenColorSettings() {
		self::_renderGroupStart( esc_html__( 'Green Color', Visualizer_Plugin::NAME ) );
			self::_renderSectionStart();
				self::_renderSectionDescription( esc_html__( 'Configure the green section of the gauge chart.', Visualizer_Plugin::NAME ) );

				echo '<div class="section-item">';
					echo '<a class="more-info" href="javascript:;">[?]</a>';
					echo '<b>', esc_html__( 'From And To Range', Visualizer_Plugin::NAME ), '</b>';

					echo '<table class="section-table" cellspacing="0" cellpadding="0" border="0">';
						echo '<tr>';
							echo '<td class="section-table-column">';
								echo '<input type="text" name="greenFrom" class="control-text" value="', esc_attr( $this->greenFrom ), '">';
							echo '</td>';
							echo '<td class="section-table-column">';
								echo '<input type="text" name="greenTo" class="control-text" value="', esc_attr( $this->greenTo ), '">';
							echo '</td>';
						echo '</tr>';
					echo '</table>';

					echo '<p class="section-description">';
						esc_html_e( 'The lowest and highest values for a range marked by a green color.', Visualizer_Plugin::NAME );
					echo '</p>';
				echo '</div>';

				self::_renderColorPickerItem(
					esc_html__( 'Green Color', Visualizer_Plugin::NAME ),
					'greenColor',
					$this->greenColor,
					'#109618'
				);
			self::_renderSectionEnd();
		self::_renderGroupEnd();
	}

	/**
	 * Renders yellow color settings group.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _renderYellowColorSettings() {
		self::_renderGroupStart( esc_html__( 'Yellow Color', Visualizer_Plugin::NAME ) );
			self::_renderSectionStart();
				self::_renderSectionDescription( esc_html__( 'Configure the yellow section of the gauge chart.', Visualizer_Plugin::NAME ) );

				echo '<div class="section-item">';
					echo '<a class="more-info" href="javascript:;">[?]</a>';
					echo '<b>', esc_html__( 'From And To Range', Visualizer_Plugin::NAME ), '</b>';

					echo '<table class="section-table" cellspacing="0" cellpadding="0" border="0">';
						echo '<tr>';
							echo '<td class="section-table-column">';
								echo '<input type="text" name="yellowFrom" class="control-text" value="', esc_attr( $this->yellowFrom ), '">';
							echo '</td>';
							echo '<td class="section-table-column">';
								echo '<input type="text" name="yellowTo" class="control-text" value="', esc_attr( $this->yellowTo ), '">';
							echo '</td>';
						echo '</tr>';
					echo '</table>';

					echo '<p class="section-description">';
						esc_html_e( 'The lowest and highest values for a range marked by a yellow color.', Visualizer_Plugin::NAME );
					echo '</p>';
				echo '</div>';

				self::_renderColorPickerItem(
					esc_html__( 'Yellow Color', Visualizer_Plugin::NAME ),
					'yellowColor',
					$this->yellowColor,
					'#FF9900'
				);
			self::_renderSectionEnd();
		self::_renderGroupEnd();
	}

	/**
	 * Renders red color settings group.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _renderRedColorSettings() {
		self::_renderGroupStart( esc_html__( 'Red Color', Visualizer_Plugin::NAME ) );
			self::_renderSectionStart();
				self::_renderSectionDescription( esc_html__( 'Configure the red section of the gauge chart.', Visualizer_Plugin::NAME ) );

				echo '<div class="section-item">';
					echo '<a class="more-info" href="javascript:;">[?]</a>';
					echo '<b>', esc_html__( 'From And To Range', Visualizer_Plugin::NAME ), '</b>';

					echo '<table class="section-table" cellspacing="0" cellpadding="0" border="0">';
						echo '<tr>';
							echo '<td class="section-table-column">';
								echo '<input type="text" name="redFrom" class="control-text" value="', esc_attr( $this->redFrom ), '">';
							echo '</td>';
							echo '<td class="section-table-column">';
								echo '<input type="text" name="redTo" class="control-text" value="', esc_attr( $this->redTo ), '">';
							echo '</td>';
						echo '</tr>';
					echo '</table>';

					echo '<p class="section-description">';
						esc_html_e( 'The lowest and highest values for a range marked by a red color.', Visualizer_Plugin::NAME );
					echo '</p>';
				echo '</div>';

				self::_renderColorPickerItem(
					esc_html__( 'Red Color', Visualizer_Plugin::NAME ),
					'redColor',
					$this->redColor,
					'#DC3912'
				);
			self::_renderSectionEnd();
		self::_renderGroupEnd();
	}

	/**
	 * Renders chart view settings group.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _renderViewSettings() {
		self::_renderGroupStart( esc_html__( 'Layout & Chart Area', Visualizer_Plugin::NAME ) );
			self::_renderSectionStart();
				self::_renderSectionDescription( esc_html__( 'Configure the total size of the chart. Two formats are supported: a number, or a number followed by %. A simple number is a value in pixels; a number followed by % is a percentage.', Visualizer_Plugin::NAME ) );

				echo '<div class="section-item">';
					echo '<a class="more-info" href="javascript:;">[?]</a>';
					echo '<b>', esc_html__( 'Width And Height Of Chart', Visualizer_Plugin::NAME ), '</b>';

					echo '<table class="section-table" cellspacing="0" cellpadding="0" border="0">';
						echo '<tr>';
							echo '<td class="section-table-column">';
								echo '<input type="text" name="width" class="control-text" value="', esc_attr( $this->width ), '" placeholder="100%">';
							echo '</td>';
							echo '<td class="section-table-column">';
								echo '<input type="text" name="height" class="control-text" value="', esc_attr( $this->height ), '" placeholder="400">';
							echo '</td>';
						echo '</tr>';
					echo '</table>';

					echo '<p class="section-description">';
						esc_html_e( 'Determines the total width and height of the chart.', Visualizer_Plugin::NAME );
					echo '</p>';
				echo '</div>';
			self::_renderSectionEnd();
		self::_renderGroupEnd();
	}

}