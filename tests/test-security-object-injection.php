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
	 * Serialized payload carrying a Visualizer_POI_Canary object.
	 *
	 * @return string
	 */
	private function object_payload() {
		Visualizer_POI_Canary::$awoke = false;
		return serialize( new Visualizer_POI_Canary() );
	}

	/**
	 * Visualizer_Module::get_chart_data() must not instantiate objects from content.
	 */
	public function test_get_chart_data_does_not_instantiate_objects() {
		$chart               = new stdClass();
		$chart->post_content = $this->object_payload();

		try {
			Visualizer_Module::get_chart_data( $chart, 'line', false );
		} catch ( \Throwable $e ) {
			// Downstream handling of the neutralized payload is irrelevant here;
			// we only assert that no object was instantiated during unserialize().
		}

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
				'post_content' => wp_slash( $this->object_payload() ),
			)
		);

		$source = new Visualizer_Source_Csv_Remote();
		$method = new ReflectionMethod( 'Visualizer_Source_Csv_Remote', '_repopulate' );
		$method->setAccessible( true );

		try {
			$method->invoke( $source, $chart_id );
		} catch ( \Throwable $e ) {
			// The neutralized payload has no 'source' key and returns false; only the canary matters.
		}

		$this->assertFalse(
			Visualizer_POI_Canary::$awoke,
			'Remote CSV source must not instantiate objects from chart post_content.'
		);
	}

	/**
	 * The shared decode_content() chokepoint must not instantiate objects.
	 *
	 * The business/scheduled JSON sink (updateBusinessJson) reads
	 * serialize()'d source data via getData() and decodes it through
	 * Visualizer_Module::decode_content(). This feeds a serialized canary the
	 * same way and asserts the object is never instantiated.
	 */
	public function test_decode_content_does_not_instantiate_objects() {
		Visualizer_POI_Canary::$awoke = false;

		// Reproduce the content the sink reads: serialize() of the source data
		// (getData()), here carrying a canary object.
		$source    = new Visualizer_Source_Json( array( 'url' => '', 'root' => '', 'paging' => '' ) );
		$data_prop = new ReflectionProperty( 'Visualizer_Source', '_data' );
		$data_prop->setAccessible( true );
		$data_prop->setValue( $source, array( new Visualizer_POI_Canary() ) );
		$content = $source->getData();

		$result = Visualizer_Module::decode_content( $content );

		$this->assertFalse(
			Visualizer_POI_Canary::$awoke,
			'decode_content() must not instantiate objects from source content.'
		);
		$this->assertIsArray(
			$result,
			'decode_content() should still return the (neutralized) array.'
		);
	}
}
