<?php

class Visualizer_Module_Builder extends Visualizer_Module {

	const NAME = __CLASS__;

	public function __construct( Visualizer_Plugin $plugin ) {
		parent::__construct( $plugin );

		$this->_addAjaxAction( VISUALIZER_ACTION_SELECT_CHART_TYPE, 'renderChartTypeSelect' );
	}

	public function renderChartTypeSelect() {
		$render = new Visualizer_Render_Page_Types();
		$render->types = apply_filters( VISUALIZER_FILTER_GET_CHART_TYPES, array() );
		$render->render();
		exit;
	}

}