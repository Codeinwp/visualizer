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
 * Class for table chart sidebar settings.
 *
 * @since 1.0.0
 */
class Visualizer_Render_Sidebar_Type_GoogleCharts_Tabular extends Visualizer_Render_Sidebar_Columnar {

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
		// @codingStandardsIgnoreLine WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		$this->_includeCurveTypes = false;
	}

	/**
	 * Renders template.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _toHTML() {
		// @codingStandardsIgnoreLine WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		$this->_supportsAnimation = false;
		$this->_renderGeneralSettings();
		$this->_renderColumnarSettings();
		$this->_renderSeriesSettings();
		$this->_renderViewSettings();
		$this->_renderAdvancedSettings();
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
	protected function _renderColumnarSettings() {
		self::_renderGroupStart( esc_html__( 'Table Settings', 'visualizer' ) );
			self::_renderSectionStart();

				self::_renderCheckboxItem(
					esc_html__( 'Enable Pagination', 'visualizer' ),
					'pagination',
					$this->pagination,
					1,
					esc_html__( 'To enable paging through the data.', 'visualizer' )
				);

				echo '<div class="viz-section-delimiter section-delimiter"></div>';

				self::_renderTextItem(
					esc_html__( 'Number of rows per page', 'visualizer' ),
					'pageSize',
					$this->pageSize, // @codingStandardsIgnoreLine WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
					esc_html__( 'The number of rows in each page, when paging is enabled.', 'visualizer' ),
					'10'
				);

				echo '<div class="viz-section-delimiter section-delimiter"></div>';

				self::_renderCheckboxItem(
					esc_html__( 'Disable Sort', 'visualizer' ),
					'sort',
					$this->sort,
					'disable',
					esc_html__( 'To disable sorting on columns.', 'visualizer' )
				);

				echo '<div class="viz-section-delimiter section-delimiter"></div>';

				self::_renderTextItem(
					esc_html__( 'Freeze Columns', 'visualizer' ),
					'frozenColumns',
					$this->frozenColumns, // @codingStandardsIgnoreLine WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
					esc_html__( 'The number of columns from the left that will be frozen.', 'visualizer' ),
					'',
					'number',
					array(
						'min' => 0,
					)
				);

				echo '<div class="viz-section-delimiter section-delimiter"></div>';

				self::_renderCheckboxItem(
					esc_html__( 'Allow HTML', 'visualizer' ),
					'allowHtml',
					$this->allowHtml, // @codingStandardsIgnoreLine WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
					1,
					esc_html__( 'If enabled, formatted values of cells that include HTML tags will be rendered as HTML.', 'visualizer' )
				);

				echo '<div class="viz-section-delimiter section-delimiter"></div>';

				self::_renderCheckboxItem(
					esc_html__( 'Right to Left table', 'visualizer' ),
					'rtlTable',
					$this->rtlTable, // @codingStandardsIgnoreLine WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
					1,
					esc_html__( 'Adds basic support for right-to-left languages.', 'visualizer' )
				);

			self::_renderSectionEnd();
		self::_renderGroupEnd();

		self::_renderGroupStart( esc_html__( 'Row/Cell Settings', 'visualizer' ) );
			self::_renderSectionStart( esc_html__( 'Header Row', 'visualizer' ) );
				self::_renderColorPickerItem(
					esc_html__( 'Background Color', 'visualizer' ),
					'customcss[headerRow][background-color]',
					isset( $this->customcss['headerRow']['background-color'] ) ? $this->customcss['headerRow']['background-color'] : null,
					null
				);

				self::_renderColorPickerItem(
					esc_html__( 'Color', 'visualizer' ),
					'customcss[headerRow][color]',
					isset( $this->customcss['headerRow']['color'] ) ? $this->customcss['headerRow']['color'] : null,
					null
				);

				self::_renderTextItem(
					esc_html__( 'Text Orientation', 'visualizer' ),
					'customcss[headerRow][transform]',
					isset( $this->customcss['headerRow']['transform'] ) ? $this->customcss['headerRow']['transform'] : null,
					esc_html__( 'In degrees.', 'visualizer' ),
					'',
					'number',
					array(
						'min' => -180,
						'max' => 180,
					)
				);
			self::_renderSectionEnd();

			self::_renderSectionStart( esc_html__( 'Table Row', 'visualizer' ) );
				self::_renderColorPickerItem(
					esc_html__( 'Background Color', 'visualizer' ),
					'customcss[tableRow][background-color]',
					isset( $this->customcss['tableRow']['background-color'] ) ? $this->customcss['tableRow']['background-color'] : null,
					null
				);

				self::_renderColorPickerItem(
					esc_html__( 'Color', 'visualizer' ),
					'customcss[tableRow][color]',
					isset( $this->customcss['tableRow']['color'] ) ? $this->customcss['tableRow']['color'] : null,
					null
				);

				self::_renderTextItem(
					esc_html__( 'Text Orientation', 'visualizer' ),
					'customcss[tableRow][transform]',
					isset( $this->customcss['tableRow']['transform'] ) ? $this->customcss['tableRow']['transform'] : null,
					esc_html__( 'In degrees.', 'visualizer' ),
					'',
					'number',
					array(
						'min' => -180,
						'max' => 180,
					)
				);
			self::_renderSectionEnd();

			self::_renderSectionStart( esc_html__( 'Odd Table Row', 'visualizer' ) );
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

			self::_renderSectionStart( esc_html__( 'Selected Table Row', 'visualizer' ) );
				self::_renderColorPickerItem(
					esc_html__( 'Background Color', 'visualizer' ),
					'customcss[selectedTableRow][background-color]',
					isset( $this->customcss['selectedTableRow']['background-color'] ) ? $this->customcss['selectedTableRow']['background-color'] : null,
					null
				);

				self::_renderColorPickerItem(
					esc_html__( 'Color', 'visualizer' ),
					'customcss[selectedTableRow][color]',
					isset( $this->customcss['selectedTableRow']['color'] ) ? $this->customcss['selectedTableRow']['color'] : null,
					null
				);

				self::_renderTextItem(
					esc_html__( 'Text Orientation', 'visualizer' ),
					'customcss[selectedTableRow][transform]',
					isset( $this->customcss['selectedTableRow']['transform'] ) ? $this->customcss['selectedTableRow']['transform'] : null,
					esc_html__( 'In degrees.', 'visualizer' ),
					'',
					'number',
					array(
						'min' => -180,
						'max' => 180,
					)
				);
			self::_renderSectionEnd();

			self::_renderSectionStart( esc_html__( 'Hover Table Row', 'visualizer' ) );
				self::_renderColorPickerItem(
					esc_html__( 'Background Color', 'visualizer' ),
					'customcss[hoverTableRow][background-color]',
					isset( $this->customcss['hoverTableRow']['background-color'] ) ? $this->customcss['hoverTableRow']['background-color'] : null,
					null
				);

				self::_renderColorPickerItem(
					esc_html__( 'Color', 'visualizer' ),
					'customcss[hoverTableRow][color]',
					isset( $this->customcss['hoverTableRow']['color'] ) ? $this->customcss['hoverTableRow']['color'] : null,
					null
				);

				self::_renderTextItem(
					esc_html__( 'Text Orientation', 'visualizer' ),
					'customcss[hoverTableRow][transform]',
					isset( $this->customcss['hoverTableRow']['transform'] ) ? $this->customcss['hoverTableRow']['transform'] : null,
					esc_html__( 'In degrees.', 'visualizer' ),
					'',
					'number',
					array(
						'min' => -180,
						'max' => 180,
					)
				);
			self::_renderSectionEnd();

			self::_renderSectionStart( esc_html__( 'Header Cell', 'visualizer' ) );
				self::_renderColorPickerItem(
					esc_html__( 'Background Color', 'visualizer' ),
					'customcss[headerCell][background-color]',
					isset( $this->customcss['headerCell']['background-color'] ) ? $this->customcss['headerCell']['background-color'] : null,
					null
				);

				self::_renderColorPickerItem(
					esc_html__( 'Color', 'visualizer' ),
					'customcss[headerCell][color]',
					isset( $this->customcss['headerCell']['color'] ) ? $this->customcss['headerCell']['color'] : null,
					null
				);

				self::_renderTextItem(
					esc_html__( 'Text Orientation', 'visualizer' ),
					'customcss[headerCell][transform]',
					isset( $this->customcss['headerCell']['transform'] ) ? $this->customcss['headerCell']['transform'] : null,
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

		self::_renderGroupEnd();
	}

	/**
	 * Renders concreate series settings.
	 *
	 * @since 1.4.0
	 *
	 * @access protected
	 * @param int $index The series index.
	 */
	protected function _renderSeries( $index ) {
		$this->_renderFormatField( $index );
	}

	/**
	 * Renders combo series settings
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _renderSeriesSettings() {
		parent::_renderSeriesSettings();
	}

}
