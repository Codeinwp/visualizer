<?php

/**
 * Class for bar chart sidebar settings.
 *
 * @category Visualizer
 * @package Render
 * @subpackage Sidebar
 *
 * @since 3.3.0
 */
class Visualizer_Render_Sidebar_Type_ChartJS_Bar extends Visualizer_Render_Sidebar_Type_ChartJS_Linear {

	/**
	 * The constructor.
	 */
	public function __construct( $data = array() ) {
		parent::__construct( $data );
	}


	/**
	 * Renders concrete series settings for the Bar chart.
	 *
	 * @since 3.3.0
	 *
	 * @access protected
	 * @param int $index The series index.
	 */
	protected function _renderChartTypeSeries( $index ) {
		self::_renderColorPickerItem(
			esc_html__( 'Bar border color', 'visualizer' ),
			'series[' . $index . '][borderColor]',
			isset( $this->series[ $index ]['borderColor'] ) ? $this->series[ $index ]['borderColor'] : null,
			null
		);

		self::_renderColorPickerItem(
			esc_html__( 'Bar background color', 'visualizer' ),
			'series[' . $index . '][backgroundColor]',
			isset( $this->series[ $index ]['backgroundColor'] ) ? $this->series[ $index ]['backgroundColor'] : null,
			null
		);

		self::_renderColorPickerItem(
			esc_html__( 'Bar background color when hovered', 'visualizer' ),
			'series[' . $index . '][hoverBackgroundColor]',
			isset( $this->series[ $index ]['hoverBackgroundColor'] ) ? $this->series[ $index ]['hoverBackgroundColor'] : null,
			null
		);

		self::_renderTextItem(
			esc_html__( 'Bar border width', 'visualizer' ),
			'series[' . $index . '][borderWidth]',
			isset( $this->series[ $index ]['borderWidth'] ) ? $this->series[ $index ]['borderWidth'] : 1,
			'',
			1,
			'number',
			array( 'min' => 1 )
		);
	}

	/**
	 * Renders settings specific to the Bar chart.
	 *
	 * @since 3.3.0
	 *
	 * @access protected
	 */
	protected function _renderChartTypeSettings() {
		self::_renderGroupStart( esc_html__( 'Bar Settings', 'visualizer' ) );

			self::_renderSectionStart( esc_html__( 'General', 'visualizer' ), false );

			$type = str_replace( 'Visualizer_Render_Sidebar_Type_ChartJS_', '', get_class( $this ) );

			// Bar type will work on the Y-axis and Column on the X-axis
		switch ( $type ) {
			case 'Column':
				self::_renderTextItem(
					esc_html__( 'Bar Percentage', 'visualizer' ),
					'xAxes[barPercentage_int]',
					isset( $this->xAxes['barPercentage_int'] ) ? $this->xAxes['barPercentage_int'] : 0.9,
					esc_html__( 'Percent (0-1) of the available width each bar should be within the category width.', 'visualizer' ),
					0.9,
					'number',
					array( 'min' => 0, 'max' => 1, 'step' => 0.1 )
				);
				self::_renderTextItem(
					esc_html__( 'Bar Thickness', 'visualizer' ),
					'xAxes[barThickness]',
					isset( $this->xAxes['barThickness'] ) ? $this->xAxes['barThickness'] : '',
					esc_html__( 'Manually set width of each bar in pixels. If set to "flex", it computes "optimal" sample widths that globally arrange bars side by side. If not set (default), bars are equally sized based on the smallest interval.', 'visualizer' ),
					''
				);
				self::_renderCheckboxItem(
					esc_html__( 'Stacked', 'visualizer' ),
					'xAxes[stacked_bool]',
					isset( $this->xAxes['stacked_bool'] ) ? $this->xAxes['stacked_bool'] : false,
					'true',
					esc_html__( 'If checked, series elements are stacked.', 'visualizer' )
				);
				break;
			case 'Bar':
				self::_renderTextItem(
					esc_html__( 'Bar Percentage', 'visualizer' ),
					'yAxes[barPercentage_int]',
					isset( $this->yAxes['barPercentage_int'] ) ? $this->yAxes['barPercentage_int'] : 0.9,
					esc_html__( 'Percent (0-1) of the available width each bar should be within the category width.', 'visualizer' ),
					0.9,
					'number',
					array( 'min' => 0, 'max' => 1, 'step' => 0.1 )
				);
				self::_renderTextItem(
					esc_html__( 'Bar Thickness', 'visualizer' ),
					'yAxes[barThickness]',
					isset( $this->yAxes['barThickness'] ) ? $this->yAxes['barThickness'] : '',
					esc_html__( 'Manually set width of each bar in pixels. If set to "flex", it computes "optimal" sample widths that globally arrange bars side by side. If not set (default), bars are equally sized based on the smallest interval.', 'visualizer' ),
					''
				);
				self::_renderCheckboxItem(
					esc_html__( 'Stacked', 'visualizer' ),
					'yAxes[stacked_bool]',
					isset( $this->yAxes['stacked_bool'] ) ? $this->yAxes['stacked_bool'] : false,
					'true',
					esc_html__( 'If checked, series elements are stacked.', 'visualizer' )
				);
				break;
		}

			self::_renderSectionEnd();

		self::_renderGroupEnd();

	}
}
