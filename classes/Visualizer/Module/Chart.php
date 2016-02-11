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

        // Added by Ash/Upwork
        if( defined( 'Visualizer_Pro' ) ){
            global $Visualizer_Pro;
            list($action, $name, $class) = $Visualizer_Pro->_getAjaxAction($this);
            $this->_addAjaxAction($action, $name, $class);
        }
        // Added by Ash/Upwork

	}

	/**
	 * Sends json response.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @param array $results The response array.
	 */
	public function _sendResponse( $results ) {
		header( 'Content-type: application/json' );
		nocache_headers();

		echo json_encode( $results );
		exit;
	}

	/**
	 * Returns chart data required for rendering.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @param WP_Post $chart The chart object.
	 * @return array The array of chart data.
	 */
	private function _getChartArray( WP_Post $chart = null ) {
		if ( is_null( $chart ) ) {
			$chart = $this->_chart;
		}

		$type = get_post_meta( $chart->ID, Visualizer_Plugin::CF_CHART_TYPE, true );
		$series = apply_filters( Visualizer_Plugin::FILTER_GET_CHART_SERIES, get_post_meta( $chart->ID, Visualizer_Plugin::CF_SERIES, true ), $chart->ID, $type );
		$data = apply_filters( Visualizer_Plugin::FILTER_GET_CHART_DATA, unserialize( $chart->post_content ), $chart->ID, $type );

		return array(
			'type'     => $type,
			'series'   => $series,
			'settings' => get_post_meta( $chart->ID, Visualizer_Plugin::CF_SETTINGS, true ),
			'data'     => $data,
		);
	}

	/**
	 * Fetches charts from database.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function getCharts() {
		$query_args = array(
			'post_type'      => Visualizer_Plugin::CPT_VISUALIZER,
			'posts_per_page' => 9,
			'paged'          => filter_input( INPUT_GET, 'page', FILTER_VALIDATE_INT, array(
				'options' => array(
					'min_range' => 1,
					'default'   => 1,
				)
			) )
		);

		$filter = filter_input( INPUT_GET, 'filter' );
		if ( $filter && in_array( $filter, Visualizer_Plugin::getChartTypes() ) ) {
			$query_args['meta_query'] = array(
				array(
					'key'     => Visualizer_Plugin::CF_CHART_TYPE,
					'value'   => $filter,
					'compare' => '=',
				),
			);
		}

		$query = new WP_Query( $query_args );

		$charts = array();
		while( $query->have_posts() ) {
			$chart = $query->next_post();

			$chart_data = $this->_getChartArray( $chart );
			$chart_data['id'] = $chart->ID;

			$charts[] = $chart_data;
		}

		$this->_sendResponse( array(
			'success' => true,
			'data'    => $charts,
			'total'   => $query->max_num_pages,
		) );
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
		$is_post = $_SERVER['REQUEST_METHOD'] == 'POST';
		$input_method = $is_post ? INPUT_POST : INPUT_GET;

		$chart_id = $success = false;
		$nonce = wp_verify_nonce( filter_input( $input_method, 'nonce' ) );
		$capable = current_user_can( 'delete_posts' );
		if ( $nonce && $capable ) {
			$chart_id = filter_input( $input_method, 'chart', FILTER_VALIDATE_INT, array( 'options' => array( 'min_range' => 1 ) ) );
			if ( $chart_id ) {
				$chart = get_post( $chart_id );
				$success = $chart && $chart->post_type == Visualizer_Plugin::CPT_VISUALIZER;
			}
		}

		if ( $success ) {
			wp_delete_post( $chart_id, true );
		}

		if ( $is_post ) {
			$this->_sendResponse( array( 'success' => $success ) );
		}

		wp_redirect( wp_get_referer() );
		exit;
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
		define ( 'IFRAME_REQUEST', 1 );

		// check chart, if chart not exists, will create new one and redirects to the same page with proper chart id
		$chart_id = filter_input( INPUT_GET, 'chart', FILTER_VALIDATE_INT );
		if ( !$chart_id || !( $chart = get_post( $chart_id ) ) || $chart->post_type != Visualizer_Plugin::CPT_VISUALIZER ) {
			$default_type = 'line';

			$source = new Visualizer_Source_Csv( VISUALIZER_ABSPATH . DIRECTORY_SEPARATOR . 'samples' . DIRECTORY_SEPARATOR . $default_type . '.csv' );
			$source->fetch();

			$chart_id = wp_insert_post( array(
				'post_type'    => Visualizer_Plugin::CPT_VISUALIZER,
				'post_title'   => 'Visualization',
				'post_author'  => get_current_user_id(),
				'post_status'  => 'auto-draft',
				'post_content' => $source->getData(),
			) );

			if ( $chart_id && !is_wp_error( $chart_id ) ) {
				add_post_meta( $chart_id, Visualizer_Plugin::CF_CHART_TYPE, $default_type );
				add_post_meta( $chart_id, Visualizer_Plugin::CF_DEFAULT_DATA, 1 );
				add_post_meta( $chart_id, Visualizer_Plugin::CF_SOURCE, $source->getSourceName() );
				add_post_meta( $chart_id, Visualizer_Plugin::CF_SERIES, $source->getSeries() );
				add_post_meta( $chart_id, Visualizer_Plugin::CF_SETTINGS, array() );
			}

			wp_redirect( add_query_arg( 'chart', (int)$chart_id ) );
			exit;
		}

		// enqueue and register scripts and styles
		wp_register_style( 'visualizer-frame', VISUALIZER_ABSURL . 'css/frame.css', array(), Visualizer_Plugin::VERSION );

		wp_register_script( 'visualizer-frame', VISUALIZER_ABSURL . 'js/frame.js', array( 'jquery' ), Visualizer_Plugin::VERSION, true );
		wp_register_script( 'google-jsapi', '//www.google.com/jsapi', array(), null, true );
		wp_register_script( 'visualizer-render', VISUALIZER_ABSURL . 'js/render.js', array( 'google-jsapi', 'visualizer-frame' ), Visualizer_Plugin::VERSION, true );
		wp_register_script( 'visualizer-preview', VISUALIZER_ABSURL . 'js/preview.js', array( 'wp-color-picker', 'visualizer-render' ), Visualizer_Plugin::VERSION, true );

        // added by Ash/Upwork
        if( defined( 'Visualizer_Pro' ) ){
            global $Visualizer_Pro;
            $Visualizer_Pro->_addScriptsAndStyles();
        }

		// dispatch pages
		$this->_chart = $chart;
		switch ( filter_input( INPUT_GET, 'tab' ) ) {
			case 'settings':
                // changed by Ash/Upwork
				$this->_handleDataAndSettingsPage();
				break;
			case 'type':
			default:
				$this->_handleTypesPage();
				break;
		}

		exit;
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
		if ( $_SERVER['REQUEST_METHOD'] == 'POST' && wp_verify_nonce( filter_input( INPUT_POST, 'nonce' ) ) ) {
			$type = filter_input( INPUT_POST, 'type' );
			if ( in_array( $type, Visualizer_Plugin::getChartTypes() ) ) {
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

		$render = new Visualizer_Render_Page_Types();
		$render->type = get_post_meta( $this->_chart->ID, Visualizer_Plugin::CF_CHART_TYPE, true );
		$render->types = Visualizer_Plugin::getChartTypes();
		$render->chart = $this->_chart;

		wp_enqueue_style( 'visualizer-frame' );
		wp_enqueue_script( 'visualizer-frame' );

		wp_iframe( array( $render, 'render') );
	}

	/**
	 * Handles chart data page.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 */
	private function _handleDataPage() {
		$data = $this->_getChartArray();

		$render = new Visualizer_Render_Page_Data();
		$render->chart = $this->_chart;
		$render->type = $data['type'];

		unset( $data['settings']['width'], $data['settings']['height'] );

		wp_enqueue_style( 'visualizer-frame' );
		wp_enqueue_script( 'visualizer-render' );
		wp_localize_script( 'visualizer-render', 'visualizer', array(
			'l10n'   => array(
				'remotecsv_prompt' => esc_html__( 'Please, enter the URL of CSV file:', Visualizer_Plugin::NAME ),
				'invalid_source'   => esc_html__( 'You have entered invalid URL. Please, insert proper URL.', Visualizer_Plugin::NAME ),
			),
			'charts' => array(
				'canvas' => $data,
			),
		) );

        // Added by Ash/Upwork
        if( defined( 'Visualizer_Pro' ) ){
            global $Visualizer_Pro;
            $Visualizer_Pro->_enqueueScriptsAndStyles($data);
        }
        // Added by Ash/Upwork

		$this->_addAction( 'admin_head', 'renderFlattrScript' );

		wp_iframe( array( $render, 'render') );
	}

	/**
	 * Handles chart settigns page.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 */
	private function _handleSettingsPage() {
		if ( $_SERVER['REQUEST_METHOD'] == 'POST' && wp_verify_nonce( filter_input( INPUT_GET, 'nonce' ) ) ) {
			if ( $this->_chart->post_status == 'auto-draft' ) {
				$this->_chart->post_status = 'publish';
				wp_update_post( $this->_chart->to_array() );
			}

			update_post_meta( $this->_chart->ID, Visualizer_Plugin::CF_SETTINGS, $_POST );

			$render = new Visualizer_Render_Page_Send();
			$render->text = sprintf( '[visualizer id="%d"]', $this->_chart->ID );

			wp_iframe( array( $render, 'render') );
			return;
		}

		$data = $this->_getChartArray();

		$sidebar = '';
		$sidebar_class = 'Visualizer_Render_Sidebar_Type_' . ucfirst( $data['type'] );
		if ( class_exists( $sidebar_class, true ) ) {
			$sidebar = new $sidebar_class( $data['settings'] );
			$sidebar->__series = $data['series'];
			$sidebar->__data = $data['data'];
		}

		unset( $data['settings']['width'], $data['settings']['height'] );

		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_style( 'visualizer-frame' );

		wp_enqueue_script( 'visualizer-preview' );
		wp_localize_script( 'visualizer-render', 'visualizer', array(
			'charts' => array(
				'canvas' => $data,
			),
		) );

		$render = new Visualizer_Render_Page_Settings();

		$render->sidebar = $sidebar;
		if ( filter_input( INPUT_GET, 'library', FILTER_VALIDATE_BOOLEAN ) ) {
			$render->button = filter_input( INPUT_GET, 'action' ) == Visualizer_Plugin::ACTION_EDIT_CHART
				? esc_html__( 'Save Chart', Visualizer_Plugin::NAME )
				: esc_html__( 'Create Chart', Visualizer_Plugin::NAME );
		} else {
			$render->button = esc_attr__( 'Insert Chart', Visualizer_Plugin::NAME );
		}

		$this->_addAction( 'admin_head', 'renderFlattrScript' );

		wp_iframe( array( $render, 'render') );
	}


    // changed by Ash/Upwork
    private function _handleDataAndSettingsPage(){
		if ( $_SERVER['REQUEST_METHOD'] == 'POST' && wp_verify_nonce( filter_input( INPUT_GET, 'nonce' ) ) ) {
			if ( $this->_chart->post_status == 'auto-draft' ) {
				$this->_chart->post_status = 'publish';
				wp_update_post( $this->_chart->to_array() );
			}

			update_post_meta( $this->_chart->ID, Visualizer_Plugin::CF_SETTINGS, $_POST );

			$render = new Visualizer_Render_Page_Send();
			$render->text = sprintf( '[visualizer id="%d"]', $this->_chart->ID );

			wp_iframe( array( $render, 'render') );
			return;
		}

		$data = $this->_getChartArray();

		$sidebar = '';
		$sidebar_class = 'Visualizer_Render_Sidebar_Type_' . ucfirst( $data['type'] );
		if ( class_exists( $sidebar_class, true ) ) {
			$sidebar = new $sidebar_class( $data['settings'] );
			$sidebar->__series = $data['series'];
			$sidebar->__data = $data['data'];
		}

		unset( $data['settings']['width'], $data['settings']['height'] );

		wp_enqueue_style( 'visualizer-frame' );
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_style( 'visualizer-frame' );

		wp_enqueue_script( 'visualizer-preview' );
		wp_enqueue_script( 'visualizer-render' );
		wp_localize_script( 'visualizer-render', 'visualizer', array(
			'l10n'   => array(
				'remotecsv_prompt' => esc_html__( 'Please, enter the URL of CSV file:', Visualizer_Plugin::NAME ),
				'invalid_source'   => esc_html__( 'You have entered invalid URL. Please, insert proper URL.', Visualizer_Plugin::NAME ),
			),
			'charts' => array(
				'canvas' => $data,
			),
		) );

		$render = new Visualizer_Render_Page_Data();
		$render->chart = $this->_chart;
		$render->type = $data['type'];

		$render->sidebar = $sidebar;
		if ( filter_input( INPUT_GET, 'library', FILTER_VALIDATE_BOOLEAN ) ) {
			$render->button = filter_input( INPUT_GET, 'action' ) == Visualizer_Plugin::ACTION_EDIT_CHART
				? esc_html__( 'Save Chart', Visualizer_Plugin::NAME )
				: esc_html__( 'Create Chart', Visualizer_Plugin::NAME );
		} else {
			$render->button = esc_attr__( 'Insert Chart', Visualizer_Plugin::NAME );
		}

        if( defined( 'Visualizer_Pro' ) ){
            global $Visualizer_Pro;
            $Visualizer_Pro->_enqueueScriptsAndStyles($data);
        }

		$this->_addAction( 'admin_head', 'renderFlattrScript' );

		wp_iframe( array( $render, 'render') );
    }
    // changed by Ash/Upwork

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
	 * Parses uploaded CSV file and saves new data for the chart.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function uploadData() {
		// validate nonce
		if ( !wp_verify_nonce( filter_input( INPUT_GET, 'nonce' ) ) ) {
			status_header( 403 );
			exit;
		}

		// check chart, if chart exists
		$chart_id = filter_input( INPUT_GET, 'chart', FILTER_VALIDATE_INT );
		if ( !$chart_id || !( $chart = get_post( $chart_id ) ) || $chart->post_type != Visualizer_Plugin::CPT_VISUALIZER ) {
			status_header( 400 );
			exit;
		}

		$source = null;
		$render = new Visualizer_Render_Page_Update();
		if ( filter_input( INPUT_POST, 'remote_data', FILTER_VALIDATE_URL ) ) {
			$source =  new Visualizer_Source_Csv_Remote( $_POST['remote_data'] );
		} elseif ( isset( $_FILES['local_data'] ) && $_FILES['local_data']['error'] == 0 ) {
			$source =  new Visualizer_Source_Csv( $_FILES['local_data']['tmp_name'] );

        // Added by Ash/Upwork
		} elseif ( defined( 'Visualizer_Pro' ) && isset( $_POST['chart_data'] ) && strlen( $_POST['chart_data'] ) > 0){
            global $Visualizer_Pro;
            $source = $Visualizer_Pro->_handleChartData($_POST['chart_data']);
        // Added by Ash/Upwork

		} else  {
			$render->message = esc_html__( "CSV file with chart data was not uploaded. Please, try again.", Visualizer_Plugin::NAME );
		}

		if ( $source ) {
			if ( $source->fetch() ) {
				$chart->post_content = $source->getData();
				wp_update_post( $chart->to_array() );

				update_post_meta( $chart->ID, Visualizer_Plugin::CF_SERIES, $source->getSeries() );
				update_post_meta( $chart->ID, Visualizer_Plugin::CF_SOURCE, $source->getSourceName() );
				update_post_meta( $chart->ID, Visualizer_Plugin::CF_DEFAULT_DATA, 0 );

				$render->data = json_encode( $source->getRawData() );
				$render->series = json_encode( $source->getSeries() );
			} else {
				$render->message = esc_html__( "CSV file is broken or invalid. Please, try again.", Visualizer_Plugin::NAME );
			}
		}

		$render->render();
		exit;
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
		$nonce = wp_verify_nonce( filter_input( INPUT_GET, 'nonce' ), Visualizer_Plugin::ACTION_CLONE_CHART );
		$capable = current_user_can( 'edit_posts' );
		if ( $nonce && $capable ) {
			$chart_id = filter_input( INPUT_GET, 'chart', FILTER_VALIDATE_INT, array( 'options' => array( 'min_range' => 1 ) ) );
			if ( $chart_id ) {
				$chart = get_post( $chart_id );
				$success = $chart && $chart->post_type == Visualizer_Plugin::CPT_VISUALIZER;
			}
		}

		$redirect = wp_get_referer();
		if ( $success ) {
			$new_chart_id = wp_insert_post( array(
				'post_type'    => Visualizer_Plugin::CPT_VISUALIZER,
				'post_title'   => 'Visualization',
				'post_author'  => get_current_user_id(),
				'post_status'  => $chart->post_status,
				'post_content' => $chart->post_content,
			) );

			if ( $new_chart_id && !is_wp_error( $new_chart_id ) ) {
				add_post_meta( $new_chart_id, Visualizer_Plugin::CF_CHART_TYPE, get_post_meta( $chart_id, Visualizer_Plugin::CF_CHART_TYPE, true ) );
				add_post_meta( $new_chart_id, Visualizer_Plugin::CF_DEFAULT_DATA, get_post_meta( $chart_id, Visualizer_Plugin::CF_DEFAULT_DATA, true ) );
				add_post_meta( $new_chart_id, Visualizer_Plugin::CF_SOURCE, get_post_meta( $chart_id, Visualizer_Plugin::CF_SOURCE, true ) );
				add_post_meta( $new_chart_id, Visualizer_Plugin::CF_SERIES, get_post_meta( $chart_id, Visualizer_Plugin::CF_SERIES, true ) );
				add_post_meta( $new_chart_id, Visualizer_Plugin::CF_SETTINGS, get_post_meta( $chart_id, Visualizer_Plugin::CF_SETTINGS, true ) );

				$redirect = add_query_arg( array(
					'page' => 'visualizer',
					'type' => filter_input( INPUT_GET, 'type' ),
				), admin_url( 'upload.php' ) );
			}
		}

		wp_redirect( $redirect );
		exit;
	}

}