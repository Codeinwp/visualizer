<?php

/*
	Plugin Name: Visualizer: Tables and Charts Manager for WordPress
	Plugin URI: https://themeisle.com/plugins/visualizer-charts-and-graphs-lite/
	Description: A simple, easy to use and quite powerful tool to create, manage and embed interactive charts into your WordPress posts and pages. The plugin uses Google Visualization API to render charts, which supports cross-browser compatibility (adopting VML for older IE versions) and cross-platform portability to iOS and new Android releases.
	Version: 3.2.1
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
if ( class_exists( 'Visualizer_Pro', false ) ) {
	define( 'VISUALIZER_PRO', true );
} else {
	defined( 'VISUALIZER_PRO' ) || define( 'VISUALIZER_PRO', false );
}

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
		if ( substr( $class, 0, strlen( $namespace ) ) === $namespace ) {
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
	define( 'VISUALIZER_BASENAME', plugin_basename( __FILE__ ) );
	define( 'VISUALIZER_ABSURL', plugins_url( '/', __FILE__ ) );
	define( 'VISUALIZER_ABSPATH', dirname( __FILE__ ) );
	define( 'VISUALIZER_REST_VERSION', 1 );
	// if the below is true, then the js/customization.js in the plugin folder will be used instead of the one in the uploads folder (if it exists).
	define( 'VISUALIZER_TEST_JS_CUSTOMIZATION', false );

	if ( ! defined( 'VISUALIZER_CSV_DELIMITER' ) ) {
		define( 'VISUALIZER_CSV_DELIMITER', ',' );
	}
	if ( ! defined( 'VISUALIZER_CSV_ENCLOSURE' ) ) {
		define( 'VISUALIZER_CSV_ENCLOSURE', '"' );
	}
	if ( ! defined( 'VISUALIZER_DEBUG' ) ) {
		define( 'VISUALIZER_DEBUG', false );
	}

	define( 'VISUALIZER_SKIP_CHART_TYPE_PAGE', true );

	// if x and y features are required, this value should read x,y or x|y or x;y.
	define( 'VISUALIZER_ENABLE_BETA_FEATURES', '' );

	// the link to pre-build queries.
	define( 'VISUALIZER_DB_QUERY_DOC_URL', 'https://docs.themeisle.com/article/970-visualizer-sample-queries-to-generate-charts' );

	// instantiate the plugin
	$plugin = Visualizer_Plugin::instance();

	// instantiate Gutenberg block
	add_action(
		'plugins_loaded', function () {
			if ( function_exists( 'register_block_type' ) ) {
				Visualizer_Gutenberg_Block::get_instance();
			}}
	);

	// set general modules
	$plugin->setModule( Visualizer_Module_Setup::NAME );
	$plugin->setModule( Visualizer_Module_Sources::NAME );
	$plugin->setModule( Visualizer_Module_Chart::NAME );

	if ( is_admin() || defined( 'WP_TESTS_DOMAIN' ) ) {
		// set admin modules
		$plugin->setModule( Visualizer_Module_Admin::NAME );
	}

	// set frontend modules
	$plugin->setModule( Visualizer_Module_Frontend::NAME );

	$plugin->setModule( Visualizer_Module_AMP::NAME );

	$vendor_file = VISUALIZER_ABSPATH . '/vendor/autoload_52.php';
	if ( is_readable( $vendor_file ) ) {
		include_once( $vendor_file );
	}
	add_filter( 'themeisle_sdk_products', 'visualizer_register_sdk', 10, 1 );
	add_filter( 'pirate_parrot_log', 'visualizer_register_parrot', 10, 1 );
}

/**
 * Registers with the SDK
 *
 * @since    1.0.0
 */
function visualizer_register_sdk( $products ) {
	$products[] = VISUALIZER_BASEFILE;
	return $products;
}

/**
 * Registers with the parrot plugin
 *
 * @since    1.0.0
 */
function visualizer_register_parrot( $plugins ) {
	$plugins[] = Visualizer_Plugin::NAME;
	return $plugins;
}

// register autoloader function
spl_autoload_register( 'visualizer_autoloader' );
// launch the plugin
visualizer_launch();
