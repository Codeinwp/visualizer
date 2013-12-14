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
 * Base class for all chart sidebar groups.
 *
 * @category Visualizer
 * @package Render
 *
 * @since 1.0.0
 * @abstract
 */
abstract class Visualizer_Render_Sidebar extends Visualizer_Render {

	/**
	 * The array of font families accepted by visualization API.
	 *
	 * @since 1.0.0
	 *
	 * @static
	 * @access protected
	 * @var array
	 */
	protected static $_fontFamilies = array(
		'Arial'         => 'Arial',
		'Sans Serif'    => 'Sans Serif',
		'serif'         => 'Serif',
		'Arial black'   => 'Wide',
		'Arial Narrow'  => 'Narrow',
		'Comic Sans MS' => 'Comic Sans MS',
		'Courier New'   => 'Courier New',
		'Garamond'      => 'Garamond',
		'Georgia'       => 'Georgia',
		'Tahoma'        => 'Tahoma',
		'Verdana'       => 'Verdana',
	);

	/**
	 * The Yes/No array.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @var array
	 */
	protected $_yesno;

	/**
	 * The array of available legend positions.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @var array
	 */
	protected $_legendPositions;

	/**
	 * The array of available alignments.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @var array
	 */
	protected $_alignments;

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

		$this->_legendPositions = array(
			''       => '',
			'right'  => esc_html__( 'Right of the chart', Visualizer_Plugin::NAME ),
			'top'    => esc_html__( 'Above the chart', Visualizer_Plugin::NAME ),
			'bottom' => esc_html__( 'Below the chart', Visualizer_Plugin::NAME ),
			'in'     => esc_html__( 'Inside the chart', Visualizer_Plugin::NAME ),
			'none'   => esc_html__( 'Omit the legend', Visualizer_Plugin::NAME ),
		);

		$this->_alignments = array(
			''       => '',
			'start'  => esc_html__( 'Aligned to the start of the allocated area', Visualizer_Plugin::NAME ),
			'center' => esc_html__( 'Centered in the allocated area', Visualizer_Plugin::NAME ),
			'end'    => esc_html__( 'Aligned to the end of the allocated area', Visualizer_Plugin::NAME ),
		);

		$this->_yesno = array(
			''  => '',
			'1' => esc_html__( 'Yes', Visualizer_Plugin::NAME ),
			'0' => esc_html__( 'No', Visualizer_Plugin::NAME ),
		);
	}

	/**
	 * Renders chart title settings.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _renderChartTitleSettings() {
		self::_renderTextItem(
			esc_html__( 'Chart Title', Visualizer_Plugin::NAME ),
			'title',
			$this->title,
			esc_html__( 'Text to display above the chart.', Visualizer_Plugin::NAME )
		);

		self::_renderColorPickerItem(
			esc_html__( 'Chart Title Color', Visualizer_Plugin::NAME ),
			'titleTextStyle[color]',
			isset( $this->titleTextStyle['color'] ) ? $this->titleTextStyle['color'] : null,
			'#000'
		);
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
				self::_renderSectionDescription( esc_html__( 'Configure title, font styles, tooltip, legend and else settings for the chart.', Visualizer_Plugin::NAME ) );
			self::_renderSectionEnd();

			self::_renderSectionStart( esc_html__( 'Title', Visualizer_Plugin::NAME ), false );
				$this->_renderChartTitleSettings();
			self::_renderSectionEnd();

			self::_renderSectionStart( esc_html__( 'Font Styles' ), false );
				echo '<div class="section-item">';
					echo '<a class="more-info" href="javascript:;">[?]</a>';
					echo '<b>', esc_html__( 'Family And Size', Visualizer_Plugin::NAME ), '</b>';

					echo '<table class="section-table" cellspacing="0" cellpadding="0" border="0">';
						echo '<tr>';
							echo '<td class="section-table-column">';
								echo '<select name="fontName" class="control-select">';
									echo '<option></option>';
									foreach ( self::$_fontFamilies as $font => $label ) {
										echo '<option value="', $font, '"', selected( $font, $this->fontName, false ), '>';
											echo $label;
										echo '</option>';
									}
								echo '</select>';
							echo '</td>';
							echo '<td class="section-table-column">';
								echo '<select name="fontSize" class="control-select">';
									echo '<option></option>';
									for	( $i = 7; $i <= 20; $i++ ) {
										echo '<option value="', $i, '"', selected( $i, $this->fontSize, false ), '>', $i, '</option>';
									}
								echo '</select>';
							echo '</td>';
						echo '</tr>';
					echo '</table>';

					echo '<p class="section-description">';
						esc_html_e( 'The default font family and size for all text in the chart.', Visualizer_Plugin::NAME );
					echo '</p>';
				echo '</div>';
			self::_renderSectionEnd();

			self::_renderSectionStart( esc_html__( 'Legend', Visualizer_Plugin::NAME ), false );
				self::_renderSelectItem(
					esc_html__( 'Position', Visualizer_Plugin::NAME ),
					'legend[position]',
					$this->legend['position'],
					$this->_legendPositions,
					esc_html__( 'Determines where to place the legend, compared to the chart area.', Visualizer_Plugin::NAME )
				);

				self::_renderSelectItem(
					esc_html__( 'Alignment', Visualizer_Plugin::NAME ),
					'legend[alignment]',
					$this->legend['alignment'],
					$this->_alignments,
					esc_html__( 'Determines the alignment of the legend.', Visualizer_Plugin::NAME )
				);

				self::_renderColorPickerItem(
					esc_html__( 'Font Color', Visualizer_Plugin::NAME ),
					'legend[textStyle][color]',
					isset( $this->legend['textStyle']['color'] ) ? $this->legend['textStyle']['color'] : null,
					'#000'
				);
			self::_renderSectionEnd();

			self::_renderSectionStart( esc_html__( 'Tooltip', Visualizer_Plugin::NAME ), false );
				$this->_renderTooltipSettigns();
			self::_renderSectionEnd();
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
		self::_renderSelectItem(
			esc_html__( 'Trigger', Visualizer_Plugin::NAME ),
			'tooltip[trigger]',
			isset( $this->tooltip['trigger'] ) ? $this->tooltip['trigger'] : null,
			array(
				''          => '',
				'focus'     => esc_html__( 'The tooltip will be displayed when the user hovers over an element', Visualizer_Plugin::NAME ),
				'selection' => esc_html__( 'The tooltip will be displayed when the user selects an element', Visualizer_Plugin::NAME ),
				'none'      => esc_html__( 'The tooltip will not be displayed', Visualizer_Plugin::NAME ),
			),
			esc_html__( 'Determines the user interaction that causes the tooltip to be displayed.', Visualizer_Plugin::NAME )
		);

		self::_renderSelectItem(
			esc_html__( 'Show Color Code', Visualizer_Plugin::NAME ),
			'tooltip[showColorCode]',
			isset( $this->tooltip['showColorCode'] ) ? $this->tooltip['showColorCode'] : null,
			$this->_yesno,
			esc_html__( 'If set to yes, will show colored squares next to the slice information in the tooltip.', Visualizer_Plugin::NAME )
		);
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
			self::_renderSectionStart( esc_html__( 'Layout', Visualizer_Plugin::NAME ), false );
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

				echo '<div class="section-delimiter"></div>';

				self::_renderSectionDescription( esc_html__( 'Configure the background color for the main area of the chart and the chart border width and color.', Visualizer_Plugin::NAME ) );

				self::_renderTextItem(
					esc_html__( 'Stroke Width', Visualizer_Plugin::NAME ),
					'backgroundColor[strokeWidth]',
					isset( $this->backgroundColor['strokeWidth'] ) ? $this->backgroundColor['strokeWidth'] : null,
					esc_html__( 'The chart border width in pixels.', Visualizer_Plugin::NAME ),
					'0'
				);

				self::_renderColorPickerItem(
					esc_html__( 'Stroke Color', Visualizer_Plugin::NAME ),
					'backgroundColor[stroke]',
					!empty( $this->backgroundColor['stroke'] ) ? $this->backgroundColor['stroke'] : null,
					'#666'
				);

				$background_color = !empty( $this->backgroundColor['fill'] ) ? $this->backgroundColor['fill'] : null;
				self::_renderColorPickerItem(
					esc_html__( 'Background Color', Visualizer_Plugin::NAME ),
					'backgroundColor[fill]',
					$background_color,
					'#fff'
				);

				echo '<div class="section-item">';
					echo '<label>';
						echo '<input type="checkbox" class="control-checkbox" name="backgroundColor[fill]" value="transparent"', checked( $background_color, 'transparent', false ), '> ';
						esc_html_e( 'Transparent background' );
					echo '</label>';
				echo '</div>';
			self::_renderSectionEnd();

			self::_renderSectionStart( esc_html__( 'Chart Area', Visualizer_Plugin::NAME ), false );
				self::_renderSectionDescription( esc_html__( 'Configure the placement and size of the chart area (where the chart itself is drawn, excluding axis and legends). Two formats are supported: a number, or a number followed by %. A simple number is a value in pixels; a number followed by % is a percentage.', Visualizer_Plugin::NAME ) );

				echo '<div class="section-item">';
					echo '<a class="more-info" href="javascript:;">[?]</a>';
					echo '<b>', esc_html__( 'Left And Top Margins', Visualizer_Plugin::NAME ), '</b>';

					echo '<table class="section-table" cellspacing="0" cellpadding="0" border="0">';
						echo '<tr>';
							echo '<td class="section-table-column">';
								echo '<input type="text" name="chartArea[left]" class="control-text" value="', !empty( $this->chartArea['left'] ) ? esc_attr( $this->chartArea['left'] ) : '', '" placeholder="20%">';
							echo '</td>';
							echo '<td class="section-table-column">';
								echo '<input type="text" name="chartArea[top]" class="control-text" value="', !empty( $this->chartArea['top'] ) ? esc_attr( $this->chartArea['top'] ) : '', '" placeholder="20%">';
							echo '</td>';
						echo '</tr>';
					echo '</table>';

					echo '<p class="section-description">';
						esc_html_e( 'Determines how far to draw the chart from the left and top borders.', Visualizer_Plugin::NAME );
					echo '</p>';
				echo '</div>';

				echo '<div class="section-item">';
					echo '<a class="more-info" href="javascript:;">[?]</a>';
					echo '<b>', esc_html__( 'Width And Height Of Chart Area', Visualizer_Plugin::NAME ), '</b>';

					echo '<table class="section-table" cellspacing="0" cellpadding="0" border="0">';
						echo '<tr>';
							echo '<td class="section-table-column">';
								echo '<input type="text" name="chartArea[width]" class="control-text" value="', !empty( $this->chartArea['width'] ) ? esc_attr( $this->chartArea['width'] ) : '', '" placeholder="60%">';
							echo '</td>';
							echo '<td class="section-table-column">';
								echo '<input type="text" name="chartArea[height]" class="control-text" value="', !empty( $this->chartArea['height'] ) ? esc_attr( $this->chartArea['height'] ) : '', '" placeholder="60%">';
							echo '</td>';
						echo '</tr>';
					echo '</table>';

					echo '<p class="section-description">';
						esc_html_e( 'Determines the width and hight of the chart area.', Visualizer_Plugin::NAME );
					echo '</p>';
				echo '</div>';
			self::_renderSectionEnd();
		self::_renderGroupEnd();
	}

	/**
	 * Renders select item.
	 *
	 * @since 1.0.0
	 *
	 * @static
	 * @access protected
	 * @param string $title The title of the select item.
	 * @param string $name The name of the select item.
	 * @param string $value The actual value of the select item.
	 * @param array $options The array of select options.
	 * @param string $desc The description of the select item.
	 */
	protected static function _renderSelectItem( $title, $name, $value, array $options, $desc ) {
		echo '<div class="section-item">';
			echo '<a class="more-info" href="javascript:;">[?]</a>';
			echo '<b>', $title, '</b>';
			echo '<select class="control-select" name="', $name, '">';
				foreach ( $options as $key => $label ) {
					echo '<option value="', $key, '"', selected( $key, $value, false ), '>';
						echo $label;
					echo '</option>';
				}
			echo '</select>';
			echo '<p class="section-description">', $desc, '</p>';
		echo '</div>';
	}

	/**
	 * Renders color picker item.
	 *
	 * @since 1.0.0
	 *
	 * @static
	 * @access protected
	 * @param string $title The title of the select item.
	 * @param string $name The name of the select item.
	 * @param string $value The actual value of the select item.
	 * @param string $default The default value of the color picker.
	 */
	protected static function _renderColorPickerItem( $title, $name, $value, $default ) {
		echo '<div class="section-item">';
			echo '<b>', $title, '</b>';
			echo '<div>';
				echo '<input type="text" class="color-picker-hex" name="', $name, '" maxlength="7" placeholder="', esc_attr__( 'Hex Value', Visualizer_Plugin::NAME ), '" value="', is_null( $value ) ? $default : esc_attr( $value ), '" data-default-color="', $default, '">';
			echo '</div>';
		echo '</div>';
	}

	/**
	 * Renders text item.
	 *
	 * @since 1.0.0
	 *
	 * @static
	 * @access protected
	 * @param string $title The title of the select item.
	 * @param string $name The name of the select item.
	 * @param string $value The actual value of the select item.
	 * @param string $desc The description of the select item.
	 * @param string $placeholder The placeholder for the input.
	 */
	protected static function _renderTextItem( $title, $name, $value, $desc, $placeholder = '' ) {
		echo '<div class="section-item">';
			echo '<a class="more-info" href="javascript:;">[?]</a>';
			echo '<b>', $title, '</b>';
			echo '<input type="text" class="control-text" name="', $name, '" value="', esc_attr( $value ), '" placeholder="', $placeholder, '">';
			echo '<p class="section-description">', $desc, '</p>';
		echo '</div>';
	}

	/**
	 * Renders the beginning of a group.
	 *
	 * @since 1.0.0
	 *
	 * @static
	 * @access protected
	 * @param string $title The title of this group.
	 */
	protected static function _renderGroupStart( $title ) {
		echo '<li class="group">';
			echo '<h3 class="group-title">', $title, '</h3>';
			echo '<ul class="group-content">';
	}

	/**
	 * Renders the ending of a group.
	 *
	 * @since 1.0.0
	 *
	 * @static
	 * @access protected
	 */
	protected static function _renderGroupEnd() {
			echo '</ul>';
		echo '</li>';
	}

	/**
	 * Renders the beginning of a section.
	 *
	 * @since 1.0.0
	 *
	 * @static
	 * @access protected
	 * @param string $title The title of this section. If the title is empty, no title will be displayed.
	 * @param boolean $open Determines whether the section items block has to be expanded or collapsed.
	 */
	protected static function _renderSectionStart( $title = false, $open = true ) {
		echo '<li>';
			if ( !empty( $title ) ) {
				echo '<span class="section-title">', $title, '</span>';
			}
			echo '<div class="section-items', $open ? ' open' : '', '">';
	}

	/**
	 * Renders the ending of a section.
	 *
	 * @since 1.0.0
	 *
	 * @static
	 * @access protected
	 */
	protected static function _renderSectionEnd() {
			echo '</div>';
		echo '</li>';
	}

	/**
	 * Renders section description block.
	 *
	 * @since 1.0.0
	 *
	 * @static
	 * @access protected
	 * @param string $description The description text.
	 */
	protected static function _renderSectionDescription( $description ) {
		echo '<div class="section-item">';
			echo '<div class="section-description">', $description, '</div>';
		echo '</div>';
	}

	/**
	 * Renders format field according to series type.
	 *
	 * @since 1.3.0
	 *
	 * @access protected
	 * @param int $index The index of the series.
	 */
	protected function _renderFormatField( $index = 0 ) {
		switch ( $this->__series[$index + 1]['type'] ) {
			case 'number':
				self::_renderTextItem(
					esc_html__( 'Number Format', Visualizer_Plugin::NAME ),
					'series[' . $index . '][format]',
					isset( $this->series[$index]['format'] ) ? $this->series[$index]['format'] : '',
					sprintf( esc_html__( 'Enter custom format pattern to apply to this series value, similar to the %sICU pattern set%s. Use something like #,### to get 1,234 as output, or $# to add dollar sign before digits. Pay attention that if you use #%% percentage format then your values will be multiplied by 100.', Visualizer_Plugin::NAME ), '<a href="http://icu-project.org/apiref/icu4c/classDecimalFormat.html#_details" target="_blank">', '</a>' ),
					'#,###.##'
				);
				break;
			case 'date':
			case 'datetime':
			case 'timeofday':
				self::_renderTextItem(
					esc_html__( 'Date Format', Visualizer_Plugin::NAME ),
					'series[' . $index . '][format]',
					isset( $this->series[$index]['format'] ) ? $this->series[$index]['format'] : '',
					sprintf( esc_html__( 'Enter custom format pattern to apply to this series value, similar to the %sICU date and time format%s.', Visualizer_Plugin::NAME ), '<a href="http://userguide.icu-project.org/formatparse/datetime#TOC-Date-Time-Format-Syntax" target="_blank">', '</a>' ),
					'eeee, dd LLLL yyyy'
				);
				break;
		}
	}

}