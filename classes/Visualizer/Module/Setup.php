<?php

class Visualizer_Module_Setup extends Visualizer_Module {

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
		$this->_addAction( 'init', 'setupCustomPostTypes' );
	}

	/**
	 * Registers custom post type for charts.
	 *
	 * @since 1.0.0
	 * @uses register_post_type() To register custom post type for charts.
	 *
	 * @access public
	 */
	public function setupCustomPostTypes() {
		register_post_type( Visualizer_Plugin::CPT, array(
			'public' => false,
		) );
	}

}