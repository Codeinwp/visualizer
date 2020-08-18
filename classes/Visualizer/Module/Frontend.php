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
class Visualizer_Module_Frontend extends Visualizer_Module {

	const NAME = __CLASS__;

	/**
	 * The array of charts to render.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @var array
	 */
	private $_charts = array();

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @uses add_filter() To add "do_shortcode" hook for "widget_text" and "term_description" filters.
	 *
	 * @access public
	 * @param Visualizer_Plugin $plugin The instance of the plugin.
	 */
	public function __construct( Visualizer_Plugin $plugin ) {
		parent::__construct( $plugin );

		$this->_addAction( 'wp_enqueue_scripts', 'enqueueScripts' );
		$this->_addAction( 'visualizer_enqueue_scripts', 'enqueueScripts' );
		$this->_addFilter( 'visualizer_get_language', 'getLanguage' );
		$this->_addShortcode( 'visualizer', 'renderChart' );

		// add do_shortocde hook for widget_text filter
		if ( ! has_filter( 'widget_text', 'do_shortcode' ) ) {
			add_filter( 'widget_text', 'do_shortcode' );
		}

		// add do_shortcode hook for term_description filter
		if ( ! has_filter( 'term_description', 'do_shortcode' ) ) {
			add_filter( 'term_description', 'do_shortcode' );
		}

		add_action( 'rest_api_init', array( $this, 'endpoint_register' ) );

		$this->_addFilter( 'script_loader_tag', 'script_loader_tag', null, 10, 3 );
	}

	/**
	 * Adds the async attribute to certain scripts.
	 */
	function script_loader_tag( $tag, $handle, $src ) {
		if ( is_admin() ) {
			return $tag;
		}

		$scripts    = array( 'google-jsapi', 'visualizer-render-google-lib', 'visualizer-render-google' );

		foreach ( $scripts as $async ) {
			if ( $async === $handle ) {
				$tag = str_replace( ' src', ' defer="defer" src', $tag );
				break;
			}
		}
		return $tag;
	}

	/**
	 * Returns the language/locale.
	 */
	function getLanguage( $dummy, $only_language ) {
		return $this->get_language();
	}


	/**
	 * Registers the endpoints
	 */
	function endpoint_register() {
		register_rest_route(
			'visualizer/v' . VISUALIZER_REST_VERSION,
			'/action/(?P<chart>\d+)/(?P<type>.+)/',
			array(
				// POST is required for save/cancel, GET for all others
				'methods'  => array( 'POST', 'GET' ),
				'args'     => array(
					'chart' => array(
						'required' => true,
						'sanitize_callback' => function( $param ) {
							return is_numeric( $param ) ? $param : null;
						},
					),
					'type' => array(
						'required' => true,
						'type' => 'string',
						'enum' => array_merge( array( 'save', 'cancel' ), array_keys( $this->get_actions() ) ),
					),
				),
				'permission_callback' => function ( WP_REST_Request $request ) {
					$chart_id   = filter_var( sanitize_text_field( $request->get_param( 'chart' ), FILTER_VALIDATE_INT ) );
					if ( ! empty( $chart_id ) && in_array( $request->get_param( 'type' ), array( 'save', 'cancel' ), true ) ) {
						// let save and cancel go without any check as past version of pro
						// did not send the X-WP-Nonce
						// we can change this at a later date.
						return true;
					}
					return ! empty( $chart_id ) && apply_filters( 'visualizer_pro_show_chart', true, $chart_id );
				},
				'callback' => array( $this, 'perform_action' ),
			)
		);
	}

	/**
	 * All possible actions and their labels
	 *
	 * @access private
	 */
	private function get_actions() {
		$actions = apply_filters(
			'visualizer_action_buttons',
			array(
				'print'     => array(
					'label' => esc_html__( 'Print', 'visualizer' ),
					'title' => esc_html__( 'Print Chart', 'visualizer' ),
				),
				'csv'       => array(
					'label' => esc_html__( 'CSV', 'visualizer' ),
					'title' => esc_html__( 'Download as a CSV', 'visualizer' ),
				),
				'xls'       => array(
					'label' => esc_html__( 'Excel', 'visualizer' ),
					'title' => esc_html__( 'Download as a spreadsheet', 'visualizer' ),
				),
				'copy'      => array(
					'label' => esc_html__( 'Copy', 'visualizer' ),
					'title' => esc_html__( 'Copy data', 'visualizer' ),
				),
				'image'      => array(
					'label' => esc_html__( 'Download', 'visualizer' ),
					'title' => esc_html__( 'Download as an image', 'visualizer' ),
				),
			)
		);

		// backward compatibility before label and title attributes were introduced.
		if ( isset( $actions['edit'] ) && is_string( $actions['edit'] ) ) {
			$actions['edit'] = array(
				'label' => esc_html__( 'Edit', 'visualizer' ),
				'title' => esc_html__( 'Edit data', 'visualizer' ),
			);
		}
		return $actions;
	}

	/**
	 * The print button
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function perform_action( WP_REST_Request $params ) {
		$chart_id   = filter_var( sanitize_text_field( $params['chart'] ), FILTER_VALIDATE_INT );
		$type       = sanitize_text_field( $params['type'] );
		$data       = null;

		if ( ! $chart_id ) {
			return new WP_REST_Response( array( 'error' => new WP_Error( __( 'Invalid chart ID', 'visualizer' ) ) ) );
		}

		if ( ! $type ) {
			return new WP_REST_Response( array( 'error' => new WP_Error( __( 'Invalid action', 'visualizer' ) ) ) );
		}

		switch ( $type ) {
			case 'print':
				$data   = $this->_getDataAs( $chart_id, 'print' );
				break;
			case 'csv':
				$data   = $this->_getDataAs( $chart_id, 'csv' );
				break;
			case 'xls':
				$data   = $this->_getDataAs( $chart_id, 'xls' );
				break;
			case 'image':
				$settings = get_post_meta( $chart_id, Visualizer_Plugin::CF_SETTINGS, true );
				$title       = 'visualizer#' . $chart_id;
				if ( ! empty( $settings['title'] ) ) {
					$title  = $settings['title'];
				}
				// for ChartJS, title is an array.
				if ( is_array( $title ) && isset( $title['text'] ) ) {
					$title = $title['text'];
				}
				if ( empty( $title ) ) {
					$title  = 'visualizer#' . $chart_id;
				}
				$data   = array( 'name' => $title );
				break;
			default:
				$data   = apply_filters( 'visualizer_action_data', $data, $chart_id, $type, $params, $this );
				break;
		}

		return new WP_REST_Response( array( 'data' => $data ) );
	}

	/**
	 * Registers scripts for charts rendering.
	 *
	 * @since 1.0.0
	 * @uses wp_register_script To register javascript file.
	 *
	 * @access public
	 */
	public function enqueueScripts() {
		wp_register_script( 'visualizer-customization', $this->get_user_customization_js(), array(), null, true );
		wp_register_style( 'visualizer-front', VISUALIZER_ABSURL . 'css/front.css', array(), Visualizer_Plugin::VERSION );
		do_action( 'visualizer_pro_frontend_load_resources' );
	}

	/**
	 * Returns placeholder for chart according to visualizer shortcode attributes.
	 *
	 * @since 1.0.0
	 * @uses shortcode_atts() To parse income shortocdes.
	 * @uses apply_filters() To filter chart's data and series arrays.
	 * @uses get_post_meta() To fetch chart's meta information.
	 * @uses wp_enqueue_script() To enqueue charts render script.
	 * @uses wp_localize_script() To add chart data to the page inline script.
	 *
	 * @access public
	 * @param array $atts The array of shortcode attributes.
	 */
	public function renderChart( $atts ) {
		global $wp_version;
		$atts = shortcode_atts(
			array(
				// chart id
				'id'     => false,
				// chart class
				'class'  => false,
				// for lazy load
				// set 'yes' to use the default intersection limit (300px)
				// OR set a number (e.g. 700) to use 700px as the intersection limit
				'lazy' => apply_filters( 'visualizer_lazy_by_default', false, $atts['id'] ),
			),
			$atts
		);

		// if empty id or chart does not exists, then return empty string
		if ( ! $atts['id'] || ! ( $chart = get_post( $atts['id'] ) ) || $chart->post_type !== Visualizer_Plugin::CPT_VISUALIZER ) {
			return '';
		}

		// do not show the chart?
		if ( ! apply_filters( 'visualizer_pro_show_chart', true, $atts['id'] ) ) {
			return '';
		}

		// in case revisions exist.
		// phpcs:ignore WordPress.CodeAnalysis.AssignmentInCondition.Found
		if ( true === ( $revisions = $this->undoRevisions( $chart->ID, true ) ) ) {
			$chart = get_post( $chart->ID );
		}

		$id = 'visualizer-' . $atts['id'];
		$defaultClass   = 'visualizer-front';
		$class = apply_filters( Visualizer_Plugin::FILTER_CHART_WRAPPER_CLASS, $atts['class'], $atts['id'] );

		$lazyClass = $atts['lazy'] === 'yes' || ctype_digit( $atts['lazy'] ) ? 'visualizer-lazy' : '';

		$class  = sprintf( '%s %s %s %s', $defaultClass, $class, 'visualizer-front-' . $atts['id'], $lazyClass );
		$attributes = array();
		if ( ! empty( $class ) ) {
			$attributes['class'] = trim( $class );
		}

		if ( ctype_digit( $atts['lazy'] ) ) {
			$attributes['data-lazy-limit'] = $atts['lazy'];
		}

		$type = get_post_meta( $chart->ID, Visualizer_Plugin::CF_CHART_TYPE, true );

		$chart = apply_filters( 'visualizer_schedule_refresh_chart', $chart, $chart->ID, false );

		// fetch and update settings
		$settings = get_post_meta( $chart->ID, Visualizer_Plugin::CF_SETTINGS, true );
		if ( empty( $settings['height'] ) ) {
			$settings['height'] = '400';
		}

		// handle series filter hooks
		$series = apply_filters( Visualizer_Plugin::FILTER_GET_CHART_SERIES, get_post_meta( $chart->ID, Visualizer_Plugin::CF_SERIES, true ), $chart->ID, $type );

		// handle settings filter hooks
		$settings = apply_filters( Visualizer_Plugin::FILTER_GET_CHART_SETTINGS, $settings, $chart->ID, $type );

		// handle data filter hooks
		$data   = self::get_chart_data( $chart, $type );

		$css        = '';
		$arguments  = $this->get_inline_custom_css( $id, $settings );
		if ( ! empty( $arguments ) ) {
			$css        = $arguments[0];
			$settings   = $arguments[1];
		}

		$library    = $this->load_chart_type( $chart->ID );

		$id         = $id . '-' . rand();

		$amp = Visualizer_Plugin::instance()->getModule( Visualizer_Module_AMP::NAME );
		if ( $amp && $amp->is_amp() ) {
			return '<div id="' . $id . '"' . $this->getHtmlAttributes( $attributes ) . '>' . $amp->get_chart( $chart, $data, $series, $settings ) . '</div>';
		}

		// add chart to the array
		$this->_charts[ $id ] = array(
			'type'     => $type,
			'series'   => $series,
			'settings' => $settings,
			'data'     => $data,
			'library'  => $library,
		);

		$actions_div            = '';
		$actions_visible        = apply_filters( 'visualizer_pro_add_actions', isset( $settings['actions'] ) ? $settings['actions'] : array(), $atts['id'] );
		if ( ! empty( $actions_visible ) ) {
			$actions            = $this->get_actions();
			$actions_div        = '<div class="visualizer-actions">';
			foreach ( $actions_visible as $action_type ) {
				$key            = $action_type;
				$mime           = '';
				if ( strpos( $action_type, ';' ) !== false ) {
					$array      = explode( ';', $action_type );
					$key        = $array[0];
					$mime       = end( $array );
				}
				$label          = $actions[ $key ]['label'];
				$title          = $actions[ $key ]['title'];
				$actions_div    .= sprintf( '<a href="#" class="visualizer-action visualizer-action-%s" data-visualizer-type="%s" data-visualizer-chart-id="%s" data-visualizer-container-id="%s" data-visualizer-mime="%s" title="%s"', $key, $key, $atts['id'], $id, $mime, $title );

				if ( 'copy' === $key ) {
					$copy           = $this->_getDataAs( $atts['id'], 'csv' );
					$actions_div    .= ' data-clipboard-text="' . esc_attr( $copy['csv'] ) . '"';
					wp_enqueue_script( 'clipboard' );
				}

				$actions_div    .= apply_filters( 'visualizer_action_attributes', '', $key, $atts['id'] );
				$actions_div    .= '>' . $label . '</a> &nbsp;';
			}

			$actions_div        .= '</div>';
		}

		$actions_div            .= $css;

		foreach ( $this->_charts as $id => $array ) {
			$library = $array['library'];
			wp_register_script(
				"visualizer-render-$library",
				VISUALIZER_ABSURL . 'js/render-facade.js',
				apply_filters( 'visualizer_assets_render', array( 'jquery', 'visualizer-customization' ), true ),
				Visualizer_Plugin::VERSION,
				true
			);

			wp_enqueue_script( "visualizer-render-$library" );
			wp_localize_script(
				"visualizer-render-$library",
				'visualizer',
				array(
					'charts'        => $this->_charts,
					'language'      => $this->get_language(),
					'map_api_key'   => get_option( 'visualizer-map-api-key' ),
					'rest_url'      => version_compare( $wp_version, '4.7.0', '>=' ) ? rest_url( 'visualizer/v' . VISUALIZER_REST_VERSION . '/action/#id#/#type#/' ) : '',
					'wp_nonce'      => wp_create_nonce( 'wp_rest' ),
					'i10n'          => array(
						'copied'        => __( 'The data has been copied to your clipboard. Hit Ctrl-V/Cmd-V in your spreadsheet editor to paste the data.', 'visualizer' ),
					),
					'page_type' => 'frontend',
					'is_front'  => true,
				)
			);
			wp_enqueue_style( 'visualizer-front' );
		}

		// return placeholder div
		return $actions_div . '<div id="' . $id . '"' . $this->getHtmlAttributes( $attributes ) . '></div>' . $this->addSchema( $chart->ID );
	}

	/**
	 * Converts the array of attributes to a string of HTML attributes.
	 *
	 * @since ?
	 *
	 * @access private
	 */
	private function getHtmlAttributes( $attributes ) {
		$string = '';
		if ( ! $attributes ) {
			return $string;
		}

		foreach ( $attributes as $name => $value ) {
			$string .= sprintf( '%s="%s"', esc_attr( $name ), esc_attr( $value ) );
		}
		return $string;
	}

	/**
	 * Adds the schema corresponding to the dataset.
	 *
	 * @since 3.3.2
	 *
	 * @access private
	 */
	private function addSchema( $id ) {
		// should we show informative errors as HTML comments?
		$show_errors = apply_filters( 'visualizer_schema_show_errors', true, $id );

		$settings = get_post_meta( $id, Visualizer_Plugin::CF_SETTINGS, true );
		$title = '';
		if ( isset( $settings['title'] ) && ! empty( $settings['title'] ) ) {
			$title  = $settings['title'];
			if ( is_array( $title ) ) {
				$title  = $settings['title']['text'];
			}
		}
		$title = apply_filters( 'visualizer_schema_name', $title, $id );
		if ( empty( $title ) ) {
			if ( $show_errors ) {
				return "<!-- Not showing structured data for chart $id because title is empty -->";
			}
			return '';
		}

		$desc = '';
		if ( isset( $settings['description'] ) && ! empty( $settings['description'] ) ) {
			$desc  = $settings['description'];
		}
		$desc = apply_filters( 'visualizer_schema_description', $desc, $id );
		if ( empty( $desc ) ) {
			if ( $show_errors ) {
				return "<!-- Not showing structured data for chart $id because description is empty -->";
			}
			return '';
		}

		// descriptions below 50 chars are not allowed.
		if ( strlen( $desc ) < 50 ) {
			if ( $show_errors ) {
				return "<!-- Not showing structured data for chart $id because description is shorter than 50 characters -->";
			}
			return '';
		}

		$schema = apply_filters(
			'visualizer_schema',
			'{
			  "@context":"https://schema.org/",
			  "@type":"Dataset",
			  "name":"' . esc_html( $title ) . '",
			  "description":"' . esc_html( $desc ) . '"
			}',
			$id
		);

		if ( empty( $schema ) ) {
			return '';
		}

		return '<script type="application/ld+json">' . $schema . '</script>';
	}
}
