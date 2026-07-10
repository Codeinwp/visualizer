<?php
/**
 * Enable database-source E2E tests for requests carrying the test cookie.
 */

defined( 'VISUALIZER_PRO_VERSION' ) || define( 'VISUALIZER_PRO_VERSION', '2.0.1' );

if ( ! class_exists( 'Visualizer_Pro' ) ) {
	class Visualizer_Pro {
		const ACTION_FETCH_DATA = 'visualizer-fetch-data';
		const CF_PERMISSIONS    = 'visualizer-permissions';
	}
}

function visualizer_e2e_database_source_enabled() {
	return defined( 'TI_E2E_TESTING' ) && isset( $_COOKIE['visualizer_e2e_database_source'] );
}

add_filter(
	'visualizer_is_pro',
	function ( $enabled ) {
		return visualizer_e2e_database_source_enabled() ? true : $enabled;
	},
	PHP_INT_MAX
);

add_filter(
	'visualizer_pro_upsell_class',
	function ( $class, $feature = '' ) {
		return visualizer_e2e_database_source_enabled() && 'db-query' === $feature ? '' : $class;
	},
	PHP_INT_MAX,
	2
);
