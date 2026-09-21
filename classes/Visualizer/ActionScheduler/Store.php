<?php
/**
 * Action Scheduler store that tolerates a lost race when marking an action failed.
 *
 * `ActionScheduler_DBStore::mark_failure()` throws when its UPDATE changes no
 * row. That happens when another process deleted the action, or already marked
 * it failed: the queue cleaner and the queue runner can both reach the same
 * action, and WP-Cron's queue run takes no lock. Nothing catches the exception,
 * so the whole queue run ends with a fatal error (#1369, upstream
 * woocommerce/action-scheduler#970).
 *
 * Registered through the `action_scheduler_store_class` filter in `index.php`.
 *
 * @category Visualizer
 * @package ActionScheduler
 *
 * @since 4.0.9
 */
class Visualizer_ActionScheduler_Store extends ActionScheduler_DBStore {

	/**
	 * Mark an action failed, and accept that another process got there first.
	 *
	 * A database error still throws, so real failures stay visible.
	 *
	 * @param int $action_id Action ID.
	 *
	 * @throws InvalidArgumentException When the UPDATE itself failed.
	 *
	 * @return void
	 */
	public function mark_failure( $action_id ) {
		global $wpdb;

		try {
			parent::mark_failure( $action_id );
		} catch ( InvalidArgumentException $e ) {
			// The parent throws on zero changed rows. `wpdb::query()` clears
			// `last_error` before each statement, so an error set here belongs
			// to that UPDATE; without one the row was deleted or already failed.
			if ( ! empty( $wpdb->last_error ) ) {
				throw $e;
			}
		}
	}
}
