<?php
/**
 * WordPress unit test plugin.
 *
 * @package     visualizer
 * @subpackage  Tests
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/**
 * Test the capability check on the chart_data REST field callback.
 */
class Test_Visualizer_Chart_Data_Permissions extends WP_UnitTestCase {

	/**
	 * Create a chart owned by the given user.
	 *
	 * @param int $author_id The chart author.
	 * @return int The chart id.
	 */
	private function create_chart( $author_id ) {
		$chart_id = self::factory()->post->create(
			array(
				'post_type'    => Visualizer_Plugin::CPT_VISUALIZER,
				'post_status'  => 'publish',
				'post_author'  => $author_id,
				'post_content' => wp_slash( serialize( array( array( 'Label' ), array( 'Value' ) ) ) ),
			)
		);
		update_post_meta( $chart_id, Visualizer_Plugin::CF_CHART_TYPE, 'line' );
		update_post_meta( $chart_id, Visualizer_Plugin::CF_SETTINGS, array() );
		update_post_meta( $chart_id, Visualizer_Plugin::CF_SERIES, array( array( 'label' => 'Label', 'type' => 'string' ) ) );
		return $chart_id;
	}

	/**
	 * A contributor must not read another user's chart configuration.
	 */
	public function test_contributor_cannot_read_others_chart_data() {
		$author_id = self::factory()->user->create( array( 'role' => 'editor' ) );
		$chart_id  = $this->create_chart( $author_id );

		wp_set_current_user( self::factory()->user->create( array( 'role' => 'contributor' ) ) );

		$result = Visualizer_Gutenberg_Block::get_instance()->get_visualizer_data( array( 'id' => $chart_id ) );
		$this->assertFalse( $result );
	}

	/**
	 * The chart author can still read their own chart configuration.
	 *
	 * Charts are always published on save, so a Contributor author must pass
	 * the check even without the edit_published_posts capability.
	 */
	public function test_contributor_author_can_read_own_chart_data() {
		$author_id = self::factory()->user->create( array( 'role' => 'contributor' ) );
		$chart_id  = $this->create_chart( $author_id );

		wp_set_current_user( $author_id );

		$result = Visualizer_Gutenberg_Block::get_instance()->get_visualizer_data( array( 'id' => $chart_id ) );
		$this->assertIsArray( $result );
	}

	/**
	 * An editor can read any chart configuration.
	 */
	public function test_editor_can_read_others_chart_data() {
		$chart_id = $this->create_chart( self::factory()->user->create( array( 'role' => 'contributor' ) ) );

		wp_set_current_user( self::factory()->user->create( array( 'role' => 'editor' ) ) );

		$result = Visualizer_Gutenberg_Block::get_instance()->get_visualizer_data( array( 'id' => $chart_id ) );
		$this->assertIsArray( $result );
	}
}
