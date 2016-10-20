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
 * Media view template rendering class.
 *
 * @category Visualizer
 * @package Render
 *
 * @since 1.0.0
 */
class Visualizer_Render_Templates extends Visualizer_Render {

	/**
	 * The array of template names.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @var array
	 */
	private $_templates = array(
		'library-chart',
		'library-empty',
	);

	/**
	 * Renders concreate template and wraps it into script tag.
	 *
	 * @since 1.0.0
	 *
	 * @param string $id The name of a template.
	 * @param string $callback The name of the function to render a template.
	 */
	private function _renderTemplate( $id, $callback ) {
		echo '<script id="tmpl-visualizer-', $id, '" type="text/html">';
			call_user_func( array( $this, $callback ) );
		echo '</script>';
	}

	/**
	 * Renders library-chart template.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _renderLibraryChart() {
		echo '<div class="visualizer-library-chart-footer visualizer-clearfix">';
			echo '<a class="visualizer-library-chart-action visualizer-library-chart-delete" href="javascript:;" title="', esc_attr__( 'Delete', 'visualizer' ), '"></a>';
			echo '<a class="visualizer-library-chart-action visualizer-library-chart-insert" href="javascript:;" title="', esc_attr__( 'Insert', 'visualizer' ), '"></a>';

			echo '<span class="visualizer-library-chart-shortcode" title="', esc_attr__( 'Click to select', 'visualizer' ), '">&nbsp;[visualizer id=&quot;{{data.id}}&quot;]&nbsp;</span>';
		echo '</div>';
	}

	/**
	 * Renders library-empty template.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _renderLibraryEmpty() {
		echo '<div class="visualizer-library-chart">';
			echo '<div class="visualizer-library-chart-canvas visualizer-library-nochart-canvas">';
				echo '<div class="visualizer-library-notfound">', esc_html__( 'No charts found', 'visualizer' ), '</div>';
			echo '</div>';
			echo '<div class="visualizer-library-chart-footer visualizer-clearfix">';
				echo '<span class="visualizer-library-chart-action visualizer-library-nochart-delete"></span>';
				echo '<span class="visualizer-library-chart-action visualizer-library-nochart-insert"></span>';

				echo '<span class="visualizer-library-chart-shortcode">';
					echo '&nbsp;[visualizer]&nbsp;';
				echo '</span>';
			echo '</div>';
		echo '</div>';
	}

	/**
	 * Renders templates.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _toHTML() {
		foreach ( $this->_templates as $template ) {
			$callback = '_render' . str_replace( ' ', '', ucwords( str_replace( '-', ' ', $template ) ) );
			$this->_renderTemplate( $template, $callback );
		}
	}

}
