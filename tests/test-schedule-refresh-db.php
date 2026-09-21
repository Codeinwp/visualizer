<?php
/**
 * Tests for the recurring DB refresh trigger (regression for #1384).
 *
 * @package     visualizer
 * @subpackage  Tests
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/**
 * Database charts refresh only while `visualizer_schedule_refresh_db` has a live trigger.
 *
 * Action Scheduler returns 0 instead of throwing when it cannot create an action, so the
 * plugin used to drop the WP-Cron fallback for an action that was never stored, leaving
 * nothing scheduled and no way back.
 */
class Test_Visualizer_Schedule_Refresh_Db extends WP_UnitTestCase {

	const HOOK  = 'visualizer_schedule_refresh_db';
	const GROUP = 'visualizer';

	/**
	 * Start every test with neither scheduler armed; the bootstrap activates the plugin.
	 */
	public function set_up() {
		parent::set_up();

		// index.php loads Action Scheduler only when visualizer_can_use_action_scheduler()
		// passes, so skip rather than fatal on a host that cannot run it.
		if (
			! class_exists( 'ActionScheduler' )
			|| ! class_exists( 'ActionScheduler_Store' )
			|| ! function_exists( 'as_unschedule_all_actions' )
		) {
			$this->markTestSkipped( 'Action Scheduler is not loaded on this environment.' );
		}

		as_unschedule_all_actions( self::HOOK, array(), self::GROUP );
		wp_clear_scheduled_hook( self::HOOK );

		// the bootstrap activates the plugin, so `init` has already opened a check window.
		delete_transient( Visualizer_Module_Setup::REFRESH_DB_CHECK_TRANSIENT );
	}

	/**
	 * Whether anything will fire the refresh hook again.
	 *
	 * @return bool
	 */
	private function has_trigger(): bool {
		return false !== as_next_scheduled_action( self::HOOK, array(), self::GROUP )
			|| false !== wp_next_scheduled( self::HOOK );
	}

	/**
	 * Fire a plugin lifecycle hook the way WordPress does.
	 *
	 * @param string $action Either `activate` or `deactivate`.
	 */
	private function lifecycle( string $action ) {
		do_action( $action . '_' . plugin_basename( VISUALIZER_BASEFILE ), false );
	}

	/**
	 * Precondition: Action Scheduler is usable, otherwise the rest proves nothing.
	 */
	public function test_action_scheduler_is_available() {
		$this->assertTrue( function_exists( 'as_schedule_recurring_action' ), 'Action Scheduler must be loaded' );
		$this->assertTrue( ActionScheduler::is_initialized(), 'Action Scheduler must be initialized' );
	}

	/**
	 * A refused Action Scheduler creation must leave the WP-Cron fallback in place.
	 */
	public function test_activation_keeps_a_trigger_when_action_scheduler_creation_fails() {
		add_filter( 'pre_as_schedule_recurring_action', '__return_zero' );
		$this->lifecycle( 'activate' );
		remove_filter( 'pre_as_schedule_recurring_action', '__return_zero' );

		$this->assertTrue( $this->has_trigger(), 'activation must not leave the refresh hook with no trigger at all' );
	}

	/**
	 * A site that already lost both schedulers must recover on an ordinary request.
	 */
	public function test_recovery_restores_a_trigger_when_both_schedulers_are_empty() {
		$this->assertFalse( $this->has_trigger(), 'precondition: nothing is scheduled' );

		$this->setup_module()->maybe_reschedule_refresh_db();

		$this->assertTrue( $this->has_trigger(), 'a missing refresh trigger must be restored without another activation' );
	}

	/**
	 * A legacy WP-Cron event must move onto Action Scheduler and stop firing twice.
	 */
	public function test_legacy_wp_cron_event_migrates_to_action_scheduler() {
		wp_schedule_event( time(), 'visualizer_ten_minutes', self::HOOK );
		$this->assertNotFalse( wp_next_scheduled( self::HOOK ), 'precondition: a legacy WP-Cron event exists' );

		$this->setup_module()->maybe_reschedule_refresh_db();

		$this->assertNotFalse( as_next_scheduled_action( self::HOOK, array(), self::GROUP ), 'the refresh must move onto Action Scheduler' );
		$this->assertFalse( wp_next_scheduled( self::HOOK ), 'the superseded WP-Cron event must not survive the migration' );
	}

	/**
	 * While Action Scheduler keeps refusing, later requests must leave the fallback alone.
	 *
	 * Re-arming it on every request pins the event to a past timestamp, so the refresh runs
	 * on every cron spawn instead of every ten minutes.
	 */
	public function test_recovery_does_not_drag_a_live_wp_cron_event_back_into_the_past() {
		add_filter( 'pre_as_schedule_recurring_action', '__return_zero' );

		$this->setup_module()->ensure_refresh_db_action();
		$this->assertNotFalse( wp_next_scheduled( self::HOOK ), 'precondition: the fallback is armed' );

		// mimic WP-Cron having run the event and rescheduled it forward.
		wp_clear_scheduled_hook( self::HOOK );
		$future = time() + 600;
		wp_schedule_event( $future, 'visualizer_ten_minutes', self::HOOK );

		$this->setup_module()->ensure_refresh_db_action();
		remove_filter( 'pre_as_schedule_recurring_action', '__return_zero' );

		$this->assertSame( $future, wp_next_scheduled( self::HOOK ), 'a later request must not make the refresh due again' );
	}

	/**
	 * A concurrent request must not be able to create a second recurring action.
	 *
	 * Action Scheduler creates the next recurrence only after the current one completes, so
	 * every interval there is a moment with nothing pending. A visitor arriving in that
	 * window used to add a duplicate, and duplicates never go away on their own.
	 */
	public function test_recovery_does_not_create_a_second_action_when_a_concurrent_request_wins_the_race() {
		$done = false;
		$seen = array();
		// Stand in for another request that schedules between our lookup and our write.
		// Action Scheduler passes $priority before $unique, see its functions.php:165.
		$racer = function ( $pre, $timestamp, $interval, $hook, $args, $group, $priority, $unique ) use ( &$done, &$seen ) {
			if ( ! $done ) {
				$done = true;
				$seen = array(
					'priority' => $priority,
					'unique'   => $unique,
				);
				as_schedule_recurring_action( $timestamp, $interval, $hook, $args, $group, $unique, $priority );
			}
			return null;
		};
		add_filter( 'pre_as_schedule_recurring_action', $racer, 10, 8 );

		$this->setup_module()->maybe_reschedule_refresh_db();
		remove_filter( 'pre_as_schedule_recurring_action', $racer, 10 );

		// The simulation only reproduces the race if the racer read the real arguments, so
		// pin the order here rather than trusting the signature. Types alone separate the
		// two, whatever value the code under test passes for $unique.
		$this->assertIsInt( $seen['priority'], 'the filter must pass $priority before $unique' );
		$this->assertIsBool( $seen['unique'], 'the filter must pass $priority before $unique' );
		$this->assertTrue( $done, 'precondition: the race was actually simulated' );
		$this->assertCount( 1, $this->pending_actions(), 'a lost race must not leave the refresh scheduled twice' );
	}

	/**
	 * A WP-Cron event left beside an Action Scheduler action must go.
	 *
	 * Both schedulers fire the same hook, so a site that keeps both refreshes twice per
	 * interval. A lost race between the fallback and a concurrent request can leave that pair.
	 */
	public function test_recovery_removes_a_wp_cron_event_left_beside_an_action_scheduler_action() {
		as_schedule_recurring_action( time(), 600, self::HOOK, array(), self::GROUP, true );
		wp_schedule_event( time(), 'visualizer_ten_minutes', self::HOOK );
		$this->assertNotFalse( as_next_scheduled_action( self::HOOK, array(), self::GROUP ), 'precondition: Action Scheduler owns the refresh' );
		$this->assertNotFalse( wp_next_scheduled( self::HOOK ), 'precondition: a WP-Cron event sits beside it' );

		$this->setup_module()->maybe_reschedule_refresh_db();

		$this->assertFalse( wp_next_scheduled( self::HOOK ), 'the refresh must not stay scheduled on both systems' );
		$this->assertNotFalse( as_next_scheduled_action( self::HOOK, array(), self::GROUP ), 'the Action Scheduler action must survive' );
	}

	/**
	 * A run killed mid flight must not end the recurring chain.
	 *
	 * Action Scheduler creates the next occurrence inside schedule_next_instance(), which a
	 * host kill, fatal or timeout never reaches. The queue cleaner then marks the action
	 * failed, and nothing succeeds it. This is the scenario confirmed on the reporting site.
	 */
	public function test_a_killed_run_does_not_end_the_recurring_chain() {
		as_schedule_recurring_action( time(), 600, self::HOOK, array(), self::GROUP, true );
		$pending   = $this->pending_actions();
		$action_id = reset( $pending );

		// what ActionScheduler_QueueCleaner::mark_failures() does to a run that never returned.
		$store = ActionScheduler::store();
		$store->log_execution( $action_id );
		$store->mark_failure( $action_id );

		$this->assertSame( ActionScheduler_Store::STATUS_FAILED, $store->get_status( $action_id ), 'precondition: the run was killed' );
		$this->assertFalse( $this->has_trigger(), 'precondition: nothing succeeds the killed run' );

		$this->setup_module()->maybe_reschedule_refresh_db();

		$this->assertCount( 1, $this->pending_actions(), 'a killed run must get a successor' );
	}

	/**
	 * Action Scheduler's own daily assurance hook must restore a missing action.
	 *
	 * This is the floor under the per-request check, and it runs even on a site that serves
	 * no admin requests for a while.
	 */
	public function test_the_daily_action_scheduler_hook_restores_a_missing_action() {
		$this->assertFalse( $this->has_trigger(), 'precondition: nothing is scheduled' );

		do_action( 'action_scheduler_ensure_recurring_actions' );

		$this->assertTrue( $this->has_trigger(), 'the daily assurance hook must restore the refresh' );
	}

	/**
	 * The per-request check stands down inside its window, and the daily hook does not.
	 *
	 * The check runs on `init`, so it must not query Action Scheduler on every request of a
	 * settled site. The daily assurance hook ignores the window and is the floor.
	 */
	public function test_the_per_request_check_is_throttled_and_the_daily_hook_is_the_floor() {
		$module = $this->setup_module();

		$module->maybe_reschedule_refresh_db();
		$this->assertTrue( $this->has_trigger(), 'precondition: the first request scheduled the refresh' );

		// the chain dies again, inside the window the first request opened.
		as_unschedule_all_actions( self::HOOK, array(), self::GROUP );
		$module->maybe_reschedule_refresh_db();

		$this->assertFalse( $this->has_trigger(), 'inside the window the per-request check must stand down' );

		do_action( 'action_scheduler_ensure_recurring_actions' );

		$this->assertTrue( $this->has_trigger(), 'the daily assurance hook must repair it whatever the window says' );
	}

	/**
	 * Recovery must make the refresh due now, not at a midnight that has not happened yet.
	 *
	 * The start time is local midnight derived from `gmt_offset`. West of UTC that midnight
	 * can still be ahead of us, which would park the recovered run hours into the future and
	 * leave the charts stale for the rest of the day.
	 */
	public function test_recovery_does_not_park_the_next_run_in_the_future() {
		// Far enough west that the computed midnight is ahead of us whatever the time of
		// day. WordPress does not clamp gmt_offset, so this stays deterministic.
		$hours_into_utc_day = ( time() - strtotime( 'midnight' ) ) / HOUR_IN_SECONDS;
		add_filter(
			'pre_option_gmt_offset',
			function () use ( $hours_into_utc_day ) {
				return - ( $hours_into_utc_day + 1 );
			}
		);

		$this->setup_module()->ensure_refresh_db_action();

		$next = as_next_scheduled_action( self::HOOK, array(), self::GROUP );
		$this->assertLessThanOrEqual( time(), $next, 'the recovered refresh must be due now, not hours from now' );
	}

	/**
	 * Every pending refresh action.
	 *
	 * @return array
	 */
	private function pending_actions(): array {
		return as_get_scheduled_actions(
			array(
				'hook'   => self::HOOK,
				'group'  => self::GROUP,
				'status' => ActionScheduler_Store::STATUS_PENDING,
			),
			'ids'
		);
	}

	/**
	 * The registered setup module.
	 *
	 * @return Visualizer_Module_Setup
	 */
	private function setup_module(): Visualizer_Module_Setup {
		return Visualizer_Plugin::instance()->getModule( Visualizer_Module_Setup::NAME );
	}
}
