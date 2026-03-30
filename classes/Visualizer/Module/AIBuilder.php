<?php
/**
 * AI Chart Builder module.
 *
 * AJAX endpoints for the React AI Chart Builder wizard:
 *   - visualizer-ai-create  : create a draft chart post
 *   - visualizer-ai-upload  : parse & persist data (CSV/XLSX file, URL, JSON, DB)
 *   - visualizer-ai-save    : persist D3.js code and publish
 *
 * @category Visualizer
 * @package  Module
 */
class Visualizer_Module_AIBuilder extends Visualizer_Module {

	const NAME     = __CLASS__;
	const CF_D3_CODE = 'visualizer-d3-code';

	/**
	 * Constructor.
	 *
	 * @param Visualizer_Plugin $plugin Plugin instance.
	 */
	public function __construct( $plugin ) {
		parent::__construct( $plugin );
		$this->_addAction( 'wp_ajax_visualizer-ai-create', 'createChart' );
		$this->_addAction( 'wp_ajax_visualizer-ai-upload', 'uploadData' );
		$this->_addAction( 'wp_ajax_visualizer-ai-save', 'saveChart' );
		$this->_addAction( 'wp_ajax_visualizer-ai-generate', 'generateChart' );
		$this->_addAction( 'wp_ajax_visualizer-ai-status', 'chartStatus' );
		$this->_addAction( 'wp_ajax_visualizer-ai-chart-nonce', 'getChartNonce' );
		$this->_addAction( 'wp_ajax_visualizer-ai-fetch', 'fetchChart' );
	}

	// -------------------------------------------------------------------------
	// Helpers
	// -------------------------------------------------------------------------

	/**
	 * Get the Agents workflow slug.
	 *
	 * @return string
	 */
	private function _get_workflow_slug() {
		return defined( 'VISUALIZER_AGENTS_WORKFLOW' ) ? VISUALIZER_AGENTS_WORKFLOW : 'visualizer-generate';
	}

	/**
	 * Resolve the license token used for agent authorization.
	 *
	 * @return string
	 */
	private function _get_agents_license_token() {
		$license = get_option( 'visualizer_pro_license_data', 'free' );
		if ( ! empty( $license ) && is_object( $license ) ) {
			$license = isset( $license->key ) ? $license->key : 'free';
		} else {
			$license = 'free';
		}

		return (string) $license;
	}

	/**
	 * Build request headers for the Agents service.
	 *
	 * @param bool $with_content_type Whether to include Content-Type header.
	 * @return array<string, string>
	 */
	private function _get_agents_headers( $with_content_type = false ) {
		$headers = array(
			'X-Site-Url' => home_url(),
			'Accept'     => 'application/json',
		);

		if ( $with_content_type ) {
			$headers['Content-Type'] = 'application/json';
		}

		$license = $this->_get_agents_license_token();
		if ( ! empty( $license ) ) {
			$headers['Authorization'] = 'Bearer ' . base64_encode( $license );
		}

		return $headers;
	}

	/**
	 * Verify nonce and capability for AI Builder requests.
	 */
	private function _verify_create_nonce(): void {
		check_ajax_referer( 'visualizer-ai-builder', 'nonce' );
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized.', 'visualizer' ) ), 403 );
		}
	}

	/**
	 * Persist chart data + series from a source.
	 *
	 * @param int               $chart_id Chart ID.
	 * @param Visualizer_Source $source   Data source instance.
	 */
	private function _persist( $chart_id, $source ): void {
		update_post_meta( $chart_id, Visualizer_Plugin::CF_SERIES, $source->getSeries() );
		update_post_meta( $chart_id, Visualizer_Plugin::CF_SOURCE, $source->getSourceName() );
		update_post_meta( $chart_id, Visualizer_Plugin::CF_DEFAULT_DATA, 0 );
		wp_update_post(
			array(
				'ID'           => $chart_id,
				'post_content' => $source->getData( false ),
			)
		);
	}

	// -------------------------------------------------------------------------
	// AJAX: create draft
	// -------------------------------------------------------------------------

	/**
	 * AJAX: create a draft chart post for AI Builder.
	 */
	public function createChart(): void {
		$this->_verify_create_nonce();

		$chart_id = wp_insert_post(
			array(
				'post_type'   => Visualizer_Plugin::CPT_VISUALIZER,
				'post_status' => 'auto-draft',
				'post_title'  => __( 'AI Chart', 'visualizer' ),
			),
			true
		);

		if ( is_wp_error( $chart_id ) ) {
			wp_send_json_error( array( 'message' => $chart_id->get_error_message() ) );
		}

		update_post_meta( $chart_id, Visualizer_Plugin::CF_CHART_LIBRARY, 'd3' );
		update_post_meta( $chart_id, Visualizer_Plugin::CF_SOURCE, 'Visualizer_Source_Csv' );

		wp_send_json_success(
			array(
				'chart_id'     => $chart_id,
				'upload_nonce' => wp_create_nonce( 'visualizer-ai-upload-' . $chart_id ),
			)
		);
	}

	// -------------------------------------------------------------------------
	// AJAX: get upload nonce for an existing chart (used in edit mode)
	// -------------------------------------------------------------------------

	/**
	 * AJAX: get upload nonce for an existing chart (edit mode).
	 */
	public function getChartNonce(): void {
		$this->_verify_create_nonce();
		$chart_id = intval( isset( $_POST['chart_id'] ) ? $_POST['chart_id'] : 0 );
		if ( ! $chart_id || ! get_post( $chart_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Chart not found.', 'visualizer' ) ) );
		}
		wp_send_json_success(
			array(
				'upload_nonce' => wp_create_nonce( 'visualizer-ai-upload-' . $chart_id ),
			)
		);
	}

	// -------------------------------------------------------------------------
	// AJAX: fetch chart data/spec for edit mode (used on refresh)
	// -------------------------------------------------------------------------

	/**
	 * AJAX: fetch chart data/spec for edit mode.
	 */
	public function fetchChart(): void {
		$this->_verify_create_nonce();
		$chart_id = intval( isset( $_POST['chart_id'] ) ? $_POST['chart_id'] : 0 );
		$chart    = $chart_id ? get_post( $chart_id ) : null;
		if ( ! $chart || $chart->post_type !== Visualizer_Plugin::CPT_VISUALIZER ) {
			wp_send_json_error( array( 'message' => __( 'Chart not found.', 'visualizer' ) ) );
		}

		$series   = get_post_meta( $chart_id, Visualizer_Plugin::CF_SERIES, true );
		$data     = Visualizer_Module::get_chart_data( $chart, '', false );
		$code     = get_post_meta( $chart_id, self::CF_D3_CODE, true );
		$settings = get_post_meta( $chart_id, Visualizer_Plugin::CF_SETTINGS, true );
		$title    = ( is_array( $settings ) && ! empty( $settings['backend-title'] ) )
			? $settings['backend-title']
			: $chart->post_title;

		wp_send_json_success(
			array(
				'title'  => $title,
				'series' => $series,
				'data'   => $data,
				'code'   => $code,
			)
		);
	}

	// -------------------------------------------------------------------------
	// AJAX: upload / parse data
	// -------------------------------------------------------------------------

	/**
	 * Determines whether a remote URL serves an XLSX file.
	 *
	 * Uses wp_safe_remote_get() and checks ZIP magic number (PK\x03\x04).
	 *
	 * @access private
	 * @param string $url The remote URL to probe.
	 * @return bool TRUE if the file appears to be XLSX, FALSE otherwise.
	 */
	private static function _url_is_xlsx( $url ) {
		$tmpfile = wp_tempnam( 'visualizer_xlsx_probe' );
		if ( ! $tmpfile ) {
			return false;
		}

		$response = wp_safe_remote_get(
			$url,
			array(
				'timeout'     => 15,
				'redirection' => 5,
				'stream'      => true,
				'filename'    => $tmpfile,
				'headers'     => array( 'Range' => 'bytes=0-3' ),
			)
		);

		if ( is_wp_error( $response ) ) {
			@unlink( $tmpfile );
			return false;
		}

		$body = file_get_contents( $tmpfile );
		@unlink( $tmpfile );

		if ( ! empty( $body ) ) {
			return 0 === strpos( $body, "PK\x03\x04" );
		}

		$content_type = wp_remote_retrieve_header( $response, 'content-type' );
		return is_string( $content_type ) && false !== stripos( $content_type, 'sheet' );
	}

	/**
	 * AJAX: upload/parse data for AI Builder.
	 */
	public function uploadData(): void {
		$chart_id = intval( isset( $_POST['chart_id'] ) ? $_POST['chart_id'] : 0 );
		check_ajax_referer( 'visualizer-ai-upload-' . $chart_id, 'nonce' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized.', 'visualizer' ) ), 403 );
		}
		if ( ! get_post( $chart_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Chart not found.', 'visualizer' ) ) );
		}

		$source_type = isset( $_POST['source_type'] ) ? sanitize_key( $_POST['source_type'] ) : 'csv_string';
		$source      = null;
		$tmp_files   = array();

		switch ( $source_type ) {

			// ── Manual CSV text ──────────────────────────────────────────────
			case 'csv_string':
				if ( empty( $_POST['csv_data'] ) ) {
					wp_send_json_error( array( 'message' => __( 'No data provided.', 'visualizer' ) ) );
				}
				$tmp = tempnam( sys_get_temp_dir(), 'viz_ai_' );
				file_put_contents( $tmp, wp_unslash( $_POST['csv_data'] ) );
				$tmp_files[] = $tmp;
				$source = new Visualizer_Source_Csv( $tmp );
				break;

			// ── CSV / XLSX file upload ────────────────────────────────────────
			case 'csv_file':
			case 'xlsx_file':
				if ( empty( $_FILES['data_file']['tmp_name'] ) ) {
					wp_send_json_error( array( 'message' => __( 'No file uploaded.', 'visualizer' ) ) );
				}
				$ext = strtolower( pathinfo( $_FILES['data_file']['name'], PATHINFO_EXTENSION ) );
				if ( $ext === 'xlsx' && class_exists( 'Visualizer_Source_Xlsx' ) ) {
					$source = new Visualizer_Source_Xlsx( $_FILES['data_file']['tmp_name'] );
				} else {
					$source = new Visualizer_Source_Csv( $_FILES['data_file']['tmp_name'] );
				}
				break;

			// ── Remote CSV / XLSX URL ─────────────────────────────────────────
			case 'file_url':
				if ( empty( $_POST['file_url'] ) ) {
					wp_send_json_error( array( 'message' => __( 'No URL provided.', 'visualizer' ) ) );
				}
				$url = wp_unslash( $_POST['file_url'] );

				// Allow local absolute paths in dev (same CSVs used by Classic).
				if ( is_string( $url ) && file_exists( $url ) && is_readable( $url ) ) {
					$ext = strtolower( pathinfo( $url, PATHINFO_EXTENSION ) );
					if ( 'xlsx' === $ext && class_exists( 'Visualizer_Source_Xlsx' ) ) {
						$source = new Visualizer_Source_Xlsx( $url );
					} else {
						$source = new Visualizer_Source_Csv( $url );
					}
					break;
				}

				if ( function_exists( 'wp_http_validate_url' ) ) {
					$validated_url = wp_http_validate_url( (string) $url );
					$url           = false === $validated_url ? false : (string) $validated_url;
				} else {
					$url = esc_url_raw( (string) $url );
				}
				if ( false === $url ) {
					wp_send_json_error( array( 'message' => __( 'Invalid URL. Please check the URL and try again.', 'visualizer' ) ) );
				}

				$ext = strtolower( pathinfo( parse_url( $url, PHP_URL_PATH ), PATHINFO_EXTENSION ) );
				if ( 'xlsx' === $ext || ( 'csv' !== $ext && self::_url_is_xlsx( $url ) ) ) {
					$source = new Visualizer_Source_Xlsx_Remote( $url );
				} else {
					$source = new Visualizer_Source_Csv_Remote( $url );
				}

				// Optionally store schedule
				if ( ! empty( $_POST['schedule'] ) ) {
					update_post_meta( $chart_id, 'visualizer-chart-url', $url );
					update_post_meta( $chart_id, 'visualizer-chart-schedule', intval( $_POST['schedule'] ) );
					apply_filters( 'visualizer_pro_chart_schedule', $chart_id, $url, $_POST['schedule'] );
				}
				break;

			// ── JSON URL ─────────────────────────────────────────────────────
			case 'json_url':
				if ( empty( $_POST['json_url'] ) ) {
					wp_send_json_error( array( 'message' => __( 'No URL provided.', 'visualizer' ) ) );
				}
				$params = array(
					'url'    => esc_url_raw( wp_unslash( $_POST['json_url'] ) ),
					'root'   => isset( $_POST['json_root'] ) ? sanitize_text_field( wp_unslash( $_POST['json_root'] ) ) : '',
					'paging' => isset( $_POST['json_paging'] ) ? sanitize_text_field( wp_unslash( $_POST['json_paging'] ) ) : '',
					'method' => ( isset( $_POST['json_method'] ) && $_POST['json_method'] === 'POST' ) ? 'POST' : 'GET',
				);
				if ( ! empty( $_POST['json_auth'] ) ) {
					$params['auth'] = sanitize_text_field( wp_unslash( $_POST['json_auth'] ) );
				} elseif ( ! empty( $_POST['json_username'] ) ) {
					$params['username'] = sanitize_text_field( wp_unslash( $_POST['json_username'] ) );
					$params['password'] = sanitize_text_field( wp_unslash( isset( $_POST['json_password'] ) ? $_POST['json_password'] : '' ) );
				}
				if ( ! empty( $_POST['json_headers'] ) ) {
					$params['headers'] = sanitize_textarea_field( wp_unslash( $_POST['json_headers'] ) );
				}
				$source = new Visualizer_Source_Json( $params );

				// Store config for sync
				update_post_meta( $chart_id, Visualizer_Plugin::CF_JSON_URL, $params['url'] );
				update_post_meta( $chart_id, Visualizer_Plugin::CF_JSON_ROOT, $params['root'] );
				if ( ! empty( $_POST['json_schedule'] ) ) {
					update_post_meta( $chart_id, Visualizer_Plugin::CF_JSON_SCHEDULE, intval( $_POST['json_schedule'] ) );
				}
				break;

			// ── Database query ────────────────────────────────────────────────
			case 'db_query':
				if ( empty( $_POST['db_query'] ) ) {
					wp_send_json_error( array( 'message' => __( 'No query provided.', 'visualizer' ) ) );
				}
				$query = wp_unslash( $_POST['db_query'] );
				$params = array();
				if ( ! empty( $_POST['db_host'] ) ) {
					$params = array(
						'host'     => sanitize_text_field( wp_unslash( $_POST['db_host'] ) ),
						'port'     => intval( isset( $_POST['db_port'] ) ? $_POST['db_port'] : 3306 ),
						'name'     => sanitize_text_field( wp_unslash( isset( $_POST['db_name'] ) ? $_POST['db_name'] : '' ) ),
						'username' => sanitize_text_field( wp_unslash( isset( $_POST['db_username'] ) ? $_POST['db_username'] : '' ) ),
						'password' => sanitize_text_field( wp_unslash( isset( $_POST['db_password'] ) ? $_POST['db_password'] : '' ) ),
						'type'     => sanitize_key( isset( $_POST['db_type'] ) ? $_POST['db_type'] : 'mysql' ),
					);
				}
				$source = new Visualizer_Source_Query( $query, $chart_id, $params );
				update_post_meta( $chart_id, 'visualizer-db-query', $query );
				break;

			default:
				wp_send_json_error( array( 'message' => __( 'Unknown source type.', 'visualizer' ) ) );
		}

		if ( ! $source->fetch() ) {
			foreach ( $tmp_files as $f ) {
				@unlink( $f );
			}
			wp_send_json_error(
				array(
					'message' => __( 'Could not parse data. Check format and try again.', 'visualizer' ),
				)
			);
		}

		foreach ( $tmp_files as $f ) {
			@unlink( $f );
		}

		$series = $source->getSeries();
		if ( empty( $series ) ) {
			wp_send_json_error( array( 'message' => __( 'No columns found. Check that row 1 has headers and row 2 has types (string/number/date).', 'visualizer' ) ) );
		}

		$this->_persist( $chart_id, $source );

		wp_send_json_success(
			array(
				'series' => $series,
				'data'   => $source->getRawData( false ),
			)
		);
	}

	// -------------------------------------------------------------------------
	// AJAX: generate chart (async — queues AI job, returns workflow_id)
	// -------------------------------------------------------------------------

	/**
	 * AJAX: start async chart generation.
	 */
	public function generateChart(): void {
		$this->_verify_create_nonce();

		$chart_id = intval( isset( $_POST['chart_id'] ) ? $_POST['chart_id'] : 0 );
		if ( ! $chart_id || ! get_post( $chart_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Chart not found.', 'visualizer' ) ) );
		}

		$prompt         = isset( $_POST['prompt'] ) ? sanitize_textarea_field( wp_unslash( $_POST['prompt'] ) ) : '';
		$series         = isset( $_POST['series'] ) ? wp_unslash( $_POST['series'] ) : '';
		$data           = isset( $_POST['data'] ) ? wp_unslash( $_POST['data'] ) : '';
		$existing_code  = isset( $_POST['existing_code'] ) ? wp_unslash( $_POST['existing_code'] ) : '';
		$ref_image      = isset( $_POST['ref_image'] ) ? wp_unslash( $_POST['ref_image'] ) : '';
		$ref_image_mime = isset( $_POST['ref_image_mime'] ) ? sanitize_text_field( wp_unslash( $_POST['ref_image_mime'] ) ) : '';

		if ( empty( $series ) || empty( $data ) ) {
			wp_send_json_error( array( 'message' => __( 'No data available. Load data first.', 'visualizer' ) ) );
		}

		$agents_url    = VISUALIZER_AGENTS_URL;
		$workflow_slug = $this->_get_workflow_slug();

		$request_body = array(
			'prompt' => $prompt,
			'series' => $series,
			'data'   => $data,
		);
		if ( ! empty( $existing_code ) ) {
			$request_body['existing_code'] = $existing_code;
		}
		if ( ! empty( $ref_image ) ) {
			$request_body['ref_image']      = $ref_image;
			$request_body['ref_image_mime'] = ! empty( $ref_image_mime ) ? $ref_image_mime : 'image/jpeg';
		}

		$headers = $this->_get_agents_headers( true );

		$response = wp_remote_post(
			trailingslashit( $agents_url ) . 'api/workflows/' . rawurlencode( $workflow_slug ) . '/start',
			array(
				'timeout' => 15,
				'headers' => $headers,
				'body'    => wp_json_encode( $request_body ),
			)
		);

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( array( 'message' => $response->get_error_message() ) );
		}

		$status        = wp_remote_retrieve_response_code( $response );
		$response_body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( $status !== 200 && $status !== 201 && $status !== 202 ) {
			$msg = ( is_array( $response_body ) && isset( $response_body['error'] ) ) ? $response_body['error'] : __( 'Generation request failed.', 'visualizer' );
			wp_send_json_error( array( 'message' => $msg ) );
		}

		$workflow_id = '';
		if ( is_array( $response_body ) ) {
			if ( isset( $response_body['workflowId'] ) ) {
				$workflow_id = $response_body['workflowId'];
			} elseif ( isset( $response_body['workflow_id'] ) ) {
				$workflow_id = $response_body['workflow_id'];
			} elseif ( isset( $response_body['data']['workflowId'] ) ) {
				$workflow_id = $response_body['data']['workflowId'];
			}
		}

		wp_send_json_success(
			array(
				'workflow_id' => $workflow_id,
			)
		);
	}

	// -------------------------------------------------------------------------
	// AJAX: poll generation status
	// -------------------------------------------------------------------------

	/**
	 * AJAX: poll generation status.
	 */
	public function chartStatus(): void {
		$this->_verify_create_nonce();

		$workflow_id = isset( $_POST['workflow_id'] ) ? sanitize_text_field( wp_unslash( $_POST['workflow_id'] ) ) : '';
		if ( empty( $workflow_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Missing workflow ID.', 'visualizer' ) ) );
		}

		$agents_url    = VISUALIZER_AGENTS_URL;
		$workflow_slug = $this->_get_workflow_slug();
		$headers       = $this->_get_agents_headers();

		$response = wp_remote_get(
			trailingslashit( $agents_url ) . 'api/workflows/' . rawurlencode( $workflow_slug ) . '/' . rawurlencode( $workflow_id ),
			array(
				'timeout' => 15,
				'headers' => $headers,
			)
		);

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( array( 'message' => $response->get_error_message() ) );
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( ! is_array( $body ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid response from AI service.', 'visualizer' ) ) );
		}

		wp_send_json_success( $body );
	}

	// -------------------------------------------------------------------------
	// AJAX: save chart
	// -------------------------------------------------------------------------

	/**
	 * AJAX: save chart with D3 code and publish.
	 */
	public function saveChart(): void {
		$this->_verify_create_nonce();

		$chart_id = intval( isset( $_POST['chart_id'] ) ? $_POST['chart_id'] : 0 );
		$code     = isset( $_POST['code'] ) ? wp_unslash( $_POST['code'] ) : '';
		$title    = sanitize_text_field( wp_unslash( isset( $_POST['title'] ) ? $_POST['title'] : __( 'AI Chart', 'visualizer' ) ) );

		if ( ! $chart_id || ! get_post( $chart_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Chart not found.', 'visualizer' ) ) );
		}
		if ( empty( $code ) ) {
			wp_send_json_error( array( 'message' => __( 'No chart code found. Generate a chart first.', 'visualizer' ) ) );
		}

		update_post_meta( $chart_id, self::CF_D3_CODE, $code );
		update_post_meta( $chart_id, Visualizer_Plugin::CF_CHART_LIBRARY, 'd3' );
		$settings = get_post_meta( $chart_id, Visualizer_Plugin::CF_SETTINGS, true );
		if ( ! is_array( $settings ) ) {
			$settings = array();
		}
		$settings['backend-title'] = $title;
		update_post_meta( $chart_id, Visualizer_Plugin::CF_SETTINGS, $settings );
		wp_update_post(
			array(
				'ID'          => $chart_id,
				'post_status' => 'publish',
				'post_title'  => $title,
			)
		);

		wp_send_json_success(
			array(
				'id'        => $chart_id,
				'shortcode' => '[visualizer id="' . $chart_id . '"]',
			)
		);
	}
}
