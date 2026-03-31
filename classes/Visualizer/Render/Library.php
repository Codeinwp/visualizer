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
	 * Cached result of _isListView() to avoid repeat DB reads per request.
	 *
	 * @var bool|null
	 */
	private $_list_view_cached = null;

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
		echo ' <div class="viz-add-new-group">';
		echo '<a href="javascript:;" class="add-new-h2 add-new-chart">', esc_html__( 'Add New Chart', 'visualizer' ), '</a>';
		echo '<button type="button" class="viz-add-new-toggle" aria-haspopup="true" aria-expanded="false"><span class="dashicons dashicons-arrow-down-alt2"></span></button>';
		echo '<div class="viz-add-new-menu" role="menu" aria-hidden="true">';
		echo '<button type="button" class="viz-add-new-item" data-viz-builder="ai">', esc_html__( 'AI Chart Builder', 'visualizer' ), '</button>';
		echo '<button type="button" class="viz-add-new-item" data-viz-builder="classic">', esc_html__( 'Classic Builder', 'visualizer' ), '</button>';
		echo '</div>';
		if ( Visualizer_Module::is_pro() ) {
			echo ' <a href="' . admin_url( 'options-general.php#visualizer_pro_license' ) . '" class="page-title-action">', esc_html__( 'License Settings', 'visualizer' ), '</a>';
		}
		echo '</div>';
		echo '</h2>';
		$this->_renderMessages();
		$this->_renderLibrary();
		echo '<div id="viz-chart-builder-root"></div>';
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
			printf(
				// translators: %s - the name of the option.
				esc_html__( '%s option is disabled in your php.ini config. Please enable it by changing its value to 1. This option increases the speed of remote CSV uploading.', 'visualizer' ),
				'<b>allow_url_fopen</b>'
			);
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
			<input type="hidden" name="view" value="' . esc_attr( $this->_isListView() ? 'list' : 'grid' ) . '"/>
			<span class="viz-view-toggle-group">' . $this->_getViewToggleHTML() . '</span>
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
			$sources, function ( $a, $b ) {
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
			$license_key = isset( $license->key ) ? $license->key : '';
			$download_id = isset( $license->download_id ) ? $license->download_id : '';
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
		echo '<div id="tsdk_banner" class="visualizer-banner"></div>';
		if ( ! empty( $this->charts ) ) {
			if ( $this->_isListView() ) {
				echo '<div id="visualizer-library" class="visualizer-clearfix view-list">';
				$this->_renderSidebar();
				echo '<table class="wp-list-table widefat striped viz-charts-table">';
				echo '<thead><tr>';
				echo '<th class="col-id">' . esc_html__( 'ID', 'visualizer' ) . '</th>';
				echo '<th class="col-title">' . esc_html__( 'Title', 'visualizer' ) . '</th>';
				echo '<th class="col-type">' . esc_html__( 'Type', 'visualizer' ) . '</th>';
				echo '<th class="col-shortcode">' . esc_html__( 'Shortcode', 'visualizer' ) . '</th>';
				echo '<th class="col-actions">' . esc_html__( 'Actions', 'visualizer' ) . '</th>';
				echo '</tr></thead><tbody>';
				foreach ( $this->charts as $placeholder_id => $chart ) {
					$enable_controls = false;
					$settings        = isset( $chart['settings'] ) ? $chart['settings'] : array();
					if ( ! empty( $settings['controls']['controlType'] ) ) {
						$column_index = $settings['controls']['filterColumnIndex'];
						$column_label = $settings['controls']['filterColumnLabel'];
						if ( 'false' !== $column_index || 'false' !== $column_label ) {
							$enable_controls = true;
						}
					}
					$this->_renderChartBox( $placeholder_id, $chart['id'], $enable_controls );
				}
				echo '</tbody></table>';
				echo '</div>';
			} else {
				echo '<div id="visualizer-library" class="visualizer-clearfix view-grid">';
				$this->_renderSidebar();
				foreach ( $this->charts as $placeholder_id => $chart ) {
					$enable_controls = false;
					$settings        = isset( $chart['settings'] ) ? $chart['settings'] : array();
					if ( ! empty( $settings['controls']['controlType'] ) ) {
						$column_index = $settings['controls']['filterColumnIndex'];
						$column_label = $settings['controls']['filterColumnLabel'];
						if ( 'false' !== $column_index || 'false' !== $column_label ) {
							$enable_controls = true;
						}
					}
					$this->_renderChartBox( $placeholder_id, $chart['id'], $enable_controls );
				}
				echo '</div>';
			}
		} else {
			echo '<div id="visualizer-library" class="visualizer-clearfix view-grid">';
			$this->_renderSidebar();
			echo '<div class="items"><div class="visualizer-chart">';
			echo '<div class="visualizer-chart-canvas visualizer-nochart-canvas">';
			echo '<div class="visualizer-notfound">';
			echo esc_html__( 'No charts found. Click \'Add New Chart\' to create one.', 'visualizer' );
			echo '<p><h2>';
			echo '<div class="viz-add-new-group">';
			echo '<a href="javascript:;" class="add-new-h2 add-new-chart">', esc_html__( 'Add New Chart', 'visualizer' ), '</a>';
			echo '<button type="button" class="viz-add-new-toggle" aria-haspopup="true" aria-expanded="false"><span class="dashicons dashicons-arrow-down-alt2"></span></button>';
			echo '<div class="viz-add-new-menu" role="menu" aria-hidden="true">';
			echo '<button type="button" class="viz-add-new-item" data-viz-builder="ai">', esc_html__( 'AI Chart Builder', 'visualizer' ), '</button>';
			echo '<button type="button" class="viz-add-new-item" data-viz-builder="classic">', esc_html__( 'Classic Builder', 'visualizer' ), '</button>';
			echo '</div>';
			echo '</div>';
			echo '</h2></p></div>';
			echo '</div>';
			echo '<div class="visualizer-chart-footer visualizer-clearfix">';
			echo '<div class="visualizer-action-group visualizer-nochart">';
			echo '<span class="visualizer-chart-action visualizer-nochart-delete"><span class="dashicons dashicons-trash"></span></span>';
			echo '<span class="visualizer-chart-action visualizer-nochart-shortcode"><span class="dashicons dashicons-shortcode"></span></span>';
			echo '<span class="visualizer-chart-action visualizer-nochart-image"><span class="dashicons dashicons-format-image"></span></span>';
			echo '<span class="visualizer-chart-action visualizer-nochart-export"><span class="dashicons dashicons-download"></span></span>';
			echo '<span class="visualizer-chart-action visualizer-nochart-clone"><span class="dashicons dashicons-admin-page"></span></span>';
			echo '<span class="visualizer-chart-action visualizer-nochart-edit"><span class="dashicons dashicons-edit"></span></span>';
			echo '</div>';
			echo '</div>';
			echo '</div></div>';
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
		$settings      = get_post_meta( $chart_id, Visualizer_Plugin::CF_SETTINGS );
		$chart_library = get_post_meta( $chart_id, Visualizer_Plugin::CF_CHART_LIBRARY, true );
		$title         = '#' . $chart_id;
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
		$chart_type_label = $chart_type;
		if ( empty( $chart_type_label ) && 'd3' === strtolower( (string) $chart_library ) ) {
			$chart_type_label = __( 'AI', 'visualizer' );
		}

		$types = array( 'area', 'geo', 'column', 'bubble', 'scatter', 'gauge', 'candlestick', 'timeline', 'combo', 'polarArea', 'radar' );

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

		if ( $this->_isListView() ) {
			// ── List view: table row ──
			echo '<tr class="viz-list-row">';
			echo '<td class="col-id">#' . esc_html( (string) $chart_id ) . '</td>';
			echo '<td class="col-title">' . esc_html( $title ) . '</td>';
			echo '<td class="col-type">' . ( ! empty( $chart_type_label ) ? '<span class="viz-chart-type-badge">' . esc_html( $chart_type_label ) . '</span>' : '&mdash;' ) . '</td>';
			echo '<td class="col-shortcode"><code class="viz-shortcode-display">' . esc_html( $shortcode ) . '</code></td>';
			echo '<td class="col-actions"><div class="visualizer-action-group">';
			echo '<a class="visualizer-chart-action visualizer-chart-delete" href="' . $delete_url . '" onclick="return showNotice.warn();"><span class="dashicons dashicons-trash"></span><span class="tooltip-text">' . esc_html__( 'Delete', 'visualizer' ) . '</span></a>';
			echo '<a class="visualizer-chart-action visualizer-chart-shortcode ' . esc_attr( $pro_class ) . '" href="javascript:;" data-clipboard-text="' . esc_attr( $shortcode ) . '"><span class="dashicons dashicons-shortcode ' . esc_attr( $pro_class ) . '"></span><span class="tooltip-text">' . esc_html__( 'Copy Shortcode', 'visualizer' ) . '</span></a>';
			echo '<a class="visualizer-chart-action visualizer-chart-export ' . esc_attr( $pro_class ) . '" href="javascript:;" data-chart="' . $export_link . '"><span class="dashicons dashicons-download ' . esc_attr( $pro_class ) . '"></span><span class="tooltip-text">' . esc_html__( 'Export CSV', 'visualizer' ) . '</span></a>';
			echo '<a class="visualizer-chart-action visualizer-chart-clone ' . esc_attr( $pro_class ) . '" href="' . $clone_url . '"><span class="dashicons dashicons-admin-page ' . esc_attr( $pro_class ) . '"></span><span class="tooltip-text">' . esc_html__( 'Duplicate', 'visualizer' ) . '</span></a>';
			echo '<a class="visualizer-chart-action visualizer-chart-edit button button-secondary ' . esc_attr( $pro_class ) . '" href="javascript:;" data-chart="' . esc_attr( (string) $chart_id ) . '" data-library="' . esc_attr( $chart_library ) . '"><span class="dashicons dashicons-edit ' . esc_attr( $pro_class ) . '"></span><span class="visualizer-action-label">' . esc_html__( 'Edit', 'visualizer' ) . '</span><span class="tooltip-text">' . esc_html__( 'Edit', 'visualizer' ) . '</span></a>';
			echo '</div></td>';
			echo '</tr>';
			return;
		}

		// ── Grid view: card ──
		$type_badge = ! empty( $chart_type ) ? '<span class="viz-chart-type-badge">' . esc_html( $chart_type ) . '</span>' : '';
		echo '<div class="items"><div class="visualizer-chart"><div class="visualizer-chart-title"><span>' . esc_html( $title ) . '</span>' . $type_badge . '</div>';
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
		echo '<a class="visualizer-chart-action visualizer-chart-edit button button-secondary ' . esc_attr( $pro_class ) . '" href="javascript:;" data-chart="', $chart_id, '" data-library="' . esc_attr( $chart_library ) . '"><span class="dashicons dashicons-edit ' . esc_attr( $pro_class ) . '"></span><span class="visualizer-action-label">' . esc_html__( 'Edit', 'visualizer' ) . '</span><span class="tooltip-text">' . esc_html__( 'Edit', 'visualizer' ) . '</span></a>';
		echo '<a class="visualizer-chart-action visualizer-chart-shortcode ' . esc_attr( $pro_class ) . '" href="javascript:;" data-clipboard-text="', esc_attr( $shortcode ), '"><span class="dashicons dashicons-shortcode ' . esc_attr( $pro_class ) . '"></span><span class="tooltip-text">' . esc_html__( 'Copy Shortcode', 'visualizer' ) . '</span></a>';
		if ( $this->can_chart_have_action( 'image', $chart_id ) ) {
			echo '<a class="visualizer-chart-action visualizer-chart-image ' . esc_attr( $pro_class ) . '" href="javascript:;" data-chart="visualizer-', $chart_id, '" data-chart-title="', $title, '"><span class="dashicons dashicons-format-image ' . esc_attr( $pro_class ) . '"></span><span class="tooltip-text">' . esc_html__( 'Download PNG', 'visualizer' ) . '</span></a>';
		}
		echo '<a class="visualizer-chart-action visualizer-chart-export ' . esc_attr( $pro_class ) . '" href="javascript:;" data-chart="', $export_link, '"><span class="dashicons dashicons-download ' . esc_attr( $pro_class ) . '"></span><span class="tooltip-text">' . esc_html__( 'Export CSV', 'visualizer' ) . '</span></a>';
		echo '<a class="visualizer-chart-action visualizer-chart-clone ' . esc_attr( $pro_class ) . '" href="', $clone_url, '"><span class="dashicons dashicons-admin-page ' . esc_attr( $pro_class ) . '"></span><span class="tooltip-text">' . esc_html__( 'Duplicate', 'visualizer' ) . '</span></a>';
		echo '<a class="visualizer-chart-action visualizer-chart-delete" href="', $delete_url, '" onclick="return showNotice.warn();"><span class="dashicons dashicons-trash"></span><span class="tooltip-text">' . esc_html__( 'Delete', 'visualizer' ) . '</span></a>';
		echo '</div>';
		do_action( 'visualizer_chart_languages', $chart_id );
		echo '<hr><div class="visualizer-chart-status"><span title="' . __( 'Chart ID', 'visualizer' ) . '">(' . $chart_id . '):</span> <span class="visualizer-date" title="' . __( 'Last Updated', 'visualizer' ) . '">' . $chart_status['date'] . '</span><span class="visualizer-error"><i class="dashicons ' . $chart_status['icon'] . '" data-viz-error="' . esc_attr( str_replace( '"', "'", $chart_status['error'] ) ) . '" title="' . esc_attr( $chart_status['title'] ) . '"></i></span></div>';
		echo '</div>';
		echo '</div></div>';
	}

	/**
	 * Returns true when the library should render in list (no-preview) mode.
	 *
	 * Priority: ?view= URL param (saves to user meta) → saved user meta → grid default.
	 *
	 * No nonce needed: this is a bookmarkable UI preference URL. A nonce would expire
	 * and break saved/shared links for zero real security gain — the value is allowlisted
	 * to 'list'|'grid' before any write happens.
	 */
	private function _isListView(): bool {
		if ( null !== $this->_list_view_cached ) {
			return $this->_list_view_cached;
		}
		if ( isset( $_GET['view'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$view = sanitize_text_field( wp_unslash( $_GET['view'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( in_array( $view, array( 'list', 'grid' ), true ) ) {
				update_user_meta( get_current_user_id(), 'visualizer_library_view', $view );
			}
			$this->_list_view_cached = ( 'list' === $view );
		} else {
			$saved                   = get_user_meta( get_current_user_id(), 'visualizer_library_view', true );
			$this->_list_view_cached = ( 'list' === $saved );
		}
		return $this->_list_view_cached;
	}

	/**
	 * Returns the HTML for the grid/list view toggle links.
	 */
	private function _getViewToggleHTML(): string {
		$is_list  = $this->_isListView();
		$grid_url = esc_url( add_query_arg( 'view', 'grid' ) );
		$list_url = esc_url( add_query_arg( 'view', 'list' ) );
		return '<a href="' . $grid_url . '" class="viz-view-toggle' . ( ! $is_list ? ' active' : '' ) . '" title="' . esc_attr__( 'Grid View', 'visualizer' ) . '"><span class="dashicons dashicons-screenoptions"></span></a>'
			. '<a href="' . $list_url . '" class="viz-view-toggle' . ( $is_list ? ' active' : '' ) . '" title="' . esc_attr__( 'List View', 'visualizer' ) . '"><span class="dashicons dashicons-list-view"></span></a>';
	}

	/**
	 * Render 2-col sidebar
	 */
	private function _renderSidebar() {
		if ( ! Visualizer_Module::is_pro() ) {
			$upgrade_url = tsdk_utmify( Visualizer_Plugin::PRO_TEASER_URL, 'sidebarMenuUpgrade', 'index' );
			$chart_types = Visualizer_Module_Admin::proFeaturesEnabled() ? __( '6 more chart types', 'visualizer' ) : __( '11 more chart types', 'visualizer' );
			echo '<div class="items--upsell">';
			echo '<div class="viz-upsell-banner">';
			echo '<span class="dashicons dashicons-star-filled viz-upsell-banner__icon"></span>';
			echo '<div class="viz-upsell-banner__text">';
			echo '<strong>' . esc_html__( 'Unlock the full power of Visualizer PRO!', 'visualizer' ) . '</strong>';
			/* translators: %s: number of additional chart types (e.g. "11 more chart types") */
			echo '<span>' . sprintf( esc_html__( '%s, periodic data sync, database queries, frontend editor, and more.', 'visualizer' ), esc_html( $chart_types ) ) . '</span>';
			echo '</div>';
			echo '<div class="viz-upsell-banner__actions">';
			echo '<a href="' . esc_url( $upgrade_url . '#pro-features' ) . '" target="_blank" class="button button-secondary">' . esc_html__( 'View Features', 'visualizer' ) . '</a>';
			echo '<a href="' . esc_url( $upgrade_url . '#pricing' ) . '" target="_blank" class="button button-primary">' . esc_html__( 'Upgrade Now', 'visualizer' ) . '</a>';
			echo '</div>';
			echo '</div>';
			echo '</div>';
		}
	}
}
