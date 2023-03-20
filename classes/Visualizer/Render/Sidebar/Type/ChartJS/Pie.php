<?php

/**
 * Class for pie chart sidebar settings.
 *
 * @category Visualizer
 * @package Render
 * @subpackage Sidebar
 *
 * @since 3.3.0
 */
class Visualizer_Render_Sidebar_Type_ChartJS_Pie extends Visualizer_Render_Sidebar_ChartJS {

	/**
	 * The constructor.
	 */
	public function __construct( $data = array() ) {
		parent::__construct( $data );
	}

	/**
	 * Renders chart axes settings.
	 *
	 * @since 3.3.0
	 *
	 * @access protected
	 */
	protected function _renderAxesSettings() {
		// empty.
	}

	/**
	 * Renders concrete series settings for the Pie chart.
	 *
	 * @since 3.3.0
	 *
	 * @access protected
	 */
	protected function _renderSeriesSettings() {
		// empty.
	}

	/**
	 * Renders slice settings for the Pie chart.
	 *
	 * @since 3.3.0
	 *
	 * @access protected
	 * @param int $index The series index.
	 */
	protected function _renderSliceSettings( $index ) {
		self::_renderColorPickerItem(
			esc_html__( 'Arc border color', 'visualizer' ),
			'slices[' . $index . '][borderColor]',
			isset( $this->slices[ $index ]['borderColor'] ) ? $this->slices[ $index ]['borderColor'] : null,
			null
		);

		self::_renderColorPickerItem(
			esc_html__( 'Arc background color', 'visualizer' ),
			'slices[' . $index . '][backgroundColor]',
			isset( $this->slices[ $index ]['backgroundColor'] ) ? $this->slices[ $index ]['backgroundColor'] : null,
			null
		);

		self::_renderTextItem(
			esc_html__( 'Arc border width', 'visualizer' ),
			'slices[' . $index . '][borderWidth]',
			isset( $this->slices[ $index ]['borderWidth'] ) ? $this->slices[ $index ]['borderWidth'] : 1,
			'',
			1,
			'number',
			array( 'min' => 1 )
		);

		self::_renderColorPickerItem(
			esc_html__( 'Arc border color when hovered', 'visualizer' ),
			'slices[' . $index . '][hoverBorderColor]',
			isset( $this->slices[ $index ]['hoverBorderColor'] ) ? $this->slices[ $index ]['hoverBorderColor'] : null,
			null
		);

		self::_renderColorPickerItem(
			esc_html__( 'Arc background color when hovered', 'visualizer' ),
			'slices[' . $index . '][hoverBackgroundColor]',
			isset( $this->slices[ $index ]['hoverBackgroundColor'] ) ? $this->slices[ $index ]['hoverBackgroundColor'] : null,
			null
		);

	}

	/**
	 * Renders settings specific to the Pie chart.
	 *
	 * @since 3.3.0
	 *
	 * @access protected
	 */
	protected function _renderChartTypeSettings() {
		self::_renderGroupStart( esc_html__( 'Pie Settings', 'visualizer' ) );

			self::_renderSectionStart( esc_html__( 'General', 'visualizer' ), false );

				self::_renderCheckboxItem(
					esc_html__( 'Donut', 'visualizer' ),
					'custom[donut]',
					isset( $this->custom['donut'] ) ? $this->custom['donut'] : false,
					'true',
					esc_html__( 'If checked, the chart will be rendered as a donut chart.', 'visualizer' )
				);

				self::_renderTextItem(
					esc_html__( 'Number Format', 'visualizer' ),
					'format',
					isset( $this->format ) ? $this->format : '',
					sprintf(
						'%s<br><br>%s<br><br>%s',
						esc_html__( 'Enter custom format pattern to apply to horizontal axis labels.', 'visualizer' ),
						sprintf(
							esc_html__( 'For number axis labels, this is a subset of the decimal formatting %1$sICU pattern set%2$s. For instance, $#,###.## will display values $1,234.56 for value 1234.56. Pay attention that if you use #&#37;&#37; percentage format then your values will be multiplied by 100.', 'visualizer' ),
							'<a href="http://icu-project.org/apiref/icu4c/classDecimalFormat.html#_details" target="_blank">',
							'</a>'
						),
						sprintf(
							esc_html__( 'For date axis labels, this is a subset of the date formatting %1$sICU date and time format%2$s.', 'visualizer' ),
							'<a href="https://unicode-org.github.io/icu/userguide/format_parse/datetime/#datetime-format-syntax" target="_blank">',
							'</a>'
						)
					)
				);

			self::_renderSectionEnd();

		self::_renderGroupEnd();

		self::_renderGroupStart( esc_html__( 'Slices Settings', 'visualizer' ) );
			self::_renderSectionStart();
				self::_renderSectionDescription( esc_html__( 'If you have just updated/modified the chart data, you may need to save it before the new data reflects in the settings.', 'visualizer' ), 'viz-info-msg' );
			self::_renderSectionEnd();

		for ( $i = 0, $cnt = count( $this->__data ); $i < $cnt; $i++ ) {
			self::_renderSectionStart( esc_html( $this->__data[ $i ][0] ), false );
				$this->_renderSliceSettings( $i );
			self::_renderSectionEnd();
		}
		self::_renderGroupEnd();
	}
}
