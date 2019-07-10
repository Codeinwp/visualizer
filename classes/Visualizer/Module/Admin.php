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
	 *
	 * @param Visualizer_Plugin $plugin The instance of the plugin.
	 */
	public function __construct( Visualizer_Plugin $plugin ) {
		parent::__construct( $plugin );
		$this->_addAction( 'load-post.php', 'enqueueMediaScripts' );
		$this->_addAction( 'load-post-new.php', 'enqueueMediaScripts' );
		$this->_addAction( 'admin_footer', 'renderTemplates' );
		$this->_addAction( 'admin_enqueue_scripts', 'enqueueLibraryScripts', null, 8 );
		$this->_addAction( 'admin_menu', 'registerAdminMenu' );
		$this->_addFilter( 'media_view_strings', 'setupMediaViewStrings' );
		$this->_addFilter( 'plugin_action_links', 'getPluginActionLinks', 10, 2 );
		$this->_addFilter( 'visualizer_logger_data', 'getLoggerData' );
		$this->_addFilter( 'visualizer_get_chart_counts', 'getChartCountsByTypeAndMeta' );
		$this->_addFilter( 'visualizer_feedback_review_trigger', 'feedbackReviewTrigger' );

		// revision support.
		$this->_addFilter( 'wp_revisions_to_keep', 'limitRevisions', null, 10, 2 );
		$this->_addAction( '_wp_put_post_revision', 'addRevision', null, 10, 1 );
		$this->_addAction( 'wp_restore_post_revision', 'restoreRevision', null, 10, 2 );

		$this->_addAction( 'admin_init', 'init' );
	}

	/**
	 * No limits on revisions.
	 */
	public function limitRevisions( $num, $post ) {
		if ( Visualizer_Plugin::CPT_VISUALIZER === $post->post_type ) {
			return -1;
		}
		return $num;
	}

	/**
	 * Add a revision.
	 */
	public function addRevision( $revision_id ) {
		$parent_id = wp_is_post_revision( $revision_id );
		if ( Visualizer_Plugin::CPT_VISUALIZER === get_post_type( $parent_id ) ) {
			// add the meta data to this revision.
			$meta = get_post_meta( $parent_id, '', true );

			if ( $meta ) {
				foreach ( $meta as $key => $value ) {
					if ( 0 === strpos( $key, 'visualizer' ) ) {
						add_metadata( 'post', $revision_id, $key, maybe_unserialize( $value[0] ) );
					}
				}
			}
		}
	}

	/**
	 * Restore a revision.
	 */
	public function restoreRevision( $post_id, $revision_id ) {
		if ( Visualizer_Plugin::CPT_VISUALIZER === get_post_type( $post_id ) ) {
			// get the meta information from the revision.
			$meta = get_metadata( 'post', $revision_id, '', true );

			// delete all meta information from the post before adding.
			$post_meta = get_post_meta( $post_id, '', true );
			if ( $post_meta ) {
				foreach ( $meta as $key => $value ) {
					if ( 0 === strpos( $key, 'visualizer' ) ) {
						delete_post_meta( $post_id, $key );
					}
				}
			}

			if ( $meta ) {
				foreach ( $meta as $key => $value ) {
					if ( 0 === strpos( $key, 'visualizer' ) ) {
						add_post_meta( $post_id, $key, maybe_unserialize( $value[0] ) );
					}
				}
			}
		}
	}

	/**
	 * Admin init.
	 *
	 * @access  public
	 */
	public function init() {
		// phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
		if ( current_user_can( 'edit_posts' ) && current_user_can( 'edit_pages' ) && 'true' == get_user_option( 'rich_editing' ) ) {
			$this->_addFilter( 'mce_external_languages', 'add_tinymce_lang', 10, 1 );
			$this->_addFilter( 'mce_external_plugins', 'tinymce_plugin', 10, 1 );
			$this->_addFilter( 'mce_buttons', 'register_mce_button', 10, 1 );
		}
	}

	/**
	 * Load plugin translation for - TinyMCE API
	 *
	 * @access  public
	 * @param   array $arr  The tinymce_lang array.
	 * @return  array
	 */
	public function add_tinymce_lang( $arr ) {
		$ui_lang = VISUALIZER_ABSPATH . '/classes/Visualizer/Module/Language.php';
		$ui_lang = apply_filters( 'visualizer_ui_lang_filter', $ui_lang );
		$arr[] = $ui_lang;
		return $arr;
	}

	/**
	 * Load custom js options - TinyMCE API
	 *
	 * @access  public
	 * @param   array $plugin_array  The tinymce plugin array.
	 * @return  array
	 */
	public function tinymce_plugin( $plugin_array ) {
		$plugin_array['visualizer_mce_button'] = VISUALIZER_ABSURL . 'js/mce.js';
		return $plugin_array;
	}

	/**
	 * Register new button in the editor
	 *
	 * @access  public
	 * @param   array $buttons  The tinymce buttons array.
	 * @return  array
	 */
	public function register_mce_button( $buttons ) {
		array_push( $buttons, 'visualizer_mce_button' );
		return $buttons;
	}

	/**
	 * Whether to show the feedback review or not.
	 *
	 * @access public
	 */
	public function feedbackReviewTrigger( $dumb ) {
		$query  = new WP_Query(
			array(
				'posts_per_page'        => 50,
				'post_type'             => Visualizer_Plugin::CPT_VISUALIZER,
				'fields'                => 'ids',
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			)
		);

		if ( $query->have_posts() && $query->post_count > 0 ) {
			return true;
		}
		return false;
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
		global $typenow;
		if ( post_type_supports( $typenow, 'editor' ) ) {
			wp_enqueue_style( 'visualizer-media', VISUALIZER_ABSURL . 'css/media.css', array( 'media-views' ), Visualizer_Plugin::VERSION );

			// Load all the assets for the different libraries we support.
			$deps   = array(
				Visualizer_Render_Sidebar_Google::enqueue_assets( array( 'media-editor' ) ),
				Visualizer_Render_Sidebar_Type_DataTable::enqueue_assets( array( 'media-editor' ) ),
			);

			wp_enqueue_script( 'visualizer-media-model', VISUALIZER_ABSURL . 'js/media/model.js', $deps, Visualizer_Plugin::VERSION, true );
			wp_enqueue_script( 'visualizer-media-collection', VISUALIZER_ABSURL . 'js/media/collection.js', array( 'visualizer-media-model' ), Visualizer_Plugin::VERSION, true );
			wp_enqueue_script( 'visualizer-media-controller', VISUALIZER_ABSURL . 'js/media/controller.js', array( 'visualizer-media-collection' ), Visualizer_Plugin::VERSION, true );
			wp_enqueue_script( 'visualizer-media-view', VISUALIZER_ABSURL . 'js/media/view.js', array( 'visualizer-media-controller' ), Visualizer_Plugin::VERSION, true );
			wp_localize_script(
				'visualizer-media-view',
				'visualizer',
				array(
					'i10n' => array(
						'insert'    => __( 'Insert', 'visualizer' ),
					),
				)
			);
			wp_enqueue_script( 'visualizer-media-toolbar', VISUALIZER_ABSURL . 'js/media/toolbar.js', array( 'visualizer-media-view' ), Visualizer_Plugin::VERSION, true );
			wp_enqueue_script( 'visualizer-media', VISUALIZER_ABSURL . 'js/media.js', array( 'visualizer-media-toolbar' ), Visualizer_Plugin::VERSION, true );
		}
	}

	/**
	 * Extends media view strings with visualizer strings.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 *
	 * @param array $strings The array of media view strings.
	 *
	 * @return array The extended array of media view strings.
	 */
	public function setupMediaViewStrings( $strings ) {
		$chart_types = self::_getChartTypesLocalized( true, true, true );
		$strings['visualizer'] = array(
			'actions'    => array(
				'get_charts'   => Visualizer_Plugin::ACTION_GET_CHARTS,
				'delete_chart' => Visualizer_Plugin::ACTION_DELETE_CHART,
			),
			'controller' => array(
				'title' => esc_html__( 'Visualizations', 'visualizer' ),
			),
			'routers'    => array(
				'library' => esc_html__( 'From Library', 'visualizer' ),
				'create'  => esc_html__( 'Create New', 'visualizer' ),
			),
			'library'    => array(
				'filters' => $chart_types,
				'types'   => array_keys( $chart_types ),
			),
			'nonce'      => wp_create_nonce(),
			'buildurl'   => add_query_arg( 'action', Visualizer_Plugin::ACTION_CREATE_CHART, admin_url( 'admin-ajax.php' ) ),
		);

		return $strings;
	}

	/**
	 * Returns associated array of chart types and localized names.
	 *
	 * @since 1.0.0
	 *
	 * @static
	 * @access private
	 * @return array The associated array of chart types with localized names.
	 */
	public static function _getChartTypesLocalized( $enabledOnly = false, $get2Darray = false, $add_select = false, $where = null ) {
		$additional = array();
		if ( $add_select ) {
			$additional['select'] = array(
				'name'    => esc_html__( 'All', 'visualizer' ),
				'enabled' => true,
			);
		}

		$types = array_merge(
			$additional,
			array(
				'dataTable' => array(
					'name'    => esc_html__( 'Table (New)', 'visualizer' ),
					'enabled' => true,
				),
				'pie'         => array(
					'name'    => esc_html__( 'Pie', 'visualizer' ),
					'enabled' => true,
				),
				'line'        => array(
					'name'    => esc_html__( 'Line', 'visualizer' ),
					'enabled' => true,
				),
				'area'        => array(
					'name'    => esc_html__( 'Area', 'visualizer' ),
					'enabled' => true,
				),
				'geo'         => array(
					'name'    => esc_html__( 'Geo', 'visualizer' ),
					'enabled' => true,
				),
				'bar'         => array(
					'name'    => esc_html__( 'Bar', 'visualizer' ),
					'enabled' => true,
				),
				'column'      => array(
					'name'    => esc_html__( 'Column', 'visualizer' ),
					'enabled' => true,
				),
				'scatter'     => array(
					'name'    => esc_html__( 'Scatter', 'visualizer' ),
					'enabled' => true,
				),
				'gauge'       => array(
					'name'    => esc_html__( 'Gauge', 'visualizer' ),
					'enabled' => true,
				),
				'candlestick' => array(
					'name'    => esc_html__( 'Candlestick', 'visualizer' ),
					'enabled' => true,
				),
				// pro types
				'table'       => array(
					'name'    => esc_html__( 'Table (Deprecated)', 'visualizer' ),
					'enabled' => false,
				),
				'timeline'    => array(
					'name'    => esc_html__( 'Timeline', 'visualizer' ),
					'enabled' => false,
				),
				'combo'       => array(
					'name'    => esc_html__( 'Combo', 'visualizer' ),
					'enabled' => false,
				),
			)
		);
		$types = apply_filters( 'visualizer_pro_chart_types', $types );
		if ( $enabledOnly ) {
			$filtered = array();
			foreach ( $types as $type => $array ) {
				if ( ! is_array( $array ) ) {
					// support for old pro
					$array  = array( 'enabled' => true, 'name' => $array );
				}
				if ( ! $array['enabled'] ) {
					continue;
				}
				$filtered[ $type ] = $array;
			}
			$types = $filtered;
		}
		if ( $get2Darray ) {
			$doubleD = array();
			foreach ( $types as $type => $array ) {
				if ( ! is_array( $array ) ) {
					// support for old pro
					$array  = array( 'enabled' => true, 'name' => $array );
				}
				$doubleD[ $type ] = $array['name'];
			}
			$types = $doubleD;
		}

		return self::handleDeprecatedCharts( $types, $enabledOnly, $get2Darray, $where );
	}

	/**
	 * Handle (soon-to-be) deprecated charts.
	 */
	private static function handleDeprecatedCharts( $types, $enabledOnly, $get2Darray, $where ) {
		$deprecated = array();

		switch ( $where ) {
			case 'library':
				// if the user has a Google Table chart, show it as deprecated otherwise remove the option from the library.
				if ( ! self::hasChartType( 'table' ) ) {
					$deprecated[]   = 'table';
					if ( $get2Darray ) {
						$types['dataTable'] = esc_html__( 'Table', 'visualizer' );
					} else {
						$types['dataTable']['name'] = esc_html__( 'Table', 'visualizer' );
					}
				}

				// if a user has a Gauge/Candlestick chart, then let them keep using it.
				if ( ! VISUALIZER_PRO ) {
					if ( ! self::hasChartType( 'gauge' ) ) {
						if ( $get2Darray ) {
							$deprecated[]   = 'gauge';
						} else {
							$types['gauge']['enabled'] = false;
						}
					}
					if ( ! self::hasChartType( 'candlestick' ) ) {
						if ( $get2Darray ) {
							$deprecated[]   = 'candlestick';
						} else {
							$types['candlestick']['enabled'] = false;
						}
					}
				}
				break;
			default:
				// remove the option to create a Google Table chart.
				$deprecated[]   = 'table';

				// rename the new table chart type.
				if ( $get2Darray ) {
					$types['dataTable'] = esc_html__( 'Table', 'visualizer' );
				} else {
					$types['dataTable']['name'] = esc_html__( 'Table', 'visualizer' );
				}

				// if a user has a Gauge/Candlestick chart, then let them keep using it.
				if ( ! VISUALIZER_PRO ) {
					if ( ! self::hasChartType( 'gauge' ) ) {
						if ( $get2Darray ) {
							$deprecated[]   = 'gauge';
						} else {
							$types['gauge']['enabled'] = false;
						}
					}
					if ( ! self::hasChartType( 'candlestick' ) ) {
						if ( $get2Darray ) {
							$deprecated[]   = 'candlestick';
						} else {
							$types['candlestick']['enabled'] = false;
						}
					}
				}
		}

		if ( $deprecated ) {
			foreach ( $deprecated as $type ) {
				unset( $types[ $type ] );
			}
		}

		return $types;
	}

	/**
	 * Renders templates to use in media popup.
	 *
	 * @since 1.0.0
	 * @global string $pagenow The name of the current page.
	 *
	 * @access public
	 */
	public function renderTemplates() {
		global $pagenow;
		if ( 'post.php' !== $pagenow && 'post-new.php' !== $pagenow ) {
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
	 * @uses wp_enqueue_script() To enqueue javascript file.
	 * @uses wp_enqueue_media() To enqueue media stuff.
	 *
	 * @access public
	 *
	 * @param string $suffix The current page suffix.
	 */
	public function enqueueLibraryScripts( $suffix ) {
		if ( $suffix === $this->_libraryPage ) {
			wp_enqueue_style( 'visualizer-library', VISUALIZER_ABSURL . 'css/library.css', array(), Visualizer_Plugin::VERSION );
			$this->_addFilter( 'media_upload_tabs', 'setupVisualizerTab' );
			wp_enqueue_media();
			wp_enqueue_script(
				'visualizer-library',
				VISUALIZER_ABSURL . 'js/library.js',
				array(
					'jquery',
					'media-views',
				),
				Visualizer_Plugin::VERSION,
				true
			);

			wp_enqueue_script( 'visualizer-customization', $this->get_user_customization_js(), array(), null, true );

			$query = $this->getQuery();
			while ( $query->have_posts() ) {
				$chart = $query->next_post();
				$library = $this->load_chart_type( $chart->ID );
				if ( is_null( $library ) ) {
					continue;
				}
				wp_enqueue_script(
					"visualizer-render-$library",
					VISUALIZER_ABSURL . 'js/render-facade.js',
					apply_filters( 'visualizer_assets_render', array( 'visualizer-library', 'visualizer-customization' ), true ),
					Visualizer_Plugin::VERSION,
					true
				);
			}
		}
	}

	/**
	 * Adds visualizer tab for media upload tabs array.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 *
	 * @param array $tabs The array of media upload tabs.
	 *
	 * @return array Extended array of media upload tabs.
	 */
	public function setupVisualizerTab( $tabs ) {
		$tabs['visualizer'] = 'Visualizer';

		return $tabs;
	}

	/**
	 * Registers admin menu for visualizer library.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function registerAdminMenu() {
		$title              = esc_html__( 'Visualizer Library', 'visualizer' );
		$callback           = array( $this, 'renderLibraryPage' );
		$this->_libraryPage = add_submenu_page( 'upload.php', $title, $title, 'edit_posts', Visualizer_Plugin::NAME, $callback );
	}

	/**
	 * Get the instance of WP_Query that fetches the charts as per the given criteria.
	 */
	private function getQuery() {
		static $q;
		if ( ! is_null( $q ) ) {
			return $q;
		}

		// get current page
		$page = filter_input(
			INPUT_GET,
			'vpage',
			FILTER_VALIDATE_INT,
			array(
				'options' => array(
					'min_range' => 1,
					'default'   => 1,
				),
			)
		);
		// the initial query arguments to fetch charts
		$query_args = array(
			'post_type'      => Visualizer_Plugin::CPT_VISUALIZER,
			'posts_per_page' => 6,
			'paged'          => $page,
		);
		// add chart type filter to the query arguments
		$filter = filter_input( INPUT_GET, 'type' );
		if ( $filter && in_array( $filter, Visualizer_Plugin::getChartTypes(), true ) ) {
			$query_args['meta_query'] = array(
				array(
					'key'     => Visualizer_Plugin::CF_CHART_TYPE,
					'value'   => $filter,
					'compare' => '=',
				),
			);
		} else {
			$filter = 'all';
		}
		// Added by Ash/Upwork
		$filterByMeta = filter_input( INPUT_GET, 's', FILTER_SANITIZE_STRING );
		if ( $filterByMeta ) {
			$query                    = array(
				'key'     => Visualizer_Plugin::CF_SETTINGS,
				'value'   => $filterByMeta,
				'compare' => 'LIKE',
			);
			$meta                     = isset( $query_args['meta_query'] ) ? $query_args['meta_query'] : array();
			$meta[]                   = $query;
			$query_args['meta_query'] = $meta;
		}
		$q = new WP_Query( $query_args );
		return $q;
	}

	/**
	 * Renders visualizer library page.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function renderLibraryPage() {
		$charts = array();
		$query = $this->getQuery();

		// get current page
		$page = filter_input(
			INPUT_GET,
			'vpage',
			FILTER_VALIDATE_INT,
			array(
				'options' => array(
					'min_range' => 1,
					'default'   => 1,
				),
			)
		);
		// add chart type filter to the query arguments
		$filter = filter_input( INPUT_GET, 'type' );
		if ( ! ( $filter && in_array( $filter, Visualizer_Plugin::getChartTypes(), true ) ) ) {
			$filter = 'all';
		}

		$css        = '';
		while ( $query->have_posts() ) {
			$chart = $query->next_post();

			// if the user has updated a chart and instead of saving it, has closed the modal. If the user refreshes, they should get the original chart.
			$chart = $this->handleExistingRevisions( $chart->ID, $chart );
			// refresh a "live" db query chart.
			$chart = apply_filters( 'visualizer_schedule_refresh_chart', $chart, $chart->ID, false );

			$type   = get_post_meta( $chart->ID, Visualizer_Plugin::CF_CHART_TYPE, true );

			// fetch and update settings
			$settings = get_post_meta( $chart->ID, Visualizer_Plugin::CF_SETTINGS, true );

			$settings = apply_filters( Visualizer_Plugin::FILTER_GET_CHART_SETTINGS, $settings, $chart->ID, $type );
			if ( ! empty( $atts['settings'] ) ) {
				$settings = apply_filters( $atts['settings'], $settings, $chart->ID, $type );
			}

			unset( $settings['height'], $settings['width'] );
			$series = apply_filters( Visualizer_Plugin::FILTER_GET_CHART_SERIES, get_post_meta( $chart->ID, Visualizer_Plugin::CF_SERIES, true ), $chart->ID, $type );
			$data   = apply_filters( Visualizer_Plugin::FILTER_GET_CHART_DATA, unserialize( html_entity_decode( $chart->post_content ) ), $chart->ID, $type );

			$library = $this->load_chart_type( $chart->ID );

			$id         = 'visualizer-' . $chart->ID;
			$arguments  = $this->get_inline_custom_css( $id, $settings );
			if ( ! empty( $arguments ) ) {
				$css        .= $arguments[0];
				$settings   = $arguments[1];
			}

			// add chart to the array
			$charts[ $id ] = array(
				'id'       => $chart->ID,
				'type'     => $type,
				'series'   => $series,
				'settings' => $settings,
				'data'     => $data,
				'library'  => $library,
			);
		}
		// enqueue charts array
		$ajaxurl = admin_url( 'admin-ajax.php' );
		wp_localize_script(
			'visualizer-library',
			'visualizer',
			array(
				'language'  => $this->get_language(),
				'map_api_key' => get_option( 'visualizer-map-api-key' ),
				'charts' => $charts,
				'urls'   => array(
					'base'   => add_query_arg( 'vpage', false ),
					'create' => add_query_arg(
						array(
							'action'  => Visualizer_Plugin::ACTION_CREATE_CHART,
							'library' => 'yes',
							'type'      => isset( $_GET['type'] ) ? $_GET['type'] : '',
						),
						$ajaxurl
					),
					'edit'   => add_query_arg(
						array(
							'action'  => Visualizer_Plugin::ACTION_EDIT_CHART,
							'library' => 'yes',
						),
						$ajaxurl
					),
				),
				'page_type' => 'library',
				'is_front'  => false,
			)
		);
		// render library page
		$render             = new Visualizer_Render_Library();
		$render->charts     = $charts;
		$render->type       = $filter;
		$render->types      = self::_getChartTypesLocalized( false, false, false, true );
		$render->custom_css     = $css;
		$render->pagination = paginate_links(
			array(
				'base'    => add_query_arg( 'vpage', '%#%' ),
				'format'  => '',
				'current' => $page,
				'total'   => $query->max_num_pages,
				'type'    => 'array',
			)
		);
		$render->render();
	}

	/**
	 * Updates the plugin's action links, which will be rendered at the plugins table.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 *
	 * @param array  $links The array of original action links.
	 * @param string $file The plugin basename.
	 *
	 * @return array Updated array of action links.
	 */
	public function getPluginActionLinks( $links, $file ) {
		if ( $file === plugin_basename( VISUALIZER_BASEFILE ) ) {
			array_unshift(
				$links,
				sprintf(
					'<a href="%s">%s</a>',
					admin_url( 'upload.php?page=' . Visualizer_Plugin::NAME ),
					esc_html__( 'Library', 'visualizer' )
				)
			);
		}

		return $links;
	}

	/**
	 * Updates the plugin's meta links, which will be rendered at the plugins table.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 *
	 * @param array  $plugin_meta The array of a plugin meta links.
	 * @param string $plugin_file The plugin's basename.
	 *
	 * @return array Updated array of plugin meta links.
	 */
	public function getPluginMetaLinks( $plugin_meta, $plugin_file ) {
		if ( $plugin_file === plugin_basename( VISUALIZER_BASEFILE ) ) {
			// knowledge base link
			$plugin_meta[] = sprintf(
				'<a href="https://github.com/codeinwp/visualizer/wiki" target="_blank">%s</a>',
				esc_html__( 'Knowledge Base', 'visualizer' )
			);
			// flattr link
			$plugin_meta[] = sprintf(
				'<a style="color:red" href="https://themeisle.com/plugins/visualizer-charts-and-graphs-pro-addon/" target="_blank">%s</a>',
				esc_html__( 'Pro Addon', 'visualizer' )
			);
		}

		return $plugin_meta;
	}

}
