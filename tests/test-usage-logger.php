<?php
/**
 * WordPress unit test plugin.
 *
 * @package     visualizer
 * @subpackage  Tests
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/**
 * Test the usage logger fed to the Themeisle SDK.
 *
 * Regression tests for https://github.com/Codeinwp/visualizer/issues/1359 —
 * a published chart whose `visualizer-settings` meta is a string crashed
 * `Visualizer_Module_Setup::getUsage()` with a TypeError on PHP 8, aborting
 * scheduled usage collection.
 */
class Test_Visualizer_Usage_Logger extends WP_UnitTestCase {

	/**
	 * Create a published chart with the given settings meta value.
	 *
	 * @param mixed $settings The value stored in the visualizer-settings meta.
	 * @return int The chart id.
	 */
	private function create_chart( $settings ) {
		$chart_id = self::factory()->post->create(
			array(
				'post_type'    => Visualizer_Plugin::CPT_VISUALIZER,
				'post_status'  => 'publish',
				'post_content' => wp_slash( serialize( array( array( 'Label' ), array( 'Value' ) ) ) ),
			)
		);
		update_post_meta( $chart_id, Visualizer_Plugin::CF_CHART_TYPE, 'line' );
		update_post_meta( $chart_id, Visualizer_Plugin::CF_SETTINGS, $settings );
		return $chart_id;
	}

	/**
	 * A chart whose settings meta is a string must not abort usage collection.
	 */
	public function test_string_settings_meta_does_not_crash_logger() {
		$this->create_chart( 'corrupted string settings' );

		$usage = apply_filters( 'visualizer_logger_data', array() );

		$this->assertIsArray( $usage );
		$this->assertSame( 0, $usage['manual_config'] );
	}

	/**
	 * A chart with no settings meta at all must not abort usage collection.
	 */
	public function test_missing_settings_meta_does_not_crash_logger() {
		$chart_id = $this->create_chart( array() );
		delete_post_meta( $chart_id, Visualizer_Plugin::CF_SETTINGS );

		$usage = apply_filters( 'visualizer_logger_data', array() );

		$this->assertIsArray( $usage );
		$this->assertSame( 0, $usage['manual_config'] );
	}

	/**
	 * On pro, a permission entry that should be an array but is a string
	 * must not abort usage collection with a count() TypeError.
	 */
	public function test_malformed_permissions_meta_does_not_crash_logger() {
		// The stub stays defined for the rest of the PHPUnit process. That only
		// affects code gating on class_exists( 'Visualizer_Pro' ) — the legacy
		// license fallback in proFeaturesEnabled() — which no test exercises.
		if ( ! class_exists( 'Visualizer_Pro' ) ) {
			eval( 'class Visualizer_Pro { const CF_PERMISSIONS = "visualizer-permissions"; }' );
		}

		$chart_id = $this->create_chart( array() );
		update_post_meta(
			$chart_id,
			Visualizer_Pro::CF_PERMISSIONS,
			array( 'permissions' => array( 'edit-specific' => 'administrator' ) )
		);

		add_filter( 'visualizer_is_pro', '__return_true' );
		$usage = apply_filters( 'visualizer_logger_data', array() );
		remove_filter( 'visualizer_is_pro', '__return_true' );

		$this->assertIsArray( $usage );
		$this->assertSame( 0, $usage['permissions'] );
	}

	/**
	 * Valid array settings still count manual configurations.
	 */
	public function test_manual_config_still_counted_for_array_settings() {
		$this->create_chart( array( 'manual' => '{"colors": ["#000"]}' ) );
		$this->create_chart( 'corrupted string settings' );

		$usage = apply_filters( 'visualizer_logger_data', array() );

		$this->assertSame( 1, $usage['manual_config'] );
		$this->assertSame( 2, $usage['types']['line'] );
	}
}
