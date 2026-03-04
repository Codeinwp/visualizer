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
			echo '<input type="hidden" name="nonce" value="', wp_create_nonce( 'visualizer-upload-data' ), '">';
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

		// AI Image Upload Section
		$has_ai_keys = ! empty( get_option( 'visualizer_openai_api_key', '' ) ) ||
			! empty( get_option( 'visualizer_gemini_api_key', '' ) ) ||
			! empty( get_option( 'visualizer_claude_api_key', '' ) );

		// Check if PRO features are locked
		$is_pro_locked = ! Visualizer_Module_Admin::proFeaturesLocked();

		// Determine what kind of lock to show
		$show_api_lock = ! $has_ai_keys && ! $is_pro_locked; // No API keys but has PRO
		$show_pro_lock = $is_pro_locked; // Free version - needs PRO upgrade

		// Build the wrapper with appropriate classes for PRO upsell
		$wrapper_class = '';
		if ( $show_pro_lock ) {
			$wrapper_class = apply_filters( 'visualizer_pro_upsell_class', 'only-pro-feature', 'chart-from-image' );
		}

		echo '<div class="' . $wrapper_class . '">';
		echo '<div style="position: relative;">';
		echo '<div id="ai-chart-from-image" style="background: #f8f9fa; border: 2px dashed #0073aa; border-radius: 8px; padding: 20px; margin-bottom: 25px;">';

		if ( $show_api_lock ) {
			// Show API key configuration lock (for PRO users without API keys)
			echo '<div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(255,255,255,0.9); border-radius: 8px; z-index: 10; display: flex; align-items: flex-start; justify-content: center; padding-top: 60px;">';
			echo '<div style="text-align: center; padding: 20px;">';
			echo '<span class="dashicons dashicons-lock" style="font-size: 48px; color: #999; margin-bottom: 10px; display: block;"></span>';
			echo '<h3 style="margin: 10px 0; color: #666;">' . esc_html__( 'AI Features - API Key Required', 'visualizer' ) . '</h3>';
			echo '<p style="margin: 10px 0; color: #666;">' . esc_html__( 'Configure your AI API key to use AI-powered chart creation from images.', 'visualizer' ) . '</p>';
			echo '<a href="' . admin_url( 'admin.php?page=visualizer-ai-settings' ) . '" class="button button-primary" style="margin-top: 10px;" onclick="if(window.parent !== window) { window.parent.location.href = this.href; return false; }">';
			echo esc_html__( 'Configure AI Settings', 'visualizer' );
			echo '</a>';
			echo '</div>';
			echo '</div>';
		}

		echo '<h3 style="margin-top: 0; color: ' . ( $has_ai_keys ? '#0073aa' : '#999' ) . ';">' . esc_html__( 'Create Chart from Image', 'visualizer' ) . '</h3>';
		echo '<p style="margin-bottom: 15px; color: ' . ( $has_ai_keys ? '#333' : '#999' ) . ';">' . esc_html__( 'Upload or drag & drop an image of a chart and AI will detect the chart type, extract data, and recreate it for you.', 'visualizer' ) . '</p>';

		// Drag and drop zone
		echo '<div id="ai-image-drop-zone" style="border: 2px dashed #ddd; border-radius: 4px; padding: 40px 20px; text-align: center; background: #fafafa; margin-bottom: 15px; transition: all 0.3s;">';
		echo '<span class="dashicons dashicons-cloud-upload" style="font-size: 48px; color: #ccc; display: block; margin-bottom: 10px;"></span>';
		echo '<p style="margin: 0 0 10px 0; color: #666;">' . esc_html__( 'Drag & drop your chart image here', 'visualizer' ) . '</p>';
		echo '<p style="margin: 0; color: #999; font-size: 13px;">' . esc_html__( 'or', 'visualizer' ) . '</p>';
		echo '<input type="file" id="ai-chart-image-upload" accept="image/*" style="display: none;">';
		echo '<button type="button" class="button button-secondary" id="ai-upload-chart-image-btn" style="margin-top: 10px;">';
		echo esc_html__( 'Choose Image', 'visualizer' );
		echo '</button>';
		echo '</div>';

		echo '<div style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">';
		echo '<span id="ai-selected-filename" style="color: #666;"></span>';
		echo '<button type="button" class="button button-primary" id="ai-generate-from-image-btn" style="display: none;">';
		echo esc_html__( 'Generate Chart', 'visualizer' );
		echo '</button>';
		echo '<span id="ai-image-loading" style="display: none;">';
		echo '<span class="spinner is-active" style="float: none; margin: 0;"></span>';
		echo '<span style="margin-left: 5px;">' . esc_html__( 'Analyzing image...', 'visualizer' ) . '</span>';
		echo '</span>';
		echo '</div>';

		echo '<div id="ai-image-preview" style="margin-top: 15px; display: none;">';
		echo '<img id="ai-preview-img" src="" alt="Preview" style="max-width: 300px; max-height: 200px; border: 1px solid #ddd; border-radius: 4px;">';
		echo '</div>';

		echo '<div id="ai-image-error" style="display: none; margin-top: 15px; padding: 10px; background: #dc3232; color: white; border-radius: 4px;"></div>';
		echo '<div id="ai-image-success" style="display: none; margin-top: 15px; padding: 10px; background: #46b450; color: white; border-radius: 4px;"></div>';
		echo '</div>'; // End #ai-chart-from-image

		// Add PRO upsell overlay if locked (free version)
		if ( $show_pro_lock ) {
			// Add the upgrade overlay HTML
			echo '<div class="only-pro-content">';
			echo '<div class="only-pro-container">';
			echo '<div class="only-pro-inner">';
			echo '<p>' . esc_html__( 'Upgrade to PRO to activate this feature!', 'visualizer' ) . '</p>';
			echo '<a target="_blank" href="' . tsdk_utmify( Visualizer_Plugin::PRO_TEASER_URL, 'chart-from-image' ) . '" title="' . esc_attr__( 'Upgrade Now', 'visualizer' ) . '">' . esc_html__( 'Upgrade Now', 'visualizer' ) . '</a>';
			echo '</div>';
			echo '</div>';
			echo '</div>';
		}

		echo '</div>'; // End position: relative wrapper
		echo '</div>'; // End only-pro-feature wrapper

		echo '<div style="text-align: center; margin: 20px 0; color: #666; font-weight: 500;">' . esc_html__( '— OR —', 'visualizer' ) . '</div>';

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
