<?php

class Visualizer_Module_Setup extends Visualizer_Module {

	const NAME = __CLASS__;

	public function __construct( Visualizer_Plugin $plugin ) {
		parent::__construct( $plugin );
		$this->_addAction( 'init', 'setupCustomPostTypes' );
	}

	public function setupCustomPostTypes() {
		register_post_type( Visualizer_Plugin::CPT, array(
			'public' => false,
		) );
	}

}