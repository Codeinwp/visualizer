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
 * Filters added inside a test need no removal: tear_down() restores $wp_filter wholesale.
 */
class Test_Visualizer_Schedule_Refresh_Db extends WP_UnitTestCase {

	const HOOK  = 'visualizer_schedule_refresh_db';
	const GROUP = 'visualizer';

	/**
	 * Start every test with neither scheduler armed; the bootstrap activates the plugin.
	 */
	public function set_up() {
		parent::set_up();

		// Skip rather than fatal where index.php did not load Action Scheduler.
		if (
			! class_exists( 'ActionScheduler' )
			|| ! class_exists( 'ActionScheduler_Store' )
			|| ! function_exists( 'as_unschedule_all_actions' )
		) {
			$this->markTestSkipped( 'Action Scheduler is not loaded on this environment.' );
		}

		as_unschedule_all_actions( self::HOOK, array(), self::GROUP );
		wp_clear_scheduled_hook( self::HOOK );

		// the bootstrap's init already opened a check window.
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
	 * Call the lifecycle callback directly; plugin_basename() is not stable across environments.
	 *
	 * @param string $action Either `activate` or `deactivate`.
	 */
	private function lifecycle( string $action ) {
		$this->setup_module()->$action( false );
	}

	/**
	 * Precondition. Fails rather than skips: loaded but uninitialized means the load order broke.
	 */
	public function test_action_scheduler_is_available() {
		$this->assertTrue( function_exists( 'as_schedule_recurring_action' ), 'Action Scheduler must be loaded' );
		$this->assertTrue(
			ActionScheduler::is_initialized(),
			'Action Scheduler is loaded but not initialized; the other tests would assert nothing.'
		);
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
	 */
	public function test_recovery_does_not_create_a_second_action_when_a_concurrent_request_wins_the_race() {
		$done = false;
		$seen = array();
		// Another request scheduling between our lookup and our write. Action Scheduler
		// passes $priority before $unique, see its functions.php:165.
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

		// Pin the argument order by type, whatever value the code passes for $unique.
		$this->assertIsInt( $seen['priority'], 'the filter must pass $priority before $unique' );
		$this->assertIsBool( $seen['unique'], 'the filter must pass $priority before $unique' );
		$this->assertTrue( $done, 'precondition: the race was actually simulated' );
		$this->assertCount( 1, $this->pending_actions(), 'a lost race must not leave the refresh scheduled twice' );
	}

	/**
	 * A WP-Cron event left beside an Action Scheduler action must go; both fire the hook.
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
	 * A run killed mid flight must not end the recurring chain (the scenario on the reporting site).
	 */
	public function test_a_killed_run_does_not_end_the_recurring_chain() {
		as_schedule_recurring_action( time(), 600, self::HOOK, array(), self::GROUP, true );
		$pending   = $this->pending_actions();
		$action_id = reset( $pending );

		// what the queue cleaner does to a run that never returned.
		$store = ActionScheduler::store();
		$store->log_execution( $action_id );
		$store->mark_failure( $action_id );

		$this->assertSame( ActionScheduler_Store::STATUS_FAILED, $store->get_status( $action_id ), 'precondition: the run was killed' );
		$this->assertFalse( $this->has_trigger(), 'precondition: nothing succeeds the killed run' );

		$this->setup_module()->maybe_reschedule_refresh_db();

		$this->assertCount( 1, $this->pending_actions(), 'a killed run must get a successor' );
	}

	/**
	 * Action Scheduler's daily assurance hook must restore a missing action.
	 */
	public function test_the_daily_action_scheduler_hook_restores_a_missing_action() {
		$this->assertFalse( $this->has_trigger(), 'precondition: nothing is scheduled' );

		do_action( 'action_scheduler_ensure_recurring_actions' );

		$this->assertTrue( $this->has_trigger(), 'the daily assurance hook must restore the refresh' );
	}

	/**
	 * The per-request check stands down inside its window; the daily hook does not.
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
	 */
	public function test_recovery_does_not_park_the_next_run_in_the_future() {
		// Far enough west that the computed midnight is always ahead of now.
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
	 * A filtered interval key WP-Cron does not know must not drop the trigger.
	 */
	public function test_an_unknown_interval_key_still_leaves_a_trigger() {
		add_filter(
			'visualizer_chart_schedule_interval',
			static function () {
				return 'not_a_registered_schedule';
			}
		);
		// force the WP-Cron fallback.
		add_filter( 'pre_as_schedule_recurring_action', '__return_zero' );

		$this->setup_module()->ensure_refresh_db_action();

		$this->assertTrue( $this->has_trigger(), 'an unknown interval key must not leave the refresh with no trigger' );
	}

	/**
	 * The start time must be a whole second, whatever gmt_offset holds.
	 */
	public function test_a_fractional_offset_does_not_schedule_a_fractional_timestamp() {
		add_filter(
			'pre_option_gmt_offset',
			static function () {
				return 5.0001;
			}
		);
		// force the WP-Cron fallback.
		add_filter( 'pre_as_schedule_recurring_action', '__return_zero' );

		$lost = array();
		set_error_handler(
			static function ( $errno, $errstr ) use ( &$lost ) {
				if ( false !== strpos( $errstr, 'loses precision' ) ) {
					$lost[] = $errstr;
				}
				return true;
			},
			E_DEPRECATED
		);

		try {
			$this->setup_module()->ensure_refresh_db_action();
		} finally {
			restore_error_handler();
		}

		$this->assertSame( array(), $lost, 'the start time must be a whole second' );
	}

	/**
	 * A check that scheduled nothing must be retried, not cached.
	 */
	public function test_a_failed_check_is_retried_on_the_next_request() {
		$module = $this->setup_module();

		// refuse both schedulers.
		add_filter( 'pre_as_schedule_recurring_action', '__return_zero' );
		add_filter( 'schedule_event', '__return_false' );

		$module->maybe_reschedule_refresh_db();
		$this->assertFalse( $this->has_trigger(), 'precondition: nothing could be scheduled' );

		remove_filter( 'pre_as_schedule_recurring_action', '__return_zero' );
		remove_filter( 'schedule_event', '__return_false' );

		$module->maybe_reschedule_refresh_db();

		$this->assertTrue( $this->has_trigger(), 'a check that scheduled nothing must be retried on the next request' );
	}

	/**
	 * A live WP-Cron fallback counts as scheduled, so the window still applies.
	 */
	public function test_a_wp_cron_fallback_is_not_re_attempted_on_every_request() {
		$attempts = 0;
		$refuse   = function () use ( &$attempts ) {
			++$attempts;
			return 0;
		};
		add_filter( 'pre_as_schedule_recurring_action', $refuse );

		$module = $this->setup_module();
		$module->maybe_reschedule_refresh_db();

		$this->assertNotFalse( wp_next_scheduled( self::HOOK ), 'precondition: the fallback is armed' );
		$after_first = $attempts;

		$module->maybe_reschedule_refresh_db();
		remove_filter( 'pre_as_schedule_recurring_action', $refuse );

		$this->assertSame( $after_first, $attempts, 'a live fallback must not be re-attempted on the next request' );
	}

	/**
	 * A refused replacement must not take the old WP-Cron event with it.
	 */
	public function test_a_refused_reschedule_keeps_the_old_wp_cron_event() {
		// an event on another interval.
		wp_schedule_event( time() + HOUR_IN_SECONDS, 'hourly', self::HOOK );
		add_filter( 'pre_as_schedule_recurring_action', '__return_zero' );
		// WP-Cron refuses the replacement.
		add_filter( 'schedule_event', '__return_false' );

		$this->setup_module()->ensure_refresh_db_action();

		$this->assertNotFalse( wp_next_scheduled( self::HOOK ), 'the old event must survive a refused replacement' );
	}

	/**
	 * Every pending refresh action.
	 *
	 * @return list<numeric-string>
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
