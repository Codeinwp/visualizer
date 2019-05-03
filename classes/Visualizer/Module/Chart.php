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
 * The module for all stuff related to getting, editing, creating and deleting charts.
 *
 * @category Visualizer
 * @package Module
 *
 * @since 1.0.0
 */
class Visualizer_Module_Chart extends Visualizer_Module {

	const NAME = __CLASS__;

	/**
	 * The chart object.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @var WP_Post
	 */
	private $_chart;

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
		$this->_addAjaxAction( Visualizer_Plugin::ACTION_GET_CHARTS, 'getCharts' );
		$this->_addAjaxAction( Visualizer_Plugin::ACTION_DELETE_CHART, 'deleteChart' );
		$this->_addAjaxAction( Visualizer_Plugin::ACTION_CREATE_CHART, 'renderChartPages' );
		$this->_addAjaxAction( Visualizer_Plugin::ACTION_EDIT_CHART, 'renderChartPages' );
		$this->_addAjaxAction( Visualizer_Plugin::ACTION_UPLOAD_DATA, 'uploadData' );
		$this->_addAjaxAction( Visualizer_Plugin::ACTION_CLONE_CHART, 'cloneChart' );
		$this->_addAjaxAction( Visualizer_Plugin::ACTION_EXPORT_DATA, 'exportData' );

		$this->_addAjaxAction( Visualizer_Plugin::ACTION_FETCH_DB_DATA, 'getQueryData' );
		$this->_addAjaxAction( Visualizer_Plugin::ACTION_SAVE_DB_QUERY, 'saveQuery' );

		$this->_addAjaxAction( Visualizer_Plugin::ACTION_JSON_GET_ROOTS, 'getJsonRoots' );
		$this->_addAjaxAction( Visualizer_Plugin::ACTION_JSON_GET_DATA, 'getJsonData' );
		$this->_addAjaxAction( Visualizer_Plugin::ACTION_JSON_SET_DATA, 'setJsonData' );
		$this->_addAjaxAction( Visualizer_Plugin::ACTION_JSON_SET_SCHEDULE, 'setJsonSchedule' );

		$this->_addAjaxAction( Visualizer_Plugin::ACTION_SAVE_FILTER_QUERY, 'saveFilter' );

	}

	/**
	 * Sets the schedule for how JSON-endpoint charts should be updated.
	 *
	 * @since ?
	 *
	 * @access public
	 */
	public function setJsonSchedule() {
		check_ajax_referer( Visualizer_Plugin::ACTION_JSON_SET_SCHEDULE . Visualizer_Plugin::VERSION, 'security' );

		$chart_id = filter_input(
			INPUT_POST,
			'chart',
			FILTER_VALIDATE_INT,
			array(
				'options' => array(
					'min_range' => 1,
				),
			)
		);

		if ( ! $chart_id ) {
			wp_send_json_error();
		}

		$time = filter_input(
			INPUT_POST,
			'time',
			FILTER_VALIDATE_INT,
			array(
				'options' => array(
					'min_range' => -1,
				),
			)
		);

		delete_post_meta( $chart_id, Visualizer_Plugin::CF_JSON_SCHEDULE );

		if ( -1 < $time ) {
			add_post_meta( $chart_id, Visualizer_Plugin::CF_JSON_SCHEDULE, $time );
		}
		wp_send_json_success();
	}

	/**
	 * Get the root elements for JSON-endpoint.
	 *
	 * @since ?
	 *
	 * @access public
	 */
	public function getJsonRoots() {
		check_ajax_referer( Visualizer_Plugin::ACTION_JSON_GET_ROOTS . Visualizer_Plugin::VERSION, 'security' );

		$params     = wp_parse_args( $_POST['params'] );

		$source = new Visualizer_Source_Json( $params );

		$roots = $source->fetchRoots();
		if ( empty( $roots ) ) {
			wp_send_json_error();
		}

		wp_send_json_success( array( 'url' => $params['url'], 'roots' => $roots ) );
	}

	/**
	 * Get the data for the JSON-endpoint corresponding to the chosen root.
	 *
	 * @since ?
	 *
	 * @access public
	 */
	public function getJsonData() {
		check_ajax_referer( Visualizer_Plugin::ACTION_JSON_GET_DATA . Visualizer_Plugin::VERSION, 'security' );

		$params = wp_parse_args( $_POST['params'] );

		$chart_id = $params['chart'];

		if ( empty( $chart_id ) ) {
			wp_die();
		}

		$source = new Visualizer_Source_Json( $params );

		$data   = $source->parse();
		if ( empty( $data ) ) {
			wp_send_json_error();
		}

		$data   = Visualizer_Render_Layout::show( 'editor-table', $data, $chart_id, 'viz-json-table', false, false );
		wp_send_json_success( array( 'table' => $data, 'root' => $params['root'], 'url' => $params['url'], 'paging' => $source->getPaginationElements() ) );
	}

	/**
	 * Updates the database with the correct post parameters for JSON-endpoint charts.
	 *
	 * @since ?
	 *
	 * @access public
	 */
	public function setJsonData() {
		check_ajax_referer( Visualizer_Plugin::ACTION_JSON_SET_DATA . Visualizer_Plugin::VERSION, 'security' );

		$params = $_POST;
		$chart_id = $_GET['chart'];

		if ( empty( $chart_id ) ) {
			wp_die();
		}

		$chart  = get_post( $chart_id );

		$source = new Visualizer_Source_Json( $params );
		$source->fetch();

		$content    = $source->getData();
		$chart->post_content = $content;
		wp_update_post( $chart->to_array() );
		update_post_meta( $chart->ID, Visualizer_Plugin::CF_SERIES, $source->getSeries() );
		update_post_meta( $chart->ID, Visualizer_Plugin::CF_SOURCE, $source->getSourceName() );
		update_post_meta( $chart->ID, Visualizer_Plugin::CF_DEFAULT_DATA, 0 );
		update_post_meta( $chart->ID, Visualizer_Plugin::CF_JSON_URL, $params['url'] );
		update_post_meta( $chart->ID, Visualizer_Plugin::CF_JSON_ROOT, $params['root'] );
		delete_post_meta( $chart->ID, Visualizer_Plugin::CF_JSON_PAGING );
		if ( ! empty( $params['paging'] ) ) {
			add_post_meta( $chart->ID, Visualizer_Plugin::CF_JSON_PAGING, $params['paging'] );
		}

		$render         = new Visualizer_Render_Page_Update();
		$render->id     = $chart->ID;
		$render->data   = json_encode( $source->getRawData() );
		$render->series = json_encode( $source->getSeries() );
		$render->render();

		defined( 'WP_TESTS_DOMAIN' ) ? wp_die() : exit();
	}


	/**
	 * Fetches charts from database.
	 *
	 * This method is also called from the media pop-up (classic editor: create a post and add chart from insert content).
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function getCharts() {
		$query_args = array(
			'post_type'      => Visualizer_Plugin::CPT_VISUALIZER,
			'posts_per_page' => 9,
			'paged'          => filter_input(
				INPUT_GET,
				'page',
				FILTER_VALIDATE_INT,
				array(
					'options' => array(
						'min_range' => 1,
						'default'   => 1,
					),
				)
			),
		);
		$filter     = filter_input( INPUT_GET, 's', FILTER_SANITIZE_STRING );
		if ( empty( $filter ) ) {
			// 'filter' is from the modal from the add media button.
			$filter = filter_input( INPUT_GET, 'filter', FILTER_SANITIZE_STRING );
		}

		if ( $filter && in_array( $filter, Visualizer_Plugin::getChartTypes(), true ) ) {
			$query_args['meta_query'] = array(
				array(
					'key'     => Visualizer_Plugin::CF_CHART_TYPE,
					'value'   => $filter,
					'compare' => '=',
				),
			);
		}
		$query  = new WP_Query( $query_args );
		$charts = array();
		while ( $query->have_posts() ) {
			$chart            = $query->next_post();
			$chart_data       = $this->_getChartArray( $chart );
			$chart_data['id'] = $chart->ID;
			$chart_data['library'] = $this->load_chart_type( $chart->ID );
			$css                = '';
			$settings           = $chart_data['settings'];
			$arguments          = $this->get_inline_custom_css( 'visualizer-chart-' . $chart->ID, $settings );
			if ( ! empty( $arguments ) ) {
				$css        = $arguments[0];
				$settings   = $arguments[1];
			}
			$chart_data['settings'] = $settings;
			$chart_data['css'] = $css;
			$charts[]         = $chart_data;
		}
		self::_sendResponse(
			array(
				'success' => true,
				'data'    => $charts,
				'total'   => $query->max_num_pages,
			)
		);
	}

	/**
	 * Returns chart data required for rendering.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 *
	 * @param WP_Post $chart The chart object.
	 *
	 * @return array The array of chart data.
	 */
	private function _getChartArray( WP_Post $chart = null ) {
		if ( is_null( $chart ) ) {
			$chart = $this->_chart;
		}
		$type   = get_post_meta( $chart->ID, Visualizer_Plugin::CF_CHART_TYPE, true );
		$series = apply_filters( Visualizer_Plugin::FILTER_GET_CHART_SERIES, get_post_meta( $chart->ID, Visualizer_Plugin::CF_SERIES, true ), $chart->ID, $type );
		$data   = apply_filters( Visualizer_Plugin::FILTER_GET_CHART_DATA, unserialize( $chart->post_content ), $chart->ID, $type );
		$library = $this->load_chart_type( $chart->ID );

		$settings = get_post_meta( $chart->ID, Visualizer_Plugin::CF_SETTINGS, true );
		$settings = apply_filters( Visualizer_Plugin::FILTER_GET_CHART_SETTINGS, $settings, $chart->ID, $type );
		if ( ! empty( $atts['settings'] ) ) {
			$settings = apply_filters( $atts['settings'], $settings, $chart->ID, $type );
		}

		$css        = '';
		$arguments  = $this->get_inline_custom_css( 'visualizer-' . $chart->ID, $settings );
		if ( ! empty( $arguments ) ) {
			$css        = $arguments[0];
			$settings   = $arguments[1];
		}

		$date_formats = Visualizer_Source::get_date_formats_if_exists( $series, $data );

		return array(
			'type'     => $type,
			'series'   => $series,
			'settings' => $settings,
			'data'     => $data,
			'library'  => $library,
			'css'       => $css,
			'date_formats'       => $date_formats,
		);
	}

	/**
	 * Sends json response.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 *
	 * @param array $results The response array.
	 */
	public static function _sendResponse( $results ) {
		header( 'Content-type: application/json' );
		nocache_headers();
		echo json_encode( $results );
		defined( 'WP_TESTS_DOMAIN' ) ? wp_die() : exit();
	}

	/**
	 * Deletes a chart from database.
	 *
	 * @since 1.0.0
	 * @uses wp_delete_post() To delete a chart.
	 *
	 * @access public
	 */
	public function deleteChart() {
		$is_post      = $_SERVER['REQUEST_METHOD'] === 'POST';
		$input_method = $is_post ? INPUT_POST : INPUT_GET;
		$chart_id     = $success = false;
		$nonce        = wp_verify_nonce( filter_input( $input_method, 'nonce' ) );
		$capable      = current_user_can( 'delete_posts' );
		if ( $nonce && $capable ) {
			$chart_id = filter_input(
				$input_method,
				'chart',
				FILTER_VALIDATE_INT,
				array(
					'options' => array(
						'min_range' => 1,
					),
				)
			);
			if ( $chart_id ) {
				$chart   = get_post( $chart_id );
				$success = $chart && $chart->post_type === Visualizer_Plugin::CPT_VISUALIZER;
			}
		}
		if ( $success ) {
			wp_delete_post( $chart_id, true );
		}
		if ( $is_post ) {
			self::_sendResponse(
				array(
					'success' => $success,
				)
			);
		}
		wp_redirect( wp_get_referer() );
		exit;
	}

	/**
	 * Delete charts that are still in auto-draft mode.
	 */
	private function deleteOldCharts() {
		$query = new WP_Query(
			array(
				'post_type'    => Visualizer_Plugin::CPT_VISUALIZER,
				'post_status'  => 'auto-draft',
				'fields'                => 'ids',
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
				'posts_per_page'        => 50,
				'date_query' => array(
					array(
						'before' => 'today',
					),
				),
			)
		);

		if ( $query->have_posts() ) {
			$ids = array();
			while ( $query->have_posts() ) {
				wp_delete_post( $query->next_post(), true );
			}
		}
	}

	/**
	 * Renders appropriate page for chart builder. Creates new auto draft chart
	 * if no chart has been specified.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function renderChartPages() {
		defined( 'IFRAME_REQUEST' ) || define( 'IFRAME_REQUEST', 1 );
		// check chart, if chart not exists, will create new one and redirects to the same page with proper chart id
		$chart_id = isset( $_GET['chart'] ) ? filter_var( $_GET['chart'], FILTER_VALIDATE_INT ) : '';
		if ( ! $chart_id || ! ( $chart = get_post( $chart_id ) ) || $chart->post_type !== Visualizer_Plugin::CPT_VISUALIZER ) {
			$this->deleteOldCharts();
			$default_type = isset( $_GET['type'] ) && ! empty( $_GET['type'] ) ? $_GET['type'] : 'line';
			$source       = new Visualizer_Source_Csv( VISUALIZER_ABSPATH . DIRECTORY_SEPARATOR . 'samples' . DIRECTORY_SEPARATOR . $default_type . '.csv' );
			$source->fetch();
			$chart_id = wp_insert_post(
				array(
					'post_type'    => Visualizer_Plugin::CPT_VISUALIZER,
					'post_title'   => 'Visualization',
					'post_author'  => get_current_user_id(),
					'post_status'  => 'auto-draft',
					'post_content' => $source->getData(),
				)
			);
			if ( $chart_id && ! is_wp_error( $chart_id ) ) {
				add_post_meta( $chart_id, Visualizer_Plugin::CF_CHART_TYPE, $default_type );
				add_post_meta( $chart_id, Visualizer_Plugin::CF_DEFAULT_DATA, 1 );
				add_post_meta( $chart_id, Visualizer_Plugin::CF_SOURCE, $source->getSourceName() );
				add_post_meta( $chart_id, Visualizer_Plugin::CF_SERIES, $source->getSeries() );
				add_post_meta(
					$chart_id,
					Visualizer_Plugin::CF_SETTINGS,
					array(
						'focusTarget' => 'datum',
					)
				);
				do_action( 'visualizer_pro_new_chart_defaults', $chart_id );
			}
			wp_redirect( add_query_arg( 'chart', (int) $chart_id ) );
			defined( 'WP_TESTS_DOMAIN' ) ? wp_die() : exit();
		}

		$this->load_chart_type( $chart_id );

		// enqueue and register scripts and styles
		wp_register_script( 'visualizer-chosen', VISUALIZER_ABSURL . 'js/lib/chosen.jquery.min.js', array( 'jquery' ), Visualizer_Plugin::VERSION );
		wp_register_style( 'visualizer-chosen', VISUALIZER_ABSURL . 'css/lib/chosen.min.css', array(), Visualizer_Plugin::VERSION );

		wp_register_style( 'visualizer-frame', VISUALIZER_ABSURL . 'css/frame.css', array( 'visualizer-chosen' ), Visualizer_Plugin::VERSION );
		wp_register_script( 'visualizer-frame', VISUALIZER_ABSURL . 'js/frame.js', array( 'visualizer-chosen', 'jquery-ui-accordion' ), Visualizer_Plugin::VERSION, true );
		wp_register_script( 'visualizer-customization', $this->get_user_customization_js(), array(), null, true );
		wp_register_script(
			'visualizer-render',
			VISUALIZER_ABSURL . 'js/render-facade.js',
			apply_filters( 'visualizer_assets_render', array( 'visualizer-frame', 'visualizer-customization' ), false ),
			Visualizer_Plugin::VERSION,
			true
		);
		wp_register_script(
			'visualizer-preview',
			VISUALIZER_ABSURL . 'js/preview.js',
			array(
				'wp-color-picker',
				'visualizer-render',
			),
			Visualizer_Plugin::VERSION,
			true
		);
		wp_register_script( 'visualizer-editor-simple', VISUALIZER_ABSURL . 'js/simple-editor.js', array( 'jquery' ), Visualizer_Plugin::VERSION, true );

		// added by Ash/Upwork
		if ( VISUALIZER_PRO ) {
			global $Visualizer_Pro;
			$Visualizer_Pro->_addScriptsAndStyles();
		}

		// dispatch pages
		$this->_chart = get_post( $chart_id );
		$tab    = isset( $_GET['tab'] ) && ! empty( $_GET['tab'] ) ? $_GET['tab'] : 'visualizer';

		// skip chart type pages only for existing charts.
		if ( VISUALIZER_SKIP_CHART_TYPE_PAGE && 'auto-draft' !== $this->_chart->post_status && ( ! empty( $_GET['tab'] ) && 'visualizer' === $_GET['tab'] ) ) {
			$tab = 'settings';
		}

		if ( isset( $_POST['cancel'] ) && 1 === intval( $_POST['cancel'] ) ) {
			// if the cancel button is clicked.
			$this->undoRevisions( $chart_id, true );
		} elseif ( isset( $_POST['save'] ) && 1 === intval( $_POST['save'] ) ) {
			// if the save button is clicked.
			$this->undoRevisions( $chart_id, false );
		} else {
			// if the edit button is clicked.
			$this->_chart = $this->handleExistingRevisions( $chart_id, $this->_chart );
		}

		switch ( $tab ) {
			case 'settings':
				$this->_handleDataAndSettingsPage();
				break;
			case 'type': // fall through.
			case 'visualizer': // fall through.
				$this->_handleTypesPage();
				break;
			default:
				do_action( 'visualizer_pro_handle_tab', $tab, $this->_chart );
				break;
		}
		defined( 'WP_TESTS_DOMAIN' ) ? wp_die() : exit();
	}

	/**
	 * Load code editor assets.
	 */
	private function loadCodeEditorAssets() {
		global $wp_version;

		$wp_scripts = wp_scripts();

		// data tables assets.
		wp_register_script( 'visualizer-datatables', '//cdn.datatables.net/v/dt/dt-1.10.18/b-1.5.6/b-colvis-1.5.6/cr-1.5.0/fc-3.2.5/fh-3.1.4/r-2.2.2/sc-2.0.0/sl-1.3.0/datatables.min.js', array( 'jquery-ui-core' ), Visualizer_Plugin::VERSION );
		wp_register_style( 'visualizer-datatables', '//cdn.datatables.net/v/dt/dt-1.10.18/b-1.5.6/b-colvis-1.5.6/cr-1.5.0/fc-3.2.5/fh-3.1.4/r-2.2.2/sc-2.0.0/sl-1.3.0/datatables.min.css', array(), Visualizer_Plugin::VERSION );
		wp_register_style( 'visualizer-jquery-ui', sprintf( '//ajax.googleapis.com/ajax/libs/jqueryui/%s/themes/smoothness/jquery-ui.css', $wp_scripts->registered['jquery-ui-core']->ver ), array( 'visualizer-datatables' ), Visualizer_Plugin::VERSION );
		wp_enqueue_script( 'visualizer-datatables' );
		wp_enqueue_style( 'visualizer-jquery-ui' );

		if ( ! VISUALIZER_PRO ) {
			return;
		}

		$table_col_mapping  = Visualizer_Source_Query_Params::get_all_db_tables_column_mapping();

		if ( version_compare( $wp_version, '4.9.0', '<' ) ) {
			// code mirror assets.
			wp_register_script( 'visualizer-codemirror-core', '//codemirror.net/lib/codemirror.js', array( 'jquery' ), Visualizer_Plugin::VERSION );
			wp_register_script( 'visualizer-codemirror-placeholder', '//codemirror.net/addon/display/placeholder.js', array( 'visualizer-codemirror-core' ), Visualizer_Plugin::VERSION );
			wp_register_script( 'visualizer-codemirror-matchbrackets', '//codemirror.net/addon/edit/matchbrackets.js', array( 'visualizer-codemirror-core' ), Visualizer_Plugin::VERSION );
			wp_register_script( 'visualizer-codemirror-closebrackets', '//codemirror.net/addon/edit/closebrackets.js', array( 'visualizer-codemirror-core' ), Visualizer_Plugin::VERSION );
			wp_register_script( 'visualizer-codemirror-sql', '//codemirror.net/mode/sql/sql.js', array( 'visualizer-codemirror-core' ), Visualizer_Plugin::VERSION );
			wp_register_script( 'visualizer-codemirror-sql-hint', '//codemirror.net/addon/hint/sql-hint.js', array( 'visualizer-codemirror-core' ), Visualizer_Plugin::VERSION );
			wp_register_script( 'visualizer-codemirror-hint', '//codemirror.net/addon/hint/show-hint.js', array(  'visualizer-codemirror-sql', 'visualizer-codemirror-sql-hint', 'visualizer-codemirror-placeholder', 'visualizer-codemirror-matchbrackets', 'visualizer-codemirror-closebrackets' ), Visualizer_Plugin::VERSION );
			wp_register_style( 'visualizer-codemirror-core', '//codemirror.net/lib/codemirror.css', array(), Visualizer_Plugin::VERSION );
			wp_register_style( 'visualizer-codemirror-hint', '//codemirror.net/addon/hint/show-hint.css', array( 'visualizer-codemirror-core' ), Visualizer_Plugin::VERSION );

			wp_enqueue_script( 'visualizer-codemirror-hint' );
			wp_enqueue_style( 'visualizer-codemirror-hint' );
		} else {
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

		return $table_col_mapping;
	}

	/**
	 * Handle data and settings page
	 */
	private function _handleDataAndSettingsPage() {
		if ( isset( $_POST['map_api_key'] ) ) {
			update_option( 'visualizer-map-api-key', $_POST['map_api_key'] );
		}
		if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_GET['nonce'] ) && wp_verify_nonce( $_GET['nonce'] ) ) {
			if ( $this->_chart->post_status === 'auto-draft' ) {
				$this->_chart->post_status = 'publish';

				// ensure that a revision is not created. If a revision is created it will have the proper data and the parent of the revision will have default data.
				// we do not want any difference in data so disable revisions temporarily.
				add_filter( 'wp_revisions_to_keep', '__return_false' );
				wp_update_post( $this->_chart->to_array() );
			}
			// save meta data only when it is NOT being canceled.
			if ( ! ( isset( $_POST['cancel'] ) && 1 === intval( $_POST['cancel'] ) ) ) {
				update_post_meta( $this->_chart->ID, Visualizer_Plugin::CF_SETTINGS, $_POST );
			}
			$render       = new Visualizer_Render_Page_Send();
			$render->text = sprintf( '[visualizer id="%d"]', $this->_chart->ID );
			wp_iframe( array( $render, 'render' ) );

			return;
		}
		$data          = $this->_getChartArray();
		$sidebar       = '';
		$sidebar_class = 'Visualizer_Render_Sidebar_Type_' . ucfirst( $data['type'] );
		if ( class_exists( $sidebar_class, true ) ) {
			$sidebar           = new $sidebar_class( $data['settings'] );
			$sidebar->__series = $data['series'];
			$sidebar->__data   = $data['data'];
		} else {
			$sidebar = apply_filters( 'visualizer_pro_chart_type_sidebar', '', $data );
			if ( $sidebar !== '' ) {
				$sidebar->__series = $data['series'];
				$sidebar->__data   = $data['data'];
			}
		}
		unset( $data['settings']['width'], $data['settings']['height'] );
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_style( 'visualizer-frame' );
		wp_enqueue_script( 'visualizer-preview' );
		wp_enqueue_script( 'visualizer-chosen' );
		wp_enqueue_script( 'visualizer-render' );

		if ( ! VISUALIZER_PRO ) {
			wp_enqueue_script( 'visualizer-editor-simple' );
			wp_localize_script(
				'visualizer-editor-simple',
				'visualizer1',
				array(
					'ajax'      => array(
						'url'     => admin_url( 'admin-ajax.php' ),
						'nonces'  => array(
						),
						'actions' => array(
						),
					),
				)
			);
		}

		$table_col_mapping  = $this->loadCodeEditorAssets();

		wp_localize_script(
			'visualizer-render',
			'visualizer',
			array(
				'l10n'   => array(
					'invalid_source' => esc_html__( 'You have entered invalid URL. Please, insert proper URL.', 'visualizer' ),
					'loading'       => esc_html__( 'Loading...', 'visualizer' ),
					'json_error'    => esc_html__( 'An error occured in fetching data.', 'visualizer' ),
				),
				'charts' => array(
					'canvas' => $data,
					'id' => $this->_chart->ID,
				),
				'language'  => $this->get_language(),
				'map_api_key' => get_option( 'visualizer-map-api-key' ),
				'ajax'      => array(
					'url'     => admin_url( 'admin-ajax.php' ),
					'nonces'  => array(
						'permissions'   => wp_create_nonce( Visualizer_Plugin::ACTION_FETCH_PERMISSIONS_DATA ),
						'db_get_data'   => wp_create_nonce( Visualizer_Plugin::ACTION_FETCH_DB_DATA . Visualizer_Plugin::VERSION ),
						'json_get_roots'   => wp_create_nonce( Visualizer_Plugin::ACTION_JSON_GET_ROOTS . Visualizer_Plugin::VERSION ),
						'json_get_data'   => wp_create_nonce( Visualizer_Plugin::ACTION_JSON_GET_DATA . Visualizer_Plugin::VERSION ),
						'json_set_schedule'   => wp_create_nonce( Visualizer_Plugin::ACTION_JSON_SET_SCHEDULE . Visualizer_Plugin::VERSION ),
					),
					'actions' => array(
						'permissions'   => Visualizer_Plugin::ACTION_FETCH_PERMISSIONS_DATA,
						'db_get_data'   => Visualizer_Plugin::ACTION_FETCH_DB_DATA,
						'json_get_roots'   => Visualizer_Plugin::ACTION_JSON_GET_ROOTS,
						'json_get_data'   => Visualizer_Plugin::ACTION_JSON_GET_DATA,
						'json_set_schedule'   => Visualizer_Plugin::ACTION_JSON_SET_SCHEDULE,
					),
				),
				'db_query' => array(
					'tables'    => $table_col_mapping,
				),
				'is_pro'    => VISUALIZER_PRO,
				'page_type' => 'chart',
				'json_tag_separator' => Visualizer_Source_Json::TAG_SEPARATOR,
				'json_tag_separator_view' => Visualizer_Source_Json::TAG_SEPARATOR_VIEW,
				'is_front'  => false,
			)
		);

		$render          = new Visualizer_Render_Page_Data();
		$render->chart   = $this->_chart;
		$render->type    = $data['type'];
		$render->custom_css  = $data['css'];
		$render->sidebar = $sidebar;
		if ( filter_input( INPUT_GET, 'library', FILTER_VALIDATE_BOOLEAN ) ) {
			$render->button = filter_input( INPUT_GET, 'action' ) === Visualizer_Plugin::ACTION_EDIT_CHART
				? esc_html__( 'Save Chart', 'visualizer' )
				: esc_html__( 'Create Chart', 'visualizer' );
			if ( filter_input( INPUT_GET, 'action' ) === Visualizer_Plugin::ACTION_EDIT_CHART ) {
				$render->cancel_button = esc_html__( 'Cancel', 'visualizer' );
			}
		} else {
			$render->button = esc_attr__( 'Insert Chart', 'visualizer' );
		}
		if ( VISUALIZER_PRO ) {
			global $Visualizer_Pro;
			$Visualizer_Pro->_enqueueScriptsAndStyles( $data );
		}
		$this->_addAction( 'admin_head', 'renderFlattrScript' );
		wp_iframe( array( $render, 'render' ) );
	}

	/**
	 * Handles chart type selection page.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 */
	private function _handleTypesPage() {
		// process post request
		if ( $_SERVER['REQUEST_METHOD'] === 'POST' && wp_verify_nonce( filter_input( INPUT_POST, 'nonce' ) ) ) {
			$type = filter_input( INPUT_POST, 'type' );
			if ( in_array( $type, Visualizer_Plugin::getChartTypes(), true ) ) {
				// save new chart type
				update_post_meta( $this->_chart->ID, Visualizer_Plugin::CF_CHART_TYPE, $type );
				// if the chart has default data, update it with appropriate default data for new type
				if ( filter_var( get_post_meta( $this->_chart->ID, Visualizer_Plugin::CF_DEFAULT_DATA, true ), FILTER_VALIDATE_BOOLEAN ) ) {
					$source = new Visualizer_Source_Csv( VISUALIZER_ABSPATH . DIRECTORY_SEPARATOR . 'samples' . DIRECTORY_SEPARATOR . $type . '.csv' );
					$source->fetch();
					$this->_chart->post_content = $source->getData();
					wp_update_post( $this->_chart->to_array() );
					update_post_meta( $this->_chart->ID, Visualizer_Plugin::CF_SERIES, $source->getSeries() );
				}
				// redirect to next tab
				// changed by Ash/Upwork
				wp_redirect( add_query_arg( 'tab', 'settings' ) );

				return;
			}
		}
		$render        = new Visualizer_Render_Page_Types();
		$render->type  = get_post_meta( $this->_chart->ID, Visualizer_Plugin::CF_CHART_TYPE, true );
		$render->types = Visualizer_Module_Admin::_getChartTypesLocalized();
		$render->chart = $this->_chart;
		wp_enqueue_style( 'visualizer-frame' );
		wp_enqueue_script( 'visualizer-frame' );
		wp_iframe( array( $render, 'render' ) );
	}

	/**
	 * Renders flattr script in the iframe <head>
	 *
	 * @since 1.4.2
	 * @action admin_head
	 *
	 * @access public
	 */
	public function renderFlattrScript() {
		echo '';
	}

	/**
	 * Processes the CSV that is sent in the request as a string.
	 *
	 * @since 3.2.0
	 */
	private function handleCSVasString( $data ) {
		$source = null;
		if ( VISUALIZER_PRO ) {
			$source = apply_filters( 'visualizer_pro_handle_chart_data', $data, '' );
		} else {
			// data coming in from the text editor.
			$tmpfile = tempnam( get_temp_dir(), Visualizer_Plugin::NAME );
			$handle  = fopen( $tmpfile, 'w' );
			$values = preg_split( '/[\n\r]+/', stripslashes( trim( $data ) ) );
			if ( $values ) {
				foreach ( $values as $row ) {
					if ( empty( $row ) ) {
						continue;
					}
					$columns = explode( ',', $row );
					fputcsv( $handle, $columns );
				}
			}
			$source = new Visualizer_Source_Csv( $tmpfile );
			fclose( $handle );
		}
		return $source;
	}

	/**
	 * Parses the data uploaded as an HTML table.
	 *
	 * @since 3.2.0
	 *
	 * @access private
	 */
	private function handleTabularData() {
		$csv        = array();
		// the datatable mentions the headers twice, so lets remove the duplicates.
		$headers    = array_unique( array_filter( $_POST['header'] ) );
		$types      = $_POST['type'];

		// capture all the indexes that correspond to excluded columns.
		$exclude    = array();
		$index      = 0;
		foreach ( $types as $type ) {
			if ( empty( $type ) ) {
				$exclude[] = $index;
			}
			$index++;
		}

		// when N headers are being renamed, the number of headers increases by N
		// because of the way datatable duplicates header information
		// so unset the headers that have been renamed.
		if ( count( $headers ) !== count( $types ) ) {
			$to = count( $headers );
			for ( $i = count( $types ); $i < $to; $i++ ) {
				unset( $headers[ $i + 1 ] );
			}
		}

		$columns    = array();
		for ( $i = 0; $i < count( $headers ); $i++ ) {
			if ( ! isset( $_POST[ 'data' . $i ] ) ) {
				continue;
			}
			$columns[ $i ] = $_POST[ 'data' . $i ];
		}

		$csv[]      = $headers;
		$csv[]      = $types;
		for ( $j = 0; $j < count( $columns[0] ); $j++ ) {
			$row = array();
			for ( $i = 0; $i < count( $headers ); $i++ ) {
				$row[] = $columns[ $i ][ $j ];
			}
			$csv[]  = $row;
		}

		$tmpfile = tempnam( get_temp_dir(), Visualizer_Plugin::NAME );
		$handle  = fopen( $tmpfile, 'w' );

		if ( $csv ) {
			$index = 0;
			foreach ( $csv as $row ) {
				// remove all the cells corresponding to the excluded headers.
				foreach ( $exclude as $j ) {
					unset( $row[ $j ] );
				}
				fputcsv( $handle, $row );
			}
		}
		$source = new Visualizer_Source_Csv( $tmpfile );
		fclose( $handle );
		return $source;
	}

	/**
	 * Parses uploaded CSV file and saves new data for the chart.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function uploadData() {
		// if this is being called internally from pro and VISUALIZER_DO_NOT_DIE is set.
		// otherwise, assume this is a normal web request.
		$can_die    = ! ( defined( 'VISUALIZER_DO_NOT_DIE' ) && VISUALIZER_DO_NOT_DIE );

		// validate nonce
		if ( ! isset( $_GET['nonce'] ) || ! wp_verify_nonce( $_GET['nonce'] ) ) {
			if ( ! $can_die ) {
				return;
			}
			status_header( 403 );
			exit;
		}

		// check chart, if chart exists
		// do not use filter_input as it does not work for phpunit test cases, use filter_var instead
		$chart_id = isset( $_GET['chart'] ) ? filter_var( $_GET['chart'], FILTER_VALIDATE_INT ) : '';
		if ( ! $chart_id || ! ( $chart = get_post( $chart_id ) ) || $chart->post_type !== Visualizer_Plugin::CPT_VISUALIZER ) {
			if ( ! $can_die ) {
				return;
			}
			status_header( 400 );
			exit;
		}

		if ( ! isset( $_POST['vz-import-time'] ) ) {
			apply_filters( 'visualizer_pro_remove_schedule', $chart_id );
		}

		if ( ! isset( $_POST['chart_data_src'] ) || Visualizer_Plugin::CF_SOURCE_FILTER !== $_POST['chart_data_src'] ) {
			// delete the filters in case this chart is being uploaded from other data sources
			delete_post_meta( $chart_id, Visualizer_Plugin::CF_FILTER_CONFIG );
			delete_post_meta( $chart_id, '__transient-' . Visualizer_Plugin::CF_FILTER_CONFIG );
			delete_post_meta( $chart_id, '__transient-' . Visualizer_Plugin::CF_DB_QUERY );

			// delete "import from db" specific parameters.
			delete_post_meta( $chart_id, Visualizer_Plugin::CF_DB_QUERY );
			delete_post_meta( $chart_id, Visualizer_Plugin::CF_DB_SCHEDULE );
		}

		// delete json related data.
		delete_post_meta( $chart_id, Visualizer_Plugin::CF_JSON_URL );
		delete_post_meta( $chart_id, Visualizer_Plugin::CF_JSON_ROOT );
		delete_post_meta( $chart_id, Visualizer_Plugin::CF_JSON_PAGING );

		$source = null;
		$render = new Visualizer_Render_Page_Update();
		if ( isset( $_POST['remote_data'] ) && filter_var( $_POST['remote_data'], FILTER_VALIDATE_URL ) ) {
			$source = new Visualizer_Source_Csv_Remote( $_POST['remote_data'] );
			if ( isset( $_POST['vz-import-time'] ) ) {
				apply_filters( 'visualizer_pro_chart_schedule', $chart_id, $_POST['remote_data'], $_POST['vz-import-time'] );
			}
			// phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
		} elseif ( isset( $_FILES['local_data'] ) && $_FILES['local_data']['error'] == 0 ) {
			$source = new Visualizer_Source_Csv( $_FILES['local_data']['tmp_name'] );
		} elseif ( isset( $_POST['chart_data'] ) && strlen( $_POST['chart_data'] ) > 0 ) {
			$source = $this->handleCSVasString( $_POST['chart_data'] );
		} elseif ( isset( $_POST['table_data'] ) && 'yes' === $_POST['table_data'] ) {
			$source = $this->handleTabularData();
		} else {
			$render->message = esc_html__( 'CSV file with chart data was not uploaded. Please, try again.', 'visualizer' );
		}
		if ( $source ) {
			if ( $source->fetch() ) {
				$content    = $source->getData();
				$populate   = true;
				if ( is_string( $content ) && is_array( unserialize( $content ) ) ) {
					$json   = unserialize( $content );
					// if source exists, so should data. if source exists but data is blank, do not populate the chart.
					// if we populate the data even if it is empty, the chart will show "Table has no columns".
					if ( array_key_exists( 'source', $json ) && ! empty( $json['source'] ) && ( ! array_key_exists( 'data', $json ) || empty( $json['data'] ) ) ) {
						do_action( 'themeisle_log_event', Visualizer_Plugin::NAME, sprintf( 'Not populating chart data as source exists (%s) but data is empty!', $json['source'] ), 'warn', __FILE__, __LINE__ );
						$populate   = false;
					}
				}
				if ( $populate ) {
					$chart->post_content = $content;
				}
				wp_update_post( $chart->to_array() );
				update_post_meta( $chart->ID, Visualizer_Plugin::CF_SERIES, $source->getSeries() );
				update_post_meta( $chart->ID, Visualizer_Plugin::CF_SOURCE, $source->getSourceName() );
				update_post_meta( $chart->ID, Visualizer_Plugin::CF_DEFAULT_DATA, 0 );
				$render->id     = $chart->ID;
				$render->data   = json_encode( $source->getRawData() );
				$render->series = json_encode( $source->getSeries() );
			} else {
				$render->message = esc_html__( 'CSV file is broken or invalid. Please, try again.', 'visualizer' );
			}
		}
		$render->render();
		if ( ! $can_die ) {
			return;
		}
		defined( 'WP_TESTS_DOMAIN' ) ? wp_die() : exit();
	}

	/**
	 * Clones the chart.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function cloneChart() {
		$chart_id = $success = false;
		$nonce    = wp_verify_nonce( filter_input( INPUT_GET, 'nonce' ), Visualizer_Plugin::ACTION_CLONE_CHART );
		$capable  = current_user_can( 'edit_posts' );
		if ( $nonce && $capable ) {
			$chart_id = filter_input(
				INPUT_GET,
				'chart',
				FILTER_VALIDATE_INT,
				array(
					'options' => array(
						'min_range' => 1,
					),
				)
			);
			if ( $chart_id ) {
				$chart   = get_post( $chart_id );
				$success = $chart && $chart->post_type === Visualizer_Plugin::CPT_VISUALIZER;
			}
		}
		$redirect = wp_get_referer();
		if ( $success ) {
			$new_chart_id = wp_insert_post(
				array(
					'post_type'    => Visualizer_Plugin::CPT_VISUALIZER,
					'post_title'   => 'Visualization',
					'post_author'  => get_current_user_id(),
					'post_status'  => $chart->post_status,
					'post_content' => $chart->post_content,
				)
			);
			if ( $new_chart_id && ! is_wp_error( $new_chart_id ) ) {
				add_post_meta( $new_chart_id, Visualizer_Plugin::CF_CHART_TYPE, get_post_meta( $chart_id, Visualizer_Plugin::CF_CHART_TYPE, true ) );
				add_post_meta( $new_chart_id, Visualizer_Plugin::CF_DEFAULT_DATA, get_post_meta( $chart_id, Visualizer_Plugin::CF_DEFAULT_DATA, true ) );
				add_post_meta( $new_chart_id, Visualizer_Plugin::CF_SOURCE, get_post_meta( $chart_id, Visualizer_Plugin::CF_SOURCE, true ) );
				add_post_meta( $new_chart_id, Visualizer_Plugin::CF_SERIES, get_post_meta( $chart_id, Visualizer_Plugin::CF_SERIES, true ) );
				add_post_meta( $new_chart_id, Visualizer_Plugin::CF_SETTINGS, get_post_meta( $chart_id, Visualizer_Plugin::CF_SETTINGS, true ) );
				$redirect = add_query_arg(
					array(
						'page' => 'visualizer',
						'type' => filter_input( INPUT_GET, 'type' ),
					),
					admin_url( 'upload.php' )
				);
			}
		}
		wp_redirect( $redirect );
		exit;
	}

	/**
	 * Exports the chart data
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function exportData() {
		check_ajax_referer( Visualizer_Plugin::ACTION_EXPORT_DATA . Visualizer_Plugin::VERSION, 'security' );
		$capable  = current_user_can( 'edit_posts' );
		if ( $capable ) {
			$chart_id = isset( $_GET['chart'] ) ? filter_var(
				$_GET['chart'],
				FILTER_VALIDATE_INT,
				array(
					'options' => array(
						'min_range' => 1,
					),
				)
			) : '';
			if ( $chart_id ) {
				$data   = $this->_getDataAs( $chart_id, 'csv' );
				if ( $data ) {
					echo wp_send_json_success( $data );
				}
			}
		}

		defined( 'WP_TESTS_DOMAIN' ) ? wp_die() : exit();
	}

	/**
	 * Handles chart data page.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 */
	private function _handleDataPage() {
		$data          = $this->_getChartArray();
		$render        = new Visualizer_Render_Page_Data();
		$render->chart = $this->_chart;
		$render->type  = $data['type'];
		unset( $data['settings']['width'], $data['settings']['height'] );
		wp_enqueue_style( 'visualizer-frame' );
		wp_enqueue_script( 'visualizer-render' );
		wp_localize_script(
			'visualizer-render',
			'visualizer',
			array(
				'l10n'   => array(
					'invalid_source' => esc_html__( 'You have entered invalid URL. Please, insert proper URL.', 'visualizer' ),
					'loading'       => esc_html__( 'Loading...', 'visualizer' ),
				),
				'charts' => array(
					'canvas' => $data,
				),
			)
		);
		// Added by Ash/Upwork
		if ( VISUALIZER_PRO ) {
			global $Visualizer_Pro;
			$Visualizer_Pro->_enqueueScriptsAndStyles( $data );
		}
		// Added by Ash/Upwork
		$this->_addAction( 'admin_head', 'renderFlattrScript' );
		wp_iframe( array( $render, 'render' ) );
	}

	/**
	 * Returns the data for the query.
	 *
	 * @access public
	 */
	public function getQueryData() {
		check_ajax_referer( Visualizer_Plugin::ACTION_FETCH_DB_DATA . Visualizer_Plugin::VERSION, 'security' );

		$params     = wp_parse_args( $_POST['params'] );
		$source     = new Visualizer_Source_Query( stripslashes( $params['query'] ) );
		$html       = $source->fetch( true );
		$error      = '';
		if ( empty( $html ) ) {
			$error  = $source->get_error();
			wp_send_json_error( array( 'msg' => $error ) );
		}
		wp_send_json_success( array( 'table' => $html ) );
	}

	/**
	 * Saves the query and the schedule.
	 *
	 * @access public
	 */
	public function saveQuery() {
		check_ajax_referer( Visualizer_Plugin::ACTION_SAVE_DB_QUERY . Visualizer_Plugin::VERSION, 'security' );

		$chart_id   = filter_input(
			INPUT_GET,
			'chart',
			FILTER_VALIDATE_INT,
			array(
				'options' => array(
					'min_range' => 1,
				),
			)
		);

		$render = new Visualizer_Render_Page_Update();
		if ( $chart_id ) {
			$params     = wp_parse_args( $_POST['params'] );
			$source     = new Visualizer_Source_Query( stripslashes( $params['query'] ) );
			$source->fetch( false );
			$error      = $source->get_error();
			if ( empty( $error ) ) {
				$hours = $_POST['refresh'];
				update_post_meta( $chart_id, Visualizer_Plugin::CF_DB_QUERY, stripslashes( $params['query'] ) );
				update_post_meta( $chart_id, Visualizer_Plugin::CF_SOURCE, $source->getSourceName() );
				update_post_meta( $chart_id, Visualizer_Plugin::CF_SERIES, $source->getSeries() );
				update_post_meta( $chart_id, Visualizer_Plugin::CF_DB_SCHEDULE, $hours );
				update_post_meta( $chart_id, Visualizer_Plugin::CF_DEFAULT_DATA, 0 );

				$schedules              = get_option( Visualizer_Plugin::CF_DB_SCHEDULE, array() );
				$schedules[ $chart_id ] = time() + $hours * HOUR_IN_SECONDS;
				update_option( Visualizer_Plugin::CF_DB_SCHEDULE, $schedules );

				wp_update_post(
					array(
						'ID'            => $chart_id,
						'post_content'  => $source->getData(),
					)
				);
				$render->data   = json_encode( $source->getRawData() );
				$render->series = json_encode( $source->getSeries() );
			} else {
				$render->message = $error;
			}
		}
		$render->render();
		if ( ! ( defined( 'VISUALIZER_DO_NOT_DIE' ) && VISUALIZER_DO_NOT_DIE ) ) {
			defined( 'WP_TESTS_DOMAIN' ) ? wp_die() : exit();
		}
	}


	/**
	 * Saves the filter query and the schedule.
	 *
	 * @access public
	 */
	public function saveFilter() {
		check_ajax_referer( Visualizer_Plugin::ACTION_SAVE_FILTER_QUERY . Visualizer_Plugin::VERSION, 'security' );

		$chart_id   = filter_input(
			INPUT_GET,
			'chart',
			FILTER_VALIDATE_INT,
			array(
				'options' => array(
					'min_range' => 1,
				),
			)
		);

		$hours = $_POST['refresh'];

		do_action( 'visualizer_save_filter', $chart_id, $hours );

		if ( ! ( defined( 'VISUALIZER_DO_NOT_DIE' ) && VISUALIZER_DO_NOT_DIE ) ) {
			defined( 'WP_TESTS_DOMAIN' ) ? wp_die() : exit();
		}
	}
}
