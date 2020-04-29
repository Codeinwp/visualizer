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
	}

	/**
	 * All 'dataTable' and 'table' charts to become 'tabular'.
	 */
	private static function makeAllTableChartsTabular() {
		global $wpdb;

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
