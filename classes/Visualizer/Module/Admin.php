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
		$this->_addFilter( 'plugin_row_meta', 'getPluginMetaLinks', 10, 2 );
		$this->_addFilter( 'visualizer_logger_data', 'getLoggerData' );
		$this->_addFilter( 'visualizer_feedback_review_trigger', 'feedbackReviewTrigger' );

		// screen pagination
		$this->_addFilter( 'set-screen-option', 'setScreenOptions', 10, 3 );

		// revision support.
		$this->_addFilter( 'wp_revisions_to_keep', 'limitRevisions', null, 10, 2 );
		$this->_addAction( '_wp_put_post_revision', 'addRevision', null, 10, 1 );
		$this->_addAction( 'wp_restore_post_revision', 'restoreRevision', null, 10, 2 );

		$this->_addAction( 'visualizer_chart_schedules_spl', 'addSplChartSchedules', null, 10, 3 );

		$this->_addAction( 'admin_init', 'init' );

		if ( defined( 'TI_CYPRESS_TESTING' ) ) {
			$this->load_cypress_hooks();
		}

	}

	/**
	 * Define the hooks that are needed for cypress.
	 *
	 * @since   3.4.0
	 * @access  private
	 */
	private function load_cypress_hooks() {
		// all charts should load on the same page without pagination.
		add_filter(
			'visualizer_query_args', function( $args ) {
				$args['posts_per_page'] = 20;
				return $args;
			}, 10, 1
		);
	}

	/**
	 * Add disabled `optgroup` schedules to the drop downs.
	 *
	 * @since ?
	 *
	 * @access public
	 *
	 * @param string $feature The feature for which to add schedules.
	 * @param int    $chart_id The chart ID.
	 * @param int    $plan The plan number.
	 */
	public function addSplChartSchedules( $feature, $chart_id, $plan ) {
		if ( apply_filters( 'visualizer_is_business', false ) ) {
			return;
		}

		$license = __( 'PRO', 'visualizer' );
		if ( Visualizer_Module::is_pro() ) {
			switch ( $plan ) {
				case 1:
					$license = __( 'Developer', 'visualizer' );
					break;
			}
		}

		$hours = array(
			'0' => __( 'Live', 'visualizer' ),
			'1'  => __( 'Each hour', 'visualizer' ),
			'12' => __( 'Each 12 hours', 'visualizer' ),
			'24' => __( 'Each day', 'visualizer' ),
			'72' => __( 'Each 3 days', 'visualizer' ),
		);

		switch ( $feature ) {
			case 'json':
			case 'csv':
				// no more schedules if pro is already active.
				if ( Visualizer_Module::is_pro() ) {
					return;
				}
				break;
			case 'wp':
				// fall-through.
			case 'db':
				break;
			default:
				return;
		}

		echo '<optgroup disabled label="' . sprintf( __( 'More in the %s version', 'visualizer' ), $license ) . '">';
		foreach ( $hours as $hour => $desc ) {
			echo '<option disabled>' . $desc . '</option>';
		}
		echo '</optgroup>';
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
			$this->_addFilter( 'tiny_mce_before_init', 'get_strings_for_block', 10, 1 );
		}
	}

	/**
	 * Add the strings required for the TinyMCE buttons for the classic block (not the classic editor).
	 *
	 * @since   ?
	 * @access  friendly
	 */
	function get_strings_for_block( $settings ) {
		$class = new Visualizer_Module_Language();
		$strings         = $class->get_strings();
		$array = array( 'visualizer_tinymce_plugin' => json_encode( $strings ) );
		return array_merge( $settings, $array );
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
				Visualizer_Render_Sidebar_Type_DataTable_Tabular::enqueue_assets( array( 'media-editor' ) ),
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
				'tabular' => array(
					'name'    => esc_html__( 'Table', 'visualizer' ),
					'enabled' => true,
					'supports'  => array( 'Google Charts', 'DataTable' ),
				),
				'pie'         => array(
					'name'    => esc_html__( 'Pie/Donut', 'visualizer' ),
					'enabled' => true,
					'supports'  => array( 'Google Charts', 'ChartJS' ),
				),
				'line'        => array(
					'name'    => esc_html__( 'Line', 'visualizer' ),
					'enabled' => true,
					'supports'  => array( 'Google Charts', 'ChartJS' ),
				),
				'area'        => array(
					'name'    => esc_html__( 'Area', 'visualizer' ),
					'enabled' => true,
					// in ChartJS, the fill option is used to make Line chart an area: https://www.chartjs.org/docs/latest/charts/area.html
					'supports'  => array( 'Google Charts' ),
				),
				'geo'         => array(
					'name'    => esc_html__( 'Geo', 'visualizer' ),
					'enabled' => true,
					'supports'  => array( 'Google Charts' ),
				),
				'bar'         => array(
					'name'    => esc_html__( 'Bar', 'visualizer' ),
					'enabled' => true,
					'supports'  => array( 'Google Charts', 'ChartJS' ),
				),
				'column'      => array(
					'name'    => esc_html__( 'Column', 'visualizer' ),
					'enabled' => true,
					'supports'  => array( 'Google Charts', 'ChartJS' ),
				),
				'bubble'         => array(
					'name'    => esc_html__( 'Bubble', 'visualizer' ),
					'enabled' => true,
					// chartjs' bubble is ugly looking (and it won't work off the default bubble.csv) so it is being excluded for the time being.
					'supports'  => array( 'Google Charts' ),
				),
				'scatter'     => array(
					'name'    => esc_html__( 'Scatter', 'visualizer' ),
					'enabled' => true,
					'supports'  => array( 'Google Charts' ),
				),
				'gauge'       => array(
					'name'    => esc_html__( 'Gauge', 'visualizer' ),
					'enabled' => true,
					'supports'  => array( 'Google Charts' ),
				),
				'candlestick' => array(
					'name'    => esc_html__( 'Candlestick', 'visualizer' ),
					'enabled' => true,
					'supports'  => array( 'Google Charts' ),
				),
				// pro types
				'timeline'    => array(
					'name'    => esc_html__( 'Timeline', 'visualizer' ),
					'enabled' => false,
					'supports'  => array( 'Google Charts', 'ChartJS' ),
				),
				'combo'       => array(
					'name'    => esc_html__( 'Combo', 'visualizer' ),
					'enabled' => false,
					'supports'  => array( 'Google Charts' ),
				),
				'polarArea'       => array(
					'name'    => esc_html__( 'Polar Area', 'visualizer' ),
					'enabled' => false,
					'supports' => array( 'ChartJS' ),
				),
				'radar'       => array(
					'name'    => esc_html__( 'Radar/Spider', 'visualizer' ),
					'enabled' => false,
					'supports' => array( 'ChartJS' ),
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
				// backward compatibility for PRO before v1.9.0
				if ( ! array_key_exists( 'supports', $array ) ) {
					$array['supports'] = array( 'Google Charts' );
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
				// backward compatibility for PRO before v1.9.0
				if ( ! array_key_exists( 'supports', $array ) ) {
					$array['supports'] = array( 'Google Charts' );
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
			case 'types':
				// fall-through
			case 'library':
				// if a user has a Gauge/Candlestick chart, then let them keep using it.
				if ( ! Visualizer_Module::is_pro() ) {
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
				// if a user has a Gauge/Candlestick chart, then let them keep using it.
				if ( ! Visualizer_Module::is_pro() ) {
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
					'clipboard',
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
		$svg_base64_icon = 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4KPHN2ZyB3aWR0aD0iNzdweCIgaGVpZ2h0PSI3N3B4IiB2aWV3Qm94PSIwIDAgNzcgNzciIHZlcnNpb249IjEuMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayI+CiAgICA8IS0tIEdlbmVyYXRvcjogU2tldGNoIDUyLjYgKDY3NDkxKSAtIGh0dHA6Ly93d3cuYm9oZW1pYW5jb2RpbmcuY29tL3NrZXRjaCAtLT4KICAgIDx0aXRsZT5Db21iaW5lZCBTaGFwZTwvdGl0bGU+CiAgICA8ZGVzYz5DcmVhdGVkIHdpdGggU2tldGNoLjwvZGVzYz4KICAgIDxnIGlkPSJQcm9kdWN0LVBhZ2UiIHN0cm9rZT0ibm9uZSIgc3Ryb2tlLXdpZHRoPSIxIiBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPgogICAgICAgIDxnIGlkPSJXb3JkUHJlc3MtcGx1Z2lucyIgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoLTE5MC4wMDAwMDAsIC00MzUuMDAwMDAwKSIgZmlsbD0iIzM5QzNEMiI+CiAgICAgICAgICAgIDxwYXRoIGQ9Ik0yMjguNSw1MTIgQzIwNy4yMzcwMzcsNTEyIDE5MCw0OTQuNzYyOTYzIDE5MCw0NzMuNSBDMTkwLDQ1Mi4yMzcwMzcgMjA3LjIzNzAzNyw0MzUgMjI4LjUsNDM1IEMyNDkuNzYyOTYzLDQzNSAyNjcsNDUyLjIzNzAzNyAyNjcsNDczLjUgQzI2Nyw0OTQuNzYyOTYzIDI0OS43NjI5NjMsNTEyIDIyOC41LDUxMiBaIE0yNDYuODQzNzUsNDgzLjU5MjA1OSBMMjE1LjYyNSw0ODMuNTkyMDU5IEwyMTUuNjI1LDQ2MC44MjY1NDQgQzIxNS42MjUsNDYwLjE2NDU0NyAyMTUuMTA3NTc4LDQ1OS42MjgzNTkgMjE0LjQ2ODc1LDQ1OS42MjgzNTkgTDIxMi4xNTYyNSw0NTkuNjI4MzU5IEMyMTEuNTE3NDIyLDQ1OS42MjgzNTkgMjExLDQ2MC4xNjQ1NDcgMjExLDQ2MC44MjY1NDQgTDIxMSw0ODUuOTg4NDI5IEMyMTEsNDg3LjMxMTY3NCAyMTIuMDM1NTY2LDQ4OC4zODQ3OTggMjEzLjMxMjUsNDg4LjM4NDc5OCBMMjQ2Ljg0Mzc1LDQ4OC4zODQ3OTggQzI0Ny40ODI1NzgsNDg4LjM4NDc5OCAyNDgsNDg3Ljg0ODYxMSAyNDgsNDg3LjE4NjYxMyBMMjQ4LDQ4NC43OTAyNDQgQzI0OCw0ODQuMTI4MjQ2IDI0Ny40ODI1NzgsNDgzLjU5MjA1OSAyNDYuODQzNzUsNDgzLjU5MjA1OSBaIE0yNDQuNTMxMjUsNDYyLjAyNDcyOSBMMjM1Ljk5OTU3LDQ2Mi4wMjQ3MjkgQzIzNC40NTQ1MzEsNDYyLjAyNDcyOSAyMzMuNjgwNTY2LDQ2My45NjA1NDcgMjM0Ljc3MzIyMyw0NjUuMDkyODMyIEwyMzcuMTE0NjI5LDQ2Ny41MTkxNTYgTDIzMS44MTI1LDQ3My4wMTQzMzIgTDIyNi41MTAzNzEsNDY3LjUxOTkwNSBDMjI1LjYwNzA1MSw0NjYuNTgzODIzIDIyNC4xNDI5NDksNDY2LjU4MzgyMyAyMjMuMjQwMzUyLDQ2Ny41MTk5MDUgTDIxOC4yNzY0MjYsNDcyLjY2Mzg2MyBDMjE3LjgyNDc2Niw0NzMuMTMxOTA0IDIxNy44MjQ3NjYsNDczLjg5MDUwNSAyMTguMjc2NDI2LDQ3NC4zNTg1NDYgTDIxOS45MTEwNzQsNDc2LjA1MjQ4IEMyMjAuMzYyNzM0LDQ3Ni41MjA1MjEgMjIxLjA5NDc4NSw0NzYuNTIwNTIxIDIyMS41NDY0NDUsNDc2LjA1MjQ4IEwyMjQuODc1LDQ3Mi42MDI0NTYgTDIzMC4xNzcxMjksNDc4LjA5Njg4MyBDMjMxLjA4MDQ0OSw0NzkuMDMyOTY1IDIzMi41NDQ1NTEsNDc5LjAzMjk2NSAyMzMuNDQ3MTQ4LDQ3OC4wOTY4ODMgTDI0MC4zODQ2NDgsNDcwLjkwNzc3MyBMMjQyLjcyNjA1NSw0NzMuMzM0MDk4IEMyNDMuODE4NzExLDQ3NC40NjYzODIgMjQ1LjY4Njc3Nyw0NzMuNjY0MzQ3IDI0NS42ODY3NzcsNDcyLjA2MzI3MyBMMjQ1LjY4Njc3Nyw0NjMuMjIyOTE0IEMyNDUuNjg3NSw0NjIuNTYwOTE3IDI0NS4xNzAwNzgsNDYyLjAyNDcyOSAyNDQuNTMxMjUsNDYyLjAyNDcyOSBaIiBpZD0iQ29tYmluZWQtU2hhcGUiPjwvcGF0aD4KICAgICAgICA8L2c+CiAgICA8L2c+Cjwvc3ZnPg==';

		$this->_libraryPage = add_menu_page( __( 'Visualizer', 'visualizer' ), __( 'Visualizer', 'visualizer' ), 'edit_posts', Visualizer_Plugin::NAME, array( $this, 'renderLibraryPage' ), $svg_base64_icon, 99.7666 );

		add_submenu_page(
			Visualizer_Plugin::NAME,
			__( 'Chart Library', 'visualizer' ),
			__( 'Chart Library', 'visualizer' ),
			'edit_posts',
			admin_url( 'admin.php?page=' . Visualizer_Plugin::NAME )
		);
		add_submenu_page(
			Visualizer_Plugin::NAME,
			__( 'Add New Chart', 'visualizer' ),
			__( 'Add New Chart', 'visualizer' ),
			'edit_posts',
			admin_url( 'admin.php?page=' . Visualizer_Plugin::NAME . '&vaction=addnew' )
		);
		add_submenu_page(
			Visualizer_Plugin::NAME,
			__( 'Support', 'visualizer' ),
			__( 'Support', 'visualizer' ) . '<span class="dashicons dashicons-editor-help more-features-icon" style="width: 17px; height: 17px; margin-left: 4px; color: #ffca54; font-size: 17px; vertical-align: -3px;"></span>',
			'edit_posts',
			'viz-support',
			array( $this, 'renderSupportPage' )
		);
		remove_submenu_page( Visualizer_Plugin::NAME, Visualizer_Plugin::NAME );

		add_action( "load-{$this->_libraryPage}", array( $this, 'addScreenOptions' ) );
	}

	/**
	 * Adds the screen options for pagination.
	 */
	function addScreenOptions() {
		$screen = get_current_screen();

		// bail if it's some other page.
		if ( ! is_object( $screen ) || $screen->id !== $this->_libraryPage ) {
			return;
		}

		$args = array(
			'label' => __( 'Number of charts per page:', 'visualizer' ),
			'default' => 6,
			'option' => 'visualizer_per_page_library',
		);
		add_screen_option( 'per_page', $args );
	}

	/**
	 * Returns the screen option for pagination.
	 */
	function setScreenOptions( $status, $option, $value ) {
		if ( 'visualizer_per_page_library' === $option ) {
			return $value;
		}
	}

	/**
	 * Adds the display filters to be used in the meta_query while displaying the charts.
	 */
	private function getDisplayFilters( &$query_args ) {
		$query  = array();

		// add chart type filter to the query arguments
		$type = filter_input( INPUT_GET, 'type' );
		if ( $type && in_array( $type, Visualizer_Plugin::getChartTypes(), true ) ) {
			$query[] = array(
				'key'     => Visualizer_Plugin::CF_CHART_TYPE,
				'value'   => $type,
				'compare' => '=',
			);
		}

		// add chart library filter to the query arguments
		$library = filter_input( INPUT_GET, 'library' );
		if ( $library ) {
			$query[] = array(
				'key'     => Visualizer_Plugin::CF_CHART_LIBRARY,
				'value'   => $library,
				'compare' => '=',
			);
		}

		// add date filter to the query arguments
		$date = filter_input( INPUT_GET, 'date' );
		$possible = array_keys( Visualizer_Plugin::getSupportedDateFilter() );
		if ( $date && in_array( $date, $possible, true ) ) {
			$query_args['date_query'] = array(
				'after' => "$date -1 day",
				'inclusive' => true,
			);
		}

		// add source filter to the query arguments
		$source = filter_input( INPUT_GET, 'source' );
		if ( $source ) {
			$source = ucwords( $source );
			$source = "Visualizer_Source_{$source}";
			$query[] = array(
				'key'     => Visualizer_Plugin::CF_SOURCE,
				'value'   => $source,
				'compare' => '=',
			);
		}

		$query_args['meta_query'] = $query;

		$orderby = filter_input( INPUT_GET, 'orderby' );
		$order = filter_input( INPUT_GET, 'order' );
		if ( $orderby ) {
			$query_args['order_by'] = $orderby;
		}
		$query_args['order'] = empty( $order ) ? 'desc' : $order;
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

		$per_page = 6;
		$screen = get_current_screen();
		if ( $screen ) {
			// retrieve the per_page option
			$screen_option = $screen->get_option( 'per_page', 'option' );
			// retrieve the value stored for the current user
			$user = get_current_user_id();
			$per_page = get_user_meta( $user, $screen_option, true );
			if ( empty( $per_page ) || $per_page < 1 ) {
				// nothing set, get the default value
				$per_page = $screen->get_option( 'per_page', 'default' );
			}
		}

		// the initial query arguments to fetch charts
		$query_args = array(
			'post_type'      => Visualizer_Plugin::CPT_VISUALIZER,
			'posts_per_page' => $per_page,
			'paged'          => $page,
		);

		$this->getDisplayFilters( $query_args );

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
		$q = new WP_Query( apply_filters( 'visualizer_query_args', $query_args ) );
		return $q;
	}

	/**
	 * Renders support page.
	 *
	 * @since 3.3.0
	 *
	 * @access public
	 */
	public function renderSupportPage() {
		wp_enqueue_style( 'visualizer-upsell', VISUALIZER_ABSURL . 'css/upsell.css', array(), Visualizer_Plugin::VERSION );
		include_once VISUALIZER_ABSPATH . '/templates/support.php';
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

			if ( $settings ) {
				unset( $settings['height'], $settings['width'], $settings['chartArea'] );
			}

			$series = apply_filters( Visualizer_Plugin::FILTER_GET_CHART_SERIES, get_post_meta( $chart->ID, Visualizer_Plugin::CF_SERIES, true ), $chart->ID, $type );
			$data   = self::get_chart_data( $chart, $type );

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
					'base'   => add_query_arg( array( 'vpage' => false, 'vaction' => false ) ),
					'create' => add_query_arg(
						array(
							'action'  => Visualizer_Plugin::ACTION_CREATE_CHART,
							'library' => 'yes',
							'type'      => isset( $_GET['type'] ) ? $_GET['type'] : '',
							'chart-library'      => isset( $_GET['chart-library'] ) ? $_GET['chart-library'] : '',
							'vaction' => false,
						),
						$ajaxurl
					),
					'edit'   => add_query_arg(
						array(
							'action'  => Visualizer_Plugin::ACTION_EDIT_CHART,
							'library' => 'yes',
							'vaction' => false,
						),
						$ajaxurl
					),
				),
				'page_type' => 'library',
				'is_front'  => false,
				'i10n'          => array(
					'copied'        => __( 'The shortcode has been copied to your clipboard. Hit Ctrl-V/Cmd-V to paste it.', 'visualizer' ),
					'conflict' => __( 'We have detected a potential conflict with another component that prevents Visualizer from functioning properly. Please disable any of the following components if they are activated on your instance: Modern Events Calendar plugin, Acronix plugin. In case the aforementioned components are not activated or you continue to see this error message, please disable all other plugins and enable them one by one to find out the component that is causing the conflict.', 'visualizer' ),
				),
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
				'base'    => add_query_arg( array( 'vpage' => '%#%', 'vaction' => false ) ),
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
					admin_url( 'admin.php?page=' . Visualizer_Plugin::NAME ),
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
				'<a href="' . VISUALIZER_MAIN_DOC . '" target="_blank">%s</a>',
				esc_html__( 'Docs', 'visualizer' )
			);
			// flattr link
			$plugin_meta[] = sprintf(
				'<a style="color:red" href="' . Visualizer_Plugin::PRO_TEASER_URL . '" target="_blank">%s</a>',
				esc_html__( 'Pro Addon', 'visualizer' )
			);
		}

		return $plugin_meta;
	}

}
