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
 * Class for geo chart sidebar settings.
 *
 * @category Visualizer
 * @package Render
 * @subpackage Sidebar
 *
 * @since 1.0.0
 */
class Visualizer_Render_Sidebar_Type_Geo extends Visualizer_Render_Sidebar_Google {

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
		$this->_renderMapSettings();
		$this->_renderColorAxisSettings();
		$this->_renderSizeAxisSettings();
		$this->_renderMagnifyingGlassSettings();
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
	 * Renders map settings group.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _renderMapSettings() {
		self::_renderGroupStart( esc_html__( 'Map Settings', 'visualizer' ) );

			self::_renderSectionStart( esc_html__( 'API', 'visualizer' ), false );
				self::_renderSectionDescription(
					sprintf( esc_html__( 'Add the Google Maps API key (Click %1$shere%2$s to get the key)', 'visualizer' ), '<a href="https://developers.google.com/maps/documentation/javascript/get-api-key" target="_blank">', '</a>' )
				);

				self::_renderTextItem(
					esc_html__( 'API Key', 'visualizer' ),
					'map_api_key',
					get_option( 'visualizer-map-api-key' ),
					'',
					''
				);

			self::_renderSectionEnd();

			self::_renderSectionStart( esc_html__( 'Region', 'visualizer' ), false );
				self::_renderSectionDescription(
					esc_html__( 'Configure the region area to display on the map. (Surrounding areas will be displayed as well.) Can be one of the following:', 'visualizer' ) .
					'<ul>' .
						'<li>' . esc_html__( "'world' - A map of the entire world.", 'visualizer' ) . '</li>' .
						'<li>' . sprintf( esc_html__( "A continent or a sub-continent, specified by its %s code, e.g., '011' for Western Africa.", 'visualizer' ), '<a href="https://google-developers.appspot.com/chart/interactive/docs/gallery/geochart#Continent_Hierarchy" target="_blank">3-digit</a>' ) . '</li>' .
						'<li>' . sprintf( esc_html__( "A country, specified by its %s code, e.g., 'AU' for Australia.", 'visualizer' ), '<a href="http://en.wikipedia.org/wiki/ISO_3166-1_alpha-2" target="_blank">ISO 3166-1 alpha-2</a>' ) . '</li>' .
						'<li>' . sprintf( esc_html__( "A state in the United States, specified by its %s code, e.g., 'US-AL' for Alabama. Note that the resolution option must be set to either 'provinces' or 'metros'.", 'visualizer' ), '<a href="http://en.wikipedia.org/wiki/ISO_3166-2:US" target="_blank">ISO 3166-2:US</a>' ) . '</li>' .
					'</ul>'
				);

				self::_renderTextItem(
					esc_html__( 'Region', 'visualizer' ),
					'region',
					$this->region,
					'',
					'world'
				);

			self::_renderSectionEnd();
			self::_renderSectionStart( esc_html__( 'Resolution', 'visualizer' ), false );

				self::_renderSectionDescription(
					esc_html__( 'The resolution of the map borders. Choose one of the following values:', 'visualizer' ) .
					'<ul>' .
						'<li>' . esc_html__( "'countries' - Supported for all regions, except for US state regions.", 'visualizer' ) . '</li>' .
						'<li>' . esc_html__( "'provinces' - Supported only for country regions and US state regions. Not supported for all countries; please test a country to see whether this option is supported.", 'visualizer' ) . '</li>' .
						'<li>' . esc_html__( "'metros' - Supported for the US country region and US state regions only.", 'visualizer' ) . '</li>' .
					'</ul>'
				);

				self::_renderSelectItem(
					esc_html__( 'Resolution', 'visualizer' ),
					'resolution',
					$this->resolution,
					array(
						''          => '',
						'countries' => esc_html__( 'Countries', 'visualizer' ),
						'provinces' => esc_html__( 'Provinces', 'visualizer' ),
						'metros'    => esc_html__( 'Metros', 'visualizer' ),
					),
					''
				);

			self::_renderSectionEnd();
			self::_renderSectionStart( esc_html__( 'Display Mode', 'visualizer' ), false );

				self::_renderSectionDescription(
					esc_html__( 'Determines which type of map this is. The following values are supported:', 'visualizer' ) .
					'<ul>' .
						'<li>' . esc_html__( "'auto' - Choose based on the format of the data.", 'visualizer' ) . '</li>' .
						'<li>' . esc_html__( "'regions' - This is a region map.", 'visualizer' ) . '</li>' .
						'<li>' . esc_html__( "'markers' - This is a marker map.", 'visualizer' ) . '</li>' .
					'</ul>'
				);

				self::_renderSelectItem(
					esc_html__( 'Display Mode', 'visualizer' ),
					'displayMode',
					$this->displayMode,
					array(
						''        => '',
						'auto'    => esc_html__( 'Auto', 'visualizer' ),
						'regions' => esc_html__( 'Regions', 'visualizer' ),
						'markers' => esc_html__( 'Markers', 'visualizer' ),
					),
					''
				);
			self::_renderSectionEnd();
			self::_renderSectionStart( esc_html__( 'Tooltip', 'visualizer' ), false );
				$this->_renderTooltipSettigns();
			self::_renderSectionEnd();
		self::_renderGroupEnd();
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
				'none'      => esc_html__( 'The tooltip will not be displayed', 'visualizer' ),
			),
			esc_html__( 'Determines the user interaction that causes the tooltip to be displayed.', 'visualizer' )
		);
	}

	/**
	 * Renders color axis settings group.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _renderColorAxisSettings() {
		self::_renderGroupStart( esc_html__( 'Color Axis', 'visualizer' ) );
			self::_renderSectionStart();
				self::_renderSectionDescription( esc_html__( 'Configure color axis gradient scale, minimum and maximun values and a color of the dateless regions.', 'visualizer' ) );

				echo '<div class="viz-section-item">';
					echo '<a class="more-info" href="javascript:;">[?]</a>';
					echo '<b>', esc_html__( 'Minimum And Maximum Values', 'visualizer' ), '</b>';

					echo '<table class="viz-section-table" cellspacing="0" cellpadding="0" border="0">';
						echo '<tr>';
							echo '<td class="viz-section-table-column">';
								echo '<input type="text" name="colorAxis[minValue]" class="control-text" value="', isset( $this->colorAxis['minValue'] ) ? esc_attr( $this->colorAxis['minValue'] ) : '', '">';
							echo '</td>';
							echo '<td class="viz-section-table-column">';
								echo '<input type="text" name="colorAxis[maxValue]" class="control-text" value="', isset( $this->colorAxis['maxValue'] ) ? esc_attr( $this->colorAxis['maxValue'] ) : '', '">';
							echo '</td>';
						echo '</tr>';
					echo '</table>';

					echo '<p class="viz-section-description">';
						esc_html_e( 'Determines the minimum and maximum values of color axis.', 'visualizer' );
					echo '</p>';
				echo '</div>';

				self::_renderColorPickerItem(
					esc_html__( 'Minimum Value', 'visualizer' ),
					'colorAxis[colors][]',
					! empty( $this->colorAxis['colors'][0] ) ? $this->colorAxis['colors'][0] : null,
					'#efe6dc'
				);

				self::_renderColorPickerItem(
					esc_html__( 'Intermediate Value', 'visualizer' ),
					'colorAxis[colors][]',
					! empty( $this->colorAxis['colors'][1] ) ? $this->colorAxis['colors'][1] : null,
					'#82bf7c'
				);

				self::_renderColorPickerItem(
					esc_html__( 'Maximum Value', 'visualizer' ),
					'colorAxis[colors][]',
					! empty( $this->colorAxis['colors'][2] ) ? $this->colorAxis['colors'][2] : null,
					'#109618'
				);

				self::_renderColorPickerItem(
					esc_html__( 'Dateless Region', 'visualizer' ),
					'datalessRegionColor',
					! empty( $this->datalessRegionColor ) ? $this->datalessRegionColor : null,
					null
				);

			self::_renderSectionEnd();
		self::_renderGroupEnd();
	}

	/**
	 * Renders size axis settings group.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _renderSizeAxisSettings() {
		self::_renderGroupStart( esc_html__( 'Size Axis', 'visualizer' ) );
			self::_renderSectionStart();
				self::_renderSectionDescription( esc_html__( 'Configure how values are associated with bubble size, minimum and maximun values and marker opacity setting.', 'visualizer' ) );

				echo '<div class="viz-section-item">';
					echo '<a class="more-info" href="javascript:;">[?]</a>';
					echo '<b>', esc_html__( 'Minimum And Maximum Values', 'visualizer' ), '</b>';

					echo '<table class="viz-section-table" cellspacing="0" cellpadding="0" border="0">';
						echo '<tr>';
							echo '<td class="viz-section-table-column">';
								echo '<input type="text" name="sizeAxis[minValue]" class="control-text" value="', isset( $this->sizeAxis['minValue'] ) ? esc_attr( $this->sizeAxis['minValue'] ) : '', '">';
							echo '</td>';
							echo '<td class="viz-section-table-column">';
								echo '<input type="text" name="sizeAxis[maxValue]" class="control-text" value="', isset( $this->sizeAxis['maxValue'] ) ? esc_attr( $this->sizeAxis['maxValue'] ) : '', '">';
							echo '</td>';
						echo '</tr>';
					echo '</table>';

					echo '<p class="viz-section-description">';
						esc_html_e( 'Determines the minimum and maximum values of size axis.', 'visualizer' );
					echo '</p>';
				echo '</div>';

				echo '<div class="viz-section-item">';
					echo '<a class="more-info" href="javascript:;">[?]</a>';
					echo '<b>', esc_html__( 'Minimum And Maximum Marker Radius', 'visualizer' ), '</b>';

					echo '<table class="viz-section-table" cellspacing="0" cellpadding="0" border="0">';
						echo '<tr>';
							echo '<td class="viz-section-table-column">';
								echo '<input type="text" name="sizeAxis[minSize]" class="control-text" value="', isset( $this->sizeAxis['minSize'] ) ? esc_attr( $this->sizeAxis['minSize'] ) : '', '" placeholder="3">';
							echo '</td>';
							echo '<td class="viz-section-table-column">';
								echo '<input type="text" name="sizeAxis[maxSize]" class="control-text" value="', isset( $this->sizeAxis['maxSize'] ) ? esc_attr( $this->sizeAxis['maxSize'] ) : '', '" placeholder="12">';
							echo '</td>';
						echo '</tr>';
					echo '</table>';

					echo '<p class="viz-section-description">';
						esc_html_e( 'Determines the radius of the smallest and largest possible bubbles, in pixels.', 'visualizer' );
					echo '</p>';
				echo '</div>';

				self::_renderTextItem(
					esc_html__( 'Marker Opacity', 'visualizer' ),
					'markerOpacity',
					$this->markerOpacity,
					esc_html__( 'The opacity of the markers, where 0.0 is fully transparent and 1.0 is fully opaque.', 'visualizer' ),
					'1.0'
				);

				$this->_renderFormatField();

			self::_renderSectionEnd();
		self::_renderGroupEnd();
	}

	/**
	 * Renders magnifying glass settings.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _renderMagnifyingGlassSettings() {
		self::_renderGroupStart( esc_html__( 'Magnifying Glass', 'visualizer' ) );
			self::_renderSectionStart();
				self::_renderSectionDescription( esc_html__( 'Configure magnifying glass settings, which appears, when the user lingers over a cluttered marker. Note: this feature is not supported in browsers that do not support SVG, i.e. Internet Explorer version 8 or earlier.', 'visualizer' ) );

				self::_renderSelectItem(
					esc_html__( 'Enabled', 'visualizer' ),
					'magnifyingGlass[enable]',
					isset( $this->magnifyingGlass['enable'] ) ? $this->magnifyingGlass['enable'] : '',
					$this->_yesno,
					esc_html__( 'If yes, when the user lingers over a cluttered marker, a magnifiying glass will be opened.', 'visualizer' )
				);

				self::_renderTextItem(
					esc_html__( 'Zoom Factor', 'visualizer' ),
					'magnifyingGlass[zoomFactor]',
					isset( $this->magnifyingGlass['zoomFactor'] ) ? $this->magnifyingGlass['zoomFactor'] : '',
					esc_html__( 'The zoom factor of the magnifying glass. Can be any number greater than 0.', 'visualizer' ),
					'5.0'
				);
			self::_renderSectionEnd();
		self::_renderGroupEnd();
	}

	/**
	 * Renders chart view settings group.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _renderViewSettings() {
		self::_renderGroupStart( esc_html__( 'Layout Settings', 'visualizer' ) );
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
						esc_html_e( 'Determines the total width and height of the chart.', 'visualizer' );
					echo '</p>';
				echo '</div>';

				self::_renderSelectItem(
					esc_html__( 'Keep Aspect Ratio', 'visualizer' ),
					'keepAspectRatio',
					$this->keepAspectRatio,
					$this->_yesno,
					esc_html__( 'If yes, the map will be drawn at the largest size that can fit inside the chart area at its natural aspect ratio. If only one of the width and height options is specified, the other one will be calculated according to the aspect ratio.', 'visualizer' ) . '<br><br>' .
					esc_html__( 'If no, the map will be stretched to the exact size of the chart as specified by the width and height options.', 'visualizer' )
				);

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

				self::_renderColorPickerItem(
					esc_html__( 'Background Color', 'visualizer' ),
					'backgroundColor[fill]',
					! empty( $this->backgroundColor['fill'] ) ? $this->backgroundColor['fill'] : null,
					'#fff'
				);
			self::_renderSectionEnd();
		self::_renderGroupEnd();
	}

}
