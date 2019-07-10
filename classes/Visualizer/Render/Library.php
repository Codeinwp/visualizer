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
		esc_html_e( 'Visualizer Library', 'visualizer' );
		echo ' <a href="javascript:;" class="add-new-h2">', esc_html__( 'Add New', 'visualizer' ), '</a>';
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
		if ( ! filter_var( ini_get( 'allow_url_fopen' ), FILTER_VALIDATE_BOOLEAN ) ) {
			echo '<div class="updated error">';
			echo '<p>';
			printf( esc_html__( '%s option is disabled in your php.ini config. Please, enable it by change its value to 1. This option increases the speed of remote CSV uploading.', 'visualizer' ), '<b>allow_url_fopen</b>' );
			echo '</p>';
			echo '</div>';
		}
	}

	/**
	 * Renders library content.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 */
	private function _renderLibrary() {
		// Added by Ash/Upwork
		$filterBy = null;
		if ( isset( $_GET['s'] ) && strlen( $_GET['s'] ) > 0 ) {
			$filterBy = filter_input( INPUT_GET, 's', FILTER_SANITIZE_STRING );
		}
		// Added by Ash/Upwork
		echo $this->custom_css;
		echo '<div id="visualizer-types" class="visualizer-clearfix">';
		echo '<ul class="subsubsub">';
		foreach ( $this->types as $type => $array ) {
			if ( ! is_array( $array ) ) {
				// support for old pro
				$array = array( 'enabled' => true, 'name' => $array );
			}
			$label = $array['name'];
			$link  = '<a class=" " href="' . esc_url(
				add_query_arg(
					array(
						'type'  => $type,
						'vpage' => false,
					)
				)
			) . '">';
			if ( ! $array['enabled'] ) {
				$link = "<a class=' visualizer-pro-only' href='" . Visualizer_Plugin::PRO_TEASER_URL . "' target='_blank'>";
			}
			echo '<li class="visualizer-list-item all">';
			if ( $type === $this->type ) {
				echo '<a class="  current" href="', esc_url( add_query_arg( 'vpage', false ) ), '">';
				echo $label;
				echo '</a>';
			} else {
				echo $link;
				echo $label;
				echo '</a>';
			}
			echo ' | </li>';
		}
		echo '</ul>';
		echo '<form action="" method="get"><p id="visualizer-search" class="search-box">
                <input type="search"   name="s" value="' . $filterBy . '">
                <input type="hidden" name="page" value="visualizer">
                <input type="submit" id="search-submit" class="button button-secondary" value="' . esc_attr__( 'Search', 'visualizer' ) . '">
           </p> </form>';
		echo '</div>';
		echo '<div id="visualizer-content-wrapper">';
		if ( ! empty( $this->charts ) ) {
			echo '<div id="visualizer-library" class="visualizer-clearfix">';
			foreach ( $this->charts as $placeholder_id => $chart ) {
				$this->_renderChartBox( $placeholder_id, $chart['id'] );
			}
			echo '</div>';
		} else {
			echo '<div id="visualizer-library" class="visualizer-clearfix">';
			echo '<div class="visualizer-chart">';
			echo '<div class="visualizer-chart-canvas visualizer-nochart-canvas">';
			echo '<div class="visualizer-notfound">', esc_html__( 'No charts found', 'visualizer' ), '</div>';
			echo '</div>';
			echo '<div class="visualizer-chart-footer visualizer-clearfix">';
			echo '<span class="visualizer-chart-action visualizer-nochart-delete"></span>';
			echo '<span class="visualizer-chart-action visualizer-nochart-clone"></span>';
			echo '<span class="visualizer-chart-action visualizer-nochart-edit"></span>';
			echo '<span class="visualizer-chart-action visualizer-nochart-export"></span>';
			echo '<span class="visualizer-chart-shortcode">';
			echo '&nbsp;[visualizer]&nbsp;';
			echo '</span>';
			echo '</div>';
			echo '</div>';
			echo '</div>';
		}
		$this->_renderSidebar();
		echo '</div>';
		if ( is_array( $this->pagination ) ) {
			echo '<ul class=" subsubsub">';
			foreach ( $this->pagination as $page ) {
				echo '<li class="all">', $page, '</li>';
			}
			echo '</ul>';
		}
	}

	/**
	 * Renders chart's box block.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 *
	 * @param string $placeholder_id The placeholder's id for the chart.
	 * @param int    $chart_id The id of the chart.
	 */
	private function _renderChartBox( $placeholder_id, $chart_id ) {
		$settings    = get_post_meta( $chart_id, Visualizer_Plugin::CF_SETTINGS );
		$title       = '#' . $chart_id;
		if ( ! empty( $settings[0]['title'] ) ) {
			$title  = $settings[0]['title'];
		}
		$ajax_url    = admin_url( 'admin-ajax.php' );
		$delete_url  = add_query_arg(
			array(
				'action' => Visualizer_Plugin::ACTION_DELETE_CHART,
				'nonce'  => wp_create_nonce(),
				'chart'  => $chart_id,
			),
			$ajax_url
		);
		$clone_url   = add_query_arg(
			array(
				'action' => Visualizer_Plugin::ACTION_CLONE_CHART,
				'nonce'  => wp_create_nonce( Visualizer_Plugin::ACTION_CLONE_CHART ),
				'chart'  => $chart_id,
				'type'   => $this->type,
			),
			$ajax_url
		);
		$export_link = add_query_arg(
			array(
				'action'   => Visualizer_Plugin::ACTION_EXPORT_DATA,
				'chart'    => $chart_id,
				'security' => wp_create_nonce( Visualizer_Plugin::ACTION_EXPORT_DATA . Visualizer_Plugin::VERSION ),
			),
			admin_url( 'admin-ajax.php' )
		);
		echo '<div class="visualizer-chart"><div class="visualizer-chart-title">', esc_html( $title ), '</div>';
		echo '<div id="', $placeholder_id, '" class="visualizer-chart-canvas">';
		echo '<img src="', VISUALIZER_ABSURL, 'images/ajax-loader.gif" class="loader">';
		echo '</div>';
		echo '<div class="visualizer-chart-footer visualizer-clearfix">';
		echo '<a class="visualizer-chart-action visualizer-chart-delete" href="', $delete_url, '" title="', esc_attr__( 'Delete', 'visualizer' ), '" onclick="return showNotice.warn();"></a>';
		echo '<a class="visualizer-chart-action visualizer-chart-clone" href="', $clone_url, '" title="', esc_attr__( 'Clone', 'visualizer' ), '"></a>';
		echo '<a class="visualizer-chart-action visualizer-chart-edit" href="javascript:;" title="', esc_attr__( 'Edit', 'visualizer' ), '" data-chart="', $chart_id, '"></a>';
		echo '<a class="visualizer-chart-action visualizer-chart-export" href="javascript:;" title="', esc_attr__( 'Export', 'visualizer' ), '" data-chart="', $export_link, '"></a>';
		echo '<span class="visualizer-chart-shortcode" title="', esc_attr__( 'Click to select', 'visualizer' ), '">';
		echo '&nbsp;[visualizer id=&quot;', $chart_id, '&quot;]&nbsp;';
		echo '</span>';
		echo '</div>';
		echo '</div>';
	}

	/**
	 * Render sidebar.
	 */
	private function _renderSidebar() {
		if ( ! VISUALIZER_PRO ) {
			echo '<div id="visualizer-sidebar">';
			echo '<div class="visualizer-sidebar-box">';
			echo '<h3>' . __( 'Gain more editing power', 'visualizer' ) . '</h3><ul>';
			echo '<li>' . __( 'Spreadsheet like editor', 'visualizer' ) . '</li>';
			echo '<li>' . __( 'Import from other charts', 'visualizer' ) . '</li>';
			echo '<li>' . __( 'Frontend editor', 'visualizer' ) . '</li>';
			echo '<li>' . __( 'Private charts', 'visualizer' ) . '</li>';
			echo '<li>' . __( 'Auto-sync with online files', 'visualizer' ) . '</li>';
			echo '<li>' . __( '3 more chart types', 'visualizer' ) . '</li></ul>';
			echo '<a href="' . Visualizer_Plugin::PRO_TEASER_URL . '" target="_blank" class="button button-primary">' . __( 'View more features', 'visualizer' ) . '</a>';
			echo '</div>';
			echo '</div>';
		}
	}

}
