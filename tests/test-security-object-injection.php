<?php
/**
 * Regression test for PHP Object Injection via unsafe unserialize().
 *
 * Chart post_content is unserialized in several places. With the
 * array( 'allowed_classes' => false ) guard, a serialized object in the
 * content must NOT be instantiated. The canary below flips a static flag
 * from its __wakeup(); if the guard is removed, unserialize() instantiates
 * the canary, __wakeup() fires, and the assertion fails.
 *
 * @package     Visualizer
 * @subpackage  Tests
 */

if ( ! class_exists( 'Visualizer_POI_Canary' ) ) {
	/**
	 * Canary "gadget": records whether it was ever instantiated by unserialize().
	 */
	class Visualizer_POI_Canary {
		/**
		 * Flag to track whether the canary was instantiated.
		 *
		 * @var bool
		 */
		public static $awoke = false;

		/**
		 * Records that this object was instantiated by unserialize().
		 */
		public function __wakeup() {
			self::$awoke = true;
		}
	}
}

/**
 * Security tests for object injection vulnerabilities.
 */
class Test_Security_Object_Injection extends WP_UnitTestCase {

	/**
	 * Serialized array payload carrying a Visualizer_POI_Canary object.
	 *
	 * @param array $data Plain payload data.
	 * @return string
	 */
	private function object_payload( $data = array() ) {
		Visualizer_POI_Canary::$awoke = false;
		$data[] = new Visualizer_POI_Canary();
		return serialize( $data );
	}

	/**
	 * Visualizer_Module::get_chart_data() must not instantiate objects from content.
	 */
	public function test_get_chart_data_does_not_instantiate_objects() {
		$expected = array(
			array( 'Label', 'Value' ),
			array( 'Safe', 10 ),
		);
		$chart               = new stdClass();
		$chart->post_content = $this->object_payload( $expected );

		$result = Visualizer_Module::get_chart_data( $chart, 'line', false );

		$this->assertSame( $expected, $result );
		$this->assertFalse(
			Visualizer_POI_Canary::$awoke,
			'unserialize() must not instantiate objects from chart post_content.'
		);
	}

	/**
	 * The remote CSV source (_repopulate) must not instantiate objects from content.
	 */
	public function test_remote_csv_source_does_not_instantiate_objects() {
		$chart_id = self::factory()->post->create(
			array(
				'post_type'    => Visualizer_Plugin::CPT_VISUALIZER,
				'post_content' => wp_slash( $this->object_payload( array( 'marker' => 'remote-csv' ) ) ),
			)
		);

		$source = new Visualizer_Source_Csv_Remote();
		$method = new ReflectionMethod( 'Visualizer_Source_Csv_Remote', '_repopulate' );
		$method->setAccessible( true );

		$result = $method->invoke( $source, $chart_id );

		$this->assertFalse( $result, 'Content without a remote source must fail cleanly.' );
		$this->assertFalse(
			Visualizer_POI_Canary::$awoke,
			'Remote CSV source must not instantiate objects from chart post_content.'
		);
	}

	/**
	 * The shared decode_content() chokepoint must not instantiate objects.
	 *
	 * Covers the helper-level guarantee shared by chart/source content callers.
	 */
	public function test_decode_content_does_not_instantiate_objects() {
		$result = Visualizer_Module::decode_content( $this->object_payload( array( 'marker' => 'decoded' ) ) );

		$this->assertFalse(
			Visualizer_POI_Canary::$awoke,
			'decode_content() must not instantiate objects from source content.'
		);
		$this->assertIsArray(
			$result,
			'decode_content() should still return the (neutralized) array.'
		);
		$this->assertSame( 'decoded', $result['marker'] );
	}

	/**
	 * The Utility pie/polarArea render palette call site must not instantiate objects.
	 *
	 * Covers the public global-style filter seam and preserves the trimming
	 * behavior of WordPress's maybe_unserialize().
	 */
	public function test_utility_pie_palette_call_site_is_guarded() {
		$chart_id = self::factory()->post->create(
			array(
				'post_type'    => Visualizer_Plugin::CPT_VISUALIZER,
				'post_content' => wp_slash(
					" \n" . $this->object_payload(
						array(
							array( 'One' ),
							array( 'Two' ),
						)
					) . " \n"
				),
			)
		);
		update_post_meta( $chart_id, Visualizer_Plugin::CF_CHART_LIBRARY, 'ChartJS' );
		update_post_meta( $chart_id, Visualizer_Plugin::CF_CHART_TYPE, 'pie' );
		update_post_meta(
			$chart_id,
			Visualizer_Plugin::CF_SERIES,
			array(
				array( 'label' => 'Label', 'type' => 'string' ),
				array( 'label' => 'Value', 'type' => 'number' ),
			)
		);
		update_option(
			Visualizer_Module_Admin::OPTION_GLOBAL_SETTINGS,
			array(
				'color_primary' => '#3366cc',
				'apply_existing' => '1',
			)
		);

		$utility = Visualizer_Plugin::instance()->getModule( Visualizer_Module_Utility::NAME );
		$result  = $utility->apply_global_style_settings( array(), $chart_id, 'pie' );

		$this->assertCount( 3, $result['slices'], 'Palette size must match whitespace-wrapped chart data.' );
		$this->assertFalse(
			Visualizer_POI_Canary::$awoke,
			'Utility pie palette call site must not instantiate objects from post_content.'
		);
	}

	/**
	 * The ChartJS default-settings call site must not instantiate objects.
	 */
	public function test_utility_chartjs_defaults_call_site_is_guarded() {
		$chart_id = self::factory()->post->create(
			array(
				'post_type'    => Visualizer_Plugin::CPT_VISUALIZER,
				'post_status'  => 'auto-draft',
				'post_content' => wp_slash(
					$this->object_payload(
						array(
							array( 'One' ),
							array( 'Two' ),
						)
					)
				),
			)
		);
		update_post_meta( $chart_id, Visualizer_Plugin::CF_CHART_LIBRARY, 'ChartJS' );
		update_post_meta( $chart_id, Visualizer_Plugin::CF_CHART_TYPE, 'pie' );
		update_post_meta(
			$chart_id,
			Visualizer_Plugin::CF_SERIES,
			array(
				array( 'label' => 'Label', 'type' => 'string' ),
				array( 'label' => 'Value', 'type' => 'number' ),
			)
		);
		update_post_meta( $chart_id, Visualizer_Plugin::CF_SETTINGS, array() );

		Visualizer_Module_Utility::set_defaults( get_post( $chart_id ) );
		$settings = get_post_meta( $chart_id, Visualizer_Plugin::CF_SETTINGS, true );

		$this->assertCount( 3, $settings['slices'] );
		$this->assertFalse(
			Visualizer_POI_Canary::$awoke,
			'ChartJS defaults must not instantiate objects from post_content.'
		);
	}

	/**
	 * The Gutenberg block render call site must not instantiate objects.
	 *
	 * Drives the front-end/REST data path with a requested chart that differs
	 * from the global post. Reverting the guard or reading the global post fails.
	 */
	public function test_gutenberg_block_render_call_site_is_guarded() {
		$chart_id = self::factory()->post->create(
			array(
				'post_type'    => Visualizer_Plugin::CPT_VISUALIZER,
				'post_content' => wp_slash(
					$this->object_payload(
						array(
							array( 'requested-chart' ),
						)
					)
				),
			)
		);
		update_post_meta( $chart_id, Visualizer_Plugin::CF_CHART_TYPE, 'line' );
		update_post_meta( $chart_id, Visualizer_Plugin::CF_CHART_LIBRARY, 'ChartJS' );
		update_post_meta( $chart_id, Visualizer_Plugin::CF_SETTINGS, array() );
		update_post_meta(
			$chart_id,
			Visualizer_Plugin::CF_SERIES,
			array(
				array( 'label' => 'Label', 'type' => 'string' ),
			)
		);

		wp_set_current_user( self::factory()->user->create( array( 'role' => 'editor' ) ) );
		$decoy = self::factory()->post->create(
			array(
				'post_content' => wp_slash( serialize( array( array( 'global-post' ) ) ) ),
			)
		);
		$GLOBALS['post'] = get_post( $decoy );
		setup_postdata( $GLOBALS['post'] );

		try {
			$result = Visualizer_Gutenberg_Block::get_instance()->get_visualizer_data( array( 'id' => $chart_id ) );
		} finally {
			wp_reset_postdata();
		}

		$this->assertIsArray( $result );
		$this->assertSame( 'requested-chart', $result['visualizer-data'][0][0] );
		$this->assertFalse(
			Visualizer_POI_Canary::$awoke,
			'Gutenberg block render call site must not instantiate objects from post_content.'
		);
	}

	/**
	 * Cloning a chart copies raw post meta through maybe_decode_content(); it must
	 * neither instantiate objects from meta nor corrupt legitimate serialized meta.
	 */
	public function test_clone_chart_meta_is_guarded_and_round_trips() {
		$settings = array( 'series' => array( array( 'color' => '#ff0000' ) ) );
		$chart_id = self::factory()->post->create(
			array(
				'post_type'    => Visualizer_Plugin::CPT_VISUALIZER,
				'post_content' => wp_slash( serialize( array( array( 'Label' ), array( 'Value' ) ) ) ),
			)
		);
		update_post_meta( $chart_id, Visualizer_Plugin::CF_SETTINGS, $settings );
		update_post_meta( $chart_id, Visualizer_Plugin::CF_SERIES, array( new Visualizer_POI_Canary() ) );
		Visualizer_POI_Canary::$awoke = false;

		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
		$_GET['nonce'] = wp_create_nonce( Visualizer_Plugin::ACTION_CLONE_CHART );
		$_GET['chart'] = (string) $chart_id;

		try {
			Visualizer_Plugin::instance()->getModule( Visualizer_Module_Chart::NAME )->cloneChart();
		} catch ( WPDieException $e ) {
			// Expected test-mode exit before the redirect.
		}

		$this->assertFalse(
			Visualizer_POI_Canary::$awoke,
			'Chart clone must not instantiate objects from raw post meta.'
		);

		$clones = get_posts(
			array(
				'post_type'   => Visualizer_Plugin::CPT_VISUALIZER,
				'post_status' => 'any',
				'exclude'     => array( $chart_id ),
				'numberposts' => -1,
			)
		);
		$this->assertCount( 1, $clones, 'Clone action must create exactly one new chart.' );
		$this->assertSame(
			$settings,
			get_post_meta( $clones[0]->ID, Visualizer_Plugin::CF_SETTINGS, true ),
			'Legitimate serialized meta must survive cloning unchanged.'
		);
	}

	/**
	 * Saving and restoring a chart revision copies raw post meta through
	 * maybe_decode_content(); neither direction may instantiate objects, and
	 * legitimate serialized meta must survive the round trip.
	 */
	public function test_revision_meta_copy_is_guarded_and_round_trips() {
		$settings = array( 'series' => array( array( 'color' => '#00ff00' ) ) );
		$chart_id = self::factory()->post->create(
			array(
				'post_type'    => Visualizer_Plugin::CPT_VISUALIZER,
				'post_content' => wp_slash( serialize( array( array( 'Label' ), array( 'Value' ) ) ) ),
			)
		);
		update_post_meta( $chart_id, Visualizer_Plugin::CF_SETTINGS, $settings );
		update_post_meta( $chart_id, Visualizer_Plugin::CF_SERIES, array( new Visualizer_POI_Canary() ) );
		Visualizer_POI_Canary::$awoke = false;

		// _wp_put_post_revision fires the hook that runs Admin::addRevision().
		$revision_id = _wp_put_post_revision( get_post( $chart_id ) );

		$this->assertIsInt( $revision_id );
		$this->assertFalse(
			Visualizer_POI_Canary::$awoke,
			'Saving a revision must not instantiate objects from raw post meta.'
		);
		$this->assertSame(
			$settings,
			get_metadata( 'post', $revision_id, Visualizer_Plugin::CF_SETTINGS, true ),
			'Legitimate serialized meta must be copied to the revision unchanged.'
		);

		// Change the live meta, then restore; wp_restore_post_revision fires
		// the hook that runs Admin::restoreRevision().
		update_post_meta( $chart_id, Visualizer_Plugin::CF_SETTINGS, array( 'series' => array() ) );
		Visualizer_POI_Canary::$awoke = false;

		wp_restore_post_revision( $revision_id );

		$this->assertFalse(
			Visualizer_POI_Canary::$awoke,
			'Restoring a revision must not instantiate objects from raw revision meta.'
		);
		$this->assertSame(
			$settings,
			get_post_meta( $chart_id, Visualizer_Plugin::CF_SETTINGS, true ),
			'Legitimate serialized meta must survive the revision restore unchanged.'
		);
	}
}
