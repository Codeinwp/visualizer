<?php

/**
 * Base class for sidebar settigns of ChartJS based charts.
 *
 * @category Visualizer
 * @package Render
 * @subpackage Sidebar
 *
 * @since 3.2.0
 * @abstract
 */
abstract class Visualizer_Render_Sidebar_ChartJS extends Visualizer_Render_Sidebar {

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
	 * The constructor.
	 */
	public function __construct( $data = array() ) {
		$this->_library = 'chartjs';
		parent::__construct( $data );

		$this->_legendPositions = array(
			'left'  => esc_html__( 'Left of the chart', 'visualizer' ),
			'right'  => esc_html__( 'Right of the chart', 'visualizer' ),
			'top'    => esc_html__( 'Above the chart', 'visualizer' ),
			'bottom' => esc_html__( 'Below the chart', 'visualizer' ),
		);

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
		// empty.
	}

	/**
	 * Renders settings specific to the Bar chart.
	 *
	 * @since 3.3.0
	 *
	 * @access protected
	 */
	protected function _renderChartTypeSettings() {
		// empty
	}

	/**
	 * Registers additional hooks.
	 *
	 * @access protected
	 */
	protected function hooks() {
		if ( $this->_library === 'chartjs' ) {
			add_filter( 'visualizer_assets_render', array( $this, 'load_chartjs_assets' ), 10, 2 );
		}
	}

	/**
	 * Loads the assets.
	 */
	function load_chartjs_assets( $deps, $is_frontend ) {
		$this->load_dependent_assets( array( 'moment', 'numeral' ) );

		wp_register_script( 'chartjs', VISUALIZER_ABSURL . 'js/lib/chartjs.min.js', array( 'numeral', 'moment' ), null, true );
		wp_register_script(
			'visualizer-render-chartjs-lib',
			VISUALIZER_ABSURL . 'js/render-chartjs.js',
			array(
				'chartjs',
			),
			Visualizer_Plugin::VERSION,
			true
		);

		return array_merge(
			$deps,
			array( 'visualizer-render-chartjs-lib' )
		);

	}

	/**
	 * Renders series settings group.
	 *
	 * @since 3.3.0
	 *
	 * @access protected
	 */
	protected function _renderSeriesSettings() {
		self::_renderGroupStart( esc_html__( 'Series Settings', 'visualizer' ) );
			self::_renderSectionStart();
				self::_renderSectionDescription( esc_html__( 'If you have just updated/modified the chart data, you may need to save it before the new data reflects in the settings.', 'visualizer' ), 'viz-info-msg' );
			self::_renderSectionEnd();

		for ( $i = 1, $cnt = count( $this->__series ); $i < $cnt; $i++ ) {
			if ( ! empty( $this->__series[ $i ]['label'] ) ) {
				self::_renderSectionStart( esc_html( $this->__series[ $i ]['label'] ), false );
					$this->_renderSeries( $i - 1 );
				self::_renderSectionEnd();
			}
		}
		self::_renderGroupEnd();
	}

	/**
	 * Renders concrete series settings.
	 *
	 * @since 3.3.0
	 *
	 * @access protected
	 * @param int $index The series index.
	 */
	protected function _renderSeries( $index ) {
		$this->_renderFormatField( $index );
		$this->_renderChartTypeSeries( $index );

	}

	/**
	 * Renders template.
	 *
	 * @since 3.3.0
	 *
	 * @access protected
	 */
	protected function _toHTML() {
		$this->_renderGeneralSettings();
		$this->_renderAxesSettings();
		$this->_renderChartTypeSettings();
		$this->_renderSeriesSettings();
		$this->_renderViewSettings();
		$this->_renderAdvancedSettings();
	}

	/**
	 * Renders chart title settings.
	 *
	 * @since 3.3.0
	 *
	 * @access protected
	 */
	protected function _renderChartTitleSettings() {
		self::_renderTextItem(
			esc_html__( 'Chart Title', 'visualizer' ),
			'title[text]',
			isset( $this->title['text'] ) ? $this->title['text'] : '',
			esc_html__( 'Text to display above the chart.', 'visualizer' )
		);

		self::_renderColorPickerItem(
			esc_html__( 'Chart Title Color', 'visualizer' ),
			'title[fontColor]',
			isset( $this->title['fontColor'] ) ? $this->title['fontColor'] : '',
			'#000'
		);

		echo '<div class="viz-section-delimiter"></div>';

		self::_renderTextAreaItem(
			esc_html__( 'Chart Description', 'visualizer' ),
			'description',
			$this->description,
			sprintf( esc_html__( 'Description to display in the structured data schema as explained %1$shere%2$s', 'visualizer' ), '<a href="https://developers.google.com/search/docs/data-types/dataset#dataset" target="_blank">', '</a>' )
		);

	}

	/**
	 * Renders chart general settings group.
	 *
	 * @since 3.3.0
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
					// let's have a default otherwise the chart behaves weird when hovering in edit mode
					isset( $this->legend['position'] ) ? $this->legend['position'] : 'top',
					$this->_legendPositions,
					esc_html__( 'Determines where to place the legend, compared to the chart area.', 'visualizer' )
				);

				self::_renderCheckboxItem(
					esc_html__( 'Show datasets in reverse order', 'visualizer' ),
					'legend[reverse]',
					isset( $this->legend['reverse'] ) ? $this->legend['reverse'] : false,
					'true',
					esc_html__( 'Legend will show datasets in reverse order.', 'visualizer' )
				);

				echo '<div class="viz-section-item">';
					echo '<a class="more-info" href="javascript:;">[?]</a>';
					echo '<b>', esc_html__( 'Family And Size', 'visualizer' ), '</b>';

					echo '<table class="viz-section-table" cellspacing="0" cellpadding="0" border="0">';
						echo '<tr>';
							echo '<td class="viz-section-table-column">';
								echo '<select name="legend[labels][fontName]" class="control-select">';
									echo '<option></option>';
		foreach ( self::$_fontFamilies as $font => $label ) {
			echo '<option value="', $font, '"', selected( $font, $this->legend['labels']['fontName'], false ), '>';
			echo $label;
			echo '</option>';
		}
								echo '</select>';
							echo '</td>';
							echo '<td class="viz-section-table-column">';
								echo '<select name="legend[labels][fontSize]" class="control-select">';
									echo '<option></option>';
		for ( $i = 7; $i <= 20; $i++ ) {
			echo '<option value="', $i, '"', selected( $i, $this->legend['labels']['fontSize'], false ), '>', $i, '</option>';
		}
								echo '</select>';
							echo '</td>';
						echo '</tr>';
					echo '</table>';

					self::_renderColorPickerItem(
						esc_html__( 'Font Color', 'visualizer' ),
						'legend[labels][fontColor]',
						isset( $this->legend['labels']['fontColor'] ) ? $this->legend['labels']['fontColor'] : null,
						'#000'
					);

				echo '</div>';

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

		self::_renderTextItem(
			esc_html__( 'Duration', 'visualizer' ),
			'animation[duration]',
			isset( $this->animation['duration'] ) ? $this->animation['duration'] : 1000,
			esc_html__( 'The duration of the animation, in milliseconds', 'visualizer' ),
			1000,
			'number',
			array( 'min' => 1000 )
		);

		self::_renderSelectItem(
			esc_html__( 'Easing', 'visualizer' ),
			'animation[easing]',
			isset( $this->animation['easing'] ) ? $this->animation['easing'] : null,
			array(
				'linear'    => esc_html__( 'Constant speed', 'visualizer' ),
				'easeInQuad' => esc_html__( 'easeInQuad', 'visualizer' ),
				'easeOutQuad' => esc_html__( 'easeOutQuad', 'visualizer' ),
				'easeInOutQuad' => esc_html__( 'easeInOutQuad', 'visualizer' ),
				'easeInCubic' => esc_html__( 'easeInCubic', 'visualizer' ),
				'easeOutCubic' => esc_html__( 'easeOutCubic', 'visualizer' ),
				'easeInOutCubic' => esc_html__( 'easeInOutCubic', 'visualizer' ),
				'easeInQuart' => esc_html__( 'easeInQuart', 'visualizer' ),
				'easeOutQuart' => esc_html__( 'easeOutQuart', 'visualizer' ),
				'easeInOutQuart' => esc_html__( 'easeInOutQuart', 'visualizer' ),
				'easeInQuint' => esc_html__( 'easeInQuint', 'visualizer' ),
				'easeOutQuint' => esc_html__( 'easeOutQuint', 'visualizer' ),
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
		self::_renderCheckboxItem(
			esc_html__( 'Trigger', 'visualizer' ),
			'tooltip[intersect]',
			$this->tooltip['intersect'],
			1,
			esc_html__( 'Determines if the tooltip should only display when the mouse intersects with an element.', 'visualizer' )
		);
	}

	/**
	 * Add the correct description for the manual configuration box.
	 */
	protected function _renderManualConfigDescription() {
		self::_renderSectionStart();
			self::_renderSectionDescription( '<span class="viz-gvlink">' . sprintf( __( 'Configure the graph by providing configuration variables right from the %1$sChartJS API%2$s. You can refer to to some examples %3$shere%4$s.', 'visualizer' ), '<a href="https://www.chartjs.org/docs/latest/configuration/" target="_blank">', '</a>', '<a href="https://docs.themeisle.com/article/728-manual-configuration" target="_blank">', '</a>' ) . '</span>' );
	}

	/**
	 * Add the correct example for the manual configuration box.
	 */
	protected function _renderManualConfigExample() {
		return '{
			"cutoutPercentage": 5,
			"rotation": 60
		}';
	}

	/**
	 * Renders chart view settings group.
	 *
	 * @since 3.3.0
	 *
	 * @access protected
	 */
	protected function _renderViewSettings() {
		self::_renderGroupStart( esc_html__( 'Chart Size', 'visualizer' ) );
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
						esc_html_e( 'Determines the total width and height of the chart. This will only show in the front-end.', 'visualizer' );
					echo '</p>';
				echo '</div>';
			self::_renderSectionEnd();
		self::_renderGroupEnd();
	}
}
