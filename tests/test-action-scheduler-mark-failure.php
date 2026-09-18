<?php
/**
 * Marking an action failed must survive another process getting there first.
 *
 * Regression tests for #1369. `ActionScheduler_DBStore::mark_failure()` throws
 * "Unidentified action" whenever its UPDATE changes no row: the action was
 * deleted, or an overlapping cleaner already marked it failed (WP-Cron and the
 * async runner can overlap; only the async runner takes a lock). Unguarded,
 * the whole queue run dies. The bundled copy is patched so both callers, the
 * queue cleaner loop and the runner's error path, skip that action and go on.
 *
 * @package     visualizer
 * @subpackage  Tests
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/**
 * Both mark_failure() callers in the bundled Action Scheduler.
 */
class Test_Visualizer_Action_Scheduler_Mark_Failure extends WP_UnitTestCase {

	/**
	 * The database store. The bug lives there; a fresh test site may still be
	 * on the legacy post store or the hybrid migration store.
	 *
	 * @var ActionScheduler_DBStore
	 */
	private $store;

	/**
	 * Skip when the bundled library is not the one loaded (another plugin's copy won).
	 */
	public function set_up() {
		parent::set_up();

		if ( ! class_exists( 'ActionScheduler_QueueCleaner' ) || ! defined( 'VISUALIZER_ABSPATH' ) ) {
			$this->markTestSkipped( 'Action Scheduler is not loaded.' );
		}

		$loaded = wp_normalize_path( ActionScheduler::plugin_path( '' ) );
		$ours   = wp_normalize_path( VISUALIZER_ABSPATH . '/vendor/woocommerce/action-scheduler' );
		if ( 0 !== strpos( $loaded, $ours ) ) {
			$this->markTestSkipped( 'Another Action Scheduler copy is loaded: ' . $loaded );
		}

		$this->store = new ActionScheduler_DBStore();
		$this->store->init();
	}

	/**
	 * Insert a stale in-progress action (last attempt two hours ago).
	 *
	 * @return int Action id.
	 */
	private function seed_stale_running_action() {
		global $wpdb;
		$gmt = gmdate( 'Y-m-d H:i:s', time() - 2 * HOUR_IN_SECONDS );
		$wpdb->insert(
			$wpdb->actionscheduler_actions,
			array(
				'hook'                 => 'visualizer_schedule_refresh_db',
				'status'               => ActionScheduler_Store::STATUS_RUNNING,
				'scheduled_date_gmt'   => $gmt,
				'scheduled_date_local' => $gmt,
				'args'                 => '[]',
				'schedule'             => '',
				'group_id'             => 0,
				'attempts'             => 1,
				'last_attempt_gmt'     => $gmt,
				'last_attempt_local'   => $gmt,
				'claim_id'             => 0,
			)
		);
		return (int) $wpdb->insert_id;
	}

	/**
	 * Run the cleaner's mark_failures() while "another process" acts on one
	 * action the moment the cleaner issues its UPDATE for it.
	 *
	 * @param int      $action_id     Action the other process touches.
	 * @param callable $other_process Runs once, right before that UPDATE.
	 */
	private function mark_failures_racing( $action_id, callable $other_process ) {
		$this->intercept_mark_failure_update(
			$action_id,
			static function ( $sql ) use ( $other_process ) {
				$other_process();
				return $sql;
			}
		);
		( new ActionScheduler_QueueCleaner( $this->store ) )->mark_failures( 60 );
	}

	/**
	 * Run `$intercept` once, on the UPDATE that marks `$action_id` failed, and
	 * use its return value as the SQL to execute. Removed after the test.
	 *
	 * @param int      $action_id Action whose UPDATE is intercepted.
	 * @param callable $intercept Receives the SQL, returns the SQL to run.
	 */
	private function intercept_mark_failure_update( $action_id, callable $intercept ) {
		global $wpdb;
		$table = $wpdb->actionscheduler_actions;
		$done  = false;
		// Queries issued inside $intercept re-enter this filter: run it once only.
		$filter = static function ( $sql ) use ( $action_id, $table, $intercept, &$done ) {
			if ( ! $done && 0 === stripos( ltrim( $sql ), 'UPDATE' ) && false !== strpos( $sql, $table ) && preg_match( '/action_id`?\s*=\s*\'?(\d+)/', $sql, $m ) && (int) $m[1] === $action_id ) {
				$done = true;
				return $intercept( $sql );
			}
			return $sql;
		};
		add_filter( 'query', $filter );
		$this->filters_to_remove[] = $filter;
	}

	/**
	 * Query filters added by intercept_mark_failure_update().
	 *
	 * @var callable[]
	 */
	private $filters_to_remove = array();

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
	 * Cleanup keeps going when an action vanishes between its query and its update.
	 */
	public function test_mark_failures_survives_an_action_deleted_by_another_process() {
		global $wpdb;
		$vanishing = $this->seed_stale_running_action();
		$survivor  = $this->seed_stale_running_action();

		$this->mark_failures_racing(
			$vanishing,
			static function () use ( $wpdb, $vanishing ) {
				$wpdb->delete( $wpdb->actionscheduler_actions, array( 'action_id' => $vanishing ) );
			}
		);

		$this->assertNull( $this->status_of( $vanishing ), 'the concurrently deleted action stays gone' );
		$this->assertSame( ActionScheduler_Store::STATUS_FAILED, $this->status_of( $survivor ), 'cleanup continues and marks the remaining stale action failed' );
	}

	/**
	 * An overlapping cleaner marked it first. The UPDATE then changes nothing,
	 * MySQL reports zero rows, and the store throws as if the row were gone.
	 */
	public function test_mark_failures_survives_an_action_already_failed_by_an_overlapping_cleaner() {
		global $wpdb;
		$raced    = $this->seed_stale_running_action();
		$survivor = $this->seed_stale_running_action();

		$this->mark_failures_racing(
			$raced,
			static function () use ( $wpdb, $raced ) {
				$wpdb->update( $wpdb->actionscheduler_actions, array( 'status' => ActionScheduler_Store::STATUS_FAILED ), array( 'action_id' => $raced ) );
			}
		);

		$this->assertSame( ActionScheduler_Store::STATUS_FAILED, $this->status_of( $raced ) );
		$this->assertSame( ActionScheduler_Store::STATUS_FAILED, $this->status_of( $survivor ), 'cleanup continues past the action the other cleaner already handled' );
	}

	/**
	 * Same hole on the processing path (the trace in upstream #970): a long
	 * action gets marked failed by the cleaner while it runs, then throws;
	 * marking it failed again changes no row.
	 */
	public function test_process_action_survives_marking_an_already_failed_action() {
		global $wpdb;
		$hook      = 'visualizer_test_throwing_action';
		$action_id = $this->store->save_action( new ActionScheduler_Action( $hook, array(), new ActionScheduler_SimpleSchedule( as_get_datetime_object( '-1 minute' ) ) ) );

		add_action(
			$hook,
			static function () use ( $wpdb, $action_id ) {
				$wpdb->update( $wpdb->actionscheduler_actions, array( 'status' => ActionScheduler_Store::STATUS_FAILED ), array( 'action_id' => $action_id ) );
				throw new RuntimeException( 'refresh failed' );
			}
		);

		( new ActionScheduler_QueueRunner( $this->store ) )->process_action( $action_id, 'test' );

		$this->assertSame( ActionScheduler_Store::STATUS_FAILED, $this->status_of( $action_id ) );
	}

	/**
	 * The guard is for the race only. A real database error while marking the
	 * action failed must still surface, as it did before the patch.
	 */
	public function test_mark_failures_still_throws_on_a_database_error() {
		global $wpdb;
		$stale = $this->seed_stale_running_action();

		// Break the UPDATE itself: the store gets `false`, not zero rows.
		$this->intercept_mark_failure_update(
			$stale,
			static function ( $sql ) use ( $wpdb ) {
				return str_replace( $wpdb->actionscheduler_actions, 'no_such_table', $sql );
			}
		);
		$suppressed = $wpdb->suppress_errors( true );

		$this->expectException( InvalidArgumentException::class );
		try {
			( new ActionScheduler_QueueCleaner( $this->store ) )->mark_failures( 60 );
		} finally {
			$wpdb->suppress_errors( $suppressed );
		}
	}
}
