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
		$this->getDisplayForm();
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
			echo '<span class="visualizer-chart-action visualizer-nochart-shortcode"></span>';
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

		$chart_status   = array( 'date' => get_the_modified_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $chart_id ), 'error' => get_post_meta( $chart_id, Visualizer_Plugin::CF_ERROR, true ), 'icon' => 'dashicons-yes-alt', 'title' => 'A-OK!' );
		if ( ! empty( $chart_status['error'] ) ) {
			$chart_status['icon'] = 'error dashicons-dismiss';
			$chart_status['title'] = __( 'Click to view the error', 'visualizer' );
		}

		$shortcode = sprintf( '[visualizer id="%s" lazy="no" class=""]', $chart_id );
		echo '<div class="visualizer-chart"><div class="visualizer-chart-title">', esc_html( $title ), '</div>';
		echo '<div id="', $placeholder_id, '" class="visualizer-chart-canvas">';
		echo '<img src="', VISUALIZER_ABSURL, 'images/ajax-loader.gif" class="loader">';
		echo '</div>';
		echo '<div class="visualizer-chart-footer visualizer-clearfix">';
		echo '<a class="visualizer-chart-action visualizer-chart-delete" href="', $delete_url, '" title="', esc_attr__( 'Delete', 'visualizer' ), '" onclick="return showNotice.warn();"></a>';
		echo '<a class="visualizer-chart-action visualizer-chart-clone" href="', $clone_url, '" title="', esc_attr__( 'Clone', 'visualizer' ), '"></a>';
		echo '<a class="visualizer-chart-action visualizer-chart-edit" href="javascript:;" title="', esc_attr__( 'Edit', 'visualizer' ), '" data-chart="', $chart_id, '"></a>';
		echo '<a class="visualizer-chart-action visualizer-chart-export" href="javascript:;" title="', esc_attr__( 'Export', 'visualizer' ), '" data-chart="', $export_link, '"></a>';
		if ( $this->can_chart_have_action( 'image', $chart_id ) ) {
			echo '<a class="visualizer-chart-action visualizer-chart-image" href="javascript:;" title="', esc_attr__( 'Download as image', 'visualizer' ), '" data-chart="visualizer-', $chart_id, '" data-chart-title="', $title, '"></a>';
		}
		echo '<a class="visualizer-chart-action visualizer-chart-shortcode" href="javascript:;" title="', esc_attr__( 'Click to copy shortcode', 'visualizer' ), '" data-clipboard-text="', esc_attr( $shortcode ), '"></a>';
		echo '<span>&nbsp;</span>';
		echo '<hr><div class="visualizer-chart-status"><span title="' . __( 'Chart ID', 'visualizer' ) . '">(' . $chart_id . '):</span> <span class="visualizer-date" title="' . __( 'Last Updated', 'visualizer' ) . '">' . $chart_status['date'] . '</span><span class="visualizer-error"><i class="dashicons ' . $chart_status['icon'] . '" data-viz-error="' . esc_attr( str_replace( '"', "'", $chart_status['error'] ) ) . '" title="' . esc_attr( $chart_status['title'] ) . '"></i></span></div>';
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
