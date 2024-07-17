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
	 * Displays the search form.
	 */
	private function getDisplayForm() {
		echo '<div class="visualizer-library-form">
		<form action="' . admin_url( 'admin.php' ) . '">
			<input type="hidden" name="page" value="' . Visualizer_Plugin::NAME . '"/>
				<select class="viz-filter" name="type">
		';

		echo '<option value="" selected>' . __( 'All types', 'visualizer' ) . '</option>';

		$type = isset( $_GET['type'] ) ? $_GET['type'] : '';
		$enabled = array();
		$disabled = array();
		foreach ( $this->types as $id => $array ) {
			if ( ! is_array( $array ) ) {
				// support for old pro
				$array = array( 'enabled' => true, 'name' => $array );
			}
			if ( ! $array['enabled'] ) {
				$disabled[ $id ] = $array['name'];
				continue;
			}
			$enabled[ $id ] = $array['name'];
		}

		asort( $enabled );
		asort( $disabled );

		foreach ( $enabled as $id => $name ) {
			echo '<option value="' . esc_attr( $id ) . '" ' . selected( $type, $id ) . '>' . $name . '</option>';
		}

		if ( $disabled ) {
			echo '<optgroup label="' . __( 'Not available', 'visualizer' ) . '">';
			foreach ( $disabled as $id => $name ) {
				echo '<option value="' . esc_attr( $id ) . '" ' . selected( $type, $id ) . ' disabled>' . $name . '</option>';
			}
			echo '</optgroup>';
		}

		echo '
				</select>
				<select class="viz-filter" name="library">
		';

		$libraries = array( '', 'ChartJS', 'DataTable', 'GoogleCharts' );
		$library = isset( $_GET['library'] ) ? $_GET['library'] : '';
		foreach ( $libraries as $lib ) {
			echo '<option value="' . esc_attr( $lib ) . '" ' . selected( $library, $lib ) . '>' . ( $lib === '' ? __( 'All libraries', 'visualizer' ) : $lib ) . '</option>';
		}

		echo '
				</select>
				<select class="viz-filter" name="date">
		';

		$dates = Visualizer_Plugin::getSupportedDateFilter();
		$date = isset( $_GET['date'] ) ? $_GET['date'] : '';
		foreach ( Visualizer_Plugin::getSupportedDateFilter() as $dt => $label ) {
			echo '<option value="' . esc_attr( $dt ) . '" ' . selected( $date, $dt ) . '>' . $label . '</option>';
		}

		echo '
				</select>
				<select class="viz-filter" name="source">
		';

		$disabled = array();
		$sources = array( 'json' => __( 'JSON', 'visualizer' ), 'csv' => __( 'Local CSV', 'visualizer' ), 'csv_remote' => __( 'Remote CSV', 'visualizer' ), 'query' => __( 'Database', 'visualizer' ), 'query_wp' => __( 'WordPress', 'visualizer' ) );
		if ( ! Visualizer_Module::is_pro() ) {
			$disabled['query'] = $sources['query'];
			unset( $sources['query'] );
		}
		if ( ! apply_filters( 'visualizer_is_business', false ) ) {
			$disabled['query_wp'] = $sources['query_wp'];
			unset( $sources['query_wp'] );
		}
		$sources = array_filter( $sources );
		uasort(
			$sources, function( $a, $b ) {
				if ( $a === $b ) {
					return 0;
				}
				return ( $a < $b ) ? -1 : 1;
			}
		);

		$source = isset( $_GET['source'] ) ? $_GET['source'] : '';
		echo '<option value="">' . __( 'All sources', 'visualizer' ) . '</option>';
		foreach ( $sources as $field => $label ) {
			echo '<option value="' . esc_attr( $field ) . '" ' . selected( $source, $field ) . '>' . $label . '</option>';
		}

		if ( $disabled ) {
			echo '<optgroup label="' . __( 'Not available', 'visualizer' ) . '">';
			foreach ( $disabled as $id => $name ) {
				echo '<option value="' . esc_attr( $id ) . '" ' . selected( $type, $id ) . ' disabled>' . $name . '</option>';
			}
			echo '</optgroup>';
		}

		$name = isset( $_GET['s'] ) ? $_GET['s'] : '';
		echo '
				</select>
				<input class="viz-filter"  type="text" name="s" placeholder="' . __( 'Enter title', 'visualizer' ) . '" value="' . esc_attr( $name ) . '">
		';

		echo '
				<span class="viz-filter">|</span>
				<select class="viz-filter" name="orderby">
		';

		$order_by_fields = apply_filters( 'visualizer_filter_order_by', array( 'date' => __( 'Date', 'visualizer' ), 's' => __( 'Title', 'visualizer' ) ) );
		$order_by = isset( $_GET['orderby'] ) ? $_GET['orderby'] : '';
		echo '<option value="">' . __( 'Order By', 'visualizer' ) . '</option>';
		foreach ( $order_by_fields as $field => $label ) {
			echo '<option value="' . esc_attr( $field ) . '" ' . selected( $order_by, $field ) . '>' . $label . '</option>';
		}

		echo '
				</select>
				<select class="viz-filter" name="order">
		';

		$order_type = array( 'desc' => __( 'Descending', 'visualizer' ), 'asc' => __( 'Ascending', 'visualizer' ) );
		$order = isset( $_GET['order'] ) ? $_GET['order'] : 'desc';
		foreach ( $order_type as $field => $label ) {
			echo '<option value="' . esc_attr( $field ) . '" ' . selected( $order, $field ) . '>' . $label . '</option>';
		}

		echo '
				</select>
				<input type="submit" class="viz-filter button button-secondary" value="' . __( 'Apply Filters', 'visualizer' ) . '">
				<input type="button" id="viz-lib-reset" class="viz-filter button button-secondary" value="' . __( 'Clear Filters', 'visualizer' ) . '">
		</form>
		</div>';
	}
	/**
	 * Renders pro charts blocker.
	 *
	 * @access private
	 */
	private function _renderProPopupBlocker() {
		if ( Visualizer_Module::is_pro() ) {
			return;
		}
		$license = get_option( 'visualizer_pro_license_data', 'free' );
		$license_key = '';
		$download_id = '';
		if ( ! empty( $license ) && is_object( $license ) ) {
			$license_key = $license->key;
			$download_id = $license->download_id;
		}
		$admin_license_url = admin_url( 'options-general.php#visualizer_pro_license' );
		$renew_license_url = tsdk_utmify( Visualizer_Plugin::STORE_URL . '?edd_license_key=' . $license_key . '&download_id=' . $download_id, 'visualizer_license_block' );
		echo '
				<div class="vizualizer-renew-notice-overlay" id="overlay-visualizer"></div>
				<div class="vizualizer-renew-notice-popup">
					<h1 class="vizualizer-renew-notice-heading">Alert!</h1>
					<p class="vizualizer-renew-notice-message">' . esc_html__( 'In order to edit premium charts, benefit from updates and support for Visualizer Premium plugin, please renew your license code or activate it.', 'visualizer' ) . '</p>
					<div class="vizualizer-renew-notice-buttons-container">
						<a href="' . esc_url( $renew_license_url) . '" target="_blank">
							<button class="vizualizer-renew-notice-button vizualizer-renew-notice-renew-button">
								<span class="dashicons dashicons-cart"></span>' . esc_html__( 'Renew License', 'visualizer' ) . ' 
							</button>
						</a>
						<a href="' . esc_url( $admin_license_url ) . '">
							<button class="vizualizer-renew-notice-button vizualizer-renew-notice-activate-button">
								<span class="dashicons dashicons-unlock"></span> ' . esc_html__( 'Activate License', 'visualizer' ) . ' 
							</button>
						</a>
						<button class="vizualizer-renew-notice-button vizualizer-renew-notice-close-icon" aria-label="Close" onclick="closePopup()">
							<i class="dashicons dashicons-no"></i>
						</button>
					</div>
				</div>
				<script>
				function closePopup() {
					var overlay = document.getElementById("overlay-visualizer");
					var popup = document.querySelector(".vizualizer-renew-notice-popup");
					overlay.style.display = "none";
					popup.style.display = "none";
				}
				</script>';

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
		$filterBy = ! empty( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : null; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		// Added by Ash/Upwork
		echo $this->custom_css;

		$this->_renderProPopupBlocker();

		echo '<div id="visualizer-types" class="visualizer-clearfix">';
		echo '<svg xmlns="http://www.w3.org/2000/svg" style="display: none;"><symbol id="list-icon" fill="currentColor"><path d="M8 0C3.58 0 0 3.58 0 8C0 12.42 3.58 16 8 16C12.42 16 16 12.42 16 8C16 3.58 12.42 0 8 0ZM7.385 12.66H6.045L2.805 8.12L4.146 6.87L6.715 9.27L11.856 3.339L13.196 4.279L7.385 12.66Z"/></symbol></svg>';
		$this->getDisplayForm();
		echo '</div>';
		echo '<div id="visualizer-content-wrapper">';
		if ( ! empty( $this->charts ) ) {
			echo '<div id="visualizer-library" class="visualizer-clearfix">';
			$count = 0;
			foreach ( $this->charts as $placeholder_id => $chart ) {
				// show the sidebar after the first 3 charts.
				$count++;
				$enable_controls = false;
				$settings = isset( $chart['settings'] ) ? $chart['settings'] : array();
				if ( ! empty( $settings['controls']['controlType'] ) ) {
					$column_index = $settings['controls']['filterColumnIndex'];
					$column_label = $settings['controls']['filterColumnLabel'];
					if ( 'false' !== $column_index || 'false' !== $column_label ) {
						$enable_controls = true;
					}
				}
				if ( 3 === $count ) {
					$this->_renderSidebar();
					$this->_renderChartBox( $placeholder_id, $chart['id'], $enable_controls );
				} else {
					$this->_renderChartBox( $placeholder_id, $chart['id'], $enable_controls );
				}
			}
			// show the sidebar if there are less than 3 charts.
			if ( $count < 3 ) {
				$this->_renderSidebar();
			}
			echo '</div>';
		} else {
			echo '<div id="visualizer-library" class="visualizer-clearfix">';
			echo '<div class="items"><div class="visualizer-chart">';
			echo '<div class="visualizer-chart-canvas visualizer-nochart-canvas">';
			echo '<div class="visualizer-notfound">', esc_html__( 'No charts found', 'visualizer' ), '<p><h2><a href="javascript:;" class="add-new-h2 add-new-chart">', esc_html__( 'Add New', 'visualizer' ), '</a></h2></p></div>';
			echo '</div>';
			echo '<div class="visualizer-chart-footer visualizer-clearfix">';
			echo '<div class="visualizer-action-group visualizer-nochart">';
			echo '<span class="visualizer-chart-action visualizer-nochart-delete"><span class="dashicons dashicons-trash"></span></span>';
			echo '<span class="visualizer-chart-action visualizer-nochart-shortcode"><span class="dashicons dashicons-shortcode"></span></span>';
			echo '<span class="visualizer-chart-action visualizer-nochart-image"><span class="dashicons dashicons-format-image"></span></span>';
			echo '<span class="visualizer-chart-action visualizer-nochart-export"><span class="dashicons dashicons-download"></span></span>';
			echo '<span class="visualizer-chart-action visualizer-nochart-clone"><span class="dashicons dashicons-admin-page"></span></span>';
			echo '<span class="visualizer-chart-action visualizer-nochart-edit"><span class="dashicons dashicons-admin-generic"></span></span>';
			echo '</div>';
			echo '</div>';
			echo '</div></div>';
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
	private function _renderChartBox( $placeholder_id, $chart_id, $with_filter = false ) {
		$settings    = get_post_meta( $chart_id, Visualizer_Plugin::CF_SETTINGS );
		$title       = '#' . $chart_id;
		if ( ! empty( $settings[0]['title'] ) ) {
			$title  = $settings[0]['title'];
		}
		// for ChartJS, title is an array.
		if ( is_array( $title ) && isset( $title['text'] ) ) {
			$title = $title['text'];
		}
		if ( ! empty( $settings[0]['backend-title'] ) ) {
			$title  = $settings[0]['backend-title'];
		}
		if ( empty( $title ) ) {
			$title  = '#' . $chart_id;
		}

		$ajax_url    = admin_url( 'admin-ajax.php' );
		$delete_url  = esc_url(
			add_query_arg(
				array(
					'action' => Visualizer_Plugin::ACTION_DELETE_CHART,
					'nonce'  => wp_create_nonce(),
					'chart'  => $chart_id,
				),
				$ajax_url
			)
		);
		$clone_url   = esc_url(
			add_query_arg(
				array(
					'action' => Visualizer_Plugin::ACTION_CLONE_CHART,
					'nonce'  => wp_create_nonce( Visualizer_Plugin::ACTION_CLONE_CHART ),
					'chart'  => $chart_id,
					'type'   => $this->type,
				),
				$ajax_url
			)
		);
		$export_link = esc_url(
			add_query_arg(
				array(
					'action'   => Visualizer_Plugin::ACTION_EXPORT_DATA,
					'chart'    => $chart_id,
					'security' => wp_create_nonce( Visualizer_Plugin::ACTION_EXPORT_DATA . Visualizer_Plugin::VERSION ),
				),
				admin_url( 'admin-ajax.php' )
			)
		);
		$chart_type = get_post_meta( $chart_id, Visualizer_Plugin::CF_CHART_TYPE, true );

		$types = ['area', 'geo', 'column', 'bubble', 'scatter', 'gauge', 'candlestick', 'timeline', 'combo', 'polarArea', 'radar' ];

		$pro_class = '';

		if ( ! empty( $chart_type ) && in_array( $chart_type, $types, true ) ) {
			$pro_class = 'viz-is-pro-chart';
		}

		$chart_status   = array( 'date' => get_the_modified_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $chart_id ), 'error' => get_post_meta( $chart_id, Visualizer_Plugin::CF_ERROR, true ), 'icon' => 'dashicons-yes-alt', 'title' => 'A-OK!' );
		if ( ! empty( $chart_status['error'] ) ) {
			$chart_status['icon'] = 'error dashicons-dismiss';
			$chart_status['title'] = __( 'Click to view the error', 'visualizer' );
		}
		$shortcode = sprintf( '[visualizer id="%s" class=""]', $chart_id );
		echo '<div class="items"><div class="visualizer-chart"><div class="visualizer-chart-title">', esc_html( $title ), '</div>';
		if ( Visualizer_Module::is_pro() && $with_filter ) {
			echo '<div id="chart_wrapper_' . $placeholder_id . '">';
			echo '<div id="control_wrapper_' . $placeholder_id . '" class="vz-library-chart-filter"></div>';
		}
		echo '<div id="', $placeholder_id, '" class="visualizer-chart-canvas">';
		echo '<img src="', VISUALIZER_ABSURL, 'images/ajax-loader.gif" class="loader">';
		echo '</div>';
		if ( Visualizer_Module::is_pro() && $with_filter ) {
			echo '</div>';
		}
		echo '<div class="visualizer-chart-footer visualizer-clearfix">';
		echo '<div class="visualizer-action-group">';
		echo '<a class="visualizer-chart-action visualizer-chart-delete" href="', $delete_url, '" onclick="return showNotice.warn();"><span class="dashicons dashicons-trash"></span><span class="tooltip-text">' . esc_attr__( 'Delete', 'visualizer' ) . '</span></a>';
		echo '<a class="visualizer-chart-action visualizer-chart-shortcode ' . esc_attr( $pro_class ) . '" href="javascript:;" data-clipboard-text="', esc_attr( $shortcode ), '"><span class="dashicons dashicons-shortcode ' . esc_attr( $pro_class ) . '"></span><span class="tooltip-text">' . esc_attr__( 'Copy Shortcode', 'visualizer' ) . '</span></a>';
		if ( $this->can_chart_have_action( 'image', $chart_id ) ) {
			echo '<a class="visualizer-chart-action visualizer-chart-image ' . esc_attr( $pro_class ) . '" href="javascript:;" data-chart="visualizer-', $chart_id, '" data-chart-title="', $title, '"><span class="dashicons dashicons-format-image ' . esc_attr( $pro_class ) . '"></span><span class="tooltip-text">' . esc_attr__( 'Download PNG', 'visualizer' ) . '</span></a>';
		}
		echo '<a class="visualizer-chart-action visualizer-chart-export ' . esc_attr( $pro_class ) . '" href="javascript:;" data-chart="', $export_link, '"><span class="dashicons dashicons-download ' . esc_attr( $pro_class ) . '"></span><span class="tooltip-text">' . esc_attr__( 'Export CSV', 'visualizer' ) . '</span></a>';
		echo '<a class="visualizer-chart-action visualizer-chart-clone ' . esc_attr( $pro_class ) . '" href="', $clone_url, '"><span class="dashicons dashicons-admin-page ' . esc_attr( $pro_class ) . '"></span><span class="tooltip-text">' . esc_attr__( 'Duplicate', 'visualizer' ) . '</span></a>';
		echo '<a class="visualizer-chart-action visualizer-chart-edit ' . esc_attr( $pro_class ) . '" href="javascript:;" data-chart="', $chart_id, '"><span class="dashicons dashicons-admin-generic ' . esc_attr( $pro_class ) . '"></span><span class="tooltip-text">' . esc_attr__( 'Edit', 'visualizer' ) . '</span></a>';
		echo '</div>';
		do_action( 'visualizer_chart_languages', $chart_id );
		echo '<hr><div class="visualizer-chart-status"><span title="' . __( 'Chart ID', 'visualizer' ) . '">(' . $chart_id . '):</span> <span class="visualizer-date" title="' . __( 'Last Updated', 'visualizer' ) . '">' . $chart_status['date'] . '</span><span class="visualizer-error"><i class="dashicons ' . $chart_status['icon'] . '" data-viz-error="' . esc_attr( str_replace( '"', "'", $chart_status['error'] ) ) . '" title="' . esc_attr( $chart_status['title'] ) . '"></i></span></div>';
		echo '</div>';
		echo '</div></div>';
	}

	/**
	 * Render 2-col sidebar
	 */
	private function _renderSidebar() {
		if ( ! Visualizer_Module::is_pro() ) {
			echo '<div class="items">';
			echo '<div class="viz-pro">';
			echo '<div id="visualizer-sidebar" class="one-columns">';
			echo '<div class="visualizer-sidebar-box">';
			echo '<h3>' . __( 'Discover the power of PRO!', 'visualizer' ) . '</h3><ul>';
			if ( Visualizer_Module_Admin::proFeaturesLocked() ) {
				echo '<li><svg class="icon list-icon"><use xlink:href="#list-icon"></use></svg>' . __( '6 more chart types', 'visualizer' );
			} else {
				echo '<li><svg class="icon list-icon"><use xlink:href="#list-icon"></use></svg>' . __( '11 more chart types', 'visualizer' ) . '</li>';
				echo '<li><svg class="icon list-icon"><use xlink:href="#list-icon"></use></svg>' . __( 'Synchronize Data Periodically', 'visualizer' ) . '</li>';
				echo '<li><svg class="icon list-icon"><use xlink:href="#list-icon"></use></svg>' . __( 'ChartJS Charts', 'visualizer' ) . '</li>';
				echo '<li><svg class="icon list-icon"><use xlink:href="#list-icon"></use></svg>' . __( 'Table Google chart', 'visualizer' ) . '</li>';
				echo '<li><svg class="icon list-icon"><use xlink:href="#list-icon"></use></svg>' . __( 'Frontend Actions(Print, Export, Copy, Download)', 'visualizer' ) . '</li>';
			}
			echo '<li><svg class="icon list-icon"><use xlink:href="#list-icon"></use></svg>' . __( 'Spreadsheet like editor', 'visualizer' ) . '</li>';
			echo '<li><svg class="icon list-icon"><use xlink:href="#list-icon"></use></svg>' . __( 'Import from other charts', 'visualizer' ) . '</li>';
			echo '<li><svg class="icon list-icon"><use xlink:href="#list-icon"></use></svg>' . __( 'Use database query to create charts', 'visualizer' ) . '</li>';
			echo '<li><svg class="icon list-icon"><use xlink:href="#list-icon"></use></svg>' . __( 'Create charts from WordPress tables', 'visualizer' ) . '</li>';
			echo '<li><svg class="icon list-icon"><use xlink:href="#list-icon"></use></svg>' . __( 'Frontend editor', 'visualizer' ) . '</li>';
			echo '<li><svg class="icon list-icon"><use xlink:href="#list-icon"></use></svg>' . __( 'Private charts', 'visualizer' ) . '</li>';
			echo '<li><svg class="icon list-icon"><use xlink:href="#list-icon"></use></svg>' . __( 'WPML support for translating charts', 'visualizer' ) . '</li>';
			echo '<li><svg class="icon list-icon"><use xlink:href="#list-icon"></use></svg>' . __( 'Integration with Woocommerce Data endpoints', 'visualizer' ) . '</li>';
			echo '<li><svg class="icon list-icon"><use xlink:href="#list-icon"></use></svg>' . __( 'Auto-sync with online files', 'visualizer' ) . '</li></ul>';
			echo '<p class="vz-sidebar-box-action"><a href="' . tsdk_utmify( Visualizer_Plugin::PRO_TEASER_URL, 'sidebarMenuUpgrade', 'index' ) . '#pro-features" target="_blank" class="button button-secondary">' . __( 'View more features', 'visualizer' ) . '</a><a href="' . tsdk_utmify( Visualizer_Plugin::PRO_TEASER_URL, 'sidebarMenuUpgrade', 'index' ) . '#pricing" target="_blank" class="button button-primary">' . __( 'Upgrade Now', 'visualizer' ) . '</a></p>';
			echo '</div>';
			echo '</div>';
			echo '</div>';
			echo '</div>';
		}
	}

}
