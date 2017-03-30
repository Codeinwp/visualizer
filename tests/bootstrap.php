<?php
/**
 * PHPUnit bootstrap file
 *
 * @package Visualizer
 */
$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
	$_tests_dir = '/tmp/wordpress-tests-lib';
}
/**
 * The path to the main file of the plugin to test.
 */
define( 'WP_USE_THEMES', false );
define( 'WP_TESTS_FORCE_KNOWN_BUGS', true );
// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';
/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin() {
	require dirname( dirname( __FILE__ ) ) . '/index.php';
	_pro_exists( false );
}

/**
 * Load pro if it exists
 */
function _pro_exists( $only_check = true ) {
	$pro    = dirname( dirname( dirname( __FILE__ ) ) ) . '/visualizer-pro/visualizer-pro.php';
	if ( file_exists( $pro ) ) {
		if ( ! $only_check ) {
		    require $pro;
		}
		return true;
	}
	return false;
}

tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );
// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';
if ( _pro_exists() ) {
	activate_plugin( 'visualizer-pro/visualizer-pro.php' );
}
activate_plugin( 'visualizer/index.php' );
global $current_user;
$current_user = new WP_User( 1 );
$current_user->set_role( 'administrator' );
wp_update_user( array( 'ID' => 1, 'first_name' => 'Admin', 'last_name' => 'User' ) );
