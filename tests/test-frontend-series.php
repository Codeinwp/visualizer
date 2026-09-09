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
		update_post_meta( $id, Visualizer_Plugin::CF_SERIES, $series );
		$frontend = ( new ReflectionClass( 'Visualizer_Module_Frontend' ) )->newInstanceWithoutConstructor();
		$method = new ReflectionMethod( $frontend, 'getChartData' );
		$method->setAccessible( true );
		$data = $method->invoke( $frontend, 'series-test', $id );
		$this->assertSame( $expected, $data['settings']['series'] );
		$this->assertEquals( $data, get_transient( 'series-test_' . $id ) );
	}

	public function series_values() {
		return array(
			'boolean settings' => array( false, array( array() ), false ),
			'boolean columns' => array( array( array( 'color' => 'red' ) ), false, array( array( 'color' => 'red' ) ) ),
			'missing columns' => array( array(), '', array() ),
			'valid padding' => array( array( array( 'color' => 'red' ) ), array( array(), array() ), array( array( 'color' => 'red' ), array( 'color' => 'red' ) ) ),
			'equal lengths' => array( array( array() ), array( array() ), array( array() ) ),
		);
	}
}
