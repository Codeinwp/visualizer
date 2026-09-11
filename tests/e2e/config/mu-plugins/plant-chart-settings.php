<?php
/**
 * E2E test helper (loaded as an mu-plugin via .wp-env.json).
 *
 * Lets specs plant chart data/settings meta directly, e.g. a stored-XSS
 * payload in `customcss` that UI save flows strip on submission, so
 * render-time sanitization can be verified.
 */
add_action(
	'rest_api_init',
	function () {
		register_rest_route(
			'visualizer-e2e/v1',
			'/chart-settings/(?P<id>\d+)',
			array(
				'methods'             => 'POST',
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
				'callback'            => function ( WP_REST_Request $request ) {
					$chart_id = (int) $request['id'];
					$body     = $request->get_json_params();
					if ( isset( $body['settings'] ) ) {
						update_post_meta( $chart_id, 'visualizer-settings', $body['settings'] );
					}
					if ( isset( $body['series'] ) ) {
						update_post_meta( $chart_id, 'visualizer-series', $body['series'] );
					}
					if ( isset( $body['content'] ) ) {
						wp_update_post(
							array(
								'ID'           => $chart_id,
								'post_content' => maybe_serialize( $body['content'] ),
							)
						);
					}
					return array( 'ok' => true );
				},
			)
		);

		// Runs the SDK usage logger on demand, so specs can verify it
		// tolerates whatever chart meta they planted (issue #1359).
		register_rest_route(
			'visualizer-e2e/v1',
			'/usage',
			array(
				'methods'             => 'GET',
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
				'callback'            => function () {
					return apply_filters( 'visualizer_logger_data', array() );
				},
			)
		);
	}
);
