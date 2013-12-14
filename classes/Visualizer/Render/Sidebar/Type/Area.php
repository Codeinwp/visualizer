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
 * Class for area chart sidebar settings.
 *
 * @category Visualizer
 * @package Render
 * @subpackage Sidebar
 *
 * @since 1.0.0
 */
class Visualizer_Render_Sidebar_Type_Area extends Visualizer_Render_Sidebar_Linear {

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param array $data The data what has to be associated with this render.
	 */
	public function __construct( $data = array( ) ) {
		parent::__construct( $data );
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
		$this->_renderGeneralSettings();
		$this->_renderAxesSettings();
		$this->_renderLineSettings();
		$this->_renderSeriesSettings();
		$this->_renderViewSettings();
	}

	/**
	 * Renders line settings items.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _renderLineSettingsItems() {
		parent::_renderLineSettingsItems();

		self::_renderTextItem(
			esc_html__( 'Area Opacity', Visualizer_Plugin::NAME ),
			'areaOpacity',
			$this->areaOpacity,
			esc_html__( 'The default opacity of the colored area under an area chart series, where 0.0 is fully transparent and 1.0 is fully opaque. To specify opacity for an individual series, set the area opacity value in the series property.', Visualizer_Plugin::NAME ),
			'0.3'
		);

		echo '<div class="section-delimiter"></div>';

		self::_renderSelectItem(
			esc_html__( 'Is Stacked', Visualizer_Plugin::NAME ),
			'isStacked',
			$this->isStacked,
			$this->_yesno,
			esc_html__( 'If set to yes, series elements are stacked.', Visualizer_Plugin::NAME )
		);
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
		self::_renderSelectItem(
			esc_html__( 'Visible In Legend', Visualizer_Plugin::NAME ),
			'series[' . $index . '][visibleInLegend]',
			isset( $this->series[$index]['visibleInLegend'] ) ? $this->series[$index]['visibleInLegend'] : '',
			array(
				''  => '',
				'0' => esc_html__( 'No', Visualizer_Plugin::NAME ),
				'1' => esc_html__( 'Yes', Visualizer_Plugin::NAME ),
			),
			esc_html__( 'Determines whether the series has to be presented in the legend or not.', Visualizer_Plugin::NAME )
		);

		echo '<div class="section-item">';
			echo '<a class="more-info" href="javascript:;">[?]</a>';
			echo '<b>', esc_html__( 'Line Width And Point Size', Visualizer_Plugin::NAME ), '</b>';

			echo '<table class="section-table" cellspacing="0" cellpadding="0" border="0">';
				echo '<tr>';
					echo '<td class="section-table-column">';
						$line_width = isset( $this->series[$index]['lineWidth'] ) ? $this->series[$index]['lineWidth'] : '';
						echo '<input type="text" name="series[', $index, '][lineWidth]" class="control-text" value="', esc_attr( $line_width ), '" placeholder="2">';
					echo '</td>';
					echo '<td class="section-table-column">';
						$point_size = isset( $this->series[$index]['pointSize'] ) ? $this->series[$index]['pointSize'] : '';
						echo '<input type="text" name="series[', $index, '][pointSize]" class="control-text" value="', esc_attr( $point_size ), '" placeholder="0">';
					echo '</td>';
				echo '</tr>';
			echo '</table>';

			echo '<p class="section-description">';
				esc_html_e( 'Overrides the global line width and point size values for this series.', Visualizer_Plugin::NAME );
			echo '</p>';
		echo '</div>';

		$this->_renderFormatField( $index );

		self::_renderTextItem(
			esc_html__( 'Area Opacity', Visualizer_Plugin::NAME ),
			'series[' . $index . '][areaOpacity]',
			isset( $this->series[$index]['areaOpacity'] ) ? $this->series[$index]['areaOpacity'] : null,
			esc_html__( 'The opacity of the colored area, where 0.0 is fully transparent and 1.0 is fully opaque.', Visualizer_Plugin::NAME ),
			'0.3'
		);

		self::_renderColorPickerItem(
			esc_html__( 'Color', Visualizer_Plugin::NAME ),
			'series[' . $index . '][color]',
			isset( $this->series[$index]['color'] ) ? $this->series[$index]['color'] : null,
			null
		);
	}

}