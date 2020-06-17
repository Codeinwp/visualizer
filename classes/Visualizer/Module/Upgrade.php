<?php

/**
 * General module to upgrade configuration/charts.
 *
 * @category Visualizer
 * @package Module
 *
 * @since 3.4.3
 */
class Visualizer_Module_Upgrade extends Visualizer_Module {

	const NAME = __CLASS__;

	/**
	 * Upgrades the configuration of charts, if required.
	 */
	public static function upgrade() {
		$last_version = get_option( 'visualizer-upgraded', '0.0.0' );

		switch ( $last_version ) {
			case '0.0.0':
				self::makeAllTableChartsTabular();
				break;
			default:
				return;
		}

		update_option( 'visualizer-upgraded', Visualizer_Plugin::VERSION );

		// this will help in debugging to know which version this was upgraded from.
		update_option( 'visualizer-upgraded-from', $last_version );
	}

	/**
	 * All 'dataTable' and 'table' charts to become 'tabular'.
	 * All charts that do not have a library, to get the default library.
	 */
	private static function makeAllTableChartsTabular() {
		global $wpdb;

		// old table charts may not specify the library.
		$args = array(
			'post_type'      => Visualizer_Plugin::CPT_VISUALIZER,
			'fields'        => 'ids',
			'post_status'   => 'publish',
			'meta_query'    => array(
				array(
					'key'       => Visualizer_Plugin::CF_CHART_LIBRARY,
					'compare'   => 'NOT EXISTS',
				),
				array(
					'key'       => Visualizer_Plugin::CF_CHART_TYPE,
					'value'     => 'table',
				),
			),
		);
		$query = new WP_Query( $args );
		while ( $query->have_posts() ) {
			$id = $query->next_post();
			add_post_meta( $id, Visualizer_Plugin::CF_CHART_LIBRARY, 'GoogleCharts' );
		}

		// make all dataTable and table chart types into tabular.
		// phpcs:disable WordPress.DB.PreparedSQL.NotPrepared
		$wpdb->query(
			"UPDATE $wpdb->postmeta pm, $wpdb->posts p SET pm.meta_value = 'tabular'
			WHERE p.post_type = '" . Visualizer_Plugin::CPT_VISUALIZER . "'
			AND p.id = pm.post_id
			AND pm.meta_key = '" . Visualizer_Plugin::CF_CHART_TYPE . "'
			AND pm.meta_value IN ( 'dataTable', 'table' )"
		);
		// phpcs:enable WordPress.DB.PreparedSQL.NotPrepared

	}
}
