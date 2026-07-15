<?php
/**
 * WordPress unit test plugin.
 *
 * @package     visualizer
 * @subpackage  Tests
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.10.12
 */

/**
 * Test the AJAX functionality.
 */
class Test_Visualizer_Ajax extends WP_Ajax_UnitTestCase {

	/**
	 * Admin user ID.
	 *
	 * @var int
	 */
	private $admin_user_id;

	/**
	 * Contributor user ID.
	 *
	 * @var int
	 */
	private $contibutor_user_id;

	/**
	 * Subscriber user ID.
	 *
	 * @var int
	 */
	private $subscriber_user_id;

	/**
	 * Set up.
	 */
	public function setUp(): void {
		parent::setUp();
		$this->admin_user_id = $this->factory->user->create(
			array(
				'role' => 'administrator',
			)
		);
		wp_set_current_user( $this->admin_user_id );

		$this->contibutor_user_id = $this->factory->user->create(
			array(
				'role' => 'contributor',
			)
		);

		$this->subscriber_user_id = $this->factory->user->create(
			array(
				'role' => 'subscriber',
			)
		);

	}

	/**
	 * Test that the AI Builder URL import rejects local file paths.
	 */
	public function test_ai_builder_file_url_rejects_local_path() {
		wp_set_current_user( $this->contibutor_user_id );
		$chart_id = $this->factory->post->create(
			array(
				'post_type'   => Visualizer_Plugin::CPT_VISUALIZER,
				'post_status' => 'draft',
				'post_author' => $this->contibutor_user_id,
			)
		);
		$file = wp_tempnam( 'visualizer-local.csv' );
		file_put_contents( $file, "Label,Value\nstring,number\nSecret,42" );

		$_POST = array(
			'chart_id'   => $chart_id,
			'nonce'      => wp_create_nonce( 'visualizer-ai-upload-' . $chart_id ),
			'source_type' => 'file_url',
			'file_url'   => $file,
		);

		try {
			$this->_handleAjax( 'visualizer-ai-upload' );
		} catch ( WPAjaxDieContinueException $e ) {
			// Expected after wp_send_json_error().
		}
		wp_delete_file( $file );

		$response = json_decode( $this->_last_response );
		$this->assertFalse( $response->success );
		$this->assertSame( 'Invalid URL. Please check the URL and try again.', $response->data->message );
	}

	/**
	 * Test that saving chart settings sanitizes the stored settings meta.
	 */
	public function test_edit_chart_save_sanitizes_settings_meta() {
		wp_set_current_user( $this->admin_user_id );
		$chart_id = $this->factory->post->create(
			array(
				'post_type'   => Visualizer_Plugin::CPT_VISUALIZER,
				'post_status' => 'publish',
				'post_author' => $this->admin_user_id,
			)
		);
		add_post_meta( $chart_id, Visualizer_Plugin::CF_CHART_TYPE, 'line' );

		$original_request_method   = isset( $_SERVER['REQUEST_METHOD'] ) ? $_SERVER['REQUEST_METHOD'] : null;
		$_SERVER['REQUEST_METHOD'] = 'POST';
		$_GET = array(
			'chart' => $chart_id,
			'tab'   => 'settings',
			'nonce' => wp_create_nonce(),
		);
		// No literal '<' so the payload survives the wp_strip_all_tags() pass at
		// the top of renderChartPages(); only sanitizeSettings() removes the
		// percent-encoded octets. This fails if the sanitizeSettings() call is
		// dropped from the save path.
		$_POST = array(
			'backend-title' => 'Chart %3Cscript%3E',
		);

		try {
			$this->_handleAjax( Visualizer_Plugin::ACTION_EDIT_CHART );
		} catch ( WPAjaxDieContinueException $e ) {
			// Expected.
		} finally {
			if ( null === $original_request_method ) {
				unset( $_SERVER['REQUEST_METHOD'] );
			} else {
				$_SERVER['REQUEST_METHOD'] = $original_request_method;
			}
		}

		$settings = get_post_meta( $chart_id, Visualizer_Plugin::CF_SETTINGS, true );
		$this->assertSame( 'Chart script', $settings['backend-title'] );
	}

	/**
	 * Test that a user cannot request an upload nonce for another user's chart.
	 */
	public function test_ai_builder_chart_nonce_requires_chart_edit_permission() {
		$chart_id = $this->factory->post->create(
			array(
				'post_type'   => Visualizer_Plugin::CPT_VISUALIZER,
				'post_status' => 'publish',
				'post_author' => $this->admin_user_id,
			)
		);
		wp_set_current_user( $this->contibutor_user_id );

		$_POST = array(
			'chart_id' => $chart_id,
			'nonce'    => wp_create_nonce( 'visualizer-ai-builder' ),
		);

		try {
			$this->_handleAjax( 'visualizer-ai-chart-nonce' );
		} catch ( WPAjaxDieContinueException $e ) {
			// Expected after wp_send_json_error().
		}

		$response = json_decode( $this->_last_response );
		$this->assertFalse( $response->success );
		$this->assertSame( 'Unauthorized.', $response->data->message );
	}

	/**
	 * Test that a user cannot upload data to another user's chart.
	 */
	public function test_ai_builder_upload_requires_chart_edit_permission() {
		$chart_id = $this->factory->post->create(
			array(
				'post_type'   => Visualizer_Plugin::CPT_VISUALIZER,
				'post_status' => 'publish',
				'post_author' => $this->admin_user_id,
			)
		);
		$original_content = get_post_field( 'post_content', $chart_id );
		wp_set_current_user( $this->contibutor_user_id );

		$_POST = array(
			'chart_id'   => $chart_id,
			'nonce'      => wp_create_nonce( 'visualizer-ai-upload-' . $chart_id ),
			'source_type' => 'csv_string',
			'csv_data'   => "Label,Value\nstring,number\nSecret,42",
		);

		try {
			$this->_handleAjax( 'visualizer-ai-upload' );
		} catch ( WPAjaxDieContinueException $e ) {
			// Expected after wp_send_json_error().
		}

		$response = json_decode( $this->_last_response );
		$this->assertFalse( $response->success );
		$this->assertSame( 'Unauthorized.', $response->data->message );
		$this->assertSame( $original_content, get_post_field( 'post_content', $chart_id ) );
	}

	/**
	 * Test the AJAX response for fetching the database data.
	 */
	public function test_ajax_response_get_query_data_valid_query() {
		$this->_setRole( 'administrator' );

		$this->enable_pro();

		$_GET['security'] = wp_create_nonce( Visualizer_Plugin::ACTION_FETCH_DB_DATA . Visualizer_Plugin::VERSION );

		global $wpdb;
		$_POST['params'] = array(
			'query' => 'SELECT * FROM ' . $wpdb->prefix . 'posts LIMIT 1',
			'chart_id' => 1,
		);
		try {
			// Trigger the AJAX action
			$this->_handleAjax( Visualizer_Plugin::ACTION_FETCH_DB_DATA );
		} catch ( WPAjaxDieContinueException $e ) {
			// We expected this, do nothing.
		}

		$response = json_decode( $this->_last_response );
		$this->assertIsObject( $response );
		$this->assertTrue( property_exists( $response, 'success' ) );
		$this->assertTrue( property_exists( $response, 'data' ) );
		$this->assertTrue( $response->success );
	}

	/**
	 * Test the AJAX response for fetching the database data with invalid query.
	 */
	public function test_ajax_response_get_query_data_invalid_query() {
		$this->_setRole( 'administrator' );

		$this->enable_pro();

		$_GET['security'] = wp_create_nonce( Visualizer_Plugin::ACTION_FETCH_DB_DATA . Visualizer_Plugin::VERSION );

		$_POST['params'] = array(
			'query' => "/**/UPDATE wp_options SET option_value='administrator' WHERE option_name='default_role' --",
			'chart_id' => 1,
		);
		try {
			// Trigger the AJAX action
			$this->_handleAjax( Visualizer_Plugin::ACTION_FETCH_DB_DATA );
		} catch ( WPAjaxDieContinueException $e ) {
			// We expected this, do nothing.
		}

		$response = json_decode( $this->_last_response );
		$this->assertIsObject( $response );
		$this->assertTrue( property_exists( $response, 'success' ) );
		$this->assertTrue( property_exists( $response, 'data' ) );
		$this->assertEquals( 'Only SELECT queries are allowed', $response->data->msg );
		$this->assertFalse( $response->success );
	}

	/**
	 * Test the AJAX response for fetching the database data with a valid query that uses columns that might get filtered.
	 */
	public function test_ajax_response_get_query_data_valid_query_with_filtered_columns() {
		$this->_setRole( 'administrator' );

		$this->enable_pro();

		$_GET['security'] = wp_create_nonce( Visualizer_Plugin::ACTION_FETCH_DB_DATA . Visualizer_Plugin::VERSION );

		$_POST['params'] = array(
			'query' => 'select date_create from wp_insert;',
			'chart_id' => 1,
		);
		try {
			// Trigger the AJAX action
			$this->_handleAjax( Visualizer_Plugin::ACTION_FETCH_DB_DATA );
		} catch ( WPAjaxDieContinueException $e ) {
			// We expected this, do nothing.
		}

		$response = json_decode( $this->_last_response );
		$this->assertIsObject( $response );
		$this->assertTrue( property_exists( $response, 'success' ) );
		$this->assertTrue( property_exists( $response, 'data' ) );
		$this->assertFalse( $response->success );
		$this->assertTrue( strpos( $response->data->msg, ".wp_insert' doesn't exist" ) !== false );
	}

	/**
	 * Test the AJAX response for fetching the database data with user capability.
	 */
	public function test_ajax_response_get_query_data_contributor_dissallow() {
		wp_set_current_user( $this->contibutor_user_id );
		$this->_setRole( 'contributor' );

		$_GET['security'] = wp_create_nonce( Visualizer_Plugin::ACTION_FETCH_DB_DATA . Visualizer_Plugin::VERSION );

		$_POST['params'] = array(
			'query' => "/**/UPDATE wp_options SET option_value='administrator' WHERE option_name='default_role' --",
			'chart_id' => 1,
		);
		try {
			// Trigger the AJAX action
			$this->_handleAjax( Visualizer_Plugin::ACTION_FETCH_DB_DATA );
		} catch ( WPAjaxDieContinueException $e ) {
			// We expected this, do nothing.
		}

		$response = json_decode( $this->_last_response );
		$this->assertIsObject( $response );
		$this->assertTrue( property_exists( $response, 'success' ) );
		$this->assertTrue( property_exists( $response, 'data' ) );
		$this->assertEquals( 'Action not allowed for this user.', $response->data->msg );
		$this->assertFalse( $response->success );
	}

	/**
	 * Test the AJAX response for fetching the database data with user capability.
	 */
	public function test_ajax_response_get_query_data_subcriber_dissallow() {
		wp_set_current_user( $this->subscriber_user_id );
		$this->_setRole( 'subscriber' );

		$_GET['security'] = wp_create_nonce( Visualizer_Plugin::ACTION_FETCH_DB_DATA . Visualizer_Plugin::VERSION );

		$_POST['params'] = array(
			'query' => "/**/UPDATE wp_options SET option_value='administrator' WHERE option_name='default_role' --",
			'chart_id' => 1,
		);
		try {
			// Trigger the AJAX action
			$this->_handleAjax( Visualizer_Plugin::ACTION_FETCH_DB_DATA );
		} catch ( WPAjaxDieContinueException $e ) {
			// We expected this, do nothing.
		}

		$response = json_decode( $this->_last_response );
		$this->assertIsObject( $response );
		$this->assertTrue( property_exists( $response, 'success' ) );
		$this->assertTrue( property_exists( $response, 'data' ) );
		$this->assertEquals( 'Action not allowed for this user.', $response->data->msg );
		$this->assertFalse( $response->success );
	}

	/**
	 * Test the AJAX response for fetching the database data with invalid query.
	 */
	public function test_ajax_response_get_query_data_invalid_query_subquery() {
		$this->_setRole( 'administrator' );

		$this->enable_pro();

		$_GET['security'] = wp_create_nonce( Visualizer_Plugin::ACTION_FETCH_DB_DATA . Visualizer_Plugin::VERSION );

		$_POST['params'] = array(
			'query' => "UPDATE wp_options SET option_value = ( SELECT role_name FROM role_configurations WHERE condition = 'specific_condition' LIMIT 1 )WHERE option_name = 'default_role';",
			'chart_id' => 1,
		);
		try {
			// Trigger the AJAX action
			$this->_handleAjax( Visualizer_Plugin::ACTION_FETCH_DB_DATA );
		} catch ( WPAjaxDieContinueException $e ) {
			// We expected this, do nothing.
		}

		$response = json_decode( $this->_last_response );
		$this->assertIsObject( $response );
		$this->assertTrue( property_exists( $response, 'success' ) );
		$this->assertTrue( property_exists( $response, 'data' ) );
		$this->assertEquals( 'Only SELECT queries are allowed', $response->data->msg );
		$this->assertFalse( $response->success );
	}

	/**
	 * Test the AJAX response for fetching the database data with invalid query.
	 */
	public function test_ajax_response_get_query_data_invalid_query_comment() {
		$this->_setRole( 'administrator' );

		$this->enable_pro();

		$_GET['security'] = wp_create_nonce( Visualizer_Plugin::ACTION_FETCH_DB_DATA . Visualizer_Plugin::VERSION );

		$_POST['params'] = array(
			'query' => "/* SELECT */ REPLACE INTO wp_options ( option_name, option_value ) VALUES ( 'default_role', 'contributor' )",
			'chart_id' => 1,
		);
		try {
			// Trigger the AJAX action
			$this->_handleAjax( Visualizer_Plugin::ACTION_FETCH_DB_DATA );
		} catch ( WPAjaxDieContinueException $e ) {
			// We expected this, do nothing.
		}

		$response = json_decode( $this->_last_response );
		$this->assertIsObject( $response );
		$this->assertTrue( property_exists( $response, 'success' ) );
		$this->assertTrue( property_exists( $response, 'data' ) );
		$this->assertEquals( 'Only SELECT queries are allowed', $response->data->msg );
		$this->assertFalse( $response->success );
	}

	/**
	 * Test the query stripping of comments.
	 */
	public function test_sql_comment_strip() {
		$source = new Visualizer_Source_Query( "SELECT * FROM test_table /* WHERE post_type = 'post' */");
		$this->assertEquals( 'SELECT * FROM test_table', $source->get_query() );

		$source = new Visualizer_Source_Query( "SELECT * FROM test_table -- WHERE post_type = 'post'");
		$this->assertEquals( 'SELECT * FROM test_table', $source->get_query() );

		$source = new Visualizer_Source_Query( "/* SELECT */ DELETE * FROM test_table /* WHERE post_type = 'post' */");
		$this->assertEquals( 'DELETE * FROM test_table', $source->get_query() );
	}

	/**
	 * Test Save Query not allowed for subscriber.
	 */
	public function test_sql_save_chart_subscriber() {
		$this->_setRole( 'subscriber' );

		$_GET['security'] = wp_create_nonce( Visualizer_Plugin::ACTION_SAVE_DB_QUERY . Visualizer_Plugin::VERSION );
		$_GET['chart']    = '1';

		$_POST['params'] = array(
			'query' => 'SELECT * FROM wp_posts LIMIT 1',
		);
		try {
			// Trigger the AJAX action
			$this->_handleAjax( Visualizer_Plugin::ACTION_SAVE_DB_QUERY );
		} catch ( WPAjaxDieContinueException $e ) {
			// We expected this, do nothing.
		}

		$response = json_decode( $this->_last_response );
		$this->assertIsObject( $response );
		$this->assertTrue( property_exists( $response, 'success' ) );
		$this->assertTrue( property_exists( $response, 'data' ) );
		$this->assertEquals( 'Action not allowed for this user.', $response->data->msg );
		$this->assertFalse( $response->success );
	}

	/**
	 * Test Save Query not allowed if not pro.
	 */
	public function test_sql_save_chart_admin() {
		wp_set_current_user( $this->admin_user_id );
		$this->_setRole( 'administrator' );

		$_GET['security'] = wp_create_nonce( Visualizer_Plugin::ACTION_SAVE_DB_QUERY . Visualizer_Plugin::VERSION );
		$_GET['chart']    = '1';

		$_POST['params'] = array(
			'query' => 'SELECT * FROM wp_posts LIMIT 1',
		);
		try {
			// Trigger the AJAX action
			$this->_handleAjax( Visualizer_Plugin::ACTION_SAVE_DB_QUERY );
		} catch ( WPAjaxDieContinueException $e ) {
			// We expected this, do nothing.
		}

		$response = json_decode( $this->_last_response );
		$this->assertIsObject( $response );
		$this->assertTrue( property_exists( $response, 'success' ) );
		$this->assertTrue( property_exists( $response, 'data' ) );
		$this->assertEquals( 'Feature is not available.', $response->data->msg );
		$this->assertFalse( $response->success );
	}

	/**
	 * JSON get-roots must be denied for users without `edit_posts` (issue #591 access-control fix).
	 */
	public function test_json_get_roots_denied_for_subscriber() {
		wp_set_current_user( $this->subscriber_user_id );
		$this->_setRole( 'subscriber' );

		$_GET['security'] = wp_create_nonce( Visualizer_Plugin::ACTION_JSON_GET_ROOTS . Visualizer_Plugin::VERSION );
		$_POST['params']  = array(
			'url'    => 'http://127.0.0.1:9999/latest/meta-data/',
			'method' => 'GET',
		);

		try {
			$this->_handleAjax( Visualizer_Plugin::ACTION_JSON_GET_ROOTS );
		} catch ( WPAjaxDieContinueException $e ) {
			// We expected this, do nothing.
		}

		$response = json_decode( $this->_last_response );
		$this->assertIsObject( $response );
		$this->assertFalse( $response->success );
		$this->assertEquals( 'You do not have permission to perform this action.', $response->data->msg );
	}

	/**
	 * JSON get-data must be denied for users without `edit_posts` (issue #591 access-control fix).
	 */
	public function test_json_get_data_denied_for_subscriber() {
		wp_set_current_user( $this->subscriber_user_id );
		$this->_setRole( 'subscriber' );

		$_GET['security'] = wp_create_nonce( Visualizer_Plugin::ACTION_JSON_GET_DATA . Visualizer_Plugin::VERSION );
		$_POST['params']  = array(
			'url'    => 'http://127.0.0.1:9999/',
			'method' => 'GET',
			'chart'  => 1,
		);

		try {
			$this->_handleAjax( Visualizer_Plugin::ACTION_JSON_GET_DATA );
		} catch ( WPAjaxDieContinueException $e ) {
			// We expected this, do nothing.
		}

		$response = json_decode( $this->_last_response );
		$this->assertIsObject( $response );
		$this->assertFalse( $response->success );
		$this->assertEquals( 'You do not have permission to perform this action.', $response->data->msg );
	}

	/**
	 * JSON get-data admits a user who can edit the chart (issue #591 access-control fix).
	 */
	public function test_json_get_data_allows_editor_for_editable_chart() {
		$chart_id = $this->factory->post->create(
			array(
				'post_type'   => Visualizer_Plugin::CPT_VISUALIZER,
				'post_author' => $this->admin_user_id,
			)
		);
		$this->_setRole( 'editor' );

		$filter = function () {
			return array(
				'headers'  => array(),
				'body'     => wp_json_encode( array( array( 'name' => 'a', 'value' => 1 ), array( 'name' => 'b', 'value' => 2 ) ) ),
				'response' => array( 'code' => 200, 'message' => '' ),
				'cookies'  => array(),
				'filename' => null,
			);
		};
		add_filter( 'pre_http_request', $filter );

		$_GET['security'] = wp_create_nonce( Visualizer_Plugin::ACTION_JSON_GET_DATA . Visualizer_Plugin::VERSION );
		$_POST['params']  = array(
			'url'    => 'http://93.184.216.34/data.json',
			'method' => 'GET',
			'chart'  => $chart_id,
			'root'   => 'root',
		);

		try {
			$this->_handleAjax( Visualizer_Plugin::ACTION_JSON_GET_DATA );
		} catch ( WPAjaxDieContinueException $e ) {
			// We expected this, do nothing.
		}
		remove_filter( 'pre_http_request', $filter );

		$response = json_decode( $this->_last_response );
		$this->assertIsObject( $response );
		$this->assertTrue( $response->success );
	}

	/**
	 * JSON get-data dies for a chart the user cannot edit (issue #591 access-control fix).
	 */
	public function test_json_get_data_denied_for_chart_user_cannot_edit() {
		$chart_id = $this->factory->post->create(
			array(
				'post_type'   => Visualizer_Plugin::CPT_VISUALIZER,
				'post_author' => $this->admin_user_id,
			)
		);
		$this->_setRole( 'contributor' );

		$requests = 0;
		$filter   = function ( $preempt ) use ( &$requests ) {
			$requests++;
			return $preempt;
		};
		add_filter( 'pre_http_request', $filter );

		$_GET['security'] = wp_create_nonce( Visualizer_Plugin::ACTION_JSON_GET_DATA . Visualizer_Plugin::VERSION );
		$_POST['params']  = array(
			'url'    => 'http://93.184.216.34/data.json',
			'method' => 'GET',
			'chart'  => $chart_id,
		);

		try {
			$this->_handleAjax( Visualizer_Plugin::ACTION_JSON_GET_DATA );
			$this->fail( 'Expected the request to die for a chart the user cannot edit.' );
		} catch ( WPAjaxDieStopException $e ) {
			$this->assertSame( '', $e->getMessage() );
		}
		remove_filter( 'pre_http_request', $filter );

		$this->assertSame( 0, $requests );
	}

	/**
	 * A contributor may use JSON import, but link-local destinations are blocked before transport.
	 */
	public function test_json_get_roots_blocks_link_local_for_contributor() {
		wp_set_current_user( $this->contibutor_user_id );
		$this->_setRole( 'contributor' );

		$requests = 0;
		$filter   = function ( $preempt ) use ( &$requests ) {
			$requests++;
			return $preempt;
		};
		add_filter( 'pre_http_request', $filter );

		$_GET['security'] = wp_create_nonce( Visualizer_Plugin::ACTION_JSON_GET_ROOTS . Visualizer_Plugin::VERSION );
		$_POST['params']  = array(
			'url'    => 'http://169.254.169.254/latest/meta-data/',
			'method' => 'GET',
		);

		try {
			$this->_handleAjax( Visualizer_Plugin::ACTION_JSON_GET_ROOTS );
		} catch ( WPAjaxDieContinueException $e ) {
			// We expected this, do nothing.
		}
		remove_filter( 'pre_http_request', $filter );

		$response = json_decode( $this->_last_response );
		$this->assertIsObject( $response );
		$this->assertFalse( $response->success );
		$this->assertSame( 0, $requests );
	}

	/**
	 * Chart authorization validates both the object type and meta capability.
	 */
	public function test_chart_authorization_is_object_specific() {
		$own_chart   = $this->create_chart_for_user( $this->contibutor_user_id );
		$other_chart = $this->create_chart_for_user( $this->admin_user_id );
		$regular_post = $this->factory->post->create(
			array(
				'post_author' => $this->contibutor_user_id,
			)
		);
		wp_set_current_user( $this->contibutor_user_id );

		$this->assertTrue( Visualizer_Module::can_edit_chart( $own_chart ) );
		$this->assertFalse( Visualizer_Module::can_edit_chart( $other_chart ) );
		$this->assertFalse( Visualizer_Module::can_edit_chart( $regular_post ) );
		$this->assertFalse( Visualizer_Module::can_edit_chart( PHP_INT_MAX ) );
	}

	/**
	 * The chart editor rejects another user's chart before saving settings.
	 */
	public function test_edit_chart_denied_for_chart_user_cannot_edit() {
		$chart_id = $this->create_chart_for_user( $this->admin_user_id );
		$original = array( 'title' => 'Original' );
		update_post_meta( $chart_id, Visualizer_Plugin::CF_SETTINGS, $original );
		wp_set_current_user( $this->contibutor_user_id );
		$_SERVER['REQUEST_METHOD'] = 'POST';
		$_GET = array(
			'chart' => $chart_id,
			'tab'   => 'settings',
			'nonce' => wp_create_nonce(),
		);
		$_POST = array(
			'title' => 'Overwritten',
			'save'  => 1,
		);

		try {
			$this->_handleAjax( Visualizer_Plugin::ACTION_EDIT_CHART );
			$this->fail( 'Expected the request to die for a chart the user cannot edit.' );
		} catch ( WPAjaxDieStopException $e ) {
			$this->assertStringContainsString( 'permission', $e->getMessage() );
		}
		$_SERVER['REQUEST_METHOD'] = 'GET';

		$this->assertSame( $original, get_post_meta( $chart_id, Visualizer_Plugin::CF_SETTINGS, true ) );
	}

	/**
	 * Chart editors cannot update the site-wide map API key.
	 */
	public function test_edit_chart_map_api_key_requires_manage_options() {
		$chart_id = $this->create_chart_for_user( $this->contibutor_user_id );
		add_post_meta( $chart_id, Visualizer_Plugin::CF_CHART_TYPE, 'line' );
		update_option( 'visualizer-map-api-key', 'original-key' );
		wp_set_current_user( $this->contibutor_user_id );
		$_SERVER['REQUEST_METHOD'] = 'POST';
		$_GET = array(
			'chart' => $chart_id,
			'tab'   => 'settings',
			'nonce' => wp_create_nonce(),
		);
		$_POST = array(
			'map_api_key' => 'attacker-key',
			'save'        => 1,
		);

		try {
			$this->_handleAjax( Visualizer_Plugin::ACTION_EDIT_CHART );
		} catch ( WPAjaxDieContinueException $e ) {
			// Expected when the editor response completes.
		}
		$_SERVER['REQUEST_METHOD'] = 'GET';

		$this->assertSame( 'original-key', get_option( 'visualizer-map-api-key' ) );
	}

	/**
	 * Contributors only receive charts they own from the library endpoint.
	 */
	public function test_get_charts_only_lists_editable_scope_for_contributor() {
		$own_chart   = $this->create_chart_for_user( $this->contibutor_user_id, 'publish' );
		$other_chart = $this->create_chart_for_user( $this->admin_user_id, 'publish' );
		wp_set_current_user( $this->contibutor_user_id );
		$_GET = array(
			'nonce' => wp_create_nonce( Visualizer_Plugin::ACTION_GET_CHARTS ),
		);

		try {
			$this->_handleAjax( Visualizer_Plugin::ACTION_GET_CHARTS );
		} catch ( WPAjaxDieContinueException $e ) {
			// Expected after the JSON response.
		}

		$response  = json_decode( $this->_last_response, true );
		$chart_ids = wp_list_pluck( $response['data'], 'id' );
		$this->assertTrue( $response['success'] );
		$this->assertContains( $own_chart, $chart_ids );
		$this->assertNotContains( $other_chart, $chart_ids );
	}

	/**
	 * A valid nonce does not permit deleting another user's chart.
	 */
	public function test_delete_chart_denied_for_chart_user_cannot_delete() {
		$chart_id = $this->create_chart_for_user( $this->admin_user_id );
		wp_set_current_user( $this->contibutor_user_id );
		$_SERVER['REQUEST_METHOD'] = 'POST';
		$_POST = array(
			'chart' => $chart_id,
			'nonce' => wp_create_nonce(),
		);

		try {
			$this->_handleAjax( Visualizer_Plugin::ACTION_DELETE_CHART );
		} catch ( WPAjaxDieContinueException $e ) {
			// Expected after the JSON response.
		}
		$_SERVER['REQUEST_METHOD'] = 'GET';

		$response = json_decode( $this->_last_response );
		$this->assertFalse( $response->success );
		$this->assertNotNull( get_post( $chart_id ) );
	}

	/**
	 * A valid nonce does not permit cloning another user's chart.
	 */
	public function test_clone_chart_denied_for_chart_user_cannot_edit() {
		$chart_id = $this->create_chart_for_user( $this->admin_user_id );
		wp_set_current_user( $this->contibutor_user_id );
		$_GET = array(
			'chart' => $chart_id,
			'nonce' => wp_create_nonce( Visualizer_Plugin::ACTION_CLONE_CHART ),
		);
		$before = wp_count_posts( Visualizer_Plugin::CPT_VISUALIZER )->draft;

		try {
			$this->_handleAjax( Visualizer_Plugin::ACTION_CLONE_CHART );
			$this->fail( 'Expected the request to die for a chart the user cannot edit.' );
		} catch ( WPAjaxDieStopException $e ) {
			// cloneChart() ends with wp_die() in the test environment when denied.
		} catch ( WPAjaxDieContinueException $e ) {
			// Expected when the handler ends normally.
		}

		$this->assertSame( $before, wp_count_posts( Visualizer_Plugin::CPT_VISUALIZER )->draft );
	}

	/**
	 * A user cannot alter another user's JSON refresh schedule.
	 */
	public function test_json_schedule_denied_for_chart_user_cannot_edit() {
		$chart_id = $this->create_chart_for_user( $this->admin_user_id );
		wp_set_current_user( $this->contibutor_user_id );
		$_POST = array(
			'chart'    => $chart_id,
			'time'     => 12,
			'security' => wp_create_nonce( Visualizer_Plugin::ACTION_JSON_SET_SCHEDULE . Visualizer_Plugin::VERSION ),
		);

		try {
			$this->_handleAjax( Visualizer_Plugin::ACTION_JSON_SET_SCHEDULE );
		} catch ( WPAjaxDieContinueException $e ) {
			// Expected after the JSON response.
		}

		$response = json_decode( $this->_last_response );
		$this->assertFalse( $response->success );
		$this->assertSame( '', get_post_meta( $chart_id, Visualizer_Plugin::CF_JSON_SCHEDULE, true ) );
	}

	/**
	 * A user cannot replace another user's JSON chart data.
	 */
	public function test_json_set_data_denied_for_chart_user_cannot_edit() {
		$chart_id = $this->create_chart_for_user( $this->admin_user_id );
		wp_set_current_user( $this->contibutor_user_id );
		$_GET = array(
			'chart'    => $chart_id,
			'security' => wp_create_nonce( Visualizer_Plugin::ACTION_JSON_SET_DATA . Visualizer_Plugin::VERSION ),
		);
		$_POST = array(
			'url'    => 'https://example.com/data.json',
			'method' => 'GET',
			'root'   => 'items',
		);

		try {
			$this->_handleAjax( Visualizer_Plugin::ACTION_JSON_SET_DATA );
			$this->fail( 'Expected the request to die for a chart the user cannot edit.' );
		} catch ( WPAjaxDieStopException $e ) {
			$this->assertStringContainsString( 'permission', $e->getMessage() );
		}

		$this->assertSame( '', get_post_meta( $chart_id, Visualizer_Plugin::CF_JSON_URL, true ) );
	}

	/**
	 * A user cannot save filters on another user's chart.
	 */
	public function test_save_filter_denied_for_chart_user_cannot_edit() {
		$chart_id = $this->create_chart_for_user( $this->admin_user_id );
		wp_set_current_user( $this->contibutor_user_id );
		$_GET = array(
			'chart'    => $chart_id,
			'security' => wp_create_nonce( Visualizer_Plugin::ACTION_SAVE_FILTER_QUERY . Visualizer_Plugin::VERSION ),
		);

		try {
			$this->_handleAjax( Visualizer_Plugin::ACTION_SAVE_FILTER_QUERY );
		} catch ( WPAjaxDieContinueException $e ) {
			// Expected after the JSON response.
		}

		$response = json_decode( $this->_last_response );
		$this->assertFalse( $response->success );
	}

	/**
	 * Test that the setup wizard import step builds per-row settings from the
	 * decoded sample data, covering the decode_content() call in the wizard.
	 */
	public function test_wizard_import_chart_builds_settings_from_decoded_sample_data() {
		wp_set_current_user( $this->admin_user_id );

		$_POST = array(
			'security'   => wp_create_nonce( VISUALIZER_ABSPATH ),
			'step'       => 'step_2',
			'chart_type' => 'pie',
		);

		try {
			$this->_handleAjax( 'visualizer_wizard_step_process' );
		} catch ( WPAjaxDieContinueException $e ) {
			// We expected this, do nothing.
		}

		// Skip any PHP notices emitted before the JSON payload.
		$response = json_decode( substr( $this->_last_response, (int) strpos( $this->_last_response, '{' ) ) );
		$this->assertSame( 1, $response->success );

		// Data rows in the bundled sample = total lines minus label + type rows.
		$expected_rows = count( array_filter( array_map( 'trim', file( VISUALIZER_ABSPATH . '/samples/pie.csv' ) ) ) ) - 2;
		$settings      = get_post_meta( $response->chart_id, Visualizer_Plugin::CF_SETTINGS, true );

		$this->assertGreaterThan( 0, $expected_rows );
		$this->assertCount( $expected_rows, $settings['series'] );
		$this->assertCount( $expected_rows, $settings['slices'] );
	}

	/**
	 * Creates a chart owned by a specific user.
	 *
	 * @param int    $user_id User ID.
	 * @param string $status Post status.
	 * @return int
	 */
	private function create_chart_for_user( $user_id, $status = 'draft' ) {
		$chart_id = $this->factory->post->create(
			array(
				'post_type'    => Visualizer_Plugin::CPT_VISUALIZER,
				'post_status'  => $status,
				'post_author'  => $user_id,
				'post_content' => wp_slash( serialize( array( array( 'Label' ), array( 'Value' ) ) ) ),
			)
		);
		add_post_meta( $chart_id, Visualizer_Plugin::CF_CHART_TYPE, 'line' );
		add_post_meta(
			$chart_id,
			Visualizer_Plugin::CF_SERIES,
			array(
				array(
					'label' => 'Label',
					'type'  => 'string',
				),
			)
		);

		return $chart_id;
	}

	/**
	 * Utility method to mock pro version.
	 */
	private function enable_pro() {
		add_filter( 'visualizer_is_pro', '__return_true' );
	}
}
