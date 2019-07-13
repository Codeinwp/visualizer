<?php

/**
 * Class for line chart sidebar settings.
 *
 * @category Visualizer
 * @package Render
 * @subpackage Sidebar
 *
 * @since 3.3.0
 */
class Visualizer_Render_Sidebar_Type_ChartJS_Line extends Visualizer_Render_Sidebar_Type_ChartJS_Linear {

	/**
	 * The constructor.
	 */
	public function __construct( $data = array() ) {
		parent::__construct( $data );

		$this->_pointStyles = array(
			'circle'  => esc_html__( 'Circle', 'visualizer' ),
			'cross'  => esc_html__( 'Cross', 'visualizer' ),
			'dash'  => esc_html__( 'Dash', 'visualizer' ),
			'line'  => esc_html__( 'Line', 'visualizer' ),
			'rect'  => esc_html__( 'Rectangle', 'visualizer' ),
			'star'  => esc_html__( 'Star', 'visualizer' ),
			'triangle'  => esc_html__( 'Triangle', 'visualizer' ),
		);
	}


	/**
	 * Renders concrete series settings for the Line chart.
	 *
	 * @since 3.3.0
	 *
	 * @access protected
	 * @param int $index The series index.
	 */
	protected function _renderChartTypeSeries( $index ) {
		self::_renderColorPickerItem(
			esc_html__( 'Point stroke color', 'visualizer' ),
			'series[' . $index . '][borderColor]',
			isset( $this->series[ $index ]['borderColor'] ) ? $this->series[ $index ]['borderColor'] : null,
			null
		);

		self::_renderColorPickerItem(
			esc_html__( 'Point fill color', 'visualizer' ),
			'series[' . $index . '][backgroundColor]',
			isset( $this->series[ $index ]['backgroundColor'] ) ? $this->series[ $index ]['backgroundColor'] : null,
			null
		);

		self::_renderTextItem(
			esc_html__( 'Point stroke width', 'visualizer' ),
			'series[' . $index . '][borderWidth]',
			isset( $this->series[ $index ]['borderWidth'] ) ? $this->series[ $index ]['borderWidth'] : 1,
			'',
			1,
			'number',
			array( 'min' => 1 )
		);

		self::_renderSelectItem(
			esc_html__( 'Point style', 'visualizer' ),
			'series[' . $index . '][pointStyle]',
			isset( $this->series[ $index ]['pointStyle'] ) ? $this->series[ $index ]['pointStyle'] : 'circle',
			$this->_pointStyles,
			''
		);
	}
}
