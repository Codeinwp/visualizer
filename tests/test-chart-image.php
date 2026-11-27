<?php
/**
 * Chart image tests.
 *
 * @package Visualizer
 */
class Test_Visualizer_Chart_Image extends WP_Ajax_UnitTestCase {

	/**
	 * A 1x1 PNG as the editor sends it.
	 */
	const PNG = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==';

	/**
	 * Create a chart that the current user owns.
	 *
	 * @return int
	 */
	private function create_own_chart() {
		$this->_setRole( 'contributor' );

		return self::factory()->post->create(
			array(
				'post_type'   => Visualizer_Plugin::CPT_VISUALIZER,
				'post_author' => get_current_user_id(),
				'post_status' => 'draft',
			)
		);
	}

	/**
	 * An attachment that the plugin did not make for the chart is not deleted when the chart image changes.
	 */
	public function test_image_save_keeps_an_attachment_of_another_owner() {
		$attachment_id = self::factory()->attachment->create_upload_object( DIR_TESTDATA . '/images/canola.jpg' );
		$chart_id      = $this->create_own_chart();
		update_post_meta( $chart_id, Visualizer_Plugin::CF_CHART_IMAGE, $attachment_id );

		$module = new Visualizer_Module_Chart( Visualizer_Plugin::instance() );
		$module->save_chart_image( self::PNG, $chart_id, false );

		$this->assertInstanceOf( 'WP_Post', get_post( $attachment_id ) );
	}

	/**
	 * A request without the editor nonce does not save a chart image.
	 */
	public function test_image_save_needs_the_editor_nonce() {
		$chart_id = $this->create_own_chart();
		$_GET     = array( 'chart' => $chart_id );
		$_POST    = array(
			'chart-img'        => self::PNG,
			'save_chart_image' => 'yes',
		);

		ob_start();
		try {
			$this->_handleAjax( 'visualizer-edit-chart' );
		} catch ( WPAjaxDieContinueException $e ) {
			// The handler ends the request.
		} catch ( WPAjaxDieStopException $ee ) {
			// The handler ends the request.
		}
		ob_end_clean();

		$this->assertSame( '', get_post_meta( $chart_id, Visualizer_Plugin::CF_CHART_IMAGE, true ) );
	}
}
