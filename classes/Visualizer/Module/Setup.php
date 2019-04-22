<?php

// +----------------------------------------------------------------------+
// | Copyright 2013  Madpixels  (email : visualizer@madpixels.net)        |
// +----------------------------------------------------------------------+
// | This program is free software; you can redistribute it and/or modify |
// | it under the terms of the GNU General Public License, version 2, as  |
// | published by the Free Software Foundation.                           |
// |                                                                      |
// | This program is distributed in the hope that it will be useful,      |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of       |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the        |
// | GNU General Public License for more details.                         |
// |                                                                      |
// | You should have received a copy of the GNU General Public License    |
// | along with this program; if not, write to the Free Software          |
// | Foundation, Inc., 51 Franklin St, Fifth Floor, Boston,               |
// | MA 02110-1301 USA                                                    |
// +----------------------------------------------------------------------+
// | Author: Eugene Manuilov <eugene@manuilov.org>                        |
// +----------------------------------------------------------------------+
/**
 * General module what setups all required environment.
 *
 * @category Visualizer
 * @package Module
 *
 * @since 1.0.0
 */
class Visualizer_Module_Setup extends Visualizer_Module {

	const NAME = __CLASS__;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param Visualizer_Plugin $plugin The instance of the plugin.
	 */
	public function __construct( Visualizer_Plugin $plugin ) {
		parent::__construct( $plugin );

		register_activation_hook( VISUALIZER_BASEFILE, array( $this, 'activate' ) );
		register_deactivation_hook( VISUALIZER_BASEFILE, array( $this, 'deactivate' ) );
		$this->_addAction( 'visualizer_schedule_refresh_db', 'refreshDbChart' );
		$this->_addFilter( 'visualizer_schedule_refresh_chart', 'refresh_db_for_chart', 10, 3 );

		$this->_addAction( 'activated_plugin', 'onActivation' );
		$this->_addAction( 'init', 'setupCustomPostTypes' );
		$this->_addAction( 'plugins_loaded', 'loadTextDomain' );
		$this->_addFilter( 'visualizer_logger_data', 'getLoggerData' );
		$this->_addFilter( 'visualizer_get_chart_counts', 'getChartCountsByTypeAndMeta' );

	}
	/**
	 * Fetches the SDK logger data.
	 *
	 * @param array $data The default data that needs to be sent.
	 *
	 * @access public
	 */
	public function getLoggerData( $data ) {
		return $this->getChartCountsByTypeAndMeta();
	}

	/**
	 * Fetches the types of charts created and their counts.
	 *
	 * @param array $meta_keys An array of name vs. meta keys - to return how many charts have these keys.
	 * @access private
	 */
	public function getChartCountsByTypeAndMeta( $meta_keys = array() ) {
		$charts                 = array();
		$charts['chart_types']  = array();
		// the initial query arguments to fetch charts
		$query_args = array(
			'post_type'         => Visualizer_Plugin::CPT_VISUALIZER,
			'posts_per_page'    => 300,
			'fields'            => 'ids',
			'no_rows_found'     => false,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,

		);

		$query  = new WP_Query( $query_args );
		while ( $query->have_posts() ) {
			$chart_id   = $query->next_post();
			$type       = get_post_meta( $chart_id, Visualizer_Plugin::CF_CHART_TYPE, true );
			$charts['chart_types'][ $type ]    = isset( $charts['chart_types'][ $type ] ) ? $charts['chart_types'][ $type ] + 1 : 1;
			if ( ! empty( $meta_keys ) ) {
				foreach ( $meta_keys as $name => $key ) {
					$data   = get_post_meta( $chart_id, $key, true );
					if ( ! empty( $data ) ) {
						$charts[ $name ] = isset( $charts[ $name ] ) ? $charts[ $name ] + 1 : 1;
					} else {
						$charts[ $name ] = 0;
					}
				}
			}
		}
		return $charts;
	}

	/**
	 * Registers custom post type for charts.
	 *
	 * @since 1.0.0
	 * @uses register_post_type() To register custom post type for charts.
	 *
	 * @access public
	 */
	public function setupCustomPostTypes() {
		register_post_type(
			Visualizer_Plugin::CPT_VISUALIZER,
			array(
				'label'  => 'Visualizer Charts',
				'public' => false,
				'supports' => array( 'revisions' ),
				'show_in_rest'          => true,
				'rest_base'             => 'visualizer',
				'rest_controller_class' => 'WP_REST_Posts_Controller',
			)
		);
	}

	/**
	 * Loads plugin text domain translations.
	 *
	 * @since 1.0.0
	 * @uses load_plugin_textdomain() To load translations for the plugin.
	 *
	 * @access public
	 */
	public function loadTextDomain() {
		load_plugin_textdomain( Visualizer_Plugin::NAME, false, dirname( plugin_basename( VISUALIZER_BASEFILE ) ) . '/languages/' );
	}

	/**
	 * Activate the plugin
	 */
	public function activate() {
		wp_clear_scheduled_hook( 'visualizer_schedule_refresh_db' );
		wp_schedule_event( strtotime( 'midnight' ) - get_option( 'gmt_offset' ) * HOUR_IN_SECONDS, 'hourly', 'visualizer_schedule_refresh_db' );
	}

	/**
	 * On activation of the plugin
	 */
	public function onActivation( $plugin ) {
		if ( defined( 'TI_UNIT_TESTING' ) ) {
			return;
		}

		if ( $plugin === VISUALIZER_BASENAME ) {
			wp_redirect( admin_url( 'upload.php?page=' . Visualizer_Plugin::NAME ) );
			exit();
		}
	}


	/**
	 * Deactivate the plugin
	 */
	public function deactivate() {
		wp_clear_scheduled_hook( 'visualizer_schedule_refresh_db' );
	}

	/**
	 * Refresh the specific chart from the db.
	 *
	 * @param WP_Post $chart The chart object.
	 * @param int     $chart_id The chart id.
	 * @param bool    $force If this is true, then the chart data will be force refreshed. If false, data will be refreshed only if the chart requests live data.
	 *
	 * @access public
	 */
	public function refresh_db_for_chart( $chart, $chart_id, $force = false ) {
		if ( ! $chart_id ) {
			return $chart;
		}

		if ( ! $chart ) {
			$chart = get_post( $chart_id );
		}

		if ( ! $chart ) {
			return $chart;
		}

		// check if the source is correct.
		$source     = get_post_meta( $chart_id, Visualizer_Plugin::CF_SOURCE, true );
		$load_series = false;
		switch ( $source ) {
			case 'Visualizer_Source_Query':
				// check if its a live-data chart or a cached-data chart.
				if ( ! $force ) {
					$hours = get_post_meta( $chart_id, Visualizer_Plugin::CF_DB_SCHEDULE, true );
					if ( ! empty( $hours ) ) {
						// cached, bail!
						return $chart;
					}
				}

				$params     = get_post_meta( $chart_id, Visualizer_Plugin::CF_DB_QUERY, true );
				$source     = new Visualizer_Source_Query( $params );
				$source->fetch( false );
				$load_series = true;
				break;
			case 'Visualizer_Source_Json':
				// check if its a live-data chart or a cached-data chart.
				if ( ! $force ) {
					$hours = get_post_meta( $chart_id, Visualizer_Plugin::CF_JSON_SCHEDULE, true );
					if ( ! empty( $hours ) ) {
						// cached, bail!
						return $chart;
					}
				}

				$url        = get_post_meta( $chart_id, Visualizer_Plugin::CF_JSON_URL, true );
				$root       = get_post_meta( $chart_id, Visualizer_Plugin::CF_JSON_ROOT, true );
				$paging     = get_post_meta( $chart_id, Visualizer_Plugin::CF_JSON_PAGING, true );
				$series     = get_post_meta( $chart_id, Visualizer_Plugin::CF_SERIES, true );
				$source     = new Visualizer_Source_Json( array( 'url' => $url, 'root' => $root, 'paging' => $paging ) );
				$source->refresh( $series );
				break;
			default:
				return $chart;
		}

		$error      = $source->get_error();
		if ( empty( $error ) ) {
			add_filter( 'wp_revisions_to_keep', '__return_false' );
			if ( $load_series ) {
				update_post_meta( $chart_id, Visualizer_Plugin::CF_SERIES, $source->getSeries() );
			}

			wp_update_post(
				array(
					'ID'            => $chart_id,
					'post_content'  => $source->getData(),
				)
			);

			$chart = get_post( $chart_id );
		}

		return $chart;
	}

	/**
	 * Refresh the db chart.
	 *
	 * @access public
	 */
	public function refreshDbChart() {
		$schedules = get_option( Visualizer_Plugin::CF_DB_SCHEDULE, array() );
		if ( ! $schedules ) {
			return;
		}
		if ( ! defined( 'VISUALIZER_DO_NOT_DIE' ) ) {
			// define this so that the ajax call does not die
			// this means that if the new version of pro and the old version of free are installed, only the first chart will be updated
			define( 'VISUALIZER_DO_NOT_DIE', true );
		}

		$new_schedules = array();
		$now           = time();
		foreach ( $schedules as $chart_id => $time ) {
			$new_schedules[ $chart_id ] = $time;
			if ( $time > $now ) {
				continue;
			}

			// if the time is nigh, we force an update.
			$this->refresh_db_for_chart( null, $chart_id, true );
			$hours                      = get_post_meta( $chart_id, Visualizer_Plugin::CF_DB_SCHEDULE, true );
			$new_schedules[ $chart_id ] = time() + $hours * HOUR_IN_SECONDS;
		}
		update_option( Visualizer_Plugin::CF_DB_SCHEDULE, $new_schedules );
	}

}
