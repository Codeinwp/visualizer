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
					'plugin'	=> 'Visualizer',
				),
			)
		);

		wp_enqueue_style( 'visualizer-block-css', PIRATEFORMS_URL . 'css/gutenberg/block.css' );
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
		$arributes  = array();
		if ( is_array( $atts ) && $atts ) {
			if ( array_key_exists( 'id', $atts ) ) {
				$attributes['id'] = $atts['form_id'];
			}
		} else {
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
	 * Register the REST endpoints.
	 */
	public function register_endpoints() {
		register_rest_route(
			'visualizer', '/v' . intval( VISUALIZER_REST_VERSION ) . '/get_chart/(?P<id>\d+)/',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_chart' ),
			)
		);
	}

	/**
	 * Get the requested form's HTML content.
	 */
	function get_chart( WP_REST_Request $request ) {
		$return = $this->validate_params( $request, array( 'id' ) );
		if ( is_wp_error( $return ) ) {
			return $return;
		}

		return new WP_REST_Response( array( 'html' => $this->render_block( $request->get_param( 'id' ) ) ) );
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
				$return[] = $value;
			}
		}

		return $return;
	}
}
