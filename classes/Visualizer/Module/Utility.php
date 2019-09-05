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
	 * Some default distinct colors.
	 *
	 * @since 3.3.0
	 *
	 * @access private
	 * @var _CHART_COLORS
	 */
	private static $_CHART_COLORS = array(
		'#e6194b', '#3cb44b', '#ffe119', '#4363d8', '#f58231', '#911eb4', '#46f0f0', '#f032e6', '#bcf60c', '#fabebe', '#008080', '#e6beff', '#9a6324', '#fffac8', '#800000', '#aaffc3', '#808000', '#ffd8b1', '#000075', '#808080',
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
		if ( strpos( $color, '#' ) === 0 ) {
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
	 * Gets a random color from the array of chart colors and returns it as well as its transparent equivalent.
	 *
	 * @since 3.3.0
	 *
	 * @access private
	 */
	private static function get_random_color() {
		$color = self::$_CHART_COLORS[ rand( 0, count( self::$_CHART_COLORS ) - 1 ) ];
		return array( self::hex2rgba( $color, 0.5 ), $color );
	}

	/**
	 * Sets some defaults in the chart.
	 *
	 * @since 3.3.0
	 *
	 * @access public
	 */
	public static function set_defaults( $chart, $post_status = 'auto-draft' ) {
		$type           = get_post_meta( $chart->ID, Visualizer_Plugin::CF_CHART_TYPE, true );
		$library        = get_post_meta( $chart->ID, Visualizer_Plugin::CF_CHART_LIBRARY, true );

		// if post_status is null, operate on the chart irrespective of the post_status
		if ( ( ! is_null( $post_status ) && $chart->post_status !== $post_status ) ) {
			return;
		}

		$series         = get_post_meta( $chart->ID, Visualizer_Plugin::CF_SERIES, true );
		if ( ! $series || ! is_array( $series ) ) {
			return;
		}

		switch ( $library ) {
			case 'ChartJS':
				self::set_defaults_chartjs( $chart, $post_status );
				break;
			case 'GoogleCharts':
				self::set_defaults_google( $chart, $post_status );
				break;
		}
	}

	/**
	 * Sets some defaults in the chart for Google charts.
	 *
	 * @since 3.3.0
	 *
	 * @access private
	 */
	private static function set_defaults_google( $chart, $post_status ) {
		$type           = get_post_meta( $chart->ID, Visualizer_Plugin::CF_CHART_TYPE, true );
		$series         = get_post_meta( $chart->ID, Visualizer_Plugin::CF_SERIES, true );

		$attributes     = array();
		if ( $post_status === 'auto-draft' ) {
			switch ( $type ) {
				case 'combo':
					// chart type 'bars' and randomly choose the type of each series.
					$types = array( 'area', 'line', 'steppedArea', 'bar' );
					$attributes['seriesType'] = 'bars';
					for ( $x = 0; $x < count( $series ); $x++ ) {
						$attributes['series'][ $x ]['type'] = $types[ rand( 0, count( $types ) - 1 ) ];
					}
					break;
				case 'candlestick':
					// add stroke and fill color so that behavior is consistent.
					$attributes['candlestick']['fallingColor']['stroke'] = '#3366cc';
					$attributes['candlestick']['fallingColor']['fill'] = '#fff';
					$attributes['candlestick']['risingColor']['stroke'] = '#3366cc';
					$attributes['candlestick']['risingColor']['fill'] = '#3366cc';
					break;
			}
		}

		if ( $attributes ) {
			$settings       = get_post_meta( $chart->ID, Visualizer_Plugin::CF_SETTINGS, true );
			update_post_meta( $chart->ID, Visualizer_Plugin::CF_SETTINGS, array_merge( $settings, $attributes ) );
		}
	}

	/**
	 * Sets some defaults in the chart for ChartJS charts. Only during first creation.
	 *
	 * @since 3.3.0
	 *
	 * @access private
	 */
	private static function set_defaults_chartjs( $chart, $post_status ) {
		if ( $post_status !== 'auto-draft' ) {
			return;
		}

		$type = get_post_meta( $chart->ID, Visualizer_Plugin::CF_CHART_TYPE, true );
		$series = get_post_meta( $chart->ID, Visualizer_Plugin::CF_SERIES, true );
		$settings = get_post_meta( $chart->ID, Visualizer_Plugin::CF_SETTINGS, true );

		$attributes = array();
		$name   = 'series';
		$count  = count( $series );
		$max    = $count - 1;
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
					$colors = self::get_random_color();
					$attributes[] = array( 'backgroundColor' => $colors[0], 'hoverBackgroundColor' => $colors[1] );
				}
				break;
			case 'radar':
				// fall through.
			case 'line':
				// fall through.
			case 'area':
				for ( $i = 0; $i < $max; $i++ ) {
					$colors = self::get_random_color();
					$attributes[] = array( 'borderColor' => $colors[0] );
				}
				break;
		}

		if ( $post_status === 'auto-draft' ) {
			// the charts are huge in size so let's always get them down to 50%.
			$settings['width'] = $settings['height'] = '50%';
		}

		if ( $attributes ) {
			$settings[ $name ] = $attributes;
			update_post_meta( $chart->ID, Visualizer_Plugin::CF_SETTINGS, $settings );
		}
	}
}
