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
 * Utilities module class.
 *
 * @category Visualizer
 * @package Module
 *
 * @since 3.3.0
 */
class Visualizer_Module_Utility extends Visualizer_Module {

	const NAME = __CLASS__;

	/**
	 * Some default chart colors.
	 *
	 * @since 3.3.0
	 *
	 * @access private
	 * @var _CHART_COLORS
	 */
	private static $_CHART_COLORS = array(
		'#3366CC', '#DC3912', '#FF9900', '#109618', '#990099', '#3B3EAC', '#0099C6', '#DD4477', '#66AA00', '#B82E2E', '#316395', '#994499', '#22AA99', '#AAAA11', '#6633CC', '#E67300', '#8B0707', '#329262', '#5574A6', '#3B3EAC',
	);


	/**
	 * Constructor.
	 *
	 * @since 3.3.0
	 *
	 * @access public
	 *
	 * @param Visualizer_Plugin $plugin The instance of the plugin.
	 */
	public function __construct( Visualizer_Plugin $plugin ) {
		parent::__construct( $plugin );
	}


	/**
	 * Convert hexdec color string to rgb(a) string.
	 *
	 * Props to https://mekshq.com/how-to-convert-hexadecimal-color-code-to-rgb-or-rgba-using-php/
	 *
	 * @since 3.3.0
	 *
	 * @access private
	 */
	private static function hex2rgba( $color, $opacity = false ) {
		$default = 'rgb(0,0,0)';

		// Return default if no color provided
		if ( empty( $color ) ) {
			return $default;
		}

		// Sanitize $color if "#" is provided
		if ( $color[0] !== '#' ) {
			$color = substr( $color, 1 );
		}

			// Check if color has 6 or 3 characters and get values
		if ( strlen( $color ) === 6 ) {
			$hex = array( $color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5] );
		} elseif ( strlen( $color ) === 3 ) {
			$hex = array( $color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2] );
		} else {
			return $default;
		}

		// Convert hexadec to rgb
		$rgb = array_map( 'hexdec', $hex );

		// Check if opacity is set(rgba or rgb)
		if ( $opacity ) {
			if ( abs( $opacity ) > 1 ) {
				$opacity = 1.0;
			}
			$output = 'rgba(' . implode( ',', $rgb ) . ',' . $opacity . ')';
		} else {
			$output = 'rgb(' . implode( ',', $rgb ) . ')';
		}

		// Return rgb(a) color string
		return $output;
	}

	/**
	 * Gets a random color from the array of chart colors.
	 *
	 * @since 3.3.0
	 *
	 * @access private
	 */
	private static function get_random_color() {
		return self::hex2rgba( self::$_CHART_COLORS[ rand( 0, count( self::$_CHART_COLORS ) - 1 ) ], 0.5 );
	}

	/**
	 * Sets some defaults (colors etc.) in the chart.
	 * Currently only for ChartJS.
	 *
	 * @since 3.3.0
	 *
	 * @access public
	 */
	public static function set_defaults( $chart, $type, $library ) {
		if ( $chart->post_status !== 'auto-draft' || $library !== 'ChartJS' ) {
			return;
		}

		$series = get_post_meta( $chart->ID, Visualizer_Plugin::CF_SERIES, true );
		$settings = get_post_meta( $chart->ID, Visualizer_Plugin::CF_SETTINGS, true );
		if ( ! $series || ! is_array( $series ) ) {
			return;
		}

		$name   = 'series';
		$count  = count( $series );
		$max    = $count - 1;
		$colors = array();
		switch ( $type ) {
			case 'polarArea':
				// fall through.
			case 'pie':
				$data   = unserialize( $chart->post_content );
				$name   = 'slices';
				$max    = count( $data );
				// fall through.
			case 'column':
				// fall through.
			case 'bar':
				for ( $i = 0; $i < $max; $i++ ) {
					$colors[]['backgroundColor'] = self::get_random_color();
				}
				break;
			case 'radar':
				// fall through.
			case 'line':
				// fall through.
			case 'area':
				for ( $i = 0; $i < $max; $i++ ) {
					$colors[]['borderColor'] = self::get_random_color();
				}
				break;
		}
		if ( $colors ) {
			$settings[ $name ] = $colors;
			update_post_meta( $chart->ID, Visualizer_Plugin::CF_SETTINGS, $settings );
		}
		error_log( 'aya' );
	}

}
