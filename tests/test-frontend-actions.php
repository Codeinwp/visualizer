<?php
/**
 * Chart action buttons tests.
 *
 * @package Visualizer
 */
class Test_Visualizer_Frontend_Actions extends WP_UnitTestCase {

	/**
	 * Render the shortcode of a chart that has the given action buttons setting.
	 *
	 * @param string[] $actions Saved action buttons.
	 *
	 * @return string
	 */
	private function render_chart_with_actions( $actions ) {
		$id = self::factory()->post->create(
			array(
				'post_type'    => Visualizer_Plugin::CPT_VISUALIZER,
				'post_content' => serialize( array( array( 'a', 1 ) ) ),
			)
		);
		update_post_meta( $id, Visualizer_Plugin::CF_CHART_TYPE, 'pie' );
		update_post_meta( $id, Visualizer_Plugin::CF_SERIES, array( array( 'label' => 'x', 'type' => 'string' ), array( 'label' => 'y', 'type' => 'number' ) ) );
		update_post_meta( $id, Visualizer_Plugin::CF_SETTINGS, array( 'actions' => $actions ) );

		return do_shortcode( '[visualizer id="' . $id . '"]' );
	}

	/**
	 * The part after the action name stays inside its attribute.
	 */
	public function test_action_mime_cannot_add_an_attribute() {
		$markup = $this->render_chart_with_actions( array( 'print;x" onfocus="alert(1)' ) );

		$this->assertStringContainsString( 'data-visualizer-mime="x&quot; onfocus=&quot;alert(1)"', $markup );
	}

	/**
	 * An action that the plugin does not define gets no button.
	 */
	public function test_unknown_action_gets_no_button() {
		$markup = $this->render_chart_with_actions( array( 'x" onfocus="alert(1)', 'csv;application/csv' ) );

		$this->assertStringNotContainsString( 'onfocus', $markup );
		$this->assertStringContainsString( 'data-visualizer-type="csv"', $markup );
	}

	/**
	 * The key and the title of an action that a filter adds stay inside their attributes.
	 */
	public function test_filtered_action_key_and_title_cannot_add_an_attribute() {
		add_filter(
			'visualizer_action_buttons',
			function ( $actions ) {
				$actions['share" data-x="1'] = array(
					'label' => 'Share',
					'title' => 'Share" onfocus="alert(1)',
				);
				return $actions;
			}
		);

		$markup = $this->render_chart_with_actions( array( 'share" data-x="1' ) );

		$this->assertStringContainsString( 'data-visualizer-type="share&quot; data-x=&quot;1"', $markup );
		$this->assertStringContainsString( 'title="Share&quot; onfocus=&quot;alert(1)"', $markup );
	}
}
