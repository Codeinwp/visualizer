<?php
/**
 * Registers the Visualizer abilities with the WordPress Abilities API.
 *
 * @package Visualizer
 */

/**
 * Abilities module class.
 *
 * Every ability is a thin wrapper over the chart storage, source classes and
 * hooks the editors already use. It is a no-op when the Abilities API is not
 * available (WordPress < 6.9).
 *
 * @category Visualizer
 * @package Module
 */
class Visualizer_Module_Abilities extends Visualizer_Module {

	const NAME = __CLASS__;

	const CATEGORY = 'visualizer';

	const MAX_ROWS     = 5000;
	const MAX_COLUMNS  = 100;
	const PREVIEW_ROWS = 20;
	const MAX_PER_PAGE = 100;

	/**
	 * Constructor.
	 *
	 * @access public
	 *
	 * @param Visualizer_Plugin $plugin The instance of the plugin.
	 */
	public function __construct( Visualizer_Plugin $plugin ) {
		parent::__construct( $plugin );

		// These hooks only fire when the Abilities API is available (WordPress 6.9+).
		$this->_addAction( 'wp_abilities_api_categories_init', 'registerCategory' );
		$this->_addAction( 'wp_abilities_api_init', 'registerAbilities' );
	}

	/**
	 * Registers the ability category.
	 *
	 * @access public
	 * @return void
	 */
	public function registerCategory() {
		if ( ! function_exists( 'wp_register_ability_category' ) ) {
			return;
		}

		wp_register_ability_category(
			self::CATEGORY,
			array(
				'label'       => __( 'Visualizer', 'visualizer' ),
				'description' => __( 'Create, read and refresh Visualizer charts and tables.', 'visualizer' ),
			)
		);
	}

	/**
	 * Registers the abilities.
	 *
	 * @access public
	 * @return void
	 */
	public function registerAbilities() {
		if ( ! function_exists( 'wp_register_ability' ) ) {
			return;
		}

		$column_schema = array(
			'type'       => 'object',
			'properties' => array(
				'name' => array(
					'type'        => 'string',
					'description' => __( 'Column label.', 'visualizer' ),
				),
				'type' => array(
					'type'        => 'string',
					'description' => __( 'Column data type.', 'visualizer' ),
					'enum'        => Visualizer_Source::getAllowedTypes(),
				),
			),
			'required'   => array( 'name', 'type' ),
		);

		$embed_schema = array(
			'type'       => 'object',
			'properties' => array(
				'shortcode' => array( 'type' => 'string' ),
				'block'     => array( 'type' => 'string' ),
			),
		);

		$rows_schema = array(
			'type'        => 'array',
			'description' => __( 'Data rows. Each row is an array of cell values in column order.', 'visualizer' ),
			'items'       => array(
				'type' => 'array',
			),
		);

		$preview_schema = array(
			'type'       => 'object',
			'properties' => array(
				'columns'   => array(
					'type'  => 'array',
					'items' => $column_schema,
				),
				'rows'      => $rows_schema,
				'row_count' => array( 'type' => 'integer' ),
			),
		);

		wp_register_ability(
			'visualizer/list-charts',
			array(
				'label'               => __( 'List charts', 'visualizer' ),
				'description'         => __( 'Lists the Visualizer charts the current user can manage, with their type, library, data source and shortcode.', 'visualizer' ),
				'category'            => self::CATEGORY,
				'input_schema'        => array(
					'type'       => 'object',
					'default'    => array(),
					'properties' => array(
						'page'     => array(
							'type'        => 'integer',
							'description' => __( 'Page of results.', 'visualizer' ),
							'default'     => 1,
							'minimum'     => 1,
						),
						'per_page' => array(
							'type'        => 'integer',
							'description' => __( 'Charts per page.', 'visualizer' ),
							'default'     => 20,
							'minimum'     => 1,
							'maximum'     => self::MAX_PER_PAGE,
						),
						'type'     => array(
							'type'        => 'string',
							'description' => __( 'Only return charts of this type (for example pie, line, bar, tabular).', 'visualizer' ),
						),
						'search'   => array(
							'type'        => 'string',
							'description' => __( 'Search term matched against the chart settings (title), like the chart library search.', 'visualizer' ),
						),
					),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'charts'      => array(
							'type'  => 'array',
							'items' => array(
								'type'       => 'object',
								'properties' => array(
									'id'        => array( 'type' => 'integer' ),
									'title'     => array( 'type' => 'string' ),
									'type'      => array( 'type' => 'string' ),
									'library'   => array( 'type' => 'string' ),
									'source'    => array( 'type' => 'string' ),
									'modified'  => array( 'type' => 'string' ),
									'shortcode' => array( 'type' => 'string' ),
								),
							),
						),
						'total'       => array( 'type' => 'integer' ),
						'total_pages' => array( 'type' => 'integer' ),
					),
				),
				'execute_callback'    => array( $this, 'executeListCharts' ),
				'permission_callback' => array( $this, 'canManageCharts' ),
				'meta'                => array(
					'annotations'  => array(
						'readonly'    => true,
						'destructive' => false,
						'idempotent'  => true,
					),
					'show_in_rest' => true,
				),
			)
		);

		wp_register_ability(
			'visualizer/get-chart',
			array(
				'label'               => __( 'Get chart', 'visualizer' ),
				'description'         => __( 'Returns the type, columns, rows, options, data source and embed markup (shortcode and block) of a chart. Source credentials are never returned.', 'visualizer' ),
				'category'            => self::CATEGORY,
				'input_schema'        => array(
					'type'       => 'object',
					'properties' => array(
						'chart_id' => array(
							'type'        => 'integer',
							'description' => __( 'The chart ID.', 'visualizer' ),
							'minimum'     => 1,
						),
					),
					'required'   => array( 'chart_id' ),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'id'      => array( 'type' => 'integer' ),
						'title'   => array( 'type' => 'string' ),
						'type'    => array( 'type' => 'string' ),
						'library' => array( 'type' => 'string' ),
						'columns' => array(
							'type'  => 'array',
							'items' => $column_schema,
						),
						'rows'    => $rows_schema,
						'options' => array(
							'type'                 => 'object',
							'additionalProperties' => true,
						),
						'source'  => array(
							'type'                 => 'object',
							'additionalProperties' => true,
						),
						'embed'   => $embed_schema,
					),
				),
				'execute_callback'    => array( $this, 'executeGetChart' ),
				'permission_callback' => array( $this, 'canEditChartInput' ),
				'meta'                => array(
					'annotations'  => array(
						'readonly'    => true,
						'destructive' => false,
						'idempotent'  => true,
					),
					'show_in_rest' => true,
				),
			)
		);

		wp_register_ability(
			'visualizer/upsert-chart',
			array(
				'label'               => __( 'Create or update chart', 'visualizer' ),
				'description'         => __( 'Creates a chart (no chart_id) or updates one (chart_id given) from typed columns, rows and options. With dry_run=true nothing is saved and the parsed data preview is returned. Chart types that need a higher plan are rejected.', 'visualizer' ),
				'category'            => self::CATEGORY,
				'input_schema'        => array(
					'type'       => 'object',
					'properties' => array(
						'chart_id' => array(
							'type'        => 'integer',
							'description' => __( 'The chart to update. Omit to create a new chart.', 'visualizer' ),
							'minimum'     => 1,
						),
						'title'    => array(
							'type'        => 'string',
							'description' => __( 'Chart name shown in the chart library.', 'visualizer' ),
						),
						'type'     => array(
							'type'        => 'string',
							'description' => __( 'Chart type, for example pie, line, bar, column, area, tabular. Required when creating.', 'visualizer' ),
						),
						'library'  => array(
							'type'        => 'string',
							'description' => __( 'Rendering library. Defaults to the first library the chart type supports.', 'visualizer' ),
							'enum'        => array( 'GoogleCharts', 'ChartJS', 'DataTable' ),
						),
						'columns'  => array(
							'type'        => 'array',
							'description' => __( 'Typed columns. Required when creating; when updating, send together with rows to replace the chart data.', 'visualizer' ),
							'items'       => $column_schema,
						),
						'rows'     => $rows_schema,
						'options'  => array(
							'type'                 => 'object',
							'description'          => __( 'Chart settings merged over the existing ones (for example title, legend, colors).', 'visualizer' ),
							'additionalProperties' => true,
						),
						'dry_run'  => array(
							'type'        => 'boolean',
							'description' => __( 'Validate and return the parsed data preview without saving.', 'visualizer' ),
							'default'     => false,
						),
					),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'id'             => array( 'type' => 'integer' ),
						'created'        => array( 'type' => 'boolean' ),
						'embed'          => $embed_schema,
						'dry_run'        => array( 'type' => 'boolean' ),
						'valid'          => array( 'type' => 'boolean' ),
						'parsed_preview' => $preview_schema,
						'errors'         => array(
							'type'  => 'array',
							'items' => array( 'type' => 'string' ),
						),
					),
				),
				'execute_callback'    => array( $this, 'executeUpsertChart' ),
				'permission_callback' => array( $this, 'canUpsertChart' ),
				'meta'                => array(
					'annotations'  => array(
						'readonly'    => false,
						'destructive' => false,
						'idempotent'  => true,
					),
					'show_in_rest' => true,
				),
			)
		);

		wp_register_ability(
			'visualizer/set-data-source',
			array(
				'label'               => __( 'Set chart data source', 'visualizer' ),
				'description'         => __( 'Points a chart at a remote CSV/XLSX file, a JSON endpoint or a WordPress database query, imports the data and optionally saves a refresh interval. Requires a plan that includes the chosen source. With dry_run=true nothing is saved and the fetched preview is returned.', 'visualizer' ),
				'category'            => self::CATEGORY,
				'input_schema'        => array(
					'type'       => 'object',
					'properties' => array(
						'chart_id'         => array(
							'type'        => 'integer',
							'description' => __( 'The chart ID.', 'visualizer' ),
							'minimum'     => 1,
						),
						'source'           => array(
							'type'        => 'string',
							'description' => __( 'Source kind.', 'visualizer' ),
							'enum'        => array( 'csv_url', 'json', 'db_query' ),
						),
						'url'              => array(
							'type'        => 'string',
							'description' => __( 'URL of the CSV/XLSX file (csv_url) or of the JSON endpoint (json). The CSV must have the labels in row 1 and the data types in row 2.', 'visualizer' ),
						),
						'json_root'        => array(
							'type'        => 'string',
							'description' => __( 'Root node that holds the rows, in the format the chart editor uses: "root" for the top level, "root>data>results" for nested nodes. Run with dry_run=true and no mapping to list the available roots.', 'visualizer' ),
						),
						'json_method'      => array(
							'type'    => 'string',
							'enum'    => array( 'GET', 'POST' ),
							'default' => 'GET',
						),
						'json_paging'      => array(
							'type'        => 'string',
							'description' => __( 'Node that holds the next page URL, same format as json_root.', 'visualizer' ),
						),
						'mapping'          => array(
							'type'        => 'array',
							'description' => __( 'JSON sources only: the JSON keys to use as columns, in order, with their data type. Run with dry_run=true first to see the available keys.', 'visualizer' ),
							'items'       => $column_schema,
						),
						'query'            => array(
							'type'        => 'string',
							'description' => __( 'db_query sources only: a single SELECT statement run against the WordPress database.', 'visualizer' ),
						),
						'refresh_interval' => array(
							'type'        => 'number',
							'description' => __( 'Hours between automatic refreshes. -1 imports once. Only the intervals offered by the chart editor for the active plan are accepted.', 'visualizer' ),
							'default'     => -1,
						),
						'dry_run'          => array(
							'type'    => 'boolean',
							'default' => false,
						),
					),
					'required'   => array( 'chart_id', 'source' ),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'id'               => array( 'type' => 'integer' ),
						'source'           => array( 'type' => 'string' ),
						'refresh_interval' => array( 'type' => 'number' ),
						'dry_run'          => array( 'type' => 'boolean' ),
						'available_roots'  => array(
							'type'  => 'array',
							'items' => array( 'type' => 'string' ),
						),
						'available_keys'   => array(
							'type'  => 'array',
							'items' => array( 'type' => 'string' ),
						),
						'parsed_preview'   => $preview_schema,
						'embed'            => $embed_schema,
					),
				),
				'execute_callback'    => array( $this, 'executeSetDataSource' ),
				'permission_callback' => array( $this, 'canSetDataSource' ),
				'meta'                => array(
					'annotations'  => array(
						'readonly'    => false,
						'destructive' => false,
						'idempotent'  => true,
					),
					'show_in_rest' => true,
				),
			)
		);

		wp_register_ability(
			'visualizer/refresh-chart-data',
			array(
				'label'               => __( 'Refresh chart data', 'visualizer' ),
				'description'         => __( 'Fetches the current data for a chart from its saved remote CSV/XLSX, JSON or database source and stores it.', 'visualizer' ),
				'category'            => self::CATEGORY,
				'input_schema'        => array(
					'type'       => 'object',
					'properties' => array(
						'chart_id' => array(
							'type'        => 'integer',
							'description' => __( 'The chart ID.', 'visualizer' ),
							'minimum'     => 1,
						),
					),
					'required'   => array( 'chart_id' ),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'id'        => array( 'type' => 'integer' ),
						'source'    => array( 'type' => 'string' ),
						'refreshed' => array( 'type' => 'boolean' ),
						'row_count' => array( 'type' => 'integer' ),
					),
				),
				'execute_callback'    => array( $this, 'executeRefreshChartData' ),
				'permission_callback' => array( $this, 'canEditChartInput' ),
				'meta'                => array(
					'annotations'  => array(
						'readonly'    => false,
						'destructive' => false,
						'idempotent'  => true,
					),
					'show_in_rest' => true,
				),
			)
		);
	}

	// -------------------------------------------------------------------------
	// Permissions
	// -------------------------------------------------------------------------

	/**
	 * Same capability as the chart library page and the charts list endpoint.
	 *
	 * @access public
	 * @return bool
	 */
	public function canManageCharts() {
		return current_user_can( 'edit_posts' );
	}

	/**
	 * Same checks as the chart editor: edit_posts plus Visualizer_Module::can_edit_chart().
	 *
	 * @access public
	 *
	 * @param mixed $input The ability input.
	 * @return bool
	 */
	public function canEditChartInput( $input = array() ) {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return false;
		}

		$chart_id = $this->getChartId( $input );

		// A missing or invalid ID is reported by the execute callback.
		return ! $chart_id || ! $this->getChart( $chart_id ) || self::can_edit_chart( $chart_id );
	}

	/**
	 * Creating needs edit_posts; updating additionally needs can_edit_chart().
	 *
	 * @access public
	 *
	 * @param mixed $input The ability input.
	 * @return bool
	 */
	public function canUpsertChart( $input = array() ) {
		return $this->canEditChartInput( $input );
	}

	/**
	 * Chart editor checks, plus the database import checks for SQL sources.
	 *
	 * @access public
	 *
	 * @param mixed $input The ability input.
	 * @return bool
	 */
	public function canSetDataSource( $input = array() ) {
		if ( ! $this->canEditChartInput( $input ) ) {
			return false;
		}

		if ( is_array( $input ) && isset( $input['source'] ) && 'db_query' === $input['source'] ) {
			// Same as Visualizer_Module_Chart::saveQuery().
			return current_user_can( 'administrator' ) && is_super_admin();
		}

		return true;
	}

	// -------------------------------------------------------------------------
	// Execute callbacks
	// -------------------------------------------------------------------------

	/**
	 * Lists charts.
	 *
	 * @access public
	 *
	 * @param mixed $input The ability input.
	 * @return array<string, mixed>|WP_Error
	 */
	public function executeListCharts( $input = array() ) {
		$input    = is_array( $input ) ? $input : array();
		$page     = isset( $input['page'] ) ? max( 1, absint( $input['page'] ) ) : 1;
		$per_page = isset( $input['per_page'] ) ? absint( $input['per_page'] ) : 20;
		$per_page = min( self::MAX_PER_PAGE, max( 1, $per_page ) );

		$query_args = array(
			'post_type'      => Visualizer_Plugin::CPT_VISUALIZER,
			'post_status'    => 'publish',
			'posts_per_page' => $per_page,
			'paged'          => $page,
		);

		// Same restriction as Visualizer_Module_Chart::getCharts().
		if ( ! current_user_can( 'edit_others_posts' ) ) {
			$query_args['author'] = get_current_user_id();
		}

		$meta_query = array();
		if ( ! empty( $input['type'] ) ) {
			$type = sanitize_text_field( $input['type'] );
			if ( ! in_array( $type, Visualizer_Plugin::getChartTypes(), true ) ) {
				return new WP_Error( 'visualizer_invalid_type', __( 'Unknown chart type.', 'visualizer' ) );
			}
			$meta_query[] = array(
				'key'     => Visualizer_Plugin::CF_CHART_TYPE,
				'value'   => $type,
				'compare' => '=',
			);
		}
		if ( ! empty( $input['search'] ) ) {
			$meta_query[] = array(
				'key'     => Visualizer_Plugin::CF_SETTINGS,
				'value'   => sanitize_text_field( $input['search'] ),
				'compare' => 'LIKE',
			);
		}
		if ( $meta_query ) {
			$query_args['meta_query'] = $meta_query; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		}

		$query  = new WP_Query( apply_filters( 'visualizer_query_args', $query_args ) );
		$charts = array();
		foreach ( $query->posts as $chart ) {
			if ( ! $chart instanceof WP_Post ) {
				continue;
			}
			$charts[] = array(
				'id'        => $chart->ID,
				'title'     => $this->getChartTitle( $chart ),
				'type'      => (string) get_post_meta( $chart->ID, Visualizer_Plugin::CF_CHART_TYPE, true ),
				'library'   => (string) get_post_meta( $chart->ID, Visualizer_Plugin::CF_CHART_LIBRARY, true ),
				'source'    => $this->getSourceKind( $chart->ID ),
				'modified'  => (string) $chart->post_modified_gmt,
				'shortcode' => $this->getShortcode( $chart->ID ),
			);
		}

		return array(
			'charts'      => $charts,
			'total'       => (int) $query->found_posts,
			'total_pages' => (int) $query->max_num_pages,
		);
	}

	/**
	 * Returns one chart.
	 *
	 * @access public
	 *
	 * @param mixed $input The ability input.
	 * @return array<string, mixed>|WP_Error
	 */
	public function executeGetChart( $input = array() ) {
		$chart = $this->getEditableChart( $input );
		if ( is_wp_error( $chart ) ) {
			return $chart;
		}

		$type     = (string) get_post_meta( $chart->ID, Visualizer_Plugin::CF_CHART_TYPE, true );
		$series   = apply_filters( Visualizer_Plugin::FILTER_GET_CHART_SERIES, get_post_meta( $chart->ID, Visualizer_Plugin::CF_SERIES, true ), $chart->ID, $type );
		$data     = self::get_chart_data( $chart, $type );
		$settings = get_post_meta( $chart->ID, Visualizer_Plugin::CF_SETTINGS, true );
		$settings = apply_filters( Visualizer_Plugin::FILTER_GET_CHART_SETTINGS, $settings, $chart->ID, $type );
		if ( ! is_array( $settings ) ) {
			$settings = array();
		}
		// The chart preview image is a large data URI, not an option.
		unset( $settings['chart-img'] );

		return array(
			'id'      => $chart->ID,
			'title'   => $this->getChartTitle( $chart ),
			'type'    => $type,
			'library' => (string) get_post_meta( $chart->ID, Visualizer_Plugin::CF_CHART_LIBRARY, true ),
			'columns' => $this->seriesToColumns( $series ),
			'rows'    => $this->normalizeRows( $data ),
			'options' => $settings,
			'source'  => $this->describeSource( $chart->ID ),
			'embed'   => $this->getEmbed( $chart->ID ),
		);
	}

	/**
	 * Creates or updates a chart from typed columns and rows.
	 *
	 * @access public
	 *
	 * @param mixed $input The ability input.
	 * @return array<string, mixed>|WP_Error
	 */
	public function executeUpsertChart( $input = array() ) {
		$input   = is_array( $input ) ? $input : array();
		$dry_run = ! empty( $input['dry_run'] );
		$chart   = null;

		if ( ! empty( $input['chart_id'] ) ) {
			$chart = $this->getEditableChart( $input );
			if ( is_wp_error( $chart ) ) {
				return $chart;
			}
		}

		$has_data = isset( $input['columns'] ) || isset( $input['rows'] );
		if ( ! $chart && ! $has_data ) {
			return new WP_Error( 'visualizer_missing_data', __( 'columns and rows are required to create a chart.', 'visualizer' ) );
		}

		// Chart type and library, gated like the chart type page.
		$type = $chart ? (string) get_post_meta( $chart->ID, Visualizer_Plugin::CF_CHART_TYPE, true ) : '';
		if ( isset( $input['type'] ) && '' !== $input['type'] ) {
			$type = sanitize_text_field( $input['type'] );
		}
		if ( '' === $type ) {
			return new WP_Error( 'visualizer_missing_type', __( 'type is required to create a chart.', 'visualizer' ) );
		}

		$type_changed = ! $chart || isset( $input['type'] ) || isset( $input['library'] );
		$library      = $chart ? (string) get_post_meta( $chart->ID, Visualizer_Plugin::CF_CHART_LIBRARY, true ) : '';
		if ( $type_changed ) {
			$library = $this->resolveLibrary( $type, isset( $input['library'] ) ? (string) $input['library'] : ( isset( $input['type'] ) ? '' : $library ) );
			if ( is_wp_error( $library ) ) {
				return $library;
			}
		}

		$source = null;
		if ( $has_data ) {
			$source = $this->buildManualSource( $input );
			if ( is_wp_error( $source ) ) {
				if ( $dry_run ) {
					return array(
						'dry_run' => true,
						'valid'   => false,
						'errors'  => $source->get_error_messages(),
					);
				}
				return $source;
			}
		}

		$options = array();
		if ( isset( $input['options'] ) ) {
			if ( ! is_array( $input['options'] ) ) {
				return new WP_Error( 'visualizer_invalid_options', __( 'options must be an object.', 'visualizer' ) );
			}
			// Same sanitization as the settings form (Visualizer_Module_Chart::sanitizeSettings()).
			$options = map_deep( $input['options'], 'sanitize_textarea_field' );
			unset( $options['chart-img'] );
		}

		if ( $dry_run ) {
			$result = array(
				'dry_run' => true,
				'valid'   => true,
				'errors'  => array(),
			);
			if ( $source ) {
				$result['parsed_preview'] = $this->getPreview( $source->getSeries(), $source->getRawData() );
			}
			return $result;
		}

		$created = false;
		$this->disableRevisionsTemporarily();

		if ( ! $chart ) {
			// Same defaults as the chart creation flow in Visualizer_Module_Chart::renderChartPages().
			$chart_id = wp_insert_post(
				array(
					'post_type'    => Visualizer_Plugin::CPT_VISUALIZER,
					'post_title'   => 'Visualization',
					'post_author'  => get_current_user_id(),
					'post_status'  => 'auto-draft',
					'post_content' => $source->getData(),
				),
				true
			);
			if ( is_wp_error( $chart_id ) ) {
				return $chart_id;
			}

			add_post_meta( $chart_id, Visualizer_Plugin::CF_CHART_TYPE, $type );
			add_post_meta( $chart_id, Visualizer_Plugin::CF_DEFAULT_DATA, 0 );
			add_post_meta( $chart_id, Visualizer_Plugin::CF_SOURCE, $source->getSourceName() );
			add_post_meta( $chart_id, Visualizer_Plugin::CF_SERIES, $source->getSeries() );
			add_post_meta( $chart_id, Visualizer_Plugin::CF_CHART_LIBRARY, $library );
			add_post_meta( $chart_id, Visualizer_Plugin::CF_SETTINGS, array( 'focusTarget' => 'datum' ) );

			do_action( 'visualizer_pro_new_chart_defaults', $chart_id );

			$chart = get_post( $chart_id );
			if ( ! $chart instanceof WP_Post ) {
				return new WP_Error( 'visualizer_create_failed', __( 'The chart could not be created.', 'visualizer' ) );
			}
			Visualizer_Module_Utility::set_defaults( $chart );
			$created = true;
		} else {
			if ( $type_changed ) {
				update_post_meta( $chart->ID, Visualizer_Plugin::CF_CHART_TYPE, $type );
				update_post_meta( $chart->ID, Visualizer_Plugin::CF_CHART_LIBRARY, $library );
			}
			if ( $source ) {
				$this->clearSourceMeta( $chart->ID, true );
				$this->persistSource( $chart->ID, $source );
			}
		}

		// Settings, stored the way the settings form stores them.
		$settings = get_post_meta( $chart->ID, Visualizer_Plugin::CF_SETTINGS, true );
		if ( ! is_array( $settings ) ) {
			$settings = array();
		}
		$settings = array_merge( $settings, $options );

		$post_update = array( 'ID' => $chart->ID );
		if ( isset( $input['title'] ) && '' !== trim( (string) $input['title'] ) ) {
			$title                     = sanitize_text_field( $input['title'] );
			$settings['backend-title'] = $title;
			$post_update['post_title'] = $title;
		}

		$internal_title = '';
		if ( ! empty( $settings['title'] ) ) {
			$internal_title = is_array( $settings['title'] ) ? ( isset( $settings['title']['text'] ) ? $settings['title']['text'] : '' ) : $settings['title'];
		}
		$settings['internal_title'] = '' !== (string) $internal_title ? $internal_title : $chart->ID;
		if ( empty( $settings['pieResidueSliceLabel'] ) ) {
			$settings['pieResidueSliceLabel'] = esc_html__( 'Other', 'visualizer' );
		}
		update_post_meta( $chart->ID, Visualizer_Plugin::CF_SETTINGS, $settings );

		if ( $created ) {
			$post_update['post_status'] = 'publish';
		}
		if ( count( $post_update ) > 1 ) {
			wp_update_post( $post_update );
		}

		$this->clearChartCache( $chart->ID );

		return array(
			'id'      => $chart->ID,
			'created' => $created,
			'embed'   => $this->getEmbed( $chart->ID ),
		);
	}

	/**
	 * Configures a remote CSV/XLSX, JSON or database source for a chart.
	 *
	 * @access public
	 *
	 * @param mixed $input The ability input.
	 * @return array<string, mixed>|WP_Error
	 */
	public function executeSetDataSource( $input = array() ) {
		$input = is_array( $input ) ? $input : array();
		$chart = $this->getEditableChart( $input );
		if ( is_wp_error( $chart ) ) {
			return $chart;
		}

		$kind    = isset( $input['source'] ) ? sanitize_key( $input['source'] ) : '';
		$dry_run = ! empty( $input['dry_run'] );

		$features = array(
			'csv_url'  => 'import-url',
			'json'     => 'import-url',
			'db_query' => 'db-query',
		);
		if ( ! isset( $features[ $kind ] ) ) {
			return new WP_Error( 'visualizer_invalid_source', __( 'source must be one of csv_url, json or db_query.', 'visualizer' ) );
		}
		if ( ! $this->isFeatureAvailable( $features[ $kind ] ) ) {
			return new WP_Error( 'visualizer_feature_unavailable', __( 'This data source is not available on the current Visualizer plan.', 'visualizer' ) );
		}

		$schedule_types = array(
			'csv_url'  => 'csv',
			'json'     => 'json',
			'db_query' => 'db',
		);
		$hours          = $this->resolveInterval( $input, $schedule_types[ $kind ], $chart->ID );
		if ( is_wp_error( $hours ) ) {
			return $hours;
		}

		switch ( $kind ) {
			case 'csv_url':
				return $this->setCsvUrlSource( $chart, $input, $hours, $dry_run );
			case 'json':
				return $this->setJsonSource( $chart, $input, $hours, $dry_run );
			default:
				return $this->setQuerySource( $chart, $input, $hours, $dry_run );
		}
	}

	/**
	 * Re-fetches the data of a chart from its saved source.
	 *
	 * @access public
	 *
	 * @param mixed $input The ability input.
	 * @return array<string, mixed>|WP_Error
	 */
	public function executeRefreshChartData( $input = array() ) {
		$chart = $this->getEditableChart( $input );
		if ( is_wp_error( $chart ) ) {
			return $chart;
		}

		$source_class = (string) get_post_meta( $chart->ID, Visualizer_Plugin::CF_SOURCE, true );

		switch ( $source_class ) {
			case 'Visualizer_Source_Query':
			case 'Visualizer_Source_Json':
				// Handled by Visualizer_Module_Setup::refresh_db_for_chart().
				delete_post_meta( $chart->ID, Visualizer_Plugin::CF_ERROR );
				apply_filters( 'visualizer_schedule_refresh_chart', $chart, $chart->ID, true );
				$error = get_post_meta( $chart->ID, Visualizer_Plugin::CF_ERROR, true );
				if ( ! empty( $error ) ) {
					return new WP_Error( 'visualizer_refresh_failed', sanitize_text_field( $error ) );
				}
				break;
			case 'Visualizer_Source_Csv_Remote':
			case 'Visualizer_Source_Xlsx_Remote':
				$url = $this->getRemoteFileUrl( $chart );
				if ( '' === $url ) {
					return new WP_Error( 'visualizer_no_source', __( 'The chart has no saved source URL.', 'visualizer' ) );
				}
				$source = $this->fetchSource( new $source_class( $url ) );
				if ( is_wp_error( $source ) ) {
					return $source;
				}
				$this->disableRevisionsTemporarily();
				$this->persistSource( $chart->ID, $source );
				break;
			default:
				return new WP_Error( 'visualizer_not_refreshable', __( 'The chart data was entered manually or uploaded as a file, so there is no source to refresh from.', 'visualizer' ) );
		}

		$this->clearChartCache( $chart->ID );

		$chart = get_post( $chart->ID );
		$rows  = $chart instanceof WP_Post ? self::get_chart_data( $chart, '' ) : array();

		return array(
			'id'        => (int) $input['chart_id'],
			'source'    => $this->getSourceKind( (int) $input['chart_id'] ),
			'refreshed' => true,
			'row_count' => is_array( $rows ) ? count( $rows ) : 0,
		);
	}

	// -------------------------------------------------------------------------
	// Source helpers
	// -------------------------------------------------------------------------

	/**
	 * Remote CSV/XLSX source, same flow as the "Import from URL" form in Visualizer_Module_Chart::uploadData().
	 *
	 * @param WP_Post              $chart   The chart.
	 * @param array<string, mixed> $input   The ability input.
	 * @param float                $hours   The refresh interval.
	 * @param bool                 $dry_run Whether to skip saving.
	 * @return array<string, mixed>|WP_Error
	 */
	private function setCsvUrlSource( $chart, $input, $hours, $dry_run ) {
		$url = $this->validateUrl( $input );
		if ( is_wp_error( $url ) ) {
			return $url;
		}

		$extension = strtolower( (string) pathinfo( (string) wp_parse_url( $url, PHP_URL_PATH ), PATHINFO_EXTENSION ) );
		$source    = 'xlsx' === $extension ? new Visualizer_Source_Xlsx_Remote( $url ) : new Visualizer_Source_Csv_Remote( $url );
		$source    = $this->fetchSource( $source );
		if ( is_wp_error( $source ) ) {
			return $source;
		}

		if ( $dry_run ) {
			return array(
				'id'             => $chart->ID,
				'source'         => 'csv_url',
				'dry_run'        => true,
				'parsed_preview' => $this->getPreview( $source->getSeries(), $source->getRawData() ),
			);
		}

		$this->disableRevisionsTemporarily();
		$this->clearSourceMeta( $chart->ID, $hours < 0 );
		$this->persistSource( $chart->ID, $source );
		if ( $hours >= 0 ) {
			apply_filters( 'visualizer_pro_chart_schedule', $chart->ID, $url, $hours );
		}
		$this->clearChartCache( $chart->ID );

		return array(
			'id'               => $chart->ID,
			'source'           => 'csv_url',
			'refresh_interval' => $hours,
			'dry_run'          => false,
			'embed'            => $this->getEmbed( $chart->ID ),
		);
	}

	/**
	 * JSON source, same flow as Visualizer_Module_Chart::setJsonData().
	 *
	 * @param WP_Post              $chart   The chart.
	 * @param array<string, mixed> $input   The ability input.
	 * @param float                $hours   The refresh interval.
	 * @param bool                 $dry_run Whether to skip saving.
	 * @return array<string, mixed>|WP_Error
	 */
	private function setJsonSource( $chart, $input, $hours, $dry_run ) {
		$url = $this->validateUrl( $input );
		if ( is_wp_error( $url ) ) {
			return $url;
		}

		$params = array(
			'url'    => $url,
			'root'   => ( isset( $input['json_root'] ) && is_string( $input['json_root'] ) && '' !== trim( $input['json_root'] ) ) ? sanitize_text_field( $input['json_root'] ) : 'root',
			'method' => ( isset( $input['json_method'] ) && 'POST' === strtoupper( (string) $input['json_method'] ) ) ? 'POST' : 'GET',
		);
		if ( ! empty( $input['json_paging'] ) && is_string( $input['json_paging'] ) ) {
			$params['paging'] = sanitize_text_field( $input['json_paging'] );
		}

		// Credentials saved from the chart editor are reused for the same host only; they are never accepted or returned here.
		$saved_headers = get_post_meta( $chart->ID, Visualizer_Plugin::CF_JSON_HEADERS, true );
		$saved_url     = (string) get_post_meta( $chart->ID, Visualizer_Plugin::CF_JSON_URL, true );
		$auth          = array();
		if ( is_array( $saved_headers ) && ! empty( $saved_headers['auth'] ) && '' !== $saved_url && wp_parse_url( $saved_url, PHP_URL_HOST ) === wp_parse_url( $url, PHP_URL_HOST ) && wp_parse_url( $saved_url, PHP_URL_SCHEME ) === wp_parse_url( $url, PHP_URL_SCHEME ) ) {
			$auth = $saved_headers['auth'];
			if ( is_array( $auth ) && isset( $auth['username'], $auth['password'] ) ) {
				$params['username'] = $auth['username'];
				$params['password'] = $auth['password'];
			} elseif ( is_string( $auth ) ) {
				$params['auth'] = $auth;
			}
		}

		// First pass: discover the keys available under the chosen root.
		$probe = $this->fetchSource( new Visualizer_Source_Json( $params ) );
		if ( is_wp_error( $probe ) ) {
			return $probe;
		}
		$raw     = $probe->getRawData();
		$first   = $raw ? reset( $raw ) : null;
		$keys    = is_array( $first ) ? array_map( 'strval', array_keys( $first ) ) : array();
		$mapping = isset( $input['mapping'] ) ? $input['mapping'] : array();
		if ( ! $keys && ( $mapping || ! $dry_run ) ) {
			return new WP_Error( 'visualizer_fetch_failed', __( 'Unable to fetch data from the endpoint. Check the URL and the root.', 'visualizer' ) );
		}

		if ( ! $mapping ) {
			if ( $dry_run ) {
				$roots = ( new Visualizer_Source_Json( $params ) )->fetchRoots();
				return array(
					'id'              => $chart->ID,
					'source'          => 'json',
					'dry_run'         => true,
					'available_roots' => is_array( $roots ) ? array_values( array_map( 'strval', $roots ) ) : array(),
					'available_keys'  => $keys,
				);
			}
			return new WP_Error( 'visualizer_missing_mapping', __( 'mapping is required for JSON sources. Run with dry_run=true to list the available keys.', 'visualizer' ) );
		}

		$columns = $this->validateColumns( $mapping );
		if ( is_wp_error( $columns ) ) {
			return $columns;
		}
		$params['header'] = array();
		$params['type']   = array();
		foreach ( $columns as $column ) {
			if ( ! in_array( $column['name'], $keys, true ) ) {
				/* translators: %s: JSON key. */
				return new WP_Error( 'visualizer_invalid_mapping', sprintf( __( 'The key "%s" does not exist in the JSON data.', 'visualizer' ), $column['name'] ) );
			}
			$params['header'][]                = $column['name'];
			$params['type'][ $column['name'] ] = $column['type'];
		}

		$source = new Visualizer_Source_Json( $params );
		try {
			$source->fetchFromEditableTable();
		} catch ( Exception $e ) {
			return new WP_Error( 'visualizer_fetch_failed', __( 'The data could not be parsed with the given column types.', 'visualizer' ) );
		}
		if ( ! $source->getSeries() ) {
			return new WP_Error( 'visualizer_invalid_mapping', __( 'No columns could be mapped.', 'visualizer' ) );
		}

		if ( $dry_run ) {
			return array(
				'id'             => $chart->ID,
				'source'         => 'json',
				'dry_run'        => true,
				'available_keys' => $keys,
				'parsed_preview' => $this->getPreview( $source->getSeries(), $source->getRawData() ),
			);
		}

		$this->disableRevisionsTemporarily();
		$this->clearSourceMeta( $chart->ID, true );
		update_post_meta( $chart->ID, Visualizer_Plugin::CF_EDITABLE_TABLE, true );
		$this->persistSource( $chart->ID, $source );
		update_post_meta( $chart->ID, Visualizer_Plugin::CF_JSON_URL, $params['url'] );
		update_post_meta( $chart->ID, Visualizer_Plugin::CF_JSON_ROOT, $params['root'] );

		$headers = array( 'method' => $params['method'] );
		if ( $auth ) {
			$headers['auth'] = $auth;
		}
		add_post_meta( $chart->ID, Visualizer_Plugin::CF_JSON_HEADERS, $headers );
		if ( ! empty( $params['paging'] ) ) {
			add_post_meta( $chart->ID, Visualizer_Plugin::CF_JSON_PAGING, $params['paging'] );
		}

		// Same as Visualizer_Module_Chart::setJsonSchedule().
		$schedules = get_option( Visualizer_Plugin::CF_JSON_SCHEDULE, array() );
		$schedules = is_array( $schedules ) ? $schedules : array();
		if ( $hours >= 0 ) {
			add_post_meta( $chart->ID, Visualizer_Plugin::CF_JSON_SCHEDULE, $hours );
			$schedules[ $chart->ID ] = time() + $hours * HOUR_IN_SECONDS;
		} else {
			unset( $schedules[ $chart->ID ] );
		}
		update_option( Visualizer_Plugin::CF_JSON_SCHEDULE, $schedules );

		$this->clearChartCache( $chart->ID );

		return array(
			'id'               => $chart->ID,
			'source'           => 'json',
			'refresh_interval' => $hours,
			'dry_run'          => false,
			'embed'            => $this->getEmbed( $chart->ID ),
		);
	}

	/**
	 * WordPress database query source, same flow as Visualizer_Module_Chart::saveQuery().
	 *
	 * @param WP_Post              $chart   The chart.
	 * @param array<string, mixed> $input   The ability input.
	 * @param float                $hours   The refresh interval.
	 * @param bool                 $dry_run Whether to skip saving.
	 * @return array<string, mixed>|WP_Error
	 */
	private function setQuerySource( $chart, $input, $hours, $dry_run ) {
		// Repeated here because the permission callback only sees the raw input.
		if ( ! current_user_can( 'administrator' ) || ! is_super_admin() ) {
			return new WP_Error( 'visualizer_forbidden', __( 'Action not allowed for this user.', 'visualizer' ) );
		}

		$query = isset( $input['query'] ) && is_string( $input['query'] ) ? trim( trim( $input['query'] ), ';' ) : '';
		if ( '' === $query ) {
			return new WP_Error( 'visualizer_missing_query', __( 'query is required for db_query sources.', 'visualizer' ) );
		}

		// No connection parameters: the query always runs against the WordPress database.
		$source = new Visualizer_Source_Query( $query, $chart->ID, array( 'db_type' => Visualizer_Plugin::WP_DB_NAME ) );
		$source->fetch( false );
		$error = $source->get_error();
		if ( ! empty( $error ) ) {
			return new WP_Error( 'visualizer_query_failed', sanitize_text_field( $error ) );
		}
		if ( ! $source->getSeries() ) {
			return new WP_Error( 'visualizer_query_failed', __( 'The query returned no rows.', 'visualizer' ) );
		}

		if ( $dry_run ) {
			return array(
				'id'             => $chart->ID,
				'source'         => 'db_query',
				'dry_run'        => true,
				'parsed_preview' => $this->getPreview( $source->getSeries(), $source->getRawData() ),
			);
		}

		$this->disableRevisionsTemporarily();
		$this->clearSourceMeta( $chart->ID, true );
		update_post_meta( $chart->ID, Visualizer_Plugin::CF_DB_QUERY, $query );
		update_post_meta( $chart->ID, Visualizer_Plugin::CF_DB_SCHEDULE, $hours );
		$this->persistSource( $chart->ID, $source );

		$schedules                = get_option( Visualizer_Plugin::CF_DB_SCHEDULE, array() );
		$schedules                = is_array( $schedules ) ? $schedules : array();
		$schedules[ $chart->ID ] = time() + $hours * HOUR_IN_SECONDS;
		update_option( Visualizer_Plugin::CF_DB_SCHEDULE, $schedules );

		$this->clearChartCache( $chart->ID );

		return array(
			'id'               => $chart->ID,
			'source'           => 'db_query',
			'refresh_interval' => $hours,
			'dry_run'          => false,
			'embed'            => $this->getEmbed( $chart->ID ),
		);
	}

	/**
	 * Builds a CSV source from typed columns and rows, the format the manual data editor submits.
	 *
	 * @param array<string, mixed> $input The ability input.
	 * @return Visualizer_Source|WP_Error
	 */
	private function buildManualSource( $input ) {
		$columns = $this->validateColumns( isset( $input['columns'] ) ? $input['columns'] : null );
		if ( is_wp_error( $columns ) ) {
			return $columns;
		}

		$rows = isset( $input['rows'] ) ? $input['rows'] : null;
		if ( ! is_array( $rows ) || ! $rows ) {
			return new WP_Error( 'visualizer_invalid_rows', __( 'rows must be a non-empty array of arrays.', 'visualizer' ) );
		}
		if ( count( $rows ) > self::MAX_ROWS ) {
			/* translators: %d: maximum number of rows. */
			return new WP_Error( 'visualizer_too_many_rows', sprintf( __( 'A maximum of %d rows is accepted.', 'visualizer' ), self::MAX_ROWS ) );
		}

		$errors = new WP_Error();
		$lines  = array( wp_list_pluck( $columns, 'name' ), wp_list_pluck( $columns, 'type' ) );
		foreach ( array_values( $rows ) as $index => $row ) {
			if ( ! is_array( $row ) || count( $row ) > count( $columns ) ) {
				/* translators: %d: row number. */
				$errors->add( 'visualizer_invalid_rows', sprintf( __( 'Row %d is not an array or has more cells than columns.', 'visualizer' ), $index + 1 ) );
				continue;
			}
			$line = array();
			foreach ( array_values( $row ) as $position => $cell ) {
				if ( is_bool( $cell ) ) {
					$cell = $cell ? 'true' : 'false';
				} elseif ( null === $cell ) {
					$cell = '';
				} elseif ( ! is_scalar( $cell ) ) {
					/* translators: %d: row number. */
					$errors->add( 'visualizer_invalid_rows', sprintf( __( 'Row %d contains a value that is not a string, number or boolean.', 'visualizer' ), $index + 1 ) );
					continue 2;
				}
				$cell = sanitize_text_field( (string) $cell );
				if ( 'number' === $columns[ $position ]['type'] && '' !== $cell && ! is_numeric( str_replace( ',', '', $cell ) ) ) {
					/* translators: 1: row number, 2: column label. */
					$errors->add( 'visualizer_invalid_rows', sprintf( __( 'Row %1$d: "%2$s" expects a number.', 'visualizer' ), $index + 1, $columns[ $position ]['name'] ) );
					continue 2;
				}
				$line[] = $cell;
			}
			$lines[] = array_pad( $line, count( $columns ), '' );

			if ( count( $errors->get_error_messages() ) >= 20 ) {
				break;
			}
		}
		if ( $errors->has_errors() ) {
			return $errors;
		}

		$tmpfile = wp_tempnam( Visualizer_Plugin::NAME );
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen
		$handle = $tmpfile ? fopen( $tmpfile, 'w' ) : false;
		if ( ! $handle ) {
			return new WP_Error( 'visualizer_tmp_file', __( 'A temporary file could not be created.', 'visualizer' ) );
		}
		foreach ( $lines as $line ) {
			fputcsv( $handle, $line, VISUALIZER_CSV_DELIMITER, VISUALIZER_CSV_ENCLOSURE );
		}
		fclose( $handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose

		$source = $this->fetchSource( new Visualizer_Source_Csv( $tmpfile ) );
		wp_delete_file( $tmpfile );

		if ( ! is_wp_error( $source ) && count( $source->getSeries() ) !== count( $columns ) ) {
			return new WP_Error( 'visualizer_invalid_columns', __( 'The columns could not be parsed. Check that every column has a label and a type.', 'visualizer' ) );
		}

		return $source;
	}

	/**
	 * Runs fetch() on a source and converts failures to WP_Error.
	 *
	 * @param Visualizer_Source $source The source.
	 * @return Visualizer_Source|WP_Error
	 */
	private function fetchSource( $source ) {
		try {
			$fetched = $source->fetch();
		} catch ( Exception $e ) {
			$fetched = false;
		}

		$error = $source->get_error();
		if ( ! $fetched || ! empty( $error ) ) {
			return new WP_Error( 'visualizer_fetch_failed', ! empty( $error ) ? sanitize_text_field( $error ) : __( 'Could not parse data. Check the format and try again.', 'visualizer' ) );
		}

		return $source;
	}

	/**
	 * Stores the series and data of a fetched source, as the editors do after an import.
	 *
	 * @param int               $chart_id The chart ID.
	 * @param Visualizer_Source $source   The fetched source.
	 * @return void
	 */
	private function persistSource( $chart_id, $source ) {
		wp_update_post(
			array(
				'ID'           => $chart_id,
				'post_content' => $source->getData(),
			)
		);
		update_post_meta( $chart_id, Visualizer_Plugin::CF_SERIES, $source->getSeries() );
		update_post_meta( $chart_id, Visualizer_Plugin::CF_SOURCE, $source->getSourceName() );
		update_post_meta( $chart_id, Visualizer_Plugin::CF_DEFAULT_DATA, 0 );
		delete_post_meta( $chart_id, Visualizer_Plugin::CF_ERROR );
	}

	/**
	 * Removes the parameters of the previous source, as Visualizer_Module_Chart::uploadData() does before an import.
	 *
	 * @param int  $chart_id        The chart ID.
	 * @param bool $remove_schedule Whether to remove the remote file schedule too.
	 * @return void
	 */
	private function clearSourceMeta( $chart_id, $remove_schedule ) {
		if ( $remove_schedule ) {
			apply_filters( 'visualizer_pro_remove_schedule', $chart_id );
			delete_post_meta( $chart_id, Visualizer_Plugin::CF_CHART_URL );
			delete_post_meta( $chart_id, Visualizer_Plugin::CF_CHART_SCHEDULE );
		}

		delete_post_meta( $chart_id, Visualizer_Plugin::CF_FILTER_CONFIG );
		delete_post_meta( $chart_id, '__transient-' . Visualizer_Plugin::CF_FILTER_CONFIG );
		delete_post_meta( $chart_id, '__transient-' . Visualizer_Plugin::CF_DB_QUERY );
		delete_post_meta( $chart_id, Visualizer_Plugin::CF_DB_QUERY );
		delete_post_meta( $chart_id, Visualizer_Plugin::CF_DB_SCHEDULE );
		delete_post_meta( $chart_id, Visualizer_Plugin::CF_REMOTE_DB_PARAMS );

		delete_post_meta( $chart_id, Visualizer_Plugin::CF_JSON_URL );
		delete_post_meta( $chart_id, Visualizer_Plugin::CF_JSON_ROOT );
		delete_post_meta( $chart_id, Visualizer_Plugin::CF_JSON_PAGING );
		delete_post_meta( $chart_id, Visualizer_Plugin::CF_JSON_HEADERS );
		delete_post_meta( $chart_id, Visualizer_Plugin::CF_JSON_SCHEDULE );

		delete_post_meta( $chart_id, Visualizer_Plugin::CF_ERROR );
		delete_post_meta( $chart_id, Visualizer_Plugin::CF_EDITOR );
		delete_post_meta( $chart_id, Visualizer_Plugin::CF_EDITABLE_TABLE );
	}

	/**
	 * Clears the cached chart output.
	 *
	 * @param int $chart_id The chart ID.
	 * @return void
	 */
	private function clearChartCache( $chart_id ) {
		delete_transient( Visualizer_Plugin::CF_CHART_CACHE . '_' . $chart_id );
	}

	// -------------------------------------------------------------------------
	// Validation helpers
	// -------------------------------------------------------------------------

	/**
	 * Extracts the chart ID from the input.
	 *
	 * @param mixed $input The ability input.
	 * @return int
	 */
	private function getChartId( $input ) {
		return ( is_array( $input ) && isset( $input['chart_id'] ) ) ? absint( $input['chart_id'] ) : 0;
	}

	/**
	 * Returns the chart post, or null when the ID is not a chart.
	 *
	 * @param int $chart_id The chart ID.
	 * @return WP_Post|null
	 */
	private function getChart( $chart_id ) {
		$chart = $chart_id ? get_post( $chart_id ) : null;

		return ( $chart instanceof WP_Post && Visualizer_Plugin::CPT_VISUALIZER === $chart->post_type ) ? $chart : null;
	}

	/**
	 * Returns the chart from the input when the current user can edit it.
	 *
	 * @param mixed $input The ability input.
	 * @return WP_Post|WP_Error
	 */
	private function getEditableChart( $input ) {
		$chart = $this->getChart( $this->getChartId( $input ) );
		if ( ! $chart ) {
			return new WP_Error( 'visualizer_chart_not_found', __( 'Chart not found.', 'visualizer' ) );
		}
		if ( ! self::can_edit_chart( $chart->ID ) ) {
			return new WP_Error( 'visualizer_forbidden', __( 'You do not have permission to perform this action.', 'visualizer' ) );
		}

		return $chart;
	}

	/**
	 * Validates a list of {name, type} columns.
	 *
	 * @param mixed $columns The columns input.
	 * @return array<int, array{name: string, type: string}>|WP_Error
	 */
	private function validateColumns( $columns ) {
		if ( ! is_array( $columns ) || ! $columns ) {
			return new WP_Error( 'visualizer_invalid_columns', __( 'columns must be a non-empty array of {name, type} objects.', 'visualizer' ) );
		}
		if ( count( $columns ) > self::MAX_COLUMNS ) {
			/* translators: %d: maximum number of columns. */
			return new WP_Error( 'visualizer_too_many_columns', sprintf( __( 'A maximum of %d columns is accepted.', 'visualizer' ), self::MAX_COLUMNS ) );
		}

		$valid = array();
		$names = array();
		foreach ( $columns as $column ) {
			$name = ( is_array( $column ) && isset( $column['name'] ) && is_scalar( $column['name'] ) ) ? sanitize_text_field( wp_strip_all_tags( (string) $column['name'] ) ) : '';
			$type = ( is_array( $column ) && isset( $column['type'] ) && is_string( $column['type'] ) ) ? $column['type'] : '';
			if ( '' === $name || '0' === $name ) {
				return new WP_Error( 'visualizer_invalid_columns', __( 'Every column needs a non-empty name.', 'visualizer' ) );
			}
			if ( in_array( $name, $names, true ) ) {
				/* translators: %s: column label. */
				return new WP_Error( 'visualizer_invalid_columns', sprintf( __( 'Duplicate column name "%s".', 'visualizer' ), $name ) );
			}
			if ( ! in_array( $type, Visualizer_Source::getAllowedTypes(), true ) ) {
				/* translators: 1: column label, 2: list of types. */
				return new WP_Error( 'visualizer_invalid_columns', sprintf( __( 'Column "%1$s" has an invalid type. Allowed types: %2$s.', 'visualizer' ), $name, implode( ', ', Visualizer_Source::getAllowedTypes() ) ) );
			}
			$names[] = $name;
			$valid[] = array(
				'name' => $name,
				'type' => $type,
			);
		}

		return $valid;
	}

	/**
	 * Validates the chart type against the active plan and resolves the library.
	 *
	 * @param string $type    The chart type.
	 * @param string $library The requested library, or empty for the default.
	 * @return string|WP_Error
	 */
	private function resolveLibrary( $type, $library ) {
		$types = Visualizer_Module_Admin::_getChartTypesLocalized();
		if ( ! isset( $types[ $type ] ) || ! is_array( $types[ $type ] ) ) {
			/* translators: %s: list of chart types. */
			return new WP_Error( 'visualizer_invalid_type', sprintf( __( 'Unknown chart type. Available types: %s.', 'visualizer' ), implode( ', ', array_keys( $types ) ) ) );
		}
		if ( ! Visualizer_Module_Admin::checkChartStatus( $type ) ) {
			return new WP_Error( 'visualizer_type_unavailable', __( 'This chart type is not available on the current Visualizer plan.', 'visualizer' ) );
		}

		$supported = isset( $types[ $type ]['supports'] ) ? (array) $types[ $type ]['supports'] : array( 'Google Charts' );
		$supported = array_values( array_map( array( $this, 'removeSpaces' ), $supported ) );
		if ( '' === $library ) {
			return $supported[0];
		}
		if ( ! in_array( $library, $supported, true ) ) {
			/* translators: %s: list of libraries. */
			return new WP_Error( 'visualizer_invalid_library', sprintf( __( 'This chart type supports these libraries on the current plan: %s.', 'visualizer' ), implode( ', ', $supported ) ) );
		}

		return $library;
	}

	/**
	 * Removes the spaces of a library label ("Google Charts" is stored as "GoogleCharts").
	 *
	 * @access public
	 *
	 * @param string $label The library label.
	 * @return string
	 */
	public function removeSpaces( $label ) {
		return str_replace( ' ', '', (string) $label );
	}

	/**
	 * Same plan check as the upsell overlays in the chart editor (Visualizer_Module_Sources::addProUpsell()).
	 *
	 * @param string $feature The feature key.
	 * @return bool
	 */
	private function isFeatureAvailable( $feature ) {
		if ( in_array( $feature, (array) self::get_features_for_license( 2 ), true ) ) {
			return (bool) apply_filters( 'visualizer_is_business', false );
		}
		if ( in_array( $feature, (array) self::get_features_for_license( 1 ), true ) ) {
			return (bool) self::is_pro();
		}

		return true;
	}

	/**
	 * Validates refresh_interval against the intervals the chart editor offers for the active plan.
	 *
	 * @param array<string, mixed> $input    The ability input.
	 * @param string               $type     The schedule type (csv, json, db).
	 * @param int                  $chart_id The chart ID.
	 * @return float|WP_Error
	 */
	private function resolveInterval( $input, $type, $chart_id ) {
		if ( ! isset( $input['refresh_interval'] ) || ! is_numeric( $input['refresh_interval'] ) || (float) $input['refresh_interval'] < 0 ) {
			return -1.0;
		}

		$hours   = (float) $input['refresh_interval'];
		$allowed = apply_filters( 'visualizer_chart_schedules', array( '-1' => __( 'One-time', 'visualizer' ) ), $type, $chart_id );
		$allowed = is_array( $allowed ) ? array_keys( $allowed ) : array();
		foreach ( $allowed as $option ) {
			if ( is_numeric( $option ) && abs( (float) $option - $hours ) < 0.00001 ) {
				return $hours;
			}
		}

		return new WP_Error(
			'visualizer_interval_unavailable',
			/* translators: %s: list of intervals in hours. */
			sprintf( __( 'This refresh interval is not available on the current Visualizer plan. Allowed values (hours): %s.', 'visualizer' ), implode( ', ', $allowed ) )
		);
	}

	/**
	 * Validates the url input.
	 *
	 * @param array<string, mixed> $input The ability input.
	 * @return string|WP_Error
	 */
	private function validateUrl( $input ) {
		$url = ( isset( $input['url'] ) && is_string( $input['url'] ) ) ? wp_http_validate_url( esc_url_raw( trim( $input['url'] ) ) ) : false;
		if ( ! $url ) {
			return new WP_Error( 'visualizer_invalid_url', __( 'The URL you entered is invalid. Please enter a valid URL.', 'visualizer' ) );
		}

		return (string) $url;
	}

	// -------------------------------------------------------------------------
	// Output helpers
	// -------------------------------------------------------------------------

	/**
	 * Returns the chart name as shown in the chart library.
	 *
	 * @param WP_Post $chart The chart.
	 * @return string
	 */
	private function getChartTitle( $chart ) {
		$settings = get_post_meta( $chart->ID, Visualizer_Plugin::CF_SETTINGS, true );
		if ( is_array( $settings ) && ! empty( $settings['backend-title'] ) && is_string( $settings['backend-title'] ) ) {
			return $settings['backend-title'];
		}

		return '#' . $chart->ID;
	}

	/**
	 * Returns the shortcode of a chart.
	 *
	 * @param int $chart_id The chart ID.
	 * @return string
	 */
	private function getShortcode( $chart_id ) {
		return sprintf( '[visualizer id="%d"]', $chart_id );
	}

	/**
	 * Returns the embed descriptor of a chart.
	 *
	 * @param int $chart_id The chart ID.
	 * @return array{shortcode: string, block: string}
	 */
	private function getEmbed( $chart_id ) {
		return array(
			'shortcode' => $this->getShortcode( $chart_id ),
			'block'     => sprintf( '<!-- wp:visualizer/chart {"id":%d} /-->', $chart_id ),
		);
	}

	/**
	 * Maps a source class to the source kind used by the abilities.
	 *
	 * @param int $chart_id The chart ID.
	 * @return string
	 */
	private function getSourceKind( $chart_id ) {
		$kinds = array(
			'Visualizer_Source_Csv'         => 'manual',
			'Visualizer_Source_Xlsx'        => 'manual',
			'Visualizer_Source_Csv_Remote'  => 'csv_url',
			'Visualizer_Source_Xlsx_Remote' => 'csv_url',
			'Visualizer_Source_Json'        => 'json',
			'Visualizer_Source_Query'       => 'db_query',
		);
		$class = (string) get_post_meta( $chart_id, Visualizer_Plugin::CF_SOURCE, true );

		return isset( $kinds[ $class ] ) ? $kinds[ $class ] : $class;
	}

	/**
	 * Describes the saved source of a chart without any credentials.
	 *
	 * @param int $chart_id The chart ID.
	 * @return array<string, mixed>
	 */
	private function describeSource( $chart_id ) {
		$kind   = $this->getSourceKind( $chart_id );
		$source = array( 'kind' => $kind );

		switch ( $kind ) {
			case 'csv_url':
				$chart                      = get_post( $chart_id );
				$source['url']              = $chart instanceof WP_Post ? $this->getRemoteFileUrl( $chart ) : '';
				$source['refresh_interval'] = $this->getInterval( $chart_id, Visualizer_Plugin::CF_CHART_SCHEDULE );
				break;
			case 'json':
				$headers                    = get_post_meta( $chart_id, Visualizer_Plugin::CF_JSON_HEADERS, true );
				$source['url']              = (string) get_post_meta( $chart_id, Visualizer_Plugin::CF_JSON_URL, true );
				$source['json_root']        = (string) get_post_meta( $chart_id, Visualizer_Plugin::CF_JSON_ROOT, true );
				$source['json_method']      = ( is_array( $headers ) && ! empty( $headers['method'] ) ) ? (string) $headers['method'] : 'GET';
				$source['has_saved_auth']   = is_array( $headers ) && ! empty( $headers['auth'] );
				$source['refresh_interval'] = $this->getInterval( $chart_id, Visualizer_Plugin::CF_JSON_SCHEDULE );
				break;
			case 'db_query':
				$source['is_remote_db']     = ! empty( get_post_meta( $chart_id, Visualizer_Plugin::CF_REMOTE_DB_PARAMS, true ) );
				$source['refresh_interval'] = $this->getInterval( $chart_id, Visualizer_Plugin::CF_DB_SCHEDULE );
				// The SQL is only shown to users who may edit it in the chart editor.
				if ( current_user_can( 'administrator' ) && is_super_admin() ) {
					$source['query'] = (string) get_post_meta( $chart_id, Visualizer_Plugin::CF_DB_QUERY, true );
				}
				break;
		}

		return $source;
	}

	/**
	 * Returns a saved refresh interval in hours, -1 when none is saved.
	 *
	 * @param int    $chart_id The chart ID.
	 * @param string $meta_key The schedule meta key.
	 * @return float
	 */
	private function getInterval( $chart_id, $meta_key ) {
		$hours = get_post_meta( $chart_id, $meta_key, true );

		return is_numeric( $hours ) ? (float) $hours : -1.0;
	}

	/**
	 * Returns the URL of a remote CSV/XLSX chart.
	 *
	 * @param WP_Post $chart The chart.
	 * @return string
	 */
	private function getRemoteFileUrl( $chart ) {
		$url = (string) get_post_meta( $chart->ID, Visualizer_Plugin::CF_CHART_URL, true );
		if ( '' === $url ) {
			// Same fallback as Visualizer_Source_Csv_Remote::_repopulate().
			$content = self::decode_content( html_entity_decode( $chart->post_content ) );
			$url     = ( is_array( $content ) && isset( $content['source'] ) && is_string( $content['source'] ) ) ? $content['source'] : '';
		}

		return wp_http_validate_url( $url ) ? $url : '';
	}

	/**
	 * Converts stored series to columns.
	 *
	 * @param mixed $series The series.
	 * @return array<int, array{name: string, type: string}>
	 */
	private function seriesToColumns( $series ) {
		$columns = array();
		if ( is_array( $series ) ) {
			foreach ( $series as $serie ) {
				if ( ! is_array( $serie ) ) {
					continue;
				}
				$columns[] = array(
					'name' => isset( $serie['label'] ) ? (string) $serie['label'] : '',
					'type' => isset( $serie['type'] ) ? (string) $serie['type'] : 'string',
				);
			}
		}

		return $columns;
	}

	/**
	 * Returns data rows as a list of lists.
	 *
	 * @param mixed $data The chart data.
	 * @return array<int, array<int, mixed>>
	 */
	private function normalizeRows( $data ) {
		$rows = array();
		if ( is_array( $data ) ) {
			foreach ( $data as $row ) {
				if ( is_array( $row ) ) {
					$rows[] = array_values( $row );
				}
			}
		}

		return $rows;
	}

	/**
	 * Returns the parsed preview of a fetched source.
	 *
	 * @param mixed $series The parsed series.
	 * @param mixed $data   The parsed data.
	 * @return array<string, mixed>
	 */
	private function getPreview( $series, $data ) {
		$rows = $this->normalizeRows( $data );

		return array(
			'columns'   => $this->seriesToColumns( $series ),
			'rows'      => array_slice( $rows, 0, self::PREVIEW_ROWS ),
			'row_count' => count( $rows ),
		);
	}
}
