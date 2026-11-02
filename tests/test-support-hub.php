<?php
/**
 * WordPress unit test plugin.
 *
 * @package     visualizer
 * @subpackage  Tests
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/**
 * Test the metadata Visualizer hands to the Themeisle SDK Support Hub.
 */
class Test_Visualizer_Support_Hub extends WP_UnitTestCase {

	/**
	 * The filter is registered and returns the keys the SDK reads.
	 */
	public function test_metadata_has_the_contract_keys() {
		$this->assertNotFalse( has_filter( 'visualizer_support_hub_metadata', 'visualizer_support_hub_metadata' ) );

		$data = apply_filters( 'visualizer_support_hub_metadata', array() );

		$this->assertIsArray( $data );
		$this->assertArrayHasKey( 'is_pro', $data );
		$this->assertIsBool( $data['is_pro'] );
		$this->assertArrayHasKey( 'setup_complete', $data );
		$this->assertIsCallable( $data['setup_complete'] );
		$this->assertArrayNotHasKey( 'pages', $data, 'Screens are declared through themeisle_internal_page, not pages.' );
		$this->assertArrayNotHasKey( 'primary_color', $data, 'The hub keeps the shared SDK accent.' );
	}

	/**
	 * Every footer link has an icon, a text and a URL.
	 */
	public function test_links_are_complete() {
		$data = apply_filters( 'visualizer_support_hub_metadata', array() );

		$this->assertNotEmpty( $data['links'] );
		$this->assertLessThanOrEqual( 6, count( $data['links'] ) );
		foreach ( $data['links'] as $link ) {
			$this->assertNotEmpty( $link['icon'] );
			$this->assertNotEmpty( $link['text'] );
			$this->assertNotFalse( filter_var( $link['url'], FILTER_VALIDATE_URL ) );
		}
		$this->assertSame( VISUALIZER_MAIN_DOC, $data['links'][0]['url'] );
	}

	/**
	 * `is_pro` follows the `visualizer_is_pro` filter Pro answers from its license.
	 */
	public function test_is_pro_follows_the_license_filter() {
		$this->assertFalse( apply_filters( 'visualizer_support_hub_metadata', array() )['is_pro'] );

		add_filter( 'visualizer_is_pro', '__return_true' );
		$this->assertTrue( apply_filters( 'visualizer_support_hub_metadata', array() )['is_pro'] );
		remove_filter( 'visualizer_is_pro', '__return_true' );
	}

	/**
	 * Setup counts as complete once the site has one chart, in any live status.
	 */
	public function test_setup_complete_needs_one_chart() {
		$this->assertFalse( visualizer_support_hub_setup_complete() );

		$chart_id = self::factory()->post->create(
			array(
				'post_type'   => Visualizer_Plugin::CPT_VISUALIZER,
				'post_status' => 'draft',
			)
		);
		$this->assertTrue( visualizer_support_hub_setup_complete() );

		wp_trash_post( $chart_id );
		$this->assertFalse( visualizer_support_hub_setup_complete(), 'A trashed chart does not count.' );
	}

	/**
	 * Auto-drafts (the editor's placeholder posts) do not count as a chart.
	 */
	public function test_setup_complete_ignores_auto_drafts() {
		self::factory()->post->create(
			array(
				'post_type'   => Visualizer_Plugin::CPT_VISUALIZER,
				'post_status' => 'auto-draft',
			)
		);
		$this->assertFalse( visualizer_support_hub_setup_complete() );

		self::factory()->post->create(
			array(
				'post_type'   => 'post',
				'post_status' => 'publish',
			)
		);
		$this->assertFalse( visualizer_support_hub_setup_complete(), 'Other post types do not count.' );
	}

	/**
	 * The Settings screen declares itself a product screen like the Library and Support screens.
	 */
	public function test_settings_screen_fires_internal_page() {
		wp_set_current_user( 1 );
		set_current_screen( 'dashboard' );

		$admin = Visualizer_Plugin::instance()->getModule( Visualizer_Module_Admin::NAME );
		$admin->registerAdminMenu();

		$fired = array();
		add_action(
			'themeisle_internal_page',
			function ( $product, $page ) use ( &$fired ) {
				$fired[] = array( $product, $page );
			},
			10,
			2
		);

		// The suffix add_submenu_page() returned, whatever menu state earlier tests left behind.
		$admin->enqueueSettingsScripts( get_plugin_page_hookname( 'viz-settings', Visualizer_Plugin::NAME ) );
		$admin->enqueueSettingsScripts( 'edit-post' );

		$this->assertSame( array( array( VISUALIZER_DIRNAME, 'settings' ) ), $fired );
	}
}
