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
		echo ' <a href="javascript:;" class="add-new-h2 add-new-chart">', esc_html__( 'Add New', 'visualizer' ), '</a>';
		if ( Visualizer_Module::is_pro() ) {
			echo ' <a href="' . admin_url( 'options-general.php' ) . '" class="page-title-action">', esc_html__( 'License Settings', 'visualizer' ), '</a>';
		}
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
		// All tab.
		echo '<li class="visualizer-list-item all"><a class="' . ( ! isset( $_GET['type'] ) || empty( $_GET['type'] ) ? 'current' : '' ) . '" href="', esc_url( add_query_arg( array( 'vpage' => false, 'type' => false, 'vaction' => false, 's' => false ) ) ), '">' . __( 'All', 'visualizer' ) . '</a> | </li>';
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
						'vaction' => false,
						's' => false,
					)
				)
			) . '">';
			if ( ! $array['enabled'] ) {
				$link = "<a class=' visualizer-pro-only' href='" . Visualizer_Plugin::PRO_TEASER_URL . "' target='_blank'>";
			}
			echo '<li class="visualizer-list-item ' . esc_attr( $this->type ) . '">';
			if ( $type === $this->type ) {
				echo '<a class="current" href="', esc_url( add_query_arg( 'vpage', false ) ), '">';
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
                <input type="search" placeholder="' . __( 'Enter title', 'visualizer' ) . '" name="s" value="' . $filterBy . '">
                <input type="hidden" name="page" value="visualizer">
                <button type="submit" id="search-submit" title="' . __( 'Search', 'visualizer' ) . '"><i class="dashicons dashicons-search"></i></button>
           </p> </form>';

		echo '</div>';
		echo '<div id="visualizer-content-wrapper">';
		if ( ! empty( $this->charts ) ) {
			echo '<div id="visualizer-library" class="visualizer-clearfix">';
			$count = 0;
			foreach ( $this->charts as $placeholder_id => $chart ) {
				$this->_renderChartBox( $placeholder_id, $chart['id'] );
				// show the sidebar after the first 3 charts.
				if ( $count++ === 2 ) {
					$this->_renderSidebar();
				}
			}
			// show the sidebar if there are less than 3 charts.
			if ( $count < 3 ) {
				$this->_renderSidebar();
			}
			echo '</div>';
		} else {
			echo '<div id="visualizer-library" class="visualizer-clearfix">';
			echo '<div class="visualizer-chart">';
			echo '<div class="visualizer-chart-canvas visualizer-nochart-canvas">';
			echo '<div class="visualizer-notfound">', esc_html__( 'No charts found', 'visualizer' ), '<p><h2><a href="javascript:;" class="add-new-h2 add-new-chart">', esc_html__( 'Add New', 'visualizer' ), '</a></h2></p></div>';
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
			$this->_renderSidebar();
			echo '</div>';
		}
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
		// for ChartJS, title is an array.
		if ( is_array( $title ) && isset( $title['text'] ) ) {
			$title = $title['text'];
		}
		if ( empty( $title ) ) {
			$title  = '#' . $chart_id;
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

		$chart_status   = array( 'date' => get_the_modified_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $chart_id ), 'error' => get_post_meta( $chart_id, Visualizer_Plugin::CF_ERROR, true ), 'icon' => 'dashicons-yes-alt', 'title' => '' );
		if ( ! empty( $chart_status['error'] ) ) {
			$chart_status['icon'] = 'error dashicons-dismiss';
			$chart_status['title'] = __( 'Click to view the error', 'visualizer' );
		}

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
		echo '<hr><div class="visualizer-chart-status"><span class="visualizer-date" title="' . __( 'Last Updated', 'visualizer' ) . '">' . $chart_status['date'] . '</span><span class="visualizer-error"><i class="dashicons ' . $chart_status['icon'] . '" data-viz-error="' . esc_attr( str_replace( '"', "'", $chart_status['error'] ) ) . '" title="' . esc_attr( $chart_status['title'] ) . '"></i></span></div>';
		echo '</div>';
		echo '</div>';
	}

	/**
	 * Render sidebar.
	 */
	private function _renderSidebar() {
		if ( ! Visualizer_Module::is_pro() ) {
			echo '<div id="visualizer-sidebar">';
			echo '<div class="visualizer-sidebar-box">';
			echo '<h3>' . __( 'Discover the power of PRO!', 'visualizer' ) . '</h3><ul>';
			echo '<li>' . __( 'Spreadsheet like editor', 'visualizer' ) . '</li>';
			echo '<li>' . __( 'Import from other charts', 'visualizer' ) . '</li>';
			echo '<li>' . __( 'Use database query to create charts', 'visualizer' ) . '</li>';
			echo '<li>' . __( 'Create charts from WordPress tables', 'visualizer' ) . '</li>';
			echo '<li>' . __( 'Frontend editor', 'visualizer' ) . '</li>';
			echo '<li>' . __( 'Private charts', 'visualizer' ) . '</li>';
			echo '<li>' . __( 'Auto-sync with online files', 'visualizer' ) . '</li>';
			echo '<li>' . __( '6 more chart types', 'visualizer' ) . '</li></ul>';
			echo '<p><a href="' . Visualizer_Plugin::PRO_TEASER_URL . '" target="_blank" class="button button-primary">' . __( 'View more features', 'visualizer' ) . '</a></p>';
			echo '<p style="background-color: #0073aac7; color: #ffffff; padding: 2px; font-weight: bold;">' . __( 'We offer a 30-day no-questions-asked money back guarantee!', 'visualizer' ) . '</p>';
			echo '<p><a href="' . VISUALIZER_SURVEY . '" target="_blank" class="">' . __( 'Don\'t see the features you need? Help us improve!', 'visualizer' ) . '</a></p>';
			echo '</div>';
			echo '</div>';
		}
	}

}
