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
	 * Utility method to mock pro version.
	 */
	private function enable_pro() {
		add_filter( 'visualizer_is_pro', '__return_true' );
	}
}
