<?php

/*
	Plugin Name: Visualizer: Charts and Graphs Lite
	Plugin URI: https://themeisle.com/plugins/visualizer-charts-and-graphs-lite/
	Description: A simple, easy to use and quite powerful tool to create, manage and embed interactive charts into your WordPress posts and pages. The plugin uses Google Visualization API to render charts, which supports cross-browser compatibility (adopting VML for older IE versions) and cross-platform portability to iOS and new Android releases.
	Version: 2.1.7
	Author: Themeisle
	Author URI: http://themeisle.com
	License: GPL v2.0 or later
    WordPress Available:  yes
    Requires License:    no
    Pro Slug:    visualizer-pro
	License URI: http://www.opensource.org/licenses/gpl-license.php
*/

// Prevent direct access to the plugin folder.
if ( ! defined( 'ABSPATH' ) ) {
	header( 'HTTP/1.0 404 Not Found', true, 404 );
	exit;
}
// don't load the plugin, if it has been already loaded
if ( class_exists( 'Visualizer_Plugin', false ) ) {
	return;
}
// Added by Ash/Upwork
if ( class_exists( 'Visualizer_Pro', false ) ) {
	define( 'VISUALIZER_PRO', true );
} else {
	defined( 'VISUALIZER_PRO' ) || define( 'VISUALIZER_PRO', false );
}
// Added by Ash/Upwork
/**
 * Automatically loads classes for the plugin. Checks a namespace and loads only
 * approved classes.
 *
 * @since 1.0.0
 *
 * @param string $class The class name to autoload.
 *
 * @return boolean Returns TRUE if the class is located. Otherwise FALSE.
 */
function visualizer_autoloader( $class ) {
	$namespaces = array( 'Visualizer' );
	foreach ( $namespaces as $namespace ) {
		if ( substr( $class, 0, strlen( $namespace ) ) == $namespace ) {
			$filename = dirname( __FILE__ ) . str_replace( '_', DIRECTORY_SEPARATOR, "_classes_{$class}.php" );
			if ( is_readable( $filename ) ) {
				require $filename;

				return true;
			}
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
	if ( ! defined( 'VISUALIZER_CSV_DELIMITER' ) ) {
		define( 'VISUALIZER_CSV_DELIMITER', ',' );
	}
	if ( ! defined( 'VISUALIZER_CSV_ENCLOSURE' ) ) {
		define( 'VISUALIZER_CSV_ENCLOSURE', '"' );
	}
	// instantiate the plugin
	$plugin = Visualizer_Plugin::instance();
	// set general modules
	$plugin->setModule( Visualizer_Module_Setup::NAME );
	$plugin->setModule( Visualizer_Module_Sources::NAME );
	$plugin->setModule( Visualizer_Module_Chart::NAME );
	if ( is_admin() ) {
		// set admin modules
		$plugin->setModule( Visualizer_Module_Admin::NAME );
	} else {
		// set frontend modules
		$plugin->setModule( Visualizer_Module_Frontend::NAME );
	}
	$vendor_file = VISUALIZER_ABSPATH . '/vendor/autoload_52.php';
	if ( is_readable( $vendor_file ) ) {
		include_once( $vendor_file );
	}
	add_filter( 'themeisle_sdk_products', function ( $products ) {
		$products[] = VISUALIZER_BASEFILE;

		return $products;
	} );
}

// register autoloader function
spl_autoload_register( 'visualizer_autoloader' );
// launch the plugin
visualizer_launch();
