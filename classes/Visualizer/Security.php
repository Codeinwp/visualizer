<?php

class Visualizer_Security {

	private static function _getSalt() {
		return array(
			'__ip'     => @$_SERVER['REMOTE_ADDR'],
			'__agent'  => urlencode( @$_SERVER['HTTP_USER_AGENT'] ),
			'__userid' => get_current_user_id(),
		);
	}

	public static function createNonce() {
		return wp_create_nonce( implode( '/', array_slice( explode( '/', home_url() ), 0, 3 ) ) . add_query_arg( self::_getSalt() ) );
	}

	public static function verifyNonce( $nonce ) {
		return wp_verify_nonce( $nonce, add_query_arg( self::_getSalt(), $_SERVER['HTTP_REFERER'] ) );
	}

}