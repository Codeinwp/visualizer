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
		add_filter( 'rest_visualizer_query', array( $this, 'add_rest_query_vars' ), 9, 2 );
	}

	/**
	 * Enqueue front end and editor JavaScript and CSS
	 */
	public function enqueue_gutenberg_scripts() {
		global $wp_version;

		$blockPath = VISUALIZER_ABSURL . 'classes/Visualizer/Gutenberg/build/block.js';
		$handsontableJS = VISUALIZER_ABSURL . 'classes/Visualizer/Gutenberg/build/handsontable.js';
		$stylePath = VISUALIZER_ABSURL . 'classes/Visualizer/Gutenberg/build/block.css';
		$handsontableCSS = VISUALIZER_ABSURL . 'classes/Visualizer/Gutenberg/build/handsontable.css';

		if ( VISUALIZER_TEST_JS_CUSTOMIZATION ) {
			$version = filemtime( VISUALIZER_ABSPATH . '/classes/Visualizer/Gutenberg/build/block.js' );
		} else {
			$version = $this->version;
		}

		// Enqueue the bundled block JS file
		wp_enqueue_script( 'handsontable', $handsontableJS );
		wp_enqueue_script( 'visualizer-gutenberg-block', $blockPath, array( 'wp-api', 'handsontable', 'visualizer-datatables', 'moment' ), $version, true );

		$type = 'community';

		if ( Visualizer_Module::is_pro() ) {
			$type = 'pro';
			if ( apply_filters( 'visualizer_is_business', false ) ) {
				$type = 'business';
			}
		}

		$table_col_mapping  = Visualizer_Source_Query_Params::get_all_db_tables_column_mapping( null, false );

		$translation_array = array(
			'isPro'     => $type,
			'proTeaser' => Visualizer_Plugin::PRO_TEASER_URL,
			'absurl'    => VISUALIZER_ABSURL,
			'charts'    => Visualizer_Module_Admin::_getChartTypesLocalized(),
			'adminPage' => menu_page_url( 'visualizer', false ),
			'sqlTable'  => $table_col_mapping,
			'chartsPerPage' => defined( 'TI_CYPRESS_TESTING' ) ? 20 : 6,
		);
		wp_localize_script( 'visualizer-gutenberg-block', 'visualizerLocalize', $translation_array );

		// Enqueue frontend and editor block styles
		wp_enqueue_style( 'handsontable', $handsontableCSS );
		wp_enqueue_style( 'visualizer-gutenberg-block', $stylePath, array( 'visualizer-datatables' ), $version );

		if ( version_compare( $wp_version, '4.9.0', '>' ) ) {

			wp_enqueue_code_editor(
				array(
					'type' => 'sql',
					'codemirror' => array(
						'autofocus'         => true,
						'lineWrapping'      => true,
						'dragDrop'          => false,
						'matchBrackets'     => true,
						'autoCloseBrackets' => true,
						'extraKeys'         => array( 'Ctrl-Space' => 'autocomplete' ),
						'hintOptions'       => array( 'tables' => $table_col_mapping ),
					),
				)
			);
		}
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
					'lazy' => array(
						'type' => 'string',
					),
				),
			)
		);
	}

	/**
	 * Gutenberg Block Callback Function
	 */
	public function gutenberg_block_callback( $atts ) {
		// no id, no fun.
		if ( ! isset( $atts['id'] ) ) {
			return '';
		}

		$atts = shortcode_atts(
			array(
				'id'     => false,
				'lazy' => apply_filters( 'visualizer_lazy_by_default', false, $atts['id'] ),
				// we are deliberating excluding the class attribute from here
				// as this will be handled by the custom class in Gutenberg
			),
			$atts
		);

		// no id, no fun.
		if ( ! $atts['id'] ) {
			return '';
		}

		// phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
		if ( $atts['lazy'] == -1 || $atts['lazy'] == false ) {
			$atts['lazy'] = 'no';
		}

		// we don't want the chart in the editor lazy-loading.
		if ( is_admin() ) {
			unset( $atts['lazy'] );
		}

		$shortcode = '[visualizer';
		foreach ( $atts as $name => $value ) {
			$shortcode .= sprintf( ' %s="%s"', $name, $value );
		}
		$shortcode .= ']';

		return $shortcode;
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
			'/get-query-data',
			array(
				'methods'  => 'GET',
				'callback' => array( $this, 'get_query_data' ),
				'permission_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
			)
		);

		register_rest_route(
			'visualizer/v' . VISUALIZER_REST_VERSION,
			'/get-json-root',
			array(
				'methods'  => 'GET',
				'callback' => array( $this, 'get_json_root_data' ),
				'args'     => array(
					'url' => array(
						'sanitize_callback' => 'esc_url_raw',
					),
				),
				'permission_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
			)
		);

		register_rest_route(
			'visualizer/v' . VISUALIZER_REST_VERSION,
			'/get-json-data',
			array(
				'methods'  => 'GET',
				'callback' => array( $this, 'get_json_data' ),
				'args'     => array(
					'url' => array(
						'sanitize_callback' => 'esc_url_raw',
					),
					'chart' => array(
						'sanitize_callback' => 'absint',
					),
				),
				'permission_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
			)
		);

		register_rest_route(
			'visualizer/v' . VISUALIZER_REST_VERSION,
			'/set-json-data',
			array(
				'methods'  => 'GET',
				'callback' => array( $this, 'set_json_data' ),
				'args'     => array(
					'url' => array(
						'sanitize_callback' => 'esc_url_raw',
					),
				),
				'permission_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
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
				'permission_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
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
				'permission_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
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
				'permission_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
			)
		);
	}

	/**
	 * Get Post Meta Fields
	 */
	public function get_visualizer_data( $post ) {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return false;
		}

		$data = array();
		$post_id = $post['id'];

		$data['visualizer-chart-type'] = get_post_meta( $post_id, Visualizer_Plugin::CF_CHART_TYPE, true );

		$library = get_post_meta( $post_id, Visualizer_Plugin::CF_CHART_LIBRARY, true );
		$data['visualizer-chart-library'] = $library;

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

		// we are going to format only for tabular charts, because we are not sure of the effect on others.
		// this is to solve the case where boolean data shows up as all-ticks on gutenberg.
		if ( in_array( $data['visualizer-chart-type'], array( 'tabular' ), true ) ) {
			$data['visualizer-data'] = $this->format_chart_data( $data['visualizer-data'], $data['visualizer-series'] );
		}

		$data['visualizer-data-exploded'] = '';
		// handle annotations for google charts
		if ( 'GoogleCharts' === $library ) {
			// this will contain the data of both axis.
			$settings = $data['visualizer-settings'];
			// this will contain data only of Y axis.
			$series = $data['visualizer-series'];
			$annotations = array();
			if ( isset( $settings['series'] ) ) {
				foreach ( $settings['series'] as $index => $serie ) {
					// skip X axis data.
					if ( $index === 0 ) {
						continue;
					}
					if ( ! empty( $serie['role'] ) ) {
						// this series is some kind of annotation, so let's collect its index.
						// the index will be +1 because the X axis value is index 0, which is being ignored.
						$annotations[ 'role' . ( intval( $index ) + 1 ) ] = $serie['role'];
					}
				}
			}
			if ( ! empty( $annotations ) ) {
				$exploded_data = array();
				$series_names = array();
				foreach ( $series as $index => $serie ) {
					// skip X axis data.
					if ( $index === 0 ) {
						continue;
					}
					if ( array_key_exists( 'role' . $index, $annotations ) ) {
						$series_names[] = (object) array( 'role' => $annotations[ 'role' . $index ], 'type' => $serie['type'] );
					} else {
						$series_names[] = $serie['label'];
					}
				}
				$exploded_data[] = $series_names;

				foreach ( $data['visualizer-data'] as $datum ) {
					// skip X axis data.
					unset( $datum[0] );
					$exploded_data[] = $datum;
				}
				$data['visualizer-data-exploded'] = array( $exploded_data );
			}
		}

		$import = get_post_meta( $post_id, Visualizer_Plugin::CF_CHART_URL, true );

		$schedule = get_post_meta( $post_id, Visualizer_Plugin::CF_CHART_SCHEDULE, true );

		$db_schedule = get_post_meta( $post_id, Visualizer_Plugin::CF_DB_SCHEDULE, true );

		$db_query = get_post_meta( $post_id, Visualizer_Plugin::CF_DB_QUERY, true );

		$json_url = get_post_meta( $post_id, Visualizer_Plugin::CF_JSON_URL, true );

		$json_headers = get_post_meta( $post_id, Visualizer_Plugin::CF_JSON_HEADERS, true );

		$json_schedule = get_post_meta( $post_id, Visualizer_Plugin::CF_JSON_SCHEDULE, true );

		$json_root = get_post_meta( $post_id, Visualizer_Plugin::CF_JSON_ROOT, true );

		$json_paging = get_post_meta( $post_id, Visualizer_Plugin::CF_JSON_PAGING, true );

		if ( ! empty( $import ) && ! empty( $schedule ) ) {
			$data['visualizer-chart-url'] = $import;
			$data['visualizer-chart-schedule'] = $schedule;
		}

		if ( ! empty( $db_schedule ) && ! empty( $db_query ) ) {
			$data['visualizer-db-schedule'] = $db_schedule;
			$data['visualizer-db-query'] = $db_query;
		}

		if ( ! empty( $json_url ) ) {
			$data['visualizer-json-schedule'] = $json_schedule;
			$data['visualizer-json-url'] = $json_url;
			$data['visualizer-json-headers'] = $json_headers;
			$data['visualizer-json-root'] = $json_root;

			if ( Visualizer_Module::is_pro() && ! empty( $json_paging ) ) {
				$data['visualizer-json-paging'] = $json_paging;
			}
		}

		if ( Visualizer_Module::is_pro() ) {
			$permissions = get_post_meta( $post_id, Visualizer_PRO::CF_PERMISSIONS, true );

			if ( empty( $permissions ) ) {
				$permissions = array( 'permissions' => array(
						'read'          => 'all',
						'edit'          => 'roles',
						'edit-specific' => array( 'administrator' ),
				),
				);
			}

			$data['visualizer-permissions'] = $permissions;
		}

		return $data;
	}

	/**
	 * Returns the data for the query.
	 *
	 * @access public
	 */
	public function get_query_data( $data ) {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return false;
		}

		$source = new Visualizer_Source_Query( stripslashes( $data['query'] ) );
		$html = $source->fetch( true );
		$source->fetch( false );
		$name = $source->getSourceName();
		$series = $source->getSeries();
		$data = $source->getRawData();
		$error = '';
		if ( empty( $html ) ) {
			$error = $source->get_error();
			wp_send_json_error( array( 'msg' => $error ) );
		}
		wp_send_json_success( array( 'table' => $html, 'name' => $name, 'series' => $series, 'data' => $data ) );
	}

	/**
	 * Returns the JSON root.
	 *
	 * @access public
	 */
	public function get_json_root_data( $data ) {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return false;
		}

		$source = new Visualizer_Source_Json( $data );

		$roots = $source->fetchRoots();
		if ( empty( $roots ) ) {
			wp_send_json_error( array( 'msg' => $source->get_error() ) );
		}

		wp_send_json_success( array( 'url' => $data['url'], 'roots' => $roots ) );
	}

	/**
	 * Returns the JSON data.
	 *
	 * @access public
	 */
	public function get_json_data( $data ) {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return false;
		}

		$chart_id = $data['chart'];

		if ( empty( $chart_id ) ) {
			wp_die();
		}

		$source = new Visualizer_Source_Json( $data );
		$source->fetch();
		$table = $source->getRawData();

		if ( empty( $table ) ) {
			wp_send_json_error( array( 'msg' => esc_html__( 'Unable to fetch data from the endpoint. Please try again.', 'visualizer' ) ) );
		}

		$table = Visualizer_Render_Layout::show( 'editor-table', $table, $chart_id, 'viz-json-table', false, false );
		wp_send_json_success( array( 'table' => $table, 'root' => $data['root'], 'url' => $data['url'], 'paging' => $source->getPaginationElements() ) );
	}

	/**
	 * Set the JSON data.
	 *
	 * @access public
	 */
	public function set_json_data( $data ) {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return false;
		}

		$source = new Visualizer_Source_Json( $data );

		$table = $source->fetch();
		if ( empty( $table ) ) {
			wp_send_json_error( array( 'msg' => esc_html__( 'Unable to fetch data from the endpoint. Please try again.', 'visualizer' ) ) );
		}

		$source->fetchFromEditableTable();
		$name = $source->getSourceName();
		$series = json_encode( $source->getSeries() );
		$data = json_encode( $source->getRawData() );
		wp_send_json_success( array( 'name' => $name, 'series' => $series, 'data' => $data ) );
	}

	/**
	 * Rest Callback Method
	 */
	public function update_chart_data( $data ) {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return false;
		}

		if ( $data['id'] && ! is_wp_error( $data['id'] ) ) {
			if ( get_post_type( $data['id'] ) !== Visualizer_Plugin::CPT_VISUALIZER ) {
				return new WP_Error( 'invalid_post_type', 'Invalid post type.' );
			}
			$chart_type = sanitize_text_field( $data['visualizer-chart-type'] );
			$source_type = sanitize_text_field( $data['visualizer-source'] );

			update_post_meta( $data['id'], Visualizer_Plugin::CF_CHART_TYPE, $chart_type );
			update_post_meta( $data['id'], Visualizer_Plugin::CF_SOURCE, $source_type );
			update_post_meta( $data['id'], Visualizer_Plugin::CF_DEFAULT_DATA, $data['visualizer-default-data'] );
			update_post_meta( $data['id'], Visualizer_Plugin::CF_SERIES, $data['visualizer-series'] );
			update_post_meta( $data['id'], Visualizer_Plugin::CF_SETTINGS, $data['visualizer-settings'] );

			if ( $data['visualizer-chart-url'] && $data['visualizer-chart-schedule'] ) {
				$chart_url = esc_url_raw( $data['visualizer-chart-url'] );
				$chart_schedule = intval( $data['visualizer-chart-schedule'] );
				update_post_meta( $data['id'], Visualizer_Plugin::CF_CHART_URL, $chart_url );
				apply_filters( 'visualizer_pro_chart_schedule', $data['id'], $chart_url, $chart_schedule );
			} else {
				delete_post_meta( $data['id'], Visualizer_Plugin::CF_CHART_URL );
				apply_filters( 'visualizer_pro_remove_schedule', $data['id'] );
			}

			// let's check if this is not an external db chart
			// as there is no support for that in the block editor interface
			$external_params = get_post_meta( $data['id'], Visualizer_Plugin::CF_REMOTE_DB_PARAMS, true );
			if ( empty( $external_params ) ) {
				if ( $source_type === 'Visualizer_Source_Query' ) {
					$db_schedule = intval( $data['visualizer-db-schedule'] );
					$db_query = $data['visualizer-db-query'];
					update_post_meta( $data['id'], Visualizer_Plugin::CF_DB_SCHEDULE, $db_schedule );
					update_post_meta( $data['id'], Visualizer_Plugin::CF_DB_QUERY, stripslashes( $db_query ) );
				} else {
					delete_post_meta( $data['id'], Visualizer_Plugin::CF_DB_SCHEDULE );
					delete_post_meta( $data['id'], Visualizer_Plugin::CF_DB_QUERY );
				}
			}

			if ( $source_type === 'Visualizer_Source_Json' ) {
				$json_schedule = intval( $data['visualizer-json-schedule'] );
				$json_url = esc_url_raw( $data['visualizer-json-url'] );
				$json_headers = esc_url_raw( $data['visualizer-json-headers'] );
				$json_root = $data['visualizer-json-root'];
				$json_paging = $data['visualizer-json-paging'];

				update_post_meta( $data['id'], Visualizer_Plugin::CF_JSON_SCHEDULE, $json_schedule );
				update_post_meta( $data['id'], Visualizer_Plugin::CF_JSON_URL, $json_url );
				update_post_meta( $data['id'], Visualizer_Plugin::CF_JSON_HEADERS, $json_headers );
				update_post_meta( $data['id'], Visualizer_Plugin::CF_JSON_ROOT, $json_root );

				if ( ! empty( $json_paging ) ) {
					update_post_meta( $data['id'], Visualizer_Plugin::CF_JSON_PAGING, $json_paging );
				} else {
					delete_post_meta( $data['id'], Visualizer_Plugin::CF_JSON_PAGING );
				}
			} else {
				delete_post_meta( $data['id'], Visualizer_Plugin::CF_JSON_SCHEDULE );
				delete_post_meta( $data['id'], Visualizer_Plugin::CF_JSON_URL );
				delete_post_meta( $data['id'], Visualizer_Plugin::CF_JSON_HEADERS );
				delete_post_meta( $data['id'], Visualizer_Plugin::CF_JSON_ROOT );
				delete_post_meta( $data['id'], Visualizer_Plugin::CF_JSON_PAGING );
			}

			if ( Visualizer_Module::is_pro() ) {
				update_post_meta( $data['id'], Visualizer_PRO::CF_PERMISSIONS, $data['visualizer-permissions'] );
			}

			if ( $data['visualizer-chart-url'] ) {
				$chart_url = esc_url_raw( $data['visualizer-chart-url'] );
				$content['source'] = $chart_url;
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
	 *
	 * Note: No matter how tempted, don't use the similar method from Visualizer_Source. That works on a different structure.
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

			switch ( $row['type'] ) {
				case 'number':
					foreach ( $data as $o => $col ) {
						$data[ $o ][ $i ] = ( is_numeric( $col[ $i ] ) ) ? floatval( $col[ $i ] ) : ( is_numeric( str_replace( ',', '', $col[ $i ] ) ) ? floatval( str_replace( ',', '', $col[ $i ] ) ) : null );
					}
					break;
				case 'boolean':
					foreach ( $data as $o => $col ) {
						$data[ $o ][ $i ] = ! empty( $col[ $i ] ) ? filter_var( $col[ $i ], FILTER_VALIDATE_BOOLEAN ) : false;
					}
					break;
				case 'timeofday':
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
					break;
				case 'string':
					foreach ( $data as $o => $col ) {
						$data[ $o ][ $i ] = $this->toUTF8( $col[ $i ] );
					}
					break;
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
		if ( ! current_user_can( 'edit_posts' ) ) {
			return false;
		}

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
		if ( ! current_user_can( 'edit_posts' ) ) {
			return false;
		}

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

	/**
	 * Filter Rest Query
	 */
	public function add_rest_query_vars( $args, \WP_REST_Request $request ) {
		if ( isset( $request['meta_key'] ) && isset( $request['meta_value'] ) ) {
			$args['meta_query'] = array(
				'relation'  => 'OR',
				array(
					'key'       => $request->get_param( 'meta_key' ),
					'value'     => $request->get_param( 'meta_value' ),
					'compare'   => '!=',
				),
				array(
					'key'       => $request->get_param( 'meta_key' ),
					'value'     => $request->get_param( 'meta_value' ),
					'compare'   => 'NOT EXISTS',
				),
			);
		}
		return $args;
	}
}
