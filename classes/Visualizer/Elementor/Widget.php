<?php
// +----------------------------------------------------------------------+
// | Copyright 2018  ThemeIsle (email : friends@themeisle.com)            |
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
// | Author: Hardeep Asrani <hardeep@themeisle.com>                       |
// +----------------------------------------------------------------------+
/**
 * Elementor widget for displaying Visualizer charts.
 *
 * @category Visualizer
 * @package Elementor
 *
 * @since 3.11.16
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Visualizer Elementor Widget
 */
class Visualizer_Elementor_Widget extends \Elementor\Widget_Base {

	/**
	 * Register all Elementor-related hooks for the Visualizer widget.
	 *
	 * Called once from index.php on the plugins_loaded action (after Elementor
	 * itself is confirmed present). Each inner hook fires at its normal time in
	 * the WordPress lifecycle, so timing is identical to registering them
	 * directly in visualizer_launch().
	 *
	 * @return void
	 */
	public static function register_hooks() {
		// Register the widget with Elementor's widget manager.
		add_action(
			'elementor/widgets/register',
			function ( $widgets_manager ) {
				$widgets_manager->register( new self() );
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
					wp_register_script( 'numeral', VISUALIZER_ABSURL . 'js/lib/numeral.min.js', array(), Visualizer_Plugin::VERSION, true );
				}
				if ( ! wp_script_is( 'chartjs', 'registered' ) ) {
					wp_register_script( 'chartjs', VISUALIZER_ABSURL . 'js/lib/chartjs.min.js', array( 'numeral' ), null, true );
				}
				wp_enqueue_script( 'visualizer-render-chartjs-lib', VISUALIZER_ABSURL . 'js/render-chartjs.js', array( 'chartjs', 'visualizer-customization' ), Visualizer_Plugin::VERSION, true );

				// Google Charts render library.
				wp_enqueue_script( 'visualizer-google-jsapi', '//www.gstatic.com/charts/loader.js', array(), null, true );
				wp_enqueue_script( 'visualizer-render-google-lib', VISUALIZER_ABSURL . 'js/render-google.js', array( 'visualizer-google-jsapi', 'visualizer-customization' ), Visualizer_Plugin::VERSION, true );

				// DataTable render library + styles.
				if ( ! wp_script_is( 'visualizer-datatables', 'registered' ) ) {
					wp_register_script( 'visualizer-datatables', VISUALIZER_ABSURL . 'js/lib/datatables.min.js', array( 'jquery' ), Visualizer_Plugin::VERSION, true );
				}
				wp_enqueue_script( 'visualizer-render-datatables-lib', VISUALIZER_ABSURL . 'js/render-datatables.js', array( 'visualizer-datatables', 'visualizer-customization' ), Visualizer_Plugin::VERSION, true );
				wp_enqueue_style( 'visualizer-datatables', VISUALIZER_ABSURL . 'css/lib/datatables.min.css', array(), Visualizer_Plugin::VERSION );

				// D3 render library (AI charts).
				$d3_renderer_asset = VISUALIZER_ABSPATH . '/classes/Visualizer/D3Renderer/build/index.asset.php';
				if ( file_exists( $d3_renderer_asset ) && ! wp_script_is( 'visualizer-d3-renderer', 'registered' ) ) {
					/**
					 * Ignore missing build asset in source checkout.
					 *
					 * @phpstan-ignore-next-line
					 */
					$d3_asset = include $d3_renderer_asset;
					wp_register_script(
						'visualizer-d3-renderer',
						VISUALIZER_ABSURL . 'classes/Visualizer/D3Renderer/build/index.js',
						array_merge( $d3_asset['dependencies'], array( 'jquery' ) ),
						$d3_asset['version'],
						true
					);
				}
				if ( wp_script_is( 'visualizer-d3-renderer', 'registered' ) ) {
					wp_enqueue_script( 'visualizer-d3-renderer' );
					wp_localize_script(
						'visualizer-d3-renderer',
						'vizD3Renderer',
						array(
							'iframeJsUrl' => VISUALIZER_ABSURL . 'classes/Visualizer/D3Renderer/build/iframe.js',
						)
					);
				}

				// Elementor widget preview handler — uses frontend/element_ready hook.
				wp_enqueue_script( 'visualizer-elementor-preview', VISUALIZER_ABSURL . 'js/elementor-widget-preview.js', array( 'jquery', 'elementor-frontend' ), Visualizer_Plugin::VERSION, true );

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
	}

	/**
	 * Get widget name.
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'visualizer-chart';
	}

	/**
	 * Get widget title.
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return esc_html__( 'Visualizer Chart', 'visualizer' );
	}

	/**
	 * Get widget icon.
	 *
	 * @return string Widget icon CSS class.
	 */
	public function get_icon() {
		return 'visualizer-elementor-icon';
	}

	/**
	 * Get widget categories.
	 *
	 * @return array<string> Widget categories.
	 */
	public function get_categories() {
		return array( 'general' );
	}

	/**
	 * Get widget keywords.
	 *
	 * @return array<string> Widget keywords.
	 */
	public function get_keywords() {
		return array( 'visualizer', 'chart', 'graph', 'table', 'data' );
	}

	/**
	 * Build the select options from all published Visualizer charts.
	 *
	 * @return array<int|string, string> Associative array of chart ID => label.
	 */
	private function get_chart_options() {
		static $options_cache = null;
		if ( null !== $options_cache ) {
			return $options_cache;
		}

		$options = array(
			'' => esc_html__( '— Select a chart —', 'visualizer' ),
		);

		$charts = get_posts(
			array(
				'post_type'      => Visualizer_Plugin::CPT_VISUALIZER,
				'posts_per_page' => -1,
				'post_status'    => 'publish',
				'orderby'        => 'title',
				'order'          => 'ASC',
				'no_found_rows'  => true,
			)
		);

		foreach ( $charts as $chart ) {
			$settings = get_post_meta( $chart->ID, Visualizer_Plugin::CF_SETTINGS );
			$title    = '#' . $chart->ID;
			if ( ! empty( $settings[0]['title'] ) ) {
				$title = $settings[0]['title'];
			}
			// ChartJS stores title as an array.
			if ( is_array( $title ) && isset( $title['text'] ) ) {
				$title = $title['text'];
			}
			if ( ! empty( $settings[0]['backend-title'] ) ) {
				$title = $settings[0]['backend-title'];
			}
			if ( empty( $title ) ) {
				$title = '#' . $chart->ID;
			}
			$options[ $chart->ID ] = $title;
		}

		$options_cache = $options;
		return $options_cache;
	}

	/**
	 * Register widget controls.
	 *
	 * @return void
	 */
	protected function register_controls() {
		$this->start_controls_section(
			'section_chart',
			array(
				'label' => esc_html__( 'Chart', 'visualizer' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$admin_url    = admin_url( 'admin.php?page=' . Visualizer_Plugin::NAME );
		$chart_options = $this->get_chart_options();
		$has_charts    = count( $chart_options ) > 1; // More than just the placeholder option.

		if ( $has_charts ) {
			$this->add_control(
				'chart_id',
				array(
					'label'   => esc_html__( 'Select Chart', 'visualizer' ),
					'type'    => \Elementor\Controls_Manager::SELECT,
					'options' => $chart_options,
					'default' => '',
				)
			);

			$this->add_control(
				'chart_notice',
				array(
					'type'            => \Elementor\Controls_Manager::RAW_HTML,
					'raw'             => sprintf(
						/* translators: 1: opening anchor tag, 2: closing anchor tag */
						esc_html__( 'You can create and manage your charts from the %1$sVisualizer dashboard%2$s.', 'visualizer' ),
						'<a href="' . esc_url( $admin_url ) . '" target="_blank">',
						'</a>'
					),
					'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
				)
			);
		} else {
			$this->add_control(
				'no_charts_notice',
				array(
					'type'            => \Elementor\Controls_Manager::RAW_HTML,
					'raw'             => sprintf(
						/* translators: 1: opening anchor tag, 2: closing anchor tag */
						esc_html__( 'No charts found. %1$sCreate a chart%2$s in the Visualizer dashboard first.', 'visualizer' ),
						'<a href="' . esc_url( $admin_url ) . '" target="_blank">',
						'</a>'
					),
					'content_classes' => 'elementor-panel-alert elementor-panel-alert-warning',
				)
			);
		}

		$this->end_controls_section();
	}

	/**
	 * Render the widget output on the frontend.
	 *
	 * @return void
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();
		$chart_id = ! empty( $settings['chart_id'] ) ? absint( $settings['chart_id'] ) : 0;

		if ( ! $chart_id ) {
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				echo '<p style="text-align:center;padding:20px;color:#888;">' . esc_html__( 'Please select a chart from the widget settings.', 'visualizer' ) . '</p>';
			}
			return;
		}

		// Detect Elementor edit / preview context early — needed before do_shortcode().
		$is_editor = \Elementor\Plugin::$instance->editor->is_edit_mode() ||
					\Elementor\Plugin::$instance->preview->is_preview_mode();

		// In the editor, force lazy-loading off so the chart renders immediately in the
		// preview iframe without requiring a user-interaction event (scroll, hover, etc.).
		// Also suppress action buttons (edit, export, etc.) — they are meaningless inside
		// the Elementor preview and the edit link does nothing there.
		if ( $is_editor ) {
			add_filter( 'visualizer_lazy_load_chart', '__return_false' );
			add_filter( 'visualizer_pro_add_actions', '__return_empty_array' );
		}

		// Ensure visualizer-customization is registered before the shortcode enqueues
		// visualizer-render-{library} which depends on it. wp_enqueue_scripts never fires
		// in admin or AJAX contexts (Elementor editor / AJAX re-render), so we trigger the
		// action manually. It is a no-op when already registered.
		do_action( 'visualizer_enqueue_scripts' );

		// Capture the shortcode output so we can parse the generated element ID.
		$html = do_shortcode( '[visualizer id="' . $chart_id . '"]' );

		if ( $is_editor ) {
			remove_filter( 'visualizer_lazy_load_chart', '__return_false' );
			remove_filter( 'visualizer_pro_add_actions', '__return_empty_array' );

			// The shortcode enqueues visualizer-render-{library} (render-facade.js).
			// Dequeue it so Elementor's AJAX response doesn't inject it into the preview
			// iframe. The preview page already loads render-google.js / render-chartjs.js
			// via elementor/preview/enqueue_scripts; injecting render-facade.js would add
			// a second visualizer:render:chart:start trigger causing duplicate renders.
			foreach ( wp_scripts()->queue as $handle ) {
				if ( ( 0 === strpos( $handle, 'visualizer-render-' ) || 'visualizer-d3-renderer' === $handle )
					&& 'visualizer-render-google-lib' !== $handle
					&& 'visualizer-render-chartjs-lib' !== $handle
					&& 'visualizer-render-datatables-lib' !== $handle ) {
					wp_dequeue_script( $handle );
				}
			}
		}

		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		if ( ! $is_editor ) {
			return;
		}

		// Extract the element ID generated by the shortcode (visualizer-{id}-{rand}).
		if ( ! preg_match( '/\bid="(visualizer-' . $chart_id . '-\d+)"/', $html, $matches ) ) {
			return;
		}
		$element_id = $matches[1];

		$chart = get_post( $chart_id );
		if ( ! $chart || Visualizer_Plugin::CPT_VISUALIZER !== $chart->post_type ) {
			return;
		}

		$type           = get_post_meta( $chart_id, Visualizer_Plugin::CF_CHART_TYPE, true );
		$series         = get_post_meta( $chart_id, Visualizer_Plugin::CF_SERIES, true );
		$chart_settings = get_post_meta( $chart_id, Visualizer_Plugin::CF_SETTINGS, true );
		$chart_data     = Visualizer_Module::get_chart_data( $chart, $type );

		if ( empty( $chart_settings['height'] ) ) {
			$chart_settings['height'] = '400';
		}

		// Read library from meta and normalise to the lowercase slugs that
		// render-google.js / render-chartjs.js / render-datatables.js and
		// elementor-widget-preview.js expect.
		$library     = get_post_meta( $chart_id, Visualizer_Plugin::CF_CHART_LIBRARY, true );
		$library_map = array(
			'GoogleCharts' => 'google',
			'ChartJS'      => 'chartjs',
			'DataTable'    => 'datatables',
		);
		if ( isset( $library_map[ $library ] ) ) {
			$library = $library_map[ $library ];
		} elseif ( ! $library ) {
			$library = 'google';
		}

		$series         = apply_filters( Visualizer_Plugin::FILTER_GET_CHART_SERIES, $series, $chart_id, $type );
		$chart_settings = apply_filters( Visualizer_Plugin::FILTER_GET_CHART_SETTINGS, $chart_settings, $chart_id, $type );
		$chart_settings = $this->apply_custom_css_class_names( $chart_settings, $chart_id );

		$chart_entry = array(
			'type'     => $type,
			'series'   => $series,
			'settings' => $chart_settings,
			'data'     => $chart_data,
			'library'  => $library,
		);

		// D3/AI charts store their rendering code in post meta — include it so
		// elementor-widget-preview.js can pass it to the D3 renderer.
		if ( 'd3' === $library ) {
			$chart_entry['code'] = get_post_meta( $chart_id, Visualizer_Module_AIBuilder::CF_D3_CODE, true );
		}

		// Elementor injects widget HTML via innerHTML, so <script type="text/javascript">
		// tags never execute in the preview iframe. Instead embed the chart data in a
		// JSON script element — it is preserved through innerHTML but not executed.
		// elementor-widget-preview.js reads it via the frontend/element_ready hook.
		printf(
			'<script type="application/json" class="visualizer-chart-data" data-element-id="%s">%s</script>',
			esc_attr( $element_id ),
			wp_json_encode( $chart_entry ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		);
	}

	/**
	 * Ensure custom CSS class mappings are present in settings for preview rendering.
	 *
	 * @param array<string, mixed> $settings Chart settings.
	 * @param int                  $chart_id Chart ID.
	 * @return array<string, mixed>
	 */
	private function apply_custom_css_class_names( $settings, $chart_id ) {
		if ( empty( $settings['customcss'] ) || ! is_array( $settings['customcss'] ) ) {
			return $settings;
		}

		$classes = array();
		$id      = 'visualizer-' . $chart_id;

		foreach ( $settings['customcss'] as $name => $element ) {
			if ( empty( $name ) || ! is_array( $element ) ) {
				continue;
			}
			$has_properties = false;
			foreach ( $element as $property => $value ) {
				if ( '' !== $property && '' !== $value && null !== $value ) {
					$has_properties = true;
					break;
				}
			}
			if ( ! $has_properties ) {
				continue;
			}
			$classes[ $name ] = $id . $name;
		}

		if ( ! empty( $classes ) ) {
			$settings['cssClassNames'] = $classes;
		}

		return $settings;
	}
}
