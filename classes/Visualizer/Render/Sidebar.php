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
	 * Whether this chart supports animation or not.
	 *
	 * @access protected
	 * @var bool
	 */
	protected $_supportsAnimation = true;

	/**
	 * Which library does this this chart implement?
	 *
	 * @access protected
	 * @var string
	 */
	protected $_library = null;

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
			'left'  => esc_html__( 'Left of the chart', 'visualizer' ),
			'right'  => esc_html__( 'Right of the chart', 'visualizer' ),
			'top'    => esc_html__( 'Above the chart', 'visualizer' ),
			'bottom' => esc_html__( 'Below the chart', 'visualizer' ),
			'in'     => esc_html__( 'Inside the chart', 'visualizer' ),
			'none'   => esc_html__( 'Omit the legend', 'visualizer' ),
		);

		$this->_alignments = array(
			''       => '',
			'start'  => esc_html__( 'Aligned to the start of the allocated area', 'visualizer' ),
			'center' => esc_html__( 'Centered in the allocated area', 'visualizer' ),
			'end'    => esc_html__( 'Aligned to the end of the allocated area', 'visualizer' ),
		);

		$this->_yesno = array(
			''  => '',
			'1' => esc_html__( 'Yes', 'visualizer' ),
			'0' => esc_html__( 'No', 'visualizer' ),
		);

		$this->hooks();
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
			esc_html__( 'Chart Title', 'visualizer' ),
			'title',
			$this->title,
			esc_html__( 'Text to display above the chart.', 'visualizer' )
		);

		self::_renderColorPickerItem(
			esc_html__( 'Chart Title Color', 'visualizer' ),
			'titleTextStyle[color]',
			isset( $this->titleTextStyle['color'] ) ? $this->titleTextStyle['color'] : null,
			'#000'
		);
	}

	/**
	 * Renders chart advanced settings group.
	 *
	 * @access protected
	 */
	protected function _renderAdvancedSettings() {
		self::_renderGroupStart( esc_html__( 'Frontend Actions', 'visualizer' ) );
			self::_renderSectionStart();
				self::_renderSectionDescription( esc_html__( 'Configure frontend actions here.', 'visualizer' ) );
			self::_renderSectionEnd();

			$this->_renderActionSettings();
		self::_renderGroupEnd();

		self::_renderGroupStart( esc_html__( 'Manual Configuration', 'visualizer' ) );
			self::_renderSectionStart();
				self::_renderSectionDescription( '<span class="viz-gvlink">' . sprintf( __( 'Configure the graph by providing configuration variables right from the %1$sGoogle Visualization API%2$s. You can refer to to some examples %3$shere%4$s.', 'visualizer' ), '<a href="https://developers.google.com/chart/interactive/docs/gallery/?#configuration-options" target="_blank">', '</a>', '<a href="https://docs.themeisle.com/article/728-manual-configuration" target="_blank">', '</a>' ) . '</span>' );

			$example    = '
{
	"vAxis": {
		"ticks": [5, 10, 15, 20],
		"titleTextStyle": {
			"color": "red"
		},
		"textPosition": "in"
	}
}';

			self::_renderTextAreaItem(
				esc_html__( 'Configuration', 'visualizer' ),
				'manual',
				$this->manual,
				sprintf(
					esc_html__( 'One per line in valid JSON (key:value) format e.g. %s', 'visualizer' ),
					'<br><code>' . $example . '</code>'
				),
				'',
				array( 'rows' => 5 )
			);

		self::_renderSectionEnd();
		self::_renderGroupEnd();

	}

	/**
	 * Renders chart action buttons group.
	 *
	 * @access protected
	 */
	protected function _renderActionSettings() {
		global $wp_version;
		$disable_actions    = version_compare( $wp_version, '4.7.0', '<' );
		self::_renderSectionStart( esc_html__( 'Actions', 'visualizer' ), false );
			self::_renderCheckboxItem(
				esc_html__( 'Print', 'visualizer' ),
				'actions[]',
				isset( $this->actions ) && in_array( 'print', $this->actions, true ) ? true : false,
				'print',
				$disable_actions ? '<span class="viz-section-error">' . esc_html__( 'Upgrade to at least WordPress 4.7 to use this.', 'visualizer' ) . '</span>' : esc_html__( 'To enable printing the chart/data.', 'visualizer' ),
				$disable_actions
			);
			self::_renderCheckboxItem(
				esc_html__( 'CSV', 'visualizer' ),
				'actions[]',
				isset( $this->actions ) && in_array( 'csv;application/csv', $this->actions, true ) ? true : false,
				'csv;application/csv',
				$disable_actions ? '<span class="viz-section-error">' . esc_html__( 'Upgrade to at least WordPress 4.7 to use this.', 'visualizer' ) . '</span>' : esc_html__( 'To enable downloading the data as a CSV.', 'visualizer' ),
				$disable_actions
			);

			$disabled   = ! self::is_excel_enabled();
			self::_renderCheckboxItem(
				esc_html__( 'Excel', 'visualizer' ),
				'actions[]',
				isset( $this->actions ) && in_array( 'xls;application/vnd.ms-excel', $this->actions, true ) ? true : false,
				'xls;application/vnd.ms-excel',
				$disable_actions ? '<span class="viz-section-error">' . esc_html__( 'Upgrade to at least WordPress 4.7 to use this.', 'visualizer' ) . '</span>' : ( $disabled ? '<span class="viz-section-error">' . esc_html__( 'Enable the ZIP and XML extensions to use this setting.', 'visualizer' ) . '</span>' : esc_html__( 'To enable downloading the data as an Excel spreadsheet.', 'visualizer' ) ),
				$disable_actions || $disabled
			);
			self::_renderCheckboxItem(
				esc_html__( 'Copy', 'visualizer' ),
				'actions[]',
				isset( $this->actions ) && in_array( 'copy', $this->actions, true ) ? true : false,
				'copy',
				$disable_actions ? '<span class="viz-section-error">' . esc_html__( 'Upgrade to at least WordPress 4.7 to use this.', 'visualizer' ) . '</span>' : esc_html__( 'To enable copying the data to the clipboard.', 'visualizer' ),
				$disable_actions
			);
		self::_renderSectionEnd();
	}

	/**
	 * Checks if the Excel module can be enabled.
	 */
	private static function is_excel_enabled() {
		$vendor_file = VISUALIZER_ABSPATH . '/vendor/autoload.php';
		if ( is_readable( $vendor_file ) ) {
			include_once( $vendor_file );
		}

		return class_exists( 'PhpOffice\PhpSpreadsheet\Spreadsheet' ) && extension_loaded( 'zip' ) && extension_loaded( 'xml' ) && extension_loaded( 'fileinfo' ) && version_compare( PHP_VERSION, '5.6.0', '>' );
	}

	/**
	 * Renders chart general settings group.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _renderGeneralSettings() {
		self::_renderGroupStart( esc_html__( 'General Settings', 'visualizer' ) );
			self::_renderSectionStart();
				self::_renderSectionDescription( esc_html__( 'Configure title, font styles, tooltip, legend and else settings for the chart.', 'visualizer' ) );
			self::_renderSectionEnd();

			self::_renderSectionStart( esc_html__( 'Title', 'visualizer' ), false );
				$this->_renderChartTitleSettings();
			self::_renderSectionEnd();

			self::_renderSectionStart( esc_html__( 'Font Styles', 'visualizer' ), false );
				echo '<div class="viz-section-item">';
					echo '<a class="more-info" href="javascript:;">[?]</a>';
					echo '<b>', esc_html__( 'Family And Size', 'visualizer' ), '</b>';

					echo '<table class="viz-section-table" cellspacing="0" cellpadding="0" border="0">';
						echo '<tr>';
							echo '<td class="viz-section-table-column">';
								echo '<select name="fontName" class="control-select">';
									echo '<option></option>';
		foreach ( self::$_fontFamilies as $font => $label ) {
			echo '<option value="', $font, '"', selected( $font, $this->fontName, false ), '>';
			echo $label;
			echo '</option>';
		}
								echo '</select>';
							echo '</td>';
							echo '<td class="viz-section-table-column">';
								echo '<select name="fontSize" class="control-select">';
									echo '<option></option>';
		for ( $i = 7; $i <= 20; $i++ ) {
			echo '<option value="', $i, '"', selected( $i, $this->fontSize, false ), '>', $i, '</option>';
		}
								echo '</select>';
							echo '</td>';
						echo '</tr>';
					echo '</table>';

					echo '<p class="viz-section-description">';
						esc_html_e( 'The default font family and size for all text in the chart.', 'visualizer' );
					echo '</p>';
				echo '</div>';
			self::_renderSectionEnd();

			self::_renderSectionStart( esc_html__( 'Legend', 'visualizer' ), false );
				self::_renderSelectItem(
					esc_html__( 'Position', 'visualizer' ),
					'legend[position]',
					$this->legend['position'],
					$this->_legendPositions,
					esc_html__( 'Determines where to place the legend, compared to the chart area.', 'visualizer' )
				);

				self::_renderSelectItem(
					esc_html__( 'Alignment', 'visualizer' ),
					'legend[alignment]',
					$this->legend['alignment'],
					$this->_alignments,
					esc_html__( 'Determines the alignment of the legend.', 'visualizer' )
				);

				self::_renderColorPickerItem(
					esc_html__( 'Font Color', 'visualizer' ),
					'legend[textStyle][color]',
					isset( $this->legend['textStyle']['color'] ) ? $this->legend['textStyle']['color'] : null,
					'#000'
				);
			self::_renderSectionEnd();

			self::_renderSectionStart( esc_html__( 'Tooltip', 'visualizer' ), false );
				$this->_renderTooltipSettigns();
			self::_renderSectionEnd();

			$this->_renderAnimationSettings();

		self::_renderGroupEnd();
	}

	/**
	 * Renders animation settings section.
	 *
	 * @access protected
	 */
	protected function _renderAnimationSettings() {
		if ( ! $this->_supportsAnimation ) {
			return;
		}

		self::_renderSectionStart( esc_html__( 'Animation', 'visualizer' ), false );

		self::_renderCheckboxItem(
			esc_html__( 'Animate on startup', 'visualizer' ),
			'animation[startup]',
			isset( $this->animation['startup'] ) ? $this->animation['startup'] : 0,
			true,
			esc_html__( 'Determines if the chart will animate on the initial draw.', 'visualizer' )
		);

		self::_renderTextItem(
			esc_html__( 'Duration', 'visualizer' ),
			'animation[duration]',
			isset( $this->animation['duration'] ) ? $this->animation['duration'] : 0,
			esc_html__( 'The duration of the animation, in milliseconds', 'visualizer' ),
			0,
			'number'
		);

		self::_renderSelectItem(
			esc_html__( 'Easing', 'visualizer' ),
			'animation[easing]',
			isset( $this->animation['easing'] ) ? $this->animation['easing'] : null,
			array(
				'linear'    => esc_html__( 'Constant speed', 'visualizer' ),
				'in'    => esc_html__( 'Start slow and speed up', 'visualizer' ),
				'out'   => esc_html__( 'Start fast and slow down', 'visualizer' ),
				'inAndOut'  => esc_html__( 'Start slow, speed up, then slow down', 'visualizer' ),
			),
			esc_html__( 'The easing function applied to the animation.', 'visualizer' )
		);

		self::_renderSectionEnd();

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
			esc_html__( 'Trigger', 'visualizer' ),
			'tooltip[trigger]',
			isset( $this->tooltip['trigger'] ) ? $this->tooltip['trigger'] : null,
			array(
				''          => '',
				'focus'     => esc_html__( 'The tooltip will be displayed when the user hovers over an element', 'visualizer' ),
				'selection' => esc_html__( 'The tooltip will be displayed when the user selects an element', 'visualizer' ),
				'none'      => esc_html__( 'The tooltip will not be displayed', 'visualizer' ),
			),
			esc_html__( 'Determines the user interaction that causes the tooltip to be displayed.', 'visualizer' )
		);

		self::_renderSelectItem(
			esc_html__( 'Show Color Code', 'visualizer' ),
			'tooltip[showColorCode]',
			isset( $this->tooltip['showColorCode'] ) ? $this->tooltip['showColorCode'] : null,
			$this->_yesno,
			esc_html__( 'If set to yes, will show colored squares next to the slice information in the tooltip.', 'visualizer' )
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
		self::_renderGroupStart( esc_html__( 'Layout & Chart Area', 'visualizer' ) );
			self::_renderSectionStart( esc_html__( 'Layout', 'visualizer' ), false );
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

				echo '<div class="viz-section-delimiter"></div>';

				self::_renderSectionDescription( esc_html__( 'Configure the background color for the main area of the chart and the chart border width and color.', 'visualizer' ) );

				self::_renderTextItem(
					esc_html__( 'Stroke Width', 'visualizer' ),
					'backgroundColor[strokeWidth]',
					isset( $this->backgroundColor['strokeWidth'] ) ? $this->backgroundColor['strokeWidth'] : null,
					esc_html__( 'The chart border width in pixels.', 'visualizer' ),
					'0'
				);

				self::_renderColorPickerItem(
					esc_html__( 'Stroke Color', 'visualizer' ),
					'backgroundColor[stroke]',
					! empty( $this->backgroundColor['stroke'] ) ? $this->backgroundColor['stroke'] : null,
					'#666'
				);

				$background_color = ! empty( $this->backgroundColor['fill'] ) ? $this->backgroundColor['fill'] : null;
				self::_renderColorPickerItem(
					esc_html__( 'Background Color', 'visualizer' ),
					'backgroundColor[fill]',
					$background_color,
					'#fff'
				);

				echo '<div class="viz-section-item">';
					echo '<label>';
						echo '<input type="checkbox" class="control-checkbox" name="backgroundColor[fill]" value="transparent"', checked( $background_color, 'transparent', false ), '> ';
						esc_html_e( 'Transparent background', 'visualizer' );
					echo '</label>';
				echo '</div>';
			self::_renderSectionEnd();

			self::_renderSectionStart( esc_html__( 'Chart Area', 'visualizer' ), false );
				self::_renderSectionDescription( esc_html__( 'Configure the placement and size of the chart area (where the chart itself is drawn, excluding axis and legends). Two formats are supported: a number, or a number followed by %. A simple number is a value in pixels; a number followed by % is a percentage.', 'visualizer' ) );

				echo '<div class="viz-section-item">';
					echo '<a class="more-info" href="javascript:;">[?]</a>';
					echo '<b>', esc_html__( 'Left And Top Margins', 'visualizer' ), '</b>';

					echo '<table class="viz-section-table" cellspacing="0" cellpadding="0" border="0">';
						echo '<tr>';
							echo '<td class="viz-section-table-column">';
								echo '<input type="text" name="chartArea[left]" class="control-text" value="', $this->chartArea['left'] || $this->chartArea['left'] === '0' ? esc_attr( $this->chartArea['left'] ) : '', '" placeholder="20%">';
							echo '</td>';
							echo '<td class="viz-section-table-column">';
								echo '<input type="text" name="chartArea[top]" class="control-text" value="', $this->chartArea['top'] || $this->chartArea['top'] === '0' ? esc_attr( $this->chartArea['top'] ) : '', '" placeholder="20%">';
							echo '</td>';
						echo '</tr>';
					echo '</table>';

					echo '<p class="viz-section-description">';
						esc_html_e( 'Determines how far to draw the chart from the left and top borders.', 'visualizer' );
					echo '</p>';
				echo '</div>';

				echo '<div class="viz-section-item">';
					echo '<a class="more-info" href="javascript:;">[?]</a>';
					echo '<b>', esc_html__( 'Width And Height Of Chart Area', 'visualizer' ), '</b>';

					echo '<table class="viz-section-table" cellspacing="0" cellpadding="0" border="0">';
						echo '<tr>';
							echo '<td class="viz-section-table-column">';
								echo '<input type="text" name="chartArea[width]" class="control-text" value="', ! empty( $this->chartArea['width'] ) ? esc_attr( $this->chartArea['width'] ) : '', '" placeholder="60%">';
							echo '</td>';
							echo '<td class="viz-section-table-column">';
								echo '<input type="text" name="chartArea[height]" class="control-text" value="', ! empty( $this->chartArea['height'] ) ? esc_attr( $this->chartArea['height'] ) : '', '" placeholder="60%">';
							echo '</td>';
						echo '</tr>';
					echo '</table>';

					echo '<p class="viz-section-description">';
						esc_html_e( 'Determines the width and hight of the chart area.', 'visualizer' );
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
	 * @access public
	 * @param string $title The title of the select item.
	 * @param string $name The name of the select item.
	 * @param string $value The actual value of the select item.
	 * @param array  $options The array of select options.
	 * @param string $desc The description of the select item.
	 * @param bool   $multiple Is this a multiple select box.
	 * @param array  $classes Any additional classes.
	 * @param array  $attributes Custom attributes.
	 */
	public static function _renderSelectItem( $title, $name, $value, array $options, $desc, $multiple = false, $classes = array(), $attributes = array() ) {
		$atts   = '';
		if ( $attributes ) {
			foreach ( $attributes as $k => $v ) {
				$atts   .= ' data-visualizer-' . $k . '=' . esc_attr( $v );
			}
		}
		echo '<div class="viz-section-item">';
			echo '<a class="more-info" href="javascript:;">[?]</a>';
			echo '<b>', $title, '</b>';
			echo '<select class="control-select ', implode( ' ', $classes ) , '" name="', $name, '" ', ( $multiple ? 'multiple' : '' ), ' ' , $atts, '>';
		foreach ( $options as $key => $label ) {
			$extra      = $multiple && is_array( $value ) ? ( in_array( $key, $value, true ) ? 'selected' : '' ) : selected( $key, $value, false );
			echo '<option value="', $key, '"', $extra, '>';
			echo $label;
			echo '</option>';
		}
			echo '</select>';
			echo '<p class="viz-section-description">', $desc, '</p>';
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
		echo '<div class="viz-section-item">';
			echo '<b>', $title, '</b>';
			echo '<div>';
				echo '<input type="text" class="color-picker-hex" name="', $name, '" maxlength="7" placeholder="', esc_attr__( 'Hex Value', 'visualizer' ), '" value="', is_null( $value ) ? $default : esc_attr( $value ), '" data-default-color="', $default, '">';
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
	 * @param string $type The type for the input (out of number, email, tel etc., default is text).
	 * @param array  $custom_attributes The custom attributes.
	 */
	protected static function _renderTextItem( $title, $name, $value, $desc, $placeholder = '', $type = 'text', $custom_attributes = array() ) {
		$attributes     = '';
		if ( $custom_attributes ) {
			foreach ( $custom_attributes as $k => $v ) {
				$attributes .= ' ' . $k . '="' . esc_attr( $v ) . '"';
			}
		}
		echo '<div class="viz-section-item">';
			echo '<a class="more-info" href="javascript:;">[?]</a>';
			echo '<b>', $title, '</b>';
			echo '<input type="', $type, '" class="control-text" ', $attributes, ' name="', $name, '" value="', esc_attr( $value ), '" placeholder="', $placeholder, '">';
			echo '<p class="viz-section-description">', $desc, '</p>';
		echo '</div>';
	}

	/**
	 * Renders the beginning of a group.
	 *
	 * @since 1.0.0
	 *
	 * @static
	 * @access public
	 * @param string $title The title of this group.
	 * @param string $html Any additional HTML.
	 * @param string $class Any additional classes.
	 */
	public static function _renderGroupStart( $title, $html = '', $class = '' ) {
		echo '<li class="viz-group ' . $class . '">';
			echo '<h3 class="viz-group-title">', $title, '</h3>';
			echo $html;
			echo '<ul class="viz-group-content">';
	}

	/**
	 * Renders the ending of a group.
	 *
	 * @since 1.0.0
	 *
	 * @static
	 * @access public
	 */
	public static function _renderGroupEnd() {
			echo '</ul>';
		echo '</li>';
	}

	/**
	 * Renders the beginning of a section.
	 *
	 * @since 1.0.0
	 *
	 * @static
	 * @access public
	 * @param string  $title The title of this section. If the title is empty, no title will be displayed.
	 * @param boolean $open Determines whether the section items block has to be expanded or collapsed.
	 */
	public static function _renderSectionStart( $title = false, $open = true ) {

		if ( ! empty( $title ) ) {
			echo '<li class="viz-subsection">';
			echo '<span class="viz-section-title">', $title, '</span>';
		} else {
			echo '<li class=" ">';
		}
			echo '<div class="viz-section-items section-items', $open ? ' open' : '', '">';
	}

	/**
	 * Renders the ending of a section.
	 *
	 * @since 1.0.0
	 *
	 * @public
	 * @access protected
	 * @param string $html Any addition HTML to add.
	 */
	public static function _renderSectionEnd( $html = '' ) {
			echo '</div>';
			echo $html;
		echo '</li>';
	}

	/**
	 * Renders section description block.
	 *
	 * @since 1.0.0
	 *
	 * @static
	 * @access public
	 * @param string $description The description text.
	 */
	public static function _renderSectionDescription( $description ) {
		echo '<div class="viz-section-item">';
			echo '<div class="viz-section-description">', $description, '</div>';
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
		switch ( $this->__series[ $index + 1 ]['type'] ) {
			case 'number':
				self::_renderTextItem(
					esc_html__( 'Number Format', 'visualizer' ),
					'series[' . $index . '][format]',
					isset( $this->series[ $index ]['format'] ) ? $this->series[ $index ]['format'] : '',
					sprintf( esc_html__( 'Enter custom format pattern to apply to this series value, similar to the %1$sICU pattern set%2$s. Use something like #,### to get 1,234 as output, or $# to add dollar sign before digits. Pay attention that if you use &#37; percentage format then your values will be multiplied by 100.', 'visualizer' ), '<a href="http://icu-project.org/apiref/icu4c/classDecimalFormat.html#_details" target="_blank">', '</a>' ),
					'#,###.##'
				);
				break;
			case 'date':
			case 'datetime':
			case 'timeofday':
				self::_renderTextItem(
					esc_html__( 'Date Format', 'visualizer' ),
					'series[' . $index . '][format]',
					isset( $this->series[ $index ]['format'] ) ? $this->series[ $index ]['format'] : '',
					sprintf( esc_html__( 'Enter custom format pattern to apply to this series value, similar to the %1$sICU date and time format%2$s.', 'visualizer' ), '<a href="http://userguide.icu-project.org/formatparse/datetime#TOC-Date-Time-Format-Syntax" target="_blank">', '</a>' ),
					'eeee, dd LLLL yyyy'
				);
				break;
		}
	}

	/**
	 * Render a checkbox item
	 */
	protected static function _renderCheckboxItem( $title, $name, $value, $default, $desc, $disabled = false ) {
		echo '<div class="viz-section-item">';
			echo '<a class="more-info" href="javascript:;">[?]</a>';
			echo '<b>', $title, '</b>';
			echo '<input type="checkbox" class="control-check" value="', $default, '" name="', $name, '" ', ( $value === $default ? 'checked' : '' ), ' ', ( $disabled ? 'disabled=disabled' : '' ), '>';
			echo '<p class="viz-section-description">', $desc, '</p>';
		echo '</div>';
	}

	/**
	 * Render a textarea item.
	 */
	protected static function _renderTextAreaItem( $title, $name, $value, $desc, $placeholder = '', $custom_attributes = array() ) {
		$attributes     = '';
		if ( $custom_attributes ) {
			foreach ( $custom_attributes as $k => $v ) {
				$attributes .= ' ' . $k . '="' . esc_attr( $v ) . '"';
			}
		}
		echo '<div class="viz-section-item">';
			echo '<a class="more-info" href="javascript:;">[?]</a>';
			echo '<b>', $title, '</b>';
			echo '<textarea class="control-text" ', $attributes, ' name="', $name, '" placeholder="', $placeholder, '">', $value, '</textarea>';
			echo '<p class="viz-section-description">', $desc, '</p>';
		echo '</div>';
	}

	/**
	 * Returns the library this chart implements.
	 */
	public function getLibrary() {
		return $this->_library;
	}

}
