<?php

class Visualizer_Module_Builder extends Visualizer_Module {

	const NAME = __CLASS__;

	public function __construct( Visualizer_Plugin $plugin ) {
		parent::__construct( $plugin );

		$this->_addAjaxAction( VISUALIZER_ACTION_CREATE_CHART, 'renderChartTypeSelect' );
	}

	public function renderChartTypeSelect() {
		$chart_id = filter_input( INPUT_GET, 'chart', FILTER_VALIDATE_INT );
		if ( !$chart_id || !( $chart = get_post( $chart_id ) ) || $chart->post_type != Visualizer_Plugin::CPT ) {
			$chart_id = wp_insert_post( array(
				'post_type'   => Visualizer_Plugin::CPT,
				'post_title'  => 'Visualization',
				'post_author' => get_current_user_id(),
				'post_status' => 'auto-draft',
			) );

			if ( $chart_id && !is_wp_error( $chart_id ) ) {
				add_post_meta( $chart_id, Visualizer_Module_Chart::CF_CHART_TYPE, 'line' );
			}

			wp_redirect( add_query_arg( 'chart', (int)$chart_id ) );
			exit;
		}

		switch ( filter_input( INPUT_GET, 'tab' ) ) {
			case 'data':
				$render = new Visualizer_Render_Page_Data();
				break;
			case 'settings':
				$render = new Visualizer_Render_Page_Settings();
				$render->type = get_post_meta( $chart_id, Visualizer_Module_Chart::CF_CHART_TYPE, true );
				break;
			case 'type':
			default:
				$render = new Visualizer_Render_Page_Types();
				$render->type = get_post_meta( $chart_id, Visualizer_Module_Chart::CF_CHART_TYPE, true );
				$render->types = apply_filters( VISUALIZER_FILTER_GET_CHART_TYPES, array() );
				break;
		}

		$render->chart = $chart;
		$render->render();

		exit;
	}

}