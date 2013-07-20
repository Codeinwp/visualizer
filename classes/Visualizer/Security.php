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
 * Security helper class responsible for creation and verification nonce values.
 *
 * @category Visualizer
 * @package Security
 *
 * @since 1.0.0
 */
class Visualizer_Security {

	/**
	 * Returns nonce salt.
	 *
	 * @since 1.0.0
	 *
	 * @static
	 * @access private
	 * @param string $action The action what requires the nonce.
	 * @return array
	 */
	private static function _getSalt( $action = '' ) {
		return array(
			'__ip'     => @$_SERVER['REMOTE_ADDR'],
			'__agent'  => urlencode( @$_SERVER['HTTP_USER_AGENT'] ),
			'__userid' => get_current_user_id(),
			'__action' => $action,
		);
	}

	/**
	 * Creates nonce.
	 *
	 * @since 1.0.0
	 *
	 * @static
	 * @access public
	 * @param string $action The action what requires the nonce.
	 * @return string
	 */
	public static function createNonce( $action = '' ) {
		return wp_create_nonce( implode( '/', array_slice( explode( '/', home_url() ), 0, 3 ) ) . add_query_arg( self::_getSalt( $action ) ) );
	}

	/**
	 * Returns TRUE if nonce correct. Otherwise FALSE.
	 *
	 * @since 1.0.0
	 *
	 * @static
	 * @access public
	 * @param string $nonce The nonce to verify.
	 * @param string $action The action what requires the nonce.
	 * @return boolean TRUE if nonce is correct. Otherwise FALSE.
	 */
	public static function verifyNonce( $nonce, $action = '' ) {
		return wp_verify_nonce( $nonce, add_query_arg( self::_getSalt( $action ), $_SERVER['HTTP_REFERER'] ) );
	}

}