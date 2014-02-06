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
 * Renders visualizer library page.
 *
 * @category Visualizer
 * @package Render
 *
 * @since 1.0.0
 */
class Visualizer_Render_Library extends Visualizer_Render {

	/**
	 * Renders chart's box block.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @param string $placeholder_id The placeholder's id for the chart.
	 * @param int $chart_id The id of the chart.
	 */
	private function _renderChartBox( $placeholder_id, $chart_id ) {
		$ajax_url = admin_url( 'admin-ajax.php' );

		$delete_url = add_query_arg( array(
			'action' => Visualizer_Plugin::ACTION_DELETE_CHART,
			'nonce'  => wp_create_nonce(),
			'chart'  => $chart_id,
		), $ajax_url );

		$clone_url = add_query_arg( array(
			'action' => Visualizer_Plugin::ACTION_CLONE_CHART,
			'nonce'  => wp_create_nonce( Visualizer_Plugin::ACTION_CLONE_CHART ),
			'chart'  => $chart_id,
			'type'   => $this->type,
		), $ajax_url );

		echo '<div class="visualizer-chart">';
			echo '<div id="', $placeholder_id, '" class="visualizer-chart-canvas">';
				echo '<img src="', VISUALIZER_ABSURL, 'images/ajax-loader.gif" class="loader">';
			echo '</div>';
			echo '<div class="visualizer-chart-footer visualizer-clearfix">';
				echo '<a class="visualizer-chart-action visualizer-chart-delete" href="', $delete_url, '" title="', esc_attr__( 'Delete', Visualizer_Plugin::NAME ), '" onclick="return showNotice.warn();"></a>';
				echo '<a class="visualizer-chart-action visualizer-chart-clone" href="', $clone_url, '" title="', esc_attr__( 'Clone', Visualizer_Plugin::NAME ), '"></a>';
				echo '<a class="visualizer-chart-action visualizer-chart-edit" href="javascript:;" title="', esc_attr__( 'Edit', Visualizer_Plugin::NAME ), '" data-chart="', $chart_id, '"></a>';

				echo '<span class="visualizer-chart-shortcode" title="', esc_attr__( 'Click to select', Visualizer_Plugin::NAME ), '">';
					echo '&nbsp;[visualizer id=&quot;', $chart_id, '&quot;]&nbsp;';
				echo '</span>';
			echo '</div>';
		echo '</div>';
	}

	/**
	 * Renders library content.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 */
	private function _renderLibrary() {
		echo '<div id="visualizer-types" class="visualizer-clearfix">';
			echo '<ul>';
				foreach ( $this->types as $type => $label ) {
					echo '<li class="visualizer-list-item">';
						if ( $type == $this->type ) {
							echo '<a class="page-numbers current" href="', add_query_arg( 'vpage', false ), '">';
								echo $label;
							echo '</a>';
						} else {
							echo '<a class="page-numbers" href="', add_query_arg( array( 'type' => $type, 'vpage' => false ) ), '">';
								echo $label;
							echo '</a>';
						}
					echo '</li>';
				}
			echo '</ul>';
		echo '</div>';

		if ( !empty( $this->charts ) ) {
			echo '<div id="visualizer-library" class="visualizer-clearfix">';
				foreach ( $this->charts as $placeholder_id  => $chart ) {
					$this->_renderChartBox( $placeholder_id, $chart['id'] );
				}
			echo '</div>';

			if ( is_array( $this->pagination ) ) {
				echo '<ul class="visualizer-library-pagination">';
					foreach ( $this->pagination as $page ) {
						echo '<li class="visualizer-list-item">', $page, '</li>';
					}
				echo '</ul>';
			}
		} else {
			echo '<div id="visualizer-library" class="visualizer-clearfix">';
				echo '<div class="visualizer-chart">';
					echo '<div class="visualizer-chart-canvas visualizer-nochart-canvas">';
						echo '<div class="visualizer-notfound">', esc_html__( 'No charts found', Visualizer_Plugin::NAME ), '</div>';
					echo '</div>';
					echo '<div class="visualizer-chart-footer visualizer-clearfix">';
						echo '<span class="visualizer-chart-action visualizer-nochart-delete"></span>';
						echo '<span class="visualizer-chart-action visualizer-nochart-clone"></span>';
						echo '<span class="visualizer-chart-action visualizer-nochart-edit"></span>';

						echo '<span class="visualizer-chart-shortcode">';
							echo '&nbsp;[visualizer]&nbsp;';
						echo '</span>';
					echo '</div>';
				echo '</div>';
			echo '</div>';
		}
	}

	/**
	 * Renders library page.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _toHTML() {
		echo '<div class="wrap">';
			echo '<div id="visualizer-icon" class="icon32"><br></div>';
			echo '<h2>';
				esc_html_e( 'Visualizer Library', Visualizer_Plugin::NAME );
				echo ' <a href="javascript:;" class="add-new-h2">', esc_html__( 'Add New', Visualizer_Plugin::NAME ), '</a>';
			echo '</h2>';

			$this->_renderMessages();
			$this->_renderLibrary();
		echo '</div>';
	}

	/**
	 * Renders notification messages if need be.
	 *
	 * @since 1.4.2
	 *
	 * @access private
	 */
	private function _renderMessages() {
		if ( !filter_var( ini_get( 'allow_url_fopen' ), FILTER_VALIDATE_BOOLEAN ) ) {
			echo '<div class="updated error">';
				echo '<p>';
					printf( esc_html__( '%s option is disabled in your php.ini config. Please, enable it by change its value to 1. This option increases the speed of remote CSV uploading.', Visualizer_Plugin::NAME ), '<b>allow_url_fopen</b>' );
				echo '</p>';
			echo '</div>';
		}
	}

}