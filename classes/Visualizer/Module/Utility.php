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
	 * @var string[]
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
		$this->_addFilter( Visualizer_Plugin::FILTER_GET_CHART_SETTINGS, 'apply_global_style_settings', 999, 3 );
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
	 * Returns a color at a specific index from the palette, with its transparent equivalent.
	 *
	 * @return array{string, string}
	 * @access private
	 */
	private static function get_color_at( int $index ): array {
		$colors = self::get_color_palette();
		$color  = $colors[ $index % count( $colors ) ];
		return array( self::hex2rgba( $color, 0.5 ), $color );
	}

	/**
	 * Mixes a hex color toward white by the given factor (0 = original, 1 = white).
	 *
	 * @access private
	 */
	private static function tint_color( string $hex, float $factor ): string {
		$hex = ltrim( $hex, '#' );
		if ( strlen( $hex ) === 3 ) {
			$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
		}
		$r = (int) round( hexdec( substr( $hex, 0, 2 ) ) + ( 255 - hexdec( substr( $hex, 0, 2 ) ) ) * $factor );
		$g = (int) round( hexdec( substr( $hex, 2, 2 ) ) + ( 255 - hexdec( substr( $hex, 2, 2 ) ) ) * $factor );
		$b = (int) round( hexdec( substr( $hex, 4, 2 ) ) + ( 255 - hexdec( substr( $hex, 4, 2 ) ) ) * $factor );
		return sprintf( '#%02x%02x%02x', $r, $g, $b );
	}

	/**
	 * Returns the color palette to use for charts.
	 *
	 * When global primary/secondary colors are configured, generates a palette of
	 * alternating tints: [primary, secondary, primary@25%, secondary@25%, ...].
	 * Falls back to the built-in colors otherwise.
	 *
	 * @return string[]
	 * @access private
	 */
	private static function get_color_palette(): array {
		$global  = self::get_global_style_defaults();
		$primary   = $global['color_primary'];
		$secondary = $global['color_secondary'];

		if ( empty( $primary ) && empty( $secondary ) ) {
			return self::$_CHART_COLORS;
		}

		$bases   = array_filter( array( $primary, $secondary ) );
		$factors = array( 0, 0.25, 0.5, 0.75 );
		$palette = array();

		foreach ( $factors as $factor ) {
			foreach ( $bases as $base ) {
				$palette[] = $factor === 0 ? $base : self::tint_color( $base, $factor );
			}
		}

		return $palette;
	}

	/**
	 * Returns the global style defaults stored in the plugin settings.
	 *
	 * @return array<string, string>
	 * @access public
	 * @static
	 */
	public static function get_global_style_defaults(): array {
		$option = get_option( Visualizer_Module_Admin::OPTION_GLOBAL_SETTINGS, array() );
		return wp_parse_args(
			$option,
			array(
				'color_primary'   => '',
				'color_secondary' => '',
				'apply_existing'  => '0',
			)
		);
	}

	/**
	 * Applies global styles to existing charts at render time.
	 *
	 * @param array<string, mixed>|mixed $settings Chart settings.
	 * @param int                        $chart_id Chart ID.
	 * @param string                     $type     Chart type.
	 * @return array<string, mixed>
	 * @access public
	 */
	public function apply_global_style_settings( $settings, $chart_id, $type ): array {
		$settings = is_array( $settings ) ? $settings : array();
		$global   = self::get_global_style_defaults();

		if ( empty( $global['apply_existing'] ) ) {
			return $settings;
		}

		if ( empty( $global['color_primary'] ) && empty( $global['color_secondary'] ) ) {
			return $settings;
		}

		if ( empty( $chart_id ) ) {
			return $settings;
		}

		$library = get_post_meta( $chart_id, Visualizer_Plugin::CF_CHART_LIBRARY, true );
		$library = is_string( $library ) ? strtolower( $library ) : '';
		if ( empty( $type ) ) {
			$type = get_post_meta( $chart_id, Visualizer_Plugin::CF_CHART_TYPE, true );
		}
		$type = is_string( $type ) ? strtolower( $type ) : '';

		if ( empty( $library ) ) {
			$library = in_array( $type, array( 'datatable', 'tabular' ), true ) ? 'datatable' : 'googlecharts';
		}

		if ( 'googlecharts' === $library || 'google' === $library ) {
			if ( 'geo' !== $type ) {
				$has_explicit = false;
				if ( ! empty( $settings['colors'] ) ) {
					$has_explicit = true;
				}
				if ( ! $has_explicit && isset( $settings['series'] ) && is_array( $settings['series'] ) ) {
					foreach ( $settings['series'] as $series_settings ) {
						if ( ! empty( $series_settings['color'] ) ) {
							$has_explicit = true;
							break;
						}
					}
				}
				if ( ! $has_explicit && isset( $settings['slices'] ) && is_array( $settings['slices'] ) ) {
					foreach ( $settings['slices'] as $slice_settings ) {
						if ( ! empty( $slice_settings['color'] ) ) {
							$has_explicit = true;
							break;
						}
					}
				}
				if ( ! $has_explicit ) {
					$settings['colors'] = self::get_color_palette();
				}
			}
			return $settings;
		}

		if ( 'chartjs' === $library ) {
			$has_explicit = false;
			if ( isset( $settings['series'] ) && is_array( $settings['series'] ) ) {
				foreach ( $settings['series'] as $series_settings ) {
					if ( ! empty( $series_settings['backgroundColor'] ) || ! empty( $series_settings['borderColor'] ) || ! empty( $series_settings['hoverBackgroundColor'] ) ) {
						$has_explicit = true;
						break;
					}
				}
			}
			if ( ! $has_explicit && isset( $settings['slices'] ) && is_array( $settings['slices'] ) ) {
				foreach ( $settings['slices'] as $slice_settings ) {
					if ( ! empty( $slice_settings['backgroundColor'] ) || ! empty( $slice_settings['borderColor'] ) || ! empty( $slice_settings['hoverBackgroundColor'] ) ) {
						$has_explicit = true;
						break;
					}
				}
			}
			if ( $has_explicit ) {
				return $settings;
			}
			$series = get_post_meta( $chart_id, Visualizer_Plugin::CF_SERIES, true );
			if ( is_array( $series ) ) {
				$settings = self::apply_chartjs_palette( $settings, $type, $series, $chart_id );
			}
		}

		return $settings;
	}

	/**
	 * Applies the global palette to ChartJS settings for a chart.
	 *
	 * @param array<string, mixed> $settings Current chart settings.
	 * @param string               $type     Chart type.
	 * @param array<int, mixed>    $series   Series definitions.
	 * @param int                  $chart_id Chart ID.
	 * @return array<string, mixed>
	 * @access private
	 * @static
	 */
	private static function apply_chartjs_palette( array $settings, string $type, array $series, int $chart_id ): array {
		$attributes = array();
		$name   = 'series';
		$count  = count( $series );
		$max    = $count - 1;

		switch ( $type ) {
			case 'polarArea':
				// fall through.
			case 'pie':
				$chart = get_post( $chart_id );
				$data  = $chart instanceof WP_Post ? maybe_unserialize( $chart->post_content ) : array();
				$name  = 'slices';
				$max   = is_array( $data ) ? count( $data ) : $count;
				// fall through.
			case 'column':
				// fall through.
			case 'bar':
				for ( $i = 0; $i < $max; $i++ ) {
					$colors = self::get_color_at( $i );
					$attributes[] = array( 'backgroundColor' => $colors[0], 'hoverBackgroundColor' => $colors[1] );
				}
				break;
			case 'radar':
				// fall through.
			case 'line':
				// fall through.
			case 'area':
				for ( $i = 0; $i < $max; $i++ ) {
					$colors = self::get_color_at( $i );
					$attributes[] = array( 'borderColor' => $colors[0] );
				}
				break;
		}

		if ( $attributes ) {
			$settings[ $name ] = $attributes;
		}

		return $settings;
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

			// Apply global color defaults to new Google Charts (not Geo — it uses colorAxis).
			if ( 'geo' !== $type ) {
				$global = self::get_global_style_defaults();
				if ( ! empty( $global['color_primary'] ) || ! empty( $global['color_secondary'] ) ) {
					$attributes['colors'] = self::get_color_palette();
				}
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
					$colors = self::get_color_at( $i );
					$attributes[] = array( 'backgroundColor' => $colors[0], 'hoverBackgroundColor' => $colors[1] );
				}
				break;
			case 'radar':
				// fall through.
			case 'line':
				// fall through.
			case 'area':
				for ( $i = 0; $i < $max; $i++ ) {
					$colors = self::get_color_at( $i );
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
