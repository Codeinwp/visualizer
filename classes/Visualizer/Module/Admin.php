<?php

class Visualizer_Module_Admin extends Visualizer_Module {

	const NAME = __CLASS__;

	public function __construct( Visualizer_Plugin $plugin ) {
		parent::__construct( $plugin );

		$this->_addAction( 'load-post.php', 'enqueueScripts' );
		$this->_addAction( 'load-post-new.php', 'enqueueScripts' );
		$this->_addAction( 'admin_footer', 'renderTempaltes' );

		$this->_addFilter( 'media_view_strings', 'setupMediaViewStrings' );
	}

	public function enqueueScripts() {
		wp_enqueue_style( 'visualizer-media', VISUALIZER_ABSURL . 'css/media.css', array( 'media-views' ), Visualizer_Plugin::VERSION );

		wp_enqueue_script( 'google-jsapi',               '//www.google.com/jsapi',                      array( 'media-editor' ),                null );
		wp_enqueue_script( 'visualizer-media-model',      VISUALIZER_ABSURL . 'js/media/model.js',      array( 'google-jsapi' ),                Visualizer_Plugin::VERSION );
		wp_enqueue_script( 'visualizer-media-collection', VISUALIZER_ABSURL . 'js/media/collection.js', array( 'visualizer-media-model' ),      Visualizer_Plugin::VERSION );
		wp_enqueue_script( 'visualizer-media-controller', VISUALIZER_ABSURL . 'js/media/controller.js', array( 'visualizer-media-collection' ), Visualizer_Plugin::VERSION );
		wp_enqueue_script( 'visualizer-media-view',       VISUALIZER_ABSURL . 'js/media/view.js',       array( 'visualizer-media-controller' ), Visualizer_Plugin::VERSION );
		wp_enqueue_script( 'visualizer-media-toolbar',    VISUALIZER_ABSURL . 'js/media/toolbar.js',    array( 'visualizer-media-view' ),       Visualizer_Plugin::VERSION );
		wp_enqueue_script( 'visualizer-media',            VISUALIZER_ABSURL . 'js/media.js',            array( 'visualizer-media-toolbar' ),    Visualizer_Plugin::VERSION );
	}

	public function setupMediaViewStrings( $strings ) {
		$strings['visualizer'] = array(
			'actions' => array(
				'get_charts'   => VISUALIZER_ACTION_GET_CHARTS,
				'delete_chart' => VISUALIZER_ACTION_DELETE_CHART,
			),
			'controller' => array(
				'title' => __( 'Visualizations', Visualizer_Plugin::NAME ),
			),
			'routers' => array(
				'library' => __( 'From Library', Visualizer_Plugin::NAME ),
				'create'  => __( 'Create New', Visualizer_Plugin::NAME ),
			),
			'library' => array(
				'filters' => array(
					'all'         => __( 'All', Visualizer_Plugin::NAME ),
					'pie'         => __( 'Pie', Visualizer_Plugin::NAME ),
					'line'        => __( 'Line', Visualizer_Plugin::NAME ),
					'area'        => __( 'Area', Visualizer_Plugin::NAME ),
					'geo'         => __( 'Geo', Visualizer_Plugin::NAME ),
					'bar'         => __( 'Bar', Visualizer_Plugin::NAME ),
					'column'      => __( 'Column', Visualizer_Plugin::NAME ),
					'gauge'       => __( 'Gauge', Visualizer_Plugin::NAME ),
					'scatter'     => __( 'Scatter', Visualizer_Plugin::NAME ),
					'candlestick' => __( 'Candelstick', Visualizer_Plugin::NAME ),
				),
			),
			'button' => array(
				'selecttype' => __( 'Select Chart Type', Visualizer_Plugin::NAME ),
				'create'     => __( 'Create And Insert', Visualizer_Plugin::NAME ),
			),
			'nonce'    => Visualizer_Security::createNonce(),
			'buildurl' => add_query_arg( 'action', VISUALIZER_ACTION_CREATE_CHART, admin_url( 'admin-ajax.php' ) ),
		);

		return $strings;
	}

	public function renderTempaltes() {
		global $pagenow;

		if ( 'post.php' != $pagenow && 'post-new.php' != $pagenow ) {
			return;
		}

		$render = new Visualizer_Render_Templates();
		$render->render();
	}

}