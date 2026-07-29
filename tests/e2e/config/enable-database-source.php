<?php
/**
 * Enable database-source E2E tests for requests carrying the test cookie.
 */

if ( defined( 'TI_E2E_TESTING' ) && isset( $_COOKIE['visualizer_e2e_database_source'] ) ) {
	defined( 'VISUALIZER_PRO_VERSION' ) || define( 'VISUALIZER_PRO_VERSION', '2.0.1' );

	if ( ! class_exists( 'Visualizer_Pro' ) ) {
		class Visualizer_Pro {
			const ACTION_FETCH_DATA = 'visualizer-fetch-data';
			const CF_PERMISSIONS    = 'visualizer-permissions';
		}
	}

	add_filter( 'visualizer_is_pro', '__return_true', PHP_INT_MAX );

	add_filter(
		'visualizer_pro_upsell_class',
		function ( $class, $feature = '' ) {
			return 'db-query' === $feature ? '' : $class;
		},
		PHP_INT_MAX,
		2
	);
}
