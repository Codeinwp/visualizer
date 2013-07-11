<?php

class Visualizer_Module_Chart extends Visualizer_Module {

	const NAME = __CLASS__;

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

}