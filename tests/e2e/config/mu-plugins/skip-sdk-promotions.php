<?php
/**
 * Stop the SDK promotions from loading for requests carrying the test header.
 *
 * The promotions preload wp-admin/includes/file.php, which hides missing
 * includes on sites that show no promotions.
 */

if ( defined( 'TI_E2E_TESTING' ) && isset( $_SERVER['HTTP_X_VISUALIZER_E2E_SKIP_PROMOTIONS'] ) ) {
	add_filter( 'themeisle_sdk_ran_promos', '__return_true' );
}
