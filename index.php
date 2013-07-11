<?php
/*
Plugin Name: WordPress Visualizer
Plugin URI:
Description: This plugin is easy and powerful tool to create charts.
Version: 1.0
Author: Madpixels
Author URI: http://madpixels.net
*/

// don't load the plugin, if it has been already loaded
if ( class_exists( 'Visualizer_Plugin', false ) ) {
   return;
}

/**
 * Automatically loads classes for the plugin. Checks a namespace and loads only
 * approved classes.
 *
 * @since 1.0.0
 *
 * @param string $class The class name to autoload.
 * @return boolean Returns TRUE if the class is located. Otherwise FALSE.
 */
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

/**
 * Instantiates the plugin and setup all modules.
 *
 * @since 1.0.0
 */
function visualizer_launch() {
	// setup environment
	define( 'VISUALIZER_BASEFILE', __FILE__ );
	define( 'VISUALIZER_ABSURL', plugins_url( '/', __FILE__ ) );
	define( 'VISUALIZER_ABSPATH', dirname( __FILE__ ) );

	// don't load the plugin if cron job is running or doing autosave
	$doing_autosave = defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE;
	$doing_cron = defined( 'DOING_CRON' ) && DOING_CRON;
	$doing_ajax = defined( 'DOING_AJAX' ) && DOING_AJAX;
	if ( $doing_autosave || $doing_cron ) {
		return;
	}

	// instantiate the plugin
	$plugin = Visualizer_Plugin::instance();

	// set general modules
	$plugin->setModule( Visualizer_Module_Setup::NAME );

	if ( $doing_ajax ) {
		// set ajax modules
		$plugin->setModule( Visualizer_Module_Chart::NAME );
	} else {
		if ( is_admin() ) {
			// set admin modules
			$plugin->setModule( Visualizer_Module_Admin::NAME );
		}
	}
}

// register autoloader function
spl_autoload_register( 'visualizer_autoloader' );

// launch the plugin
visualizer_launch();