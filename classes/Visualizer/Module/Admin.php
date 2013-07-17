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
 * The module for all admin stuff.
 *
 * @category Visualizer
 * @package Module
 *
 * @since 1.0.0
 */
class Visualizer_Module_Admin extends Visualizer_Module {

	const NAME = __CLASS__;

	/**
	 * Library page suffix.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @var string
	 */
	private $_libraryPage;

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

		$this->_addAction( 'load-post.php', 'enqueueMediaScripts' );
		$this->_addAction( 'load-post-new.php', 'enqueueMediaScripts' );
		$this->_addAction( 'admin_footer', 'renderTempaltes' );
		$this->_addAction( 'admin_enqueue_scripts', 'enqueueLibraryScripts' );
		$this->_addAction( 'admin_menu', 'registerAdminMenu' );
		$this->_addAction( 'plugin_action_links', 'getActionLinks', 10, 2 );

		$this->_addFilter( 'media_view_strings', 'setupMediaViewStrings' );
	}

	/**
	 * Enqueues media scripts and styles.
	 *
	 * @since 1.0.0
	 * @uses wp_enqueue_style To enqueue style file.
	 * @uses wp_enqueue_script To enqueue script file.
	 *
	 * @access public
	 */
	public function enqueueMediaScripts() {
		wp_enqueue_style( 'visualizer-media', VISUALIZER_ABSURL . 'css/media.css', array( 'media-views' ), Visualizer_Plugin::VERSION );

		wp_enqueue_script( 'google-jsapi',               '//www.google.com/jsapi',                      array( 'media-editor' ),                null );
		wp_enqueue_script( 'visualizer-media-model',      VISUALIZER_ABSURL . 'js/media/model.js',      array( 'google-jsapi' ),                Visualizer_Plugin::VERSION );
		wp_enqueue_script( 'visualizer-media-collection', VISUALIZER_ABSURL . 'js/media/collection.js', array( 'visualizer-media-model' ),      Visualizer_Plugin::VERSION );
		wp_enqueue_script( 'visualizer-media-controller', VISUALIZER_ABSURL . 'js/media/controller.js', array( 'visualizer-media-collection' ), Visualizer_Plugin::VERSION );
		wp_enqueue_script( 'visualizer-media-view',       VISUALIZER_ABSURL . 'js/media/view.js',       array( 'visualizer-media-controller' ), Visualizer_Plugin::VERSION );
		wp_enqueue_script( 'visualizer-media-toolbar',    VISUALIZER_ABSURL . 'js/media/toolbar.js',    array( 'visualizer-media-view' ),       Visualizer_Plugin::VERSION );
		wp_enqueue_script( 'visualizer-media',            VISUALIZER_ABSURL . 'js/media.js',            array( 'visualizer-media-toolbar' ),    Visualizer_Plugin::VERSION );
	}

	/**
	 * Extends media view strings with visualizer strings.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param array $strings The array of media view strings.
	 * @return array The extended array of media view strings.
	 */
	public function setupMediaViewStrings( $strings ) {
		$strings['visualizer'] = array(
			'actions' => array(
				'get_charts'   => Visualizer_Plugin::ACTION_GET_CHARTS,
				'delete_chart' => Visualizer_Plugin::ACTION_DELETE_CHART,
			),
			'controller' => array(
				'title' => __( 'Visualizations', Visualizer_Plugin::NAME ),
			),
			'routers' => array(
				'library' => __( 'From Library', Visualizer_Plugin::NAME ),
				'create'  => __( 'Create New', Visualizer_Plugin::NAME ),
			),
			'library' => array(
				'filters' => array(
					'all'         => __( 'All', Visualizer_Plugin::NAME ),
					'pie'         => __( 'Pie', Visualizer_Plugin::NAME ),
					'line'        => __( 'Line', Visualizer_Plugin::NAME ),
					'area'        => __( 'Area', Visualizer_Plugin::NAME ),
					'geo'         => __( 'Geo', Visualizer_Plugin::NAME ),
					'bar'         => __( 'Bar', Visualizer_Plugin::NAME ),
					'column'      => __( 'Column', Visualizer_Plugin::NAME ),
					'gauge'       => __( 'Gauge', Visualizer_Plugin::NAME ),
					'scatter'     => __( 'Scatter', Visualizer_Plugin::NAME ),
					'candlestick' => __( 'Candelstick', Visualizer_Plugin::NAME ),
				),
			),
			'nonce'    => Visualizer_Security::createNonce(),
			'buildurl' => add_query_arg( 'action', Visualizer_Plugin::ACTION_CREATE_CHART, admin_url( 'admin-ajax.php' ) ),
		);

		return $strings;
	}

	/**
	 * Renders templates to use in media popup.
	 *
	 * @since 1.0.0
	 * @global string $pagenow The name of the current page.
	 *
	 * @access public
	 */
	public function renderTempaltes() {
		global $pagenow;

		if ( 'post.php' != $pagenow && 'post-new.php' != $pagenow ) {
			return;
		}

		$render = new Visualizer_Render_Templates();
		$render->render();
	}

	/**
	 * Enqueues library scripts and styles.
	 *
	 * @since 1.0.0
	 * @uses wp_enqueue_style() To enqueue library stylesheet.
	 *
	 * @access public
	 * @param string $suffix The current page suffix.
	 */
	public function enqueueLibraryScripts( $suffix ) {
		if ( $suffix == $this->_libraryPage ) {
			wp_enqueue_style( 'visualizer-library', VISUALIZER_ABSURL . 'css/library.css', array(), Visualizer_Plugin::VERSION );
		}
	}

	/**
	 * Registers admin menu for visualizer library.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function registerAdminMenu() {
		$title = esc_html__( 'Visualizer Library', Visualizer_Plugin::NAME );
		$callback = array( $this, 'renderLibraryPage' );
		$this->_libraryPage = add_submenu_page( 'upload.php', $title, $title, 'edit_posts', Visualizer_Plugin::NAME, $callback );
	}

	/**
	 * Renders visualizer library page.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function renderLibraryPage() {
		$render = new Visualizer_Render_Library();
		$render->render();
	}

	/**
	 * Updates the plugin's action links, which will be rendered in the plugins table.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param array $links The array of original action links.
	 * @param string $file The plugin basename.
	 * @return array Updated array of action links.
	 */
	public function getActionLinks( $links, $file ) {
		if ( $file == plugin_basename( VISUALIZER_BASEFILE ) ) {
			array_unshift(
				$links,
				sprintf(
					'<a href="%s">%s</a>',
					admin_url( 'upload.php?page=' . Visualizer_Plugin::NAME ),
					__( 'Library', Visualizer_Plugin::NAME )
				)
			);
		}

		return $links;
	}

}