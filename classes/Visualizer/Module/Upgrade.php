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
		$upgraded     = false;

		if ( version_compare( $last_version, '3.4.3', '<' ) ) {
			self::makeAllTableChartsTabular();
			$upgraded = true;
		}

		if ( wp_next_scheduled( 'visualizer_schedule_refresh_db' ) ) {
			self::migrate_action_scheduler();
			$upgraded = true;
		}

		if ( ! $upgraded ) {
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

	/**
	 * Migrate recurring WP-Cron jobs to Action Scheduler.
	 */
	private static function migrate_action_scheduler(): void {
		if ( ! function_exists( 'as_schedule_recurring_action' ) || ! function_exists( 'as_next_scheduled_action' ) ) {
			return;
		}

		$hook         = 'visualizer_schedule_refresh_db';
		$group        = 'visualizer';
		$interval_key = apply_filters( 'visualizer_chart_schedule_interval', 'visualizer_ten_minutes' );
		$interval     = self::get_schedule_interval_seconds( $interval_key );
		$timestamp    = strtotime( 'midnight' ) - get_option( 'gmt_offset' ) * HOUR_IN_SECONDS;

		$next = as_next_scheduled_action( $hook, array(), $group );
		if ( false === $next ) {
			as_schedule_recurring_action( $timestamp, $interval, $hook, array(), $group );
		}

		wp_clear_scheduled_hook( $hook );
	}

	/**
	 * Resolve a cron schedule key to seconds.
	 *
	 * @param string $interval_key Cron schedule key.
	 * @return int Interval in seconds.
	 */
	private static function get_schedule_interval_seconds( $interval_key ) {
		$schedules = wp_get_schedules();
		if ( isset( $schedules[ $interval_key ]['interval'] ) ) {
			return (int) $schedules[ $interval_key ]['interval'];
		}

		return 600;
	}
}
