<?php
/**
 * WordPress unit test plugin.
 *
 * @package     visualizer
 * @subpackage  Tests
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.10.12
 */

class Test_Visualizer_Ajax extends WP_Ajax_UnitTestCase {

	private $admin_user_id;

	public function setUp() :void {
		parent::setUp();
		$this->admin_user_id = $this->factory->user->create(
			array(
				'role' => 'administrator',
			)
		);
		wp_set_current_user( $this->admin_user_id );

	}

	public function test_ajax_response_get_query_data_valid_query() {
		$this->_setRole( 'administrator' );

		$_GET['security'] = wp_create_nonce( Visualizer_Plugin::ACTION_FETCH_DB_DATA . Visualizer_Plugin::VERSION );

		$_POST['params'] = array(
			'query' => 'SELECT * FROM wp_posts',
		);
		try {
			// Trigger the AJAX action
			$this->_handleAjax( Visualizer_Plugin::ACTION_FETCH_DB_DATA );
		} catch (WPAjaxDieContinueException $e) {
			// We expected this, do nothing.
		}

		$response = json_decode( $this->_last_response );
		$this->assertIsObject( $response );
		$this->assertObjectHasAttribute( 'success', $response );
		$this->assertObjectHasAttribute( 'data', $response );
		$this->assertTrue( $response->success );
	}

	public function test_ajax_response_get_query_data_invalid_query() {
		$this->_setRole( 'administrator' );

		$_GET['security'] = wp_create_nonce( Visualizer_Plugin::ACTION_FETCH_DB_DATA . Visualizer_Plugin::VERSION );

		$_POST['params'] = array(
			'query' => "/**/UPDATE wp_options SET option_value='administrator' WHERE option_name='default_role' --",
		);
		try {
			// Trigger the AJAX action
			$this->_handleAjax( Visualizer_Plugin::ACTION_FETCH_DB_DATA );
		} catch (WPAjaxDieContinueException $e) {
			// We expected this, do nothing.
		}

		$response = json_decode( $this->_last_response );
		$this->assertIsObject( $response );
		$this->assertObjectHasAttribute( 'success', $response );
		$this->assertObjectHasAttribute( 'data', $response );
		$this->assertEquals( 'Only SELECT queries are allowed', $response->data->msg );
		$this->assertFalse( $response->success );
	}

	public function test_ajax_response_get_query_data_subcriber_dissallow() {
		$this->_setRole( 'subscriber' );

		$_GET['security'] = wp_create_nonce( Visualizer_Plugin::ACTION_FETCH_DB_DATA . Visualizer_Plugin::VERSION );

		$_POST['params'] = array(
			'query' => "/**/UPDATE wp_options SET option_value='administrator' WHERE option_name='default_role' --",
		);
		try {
			// Trigger the AJAX action
			$this->_handleAjax( Visualizer_Plugin::ACTION_FETCH_DB_DATA );
		} catch (WPAjaxDieContinueException $e) {
			// We expected this, do nothing.
		}

		$response = json_decode( $this->_last_response );
		$this->assertIsObject( $response );
		$this->assertObjectHasAttribute( 'success', $response );
		$this->assertObjectHasAttribute( 'data', $response );
		$this->assertEquals( 'Action not allowed for this user.', $response->data->msg );
		$this->assertFalse( $response->success );
	}
}
