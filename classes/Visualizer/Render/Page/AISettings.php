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
 * Renders AI settings page for API keys.
 *
 * @category Visualizer
 * @package Render
 * @subpackage Page
 *
 * @since 3.12.0
 */
class Visualizer_Render_Page_AISettings extends Visualizer_Render_Page {

	/**
	 * Masks an API key to show only first and last few characters.
	 *
	 * @since 3.12.0
	 *
	 * @access private
	 * @param string $key The API key to mask.
	 * @return string The masked API key.
	 */
	private function _maskAPIKey( $key ) {
		if ( empty( $key ) ) {
			return '';
		}

		$length = strlen( $key );
		if ( $length <= 12 ) {
			// For short keys, show first 4 and mask the rest
			return substr( $key, 0, 4 ) . str_repeat( '*', $length - 4 );
		}

		// For longer keys, show first 6 and last 4 characters
		return substr( $key, 0, 6 ) . str_repeat( '*', $length - 10 ) . substr( $key, -4 );
	}

	/**
	 * Renders page content.
	 *
	 * @since 3.12.0
	 *
	 * @access protected
	 * @return void
	 */
	protected function _renderContent() {
		echo '<div class="wrap">';
		echo '<h1>' . esc_html__( 'Visualizer AI Settings', 'visualizer' ) . '</h1>';

		// Check if PRO features are locked
		$is_locked = ! Visualizer_Module_Admin::proFeaturesLocked();

		if ( $is_locked ) {
			// Show locked state with upgrade message
			echo '<div style="position: relative; min-height: 400px;">';
			echo '<div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(255,255,255,0.95); z-index: 10; display: flex; align-items: center; justify-content: center;">';
			echo '<div style="text-align: center; padding: 40px; max-width: 600px;">';
			echo '<span class="dashicons dashicons-lock" style="font-size: 64px; color: #999; margin-bottom: 20px; display: block;"></span>';
			echo '<h2 style="margin: 20px 0; color: #333;">' . esc_html__( 'AI Features - Premium Feature', 'visualizer' ) . '</h2>';
			echo '<p style="margin: 20px 0; color: #666; font-size: 16px; line-height: 1.6;">' . esc_html__( 'AI-powered chart creation and configuration is available exclusively in Visualizer PRO. Upgrade now to unlock:', 'visualizer' ) . '</p>';
			echo '<ul style="text-align: left; display: inline-block; margin: 20px 0; color: #666;">';
			echo '<li style="margin: 10px 0;">✓ ' . esc_html__( 'AI Chart Configuration Assistant', 'visualizer' ) . '</li>';
			echo '<li style="margin: 10px 0;">✓ ' . esc_html__( 'Create Charts from Images', 'visualizer' ) . '</li>';
			echo '<li style="margin: 10px 0;">✓ ' . esc_html__( 'Natural Language Chart Customization', 'visualizer' ) . '</li>';
			echo '<li style="margin: 10px 0;">✓ ' . esc_html__( 'Support for ChatGPT, Gemini & Claude', 'visualizer' ) . '</li>';
			echo '</ul>';
			echo '<a href="' . tsdk_utmify( Visualizer_Plugin::PRO_TEASER_URL, 'aisettings', 'upgrade' ) . '" target="_blank" class="button button-primary" style="margin-top: 20px; padding: 10px 30px; height: auto; font-size: 16px;">';
			echo esc_html__( 'Upgrade to PRO', 'visualizer' );
			echo '</a>';
			echo '</div>';
			echo '</div>';
		}

		// Wrap the form in a div that will be overlaid if locked
		echo '<div style="' . ( $is_locked ? 'opacity: 0.5; pointer-events: none;' : '' ) . '">';

		// Check if form was submitted
		if ( ! $is_locked && isset( $_POST['visualizer_ai_settings_nonce'] ) && wp_verify_nonce( $_POST['visualizer_ai_settings_nonce'], 'visualizer_ai_settings' ) ) {
			$this->_saveSettings();

			// Display validation results
			$validation_results = get_transient( 'visualizer_ai_validation_results' );
			if ( $validation_results ) {
				delete_transient( 'visualizer_ai_validation_results' );

				foreach ( $validation_results as $provider => $result ) {
					$notice_class = 'notice-success';
					if ( $result['status'] === 'error' ) {
						$notice_class = 'notice-error';
					} elseif ( $result['status'] === 'removed' ) {
						$notice_class = 'notice-info';
					}

					echo '<div class="notice ' . esc_attr( $notice_class ) . ' is-dismissible"><p>' . esc_html( $result['message'] ) . '</p></div>';
				}
			} else {
				echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Settings saved successfully.', 'visualizer' ) . '</p></div>';
			}
		}

		// Get saved API keys
		$openai_key = get_option( 'visualizer_openai_api_key', '' );
		$gemini_key = get_option( 'visualizer_gemini_api_key', '' );
		$claude_key = get_option( 'visualizer_claude_api_key', '' );

		// Mask the keys for display
		$openai_key_display = $this->_maskAPIKey( $openai_key );
		$gemini_key_display = $this->_maskAPIKey( $gemini_key );
		$claude_key_display = $this->_maskAPIKey( $claude_key );

		echo '<form method="post" action="">';
		wp_nonce_field( 'visualizer_ai_settings', 'visualizer_ai_settings_nonce' );

		echo '<table class="form-table">';

		// OpenAI API Key
		echo '<tr>';
		echo '<th scope="row"><label for="visualizer_openai_api_key">' . esc_html__( 'OpenAI API Key (ChatGPT)', 'visualizer' ) . '</label></th>';
		echo '<td>';
		echo '<input type="text" id="visualizer_openai_api_key" name="visualizer_openai_api_key" value="' . esc_attr( $openai_key_display ) . '" class="regular-text" placeholder="' . esc_attr__( 'Enter API key', 'visualizer' ) . '" autocomplete="off" />';
		echo '<p class="description">' . esc_html__( 'Enter your OpenAI API key to enable ChatGPT integration.', 'visualizer' ) . ' <a href="https://platform.openai.com/api-keys" target="_blank">' . esc_html__( 'Get API Key', 'visualizer' ) . '</a></p>';
		echo '</td>';
		echo '</tr>';

		// Gemini API Key
		echo '<tr>';
		echo '<th scope="row"><label for="visualizer_gemini_api_key">' . esc_html__( 'Google Gemini API Key', 'visualizer' ) . '</label></th>';
		echo '<td>';
		echo '<input type="text" id="visualizer_gemini_api_key" name="visualizer_gemini_api_key" value="' . esc_attr( $gemini_key_display ) . '" class="regular-text" placeholder="' . esc_attr__( 'Enter API key', 'visualizer' ) . '" autocomplete="off" />';
		echo '<p class="description">' . esc_html__( 'Enter your Google Gemini API key.', 'visualizer' ) . ' <a href="https://makersuite.google.com/app/apikey" target="_blank">' . esc_html__( 'Get API Key', 'visualizer' ) . '</a></p>';
		echo '</td>';
		echo '</tr>';

		// Claude API Key
		echo '<tr>';
		echo '<th scope="row"><label for="visualizer_claude_api_key">' . esc_html__( 'Anthropic Claude API Key', 'visualizer' ) . '</label></th>';
		echo '<td>';
		echo '<input type="text" id="visualizer_claude_api_key" name="visualizer_claude_api_key" value="' . esc_attr( $claude_key_display ) . '" class="regular-text" placeholder="' . esc_attr__( 'Enter API key', 'visualizer' ) . '" autocomplete="off" />';
		echo '<p class="description">' . esc_html__( 'Enter your Anthropic Claude API key.', 'visualizer' ) . ' <a href="https://console.anthropic.com/account/keys" target="_blank">' . esc_html__( 'Get API Key', 'visualizer' ) . '</a></p>';
		echo '</td>';
		echo '</tr>';

		echo '</table>';

		echo '<p class="submit">';
		echo '<input type="submit" name="submit" id="submit" class="button button-primary" value="' . esc_attr__( 'Save Settings', 'visualizer' ) . '">';
		echo '</p>';

		echo '</form>';

		echo '</div>'; // End opacity wrapper

		if ( $is_locked ) {
			echo '</div>'; // End position relative wrapper
		}

		echo '</div>'; // End wrap
	}

	/**
	 * Saves AI settings.
	 *
	 * @since 3.12.0
	 *
	 * @access private
	 * @return void
	 */
	private function _saveSettings() {
		// Get current keys
		$current_openai = get_option( 'visualizer_openai_api_key', '' );
		$current_gemini = get_option( 'visualizer_gemini_api_key', '' );
		$current_claude = get_option( 'visualizer_claude_api_key', '' );

		$validation_results = array();

		// Handle OpenAI key
		if ( isset( $_POST['visualizer_openai_api_key'] ) ) {
			$new_key = sanitize_text_field( $_POST['visualizer_openai_api_key'] );

			// Allow empty value to remove key - but only show notification if key existed
			if ( empty( $new_key ) ) {
				if ( ! empty( $current_openai ) ) {
					delete_option( 'visualizer_openai_api_key' );
					$validation_results['openai'] = array( 'status' => 'removed', 'message' => __( 'OpenAI API key removed.', 'visualizer' ) );
				}
			} elseif ( $new_key !== $this->_maskAPIKey( $current_openai ) ) {
				// Validate new key before saving
				$validation = $this->_validateAPIKey( 'openai', $new_key );
				if ( $validation['valid'] ) {
					update_option( 'visualizer_openai_api_key', $new_key );
					$validation_results['openai'] = array( 'status' => 'success', 'message' => __( 'OpenAI API key saved and validated successfully.', 'visualizer' ) );
				} else {
					$validation_results['openai'] = array( 'status' => 'error', 'message' => sprintf( __( 'OpenAI API key validation failed: %s', 'visualizer' ), $validation['error'] ) );
				}
			}
		}

		// Handle Gemini key
		if ( isset( $_POST['visualizer_gemini_api_key'] ) ) {
			$new_key = sanitize_text_field( $_POST['visualizer_gemini_api_key'] );

			// Allow empty value to remove key - but only show notification if key existed
			if ( empty( $new_key ) ) {
				if ( ! empty( $current_gemini ) ) {
					delete_option( 'visualizer_gemini_api_key' );
					$validation_results['gemini'] = array( 'status' => 'removed', 'message' => __( 'Gemini API key removed.', 'visualizer' ) );
				}
			} elseif ( $new_key !== $this->_maskAPIKey( $current_gemini ) ) {
				// Validate new key before saving
				$validation = $this->_validateAPIKey( 'gemini', $new_key );
				if ( $validation['valid'] ) {
					update_option( 'visualizer_gemini_api_key', $new_key );
					$validation_results['gemini'] = array( 'status' => 'success', 'message' => __( 'Gemini API key saved and validated successfully.', 'visualizer' ) );
				} else {
					$validation_results['gemini'] = array( 'status' => 'error', 'message' => sprintf( __( 'Gemini API key validation failed: %s', 'visualizer' ), $validation['error'] ) );
				}
			}
		}

		// Handle Claude key
		if ( isset( $_POST['visualizer_claude_api_key'] ) ) {
			$new_key = sanitize_text_field( $_POST['visualizer_claude_api_key'] );

			// Allow empty value to remove key - but only show notification if key existed
			if ( empty( $new_key ) ) {
				if ( ! empty( $current_claude ) ) {
					delete_option( 'visualizer_claude_api_key' );
					$validation_results['claude'] = array( 'status' => 'removed', 'message' => __( 'Claude API key removed.', 'visualizer' ) );
				}
			} elseif ( $new_key !== $this->_maskAPIKey( $current_claude ) ) {
				// Validate new key before saving
				$validation = $this->_validateAPIKey( 'claude', $new_key );
				if ( $validation['valid'] ) {
					update_option( 'visualizer_claude_api_key', $new_key );
					$validation_results['claude'] = array( 'status' => 'success', 'message' => __( 'Claude API key saved and validated successfully.', 'visualizer' ) );
				} else {
					$validation_results['claude'] = array( 'status' => 'error', 'message' => sprintf( __( 'Claude API key validation failed: %s', 'visualizer' ), $validation['error'] ) );
				}
			}
		}

		// Store validation results for display
		set_transient( 'visualizer_ai_validation_results', $validation_results, 60 );
	}

	/**
	 * Validates an API key by making a test request.
	 *
	 * @since 3.12.0
	 *
	 * @access private
	 * @param string $provider The API provider ('openai', 'gemini', 'claude').
	 * @param string $api_key The API key to validate.
	 * @return array{valid: bool, error?: string} Validation result.
	 */
	private function _validateAPIKey( $provider, $api_key ) {
		switch ( $provider ) {
			case 'openai':
				return $this->_validateOpenAIKey( $api_key );
			case 'gemini':
				return $this->_validateGeminiKey( $api_key );
			case 'claude':
				return $this->_validateClaudeKey( $api_key );
			default:
				return array( 'valid' => false, 'error' => 'Invalid provider' );
		}
	}

	/**
	 * Validates OpenAI API key.
	 *
	 * @since 3.12.0
	 *
	 * @access private
	 * @param string $api_key The API key to validate.
	 * @return array{valid: bool, error?: string} Validation result.
	 */
	private function _validateOpenAIKey( $api_key ) {
		$response = wp_remote_post(
			'https://api.openai.com/v1/chat/completions',
			array(
				'timeout' => 10,
				'headers' => array(
					'Authorization' => 'Bearer ' . $api_key,
					'Content-Type'  => 'application/json',
				),
				'body' => wp_json_encode(
					array(
						'model'    => 'gpt-3.5-turbo',
						'messages' => array(
							array(
								'role'    => 'user',
								'content' => 'Hi',
							),
						),
						'max_tokens' => 5,
					)
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return array( 'valid' => false, 'error' => $response->get_error_message() );
		}

		$code = wp_remote_retrieve_response_code( $response );
		if ( $code !== 200 ) {
			$body = json_decode( wp_remote_retrieve_body( $response ), true );
			$error = isset( $body['error']['message'] ) ? $body['error']['message'] : 'Invalid API key';
			return array( 'valid' => false, 'error' => $error );
		}

		return array( 'valid' => true );
	}

	/**
	 * Validates Gemini API key.
	 *
	 * @since 3.12.0
	 *
	 * @access private
	 * @param string $api_key The API key to validate.
	 * @return array{valid: bool, error?: string} Validation result.
	 */
	private function _validateGeminiKey( $api_key ) {
		$response = wp_remote_post(
			'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=' . $api_key,
			array(
				'timeout' => 10,
				'headers' => array(
					'Content-Type' => 'application/json',
				),
				'body' => wp_json_encode(
					array(
						'contents' => array(
							array(
								'parts' => array(
									array(
										'text' => 'Hi',
									),
								),
							),
						),
					)
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return array( 'valid' => false, 'error' => $response->get_error_message() );
		}

		$code = wp_remote_retrieve_response_code( $response );
		if ( $code !== 200 ) {
			$body = json_decode( wp_remote_retrieve_body( $response ), true );
			$error = isset( $body['error']['message'] ) ? $body['error']['message'] : 'Invalid API key';
			return array( 'valid' => false, 'error' => $error );
		}

		return array( 'valid' => true );
	}

	/**
	 * Validates Claude API key.
	 *
	 * @since 3.12.0
	 *
	 * @access private
	 * @param string $api_key The API key to validate.
	 * @return array{valid: bool, error?: string} Validation result.
	 */
	private function _validateClaudeKey( $api_key ) {
		$response = wp_remote_post(
			'https://api.anthropic.com/v1/messages',
			array(
				'timeout' => 10,
				'headers' => array(
					'x-api-key'         => $api_key,
					'anthropic-version' => '2023-06-01',
					'Content-Type'      => 'application/json',
				),
				'body' => wp_json_encode(
					array(
						'model'      => 'claude-3-haiku-20240307',
						'max_tokens' => 10,
						'messages'   => array(
							array(
								'role'    => 'user',
								'content' => 'Hi',
							),
						),
					)
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return array( 'valid' => false, 'error' => $response->get_error_message() );
		}

		$code = wp_remote_retrieve_response_code( $response );
		if ( $code !== 200 ) {
			$body = json_decode( wp_remote_retrieve_body( $response ), true );
			$error = isset( $body['error']['message'] ) ? $body['error']['message'] : 'Invalid API key';
			return array( 'valid' => false, 'error' => $error );
		}

		return array( 'valid' => true );
	}

}
