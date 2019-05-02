<?php

/**
 * Base class for sidebar settigns of ChartJS based charts.
 *
 * @category Visualizer
 * @package Render
 * @subpackage Sidebar
 *
 * @since 3.2.0
 * @abstract
 */
abstract class Visualizer_Render_Sidebar_ChartJS extends Visualizer_Render_Sidebar {

	/**
	 * The constructor.
	 */
	public function __construct( $data = array() ) {
		$this->_library = 'chartjs';
		parent::__construct( $data );
	}

	/**
	 * Registers additional hooks.
	 *
	 * @access protected
	 */
	protected function hooks() {
		if ( $this->_library === 'chartjs' ) {
			add_filter( 'visualizer_assets_render', array( $this, 'load_chartjs_assets' ), 10, 2 );
		}
	}

	/**
	 * Loads the assets.
	 */
	function load_chartjs_assets( $deps, $is_frontend ) {
		wp_register_script( 'chartjs', VISUALIZER_ABSURL . 'js/lib/chartjs.min.js', array(), null, true );
		wp_register_script(
			'visualizer-render-chartjs-lib',
			VISUALIZER_ABSURL . 'js/render-chartjs.js',
			array(
				'chartjs',
			),
			Visualizer_Plugin::VERSION,
			true
		);

		return array_merge(
			$deps,
			array( 'visualizer-render-chartjs-lib' )
		);

	}

	/**
	 * Enqueue assets.
	 */
	public static function enqueue_assets( $deps = array() ) {
		wp_enqueue_script( 'visualizer-google-jsapi-new', '//www.gstatic.com/charts/loader.js', array(), null, true );
		wp_enqueue_script( 'visualizer-google-jsapi-old', '//www.google.com/jsapi', array( 'visualizer-google-jsapi-new' ), null, true );
		wp_enqueue_script( 'visualizer-render-google-lib', VISUALIZER_ABSURL . 'js/render-google.js', array_merge( $deps, array( 'visualizer-google-jsapi-old' ) ), Visualizer_Plugin::VERSION, true );
		return 'visualizer-render-google-lib';
	}


}
