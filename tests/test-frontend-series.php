<?php
/**
 * Chart series metadata regression tests.
 *
 * @package Visualizer
 */
class Test_Visualizer_Frontend_Series extends WP_UnitTestCase {
	/**
	 * Loading charts must tolerate malformed optional series metadata.
	 *
	 * @dataProvider series_values
	 */
	public function test_chart_series_metadata( $settings_series, $series, $expected ) {
		$id = self::factory()->post->create( array( 'post_type' => Visualizer_Plugin::CPT_VISUALIZER ) );
		update_post_meta( $id, Visualizer_Plugin::CF_SETTINGS, array( 'series' => $settings_series ) );
		if ( is_bool( $series ) ) {
			// WordPress persists scalar booleans as empty strings, so a stored boolean can only be
			// observed by short-circuiting the lookup. WP_UnitTestCase removes the filter on tear down.
			add_filter(
				'get_post_metadata',
				function ( $value, $object_id, $meta_key ) use ( $id, $series ) {
					return (int) $object_id === $id && Visualizer_Plugin::CF_SERIES === $meta_key ? $series : $value;
				},
				10,
				3
			);
		} else {
			update_post_meta( $id, Visualizer_Plugin::CF_SERIES, $series );
		}
		$frontend = ( new ReflectionClass( 'Visualizer_Module_Frontend' ) )->newInstanceWithoutConstructor();
		$method = new ReflectionMethod( $frontend, 'getChartData' );
		$method->setAccessible( true );
		$data = $method->invoke( $frontend, 'series-test', $id );
		$this->assertSame( $expected, $data['settings']['series'] );
		$this->assertEquals( $data, get_transient( 'series-test_' . $id ) );
	}

	/**
	 * Settings-side values are stored inside a serialized array, so booleans and empty strings
	 * survive as-is. Series-side booleans are injected through the metadata filter.
	 */
	public function series_values() {
		return array(
			'boolean settings'     => array( false, array( array() ), false ),
			'empty settings'       => array( '', array( array() ), '' ),
			'boolean columns'      => array( array( array( 'color' => 'red' ) ), false, array( array( 'color' => 'red' ) ) ),
			'empty columns'        => array( array( array( 'color' => 'red' ) ), '', array( array( 'color' => 'red' ) ) ),
			'missing both'         => array( array(), '', array() ),
			'valid padding'        => array( array( array( 'color' => 'red' ) ), array( array(), array() ), array( array( 'color' => 'red' ), array( 'color' => 'red' ) ) ),
			'equal lengths'        => array( array( array() ), array( array() ), array( array() ) ),
		);
	}
}
