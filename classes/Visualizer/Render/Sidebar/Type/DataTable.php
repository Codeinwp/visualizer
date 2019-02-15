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
 * Class for datatables.net table chart sidebar settings.
 *
 * @since 1.0.0
 */
class Visualizer_Render_Sidebar_Type_DataTable extends Visualizer_Render_Sidebar {


	/**
	 * The URL for the JavaScript file.
	 *
	 * @access private
	 * @var string
	 */
	private static $_js     = '//cdn.datatables.net/v/dt/dt-1.10.18/b-1.5.4/b-print-1.5.4/fc-3.2.5/fh-3.1.4/r-2.2.2/sc-1.5.0/sl-1.2.6/datatables.min.js';

	/**
	 * The URL for the CSS file.
	 *
	 * @access private
	 * @var string
	 */
	private static $_css    = '//cdn.datatables.net/v/dt/dt-1.10.18/b-1.5.4/b-print-1.5.4/fc-3.2.5/fh-3.1.4/r-2.2.2/sc-1.5.0/sl-1.2.6/datatables.min.css';

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param array $data The data what has to be associated with this render.
	 */
	public function __construct( $data = array() ) {
		$this->_library = 'datatables';
		$this->_includeCurveTypes = false;

		parent::__construct( $data );
	}

	/**
	 * Registers additional hooks.
	 *
	 * @access protected
	 */
	protected function hooks() {
		if ( $this->_library === 'datatables' ) {
			add_filter( 'visualizer_assets_render', array( $this, 'load_assets' ), 10, 2 );
		}
	}

	/**
	 * Registers assets.
	 *
	 * @access public
	 */
	function load_assets( $deps, $is_frontend ) {
		if ( ! wp_script_is( 'moment', 'registered' ) ) {
			wp_register_script( 'moment', '//cdnjs.cloudflare.com/ajax/libs/moment.js/2.22.2/moment.min.js', array(), Visualizer_Plugin::VERSION );
		}

		wp_register_script( 'visualizer-datatables', self::$_js, array( 'jquery-ui-core', 'moment' ), Visualizer_Plugin::VERSION );
		wp_enqueue_style( 'visualizer-datatables', self::$_css, array(), Visualizer_Plugin::VERSION );

		wp_register_script(
			'visualizer-render-datatables-lib',
			VISUALIZER_ABSURL . 'js/render-datatables.js',
			array(
				'visualizer-datatables',
			),
			Visualizer_Plugin::VERSION,
			true
		);

		return array_merge(
			$deps,
			array( 'visualizer-render-datatables-lib' )
		);
	}

	/**
	 * Enqueue assets.
	 */
	public static function enqueue_assets( $deps = array() ) {
		wp_enqueue_style( 'visualizer-datatables', self::$_css, array(), Visualizer_Plugin::VERSION );
		wp_enqueue_script( 'visualizer-datatables', self::$_js, array( 'jquery-ui-core' ), Visualizer_Plugin::VERSION );
		wp_enqueue_script( 'visualizer-render-datatables-lib', VISUALIZER_ABSURL . 'js/render-datatables.js', array_merge( $deps, array( 'jquery-ui-core', 'visualizer-datatables' ) ), Visualizer_Plugin::VERSION, true );
		return 'visualizer-render-datatables-lib';
	}

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
		$this->_renderTableSettings();
		 $this->_renderColumnSettings();
		$this->_renderAdvancedSettings();
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
	 * Renders line settings items.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _renderTableSettings() {
		self::_renderGroupStart( esc_html__( 'Table Settings', 'visualizer' ) );
			self::_renderSectionStart();

				self::_renderCheckboxItem(
					esc_html__( 'Enable Pagination', 'visualizer' ),
					'paging_bool',
					$this->paging_bool,
					'true',
					esc_html__( 'To enable paging through the data.', 'visualizer' )
				);

				echo '<div class="viz-section-delimiter section-delimiter"></div>';

				self::_renderTextItem(
					esc_html__( 'Number of rows per page', 'visualizer' ),
					'pageLength_int',
					$this->pageLength_int,
					esc_html__( 'The number of rows in each page, when paging is enabled.', 'visualizer' ),
					'10'
				);

				echo '<div class="viz-section-delimiter section-delimiter"></div>';

				self::_renderSelectItem(
					esc_html__( 'Pagination type', 'visualizer' ),
					'pagingType',
					$this->pagingType,
					array(
						'numbers'  => esc_html__( 'Page number buttons only', 'visualizer' ),
						'simple'  => esc_html__( '\'Previous\' and \'Next\' buttons only', 'visualizer' ),
						'simple_numbers'  => esc_html__( '\'Previous\' and \'Next\' buttons, plus page numbers', 'visualizer' ),
						'full'  => esc_html__( '\'First\', \'Previous\', \'Next\' and \'Last\' buttons', 'visualizer' ),
						'full_numbers'  => esc_html__( '\'First\', \'Previous\', \'Next\' and \'Last\' buttons, plus page numbers', 'visualizer' ),
						'first_last_numbers'  => esc_html__( '\'First\' and \'Last\' buttons, plus page numbers', 'visualizer' ),
					),
					esc_html__( 'Determines what type of pagination options to show.', 'visualizer' )
				);

				do_action( 'visualizer_chart_settings', __CLASS__, $this->_data, 'pagination' );

				echo '<div class="viz-section-delimiter section-delimiter"></div>';

				self::_renderCheckboxItem(
					esc_html__( 'Scroll Collapse', 'visualizer' ),
					'scrollCollapse_bool',
					$this->scrollCollapse_bool,
					'true',
					esc_html__( 'Allow the table to reduce in height when a limited number of rows are shown', 'visualizer' )
				);

				echo '<div class="viz-section-delimiter section-delimiter"></div>';

				self::_renderCheckboxItem(
					esc_html__( 'Disable Sort', 'visualizer' ),
					'ordering_bool',
					$this->ordering_bool,
					'false',
					esc_html__( 'To disable sorting on columns.', 'visualizer' )
				);

				echo '<div class="viz-section-delimiter section-delimiter"></div>';

				self::_renderCheckboxItem(
					esc_html__( 'Freeze Header/Footer', 'visualizer' ),
					'fixedHeader_bool',
					$this->fixedHeader_bool,
					'true',
					esc_html__( 'Freeze the header and footer.', 'visualizer' )
				);

				echo '<div class="viz-section-delimiter section-delimiter"></div>';

				self::_renderCheckboxItem(
					esc_html__( 'Responsive table?', 'visualizer' ),
					'responsive_bool',
					$this->responsive_bool,
					'true',
					esc_html__( 'Enable the table to be responsive.', 'visualizer' )
				);

				do_action( 'visualizer_chart_settings', __CLASS__, $this->_data, 'table' );

			self::_renderSectionEnd();
		self::_renderGroupEnd();

		self::_renderGroupStart( esc_html__( 'Row/Cell Settings', 'visualizer' ) );

			self::_renderSectionStart( esc_html__( 'Odd Table Row', 'visualizer' ) );

				self::_renderSectionDescription( esc_html__( 'These values will be applied once you save the chart.', 'visualizer' ) );

				self::_renderColorPickerItem(
					esc_html__( 'Background Color', 'visualizer' ),
					'customcss[oddTableRow][background-color]',
					isset( $this->customcss['oddTableRow']['background-color'] ) ? $this->customcss['oddTableRow']['background-color'] : null,
					null
				);

				self::_renderColorPickerItem(
					esc_html__( 'Color', 'visualizer' ),
					'customcss[oddTableRow][color]',
					isset( $this->customcss['oddTableRow']['color'] ) ? $this->customcss['oddTableRow']['color'] : null,
					null
				);

				self::_renderTextItem(
					esc_html__( 'Text Orientation', 'visualizer' ),
					'customcss[oddTableRow][transform]',
					isset( $this->customcss['oddTableRow']['transform'] ) ? $this->customcss['oddTableRow']['transform'] : null,
					esc_html__( 'In degrees.', 'visualizer' ),
					'',
					'number',
					array(
						'min' => -180,
						'max' => 180,
					)
				);
			self::_renderSectionEnd();

			self::_renderSectionStart( esc_html__( 'Even Table Row', 'visualizer' ) );

				self::_renderSectionDescription( esc_html__( 'These values will be applied once you save the chart.', 'visualizer' ) );

				self::_renderColorPickerItem(
					esc_html__( 'Background Color', 'visualizer' ),
					'customcss[evenTableRow][background-color]',
					isset( $this->customcss['evenTableRow']['background-color'] ) ? $this->customcss['evenTableRow']['background-color'] : null,
					null
				);

				self::_renderColorPickerItem(
					esc_html__( 'Color', 'visualizer' ),
					'customcss[evenTableRow][color]',
					isset( $this->customcss['evenTableRow']['color'] ) ? $this->customcss['evenTableRow']['color'] : null,
					null
				);

				self::_renderTextItem(
					esc_html__( 'Text Orientation', 'visualizer' ),
					'customcss[evenTableRow][transform]',
					isset( $this->customcss['evenTableRow']['transform'] ) ? $this->customcss['evenTableRow']['transform'] : null,
					esc_html__( 'In degrees.', 'visualizer' ),
					'',
					'number',
					array(
						'min' => -180,
						'max' => 180,
					)
				);
			self::_renderSectionEnd();

			self::_renderSectionStart( esc_html__( 'Table Cell', 'visualizer' ) );

				self::_renderSectionDescription( esc_html__( 'These values will be applied once you save the chart.', 'visualizer' ) );

				self::_renderColorPickerItem(
					esc_html__( 'Background Color', 'visualizer' ),
					'customcss[tableCell][background-color]',
					isset( $this->customcss['tableCell']['background-color'] ) ? $this->customcss['tableCell']['background-color'] : null,
					null
				);

				self::_renderColorPickerItem(
					esc_html__( 'Color', 'visualizer' ),
					'customcss[tableCell][color]',
					isset( $this->customcss['tableCell']['color'] ) ? $this->customcss['tableCell']['color'] : null,
					null
				);

				self::_renderTextItem(
					esc_html__( 'Text Orientation', 'visualizer' ),
					'customcss[tableCell][transform]',
					isset( $this->customcss['tableCell']['transform'] ) ? $this->customcss['tableCell']['transform'] : null,
					esc_html__( 'In degrees.', 'visualizer' ),
					'',
					'number',
					array(
						'min' => -180,
						'max' => 180,
					)
				);
			self::_renderSectionEnd();

			do_action( 'visualizer_chart_settings', __CLASS__, $this->_data, 'style' );

		self::_renderGroupEnd();
	}


	/**
	 * Renders combo series settings
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _renderColumnSettings() {
		self::_renderGroupStart( esc_html__( 'Column Settings', 'visualizer' ) );
		for ( $i = 0, $cnt = count( $this->__series ); $i < $cnt; $i++ ) {
			if ( ! empty( $this->__series[ $i ]['label'] ) ) {
				self::_renderSectionStart( esc_html( $this->__series[ $i ]['label'] ), false );
					$this->_renderFormatField( $i );
				self::_renderSectionEnd();
			}
		}
		self::_renderGroupEnd();
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
		switch ( $this->__series[ $index ]['type'] ) {
			case 'number':
				self::_renderTextItem(
					esc_html__( 'Thousands Separator', 'visualizer' ),
					'series[' . $index . '][format][thousands]',
					isset( $this->series[ $index ]['format']['thousands'] ) ? $this->series[ $index ]['format']['thousands'] : ',',
					null,
					','
				);
				self::_renderTextItem(
					esc_html__( 'Decimal Separator', 'visualizer' ),
					'series[' . $index . '][format][decimal]',
					isset( $this->series[ $index ]['format']['decimal'] ) ? $this->series[ $index ]['format']['decimal'] : '.',
					null,
					'.'
				);
				self::_renderTextItem(
					esc_html__( 'Precision', 'visualizer' ),
					'series[' . $index . '][format][precision]',
					isset( $this->series[ $index ]['format']['precision'] ) ? $this->series[ $index ]['format']['precision'] : '',
					esc_html__( 'Round values to how many decimal places?', 'visualizer' ),
					''
				);
				self::_renderTextItem(
					esc_html__( 'Prefix', 'visualizer' ),
					'series[' . $index . '][format][prefix]',
					isset( $this->series[ $index ]['format']['prefix'] ) ? $this->series[ $index ]['format']['prefix'] : '',
					null,
					''
				);
				self::_renderTextItem(
					esc_html__( 'Suffix', 'visualizer' ),
					'series[' . $index . '][format][suffix]',
					isset( $this->series[ $index ]['format']['suffix'] ) ? $this->series[ $index ]['format']['suffix'] : '',
					null,
					''
				);
				break;
			case 'date':
			case 'datetime':
			case 'timeofday':
				self::_renderTextItem(
					esc_html__( 'Display Date Format', 'visualizer' ),
					'series[' . $index . '][format][to]',
					isset( $this->series[ $index ]['format']['to'] ) ? $this->series[ $index ]['format']['to'] : '',
					sprintf( esc_html__( 'Enter custom format pattern to apply to this series value, similar to the %1$sdate and time formats here%2$s.', 'visualizer' ), '<a href="https://momentjs.com/docs/#/displaying/" target="_blank">', '</a>' ),
					'Do MMM YYYY'
				);
				self::_renderTextItem(
					esc_html__( 'Source Date Format', 'visualizer' ),
					'series[' . $index . '][format][from]',
					isset( $this->series[ $index ]['format']['from'] ) ? $this->series[ $index ]['format']['from'] : '',
					sprintf( esc_html__( 'What format is the source date in? Similar to the %1$sdate and time formats here%2$s.', 'visualizer' ), '<a href="https://momentjs.com/docs/#/displaying/" target="_blank">', '</a>' ),
					'YYYY-MM-DD'
				);
				break;
		}
	}

}
