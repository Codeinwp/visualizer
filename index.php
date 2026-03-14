<?php
/*
	Plugin Name: Visualizer: Tables and Charts for WordPress
	Plugin URI: https://themeisle.com/plugins/visualizer-charts-and-graphs/
	Description: Effortlessly create and embed responsive charts and tables with Visualizer, a powerful WordPress plugin that enhances data presentation from multiple sources.
	Version: 3.11.15
	Author: Themeisle
	Author URI: http://themeisle.com
	License: GPL v2.0 or later
	WordPress Available:  yes
	Requires License:    no
	Pro Slug:    visualizer-pro
	License URI: http://www.opensource.org/licenses/gpl-license.php
*/

// Prevent direct access to the plugin folder.
if ( ! defined( 'ABSPATH' ) ) {
	header( 'HTTP/1.0 404 Not Found', true, 404 );
	exit;
}
// don't load the plugin, if it has been already loaded
if ( class_exists( 'Visualizer_Plugin', false ) ) {
	return;
}

// support for pro versions before 3.3.0
if ( class_exists( 'Visualizer_Pro', false ) ) {
	define( 'VISUALIZER_PRO', true );
} else {
	defined( 'VISUALIZER_PRO' ) || define( 'VISUALIZER_PRO', false );
}

/**
 * Automatically loads classes for the plugin. Checks a namespace and loads only
 * approved classes.
 *
 * @since 1.0.0
 *
 * @param string $class_name The class name to autoload.
 *
 * @return boolean Returns TRUE if the class is located. Otherwise FALSE.
 */
function visualizer_autoloader( $class_name ) {
	$namespaces = array( 'Visualizer' );
	foreach ( $namespaces as $namespace ) {
		if ( substr( $class_name, 0, strlen( $namespace ) ) === $namespace ) {
			$filename = __DIR__ . str_replace( '_', DIRECTORY_SEPARATOR, "_classes_{$class_name}.php" );
			if ( is_readable( $filename ) ) {
				require $filename;

				return true;
			}
		}
	}

	return false;
}

/**
 * Instantiates the plugin and setup all modules.
 *
 * @since 1.0.0
 */
function visualizer_launch() {
	// setup environment
	define( 'VISUALIZER_BASEFILE', __FILE__ );
	define( 'VISUALIZER_BASENAME', plugin_basename( __FILE__ ) );
	define( 'VISUALIZER_ABSURL', plugins_url( '/', __FILE__ ) );
	define( 'VISUALIZER_ABSPATH', __DIR__ );
	define( 'VISUALIZER_DIRNAME', basename( VISUALIZER_ABSPATH ) );
	define( 'VISUALIZER_REST_VERSION', 1 );
	// if the below is true, then the js/customization.js in the plugin folder will be used instead of the one in the uploads folder (if it exists).
	// this is also used in Block.php
	if ( ! defined( 'VISUALIZER_TEST_JS_CUSTOMIZATION' ) ) {
		define( 'VISUALIZER_TEST_JS_CUSTOMIZATION', false );
	}

	if ( ! defined( 'VISUALIZER_CSV_DELIMITER' ) ) {
		define( 'VISUALIZER_CSV_DELIMITER', ',' );
	}
	if ( ! defined( 'VISUALIZER_CSV_ENCLOSURE' ) ) {
		define( 'VISUALIZER_CSV_ENCLOSURE', '"' );
	}
	if ( ! defined( 'VISUALIZER_DEBUG' ) ) {
		define( 'VISUALIZER_DEBUG', false );
	}

	define( 'VISUALIZER_SKIP_CHART_TYPE_PAGE', true );

	// if x and y features are required, this value should read x,y or x|y or x;y.
	define( 'VISUALIZER_ENABLE_BETA_FEATURES', '' );

	// the link to pre-build queries.
	define( 'VISUALIZER_DB_QUERY_DOC_URL', 'https://docs.themeisle.com/article/970-visualizer-sample-queries-to-generate-charts' );
	define( 'VISUALIZER_MAIN_DOC', 'https://docs.themeisle.com/category/657-visualizer' );
	define( 'VISUALIZER_DOC_COLLECTION', 'https://docs.themeisle.com/search?collectionId=561ec249c69791452ed4bceb&query=#+visualizer' );
	define( 'VISUALIZER_DEMO_URL', 'https://demo.themeisle.com/visualizer/#' );
	define( 'VISUALIZER_CODE_SNIPPETS_URL', 'https://docs.themeisle.com/category/726-visualizer' );
	define( 'VISUALIZER_SUBSCRIBE_API', 'https://api.themeisle.com/tracking/subscribe' );

	// to redirect all themeisle_log_event to error log.
	define( 'VISUALIZER_LOCAL_DEBUG', false );

	// instantiate the plugin
	$plugin = Visualizer_Plugin::instance();

	// instantiate Gutenberg block
	add_action(
		'plugins_loaded', function () {
			if ( function_exists( 'register_block_type' ) ) {
				Visualizer_Gutenberg_Block::get_instance();
			}}
	);

	// register Elementor widget
	add_action(
		'elementor/widgets/register',
		function ( $widgets_manager ) {
			require_once VISUALIZER_ABSPATH . '/classes/Visualizer/Elementor/Widget.php';
			$widgets_manager->register( new Visualizer_Elementor_Widget() );
		}
	);

	// Register the Visualizer icon for the Elementor widget panel.
	add_action(
		'elementor/editor/after_enqueue_styles',
		function () {
			$icon_url = VISUALIZER_ABSURL . 'images/visualizer-icon.svg';
			wp_add_inline_style(
				'elementor-icons',
				'.visualizer-elementor-icon { display:inline-block; width:1em; height:1em; background:url("' . esc_url( $icon_url ) . '") no-repeat center/contain; }'
			);
		}
	);

	// Enqueue Visualizer scripts inside the Elementor preview iframe.
	// Elementor serves the preview iframe as a shell page and injects widget HTML via
	// JavaScript (innerHTML), so wp_enqueue_script calls inside render() never reach the
	// iframe. We load all chart render libraries here so they are available when
	// elementor-widget-preview.js triggers visualizer:render:chart:start.
	add_action(
		'elementor/preview/enqueue_scripts',
		function () {
			do_action( 'visualizer_enqueue_scripts' );

			// ChartJS render library.
			if ( ! wp_script_is( 'numeral', 'registered' ) ) {
				wp_register_script( 'numeral', VISUALIZER_ABSURL . 'js/lib/numeral.min.js', [], Visualizer_Plugin::VERSION, true );
			}
			if ( ! wp_script_is( 'chartjs', 'registered' ) ) {
				wp_register_script( 'chartjs', VISUALIZER_ABSURL . 'js/lib/chartjs.min.js', [ 'numeral' ], null, true );
			}
			wp_enqueue_script( 'visualizer-render-chartjs-lib', VISUALIZER_ABSURL . 'js/render-chartjs.js', [ 'chartjs', 'visualizer-customization' ], Visualizer_Plugin::VERSION, true );

			// Google Charts render library.
			wp_enqueue_script( 'visualizer-google-jsapi', '//www.gstatic.com/charts/loader.js', [], null, true );
			wp_enqueue_script( 'visualizer-render-google-lib', VISUALIZER_ABSURL . 'js/render-google.js', [ 'visualizer-google-jsapi', 'visualizer-customization' ], Visualizer_Plugin::VERSION, true );

			// DataTable render library + styles.
			if ( ! wp_script_is( 'visualizer-datatables', 'registered' ) ) {
				wp_register_script( 'visualizer-datatables', VISUALIZER_ABSURL . 'js/lib/datatables.min.js', [ 'jquery' ], Visualizer_Plugin::VERSION, true );
			}
			wp_enqueue_script( 'visualizer-render-datatables-lib', VISUALIZER_ABSURL . 'js/render-datatables.js', [ 'visualizer-datatables', 'visualizer-customization' ], Visualizer_Plugin::VERSION, true );
			wp_enqueue_style( 'visualizer-datatables', VISUALIZER_ABSURL . 'css/lib/datatables.min.css', [], Visualizer_Plugin::VERSION );

			// Elementor widget preview handler — uses frontend/element_ready hook.
			wp_enqueue_script( 'visualizer-elementor-preview', VISUALIZER_ABSURL . 'js/elementor-widget-preview.js', [ 'jquery', 'elementor-frontend' ], Visualizer_Plugin::VERSION, true );

			// Prevent Elementor's editor-preview CSS from hiding our widget.
			// Elementor marks widgets without a content_template() as elementor-widget-empty
			// and adds display:none to .elementor-widget-empty when the panel is hidden
			// (.elementor-editor-preview on <body>). Our widget renders async (Google Charts
			// loads via callback), so the empty class is always present.
			wp_add_inline_style(
				'visualizer-datatables',
				'.elementor-editor-preview .elementor-widget-visualizer-chart.elementor-widget-empty { display: block !important; }'
			);
		}
	);

	// set general modules
	$plugin->setModule( Visualizer_Module_Utility::NAME );
	$plugin->setModule( Visualizer_Module_Setup::NAME );
	$plugin->setModule( Visualizer_Module_Sources::NAME );
	$plugin->setModule( Visualizer_Module_Chart::NAME );

	if ( is_admin() || defined( 'WP_TESTS_DOMAIN' ) ) {
		// set admin modules
		$plugin->setModule( Visualizer_Module_Admin::NAME );
	}

	// set frontend modules
	$plugin->setModule( Visualizer_Module_Frontend::NAME );

	$plugin->setModule( Visualizer_Module_AMP::NAME );

	// Set setup wizard module.
	$plugin->setModule( Visualizer_Module_Wizard::NAME );

	$vendor_file = VISUALIZER_ABSPATH . '/vendor/autoload.php';
	if ( is_readable( $vendor_file ) ) {
		include_once $vendor_file;
	}
	add_filter( 'themeisle_sdk_products', 'visualizer_register_sdk', 10, 1 );
	add_filter( 'pirate_parrot_log', 'visualizer_register_parrot', 10, 1 );
	add_filter(
		'themeisle_sdk_compatibilities/' . VISUALIZER_DIRNAME, function ( $compatibilities ) {
			$compatibilities['VisualizerPRO'] = array(
				'basefile'  => defined( 'VISUALIZER_PRO_BASEFILE' ) ? VISUALIZER_PRO_BASEFILE : '',
				'required'  => '1.8',
				'tested_up' => '1.14',
			);
			return $compatibilities;
		}
	);
	add_filter(
		'visualizer_about_us_metadata',
		function () {
			return array(
				'logo'             => esc_url( VISUALIZER_ABSURL . 'images/visualizer-logo.svg' ),
				'location'         => 'visualizer',
				'has_upgrade_menu' => ! Visualizer_Module::is_pro(),
				'upgrade_text'     => esc_html__( 'Get Visualizer Pro', 'visualizer' ),
				'upgrade_link'     => esc_url( tsdk_utmify( Visualizer_Plugin::PRO_TEASER_URL, 'sidebarMenuUpgrade', 'index' ) ),
			);
		}
	);

	if ( ! defined( 'TI_E2E_TESTING' ) && 'yes' === get_option( 'visualizer_logger_flag', 'no' ) ) {
		add_filter( 'themeisle_sdk_enable_telemetry', '__return_true' );
		add_filter(
			'themeisle_sdk_telemetry_products',
			function ( $products ) {
				$already_registered = false;

				$license = get_option( 'visualizer_pro_license_data', 'free' );
				if ( ! empty( $license ) && is_object( $license ) ) {
					$license = $license->key;
				}
				$track_hash = 'free' === $license ? 'free' : wp_hash( $license );

				foreach ( $products as &$product ) {
					if ( strstr( $product['slug'], 'visualizer' ) !== false ) {
						$already_registered   = true;
						$product['trackHash'] = $track_hash;
					}
				}

				if ( $already_registered ) {
					return $products;
				}

				// Add Visualizer to the list of products to track the usage of AI Block.
				$products[] = array(
					'slug'      => 'visualizer',
					'consent'   => 'yes' === get_option( 'visualizer_logger_flag', 'no' ),
					'trackHash' => $track_hash,
				);
				return $products;
			}
		);
	}
}

/**
 * Registers with the SDK
 *
 * @since    1.0.0
 */
function visualizer_register_sdk( $products ) {
	$products[] = VISUALIZER_BASEFILE;
	return $products;
}

/**
 * Registers with the parrot plugin
 *
 * @since    1.0.0
 */
function visualizer_register_parrot( $plugins ) {
	$plugins[] = Visualizer_Plugin::NAME;
	return $plugins;
}

// register autoloader function
spl_autoload_register( 'visualizer_autoloader' );
// launch the plugin
visualizer_launch();


if ( VISUALIZER_LOCAL_DEBUG ) {
	add_action( 'themeisle_log_event', 'visualizer_themeisle_log_event', 10, 5 );

	/**
	 * Redirect themeisle_log_event to error log.
	 */
	function visualizer_themeisle_log_event( $name, $msg, $type, $file, $line ) {
		if ( $name === Visualizer_Plugin::NAME ) {
			error_log( sprintf( '%s (%s:%d): %s', $type, $file, $line, $msg ) );
		}
	}
}
