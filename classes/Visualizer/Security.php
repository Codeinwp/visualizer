<?php

class Visualizer_Security {

	/**
	 * Returns nonce salt.
	 *
	 * @since 1.0.0
	 *
	 * @static
	 * @access private
	 * @return array
	 */
	private static function _getSalt() {
		return array(
			'__ip'     => @$_SERVER['REMOTE_ADDR'],
			'__agent'  => urlencode( @$_SERVER['HTTP_USER_AGENT'] ),
			'__userid' => get_current_user_id(),
		);
	}

	/**
	 * Creates nonce.
	 *
	 * @since 1.0.0
	 *
	 * @static
	 * @access public
	 * @return string
	 */
	public static function createNonce() {
		return wp_create_nonce( implode( '/', array_slice( explode( '/', home_url() ), 0, 3 ) ) . add_query_arg( self::_getSalt() ) );
	}

	/**
	 * Returns TRUE if nonce correct. Otherwise FALSE.
	 *
	 * @since 1.0.0
	 *
	 * @static
	 * @access public
	 * @param string $nonce The nonce to verify.
	 * @return boolean TRUE if nonce is correct. Otherwise FALSE.
	 */
	public static function verifyNonce( $nonce ) {
		return wp_verify_nonce( $nonce, add_query_arg( self::_getSalt(), $_SERVER['HTTP_REFERER'] ) );
	}

}