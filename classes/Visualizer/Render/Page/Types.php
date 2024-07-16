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
 * Renders chart type picker page.
 *
 * @category Visualizer
 * @package Render
 * @subpackage Page
 *
 * @since 1.0.0
 */
class Visualizer_Render_Page_Types extends Visualizer_Render_Page {

	/**
	 * Renders page template.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _toHTML() {
		echo '<form method="post" id="viz-types-form">';
			echo '<input type="hidden" name="nonce" value="', wp_create_nonce(), '">';
			parent::_toHTML();
		echo '</form>';
	}

	/**
	 * Renders page content.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _renderContent() {
		echo '<div id="type-picker">';
		echo '<div id="chart-select">' . $this->render_chart_selection() . '</div>';
		foreach ( $this->types as $type => $array ) {
			// add classes to each box that identifies the libraries this chart type supports.
			$lib_classes = '';
			foreach ( $array['supports'] as $lib ) {
				$lib_classes .= ' type-lib-' . str_replace( ' ', '', $lib );
			}
			echo '<div class="type-box type-box-', $type, $lib_classes, '">';
			if ( ! $array['enabled'] ) {
				$demo_url = isset( $array['demo_url'] ) ? $array['demo_url'] : '';
				echo '<div class="pro-upsell">';
				echo "<span class='visualizder-pro-label'>" . __( 'PREMIUM', 'visualizer' ) . '</span>';
				echo '<div class="pro-upsell-overlay">';
				echo '<div class="pro-upsell-action"><a href="' . esc_url( $demo_url ) . '" target="_blank" class="button button-secondary">' . __( 'View Demo', 'visualizer' ) . '</a><a href="' . tsdk_utmify( Visualizer_Plugin::PRO_TEASER_URL, 'procharts', 'Addnewcharts' ) . '" target="_blank" class="button button-primary">' . __( 'Upgrade Now', 'visualizer' ) . '</a></div>';
				echo '</div>';
			}
			echo '<label class="type-label', $type === $this->type ? ' type-label-selected' : '', '">';
			echo '<span>' . $array['name'] . '</span>';
			if ( $array['enabled'] ) {
				echo '<input type="radio" class="type-radio" name="type" value="', $type, '"', checked( $type, $this->type, false ), '>';
			}
			echo '</label>';
			if ( ! $array['enabled'] ) {
				echo '</div>';
			}
			echo '</div>';
		}
		echo '</div>';
	}

	/**
	 * Renders page sidebar.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _renderSidebar() {}

	/**
	 * Removes all spaces from the name of the library.
	 *
	 * @access public
	 */
	public function _removeSpaceFromLibrary( $value ) {
		return str_replace( ' ', '', $value );
	}

	/**
	 * Render the chart select component.
	 *
	 * @return string
	 */
	private function render_chart_selection() {
		$chart_types = Visualizer_Module_Admin::_getChartTypesLocalized( true, false, false, 'types' );
		$type_vs_library = array();

		$libraries = array();
		foreach ( $chart_types as $type => $atts ) {
			if ( empty( $atts['supports'] ) ) {
				continue;
			}
			$libraries = array_merge( $libraries, $atts['supports'] );
			$type_vs_library[ trim( $type ) ] = array_map( array( $this, '_removeSpaceFromLibrary' ), $atts['supports'] );
		}

		$libraries = array_unique( $libraries );

		$select = '';
		if ( ! empty( $libraries ) ) {
			$select .= '<label for="chart-library">' . __( 'Select Library for charts', 'visualizer' ) . '</label>';
			$select .= '<select name="chart-library" class="viz-select-library" data-type-vs-library="' . esc_attr( json_encode( $type_vs_library ) ) . '">';
			foreach ( $libraries as $library ) {
				$select .= '<option value="' . $this->_removeSpaceFromLibrary( $library ) . '">' . $library . '</option>';
			}

			$select .= '</select>';
		}
		return $select;
	}

	/**
	 * Renders toolbar content.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _renderToolbar() {
		// $this->render_chart_selection();
		?>
		<input type="submit" class="button button-primary button-large push-right" value="<?php esc_attr_e( 'Next', 'visualizer' ); ?>">
		<input type="button" class="button button-secondary button-large push-right viz-abort" value="<?php esc_attr_e( 'Cancel', 'visualizer' ); ?>">
		<?php
	}
}
