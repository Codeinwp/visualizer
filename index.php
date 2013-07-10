<?php
/*
Plugin Name: WordPress Visualizer
Plugin URI:
Description: This plugin is easy and powerful tool to create charts.
Version: 1.0
Author: Madpixels
Author URI: http://madpixels.net
*/

if ( class_exists( 'Visualizer_Plugin', false ) ) {
   return;
}

function visualizer_autoloader( $class ) {
	$namespaces = array( 'Visualizer' );
	foreach ( $namespaces as $namespace ) {
		if ( substr( $class, 0, strlen( $namespace ) ) == $namespace ) {
			require dirname( __FILE__ ) . str_replace( '_', DIRECTORY_SEPARATOR, "_classes_{$class}.php" );
			return true;
		}
	}

	return false;
}

function visualizer_launch() {
	define( 'VISUALIZER_BASEFILE', __FILE__ );
	define( 'VISUALIZER_ABSURL', plugins_url( '/', __FILE__ ) );
	define( 'VISUALIZER_ABSPATH', dirname( __FILE__ ) );

	define( 'VISUALIZER_ACTION_GET_CHARTS',   'visualizer-get-charts' );
	define( 'VISUALIZER_ACTION_CREATE_CHART', 'visualizer-create-chart' );
	define( 'VISUALIZER_ACTION_DELETE_CHART', 'visualizer-delete-chart' );

	define( 'VISUALIZER_FILTER_GET_CHART_TYPES', 'visualizer-get-chart-types' );

	$doing_autosave = defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE;
	$doing_cron = defined( 'DOING_CRON' ) && DOING_CRON;
	$doing_ajax = defined( 'DOING_AJAX' ) && DOING_AJAX;
	if ( $doing_autosave || $doing_cron ) {
		return;
	}

	$plugin = Visualizer_Plugin::instance();

	$plugin->setModule( Visualizer_Module_Setup::NAME );

	if ( $doing_ajax ) {
		$plugin->setModule( Visualizer_Module_Chart::NAME );
		$plugin->setModule( Visualizer_Module_Builder::NAME );
	} else {
		if ( is_admin() ) {
			$plugin->setModule( Visualizer_Module_Chart::NAME );
			$plugin->setModule( Visualizer_Module_Admin::NAME );
		}
	}
}

spl_autoload_register( 'visualizer_autoloader' );

visualizer_launch();