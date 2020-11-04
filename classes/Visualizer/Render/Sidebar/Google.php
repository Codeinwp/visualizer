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
 * Base class for sidebar settigns of graph based charts.
 *
 * @category Visualizer
 * @package Render
 * @subpackage Sidebar
 *
 * @since 1.0.0
 * @abstract
 */
abstract class Visualizer_Render_Sidebar_Google extends Visualizer_Render_Sidebar {

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
	 * The constructor.
	 */
	public function __construct( $data = array() ) {
		$this->_library = 'google';
		parent::__construct( $data );

		$this->_legendPositions = array(
			''       => '',
			'left'  => esc_html__( 'Left of the chart', 'visualizer' ),
			'right'  => esc_html__( 'Right of the chart', 'visualizer' ),
			'top'    => esc_html__( 'Above the chart', 'visualizer' ),
			'bottom' => esc_html__( 'Below the chart', 'visualizer' ),
			'none'   => esc_html__( 'Omit the legend', 'visualizer' ),
		);

		$chart_type = $this->get_chart_type( false );
		if ( ! in_array( $chart_type, array( 'Pie' ), true ) ) {
			$this->_legendPositions['in']  = esc_html__( 'Inside the chart', 'visualizer' );
		}

		if ( in_array( $chart_type, array( 'Bubble' ), true ) ) {
			unset( $this->_legendPositions['left'] );
		}

		$this->_alignments = array(
			''       => '',
			'start'  => esc_html__( 'Aligned to the start of the allocated area', 'visualizer' ),
			'center' => esc_html__( 'Centered in the allocated area', 'visualizer' ),
			'end'    => esc_html__( 'Aligned to the end of the allocated area', 'visualizer' ),
		);

	}

	/**
	 * Registers additional hooks.
	 *
	 * @access protected
	 */
	protected function hooks() {
		if ( $this->_library === 'google' ) {
			add_filter( 'visualizer_assets_render', array( $this, 'load_google_assets' ), 10, 2 );
		}
	}

	/**
	 * Loads the assets.
	 */
	function load_google_assets( $deps, $is_frontend ) {
		wp_register_script( 'google-jsapi', '//www.gstatic.com/charts/loader.js', array(), null, true );
		wp_register_script(
			'visualizer-render-google-lib',
			VISUALIZER_ABSURL . 'js/render-google.js',
			array(
				'google-jsapi',
			),
			Visualizer_Plugin::VERSION,
			true
		);

		return array_merge(
			$deps,
			array( 'visualizer-render-google-lib' )
		);

	}

	/**
	 * Enqueue assets.
	 */
	public static function enqueue_assets( $deps = array() ) {
		wp_enqueue_script( 'visualizer-google-jsapi', '//www.gstatic.com/charts/loader.js', array(), null, true );
		wp_enqueue_script( 'visualizer-render-google-lib', VISUALIZER_ABSURL . 'js/render-google.js', array_merge( $deps, array( 'visualizer-google-jsapi' ) ), Visualizer_Plugin::VERSION, true );
		return 'visualizer-render-google-lib';
	}

	/**
	 * Renders the role field.
	 *
	 * @since 3.4.0
	 *
	 * @access protected
	 */
	protected function _renderRoleField( $index ) {
		self::_renderSelectItem(
			esc_html__( 'Special Role', 'visualizer' ),
			'series[' . $index . '][role]',
			isset( $this->series[ $index ]['role'] ) ? $this->series[ $index ]['role'] : '',
			array(
				''  => esc_html__( 'Default (Data)', 'visualizer' ),
				'annotation'  => esc_html__( 'Annotation', 'visualizer' ),
				'annotationText' => esc_html__( 'Annotation Text', 'visualizer' ),
				'certainty' => esc_html__( 'Certainty', 'visualizer' ),
				'emphasis' => esc_html__( 'Emphasis', 'visualizer' ),
				'scope' => esc_html__( 'Scope', 'visualizer' ),
				'style' => esc_html__( 'Style', 'visualizer' ),
				'tooltip' => esc_html__( 'Tooltip', 'visualizer' ),
			),
			sprintf( esc_html__( 'Determines whether the series has to be used for a special role as mentioned in %1$shere%2$s. You can view a few examples %3$shere%4$s.', 'visualizer' ), '<a href="https://developers.google.com/chart/interactive/docs/roles#what-roles-are-available" target="_blank">', '</a>', '<a href="https://docs.themeisle.com/article/1160-roles-for-series-visualizer" target="_blank">', '</a>' )
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

			do_action( 'visualizer_chart_settings', get_class( $this ), $this->_data, 'general', array( 'generic' => true ) );

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
		self::_renderGroupStart( esc_html__( 'Chart Size & Placement', 'visualizer' ) );
			self::_renderSectionStart( esc_html__( 'Chart Size/Layout', 'visualizer' ), false );
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
						esc_html_e( 'Determines the total width and height of the chart. This will only show in the front-end.', 'visualizer' );
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

			self::_renderSectionStart( esc_html__( 'Placement', 'visualizer' ), false );
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
}
