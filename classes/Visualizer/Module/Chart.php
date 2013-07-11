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
 * The module for all stuff related to getting, editing, creating and deleting charts.
 *
 * @category Visualizer
 * @package Module
 *
 * @since 1.0.0
 */
class Visualizer_Module_Chart extends Visualizer_Module {

	const NAME = __CLASS__;

	/**
	 * The chart object.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @var WP_Post
	 */
	private $_chart;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param Visualizer_Plugin $plugin The instance of the plugin.
	 */
	public function __construct( Visualizer_Plugin $plugin ) {
		parent::__construct( $plugin );

		$this->_addAjaxAction( Visualizer_Plugin::ACTION_GET_CHARTS, 'getCharts' );
		$this->_addAjaxAction( Visualizer_Plugin::ACTION_DELETE_CHART, 'deleteChart' );
		$this->_addAjaxAction( Visualizer_Plugin::ACTION_CREATE_CHART, 'renderChartPages' );
	}

	/**
	 * Sends json response.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @param array $results The response array.
	 */
	private function _sendResponse( $results ) {
		header( 'Content-type: application/json' );
		header( 'Cache-Control: no-cache, must-revalidate' );
		header( 'Expires: Mon, 26 Jul 1997 05:00:00 GMT' );

		echo json_encode( $results );
		exit;
	}

	/**
	 * Fetches charts from database.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function getCharts() {
		$query_args = array(
			'post_type'      => Visualizer_Plugin::CPT_VISUALIZER,
			'posts_per_page' => 9,
			'paged'          => filter_input( INPUT_GET, 'page', FILTER_VALIDATE_INT, array(
				'options' => array(
					'min_range' => 1,
					'default'   => 1,
				)
			) )
		);

		$filter = filter_input( INPUT_GET, 'filter' );
		if ( $filter && in_array( $filter, Visualizer_Plugin::getChartTypes() ) ) {
			$query_args['meta_query'] = array(
				array(
					'key'     => Visualizer_Plugin::CF_CHART_TYPE,
					'value'   => $filter,
					'compare' => '=',
				),
			);
		}

		$query = new WP_Query( $query_args );

		$charts = array();
		while( $query->have_posts() ) {
			$query->the_post();
			// $charts[] = array();
		}

		$this->_sendResponse( array(
			'success' => true,
			'data'    => $charts,
			'total'   => $query->max_num_pages,
		) );
	}

	/**
	 * Deletes a chart from database.
	 *
	 * @since 1.0.0
	 * @uses wp_delete_post() To delete a chart.
	 *
	 * @access public
	 */
	public function deleteChart() {
		$success = false;

		$chart_id = false;
		$nonce = Visualizer_Security::verifyNonces( filter_input( INPUT_POST, 'nonce' ) );
		$capable = current_user_can( 'delete_posts' );
		if ( $nonce && $capable ) {
			$chart_id = filter_input( INPUT_POST, 'chart', FILTER_VALIDATE_INT, array( 'options' => array( 'min_range' => 1 ) ) );
			if ( $chart_id ) {
				$chart = get_post( $chart_id );
				$success = $chart && $chart->post_type == Visualizer_Plugin::CPT_VISUALIZER;
			}
		}

		if ( $success && $chart_id ) {
			$deleted = wp_delete_post( $chart_id, true );
			$success = $deleted > 0;
		}

		$this->_sendResponse( array( 'success' => $success ) );
	}

	/**
	 * Renders appropriate page for chart builder. Creates new auto draft chart
	 * if no chart has been specified.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function renderChartPages() {
		// check chart, if chart not exists, will create new one and redirects to the same page with proper chart id
		$chart_id = filter_input( INPUT_GET, 'chart', FILTER_VALIDATE_INT );
		if ( !$chart_id || !( $chart = get_post( $chart_id ) ) || $chart->post_type != Visualizer_Plugin::CPT_VISUALIZER ) {
			$chart_id = wp_insert_post( array(
				'post_type'   => Visualizer_Plugin::CPT_VISUALIZER,
				'post_title'  => 'Visualization',
				'post_author' => get_current_user_id(),
				'post_status' => 'auto-draft',
			) );

			if ( $chart_id && !is_wp_error( $chart_id ) ) {
				add_post_meta( $chart_id, Visualizer_Plugin::CF_CHART_TYPE, 'line' );
			}

			wp_redirect( add_query_arg( 'chart', (int)$chart_id ) );
			exit;
		}

		// dispatch pages
		$this->_chart = $chart;
		switch ( filter_input( INPUT_GET, 'tab' ) ) {
			case 'data':
				$this->_handleDataPage();
				break;
			case 'settings':
				$this->_handleSettingsPage();
				break;
			case 'type':
			default:
				$this->_handleTypesPage();
				break;
		}

		exit;
	}

	/**
	 * Handles chart type selection page.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 */
	private function _handleTypesPage() {
		if ( $_SERVER['REQUEST_METHOD'] == 'POST' && Visualizer_Security::verifyNonce( filter_input( INPUT_POST, 'nonce' ) ) ) {
			$type = filter_input( INPUT_POST, 'type' );
			if ( in_array( $type, Visualizer_Plugin::getChartTypes() ) ) {
				update_post_meta( $this->_chart->ID, Visualizer_Plugin::CF_CHART_TYPE, $type );
				wp_redirect( add_query_arg( 'tab', 'data' ) );
				return;
			}
		}

		$render = new Visualizer_Render_Page_Types();
		$render->type = get_post_meta( $this->_chart->ID, Visualizer_Plugin::CF_CHART_TYPE, true );
		$render->types = Visualizer_Plugin::getChartTypes();
		$render->chart = $this->_chart;
		$render->render();
	}

	/**
	 * Handles chart data page.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 */
	private function _handleDataPage() {
		if ( $_SERVER['REQUEST_METHOD'] == 'POST' && Visualizer_Security::verifyNonce( filter_input( INPUT_POST, 'nonce' ) ) ) {
			wp_redirect( add_query_arg( 'tab', 'settings' ) );
			return;
		}

		$render = new Visualizer_Render_Page_Data();
		$render->chart = $this->_chart;
		$render->render();
	}

	/**
	 * Handles chart settigns page.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 */
	private function _handleSettingsPage() {
		if ( $_SERVER['REQUEST_METHOD'] == 'POST' && Visualizer_Security::verifyNonce( filter_input( INPUT_POST, 'nonce' ) ) ) {
			$render = new Visualizer_Render_Page_Send();
			$render->text = sprintf( '[visualizer id="%d"]', $this->_chart->ID );
			$render->render();
			return;
		}

		$render = new Visualizer_Render_Page_Settings();
		$render->type = get_post_meta( $this->_chart->ID, Visualizer_Plugin::CF_CHART_TYPE, true );
		$render->chart = $this->_chart;
		$render->render();
	}

}