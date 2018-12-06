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
class Visualizer_Render_Sidebar_Type_Gauge extends Visualizer_Render_Sidebar_Google {

	/**
	 * Renders template.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _toHTML() {
		$this->_supportsAnimation = false;
		$this->_renderGeneralSettings();
		$this->_renderGaugeSettings();
		$this->_renderViewSettings();
		$this->_renderAdvancedSettings();
	}

	/**
	 * Renders Gauge settings group.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _renderGaugeSettings() {
		self::_renderGroupStart( esc_html__( 'Gauge Settings', 'visualizer' ) );
			$this->_renderTickSettings();
			$this->_renderGreenColorSettings();
			$this->_renderYellowColorSettings();
			$this->_renderRedColorSettings();
		self::_renderGroupEnd();
	}

	/**
	 * Renders general settings group.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _renderGeneralSettings() {
		self::_renderGroupStart( esc_html__( 'General Settings', 'visualizer' ) );

			self::_renderSectionStart( esc_html__( 'Title', 'visualizer' ), false );
				self::_renderTextItem(
					esc_html__( 'Chart Title', 'visualizer' ),
					'title',
					$this->title,
					esc_html__( 'Text to display in the back-end admin area.', 'visualizer' )
				);
			self::_renderSectionEnd();

		self::_renderGroupEnd();
	}

	/**
	 * Renders tick settings group.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _renderTickSettings() {
		self::_renderSectionStart( esc_html__( 'Tick Settings', 'visualizer' ), false );

			echo '<div class="viz-section-item">';
				echo '<a class="more-info" href="javascript:;">[?]</a>';
				echo '<b>', esc_html__( 'Min And Max Values', 'visualizer' ), '</b>';

				echo '<table class="viz-section-table" cellspacing="0" cellpadding="0" border="0">';
					echo '<tr>';
						echo '<td class="viz-section-table-column">';
							echo '<input type="text" name="min" class="control-text" value="', esc_attr( $this->min ), '" placeholder="0">';
						echo '</td>';
						echo '<td class="viz-section-table-column">';
							echo '<input type="text" name="max" class="control-text" value="', esc_attr( $this->max ), '" placeholder="100">';
						echo '</td>';
					echo '</tr>';
				echo '</table>';

				echo '<p class="viz-section-description">';
					esc_html_e( 'The maximal and minimal values of the gauge.', 'visualizer' );
				echo '</p>';
			echo '</div>';

			self::_renderTextItem(
				esc_html__( 'Minor Ticks', 'visualizer' ),
				'minorTicks',
				$this->minorTicks,
				esc_html__( 'The number of minor tick section in each major tick section.', 'visualizer' ),
				2
			);

			$this->_renderFormatField();

		self::_renderSectionEnd();
	}

	/**
	 * Renders green color settings group.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _renderGreenColorSettings() {
			self::_renderSectionStart( esc_html__( 'Green Color', 'visualizer' ), false );
				self::_renderSectionDescription( esc_html__( 'Configure the green section of the gauge chart.', 'visualizer' ) );

				echo '<div class="viz-section-item">';
					echo '<a class="more-info" href="javascript:;">[?]</a>';
					echo '<b>', esc_html__( 'From And To Range', 'visualizer' ), '</b>';

					echo '<table class="viz-section-table" cellspacing="0" cellpadding="0" border="0">';
						echo '<tr>';
							echo '<td class="viz-section-table-column">';
								echo '<input type="text" name="greenFrom" class="control-text" value="', esc_attr( $this->greenFrom ), '">';
							echo '</td>';
							echo '<td class="viz-section-table-column">';
								echo '<input type="text" name="greenTo" class="control-text" value="', esc_attr( $this->greenTo ), '">';
							echo '</td>';
						echo '</tr>';
					echo '</table>';

					echo '<p class="viz-section-description">';
						esc_html_e( 'The lowest and highest values for a range marked by a green color.', 'visualizer' );
					echo '</p>';
				echo '</div>';

				self::_renderColorPickerItem(
					esc_html__( 'Green Color', 'visualizer' ),
					'greenColor',
					$this->greenColor,
					'#109618'
				);
			self::_renderSectionEnd();
	}

	/**
	 * Renders yellow color settings group.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _renderYellowColorSettings() {
			self::_renderSectionStart( esc_html__( 'Yellow Color', 'visualizer' ), false );
				self::_renderSectionDescription( esc_html__( 'Configure the yellow section of the gauge chart.', 'visualizer' ) );

				echo '<div class="viz-section-item">';
					echo '<a class="more-info" href="javascript:;">[?]</a>';
					echo '<b>', esc_html__( 'From And To Range', 'visualizer' ), '</b>';

					echo '<table class="viz-section-table" cellspacing="0" cellpadding="0" border="0">';
						echo '<tr>';
							echo '<td class="viz-section-table-column">';
								echo '<input type="text" name="yellowFrom" class="control-text" value="', esc_attr( $this->yellowFrom ), '">';
							echo '</td>';
							echo '<td class="viz-section-table-column">';
								echo '<input type="text" name="yellowTo" class="control-text" value="', esc_attr( $this->yellowTo ), '">';
							echo '</td>';
						echo '</tr>';
					echo '</table>';

					echo '<p class="viz-section-description">';
						esc_html_e( 'The lowest and highest values for a range marked by a yellow color.', 'visualizer' );
					echo '</p>';
				echo '</div>';

				self::_renderColorPickerItem(
					esc_html__( 'Yellow Color', 'visualizer' ),
					'yellowColor',
					$this->yellowColor,
					'#FF9900'
				);
			self::_renderSectionEnd();
	}

	/**
	 * Renders red color settings group.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _renderRedColorSettings() {
			self::_renderSectionStart( esc_html__( 'Red Color', 'visualizer' ), false );
				self::_renderSectionDescription( esc_html__( 'Configure the red section of the gauge chart.', 'visualizer' ) );

				echo '<div class="viz-section-item">';
					echo '<a class="more-info" href="javascript:;">[?]</a>';
					echo '<b>', esc_html__( 'From And To Range', 'visualizer' ), '</b>';

					echo '<table class="viz-section-table" cellspacing="0" cellpadding="0" border="0">';
						echo '<tr>';
							echo '<td class="viz-section-table-column">';
								echo '<input type="text" name="redFrom" class="control-text" value="', esc_attr( $this->redFrom ), '">';
							echo '</td>';
							echo '<td class="viz-section-table-column">';
								echo '<input type="text" name="redTo" class="control-text" value="', esc_attr( $this->redTo ), '">';
							echo '</td>';
						echo '</tr>';
					echo '</table>';

					echo '<p class="viz-section-description">';
						esc_html_e( 'The lowest and highest values for a range marked by a red color.', 'visualizer' );
					echo '</p>';
				echo '</div>';

				self::_renderColorPickerItem(
					esc_html__( 'Red Color', 'visualizer' ),
					'redColor',
					$this->redColor,
					'#DC3912'
				);
			self::_renderSectionEnd();
	}

	/**
	 * Renders chart view settings group.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _renderViewSettings() {
		self::_renderGroupStart( esc_html__( 'Layout & Chart Area', 'visualizer' ) );
			self::_renderSectionStart();
				self::_renderSectionDescription( esc_html__( 'Configure the total size of the chart. Two formats are supported: a number, or a number followed by %. A simple number is a value in pixels; a number followed by % is a percentage.', 'visualizer' ) );

				echo '<div class="viz-section-item">';
					echo '<a class="more-info" href="javascript:;">[?]</a>';
					echo '<b>', esc_html__( 'Width And Height Of Chart', 'visualizer' ), '</b>';

					echo '<table class="viz-section-table" cellspacing="0" cellpadding="0" border="0">';
						echo '<tr>';
							echo '<td class="viz-section-table-column">';
								echo '<input type="text" name="width" class="control-text" value="', esc_attr( $this->width ), '" placeholder="100%">';
							echo '</td>';
							echo '<td class="viz-section-table-column">';
								echo '<input type="text" name="height" class="control-text" value="', esc_attr( $this->height ), '" placeholder="400">';
							echo '</td>';
						echo '</tr>';
					echo '</table>';

					echo '<p class="viz-section-description">';
						esc_html_e( 'Determines the total width and height of the chart.', 'visualizer' );
					echo '</p>';
				echo '</div>';
			self::_renderSectionEnd();
		self::_renderGroupEnd();
	}

}
