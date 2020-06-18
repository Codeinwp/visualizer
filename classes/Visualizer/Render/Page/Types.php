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
		foreach ( $this->types as $type => $array ) {
			// add classes to each box that identifies the libraries this chart type supports.
			$lib_classes = '';
			foreach ( $array['supports'] as $lib ) {
				$lib_classes .= ' type-lib-' . str_replace( ' ', '', $lib );
			}
			echo '<div class="type-box type-box-', $type, $lib_classes, '">';
			if ( ! $array['enabled'] ) {
				echo "<a class='pro-upsell' href='" . Visualizer_Plugin::PRO_TEASER_URL . "' target='_blank'>";
				echo "<span class='visualizder-pro-label'>" . __( 'PREMIUM', 'visualizer' ) . '</span>';
			}
			echo '<label class="type-label', $type === $this->type ? ' type-label-selected' : '', '">';
			echo '<span>' . $array['name'] . '</span>';
			if ( $array['enabled'] ) {
				echo '<input type="radio" class="type-radio" name="type" value="', $type, '"', checked( $type, $this->type, false ), '>';
			}
			echo '</label>';
			if ( ! $array['enabled'] ) {
				echo '</a>';
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
	 * Renders toolbar content.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _renderToolbar() {
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

		if ( ! empty( $libraries ) ) {
			?>
		<select name="chart-library" class="viz-select-library" data-type-vs-library="<?php echo esc_attr( json_encode( $type_vs_library ) ); ?>">
			<option value=""><?php esc_html_e( 'Use Library', 'visualizer' ); ?></option>
			<?php
			foreach ( $libraries as $library ) {
				?>
			<option value="<?php echo $this->_removeSpaceFromLibrary( $library ); ?>"><?php echo $library; ?></option>
				<?php
			}
			?>
		</select>
			<?php
		}
		?>
		<input type="submit" class="button button-primary button-large push-right" value="<?php esc_attr_e( 'Next', 'visualizer' ); ?>">
		<input type="button" class="button button-secondary button-large push-right viz-abort" value="<?php esc_attr_e( 'Cancel', 'visualizer' ); ?>">
		<?php
	}
}
