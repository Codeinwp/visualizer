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
		echo '<form method="post">';
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
			echo '<div class="type-box type-box-', $type, '">';
			if ( ! $array['enabled'] ) {
				echo "<a class='pro-upsell' href='" . Visualizer_Plugin::PRO_TEASER_URL . "' target='_blank'>";
				echo "<span class='visualizder-pro-label'>" . __( 'PREMIUM', 'visualizer' ) . '</span>';
			}
			echo '<label class="type-label', $type == $this->type ? ' type-label-selected' : '', '">';
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
	 * Renders toolbar content.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _renderToolbar() {
		if ( VISUALIZER_PRO ) {
				global $Visualizer_Pro;
		}
			echo '<input type="submit" class="button button-primary button-large push-right" value="', esc_attr__( 'Next', 'visualizer' ), '">';
	}
}
