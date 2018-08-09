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
 * Frontend module class.
 *
 * @category Visualizer
 * @package Module
 *
 * @since 1.0.0
 */
class Visualizer_Module_Gutenberg extends Visualizer_Module {

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

		$this->_addAction( 'enqueue_block_editor_assets', 'enqueue_block_editor_assets' );
		$this->_addAction( 'init', 'register_block' );
		$this->_addAction( 'rest_api_init', 'register_endpoints' );
		$this->_addAjaxAction( Visualizer_Plugin::ACTION_SAVE_SETTINGS, 'save_settings' );
	}


	/**
	 * Load block assets for the editor.
	 */
	public function enqueue_block_editor_assets() {
		wp_enqueue_script(
			'visualizer-block',
			VISUALIZER_ABSURL . 'js/gutenberg/block.build.js',
			array( 'wp-i18n', 'wp-blocks', 'wp-components' ),
			filemtime( VISUALIZER_ABSPATH . '/js/gutenberg/block.build.js' )
		);

		wp_localize_script(
			'visualizer-block', 'vjs', array(
				'i10n'  => array(
					'plugin'    => 'Visualizer',
					'loading'   => __( 'Loading', 'visualizer' ) . '...',
				),
				'urls'  => array(
					'create_form'   => 'visualizer/v' . intval( VISUALIZER_REST_VERSION ) . '/create_form/',
					'create_chart'  => get_rest_url( null, 'visualizer/v' . intval( VISUALIZER_REST_VERSION ) . '/create_chart/' ),
					'get_chart' => 'visualizer/v' . intval( VISUALIZER_REST_VERSION ) . '/get_chart/#/#/',
				),
				'nonce'     => wp_create_nonce( 'wp_rest' ),
				'ajax'      => array(
					'nonce'         => wp_create_nonce( Visualizer_Plugin::ACTION_SAVE_SETTINGS . Visualizer_Plugin::VERSION ),
					'save_settings' => Visualizer_Plugin::ACTION_SAVE_SETTINGS,
				),
			)
		);

		wp_enqueue_script( 'visualizer-google-jsapi-new', '//www.gstatic.com/charts/loader.js', array(), null, true );
		wp_enqueue_script( 'visualizer-google-jsapi-old', '//www.google.com/jsapi', array( 'visualizer-google-jsapi-new' ), null, true );
		wp_enqueue_script( 'visualizer-render', VISUALIZER_ABSURL . 'js/render.js', array( 'visualizer-google-jsapi-old', 'jquery' ), Visualizer_Plugin::VERSION, true );
		wp_enqueue_script( 'visualizer-preview', VISUALIZER_ABSURL . 'js/preview.js', array( 'wp-color-picker', 'visualizer-render' ), Visualizer_Plugin::VERSION, true );

		wp_enqueue_style( 'visualizer-block-css', VISUALIZER_ABSURL . '/css/gutenberg/block.css' );
		wp_enqueue_style( 'visualizer-frame-css', VISUALIZER_ABSURL . '/css/frame.css' );
	}

	/**
	 * Register the REST endpoints.
	 */
	public function register_endpoints() {
		register_rest_route(
			'visualizer', '/v' . intval( VISUALIZER_REST_VERSION ) . '/create_form/',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'create_form' ),
			)
		);
		register_rest_route(
			'visualizer', '/v' . intval( VISUALIZER_REST_VERSION ) . '/create_chart/',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'create_chart' ),
			)
		);
		register_rest_route(
			'visualizer', '/v' . intval( VISUALIZER_REST_VERSION ) . '/get_chart/(?P<id>\d+)/(?P<random>\d+)/',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_chart' ),
			)
		);
	}

	/**
	 * Register the block.
	 */
	public function register_block() {
		register_block_type(
			'visualizer/chart', array(
				'render_callback' => array( $this, 'render_block' ),
			)
		);
	}


	/**
	 * Render the pirate form block.
	 */
	function render_block( $atts = null ) {
		global $wp_version;

		$attributes  = array();
		if ( is_array( $atts ) && $atts ) {
			if ( array_key_exists( 'chart_id', $atts ) ) {
				$attributes['id'] = $atts['chart_id'];
			}
			if ( array_key_exists( 'random', $atts ) ) {
				$attributes['random'] = $atts['random'];
			}
		} elseif ( $atts ) {
			$attributes['id'] = $atts;
		}

		$params     = '';
		if ( $attributes ) {
			foreach ( $attributes as $key => $value ) {
				$params .= " $key=$value";
			}
		}

		return do_shortcode( "[visualizer $params]" );
	}

	/**
	 * Get the HTML for the chart.
	 */
	function get_chart( WP_REST_Request $request ) {
		$return = $this->validate_params( $request, array( 'id', 'random' ) );
		if ( is_wp_error( $return ) ) {
			return $return;
		}

		$id         = $return['id'];
		$random     = $return['random'];
		$charts     = $this->get_chart_data( $id, $random );
		return new WP_REST_Response( array( 'chart_id' => $id, 'random' => $random, 'charts' => $charts['charts'], 'settings' => $charts['settings'] ) );
	}

	/**
	 * Get the HTML for chart creation form.
	 */
	function create_form( WP_REST_Request $request ) {
		$render = new Visualizer_Render_Templates();
		$render->setTemplateName( 'gutenberg-create-chart-form' );
		$html   = $render->toHtml();

		return new WP_REST_Response( array( 'html' => $html ) );
	}

	/**
	 * Save settings of the chart.
	 */
	function save_settings() {
		check_ajax_referer( Visualizer_Plugin::ACTION_SAVE_SETTINGS . Visualizer_Plugin::VERSION, 'nonce' );

		$_SERVER['REQUEST_METHOD']  = 'POST';

		$_GET   = array(
			'chart' => $_POST['id'],
			'tab'   => 'settings',
			'nonce' => wp_create_nonce(),
		);

		$settings_array = array();
		$settings   = $_POST['settings'];
		parse_str( $settings, $settings_array );

		$_POST      = $settings_array;

		if ( ! defined( 'VISUALIZER_DO_NOT_DIE' ) ) {
			define( 'VISUALIZER_DO_NOT_DIE', true );
		}

		do_action( 'wp_ajax_' . Visualizer_Plugin::ACTION_EDIT_CHART );
		return new WP_REST_Response();
	}


	/**
	 * Get the HTML for chart creation.
	 */
	function create_chart( WP_REST_Request $request ) {
		$return = $this->validate_params( $request, array( 'type', 'source' ) );
		if ( is_wp_error( $return ) ) {
			return $return;
		}

		$_files = $request->get_file_params();
		$_post  = $_POST;

		$_POST  = array();

		$_GET   = array(
			'type'  => $return['type'],
		);

		if ( ! defined( 'VISUALIZER_DO_NOT_DIE' ) ) {
			define( 'VISUALIZER_DO_NOT_DIE', true );
		}

		$chart_id           = null;
		$source             = $return['source'];

		// handle special case.
		if ( 'existing' !== $source ) {
			do_action( 'wp_ajax_' . Visualizer_Plugin::ACTION_CREATE_CHART );

			// lets get the new chart created.
			$query                  = new WP_Query(
				array(
					'post_author'  => get_current_user_id(),
					'post_status'  => 'auto-draft',
					'post_type'    => Visualizer_Plugin::CPT_VISUALIZER,
					'post_title'   => 'Visualization',
					'posts_per_page'    => 1,
					'fields'        => 'ids',
					'orderby'       => 'post_date',
					'order'         => 'DESC',
				)
			);
			if ( $query->have_posts() ) {
				while ( $query->have_posts() ) {
					$query->the_post();
					$chart_id   = $query->post;
				}
			}
		}

		$upload_data        = true;

		switch ( $source ) {
			case 'csv':
				$_FILES['local_data']       = $_files['file'];
				break;
			case 'url':
				$return = $this->validate_params( $request, array( 'remote_data' ) );
				if ( is_wp_error( $return ) ) {
					return $return;
				}

				$_POST      = array(
					'remote_data'   => $_post['remote_data'],
				);
				break;
			case 'chart':
				$return = $this->validate_params( $request, array( 'chart' ) );
				if ( is_wp_error( $return ) ) {
					return $return;
				}

				$source_chart           = get_post( $_post['chart'] );
				$_POST['chart_data']    = $source_chart->post_content;
				break;
			case 'existing':
				$return = $this->validate_params( $request, array( 'chart' ) );
				if ( is_wp_error( $return ) ) {
					return $return;
				}

				$chart_id               = $_post['chart'];
				// fall-through.
			case 'manual':
				$upload_data    = false;
				break;
		}

		$html       = '<div id="canvas"></div>';

		// except existing and manual.
		if ( $upload_data ) {
			$_GET['nonce']  = wp_create_nonce();
			$_GET['chart']  = $chart_id;
			ob_start();
			do_action( 'wp_ajax_' . Visualizer_Plugin::ACTION_UPLOAD_DATA );
			$html       .= ob_get_clean();
		}

		// except existing
		if ( 'existing' != $source ) {
			wp_update_post( array( 'ID' => $chart_id, 'post_status' => 'publish' ) );
		}

		$random         = null;
		$html           .= do_shortcode( "[visualizer id='$chart_id']" );
		// search for the random number that is added to "visualizer-$chartid-" to make "visualizer-$chartid-$random".
		$matches        = array();
		preg_match( "/visualizer\-{$chart_id}\-([0-9]+)/", $html, $matches );
		if ( 2 === count( $matches ) ) {
			$random     = $matches[1];
		}

		$charts     = $this->get_chart_data( $chart_id, $random );
		return new WP_REST_Response( array( 'html' => $html, 'chart_id' => $chart_id, 'random' => $random, 'charts' => $charts['charts'], 'settings' => $charts['settings'] ) );
	}

	/**
	 * Get the chart data to send to render.js.
	 *
	 * @param int $chart_id The id of the chart.
	 * @param int $random The random number added to the chart id to make the container id.
	 */
	private function get_chart_data( $chart_id, $random ) {
		$chart  = get_post( $chart_id );

		// copied from Visualizer_Frontend
		$type       = get_post_meta( $chart_id, Visualizer_Plugin::CF_CHART_TYPE, true );
		$settings   = get_post_meta( $chart_id, Visualizer_Plugin::CF_SETTINGS, true );
		$series     = apply_filters( Visualizer_Plugin::FILTER_GET_CHART_SERIES, get_post_meta( $chart_id, Visualizer_Plugin::CF_SERIES, true ), $chart_id, $type );
		$settings   = apply_filters( Visualizer_Plugin::FILTER_GET_CHART_SETTINGS, $settings, $chart_id, $type );
		$data       = apply_filters( Visualizer_Plugin::FILTER_GET_CHART_DATA, unserialize( $chart->post_content ), $chart_id, $type );

		$id     = sprintf( 'visualizer-%d-%d', $chart_id, $random );

		$chart_data     = apply_filters( Visualizer_Plugin::FILTER_GET_CHART_ARRAY, array(), $chart_id );
		$sidebar       = '';
		$sidebar_class = 'Visualizer_Render_Sidebar_Type_' . ucfirst( $type );
		if ( class_exists( $sidebar_class, true ) ) {
			$sidebar           = new $sidebar_class( $chart_data['settings'] );
			$sidebar->__series = $chart_data['series'];
			$sidebar->__data   = $chart_data['data'];
		} else {
			$sidebar = apply_filters( 'visualizer_pro_chart_type_sidebar', '', $chart_data );
			if ( $sidebar != '' ) {
				$sidebar->__series = $chart_data['series'];
				$sidebar->__data   = $chart_data['data'];
			}
		}

		// add chart to the array
		$charts[ $id ] = array(
			'type'     => $type,
			'series'   => $series,
			'settings' => $settings,
			'data'     => $data,
		);

		return array(
			'charts'    => $charts,
			'settings'  => '<form class="settings-form" method="post" data-chart-id="' . $chart_id . '">
								<ul class="viz-group-wrapper full-height">
									<li class="viz-group viz-group-category sidebar-footer-link open">
										<ul class="viz-group-content">' . $sidebar->__toString() . '</ul>
									</li>
								</ul>
								<input type="button" class="save-settings" value="' . __( 'Save Settings', 'visualizer' ) . '">
							</form>',
		);
	}

	/**
	 * Validate REST params.
	 */
	private function validate_params( WP_REST_Request $request, $params = array() ) {
		$return = array();
		foreach ( $params as $param ) {
			$value = $request->get_param( $param );
			if ( ! is_numeric( $value ) && empty( $value ) ) {
				return new WP_Error( $param . '_invalid', sprintf( __( 'Invalid %s', 'visualizer' ), $param ), array( 'status' => 403 ) );
			} else {
				$return[ $param ] = $value;
			}
		}

		return $return;
	}
}
