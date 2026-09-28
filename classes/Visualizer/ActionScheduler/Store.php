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

		// Same UPDATE as the parent. Zero rows means the row was deleted or
		// already failed; only `false` is a database error.
		$updated = $wpdb->update(
			$wpdb->actionscheduler_actions,
			array( 'status' => self::STATUS_FAILED ),
			array( 'action_id' => $action_id ),
			array( '%s' ),
			array( '%d' )
		);
		if ( false === $updated ) {
			/* translators: %s is the action ID */
			throw new InvalidArgumentException( sprintf( __( 'Unable to mark action %s as failed.', 'visualizer' ), $action_id ) );
		}
	}
}
