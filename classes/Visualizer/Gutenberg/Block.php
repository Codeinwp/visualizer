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
	 * Enqueue Gutenberg block assets.
	 */
	public function enqueue_gutenberg_scripts() {
		global $pagenow;

		$blockPath = VISUALIZER_ABSURL . 'classes/Visualizer/Gutenberg/build/index.js';
		$stylePath = VISUALIZER_ABSURL . 'classes/Visualizer/Gutenberg/build/style-index.css';
		$asset_path = VISUALIZER_ABSPATH . '/classes/Visualizer/Gutenberg/build/index.asset.php';
		if ( file_exists( $asset_path ) ) {
			// @phpstan-ignore-next-line
			$asset = require $asset_path;
		} else {
			$asset = array(
				'dependencies' => array(),
				'version'      => $this->version,
			);
		}

		if ( VISUALIZER_TEST_JS_CUSTOMIZATION ) {
			$asset['version'] = filemtime( VISUALIZER_ABSPATH . '/classes/Visualizer/Gutenberg/build/index.js' );
		}

		if ( ! wp_script_is( 'visualizer-datatables', 'registered' ) ) {
			wp_register_script( 'visualizer-datatables', VISUALIZER_ABSURL . 'js/lib/datatables.min.js', array( 'jquery-ui-core' ), Visualizer_Plugin::VERSION );
		}

		if ( ! wp_style_is( 'visualizer-datatables', 'registered' ) ) {
			wp_register_style( 'visualizer-datatables', VISUALIZER_ABSURL . 'css/lib/datatables.min.css', array(), Visualizer_Plugin::VERSION );
		}

		// Enqueue the bundled block JS file
		$script_deps = array(
			'wp-api',
			'wp-blocks',
			'wp-block-editor',
			'wp-components',
			'wp-editor',
			'wp-element',
			'wp-i18n',
			'lodash',
			'moment',
			'react',
			'visualizer-datatables',
		);
		if ( isset( $asset['dependencies'] ) && is_array( $asset['dependencies'] ) ) {
			$script_deps = array_merge( $script_deps, $asset['dependencies'] );
		}
		$script_deps = array_values( array_unique( $script_deps ) );
		wp_enqueue_script( 'visualizer-gutenberg-block', $blockPath, $script_deps, $asset['version'], true );

		$translation_array = array(
			'adminPage' => menu_page_url( 'visualizer', false ),
			'createChart' => add_query_arg( array( 'action' => 'visualizer-create-chart', 'library' => 'yes', 'type' => '', 'chart-library' => '', 'tab' => 'visualizer' ), admin_url( 'admin-ajax.php' ) ),
			'chartsPerPage' => defined( 'TI_E2E_TESTING' ) ? 20 : 6,
			'isFullSiteEditor'  => 'site-editor.php' === $pagenow,
			/* translators: %1$s: opening tag, %2$s: closing tag */
			'chartEditUrl'      => admin_url( 'admin-ajax.php' ),
		);
		wp_localize_script( 'visualizer-gutenberg-block', 'visualizerLocalize', $translation_array );

		$d3_renderer_asset = VISUALIZER_ABSPATH . '/classes/Visualizer/D3Renderer/build/index.asset.php';
		if ( file_exists( $d3_renderer_asset ) && ! wp_script_is( 'visualizer-d3-renderer', 'registered' ) ) {
			// @phpstan-ignore-next-line
			$d3_asset = include $d3_renderer_asset;
			wp_register_script(
				'visualizer-d3-renderer',
				VISUALIZER_ABSURL . 'classes/Visualizer/D3Renderer/build/index.js',
				array_merge( $d3_asset['dependencies'], array( 'jquery' ) ),
				$d3_asset['version'],
				true
			);
		}
		if ( wp_script_is( 'visualizer-d3-renderer', 'registered' ) ) {
			wp_enqueue_script( 'visualizer-d3-renderer' );
			wp_localize_script(
				'visualizer-d3-renderer',
				'vizD3Renderer',
				array(
					'iframeJsUrl' => VISUALIZER_ABSURL . 'classes/Visualizer/D3Renderer/build/iframe.js',
				)
			);
		}

		// Enqueue frontend and editor block styles
		wp_enqueue_style( 'visualizer-gutenberg-block', $stylePath, array( 'visualizer-datatables' ), $asset['version'] );

		// Enqueue ChartBuilder (AI Builder) so the D3 edit modal is available in the block editor.
		$chart_builder_asset = VISUALIZER_ABSPATH . '/classes/Visualizer/ChartBuilder/build/index.asset.php';
		if ( file_exists( $chart_builder_asset ) && ! wp_script_is( 'visualizer-chart-builder', 'enqueued' ) ) {
			/**
			 * Ignore missing build asset in source checkout.
			 *
			 * @phpstan-ignore-next-line
			 */
			$cb_asset = include $chart_builder_asset;
			wp_enqueue_script(
				'visualizer-chart-builder',
				VISUALIZER_ABSURL . 'classes/Visualizer/ChartBuilder/build/index.js',
				$cb_asset['dependencies'],
				$cb_asset['version'],
				true
			);
			wp_enqueue_style(
				'visualizer-chart-builder',
				VISUALIZER_ABSURL . 'classes/Visualizer/ChartBuilder/build/style-index.css',
				array(),
				$cb_asset['version']
			);
			$chart_builder_css = VISUALIZER_ABSPATH . '/classes/Visualizer/ChartBuilder/build/index.css';
			if ( file_exists( $chart_builder_css ) ) {
				wp_enqueue_style(
					'visualizer-chart-builder-runtime',
					VISUALIZER_ABSURL . 'classes/Visualizer/ChartBuilder/build/index.css',
					array( 'visualizer-chart-builder' ),
					$cb_asset['version']
				);
			}
			wp_localize_script(
				'visualizer-chart-builder',
				'vizAIBuilder',
				array(
					'ajaxUrl' => admin_url( 'admin-ajax.php' ),
					'nonce'   => wp_create_nonce( 'visualizer-ai-builder' ),
					'isPro'   => Visualizer_Module::is_pro(),
				)
			);
			// Inject the mount point the ChartBuilder React app needs.
			wp_add_inline_script(
				'visualizer-chart-builder',
				'document.body.insertAdjacentHTML("beforeend","<div id=\"viz-chart-builder-root\"></div>");',
				'before'
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

		if ( $atts['lazy'] === '-1' || $atts['lazy'] === false ) {
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
		if ( 'd3' === $library ) {
			$data['visualizer-d3-code'] = get_post_meta( $post_id, Visualizer_Module_AIBuilder::CF_D3_CODE, true );
		}

		$data['visualizer-source'] = get_post_meta( $post_id, Visualizer_Plugin::CF_SOURCE, true );

		$data['visualizer-default-data'] = get_post_meta( $post_id, Visualizer_Plugin::CF_DEFAULT_DATA, true );

		// faetch and update settings
		$data['visualizer-settings'] = get_post_meta( $post_id, Visualizer_Plugin::CF_SETTINGS, true );
		if ( empty( $data['visualizer-settings']['pagination'] ) ) {
			$data['visualizer-settings']['pageSize'] = '';
		}
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

		if ( ! isset( $data['visualizer-settings']['hAxis']['format'] ) ) {
			$data['visualizer-settings']['hAxis']['format'] = '';
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

						if ( isset( $data['visualizer-series'][ intval( $index ) + 1 ] ) ) {
							$data['visualizer-series'][ intval( $index ) + 1 ]['role'] = $serie['role'];
						}
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

		if ( ! empty( $import ) && $schedule >= 0 ) {
			$data['visualizer-chart-url']      = $import;
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
			$permissions = get_post_meta( $post_id, Visualizer_Pro::CF_PERMISSIONS, true );

			if ( empty( $permissions ) ) {
				$permissions = array(
					'permissions' => array(
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
	 * Format chart data.
	 *
	 * Note: No matter how tempted, don't use the similar method from Visualizer_Source. That works on a different structure.
	 */
	public function format_chart_data( $data, $series ) {
		foreach ( $series as $i => $row ) {
			// if no value exists for the series, then add null
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
