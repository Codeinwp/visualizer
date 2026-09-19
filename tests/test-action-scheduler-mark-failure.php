<?php
/**
 * Marking an action failed must survive another process getting there first.
 *
 * Regression tests for #1369. `ActionScheduler_DBStore::mark_failure()` throws
 * "Unidentified action" whenever its UPDATE changes no row: the action was
 * deleted, or an overlapping cleaner already marked it failed (WP-Cron and the
 * async runner can overlap; only the async runner takes a lock). Unguarded,
 * the whole queue run dies. Visualizer_ActionScheduler_Store tolerates that and
 * still reports a database error.
 *
 * @package     visualizer
 * @subpackage  Tests
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/**
 * The replacement store, and the two callers that mark actions failed.
 */
class Test_Visualizer_Action_Scheduler_Mark_Failure extends WP_UnitTestCase {

	/**
	 * Store under test.
	 *
	 * @var Visualizer_ActionScheduler_Store
	 */
	private $store;

	/**
	 * Query filters added during a test.
	 *
	 * @var callable[]
	 */
	private $filters_to_remove = array();

	/**
	 * Skip when Action Scheduler is not loaded.
	 */
	public function set_up() {
		parent::set_up();

		if ( ! class_exists( 'ActionScheduler_DBStore' ) ) {
			$this->markTestSkipped( 'Action Scheduler is not loaded.' );
		}

		$this->store = new Visualizer_ActionScheduler_Store();
		$this->store->init();
	}

	/**
	 * Remove the query filters even when a test throws.
	 */
	public function tear_down() {
		foreach ( $this->filters_to_remove as $filter ) {
			remove_filter( 'query', $filter );
		}
		$this->filters_to_remove = array();
		parent::tear_down();
	}

	/**
	 * Save an action, then make it a stale in-progress one (last attempt two hours ago).
	 *
	 * @param string $hook Action hook.
	 * @return int Action id.
	 */
	private function seed_stale_running_action( $hook = 'visualizer_schedule_refresh_db' ) {
		global $wpdb;
		$action_id = $this->store->save_action( new ActionScheduler_Action( $hook, array(), new ActionScheduler_SimpleSchedule( as_get_datetime_object( '-2 hours' ) ) ) );
		$gmt       = gmdate( 'Y-m-d H:i:s', time() - 2 * HOUR_IN_SECONDS );
		$wpdb->update(
			$wpdb->actionscheduler_actions,
			array(
				'status'             => ActionScheduler_Store::STATUS_RUNNING,
				'last_attempt_gmt'   => $gmt,
				'last_attempt_local' => $gmt,
			),
			array( 'action_id' => $action_id )
		);
		return (int) $action_id;
	}

	/**
	 * Status column of one action, or null when the row is gone.
	 *
	 * @param int $action_id Action id.
	 * @return string|null
	 */
	private function status_of( $action_id ) {
		global $wpdb;
		return $wpdb->get_var( $wpdb->prepare( "SELECT status FROM {$wpdb->actionscheduler_actions} WHERE action_id = %d", $action_id ) );
	}

	/**
	 * Run `$intercept` once, on the UPDATE that marks `$action_id` failed, and
	 * use its return value as the SQL to execute.
	 *
	 * @param int      $action_id Action whose UPDATE is intercepted.
	 * @param callable $intercept Receives the SQL, returns the SQL to run.
	 */
	private function intercept_mark_failure_update( $action_id, callable $intercept ) {
		global $wpdb;
		$table = $wpdb->actionscheduler_actions;
		$done  = false;
		// Queries issued inside $intercept re-enter this filter: run it once only.
		$filter = function ( $sql ) use ( $action_id, $table, $intercept, &$done ) {
			if ( $done || ! $this->is_mark_failed_update( $sql, $table, $action_id ) ) {
				return $sql;
			}
			$done = true;
			return $intercept( $sql );
		};
		add_filter( 'query', $filter );
		$this->filters_to_remove[] = $filter;
	}

	/**
	 * Whether `$sql` is the UPDATE that marks `$action_id` in `$table` failed.
	 * Matches the SQL `wpdb::update()` builds with or without backticks and quotes.
	 *
	 * @param string $sql       SQL about to run.
	 * @param string $table     Actions table name.
	 * @param int    $action_id Action id.
	 * @return bool
	 */
	private function is_mark_failed_update( $sql, $table, $action_id ) {
		if ( 0 !== stripos( ltrim( $sql ), 'UPDATE' ) || false === strpos( $sql, $table ) ) {
			return false;
		}
		if ( ! preg_match( '/status`?\s*=\s*\'' . ActionScheduler_Store::STATUS_FAILED . '\'/', $sql ) ) {
			return false;
		}
		return preg_match( '/action_id`?\s*=\s*\'?(\d+)/', $sql, $m ) && (int) $m[1] === $action_id;
	}

	/**
	 * Visualizer replaces Action Scheduler's own database store, and nothing else.
	 */
	public function test_filter_replaces_only_the_default_database_store() {
		$this->assertSame( 'Visualizer_ActionScheduler_Store', visualizer_action_scheduler_store_class( 'ActionScheduler_DBStore' ) );
		$this->assertSame( 'Another_Plugin_Store', visualizer_action_scheduler_store_class( 'Another_Plugin_Store' ) );
		$this->assertSame( 'ActionScheduler_HybridStore', visualizer_action_scheduler_store_class( 'ActionScheduler_HybridStore' ) );
	}

	/**
	 * Another process deleted the action: nothing left to mark.
	 */
	public function test_mark_failure_tolerates_a_deleted_action() {
		global $wpdb;
		$action_id = $this->seed_stale_running_action();
		$wpdb->delete( $wpdb->actionscheduler_actions, array( 'action_id' => $action_id ) );

		$this->store->mark_failure( $action_id );

		$this->assertNull( $this->status_of( $action_id ) );
	}

	/**
	 * An overlapping cleaner already marked it failed: the UPDATE changes nothing.
	 */
	public function test_mark_failure_tolerates_an_already_failed_action() {
		global $wpdb;
		$action_id = $this->seed_stale_running_action();
		$wpdb->update( $wpdb->actionscheduler_actions, array( 'status' => ActionScheduler_Store::STATUS_FAILED ), array( 'action_id' => $action_id ) );

		$this->store->mark_failure( $action_id );

		$this->assertSame( ActionScheduler_Store::STATUS_FAILED, $this->status_of( $action_id ) );
	}

	/**
	 * A real database error still surfaces.
	 */
	public function test_mark_failure_still_throws_on_a_database_error() {
		global $wpdb;
		$action_id = $this->seed_stale_running_action();

		// Break the UPDATE itself: the store gets `false`, not zero rows.
		$this->intercept_mark_failure_update(
			$action_id,
			static function ( $sql ) use ( $wpdb ) {
				return str_replace( $wpdb->actionscheduler_actions, 'no_such_table', $sql );
			}
		);
		$suppressed = $wpdb->suppress_errors( true );

		$this->expectException( InvalidArgumentException::class );
		try {
			$this->store->mark_failure( $action_id );
		} finally {
			$wpdb->suppress_errors( $suppressed );
		}
	}

	/**
	 * Queue cleanup keeps going when an action vanishes between its query and its update.
	 */
	public function test_mark_failures_continues_past_an_action_deleted_by_another_process() {
		global $wpdb;
		$vanishing = $this->seed_stale_running_action();
		$survivor  = $this->seed_stale_running_action();

		$this->intercept_mark_failure_update(
			$vanishing,
			static function ( $sql ) use ( $wpdb, $vanishing ) {
				$wpdb->delete( $wpdb->actionscheduler_actions, array( 'action_id' => $vanishing ) );
				return $sql;
			}
		);

		( new ActionScheduler_QueueCleaner( $this->store ) )->mark_failures( 60 );

		$this->assertNull( $this->status_of( $vanishing ), 'the concurrently deleted action stays gone' );
		$this->assertSame( ActionScheduler_Store::STATUS_FAILED, $this->status_of( $survivor ), 'cleanup continues and marks the remaining stale action failed' );
	}

	/**
	 * Runner path (the trace in upstream #970): the action is deleted while it
	 * runs, then it throws, and the runner marks it failed.
	 */
	public function test_process_action_survives_marking_a_deleted_action() {
		global $wpdb;
		$hook      = 'visualizer_test_throwing_action';
		$action_id = $this->store->save_action( new ActionScheduler_Action( $hook, array(), new ActionScheduler_SimpleSchedule( as_get_datetime_object( '-1 minute' ) ) ) );

		add_action(
			$hook,
			static function () use ( $wpdb, $action_id ) {
				$wpdb->delete( $wpdb->actionscheduler_actions, array( 'action_id' => $action_id ) );
				throw new RuntimeException( 'refresh failed' );
			}
		);

		( new ActionScheduler_QueueRunner( $this->store ) )->process_action( $action_id, 'test' );

		$this->assertNull( $this->status_of( $action_id ), 'the deleted action stays gone and the run survives' );
	}
}
