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
abstract class Visualizer_Render_Sidebar_Graph extends Visualizer_Render_Sidebar {

	/**
	 * Determines whether we need to render vertical gridlines options or not.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @var boolean
	 */
	protected $_verticalGridLines;

	/**
	 * Determines whether we need to render horizontal gridlines options or not.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @var boolean
	 */
	protected $_horizontalGridLines;

	/**
	 * The array of available axis positions.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @var array
	 */
	protected $_positions;

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

		$this->_verticalGridLines = true;
		$this->_horizontalGridLines = true;

		$this->_positions = array(
			''     => '',
			'in'   => esc_html__( 'Inside the chart', 'visualizer' ),
			'out'  => esc_html__( 'Outside the chart', 'visualizer' ),
			'none' => esc_html__( 'None', 'visualizer' ),
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
			esc_html__( 'Chart Title', 'visualizer' ),
			'title',
			$this->title,
			esc_html__( 'Text to display above the chart.', 'visualizer' )
		);

		self::_renderSelectItem(
			esc_html__( 'Chart Title Position', 'visualizer' ),
			'titlePosition',
			$this->titlePosition,
			$this->_positions,
			esc_html__( 'Where to place the chart title, compared to the chart area.', 'visualizer' )
		);

		self::_renderColorPickerItem(
			esc_html__( 'Chart Title Color', 'visualizer' ),
			'titleTextStyle[color]',
			isset( $this->titleTextStyle['color'] ) ? $this->titleTextStyle['color'] : null,
			'#000'
		);

		echo '<div class="section-delimiter"></div>';

		self::_renderSelectItem(
			esc_html__( 'Axes Titles Position', 'visualizer' ),
			'axisTitlesPosition',
			$this->axisTitlesPosition,
			$this->_positions,
			esc_html__( 'Determines where to place the axis titles, compared to the chart area.', 'visualizer' )
		);
	}

	/**
	 * Renders general settings block for horizontal axis settings.
	 *
	 * @since 1.4.0
	 *
	 * @access protected
	 */
	protected function _renderHorizontalAxisGeneralSettings() {
		self::_renderTextItem(
			esc_html__( 'Axis Title', 'visualizer' ),
			'hAxis[title]',
			isset( $this->hAxis['title'] ) ? $this->hAxis['title'] : '',
			esc_html__( 'The title of the horizontal axis.', 'visualizer' )
		);

		self::_renderSelectItem(
			esc_html__( 'Text Position', 'visualizer' ),
			'vAxis[textPosition]',
			isset( $this->vAxis['textPosition'] ) ? $this->vAxis['textPosition'] : '',
			$this->_positions,
			esc_html__( 'Position of the horizontal axis text, relative to the chart area.', 'visualizer' )
		);

		self::_renderSelectItem(
			esc_html__( 'Direction', 'visualizer' ),
			'hAxis[direction]',
			isset( $this->hAxis['direction'] ) ? $this->hAxis['direction'] : '',
			array(
				''   => '',
				'1'  => esc_html__( 'Identical Direction', 'visualizer' ),
				'-1' => esc_html__( 'Reverse Direction', 'visualizer' ),
			),
			esc_html__( 'The direction in which the values along the horizontal axis grow.', 'visualizer' )
		);

		self::_renderColorPickerItem(
			esc_html__( 'Base Line Color', 'visualizer' ),
			'vAxis[baselineColor]',
			isset( $this->vAxis['baselineColor'] ) ? $this->vAxis['baselineColor'] : null,
			'#000'
		);
	}

	/**
	 * Renders horizontal axis settings.
	 *
	 * @since 1.2.0
	 *
	 * @access protected
	 */
	protected function _renderHorizontalAxisSettings() {
		self::_renderGroupStart( esc_html__( 'Horizontal Axis Settings', 'visualizer' ) );
			self::_renderSectionStart( esc_html__( 'General Settings', 'visualizer' ), false );
				$this->_renderHorizontalAxisGeneralSettings();
			self::_renderSectionEnd();

		if ( $this->_horizontalGridLines ) {
			self::_renderSectionStart( esc_html__( 'Grid Lines', 'visualizer' ), false );
			self::_renderTextItem(
				esc_html__( 'Count', 'visualizer' ),
				'vAxis[gridlines][count]',
				isset( $this->vAxis['gridlines']['count'] ) ? $this->vAxis['gridlines']['count'] : '',
				esc_html__( 'The number of horizontal gridlines inside the chart area. Minimum value is 2. Specify -1 to automatically compute the number of gridlines.', 'visualizer' ),
				5
			);

			self::_renderColorPickerItem(
				esc_html__( 'Color', 'visualizer' ),
				'vAxis[gridlines][color]',
				isset( $this->vAxis['gridlines']['color'] ) ? $this->vAxis['gridlines']['color'] : null,
				'#ccc'
			);
			self::_renderSectionEnd();

			self::_renderSectionStart( esc_html__( 'Minor Grid Lines', 'visualizer' ), false );
			self::_renderTextItem(
				esc_html__( 'Count', 'visualizer' ),
				'vAxis[minorGridlines][count]',
				isset( $this->vAxis['minorGridlines']['count'] ) ? $this->vAxis['minorGridlines']['count'] : '',
				esc_html__( 'The number of horizontal minor gridlines between two regular gridlines.', 'visualizer' ),
				0
			);

			self::_renderColorPickerItem(
				esc_html__( 'Color', 'visualizer' ),
				'vAxis[minorGridlines][color]',
				isset( $this->vAxis['minorGridlines']['color'] ) ? $this->vAxis['minorGridlines']['color'] : null,
				null
			);
			self::_renderSectionEnd();
		}

		if ( $this->_verticalGridLines ) {
			self::_renderSectionStart( esc_html__( 'View Window', 'visualizer' ), false );
			self::_renderTextItem(
				esc_html__( 'Maximum Value', 'visualizer' ),
				'hAxis[viewWindow][max]',
				isset( $this->hAxis['viewWindow']['max'] ) ? $this->hAxis['viewWindow']['max'] : '',
				'The maximum vertical data value to render.'
			);

			self::_renderTextItem(
				esc_html__( 'Minimum Value', 'visualizer' ),
				'hAxis[viewWindow][min]',
				isset( $this->hAxis['viewWindow']['min'] ) ? $this->hAxis['viewWindow']['min'] : '',
				'The minimum vertical data value to render.'
			);
			self::_renderSectionEnd();
		}
		self::_renderGroupEnd();
	}

	/**
	 * Renders general settings block for vertical axis settings.
	 *
	 * @since 1.4.0
	 *
	 * @access protected
	 */
	protected function _renderVerticalAxisGeneralSettings() {
		self::_renderTextItem(
			esc_html__( 'Axis Title', 'visualizer' ),
			'vAxis[title]',
			isset( $this->vAxis['title'] ) ? $this->vAxis['title'] : '',
			esc_html__( 'The title of the vertical axis.', 'visualizer' )
		);

		self::_renderSelectItem(
			esc_html__( 'Text Position', 'visualizer' ),
			'hAxis[textPosition]',
			isset( $this->hAxis['textPosition'] ) ? $this->hAxis['textPosition'] : '',
			$this->_positions,
			esc_html__( 'Position of the vertical axis text, relative to the chart area.', 'visualizer' )
		);

		self::_renderSelectItem(
			esc_html__( 'Direction', 'visualizer' ),
			'vAxis[direction]',
			isset( $this->vAxis['direction'] ) ? $this->vAxis['direction'] : '',
			array(
				''   => '',
				'1'  => esc_html__( 'Identical Direction', 'visualizer' ),
				'-1' => esc_html__( 'Reverse Direction', 'visualizer' ),
			),
			esc_html__( 'The direction in which the values along the vertical axis grow.', 'visualizer' )
		);

		self::_renderColorPickerItem(
			esc_html__( 'Base Line Color', 'visualizer' ),
			'hAxis[baselineColor]',
			isset( $this->hAxis['baselineColor'] ) ? $this->hAxis['baselineColor'] : null,
			'#000'
		);
	}

	/**
	 * Renders vertical axis settings.
	 *
	 * @since 1.2.0
	 *
	 * @access protected
	 */
	protected function _renderVerticalAxisSettings() {
		self::_renderGroupStart( esc_html__( 'Vertical Axis Settings', 'visualizer' ) );
			self::_renderSectionStart( esc_html__( 'General Settings', 'visualizer' ), false );
				$this->_renderVerticalAxisGeneralSettings();
			self::_renderSectionEnd();

		if ( $this->_verticalGridLines ) {
			self::_renderSectionStart( esc_html__( 'Grid Lines', 'visualizer' ), false );
			self::_renderTextItem(
				esc_html__( 'Count', 'visualizer' ),
				'hAxis[gridlines][count]',
				isset( $this->hAxis['gridlines']['count'] ) ? $this->hAxis['gridlines']['count'] : '',
				esc_html__( 'The number of vertical gridlines inside the chart area. Minimum value is 2. Specify -1 to automatically compute the number of gridlines.', 'visualizer' ),
				5
			);

			self::_renderColorPickerItem(
				esc_html__( 'Color', 'visualizer' ),
				'hAxis[gridlines][color]',
				isset( $this->hAxis['gridlines']['color'] ) ? $this->hAxis['gridlines']['color'] : null,
				'#ccc'
			);
			self::_renderSectionEnd();

			self::_renderSectionStart( esc_html__( 'Minor Grid Lines', 'visualizer' ), false );
			self::_renderTextItem(
				esc_html__( 'Count', 'visualizer' ),
				'hAxis[minorGridlines][count]',
				isset( $this->hAxis['minorGridlines']['count'] ) ? $this->hAxis['minorGridlines']['count'] : '',
				esc_html__( 'The number of vertical minor gridlines between two regular gridlines.', 'visualizer' ),
				0
			);

			self::_renderColorPickerItem(
				esc_html__( 'Color', 'visualizer' ),
				'hAxis[minorGridlines][color]',
				isset( $this->hAxis['minorGridlines']['color'] ) ? $this->hAxis['minorGridlines']['color'] : null,
				null
			);
			self::_renderSectionEnd();
		}

		if ( $this->_horizontalGridLines ) {
			self::_renderSectionStart( esc_html__( 'View Window', 'visualizer' ), false );
			self::_renderTextItem(
				esc_html__( 'Maximum Value', 'visualizer' ),
				'vAxis[viewWindow][max]',
				isset( $this->vAxis['viewWindow']['max'] ) ? $this->vAxis['viewWindow']['max'] : '',
				'The maximum vertical data value to render.'
			);

			self::_renderTextItem(
				esc_html__( 'Minimum Value', 'visualizer' ),
				'vAxis[viewWindow][min]',
				isset( $this->vAxis['viewWindow']['min'] ) ? $this->vAxis['viewWindow']['min'] : '',
				'The minimum vertical data value to render.'
			);
			self::_renderSectionEnd();
		}
		self::_renderGroupEnd();
	}

	/**
	 * Renders chart axes settings.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _renderAxesSettings() {
		$this->_renderHorizontalAxisSettings();
		$this->_renderVerticalAxisSettings();
	}

	/**
	 * Renders series settings group.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _renderSeriesSettings() {
		self::_renderGroupStart( esc_html__( 'Series Settings', 'visualizer' ) );
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
	 * Renders concreate series settings.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @param int $index The series index.
	 */
	protected function _renderSeries( $index ) {
		self::_renderSelectItem(
			esc_html__( 'Visible In Legend', 'visualizer' ),
			'series[' . $index . '][visibleInLegend]',
			isset( $this->series[ $index ]['visibleInLegend'] ) ? $this->series[ $index ]['visibleInLegend'] : '',
			array(
				''  => '',
				'0' => esc_html__( 'No', 'visualizer' ),
				'1' => esc_html__( 'Yes', 'visualizer' ),
			),
			esc_html__( 'Determines whether the series has to be presented in the legend or not.', 'visualizer' )
		);

		$this->_renderFormatField( $index );

		self::_renderColorPickerItem(
			esc_html__( 'Color', 'visualizer' ),
			'series[' . $index . '][color]',
			isset( $this->series[ $index ]['color'] ) ? $this->series[ $index ]['color'] : null,
			null
		);
	}

	/**
	 * Renders format field for horizontal axis.
	 *
	 * @since 1.4.3
	 *
	 * @access protected
	 */
	protected function _renderHorizontalAxisFormatField() {
		self::_renderTextItem(
			esc_html__( 'Number Format', 'visualizer' ),
			'hAxis[format]',
			isset( $this->hAxis['format'] ) ? $this->hAxis['format'] : '',
			sprintf(
				'%s<br><br>%s<br><br>%s',
				esc_html__( 'Enter custom format pattern to apply to horizontal axis labels.', 'visualizer' ),
				sprintf(
					esc_html__( 'For number axis labels, this is a subset of the decimal formatting %1$sICU pattern set%2$s. For instance, $#,###.## will display values $1,234.56 for value 1234.56. Pay attention that if you use #&#37;&#37; percentage format then your values will be multiplied by 100.','visualizer' ),
					'<a href="http://icu-project.org/apiref/icu4c/classDecimalFormat.html#_details" target="_blank">',
					'</a>'
				),
				sprintf(
					esc_html__( 'For date axis labels, this is a subset of the date formatting %1$sICU date and time format%2$s.','visualizer' ),
					'<a href="http://userguide.icu-project.org/formatparse/datetime#TOC-Date-Time-Format-Syntax" target="_blank">',
					'</a>'
				)
			)
		);
	}

	/**
	 * Renders format field for vertical axis settings.
	 *
	 * @since 1.4.3
	 *
	 * @access protected
	 */
	protected function _renderVerticalAxisFormatField() {
		self::_renderTextItem(
			esc_html__( 'Number Format', 'visualizer' ),
			'vAxis[format]',
			isset( $this->vAxis['format'] ) ? $this->vAxis['format'] : '',
			sprintf(
				'%s<br><br>%s<br><br>%s',
				esc_html__( 'Enter custom format pattern to apply to vertical axis labels.', 'visualizer' ),
				sprintf(
					esc_html__( 'For number axis labels, this is a subset of the decimal formatting %1$sICU pattern set%2$s. For instance, $#,###.## will display values $1,234.56 for value 1234.56. Pay attention that if you use #&#37;&#37; percentage format then your values will be multiplied by 100.','visualizer' ),
					'<a href="http://icu-project.org/apiref/icu4c/classDecimalFormat.html#_details" target="_blank">',
					'</a>'
				),
				sprintf(
					esc_html__( 'For date axis labels, this is a subset of the date formatting %1$sICU date and time format%2$s.','visualizer' ),
					'<a href="http://userguide.icu-project.org/formatparse/datetime#TOC-Date-Time-Format-Syntax" target="_blank">',
					'</a>'
				)
			)
		);
	}

}
