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
	public function setUp() {
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
	 * Test the AJAX response for fetching the database data.
	 */
	public function test_ajax_response_get_query_data_valid_query() {
		$this->_setRole( 'administrator' );

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
		$this->assertObjectHasAttribute( 'success', $response );
		$this->assertObjectHasAttribute( 'data', $response );
		$this->assertTrue( $response->success );
	}

	/**
	 * Test the AJAX response for fetching the database data with invalid query.
	 */
	public function test_ajax_response_get_query_data_invalid_query() {
		$this->_setRole( 'administrator' );

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
		$this->assertObjectHasAttribute( 'success', $response );
		$this->assertObjectHasAttribute( 'data', $response );
		$this->assertEquals( 'Only SELECT queries are allowed', $response->data->msg );
		$this->assertFalse( $response->success );
	}

	/**
	 * Test the AJAX response for fetching the database data with a valid query that uses columns that might get filtered.
	 */
	public function test_ajax_response_get_query_data_valid_query_with_filtered_columns() {
		$this->_setRole( 'administrator' );

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
		$this->assertObjectHasAttribute( 'success', $response );
		$this->assertObjectHasAttribute( 'data', $response );
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
		$this->assertObjectHasAttribute( 'success', $response );
		$this->assertObjectHasAttribute( 'data', $response );
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
		$this->assertObjectHasAttribute( 'success', $response );
		$this->assertObjectHasAttribute( 'data', $response );
		$this->assertEquals( 'Action not allowed for this user.', $response->data->msg );
		$this->assertFalse( $response->success );
	}

	/**
	 * Test the AJAX response for fetching the database data with invalid query.
	 */
	public function test_ajax_response_get_query_data_invalid_query_subquery() {
		$this->_setRole( 'administrator' );

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
		$this->assertObjectHasAttribute( 'success', $response );
		$this->assertObjectHasAttribute( 'data', $response );
		$this->assertEquals( 'Only SELECT queries are allowed', $response->data->msg );
		$this->assertFalse( $response->success );
	}
}
