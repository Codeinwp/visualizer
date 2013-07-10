<?php

class Visualizer_Module_Builder extends Visualizer_Module {

	const NAME = __CLASS__;

	public function __construct( Visualizer_Plugin $plugin ) {
		parent::__construct( $plugin );

		$this->_addAjaxAction( VISUALIZER_ACTION_SELECT_CHART_TYPE, 'renderChartTypeSelect' );
	}

	public function renderChartTypeSelect() {
		$render = new Visualizer_Render_Page_Types();
		$render->render();
		exit;
	}

}