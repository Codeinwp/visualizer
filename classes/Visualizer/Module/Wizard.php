<?php
/**
 * The class for handle setup wizard stuff.
 *
 * @category Visualizer
 * @package Module
 *
 * @since 3.9
 */

/**
 * Setup wizard main class.
 */
class Visualizer_Module_Wizard extends Visualizer_Module {

	/**
	 * Store name class.
	 */
	const NAME = __CLASS__;

	/**
	 * Option name.
	 */
	const OPTION_NAME = 'visualizer_wizard_data';

	/**
	 * Wizard data
	 *
	 * @access private
	 * @var $wizard_data array
	 */
	private $wizard_data = array();

	/**
	 * Constructor.
	 *
	 * @since 3.9
	 *
	 * @access public
	 *
	 * @param Visualizer_Plugin $plugin The instance of the plugin.
	 */
	public function __construct( Visualizer_Plugin $plugin ) {
		parent::__construct( $plugin );
		$this->_addFilter( 'admin_body_class', 'addWizardClasses' );
		$this->_addAction( 'admin_action_visualizer_dismiss_wizard', 'dismissWizard' );
		$this->_addAction( 'admin_menu', 'registerAdminMenu' );
		$this->_addAction( 'wp_ajax_visualizer_wizard_step_process', 'visualizer_wizard_step_process' );
		$this->wizard_data = get_option( self::OPTION_NAME, array() );
	}

	/**
	 * Registers admin menu for visualizer library.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function registerAdminMenu() {
		if ( ! Visualizer_Module::is_pro() && get_option( 'visualizer_fresh_install', false ) ) {
			$hook = add_submenu_page(
				Visualizer_Plugin::NAME,
				__( 'Setup Wizard', 'visualizer' ),
				__( 'Setup Wizard', 'visualizer' ),
				'manage_options',
				'visualizer-setup-wizard',
				array(
					$this,
					'visualizer_setup_wizard_page',
				)
			);
			add_action( "load-$hook", array( $this, 'visualizer_load_setup_wizard_page' ) );
		}
	}

	/**
	 * Method to register the setup wizard page.
	 *
	 * @access public
	 */
	public function visualizer_setup_wizard_page() {
		include VISUALIZER_ABSPATH . '/templates/setup-wizard.php';
	}

	/**
	 * Add classes to make the wizard full screen.
	 *
	 * @param string $classes Body classes.
	 * @return string
	 */
	public static function addWizardClasses( $classes ) {
		if ( get_option( 'visualizer_fresh_install', false ) ) {
			$classes .= ' vz-wizard-fullscreen';
		}
		return trim( $classes );
	}

	/**
	 * Load setup wizard page.
	 *
	 * @access public
	 */
	public function visualizer_load_setup_wizard_page() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['page'] ) && 'visualizer-setup-wizard' === $_GET['page'] ) {
			remove_all_actions( 'admin_notices' );
		}
		add_action( 'admin_enqueue_scripts', array( $this, 'visualizer_enqueue_setup_wizard_scripts' ) );
		add_filter( Visualizer_Plugin::FILTER_GET_CHART_SETTINGS, array( $this, 'visualizer_filter_chart_settings' ) );
	}

	/**
	 * Enqueue setup wizard required scripts.
	 *
	 * @access public
	 */
	public function visualizer_enqueue_setup_wizard_scripts() {
		wp_enqueue_style( 'jquery-slick', VISUALIZER_ABSURL . 'css/lib/slick.min.css', array(), Visualizer_Plugin::VERSION );
		wp_enqueue_style( 'jquery-smart-wizard', VISUALIZER_ABSURL . 'css/lib/smart_wizard_all.min.css', array(), Visualizer_Plugin::VERSION );
		wp_enqueue_style( 'visualizer-setup-wizard', VISUALIZER_ABSURL . 'css/style-wizard.css', array(), Visualizer_Plugin::VERSION, 'all' );

		wp_register_script( 'jquery-slick', VISUALIZER_ABSURL . 'js/lib/slick.min.js', array( 'jquery' ), Visualizer_Plugin::VERSION, true );
		wp_enqueue_script( 'jquery-smart-wizard', VISUALIZER_ABSURL . 'js/lib/jquery.smartWizard.min.js', array( 'jquery', 'jquery-slick', 'clipboard' ), Visualizer_Plugin::VERSION, true );
		wp_enqueue_script( 'visualizer-setup-wizard', VISUALIZER_ABSURL . 'js/setup-wizard.js', array( 'jquery' ), Visualizer_Plugin::VERSION, true );
		wp_localize_script(
			'visualizer-setup-wizard',
			'visualizerSetupWizardData',
			array(
				'adminPage'           => add_query_arg( 'page', Visualizer_Plugin::NAME, admin_url( 'admin.php' ) ),
				'ajax'                => array(
					'url'      => admin_url( 'admin-ajax.php' ),
					'security' => wp_create_nonce( VISUALIZER_ABSPATH ),
				),
				'errorMessages'       => array(
					'requiredEmail' => __( 'This field is required.', 'visualizer' ),
					'invalidEmail'  => __( 'Please enter a valid email address.', 'visualizer' ),
				),
				'nextButtonText'      => __( 'Next Step', 'visualizer' ),
				'backButtonText'      => __( 'Back', 'visualizer' ),
				'draftPageButtonText' => array(
					'firstButtonText'  => __( 'Save And Continue', 'visualizer' ),
					'secondButtonText' => __( 'Continue', 'visualizer' ),
				),
			)
		);
	}

	/**
	 * Dismiss setup wizard.
	 *
	 * @param bool $redirect_to_dashboard Redirect to dashboard.
	 * @return bool|void
	 */
	public function dismissWizard( $redirect_to_dashboard = true ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$status = isset( $_REQUEST['status'] ) ? (int) $_REQUEST['status'] : 0;
		update_option( 'visualizer_fresh_install', $status );
		delete_option( 'visualizer_wizard_data' );
		if ( false !== $redirect_to_dashboard ) {
			wp_safe_redirect( add_query_arg( 'page', Visualizer_Plugin::NAME, admin_url( 'admin.php' ) ) );
			exit;
		}
		return true;
	}

	/**
	 * Setup wizard process.
	 */
	public function visualizer_wizard_step_process() {
		check_ajax_referer( VISUALIZER_ABSPATH, 'security' );
		$step = ! empty( $_POST['step'] ) ? filter_input( INPUT_POST, 'step', FILTER_SANITIZE_STRING ) : 1;
		switch ( $step ) {
			case 'step_2':
				$this->setup_wizard_import_chart();
				break;
			case 'step_4':
				$this->setup_wizard_install_plugin();
				break;
			case 'step_subscribe':
				$this->setup_wizard_subscribe_process();
				break;
			case 'create_draft_page':
				$this->setup_wizard_create_draft_page();
				break;
			default:
				wp_send_json( array( 'status' => 0 ) );
				break;
		}
	}

	/**
	 * Step: 2 import chart.
	 */
	private function setup_wizard_import_chart() {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$chart_type   = ! empty( $_POST['chart_type'] ) ? filter_input( INPUT_POST, 'chart_type', FILTER_SANITIZE_STRING ) : '';
		$chart_status = Visualizer_Module_Admin::checkChartStatus( $chart_type );
		if ( ! $chart_status ) {
			wp_send_json(
				array(
					'success' => 0,
				)
			);
			exit;
		}

		$source = new Visualizer_Source_Csv( VISUALIZER_ABSPATH . DIRECTORY_SEPARATOR . 'samples' . DIRECTORY_SEPARATOR . $chart_type . '.csv' );
		$source->fetch();
		$series   = $source->getSeries();
		$response = array(
			'success' => 2,
			'message' => __( 'Something went wrong while importing the chart', 'visualizer' ),
		);

		$data     = $source->getData();
		$args     = array(
			'post_type'    => Visualizer_Plugin::CPT_VISUALIZER,
			'post_title'   => 'Visualization',
			'post_author'  => get_current_user_id(),
			'post_status'  => 'publish',
			'post_content' => $data,
		);
		$chart_id = wp_insert_post( $args );

		if ( $chart_id && ! is_wp_error( $chart_id ) ) {
			// Clear existing chart cache.
			$cache_key = Visualizer_Plugin::CF_CHART_CACHE . '_' . $chart_id;
			delete_transient( $cache_key );

			update_post_meta( $chart_id, Visualizer_Plugin::CF_CHART_TYPE, $chart_type );
			update_post_meta( $chart_id, Visualizer_Plugin::CF_DEFAULT_DATA, 1 );
			update_post_meta( $chart_id, Visualizer_Plugin::CF_SOURCE, $source->getSourceName() );
			update_post_meta( $chart_id, Visualizer_Plugin::CF_SERIES, $series );
			update_post_meta( $chart_id, Visualizer_Plugin::CF_CHART_LIBRARY, '' );

			$data           = maybe_unserialize( $data );
			$setting_series = array();
			$setting_slices = array();
			foreach ( $data as $s ) {
				$setting_series[] = array(
					'visibleInLegend' => '',
					'lineWidth'       => '',
					'pointSize'       => '',
					'format'          => '',
					'curveType'       => '',
					'color'           => '',
					'role'            => '',
				);
				$setting_slices[] = array(
					'offset' => 0,
					'color'  => '',
				);
			}
			update_post_meta(
				$chart_id,
				Visualizer_Plugin::CF_SETTINGS,
				array(
					'title'           => '',
					'titlePosition'   => '',
					'titleTextStyle'  => array(
						'color' => '#000',
					),
					'legend'          => array(
						'position'  => 'right',
						'alignment' => 15,
						'textStyle' => array(
							'color' => '#000',
							'text'  => 'both',
						),
					),
					'tooltip'         => array(
						'trigger'       => 'focus',
						'showColorCode' => 0,
						'showColorCode' => 0,
					),
					'animation'       => array(
						'startup'  => 0,
						'duration' => '',
						'easing'   => 'linear',
					),
					'width'           => '',
					'height'          => '',
					'keepAspectRatio' => false,
					'isStacked'       => false,
					'lazy_load_chart' => true,
					'backgroundColor' => array(
						'strokeWidth' => '',
						'stroke'      => '',
						'fill'        => '',
					),
					'chartArea'       => array(
						'left'   => '',
						'top'    => '',
						'width'  => '',
						'height' => '',
					),
					'focusTarget'     => 'datum',
					'series'          => $setting_series,
					'slices'          => $setting_slices,
					'vAxis'           => array(
						'title'          => '',
						'textPosition'   => '',
						'direction'      => 1,
						'baselineColor'  => '#000',
						'textStyle'      => array(
							'color' => '#000',
						),
						'format'         => '',
						'gridlines'      => array(
							'count' => '',
							'color' => '#ccc',
						),
						'minorGridlines' => array(
							'count' => '',
							'color' => '',
						),
						'viewWindow'     => array(
							'max' => '',
							'min' => '',
						),
					),
					'hAxis'           => array(
						'title'          => '',
						'textPosition'   => '',
						'direction'      => 1,
						'baselineColor'  => '#000',
						'textStyle'      => array(
							'color' => '#000',
						),
						'format'         => '',
						'gridlines'      => array(
							'count' => '',
							'color' => '#ccc',
						),
						'minorGridlines' => array(
							'count' => '',
							'color' => '',
						),
						'viewWindow'     => array(
							'max' => '',
							'min' => '',
						),
					),
					'customcss' => array(
						'headerRow' => array(
							'background-color' => '',
							'color' => '',
							'transform' => '',
						),
						'tableRow' => array(
							'background-color' => '',
							'color' => '',
							'transform' => '',
						),
						'oddTableRow' => array(
							'background-color' => '',
							'color' => '',
							'transform' => '',
						),
						'selectedTableRow' => array(
							'background-color' => '',
							'color' => '',
							'transform' => '',
						),
						'hoverTableRow' => array(
							'background-color' => '',
							'color' => '',
							'transform' => '',
						),
						'headerCell' => array(
							'background-color' => '',
							'color' => '',
							'transform' => '',
						),
						'tableCell' => array(
							'background-color' => '',
							'color' => '',
							'transform' => '',
						),
					),
				)
			);
			$wizard_data = array(
				'chart_type' => $chart_type,
				'chart_id'   => $chart_id,
			);
			$this->update_wizard_data( $wizard_data, false );
			$response = array(
				'success' => 1,
			);
		}
		wp_send_json( $response );
		exit;
	}

	/**
	 * Update wizard data.
	 *
	 * @param array $data Wizard data.
	 * @param bool  $merge_option Merge wizard data.
	 * @return bool
	 */
	private function update_wizard_data( $data = array(), $merge_option = true ) {
		if ( $merge_option ) {
			$this->wizard_data = get_option( self::OPTION_NAME, array() );
			$data              = array_merge( $this->wizard_data, $data );
		}
		return update_option( self::OPTION_NAME, $data );
	}

	/**
	 * Step: 3 Create draft page.
	 *
	 * @param bool $return_page_id Page ID.
	 */
	private function setup_wizard_create_draft_page( $return_page_id = false ) {
		$add_basic_shortcode = ! empty( $_POST['add_basic_shortcode'] ) ? sanitize_text_field( wp_unslash( $_POST['add_basic_shortcode'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$add_basic_shortcode = 'true' === $add_basic_shortcode ? true : false;
		$basic_shortcode     = ! empty( $_POST['basic_shortcode'] ) ? filter_input( INPUT_POST, 'basic_shortcode', FILTER_SANITIZE_STRING ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing

		if ( ! $add_basic_shortcode ) {
			wp_send_json(
				array(
					'status' => 1,
				)
			);
		}
		if ( function_exists( 'use_block_editor_for_post_type' ) && use_block_editor_for_post_type( 'page' ) ) {
			$this->wizard_data = get_option( self::OPTION_NAME, array() );
			if ( ! empty( $this->wizard_data['chart_id'] ) ) {
				$block_data      = array(
					'id'    => $this->wizard_data['chart_id'],
					'lazy'  => '-1',
					'route' => 'chartSelect',
				);
				$basic_shortcode = '<!-- wp:visualizer/chart ' . wp_json_encode( $block_data ) . ' /-->';
			}
		}
		$post_title = __( 'Visualizer Demo Page', 'visualizer' );
		$page_id    = post_exists( $post_title, '', '', 'page' );
		$args       = array(
			'post_type'    => 'page',
			'post_title'   => $post_title,
			'post_content' => $add_basic_shortcode ? $basic_shortcode : '',
			'post_status'  => 'draft',
		);
		if ( ! $page_id ) {
			$page_id = wp_insert_post( $args );
		} else {
			$args['ID'] = $page_id;
			$page_id    = wp_update_post( $args );
		}

		if ( $page_id ) {
			// Delete previous meta data.
			$meta = get_post_meta( $page_id );
			foreach ( $meta as $key => $value ) {
				delete_post_meta( $page_id, $key );
			}
			// Update wizard data.
			$wizard_data['page_id'] = $page_id;
			$this->update_wizard_data( $wizard_data );
		}
		if ( $return_page_id ) {
			return $page_id;
		}
		wp_send_json(
			array(
				'status' => $page_id,
			)
		);
	}

	/**
	 * Step: 3 Install plugin.
	 */
	private function setup_wizard_install_plugin() {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$slug = ! empty( $_POST['slug'] ) ? filter_input( INPUT_POST, 'slug', FILTER_SANITIZE_STRING ) : '';
		if ( empty( $slug ) ) {
			wp_send_json(
				array(
					'status'  => 0,
					'message' => __( 'No plugin specified.', 'visualizer' ),
				)
			);
		}

		if ( ! current_user_can( 'install_plugins' ) ) {
			wp_send_json(
				array(
					'status'  => 0,
					'message' => __( 'Sorry, you are not allowed to install plugins on this site.', 'visualizer' ),
				)
			);
		}

		if ( ! empty( $slug ) ) {
			require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
			include_once ABSPATH . 'wp-admin/includes/plugin-install.php';

			$api = plugins_api(
				'plugin_information',
				array(
					'slug'   => sanitize_key( wp_unslash( $slug ) ),
					'fields' => array(
						'sections' => false,
					),
				)
			);

			if ( is_wp_error( $api ) ) {
				wp_send_json(
					array(
						'status'  => 0,
						'message' => $api->get_error_message(),
					)
				);
			}

			$skin     = new WP_Ajax_Upgrader_Skin();
			$upgrader = new Plugin_Upgrader( $skin );
			$result   = $upgrader->install( $api->download_link );
			if ( is_wp_error( $result ) ) {
				wp_send_json(
					array(
						'status'  => 0,
						'message' => $api->get_error_message(),
					)
				);
			} elseif ( is_wp_error( $skin->result ) ) {
				if ( 'folder_exists' !== $skin->result->get_error_code() ) {
					wp_send_json(
						array(
							'status'  => 0,
							'message' => $skin->result->get_error_message(),
						)
					);
				}
			} elseif ( $skin->get_errors()->has_errors() ) {
				if ( 'folder_exists' !== $skin->get_error_code() ) {
					wp_send_json(
						array(
							'status'  => 0,
							'message' => $skin->get_error_message(),
						)
					);
				}
			} elseif ( is_null( $result ) ) {
				global $wp_filesystem;
				$status = array();
				$status['message'] = __( 'Unable to connect to the filesystem. Please confirm your credentials.', 'visualizer' );

				// Pass through the error from WP_Filesystem if one was raised.
				if ( $wp_filesystem instanceof WP_Filesystem_Base && is_wp_error( $wp_filesystem->errors ) && $wp_filesystem->errors->has_errors() ) {
					$status['message'] = esc_html( $wp_filesystem->errors->get_error_message() );
				}

				wp_send_json( $status );
			}

			activate_plugin( 'optimole-wp/optimole-wp.php' );
			delete_transient( 'optml_fresh_install' );
			// Update wizard data.
			$wizard_data['enable_perfomance'] = true;
			$this->update_wizard_data( $wizard_data );

			wp_send_json(
				array(
					'status' => 1,
				)
			);
		}
	}

	/**
	 * Step: 4 skip and subscribe process.
	 */
	private function setup_wizard_subscribe_process() {
		$segment     = 0;
		$wizard_data = get_option( self::OPTION_NAME, array() );
		$chart_type  = ! empty( $wizard_data['chart_type'] ) ? $wizard_data['chart_type'] : '';
		$chart_id    = ! empty( $wizard_data['chart_id'] ) ? $wizard_data['chart_id'] : '';
		$page_id     = ! empty( $wizard_data['page_id'] ) ? $wizard_data['page_id'] : '';
		$response    = array(
			'status'      => 0,
			'redirect_to' => '',
			'message'     => '',
		);

		$with_subscribe = ! empty( $_POST['with_subscribe'] ) ? (bool) $_POST['with_subscribe'] : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$email          = ! empty( $_POST['email'] ) ? filter_input( INPUT_POST, 'email', FILTER_SANITIZE_EMAIL ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$chart_types    = array(
			'pie'     => 1,
			'bar'     => 2,
			'line'    => 3,
			'tabular' => 4,
		);
		if ( $chart_type && ! empty( $chart_types[ $chart_type ] ) ) {
			$segment = $chart_types[ $chart_type ];
		}
		if ( ! empty( $page_id ) ) {
			$response = array(
				'status'      => 1,
				'redirect_to' => get_edit_post_link( $page_id, 'db' ),
				'message'     => __( 'Redirecting to draft page', 'visualizer' ),
			);
		} else {
			$response = array(
				'status'      => 1,
				'redirect_to' => add_query_arg( 'page', 'visualizer', admin_url( 'admin.php' ) ),
				'message'     => __( 'Redirecting to visualizer dashboard', 'visualizer' ),
			);
		}

		if ( $with_subscribe && is_email( $email ) ) {
			$request_res = wp_remote_post(
				VISUALIZER_SUBSCRIBE_API,
				array(
					'timeout' => 100,
					'headers' => array(
						'Content-Type'  => 'application/json',
						'Cache-Control' => 'no-cache',
						'Accept'        => 'application/json, */*;q=0.1',
					),
					'body'    => wp_json_encode(
						array(
							'slug'  => 'visualizer',
							'site'  => home_url(),
							'email' => $email,
							'data'  => array(
								'segment' => $segment,
							),
						)
					),
				)
			);
			if ( ! is_wp_error( $request_res ) ) {
				$body = json_decode( wp_remote_retrieve_body( $request_res ) );
				if ( 'success' === $body->code ) {
					$this->dismissWizard( false );
					wp_send_json( $response );
				}
			}
			wp_send_json(
				array(
					'status'      => 0,
					'redirect_to' => '',
					'message'     => '',
				)
			);
		} else {
			$this->dismissWizard( false );
			wp_send_json( $response );
		}
	}

	/**
	 * Filter chart setting.
	 *
	 * @param array $settings Chart settings.
	 * @return array
	 */
	public function visualizer_filter_chart_settings( $settings ) {
		$settings['backgroundColor'] = array(
			'fill'        => '#39c3d21a',
			'fillOpacity' => '.1',
		);
		return $settings;
	}
}
