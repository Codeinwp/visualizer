<?php

/**
 * Class for linear chart settings.
 *
 * @category Visualizer
 * @package Render
 * @subpackage Sidebar
 *
 * @since 3.3.0
 */
class Visualizer_Render_Sidebar_Type_ChartJS_Linear extends Visualizer_Render_Sidebar_ChartJS {

	/**
	 * Renders chart axes settings.
	 *
	 * @since 3.3.0
	 *
	 * @access protected
	 */
	protected function _renderAxesSettings() {
		$this->_renderHorizontalAxisSettings();
		$this->_renderVerticalAxisSettings();
	}

	/**
	 * Renders horizontal axis settings.
	 *
	 * @since 3.3.0
	 *
	 * @access protected
	 */
	protected function _renderHorizontalAxisSettings() {
		self::_renderGroupStart( esc_html__( 'Horizontal Axis Settings', 'visualizer' ) );
			$this->_renderHorizontalAxisGeneralSettings();
			$this->_renderHorizontalTickSettings();
		self::_renderGroupEnd();
	}

	/**
	 * Renders tick settings block for horizontal axis settings.
	 *
	 * @since 3.3.0
	 *
	 * @access protected
	 */
	protected function _renderHorizontalTickSettings() {
		self::_renderSectionStart( esc_html__( 'Tick Settings', 'visualizer' ), false );

			self::_renderTextItem(
				esc_html__( 'Minimum Tick', 'visualizer' ),
				'xAxes[ticks][suggestedMin]',
				isset( $this->xAxes['ticks']['suggestedMin'] ) ? $this->xAxes['ticks']['suggestedMin'] : '',
				esc_html__( 'Adjustment used when calculating the minimum data value.', 'visualizer' )
			);
			self::_renderTextItem(
				esc_html__( 'Maximum Tick', 'visualizer' ),
				'xAxes[ticks][suggestedMax]',
				isset( $this->xAxes['ticks']['suggestedMax'] ) ? $this->xAxes['ticks']['suggestedMax'] : '',
				esc_html__( 'Adjustment used when calculating the maximum data value.', 'visualizer' )
			);
			self::_renderTextItem(
				esc_html__( 'Maximum number of ticks to show', 'visualizer' ),
				'xAxes[ticks][maxTicksLimit]',
				isset( $this->xAxes['ticks']['maxTicksLimit'] ) ? $this->xAxes['ticks']['maxTicksLimit'] : '11',
				esc_html__( 'Maximum number of ticks and gridlines to show.', 'visualizer' ),
				11,
				'number',
				array( 'min' => 0, 'max' => 100 )
			);
			self::_renderTextItem(
				esc_html__( 'Step size', 'visualizer' ),
				'xAxes[ticks][stepSize]',
				isset( $this->xAxes['ticks']['stepSize'] ) ? $this->xAxes['ticks']['stepSize'] : '1',
				esc_html__( 'User defined fixed step size for the scale.', 'visualizer' ),
				1,
				'number',
				array( 'min' => 1, 'max' => 100, 'step' => 0.5 )
			);

		self::_renderSectionEnd();

	}

	/**
	 * Renders tick settings block for vertical axis settings.
	 *
	 * @since 3.3.0
	 *
	 * @access protected
	 */
	protected function _renderVerticalTickSettings() {
		self::_renderSectionStart( esc_html__( 'Tick Settings', 'visualizer' ), false );

			self::_renderTextItem(
				esc_html__( 'Minimum Tick', 'visualizer' ),
				'yAxes[ticks][suggestedMin]',
				isset( $this->yAxes['ticks']['suggestedMin'] ) ? $this->yAxes['ticks']['suggestedMin'] : '',
				esc_html__( 'Adjustment used when calculating the minimum data value.', 'visualizer' )
			);
			self::_renderTextItem(
				esc_html__( 'Maximum Tick', 'visualizer' ),
				'yAxes[ticks][suggestedMax]',
				isset( $this->yAxes['ticks']['suggestedMax'] ) ? $this->yAxes['ticks']['suggestedMax'] : '',
				esc_html__( 'Adjustment used when calculating the maximum data value.', 'visualizer' )
			);
			self::_renderTextItem(
				esc_html__( 'Maximum number of ticks to show', 'visualizer' ),
				'yAxes[ticks][maxTicksLimit]',
				isset( $this->yAxes['ticks']['maxTicksLimit'] ) ? $this->yAxes['ticks']['maxTicksLimit'] : '11',
				esc_html__( 'Maximum number of ticks and gridlines to show.', 'visualizer' ),
				11,
				'number',
				array( 'min' => 0, 'max' => 100 )
			);
			self::_renderTextItem(
				esc_html__( 'Step size', 'visualizer' ),
				'yAxes[ticks][stepSize]',
				isset( $this->yAxes['ticks']['stepSize'] ) ? $this->yAxes['ticks']['stepSize'] : '1',
				esc_html__( 'User defined fixed step size for the scale.', 'visualizer' ),
				1,
				'number',
				array( 'min' => 1, 'max' => 100, 'step' => 0.5 )
			);

		self::_renderSectionEnd();

	}

	/**
	 * Renders general settings block for horizontal axis settings.
	 *
	 * @since 3.3.0
	 *
	 * @access protected
	 */
	protected function _renderHorizontalAxisGeneralSettings() {
		self::_renderSectionStart( esc_html__( 'General Settings', 'visualizer' ), false );

			self::_renderTextItem(
				esc_html__( 'Axis Title', 'visualizer' ),
				'xAxes[scaleLabel][labelString]',
				isset( $this->xAxes['scaleLabel']['labelString'] ) ? $this->xAxes['scaleLabel']['labelString'] : '',
				esc_html__( 'The title of the vertical axis.', 'visualizer' )
			);

		self::_renderSectionEnd();

		self::_renderSectionStart( esc_html__( 'Font Styles', 'visualizer' ), false );

			self::_renderColorPickerItem(
				esc_html__( 'Font Color', 'visualizer' ),
				'xAxes[scaleLabel][fontColor]',
				isset( $this->xAxes['scaleLabel']['fontColor'] ) ? $this->xAxes['scaleLabel']['fontColor'] : '',
				'#000'
			);

			echo '<div class="viz-section-item">';
				echo '<a class="more-info" href="javascript:;">[?]</a>';
				echo '<b>', esc_html__( 'Family And Size', 'visualizer' ), '</b>';

				echo '<table class="viz-section-table" cellspacing="0" cellpadding="0" border="0">';
					echo '<tr>';
						echo '<td class="viz-section-table-column">';
							echo '<select name="xAxes[scaleLabel][fontFamily]" class="control-select">';
								echo '<option></option>';
		foreach ( self::$_fontFamilies as $font => $label ) {
			echo '<option value="', $font, '"', selected( $font, $this->xAxes['scaleLabel']['fontFamily'], false ), '>';
			echo $label;
			echo '</option>';
		}
							echo '</select>';
						echo '</td>';
						echo '<td class="viz-section-table-column">';
							echo '<select name="xAxes[scaleLabel][fontSize]" class="control-select">';
								echo '<option></option>';
		for ( $i = 7; $i <= 20; $i++ ) {
			echo '<option value="', $i, '"', selected( $i, $this->xAxes['scaleLabel']['fontSize'], false ), '>', $i, '</option>';
		}
							echo '</select>';
						echo '</td>';
					echo '</tr>';
				echo '</table>';

				echo '<p class="viz-section-description">';
					esc_html_e( 'The default font family and size for the Y Axes in the chart.', 'visualizer' );
				echo '</p>';
			echo '</div>';

		self::_renderSectionEnd();

		self::_renderSectionStart( esc_html__( 'Formatting', 'visualizer' ), false );

			self::_renderTextItem(
				esc_html__( 'Data Format', 'visualizer' ),
				'xAxes_format',
				isset( $this->xAxes_format ) ? $this->xAxes_format : '',
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
						'<a href="http://userguide.icu-project.org/formatparse/datetime#TOC-Date-Time-Format-Syntax" target="_blank">',
						'</a>'
					)
				)
			);

		self::_renderSectionEnd();
	}

	/**
	 * Renders vertical axis settings.
	 *
	 * @since 3.3.0
	 *
	 * @access protected
	 */
	protected function _renderVerticalAxisSettings() {
		self::_renderGroupStart( esc_html__( 'Vertical Axis Settings', 'visualizer' ) );
			$this->_renderVerticalAxisGeneralSettings();
			$this->_renderVerticalTickSettings();
		self::_renderGroupEnd();
	}


	/**
	 * Renders general settings block for vertical axis settings.
	 *
	 * @since 3.3.0
	 *
	 * @access protected
	 */
	protected function _renderVerticalAxisGeneralSettings() {
		self::_renderSectionStart( esc_html__( 'General Settings', 'visualizer' ), false );

			self::_renderTextItem(
				esc_html__( 'Axis Title', 'visualizer' ),
				'yAxes[scaleLabel][labelString]',
				isset( $this->yAxes['scaleLabel']['labelString'] ) ? $this->yAxes['scaleLabel']['labelString'] : '',
				esc_html__( 'The title of the vertical axis.', 'visualizer' )
			);

		self::_renderSectionEnd();

		self::_renderSectionStart( esc_html__( 'Font Styles', 'visualizer' ), false );

			self::_renderColorPickerItem(
				esc_html__( 'Font Color', 'visualizer' ),
				'yAxes[scaleLabel][fontColor]',
				isset( $this->yAxes['scaleLabel']['fontColor'] ) ? $this->yAxes['scaleLabel']['fontColor'] : '',
				'#000'
			);

			echo '<div class="viz-section-item">';
				echo '<a class="more-info" href="javascript:;">[?]</a>';
				echo '<b>', esc_html__( 'Family And Size', 'visualizer' ), '</b>';

				echo '<table class="viz-section-table" cellspacing="0" cellpadding="0" border="0">';
					echo '<tr>';
						echo '<td class="viz-section-table-column">';
							echo '<select name="yAxes[scaleLabel][fontFamily]" class="control-select">';
								echo '<option></option>';
		foreach ( self::$_fontFamilies as $font => $label ) {
			echo '<option value="', $font, '"', selected( $font, $this->yAxes['scaleLabel']['fontFamily'], false ), '>';
			echo $label;
			echo '</option>';
		}
							echo '</select>';
						echo '</td>';
						echo '<td class="viz-section-table-column">';
							echo '<select name="yAxes[scaleLabel][fontSize]" class="control-select">';
								echo '<option></option>';
		for ( $i = 7; $i <= 20; $i++ ) {
			echo '<option value="', $i, '"', selected( $i, $this->yAxes['scaleLabel']['fontSize'], false ), '>', $i, '</option>';
		}
							echo '</select>';
						echo '</td>';
					echo '</tr>';
				echo '</table>';

				echo '<p class="viz-section-description">';
					esc_html_e( 'The default font family and size for the Y Axes in the chart.', 'visualizer' );
				echo '</p>';
			echo '</div>';

		self::_renderSectionEnd();

		self::_renderSectionStart( esc_html__( 'Formatting', 'visualizer' ), false );

			self::_renderTextItem(
				esc_html__( 'Data Format', 'visualizer' ),
				'yAxes_format',
				isset( $this->yAxes_format ) ? $this->yAxes_format : '',
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
						'<a href="http://userguide.icu-project.org/formatparse/datetime#TOC-Date-Time-Format-Syntax" target="_blank">',
						'</a>'
					)
				)
			);

		self::_renderSectionEnd();

	}
}
