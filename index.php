<?php
/*
Plugin Name: Visualizer: Charts and Graphs
Plugin URI: https://themeisle.com/plugins/visualizer-charts-and-graphs/
Description: A simple, easy to use and quite powerful tool to create, manage and embed interactive charts into your WordPress posts and pages. The plugin uses Google Visualization API to render charts, which supports cross-browser compatibility (adopting VML for older IE versions) and cross-platform portability to iOS and new Android releases.
Version: 1.5.3
Author: Themeisle
Author URI: http://themeisle.com
License: GPL v2.0 or later
License URI: http://www.opensource.org/licenses/gpl-license.php
*/
 

// +----------------------------------------------------------------------+
// | Copyright 2013  Madpixels  (email : visualizer@madpixels.net)        |
// +----------------------------------------------------------------------+
// | This program is free software; you can redistribute it and/or modify |
// | it under the terms of the GNU General Public License, version 2, as  |
// | published by the Free Software Foundation.                           |
// |                                                                      |
// | This program is distributed in the hope that it will be useful,      |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of       |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the        |
// | GNU General Public License for more details.                         |
// |                                                                      |
// | You should have received a copy of the GNU General Public License    |
// | along with this program; if not, write to the Free Software          |
// | Foundation, Inc., 51 Franklin St, Fifth Floor, Boston,               |
// | MA 02110-1301 USA                                                    |
// +----------------------------------------------------------------------+
// | Author: Eugene Manuilov <eugene@manuilov.org>                        |
// +----------------------------------------------------------------------+

// prevent direct access to the plugin folder
if ( !defined( 'ABSPATH' ) ) {
	header( 'HTTP/1.0 404 Not Found', true, 404 );
	exit;
}

// don't load the plugin, if it has been already loaded
if ( class_exists( 'Visualizer_Plugin', false ) ) {
   return;
}

// Added by Ash/Upwork
if ( class_exists( 'Visualizer_Pro', false ) ){
    define( 'Visualizer_Pro', true);
}
// Added by Ash/Upwork

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

	if ( !defined( 'VISUALIZER_CSV_DELIMITER' ) ) {
		define( 'VISUALIZER_CSV_DELIMITER', ',' );
	}

	if ( !defined( 'VISUALIZER_CSV_ENCLOSURE' ) ) {
		define( 'VISUALIZER_CSV_ENCLOSURE', '"' );
	}

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
	$plugin->setModule( Visualizer_Module_Sources::NAME );

	if ( $doing_ajax ) {
		// set ajax modules
		$plugin->setModule( Visualizer_Module_Chart::NAME );
	} else {
		if ( is_admin() ) {
			// set admin modules
			$plugin->setModule( Visualizer_Module_Admin::NAME );
		} else {
			// set frontend modules
			$plugin->setModule( Visualizer_Module_Frontend::NAME );
		}
	}
}

// register autoloader function
spl_autoload_register( 'visualizer_autoloader' );

// launch the plugin
visualizer_launch();
