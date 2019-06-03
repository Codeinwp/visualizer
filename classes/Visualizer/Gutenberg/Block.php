<?php
// +----------------------------------------------------------------------+
// | Copyright 2018  ThemeIsle (email : friends@themeisle.com)            |
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
// | Author: Hardeep Asrani <hardeep@themeisle.com>                       |
// +----------------------------------------------------------------------+
/**
 * All the Gutenberg block related code.
 *
 * @category Visualizer
 * @package Gutenberg
 *
 * @since 3.1.0
 */
class Visualizer_Gutenberg_Block {

	/**
	 * A reference to an instance of this class.
	 *
	 * @var Visualizer_Gutenberg_Block The one Visualizer_Gutenberg_Block instance.
	 */
	private static $instance;

	/**
	 * Visualizer plugin version.
	 *
	 * @var string $version The current version of the plugin.
	 */
	protected $version;

	/**
	 * Returns an instance of this class.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new Visualizer_Gutenberg_Block();
		}
		return self::$instance;
	}

	/**
	 * Initializes the plugin by setting filters and administration functions.
	 */
	private function __construct() {
		$this->version = Visualizer_Plugin::VERSION;
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_gutenberg_scripts' ) );
		add_action( 'init', array( $this, 'register_block_type' ) );
		add_action( 'rest_api_init', array( $this, 'register_rest_endpoints' ) );
	}

	/**
	 * Enqueue front end and editor JavaScript and CSS
	 */
	public function enqueue_gutenberg_scripts() {
		$blockPath = VISUALIZER_ABSURL . 'classes/Visualizer/Gutenberg/build/block.js';
		$handsontableJS = VISUALIZER_ABSURL . 'classes/Visualizer/Gutenberg/build/handsontable.js';
		$stylePath = VISUALIZER_ABSURL . 'classes/Visualizer/Gutenberg/build/block.css';
		$handsontableCSS = VISUALIZER_ABSURL . 'classes/Visualizer/Gutenberg/build/handsontable.css';

		if ( VISUALIZER_TEST_JS_CUSTOMIZATION ) {
			$version = filemtime( VISUALIZER_ABSPATH . 'classes/Visualizer/Gutenberg/build/block.js' );
		} else {
			$version = $this->version;
		}

		// Enqueue the bundled block JS file
		wp_enqueue_script( 'handsontable', $handsontableJS );
		wp_enqueue_script( 'visualizer-gutenberg-block', $blockPath, array( 'wp-api', 'handsontable' ), $version, true );

		$type = 'community';

		if ( VISUALIZER_PRO ) {
			$type = 'pro';
			if ( apply_filters( 'visualizer_is_business', false ) ) {
				$type = 'developer';
			}
		}

		$translation_array = array(
			'isPro'     => $type,
			'proTeaser' => Visualizer_Plugin::PRO_TEASER_URL,
			'absurl'    => VISUALIZER_ABSURL,
		);
		wp_localize_script( 'visualizer-gutenberg-block', 'visualizerLocalize', $translation_array );

		// Enqueue frontend and editor block styles
		wp_enqueue_style( 'handsontable', $handsontableCSS );
		wp_enqueue_style( 'visualizer-gutenberg-block', $stylePath, '', $version );
	}
	/**
	 * Hook server side rendering into render callback
	 */
	public function register_block_type() {
		register_block_type(
			'visualizer/chart', array(
				'render_callback' => array( $this, 'gutenberg_block_callback' ),
				'attributes'      => array(
					'id' => array(
						'type' => 'number',
					),
				),
			)
		);
	}

	/**
	 * Gutenberg Block Callback Function
	 */
	public function gutenberg_block_callback( $attr ) {
		if ( isset( $attr['id'] ) ) {
			$id = $attr['id'];
			if ( empty( $id ) || $id === 'none' ) {
				return ''; // no id = no fun
			}
			return '[visualizer id="' . $id . '"]';
		}
	}

	/**
	 * Hook server side rendering into render callback
	 */
	public function register_rest_endpoints() {
		register_rest_field(
			'visualizer',
			'chart_data',
			array(
				'get_callback' => array( $this, 'get_visualizer_data' ),
			)
		);

		register_rest_route(
			'visualizer/v' . VISUALIZER_REST_VERSION,
			'/update-chart',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'update_chart_data' ),
				'args'     => array(
					'id' => array(
						'sanitize_callback' => 'absint',
					),
				),
			)
		);

		register_rest_route(
			'visualizer/v' . VISUALIZER_REST_VERSION,
			'/upload-data',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'upload_csv_data' ),
				'args'     => array(
					'url' => array(
						'sanitize_callback' => 'esc_url_raw',
					),
				),
			)
		);

		register_rest_route(
			'visualizer/v' . VISUALIZER_REST_VERSION,
			'/get-permission-data',
			array(
				'methods'  => 'GET',
				'callback' => array( $this, 'get_permission_data' ),
				'args'     => array(
					'type' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		);
	}

	/**
	 * Get Post Meta Fields
	 */
	public function get_visualizer_data( $post ) {
		$data = array();
		$post_id = $post['id'];

		$data['visualizer-chart-type'] = get_post_meta( $post_id, Visualizer_Plugin::CF_CHART_TYPE, true );

		$data['visualizer-source'] = get_post_meta( $post_id, Visualizer_Plugin::CF_SOURCE, true );

		$data['visualizer-default-data'] = get_post_meta( $post_id, Visualizer_Plugin::CF_DEFAULT_DATA, true );

		// faetch and update settings
		$data['visualizer-settings'] = get_post_meta( $post_id, Visualizer_Plugin::CF_SETTINGS, true );

		// handle series filter hooks
		$data['visualizer-series'] = apply_filters( Visualizer_Plugin::FILTER_GET_CHART_SERIES, get_post_meta( $post_id, Visualizer_Plugin::CF_SERIES, true ), $post_id, $data['visualizer-chart-type'] );

		// handle settings filter hooks
		$data['visualizer-settings'] = apply_filters( Visualizer_Plugin::FILTER_GET_CHART_SETTINGS, $data['visualizer-settings'], $post_id, $data['visualizer-chart-type'] );

		// handle data filter hooks
		$data['visualizer-data'] = apply_filters( Visualizer_Plugin::FILTER_GET_CHART_DATA, unserialize( html_entity_decode( get_the_content( $post_id ) ) ), $post_id, $data['visualizer-chart-type'] );

		$import = get_post_meta( $post_id, Visualizer_Plugin::CF_CHART_URL, true );

		$schedule = get_post_meta( $post_id, Visualizer_Plugin::CF_CHART_SCHEDULE, true );

		if ( ! empty( $import ) && ! empty( $schedule ) ) {
			$data['visualizer-chart-url'] = $import;
			$data['visualizer-chart-schedule'] = $schedule;
		}

		if ( VISUALIZER_PRO ) {
			$permissions = get_post_meta( $post_id, Visualizer_PRO::CF_PERMISSIONS, true );

			if ( ! empty( $permissions ) ) {
				$data['visualizer-permissions'] = $permissions;
			}
		}

		return $data;
	}

	/**
	 * Rest Callback Method
	 */
	public function update_chart_data( $data ) {
		if ( $data['id'] && ! is_wp_error( $data['id'] ) ) {

			update_post_meta( $data['id'], Visualizer_Plugin::CF_CHART_TYPE, $data['visualizer-chart-type'] );
			update_post_meta( $data['id'], Visualizer_Plugin::CF_SOURCE, $data['visualizer-source'] );
			update_post_meta( $data['id'], Visualizer_Plugin::CF_DEFAULT_DATA, $data['visualizer-default-data'] );
			update_post_meta( $data['id'], Visualizer_Plugin::CF_SERIES, $data['visualizer-series'] );
			update_post_meta( $data['id'], Visualizer_Plugin::CF_SETTINGS, $data['visualizer-settings'] );

			if ( $data['visualizer-chart-url'] && $data['visualizer-chart-schedule'] ) {
				update_post_meta( $data['id'], Visualizer_Plugin::CF_CHART_URL, $data['visualizer-chart-url'] );
				apply_filters( 'visualizer_pro_chart_schedule', $data['id'], $data['visualizer-chart-url'], $data['visualizer-chart-schedule'] );
			} else {
				delete_post_meta( $data['id'], Visualizer_Plugin::CF_CHART_URL );
				apply_filters( 'visualizer_pro_remove_schedule', $data['id'] );
			}

			if ( VISUALIZER_PRO ) {
				update_post_meta( $data['id'], Visualizer_PRO::CF_PERMISSIONS, $data['visualizer-permissions'] );
			}

			if ( $data['visualizer-chart-url'] ) {
				$content['source'] = $data['visualizer-chart-url'];
				$content['data'] = $this->format_chart_data( $data['visualizer-data'], $data['visualizer-series'] );
			} else {
				$content = $this->format_chart_data( $data['visualizer-data'], $data['visualizer-series'] );
			}

			$chart = array(
				'ID'           => $data['id'],
				'post_content' => serialize( $content ),
			);

			wp_update_post( $chart );

			$revisions = wp_get_post_revisions( $data['id'], array( 'order' => 'ASC' ) );

			if ( count( $revisions ) > 1 ) {
				$revision_ids = array_keys( $revisions );

				// delete all revisions.
				foreach ( $revision_ids as $id ) {
					wp_delete_post_revision( $id );
				}
			}

			return new \WP_REST_Response( array( 'success' => sprintf( 'Chart updated' ) ) );
		}
	}

	/**
	 * Format chart data.
	 */
	public function format_chart_data( $data, $series ) {
		foreach ( $series as $i => $row ) {
			// if no value exists for the seires, then add null
			if ( ! isset( $series[ $i ] ) ) {
				$series[ $i ] = null;
			}

			if ( is_null( $series[ $i ] ) ) {
				continue;
			}

			if ( $row['type'] === 'number' ) {
				foreach ( $data as $o => $col ) {
					$data[ $o ][ $i ] = ( is_numeric( $col[ $i ] ) ) ? floatval( $col[ $i ] ) : ( is_numeric( str_replace( ',', '', $col[ $i ] ) ) ? floatval( str_replace( ',', '', $col[ $i ] ) ) : null );
				}
			}

			if ( $row['type'] === 'boolean' ) {
				foreach ( $data as $o => $col ) {
					$data[ $o ][ $i ] = ! empty( $col[ $i ] ) ? filter_validate( $col[ $i ], FILTER_VALIDATE_BOOLEAN ) : null;
				}
			}

			if ( $row['type'] === 'timeofday' ) {
				foreach ( $data as $o => $col ) {
					$date = new DateTime( '1984-03-16T' . $col[ $i ] );
					if ( $date ) {
						$data[ $o ][ $i ] = array(
							intval( $date->format( 'H' ) ),
							intval( $date->format( 'i' ) ),
							intval( $date->format( 's' ) ),
							0,
						);
					}
				}
			}

			if ( $row['type'] === 'string' ) {
				foreach ( $data as $o => $col ) {
					$data[ $o ][ $i ] = $this->toUTF8( $col[ $i ] );
				}
			}
		}

		return $data;
	}

	/**
	 * Use toUTF8 function
	 */
	public function toUTF8( $datum ) {
		if ( ! function_exists( 'mb_detect_encoding' ) || mb_detect_encoding( $datum ) !== 'ASCII' ) {
			$datum = \ForceUTF8\Encoding::toUTF8( $datum );
		}
		return $datum;
	}

	/**
	 * Handle remote CSV data
	 */
	public function upload_csv_data( $data ) {
		if ( $data['url'] && ! is_wp_error( $data['url'] ) && filter_var( $data['url'], FILTER_VALIDATE_URL ) ) {
			$source = new Visualizer_Source_Csv_Remote( $data['url'] );
			if ( $source->fetch() ) {
				$temp = $source->getData();
				if ( is_string( $temp ) && is_array( unserialize( $temp ) ) ) {
					$content['series'] = $source->getSeries();
					$content['data']   = $source->getRawData();
					return $content;
				} else {
					return new \WP_REST_Response( array( 'failed' => sprintf( 'Invalid CSV URL' ) ) );
				}
			} else {
				return new \WP_REST_Response( array( 'failed' => sprintf( 'Invalid CSV URL' ) ) );
			}
		} else {
			return new \WP_REST_Response( array( 'failed' => sprintf( 'Invalid CSV URL' ) ) );
		}
	}

	/**
	 * Get permission data
	 */
	public function get_permission_data( $data ) {
		$options = array();
		switch ( $data['type'] ) {
			case 'users':
				$query  = new WP_User_Query(
					array(
						'number'        => 1000,
						'orderby'       => 'display_name',
						'fields'        => array( 'ID', 'display_name' ),
						'count_total'   => false,
					)
				);
				$users  = $query->get_results();
				if ( ! empty( $users ) ) {
					$i = 0;
					foreach ( $users as $user ) {
						$options[ $i ]['value'] = $user->ID;
						$options[ $i ]['label'] = $user->display_name;
						$i++;
					}
				}
				break;
			case 'roles':
				if ( ! function_exists( 'get_editable_roles' ) ) {
					require_once ABSPATH . 'wp-admin/includes/user.php';
				}
				$roles  = get_editable_roles();
				if ( ! empty( $roles ) ) {
					$i = 0;
					foreach ( get_editable_roles() as $name => $info ) {
						$options[ $i ]['value'] = $name;
						$options[ $i ]['label'] = $name;
						$i++;
					}
				}
				break;
		}
		return $options;
	}

}
