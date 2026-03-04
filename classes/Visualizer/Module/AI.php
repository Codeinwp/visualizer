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

/**
 * The module for AI-powered configuration generation.
 *
 * @category Visualizer
 * @package Module
 *
 * @since 3.12.0
 */
class Visualizer_Module_AI extends Visualizer_Module {

	const NAME = __CLASS__;
	const ACTION_GENERATE_CONFIG = 'visualizer-ai-generate-config';
	const ACTION_ANALYZE_CHART_IMAGE = 'visualizer-ai-analyze-chart-image';

	/**
	 * Constructor.
	 *
	 * @since 3.12.0
	 *
	 * @access public
	 *
	 * @param Visualizer_Plugin $plugin The instance of the plugin.
	 */
	public function __construct( Visualizer_Plugin $plugin ) {
		parent::__construct( $plugin );
		$this->_addAjaxAction( self::ACTION_GENERATE_CONFIG, 'generateConfiguration' );
		$this->_addAjaxAction( self::ACTION_ANALYZE_CHART_IMAGE, 'analyzeChartImage' );

		// Prevent PHP warnings from contaminating AJAX responses
		add_action( 'admin_init', array( $this, 'suppressAjaxWarnings' ) );
	}

	/**
	 * Suppresses PHP warnings during AJAX requests to prevent JSON contamination.
	 *
	 * @since 3.12.0
	 *
	 * @access public
	 * @return void
	 */
	public function suppressAjaxWarnings() {
		if ( wp_doing_ajax() ) {
			ini_set( 'display_errors', '0' );
		}
	}

	/**
	 * Handles AJAX request to generate configuration using AI.
	 *
	 * @since 3.12.0
	 *
	 * @access public
	 * @return void
	 */
	public function generateConfiguration() {
		error_log( 'Visualizer AI: generateConfiguration called' );

		// Verify nonce
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'visualizer-ai-generate' ) ) {
			error_log( 'Visualizer AI: Invalid nonce' );
			wp_send_json_error( array( 'message' => esc_html__( 'Invalid nonce.', 'visualizer' ) ) );
		}

		// Check permissions
		if ( ! current_user_can( 'edit_posts' ) ) {
			error_log( 'Visualizer AI: Insufficient permissions' );
			wp_send_json_error( array( 'message' => esc_html__( 'Insufficient permissions.', 'visualizer' ) ) );
		}

		$model = isset( $_POST['model'] ) ? sanitize_text_field( $_POST['model'] ) : 'openai';
		$prompt = isset( $_POST['prompt'] ) ? sanitize_textarea_field( $_POST['prompt'] ) : '';
		$chart_type = isset( $_POST['chart_type'] ) ? sanitize_text_field( $_POST['chart_type'] ) : '';
		$chart_library = isset( $_POST['chart_library'] ) ? sanitize_text_field( $_POST['chart_library'] ) : 'Google Charts';
		$chat_history = isset( $_POST['chat_history'] ) ? json_decode( stripslashes( $_POST['chat_history'] ), true ) : array();
		$current_config = isset( $_POST['current_config'] ) ? sanitize_textarea_field( $_POST['current_config'] ) : '';

		error_log( 'Visualizer AI: Model: ' . $model );
		error_log( 'Visualizer AI: Prompt: ' . $prompt );
		error_log( 'Visualizer AI: Chart Type: ' . $chart_type );
		error_log( 'Visualizer AI: Chart Library: ' . $chart_library );
		error_log( 'Visualizer AI: Chat History Items: ' . count( $chat_history ) );

		if ( empty( $prompt ) ) {
			error_log( 'Visualizer AI: Empty prompt' );
			wp_send_json_error( array( 'message' => esc_html__( 'Please provide a prompt.', 'visualizer' ) ) );
		}

		// Generate configuration based on selected model
		error_log( 'Visualizer AI: Calling AI model' );
		$result = $this->_callAIModel( $model, $prompt, $chart_type, $chart_library, $chat_history, $current_config );

		if ( is_wp_error( $result ) ) {
			error_log( 'Visualizer AI: Error: ' . $result->get_error_message() );
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		error_log( 'Visualizer AI: Success' );
		wp_send_json_success( $result );
	}

	/**
	 * Handles AJAX request to analyze chart image using AI vision.
	 *
	 * @since 3.12.0
	 *
	 * @access public
	 * @return void
	 */
	public function analyzeChartImage() {
		// Prevent any output before JSON response
		ini_set( 'display_errors', '0' );
		while ( ob_get_level() ) {
			ob_end_clean();
		}
		ob_start();

		error_log( 'Visualizer AI: analyzeChartImage called' );

		// Verify nonce
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'visualizer-ai-image' ) ) {
			error_log( 'Visualizer AI: Invalid nonce' );
			ob_end_clean();
			wp_send_json_error( array( 'message' => esc_html__( 'Invalid nonce.', 'visualizer' ) ) );
		}

		// Check permissions
		if ( ! current_user_can( 'edit_posts' ) ) {
			error_log( 'Visualizer AI: Insufficient permissions' );
			ob_end_clean();
			wp_send_json_error( array( 'message' => esc_html__( 'Insufficient permissions.', 'visualizer' ) ) );
		}

		// Get image data
		if ( ! isset( $_POST['image'] ) || empty( $_POST['image'] ) ) {
			error_log( 'Visualizer AI: No image provided' );
			ob_end_clean();
			wp_send_json_error( array( 'message' => esc_html__( 'Please provide an image.', 'visualizer' ) ) );
		}

		$image_data = $_POST['image'];
		$model = isset( $_POST['model'] ) ? sanitize_text_field( $_POST['model'] ) : 'openai';

		error_log( 'Visualizer AI: Model: ' . $model );
		error_log( 'Visualizer AI: Image data length: ' . strlen( $image_data ) );

		// Analyze image using AI vision
		$result = $this->_analyzeChartImageWithAI( $model, $image_data );

		if ( is_wp_error( $result ) ) {
			error_log( 'Visualizer AI: Error: ' . $result->get_error_message() );
			ob_end_clean();
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		error_log( 'Visualizer AI: Image analysis success' );
		ob_end_clean();
		wp_send_json_success( $result );
	}

	/**
	 * Calls the appropriate AI model API.
	 *
	 * @since 3.12.0
	 *
	 * @access private
	 *
	 * @param string               $model The AI model to use.
	 * @param string               $prompt The user prompt.
	 * @param string               $chart_type The chart type.
	 * @param string               $chart_library The chart library (Google Charts or ChartJS).
	 * @param array<string, mixed> $chat_history Previous conversation history.
	 * @param string               $current_config Current manual configuration.
	 *
	 * @return array<string, mixed>|WP_Error The response with message and optional configuration.
	 */
	private function _callAIModel( $model, $prompt, $chart_type, $chart_library = 'Google Charts', $chat_history = array(), $current_config = '' ) {
		switch ( $model ) {
			case 'openai':
				return $this->_callOpenAI( $prompt, $chart_type, $chart_library, $chat_history, $current_config );
			case 'gemini':
				return $this->_callGemini( $prompt, $chart_type, $chart_library, $chat_history, $current_config );
			case 'claude':
				return $this->_callClaude( $prompt, $chart_type, $chart_library, $chat_history, $current_config );
			default:
				return new WP_Error( 'invalid_model', esc_html__( 'Invalid AI model selected.', 'visualizer' ) );
		}
	}

	/**
	 * Creates the system prompt for AI models.
	 *
	 * @since 3.12.0
	 *
	 * @access private
	 *
	 * @param string $chart_type The chart type.
	 * @param string $chart_library The chart library (Google Charts or ChartJS).
	 *
	 * @return string The system prompt.
	 */
	private function _createSystemPrompt( $chart_type, $chart_library = 'Google Charts' ) {
		$chart_options = $this->_getChartTypeOptions( $chart_type, $chart_library );
		$library_name = strtolower( $chart_library ) === 'chartjs' ? 'Chart.js' : 'Google Charts';

		if ( strtolower( $chart_library ) === 'chartjs' ) {
			return 'You are a helpful Chart.js (ChartJS) v3+ API expert assistant. You help users customize their ' . $chart_type . ' charts through conversation.

IMPORTANT CHARTJS STRUCTURE:
Chart.js uses a specific configuration structure. You MUST follow these rules:

1. PLUGINS go under "plugins" object:
   - legend: plugins.legend
   - title: plugins.title
   - tooltip: plugins.tooltip
   Example: {"plugins": {"legend": {"display": true, "position": "bottom"}}}

2. SCALES go under "scales" object:
   - y-axis: scales.y
   - x-axis: scales.x
   Example: {"scales": {"y": {"beginAtZero": true}}}

3. DATASET PROPERTIES go at root level (these configure data appearance):
   - backgroundColor
   - borderColor
   - borderWidth
   Example: {"backgroundColor": ["#e74c3c", "#3498db"], "borderWidth": 2}

RESPONSE FORMAT:
When providing configuration, structure your response like this:
[Your explanation here]

JSON_START
{"property": "value"}
JSON_END

Example - Configuring legend:
I\'ll move the legend to the right side with larger red text.

JSON_START
{"plugins": {"legend": {"position": "right", "labels": {"color": "red", "font": {"size": 14}}}}}
JSON_END

' . $chart_options . '

Remember: Be conversational, provide context, and only include the properties that need to change!';
		}

		return 'You are a helpful ' . $library_name . ' API expert assistant. You help users customize their ' . $chart_type . ' charts through conversation.

IMPORTANT INSTRUCTIONS:
1. You are chatting with a user who wants to customize their chart. Be friendly, conversational, and helpful.
2. When the user asks what they can customize, provide specific suggestions for ' . $chart_type . ' charts.
3. When the user wants to make changes, provide the configuration in TWO parts:
   - First, explain what you\'re doing in plain English
   - Then, provide ONLY the JSON configuration needed (no markdown, no code blocks, just the raw JSON object)
4. IMPORTANT: Only include the specific properties being changed. Do not include the entire configuration.
5. For ' . $chart_type . ' charts, these are the most useful customization options:
' . $chart_options . '

RESPONSE FORMAT:
When providing configuration, structure your response like this:
[Your explanation here]

JSON_START
{"property": "value"}
JSON_END

Example:
I\'ll make the pie slices use vibrant colors and add a legend on the right side.

JSON_START
{"colors": ["#e74c3c", "#3498db", "#2ecc71", "#f39c12"], "legend": {"position": "right"}}
JSON_END

Remember: Be conversational, provide context, and only include the properties that need to change!';
	}

	/**
	 * Gets chart-specific customization options.
	 *
	 * @since 3.12.0
	 *
	 * @access private
	 *
	 * @param string $chart_type The chart type.
	 * @param string $chart_library The chart library.
	 *
	 * @return string Chart-specific options description.
	 */
	private function _getChartTypeOptions( $chart_type, $chart_library = 'Google Charts' ) {
		// Return ChartJS options if using ChartJS library
		if ( strtolower( $chart_library ) === 'chartjs' ) {
			return $this->_getChartJSOptions( $chart_type );
		}

		// Return Google Charts options (default)
		$options = array(
			'pie' => '
   - colors: Array of colors for pie slices ["#e74c3c", "#3498db", "#2ecc71"]
   - pieHole: Number 0-1 for donut chart (0.4 makes a donut)
   - pieSliceText: "percentage", "value", "label", or "none"
   - slices: Configure individual slices {0: {offset: 0.1, color: "#e74c3c"}}
   - is3D: true/false for 3D effect
   - legend: {position: "right", alignment: "center", textStyle: {color: "#000", fontSize: 12}}
   - chartArea: {width: "80%", height: "80%"}
   - backgroundColor: "#ffffff" or {fill: "#f0f0f0"}
   - pieSliceBorderColor: "#ffffff"
   - pieSliceTextStyle: {color: "#000", fontSize: 14}',

			'line' => '
   - colors: Array of line colors ["#e74c3c", "#3498db", "#2ecc71"]
   - curveType: "none" or "function" (for smooth curves)
   - lineWidth: Number (default 2)
   - pointSize: Number (default 0, size of data points)
   - vAxis: {title: "Y Axis", minValue: 0, maxValue: 100, ticks: [0, 25, 50, 75, 100], textStyle: {color: "#000"}}
   - hAxis: {title: "X Axis", slantedText: true, textStyle: {color: "#000"}}
   - legend: {position: "bottom", alignment: "center"}
   - series: {0: {lineWidth: 5}, 1: {lineDashStyle: [4, 4]}}
   - chartArea: {width: "80%", height: "70%"}
   - backgroundColor: "#ffffff"',

			'bar' => '
   - colors: Array of bar colors ["#e74c3c", "#3498db"]
   - isStacked: true/false or "percent" or "relative"
   - vAxis: {title: "Categories", textStyle: {color: "#000", fontSize: 12}}
   - hAxis: {title: "Values", minValue: 0, ticks: [0, 10, 20, 30]}
   - legend: {position: "top"}
   - bar: {groupWidth: "75%"}
   - chartArea: {width: "70%", height: "80%"}',

			'column' => '
   - colors: Array of column colors ["#e74c3c", "#3498db"]
   - isStacked: true/false or "percent"
   - vAxis: {title: "Values", minValue: 0, gridlines: {color: "#ccc"}}
   - hAxis: {title: "Categories", slantedText: true}
   - legend: {position: "top"}
   - bar: {groupWidth: "75%"}
   - chartArea: {width: "80%", height: "70%"}',

			'area' => '
   - colors: Array of area colors ["#e74c3c", "#3498db"]
   - isStacked: true/false or "percent"
   - areaOpacity: Number 0-1 (default 0.3)
   - vAxis: {title: "Values", minValue: 0}
   - hAxis: {title: "Time"}
   - legend: {position: "bottom"}
   - chartArea: {width: "80%", height: "70%"}',
		);

		return isset( $options[ $chart_type ] ) ? $options[ $chart_type ] : $options['line'];
	}

	/**
	 * Gets Chart.js-specific customization options.
	 *
	 * @since 3.12.0
	 *
	 * @access private
	 *
	 * @param string $chart_type The chart type.
	 *
	 * @return string Chart.js-specific options description.
	 */
	private function _getChartJSOptions( $chart_type ) {
		$options = array(
			'pie' => '
COMMON CUSTOMIZATIONS FOR PIE CHARTS:

Legend (goes under plugins.legend):
{"plugins": {"legend": {"display": true, "position": "top|bottom|left|right", "labels": {"color": "red", "font": {"size": 14, "family": "Arial", "weight": "bold"}}}}}

Title (goes under plugins.title):
{"plugins": {"title": {"display": true, "text": "My Chart Title", "color": "#333", "font": {"size": 18}}}}

Colors (dataset properties at root):
{"backgroundColor": ["#e74c3c", "#3498db", "#2ecc71", "#f39c12"], "borderColor": "#fff", "borderWidth": 2}

Donut hole (dataset property):
{"cutout": "50%"} - creates donut with 50% center cutout

Chart size (dataset property):
{"radius": "90%"} - controls pie size (percentage of canvas)',

			'doughnut' => '
COMMON CUSTOMIZATIONS FOR DOUGHNUT CHARTS:

Legend (goes under plugins.legend):
{"plugins": {"legend": {"display": true, "position": "top|bottom|left|right", "labels": {"color": "red", "font": {"size": 14}}}}}

Title (goes under plugins.title):
{"plugins": {"title": {"display": true, "text": "My Chart Title"}}}

Colors (dataset properties at root):
{"backgroundColor": ["#e74c3c", "#3498db", "#2ecc71"], "borderColor": "#fff", "borderWidth": 2}

Donut size (dataset property):
{"cutout": "70%"} - larger number = bigger hole',

			'line' => '
COMMON CUSTOMIZATIONS FOR LINE CHARTS:

Legend (goes under plugins.legend):
{"plugins": {"legend": {"display": true, "position": "bottom", "labels": {"color": "#666", "font": {"size": 12}}}}}

Y-Axis (goes under scales.y):
{"scales": {"y": {"beginAtZero": true, "title": {"display": true, "text": "Values"}, "ticks": {"color": "#666"}, "grid": {"color": "#e0e0e0"}}}}

X-Axis (goes under scales.x):
{"scales": {"x": {"title": {"display": true, "text": "Time"}, "ticks": {"color": "#666"}}}}

Line appearance (dataset properties at root):
{"borderColor": "#e74c3c", "backgroundColor": "rgba(231, 76, 60, 0.2)", "borderWidth": 3, "tension": 0.4, "fill": true, "pointRadius": 4, "pointBackgroundColor": "#e74c3c"}

tension: 0 = straight lines, 0.4 = smooth curves',

			'bar' => '
COMMON CUSTOMIZATIONS FOR BAR CHARTS:

Legend (goes under plugins.legend):
{"plugins": {"legend": {"display": true, "position": "top"}}}

Y-Axis (goes under scales.y):
{"scales": {"y": {"beginAtZero": true, "title": {"display": true, "text": "Values"}, "ticks": {"color": "#666"}}}}

X-Axis (goes under scales.x):
{"scales": {"x": {"title": {"display": true, "text": "Categories"}}}}

Bar appearance (dataset properties at root):
{"backgroundColor": ["#e74c3c", "#3498db", "#2ecc71"], "borderColor": "#333", "borderWidth": 1, "borderRadius": 5}

For horizontal bars (dataset property):
{"indexAxis": "y"}',

			'horizontalBar' => '
Same as bar chart. Use {"indexAxis": "y"} to make bars horizontal.',
		);

		return isset( $options[ $chart_type ] ) ? $options[ $chart_type ] : $options['line'];
	}

	/**
	 * Calls OpenAI API.
	 *
	 * @since 3.12.0
	 *
	 * @access private
	 *
	 * @param string               $prompt The user prompt.
	 * @param string               $chart_type The chart type.
	 * @param string               $chart_library The chart library.
	 * @param array<string, mixed> $chat_history Previous conversation history.
	 * @param string               $current_config Current manual configuration.
	 *
	 * @return array<string, mixed>|WP_Error The response with message and optional configuration.
	 */
	private function _callOpenAI( $prompt, $chart_type, $chart_library = 'Google Charts', $chat_history = array(), $current_config = '' ) {
		error_log( 'Visualizer AI: Calling OpenAI API' );

		$api_key = get_option( 'visualizer_openai_api_key', '' );

		if ( empty( $api_key ) ) {
			error_log( 'Visualizer AI: OpenAI API key not configured' );
			return new WP_Error( 'no_api_key', esc_html__( 'OpenAI API key is not configured.', 'visualizer' ) );
		}

		// Build messages array
		$messages = array(
			array(
				'role'    => 'system',
				'content' => $this->_createSystemPrompt( $chart_type, $chart_library ),
			),
		);

		// Add context about current configuration if exists
		if ( ! empty( $current_config ) ) {
			$messages[] = array(
				'role'    => 'system',
				'content' => 'The user currently has this configuration: ' . $current_config,
			);
		}

		// Add chat history
		if ( ! empty( $chat_history ) ) {
			foreach ( $chat_history as $msg ) {
				$messages[] = array(
					'role'    => $msg['role'],
					'content' => $msg['content'],
				);
			}
		}

		// Add current prompt
		$messages[] = array(
			'role'    => 'user',
			'content' => $prompt,
		);

		$request_body = array(
			'model'       => 'gpt-4',
			'messages'    => $messages,
			'temperature' => 0.7,
		);

		$response = wp_remote_post(
			'https://api.openai.com/v1/chat/completions',
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $api_key,
					'Content-Type'  => 'application/json',
				),
				'body'    => wp_json_encode( $request_body ),
				'timeout' => 30,
			)
		);

		if ( is_wp_error( $response ) ) {
			error_log( 'Visualizer AI: OpenAI HTTP Error: ' . $response->get_error_message() );
			return $response;
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		$response_body = wp_remote_retrieve_body( $response );

		error_log( 'Visualizer AI: OpenAI Response Code: ' . $response_code );

		$body = json_decode( $response_body, true );

		if ( isset( $body['error'] ) ) {
			error_log( 'Visualizer AI: OpenAI API Error: ' . $body['error']['message'] );
			return new WP_Error( 'api_error', $body['error']['message'] );
		}

		if ( ! isset( $body['choices'][0]['message']['content'] ) ) {
			error_log( 'Visualizer AI: Invalid OpenAI response structure' );
			return new WP_Error( 'invalid_response', esc_html__( 'Invalid response from OpenAI.', 'visualizer' ) );
		}

		$content = $body['choices'][0]['message']['content'];
		error_log( 'Visualizer AI: OpenAI Content: ' . $content );

		return $this->_parseResponse( $content );
	}

	/**
	 * Calls Google Gemini API.
	 *
	 * @since 3.12.0
	 *
	 * @access private
	 *
	 * @param string               $prompt The user prompt.
	 * @param string               $chart_type The chart type.
	 * @param string               $chart_library The chart library.
	 * @param array<string, mixed> $chat_history Previous conversation history.
	 * @param string               $current_config Current manual configuration.
	 *
	 * @return array<string, mixed>|WP_Error The response with message and optional configuration.
	 */
	private function _callGemini( $prompt, $chart_type, $chart_library = 'Google Charts', $chat_history = array(), $current_config = '' ) {
		$api_key = get_option( 'visualizer_gemini_api_key', '' );

		if ( empty( $api_key ) ) {
			return new WP_Error( 'no_api_key', esc_html__( 'Gemini API key is not configured.', 'visualizer' ) );
		}

		// Build the full prompt with context
		$full_prompt = $this->_createSystemPrompt( $chart_type, $chart_library ) . "\n\n";

		if ( ! empty( $current_config ) ) {
			$full_prompt .= 'Current configuration: ' . $current_config . "\n\n";
		}

		if ( ! empty( $chat_history ) ) {
			foreach ( $chat_history as $msg ) {
				$role = $msg['role'] === 'user' ? 'User' : 'Assistant';
				$full_prompt .= $role . ': ' . $msg['content'] . "\n\n";
			}
		}

		$full_prompt .= 'User: ' . $prompt;

		$response = wp_remote_post(
			'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=' . $api_key,
			array(
				'headers' => array(
					'Content-Type' => 'application/json',
				),
				'body'    => wp_json_encode(
					array(
						'contents' => array(
							array(
								'parts' => array(
									array( 'text' => $full_prompt ),
								),
							),
						),
						'generationConfig' => array(
							'temperature' => 0.7,
						),
					)
				),
				'timeout' => 30,
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( isset( $body['error'] ) ) {
			return new WP_Error( 'api_error', $body['error']['message'] );
		}

		if ( ! isset( $body['candidates'][0]['content']['parts'][0]['text'] ) ) {
			return new WP_Error( 'invalid_response', esc_html__( 'Invalid response from Gemini.', 'visualizer' ) );
		}

		return $this->_parseResponse( $body['candidates'][0]['content']['parts'][0]['text'] );
	}

	/**
	 * Calls Anthropic Claude API.
	 *
	 * @since 3.12.0
	 *
	 * @access private
	 *
	 * @param string               $prompt The user prompt.
	 * @param string               $chart_type The chart type.
	 * @param string               $chart_library The chart library.
	 * @param array<string, mixed> $chat_history Previous conversation history.
	 * @param string               $current_config Current manual configuration.
	 *
	 * @return array<string, mixed>|WP_Error The response with message and optional configuration.
	 */
	private function _callClaude( $prompt, $chart_type, $chart_library = 'Google Charts', $chat_history = array(), $current_config = '' ) {
		$api_key = get_option( 'visualizer_claude_api_key', '' );

		if ( empty( $api_key ) ) {
			return new WP_Error( 'no_api_key', esc_html__( 'Claude API key is not configured.', 'visualizer' ) );
		}

		// Build system prompt with context
		$system_prompt = $this->_createSystemPrompt( $chart_type, $chart_library );
		if ( ! empty( $current_config ) ) {
			$system_prompt .= "\n\nCurrent configuration: " . $current_config;
		}

		// Build messages array
		$messages = array();

		// Add chat history
		if ( ! empty( $chat_history ) ) {
			foreach ( $chat_history as $msg ) {
				$messages[] = array(
					'role'    => $msg['role'],
					'content' => $msg['content'],
				);
			}
		}

		// Add current prompt
		$messages[] = array(
			'role'    => 'user',
			'content' => $prompt,
		);

		$response = wp_remote_post(
			'https://api.anthropic.com/v1/messages',
			array(
				'headers' => array(
					'x-api-key'         => $api_key,
					'anthropic-version' => '2023-06-01',
					'Content-Type'      => 'application/json',
				),
				'body'    => wp_json_encode(
					array(
						'model'      => 'claude-3-5-sonnet-20241022',
						'max_tokens' => 1024,
						'system'     => $system_prompt,
						'messages'   => $messages,
					)
				),
				'timeout' => 30,
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( isset( $body['error'] ) ) {
			return new WP_Error( 'api_error', $body['error']['message'] );
		}

		if ( ! isset( $body['content'][0]['text'] ) ) {
			return new WP_Error( 'invalid_response', esc_html__( 'Invalid response from Claude.', 'visualizer' ) );
		}

		return $this->_parseResponse( $body['content'][0]['text'] );
	}

	/**
	 * Parses AI response to extract message and configuration.
	 *
	 * @since 3.12.0
	 *
	 * @access private
	 *
	 * @param string $text The AI response text.
	 *
	 * @return array<string, mixed> The parsed response with message and optional configuration.
	 */
	private function _parseResponse( $text ) {
		error_log( 'Visualizer AI: Parsing response: ' . substr( $text, 0, 200 ) . '...' );

		$result = array(
			'message' => '',
			'configuration' => null,
		);

		// Check for JSON_START and JSON_END markers
		if ( preg_match( '/JSON_START\s*(.*?)\s*JSON_END/s', $text, $matches ) ) {
			error_log( 'Visualizer AI: Found JSON markers' );

			// Extract message (everything before JSON_START)
			$message = preg_replace( '/JSON_START.*?JSON_END/s', '', $text );
			$result['message'] = trim( $message );

			// Extract and validate JSON
			$json_text = trim( $matches[1] );
			json_decode( $json_text );

			if ( json_last_error() === JSON_ERROR_NONE ) {
				$result['configuration'] = $json_text;
				error_log( 'Visualizer AI: Successfully extracted JSON configuration' );
			} else {
				error_log( 'Visualizer AI: JSON validation error: ' . json_last_error_msg() );
				$result['message'] .= "\n\n(Note: I tried to provide a configuration, but it had formatting issues.)";
			}
		} else {
			// No JSON markers, might be a conversational response or JSON in markdown
			error_log( 'Visualizer AI: No JSON markers found, checking for JSON object' );

			// Try to find JSON object in text
			if ( preg_match( '/\{[\s\S]*\}/U', $text, $json_matches ) ) {
				$json_text = $json_matches[0];
				json_decode( $json_text );

				if ( json_last_error() === JSON_ERROR_NONE ) {
					// Remove the JSON from the message
					$message = str_replace( $json_text, '', $text );
					// Also remove markdown code blocks
					$message = preg_replace( '/```json\s*/', '', $message );
					$message = preg_replace( '/```\s*/', '', $message );

					$result['message'] = trim( $message );
					$result['configuration'] = $json_text;
					error_log( 'Visualizer AI: Extracted JSON from text' );
				} else {
					// No valid JSON, treat entire response as message
					$result['message'] = trim( $text );
					error_log( 'Visualizer AI: No valid JSON found, treating as pure message' );
				}
			} else {
				// No JSON at all, pure conversational response
				$result['message'] = trim( $text );
				error_log( 'Visualizer AI: Pure conversational response, no JSON' );
			}
		}

		// If message is empty, use a default
		if ( empty( $result['message'] ) && ! empty( $result['configuration'] ) ) {
			$result['message'] = 'Here\'s the configuration you requested:';
		}

		return $result;
	}

	/**
	 * Analyzes chart image using AI vision.
	 *
	 * @since 3.12.0
	 *
	 * @access private
	 *
	 * @param string $model The AI model to use.
	 * @param string $image_data Base64 encoded image data.
	 *
	 * @return array<string, mixed>|WP_Error The analysis result or WP_Error on failure.
	 */
	private function _analyzeChartImageWithAI( $model, $image_data ) {
		error_log( 'Visualizer AI: Analyzing image with model: ' . $model );

		switch ( $model ) {
			case 'openai':
				return $this->_analyzeImageWithOpenAI( $image_data );
			case 'gemini':
				return $this->_analyzeImageWithGemini( $image_data );
			case 'claude':
				return $this->_analyzeImageWithClaude( $image_data );
			default:
				return new WP_Error( 'invalid_model', esc_html__( 'Invalid AI model selected.', 'visualizer' ) );
		}
	}

	/**
	 * Analyzes chart image using OpenAI Vision API.
	 *
	 * @since 3.12.0
	 *
	 * @access private
	 *
	 * @param string $image_data Base64 encoded image data.
	 *
	 * @return array<string, mixed>|WP_Error The analysis result or WP_Error on failure.
	 */
	private function _analyzeImageWithOpenAI( $image_data ) {
		error_log( 'Visualizer AI: Analyzing image with OpenAI Vision' );

		$api_key = get_option( 'visualizer_openai_api_key', '' );

		if ( empty( $api_key ) ) {
			return new WP_Error( 'no_api_key', esc_html__( 'OpenAI API key is not configured.', 'visualizer' ) );
		}

		$prompt = 'You are a data visualization expert helping to extract and recreate chart data. Analyze this chart image to extract all information needed to recreate it accurately.

Your task is to analyze the visual chart and provide structured data that can be used to recreate it. This is for data extraction and visualization purposes.

IMPORTANT: Pay careful attention to extracting accurate data values. Study the Y-axis scale and gridlines carefully. If a bar or line point falls between gridlines, INTERPOLATE the value - do not round to the nearest gridline. Example: If gridlines are at 10 and 20, and a bar reaches 60% between them, the value is 16.

STEP 1: IDENTIFY CHART TYPE
Examine the chart carefully to determine the correct type.

SUPPORTED CHART TYPES:
- tabular (table with rows and columns of data)
- pie (circular chart with slices, can be donut with hole in center)
- line (data points connected by lines)
- bar (horizontal bars)
- column (vertical bars/columns)
- area (filled area under line)
- scatter (individual data points, no connecting lines)
- bubble (scatter with varying point sizes)
- geo (geographic/map visualization)
- gauge (meter/speedometer style)
- candlestick (financial chart with open/high/low/close)
- timeline (horizontal timeline events)
- combo (CRITICAL: chart with MULTIPLE visualization types - e.g., columns AND lines together)
- radar (spider/radar chart)
- polarArea (polar area chart)

CRITICAL - COMBO CHART DETECTION:
If you see BOTH columns/bars AND lines in the SAME chart, this is a COMBO chart, NOT a column or line chart!
Example: Sales shown as columns + Average shown as a line = COMBO chart
Look for: Multiple data series displayed with different visual types (some as bars, some as lines)

STEP 2: VISUAL LAYOUT ANALYSIS

Look carefully at WHERE the legend is located (right/bottom/top/left/none) and extract the exact title text.

STEP 3: CHART-TYPE-SPECIFIC ANALYSIS

For PIE CHARTS:
- Extract colors for each slice in order
- Check if percentages or labels shown on slices
- Detect 2D vs 3D, donut style
- Note legend position

For COMBO CHARTS:
- CRITICAL: Identify which data series should be columns and which should be lines
- Set "seriesType": "bars" as default
- Use "series": {1: {"type": "line"}} to specify which series differ from default
- Example: First series columns, second series line

For BAR/COLUMN/LINE CHARTS:
- Extract colors for each data series
- Note axis titles and gridline visibility
- Check for data labels on bars or points

STEP 4: COLOR EXTRACTION
Extract colors in exact order. Use hex codes (e.g., #3366CC, #DC3912, #FF9900).

STEP 5: DATA EXTRACTION

Extract data values CAREFULLY by reading the Y-axis scale and gridlines. INTERPOLATE values between gridlines - do not round. Example: If gridlines are at 10 and 20, and a bar reaches 60% between them, use 16 not 10 or 20. Values should be accurate within 5-10% of visual appearance.

CSV DATA FORMAT (MANDATORY):
- Row 1: Column headers
- Row 2: Data types (string, number, date, datetime, boolean, timeofday)
- Row 3+: Actual data values

Example for PIE:
Category,Value
string,number
Product A,35
Product B,25
Product C,40

Example for LINE/COLUMN:
Month,Sales,Expenses
string,number,number
Jan,1000,800
Feb,1200,900

Example for COMBO (columns + lines):
Month,Sales,Average
string,number,number
Jan,1000,850
Feb,1200,900
(Note: In styling, specify which series is line vs column using "series" property)

Example with ANNOTATIONS (data labels on points):
Month,Sales,Annotation
string,number,string
Jan,1000,Peak
Feb,800,null
Mar,1200,Record


STEP 6: FORMAT YOUR RESPONSE

FORMAT YOUR RESPONSE EXACTLY AS FOLLOWS:
CHART_TYPE: [pie/line/bar/column/area/scatter/etc]
TITLE: [exact title text or "Untitled" if none]
CSV_DATA:
[csv data with headers, data types on row 2, then actual data]
STYLING:
[VALID JSON - see structure below]

STYLING JSON - INCLUDE ALL APPLICABLE PROPERTIES:

For PIE CHARTS:
{
  "title": "Exact Title From Image",
  "colors": ["#color1", "#color2", "#color3"],
  "legend": {"position": "bottom"},
  "pieSliceText": "percentage",
  "pieSliceTextStyle": {"fontSize": 12},
  "pieHole": 0,
  "is3D": false,
  "chartArea": {"width": "90%", "height": "80%"}
}

For BAR/COLUMN CHARTS:
{
  "title": "Exact Title From Image",
  "colors": ["#color1", "#color2"],
  "legend": {"position": "top"},
  "vAxis": {"title": "Y Axis Title", "gridlines": {"color": "#e0e0e0"}},
  "hAxis": {"title": "X Axis Title"},
  "isStacked": false,
  "chartArea": {"width": "70%", "height": "70%"}
}

For LINE/AREA CHARTS:
{
  "title": "Exact Title From Image",
  "colors": ["#color1", "#color2"],
  "legend": {"position": "right"},
  "vAxis": {"title": "Y Axis Title"},
  "hAxis": {"title": "X Axis Title"},
  "lineWidth": 2,
  "pointSize": 5,
  "chartArea": {"width": "80%", "height": "70%"}
}

For COMBO CHARTS (columns + lines together):
{
  "title": "Exact Title From Image",
  "colors": ["#color1", "#color2"],
  "legend": {"position": "bottom"},
  "seriesType": "bars",
  "series": {
    "1": {"type": "line", "lineWidth": 2, "pointSize": 4}
  },
  "vAxis": {"title": "Y Axis Title"},
  "hAxis": {"title": "X Axis Title"},
  "chartArea": {"width": "80%", "height": "70%"}
}

For BUBBLE CHARTS:
{
  "title": "Exact Title From Image",
  "colors": ["#color1"],
  "legend": {"position": "right"},
  "bubble": {"textStyle": {"fontSize": 11}},
  "vAxis": {"title": "Y Axis"},
  "hAxis": {"title": "X Axis"}
}

For GEO CHARTS:
{
  "title": "Exact Title From Image",
  "colorAxis": {"colors": ["#e0e0e0", "#0066cc"]},
  "region": "world"
}

For GAUGE CHARTS:
{
  "title": "Exact Title From Image",
  "redFrom": 90,
  "redTo": 100,
  "yellowFrom": 75,
  "yellowTo": 90,
  "greenFrom": 0,
  "greenTo": 75,
  "minorTicks": 5
}

For SCATTER CHARTS:
{
  "title": "Exact Title From Image",
  "colors": ["#color1"],
  "pointSize": 3,
  "vAxis": {"title": "Y Axis"},
  "hAxis": {"title": "X Axis"}
}

CRITICAL RULES:
1. CHART TYPE: If you see columns AND lines together, use "combo" not "column"!
2. DATA VALUES: Interpolate between gridlines, do not round. Must be accurate within 5-10%.
3. LEGEND POSITION: Check carefully - right/left/top/bottom?
4. COLORS: Extract in exact order, use hex codes
5. STYLING must be valid JSON with double quotes
6. For combo charts: Use "seriesType" and "series" object to specify types';

		$messages = array(
			array(
				'role' => 'user',
				'content' => array(
					array(
						'type' => 'text',
						'text' => $prompt,
					),
					array(
						'type' => 'image_url',
						'image_url' => array(
							'url' => $image_data,
						),
					),
				),
			),
		);

		$request_body = array(
			'model' => 'gpt-4o',
			'messages' => $messages,
			'max_tokens' => 2000,
		);

		$response = wp_remote_post(
			'https://api.openai.com/v1/chat/completions',
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $api_key,
					'Content-Type' => 'application/json',
				),
				'body' => wp_json_encode( $request_body ),
				'timeout' => 60,
			)
		);

		if ( is_wp_error( $response ) ) {
			error_log( 'Visualizer AI: OpenAI Vision HTTP Error: ' . $response->get_error_message() );
			return $response;
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		$response_body = wp_remote_retrieve_body( $response );

		error_log( 'Visualizer AI: OpenAI Vision Response Code: ' . $response_code );

		$body = json_decode( $response_body, true );

		if ( isset( $body['error'] ) ) {
			error_log( 'Visualizer AI: OpenAI Vision API Error: ' . $body['error']['message'] );
			return new WP_Error( 'api_error', $body['error']['message'] );
		}

		if ( ! isset( $body['choices'][0]['message']['content'] ) ) {
			return new WP_Error( 'invalid_response', esc_html__( 'Invalid response from OpenAI Vision.', 'visualizer' ) );
		}

		$content = $body['choices'][0]['message']['content'];
		error_log( 'Visualizer AI: OpenAI Vision Content: ' . substr( $content, 0, 500 ) );

		return $this->_parseImageAnalysisResponse( $content );
	}

	/**
	 * Analyzes chart image using Google Gemini Vision API.
	 *
	 * @since 3.12.0
	 *
	 * @access private
	 *
	 * @param string $image_data Base64 encoded image data.
	 *
	 * @return array<string, mixed>|WP_Error The analysis result or WP_Error on failure.
	 */
	private function _analyzeImageWithGemini( $image_data ) {
		error_log( 'Visualizer AI: Analyzing image with Gemini Vision' );

		$api_key = get_option( 'visualizer_gemini_api_key', '' );

		if ( empty( $api_key ) ) {
			return new WP_Error( 'no_api_key', esc_html__( 'Google Gemini API key is not configured.', 'visualizer' ) );
		}

		// Extract base64 data from data URL
		$image_parts = explode( ',', $image_data );
		$base64_image = isset( $image_parts[1] ) ? $image_parts[1] : $image_data;

		$prompt = 'You are a data visualization expert helping to extract and recreate chart data. Analyze this chart image to extract all information needed to recreate it accurately.

Your task is to analyze the visual chart and provide structured data that can be used to recreate it. This is for data extraction and visualization purposes.

IMPORTANT: Pay careful attention to extracting accurate data values. Study the Y-axis scale and gridlines carefully. If a bar or line point falls between gridlines, INTERPOLATE the value - do not round to the nearest gridline. Example: If gridlines are at 10 and 20, and a bar reaches 60% between them, the value is 16.

STEP 1: IDENTIFY CHART TYPE
Examine the chart carefully to determine the correct type.

SUPPORTED CHART TYPES:
- tabular (table with rows and columns of data)
- pie (circular chart with slices, can be donut with hole in center)
- line (data points connected by lines)
- bar (horizontal bars)
- column (vertical bars/columns)
- area (filled area under line)
- scatter (individual data points, no connecting lines)
- bubble (scatter with varying point sizes)
- geo (geographic/map visualization)
- gauge (meter/speedometer style)
- candlestick (financial chart with open/high/low/close)
- timeline (horizontal timeline events)
- combo (CRITICAL: chart with MULTIPLE visualization types - e.g., columns AND lines together)
- radar (spider/radar chart)
- polarArea (polar area chart)

CRITICAL - COMBO CHART DETECTION:
If you see BOTH columns/bars AND lines in the SAME chart, this is a COMBO chart, NOT a column or line chart!
Example: Sales shown as columns + Average shown as a line = COMBO chart
Look for: Multiple data series displayed with different visual types (some as bars, some as lines)

STEP 2: VISUAL LAYOUT ANALYSIS

Look carefully at WHERE the legend is located (right/bottom/top/left/none) and extract the exact title text.

STEP 3: CHART-TYPE-SPECIFIC ANALYSIS

For PIE CHARTS:
- Extract colors for each slice in order
- Check if percentages or labels shown on slices
- Detect 2D vs 3D, donut style
- Note legend position

For COMBO CHARTS:
- CRITICAL: Identify which data series should be columns and which should be lines
- Set "seriesType": "bars" as default
- Use "series": {1: {"type": "line"}} to specify which series differ from default
- Example: First series columns, second series line

For BAR/COLUMN/LINE CHARTS:
- Extract colors for each data series
- Note axis titles and gridline visibility
- Check for data labels on bars or points

STEP 4: COLOR EXTRACTION
Extract colors in exact order. Use hex codes (e.g., #3366CC, #DC3912, #FF9900).

STEP 5: DATA EXTRACTION

Extract data values CAREFULLY by reading the Y-axis scale and gridlines. INTERPOLATE values between gridlines - do not round. Example: If gridlines are at 10 and 20, and a bar reaches 60% between them, use 16 not 10 or 20. Values should be accurate within 5-10% of visual appearance.

CSV DATA FORMAT (MANDATORY):
- Row 1: Column headers
- Row 2: Data types (string, number, date, datetime, boolean, timeofday)
- Row 3+: Actual data values

Example for PIE:
Category,Value
string,number
Product A,35
Product B,25
Product C,40

Example for LINE/COLUMN:
Month,Sales,Expenses
string,number,number
Jan,1000,800
Feb,1200,900

Example for COMBO (columns + lines):
Month,Sales,Average
string,number,number
Jan,1000,850
Feb,1200,900
(Note: In styling, specify which series is line vs column using "series" property)

Example with ANNOTATIONS (data labels on points):
Month,Sales,Annotation
string,number,string
Jan,1000,Peak
Feb,800,null
Mar,1200,Record


STEP 6: FORMAT YOUR RESPONSE

FORMAT YOUR RESPONSE EXACTLY AS FOLLOWS:
CHART_TYPE: [pie/line/bar/column/area/scatter/etc]
TITLE: [exact title text or "Untitled" if none]
CSV_DATA:
[csv data with headers, data types on row 2, then actual data]
STYLING:
[VALID JSON - see structure below]

STYLING JSON - INCLUDE ALL APPLICABLE PROPERTIES:

For PIE CHARTS:
{
  "title": "Exact Title From Image",
  "colors": ["#color1", "#color2", "#color3"],
  "legend": {"position": "bottom"},
  "pieSliceText": "percentage",
  "pieSliceTextStyle": {"fontSize": 12},
  "pieHole": 0,
  "is3D": false,
  "chartArea": {"width": "90%", "height": "80%"}
}

For BAR/COLUMN CHARTS:
{
  "title": "Exact Title From Image",
  "colors": ["#color1", "#color2"],
  "legend": {"position": "top"},
  "vAxis": {"title": "Y Axis Title", "gridlines": {"color": "#e0e0e0"}},
  "hAxis": {"title": "X Axis Title"},
  "isStacked": false,
  "chartArea": {"width": "70%", "height": "70%"}
}

For LINE/AREA CHARTS:
{
  "title": "Exact Title From Image",
  "colors": ["#color1", "#color2"],
  "legend": {"position": "right"},
  "vAxis": {"title": "Y Axis Title"},
  "hAxis": {"title": "X Axis Title"},
  "lineWidth": 2,
  "pointSize": 5,
  "chartArea": {"width": "80%", "height": "70%"}
}

For COMBO CHARTS (columns + lines together):
{
  "title": "Exact Title From Image",
  "colors": ["#color1", "#color2"],
  "legend": {"position": "bottom"},
  "seriesType": "bars",
  "series": {
    "1": {"type": "line", "lineWidth": 2, "pointSize": 4}
  },
  "vAxis": {"title": "Y Axis Title"},
  "hAxis": {"title": "X Axis Title"},
  "chartArea": {"width": "80%", "height": "70%"}
}

For BUBBLE CHARTS:
{
  "title": "Exact Title From Image",
  "colors": ["#color1"],
  "legend": {"position": "right"},
  "bubble": {"textStyle": {"fontSize": 11}},
  "vAxis": {"title": "Y Axis"},
  "hAxis": {"title": "X Axis"}
}

For GEO CHARTS:
{
  "title": "Exact Title From Image",
  "colorAxis": {"colors": ["#e0e0e0", "#0066cc"]},
  "region": "world"
}

For GAUGE CHARTS:
{
  "title": "Exact Title From Image",
  "redFrom": 90,
  "redTo": 100,
  "yellowFrom": 75,
  "yellowTo": 90,
  "greenFrom": 0,
  "greenTo": 75,
  "minorTicks": 5
}

For SCATTER CHARTS:
{
  "title": "Exact Title From Image",
  "colors": ["#color1"],
  "pointSize": 3,
  "vAxis": {"title": "Y Axis"},
  "hAxis": {"title": "X Axis"}
}

CRITICAL RULES:
1. CHART TYPE: If you see columns AND lines together, use "combo" not "column"!
2. DATA VALUES: Interpolate between gridlines, do not round. Must be accurate within 5-10%.
3. LEGEND POSITION: Check carefully - right/left/top/bottom?
4. COLORS: Extract in exact order, use hex codes
5. STYLING must be valid JSON with double quotes
6. For combo charts: Use "seriesType" and "series" object to specify types';

		$request_body = array(
			'contents' => array(
				array(
					'parts' => array(
						array( 'text' => $prompt ),
						array(
							'inline_data' => array(
								'mime_type' => 'image/jpeg',
								'data' => $base64_image,
							),
						),
					),
				),
			),
		);

		$response = wp_remote_post(
			'https://generativelanguage.googleapis.com/v1/models/gemini-1.5-flash:generateContent?key=' . $api_key,
			array(
				'headers' => array(
					'Content-Type' => 'application/json',
				),
				'body' => wp_json_encode( $request_body ),
				'timeout' => 60,
			)
		);

		if ( is_wp_error( $response ) ) {
			error_log( 'Visualizer AI: Gemini Vision HTTP Error: ' . $response->get_error_message() );
			return $response;
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		$response_body = wp_remote_retrieve_body( $response );

		error_log( 'Visualizer AI: Gemini Vision Response Code: ' . $response_code );

		$body = json_decode( $response_body, true );

		if ( isset( $body['error'] ) ) {
			error_log( 'Visualizer AI: Gemini Vision API Error: ' . $body['error']['message'] );
			return new WP_Error( 'api_error', $body['error']['message'] );
		}

		if ( ! isset( $body['candidates'][0]['content']['parts'][0]['text'] ) ) {
			return new WP_Error( 'invalid_response', esc_html__( 'Invalid response from Gemini Vision.', 'visualizer' ) );
		}

		$content = $body['candidates'][0]['content']['parts'][0]['text'];
		error_log( 'Visualizer AI: Gemini Vision Content: ' . substr( $content, 0, 500 ) );

		return $this->_parseImageAnalysisResponse( $content );
	}

	/**
	 * Analyzes chart image using Anthropic Claude Vision API.
	 *
	 * @since 3.12.0
	 *
	 * @access private
	 *
	 * @param string $image_data Base64 encoded image data.
	 *
	 * @return array<string, mixed>|WP_Error The analysis result or WP_Error on failure.
	 */
	private function _analyzeImageWithClaude( $image_data ) {
		error_log( 'Visualizer AI: Analyzing image with Claude Vision' );

		$api_key = get_option( 'visualizer_claude_api_key', '' );

		if ( empty( $api_key ) ) {
			return new WP_Error( 'no_api_key', esc_html__( 'Anthropic Claude API key is not configured.', 'visualizer' ) );
		}

		// Extract base64 data and media type from data URL
		$image_parts = explode( ',', $image_data );
		$base64_image = isset( $image_parts[1] ) ? $image_parts[1] : $image_data;

		// Detect media type from data URL
		$media_type = 'image/jpeg';
		if ( preg_match( '/data:(image\/[^;]+)/', $image_parts[0], $matches ) ) {
			$media_type = $matches[1];
		}

		$prompt = 'You are a data visualization expert helping to extract and recreate chart data. Analyze this chart image to extract all information needed to recreate it accurately.

Your task is to analyze the visual chart and provide structured data that can be used to recreate it. This is for data extraction and visualization purposes.

IMPORTANT: Pay careful attention to extracting accurate data values. Study the Y-axis scale and gridlines carefully. If a bar or line point falls between gridlines, INTERPOLATE the value - do not round to the nearest gridline. Example: If gridlines are at 10 and 20, and a bar reaches 60% between them, the value is 16.

STEP 1: IDENTIFY CHART TYPE
Examine the chart carefully to determine the correct type.

SUPPORTED CHART TYPES:
- tabular (table with rows and columns of data)
- pie (circular chart with slices, can be donut with hole in center)
- line (data points connected by lines)
- bar (horizontal bars)
- column (vertical bars/columns)
- area (filled area under line)
- scatter (individual data points, no connecting lines)
- bubble (scatter with varying point sizes)
- geo (geographic/map visualization)
- gauge (meter/speedometer style)
- candlestick (financial chart with open/high/low/close)
- timeline (horizontal timeline events)
- combo (CRITICAL: chart with MULTIPLE visualization types - e.g., columns AND lines together)
- radar (spider/radar chart)
- polarArea (polar area chart)

CRITICAL - COMBO CHART DETECTION:
If you see BOTH columns/bars AND lines in the SAME chart, this is a COMBO chart, NOT a column or line chart!
Example: Sales shown as columns + Average shown as a line = COMBO chart
Look for: Multiple data series displayed with different visual types (some as bars, some as lines)

STEP 2: VISUAL LAYOUT ANALYSIS

Look carefully at WHERE the legend is located (right/bottom/top/left/none) and extract the exact title text.

STEP 3: CHART-TYPE-SPECIFIC ANALYSIS

For PIE CHARTS:
- Extract colors for each slice in order
- Check if percentages or labels shown on slices
- Detect 2D vs 3D, donut style
- Note legend position

For COMBO CHARTS:
- CRITICAL: Identify which data series should be columns and which should be lines
- Set "seriesType": "bars" as default
- Use "series": {1: {"type": "line"}} to specify which series differ from default
- Example: First series columns, second series line

For BAR/COLUMN/LINE CHARTS:
- Extract colors for each data series
- Note axis titles and gridline visibility
- Check for data labels on bars or points

STEP 4: COLOR EXTRACTION
Extract colors in exact order. Use hex codes (e.g., #3366CC, #DC3912, #FF9900).

STEP 5: DATA EXTRACTION

Extract data values CAREFULLY by reading the Y-axis scale and gridlines. INTERPOLATE values between gridlines - do not round. Example: If gridlines are at 10 and 20, and a bar reaches 60% between them, use 16 not 10 or 20. Values should be accurate within 5-10% of visual appearance.

CSV DATA FORMAT (MANDATORY):
- Row 1: Column headers
- Row 2: Data types (string, number, date, datetime, boolean, timeofday)
- Row 3+: Actual data values

Example for PIE:
Category,Value
string,number
Product A,35
Product B,25
Product C,40

Example for LINE/COLUMN:
Month,Sales,Expenses
string,number,number
Jan,1000,800
Feb,1200,900

Example for COMBO (columns + lines):
Month,Sales,Average
string,number,number
Jan,1000,850
Feb,1200,900
(Note: In styling, specify which series is line vs column using "series" property)

Example with ANNOTATIONS (data labels on points):
Month,Sales,Annotation
string,number,string
Jan,1000,Peak
Feb,800,null
Mar,1200,Record


STEP 6: FORMAT YOUR RESPONSE

FORMAT YOUR RESPONSE EXACTLY AS FOLLOWS:
CHART_TYPE: [pie/line/bar/column/area/scatter/etc]
TITLE: [exact title text or "Untitled" if none]
CSV_DATA:
[csv data with headers, data types on row 2, then actual data]
STYLING:
[VALID JSON - see structure below]

STYLING JSON - INCLUDE ALL APPLICABLE PROPERTIES:

For PIE CHARTS:
{
  "title": "Exact Title From Image",
  "colors": ["#color1", "#color2", "#color3"],
  "legend": {"position": "bottom"},
  "pieSliceText": "percentage",
  "pieSliceTextStyle": {"fontSize": 12},
  "pieHole": 0,
  "is3D": false,
  "chartArea": {"width": "90%", "height": "80%"}
}

For BAR/COLUMN CHARTS:
{
  "title": "Exact Title From Image",
  "colors": ["#color1", "#color2"],
  "legend": {"position": "top"},
  "vAxis": {"title": "Y Axis Title", "gridlines": {"color": "#e0e0e0"}},
  "hAxis": {"title": "X Axis Title"},
  "isStacked": false,
  "chartArea": {"width": "70%", "height": "70%"}
}

For LINE/AREA CHARTS:
{
  "title": "Exact Title From Image",
  "colors": ["#color1", "#color2"],
  "legend": {"position": "right"},
  "vAxis": {"title": "Y Axis Title"},
  "hAxis": {"title": "X Axis Title"},
  "lineWidth": 2,
  "pointSize": 5,
  "chartArea": {"width": "80%", "height": "70%"}
}

For COMBO CHARTS (columns + lines together):
{
  "title": "Exact Title From Image",
  "colors": ["#color1", "#color2"],
  "legend": {"position": "bottom"},
  "seriesType": "bars",
  "series": {
    "1": {"type": "line", "lineWidth": 2, "pointSize": 4}
  },
  "vAxis": {"title": "Y Axis Title"},
  "hAxis": {"title": "X Axis Title"},
  "chartArea": {"width": "80%", "height": "70%"}
}

For BUBBLE CHARTS:
{
  "title": "Exact Title From Image",
  "colors": ["#color1"],
  "legend": {"position": "right"},
  "bubble": {"textStyle": {"fontSize": 11}},
  "vAxis": {"title": "Y Axis"},
  "hAxis": {"title": "X Axis"}
}

For GEO CHARTS:
{
  "title": "Exact Title From Image",
  "colorAxis": {"colors": ["#e0e0e0", "#0066cc"]},
  "region": "world"
}

For GAUGE CHARTS:
{
  "title": "Exact Title From Image",
  "redFrom": 90,
  "redTo": 100,
  "yellowFrom": 75,
  "yellowTo": 90,
  "greenFrom": 0,
  "greenTo": 75,
  "minorTicks": 5
}

For SCATTER CHARTS:
{
  "title": "Exact Title From Image",
  "colors": ["#color1"],
  "pointSize": 3,
  "vAxis": {"title": "Y Axis"},
  "hAxis": {"title": "X Axis"}
}

CRITICAL RULES:
1. CHART TYPE: If you see columns AND lines together, use "combo" not "column"!
2. DATA VALUES: Interpolate between gridlines, do not round. Must be accurate within 5-10%.
3. LEGEND POSITION: Check carefully - right/left/top/bottom?
4. COLORS: Extract in exact order, use hex codes
5. STYLING must be valid JSON with double quotes
6. For combo charts: Use "seriesType" and "series" object to specify types';

		$request_body = array(
			'model' => 'claude-3-5-sonnet-20241022',
			'max_tokens' => 2000,
			'messages' => array(
				array(
					'role' => 'user',
					'content' => array(
						array(
							'type' => 'image',
							'source' => array(
								'type' => 'base64',
								'media_type' => $media_type,
								'data' => $base64_image,
							),
						),
						array(
							'type' => 'text',
							'text' => $prompt,
						),
					),
				),
			),
		);

		$response = wp_remote_post(
			'https://api.anthropic.com/v1/messages',
			array(
				'headers' => array(
					'x-api-key' => $api_key,
					'anthropic-version' => '2023-06-01',
					'Content-Type' => 'application/json',
				),
				'body' => wp_json_encode( $request_body ),
				'timeout' => 60,
			)
		);

		if ( is_wp_error( $response ) ) {
			error_log( 'Visualizer AI: Claude Vision HTTP Error: ' . $response->get_error_message() );
			return $response;
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		$response_body = wp_remote_retrieve_body( $response );

		error_log( 'Visualizer AI: Claude Vision Response Code: ' . $response_code );

		$body = json_decode( $response_body, true );

		if ( isset( $body['error'] ) ) {
			error_log( 'Visualizer AI: Claude Vision API Error: ' . $body['error']['message'] );
			return new WP_Error( 'api_error', $body['error']['message'] );
		}

		if ( ! isset( $body['content'][0]['text'] ) ) {
			return new WP_Error( 'invalid_response', esc_html__( 'Invalid response from Claude Vision.', 'visualizer' ) );
		}

		$content = $body['content'][0]['text'];
		error_log( 'Visualizer AI: Claude Vision Content: ' . substr( $content, 0, 500 ) );

		return $this->_parseImageAnalysisResponse( $content );
	}

	/**
	 * Parses the image analysis response from AI.
	 *
	 * @since 3.12.0
	 *
	 * @access private
	 *
	 * @param string $text The AI response text.
	 *
	 * @return array<string, mixed> The parsed result with chart_type, title, csv_data, and styling.
	 */
	private function _parseImageAnalysisResponse( $text ) {
		error_log( 'Visualizer AI: Parsing image analysis response' );

		$result = array(
			'chart_type' => '',
			'title' => '',
			'csv_data' => '',
			'styling' => '{}',
		);

		// Extract chart type
		if ( preg_match( '/CHART_TYPE:\s*(.+)/i', $text, $matches ) ) {
			$chart_type = strtolower( trim( $matches[1] ) );
			// Map common variations to Visualizer chart types
			$type_map = array(
				'pie' => 'pie',
				'line' => 'line',
				'bar' => 'bar',
				'column' => 'column',
				'area' => 'area',
				'scatter' => 'scatter',
				'geo' => 'geo',
				'gauge' => 'gauge',
				'candlestick' => 'candlestick',
				'histogram' => 'histogram',
				'table' => 'table',
				'tabular' => 'tabular',
				'combo' => 'combo',
				'bubble' => 'bubble',
				'timeline' => 'timeline',
				'radar' => 'radar',
				'polararea' => 'polarArea',
				'polar area' => 'polarArea',
			);
			$result['chart_type'] = isset( $type_map[ $chart_type ] ) ? $type_map[ $chart_type ] : 'column';
		}

		// Extract title
		if ( preg_match( '/TITLE:\s*(.+)/i', $text, $matches ) ) {
			$result['title'] = trim( $matches[1] );
		}

		// Extract CSV data
		if ( preg_match( '/CSV_DATA:\s*\n(.*?)(?=\nSTYLING:|$)/si', $text, $matches ) ) {
			$csv_data = trim( $matches[1] );
			// Remove markdown code blocks if present
			$csv_data = preg_replace( '/^```[a-z]*\n/', '', $csv_data );
			$csv_data = preg_replace( '/\n```$/', '', $csv_data );
			$result['csv_data'] = trim( $csv_data );
		}

		// Extract styling JSON
		if ( preg_match( '/STYLING:\s*\n(.*?)$/si', $text, $matches ) ) {
			$styling_text = trim( $matches[1] );
			// Try to extract JSON from the text
			if ( preg_match( '/(\{.*\})/s', $styling_text, $json_matches ) ) {
				$potential_json = trim( $json_matches[1] );

				// Try to convert JavaScript object notation to valid JSON
				// Replace single quotes with double quotes (but not inside strings)
				$potential_json = preg_replace( "/'/", '"', $potential_json );

				// Try to add quotes around unquoted keys
				// This regex finds patterns like {key: or ,key: and converts to {"key":
				$potential_json = preg_replace( '/(\{|,)\s*([a-zA-Z_][a-zA-Z0-9_]*)\s*:/', '$1"$2":', $potential_json );

				// Validate it's proper JSON
				json_decode( $potential_json );
				if ( json_last_error() === JSON_ERROR_NONE ) {
					$result['styling'] = $potential_json;
					error_log( 'Visualizer AI: Valid styling JSON extracted' );
				} else {
					error_log( 'Visualizer AI: Invalid styling JSON, using empty object. Error: ' . json_last_error_msg() );
					$result['styling'] = '{}';
				}
			}
		}

		error_log( 'Visualizer AI: Parsed chart type: ' . $result['chart_type'] );
		error_log( 'Visualizer AI: Parsed title: ' . $result['title'] );
		error_log( 'Visualizer AI: CSV data length: ' . strlen( $result['csv_data'] ) );
		error_log( 'Visualizer AI: Styling: ' . substr( $result['styling'], 0, 200 ) );

		return $result;
	}
}
