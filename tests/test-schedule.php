<?php
/**
 * Tests for background/scheduled import behavior.
 *
 * @package     visualizer
 * @subpackage  Tests
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/**
 * Scheduled/background refresh must still import with no logged-in user (regression for visualizer-pro#590).
 *
 * ponytail: defines VISUALIZER_DO_NOT_DIE process-wide; isolate this test if one ever needs the exit/die path.
 */
class Test_Visualizer_Schedule extends WP_UnitTestCase {

	/**
	 * Internal upload (no user, VISUALIZER_DO_NOT_DIE set) must still import and persist chart data.
	 */
	public function test_internal_upload_imports_without_logged_in_user() {
		wp_set_current_user( 0 );
		$this->assertFalse( current_user_can( 'edit_posts' ), 'precondition: background context has no editing user' );

		$chart_id = self::factory()->post->create(
			array(
				'post_type'    => Visualizer_Plugin::CPT_VISUALIZER,
				'post_status'  => 'publish',
				'post_content' => 'OLD-CONTENT',
			)
		);

		// mimic Pro's internal invocation; clean superglobals so prior tests don't reroute uploadData().
		$_GET = $_POST = $_FILES = array();
		define( 'VISUALIZER_DO_NOT_DIE', true );
		$_GET['nonce']        = wp_create_nonce( 'visualizer-upload-data' );
		$_GET['chart']        = $chart_id;
		$_POST['editor-type'] = 'text';
		$_POST['chart_data']  = "Name,Value\nstring,number\nAlpha,111\nBeta,222";

		do_action( 'wp_ajax_' . Visualizer_Plugin::ACTION_UPLOAD_DATA );

		$content = get_post_field( 'post_content', $chart_id );
		$this->assertStringContainsString( 'Alpha', $content, 'scheduled/background import must update chart data without a logged-in user' );
	}
}
