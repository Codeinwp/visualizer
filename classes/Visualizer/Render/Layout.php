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
 * Layout rendering class.
 *
 * @category Visualizer
 * @package Render
 */
class Visualizer_Render_Layout extends Visualizer_Render {

	/**
	 * Renders template.
	 *
	 * @since 1.0.0
	 *
	 * @abstract
	 * @access protected
	 */
	protected function _toHTML() {
		// empty.
	}

	/**
	 * Show the layout by delegating the call to the layout-specific method with the params.
	 *
	 * @access public
	 */
	public static function show( $layout ) {
		return call_user_func( array( __CLASS__, '_render' . str_replace( ' ', '', ucwords( str_replace( '-', ' ', $layout ) ) ) ), func_get_args() );
	}

	/**
	 * Show the fake editor (just an empty div).
	 *
	 * @access public
	 */
	public static function _renderFauxEditor( $args ) {
		echo '<div id="chart-lhs" class="visualizer-editor-lhs" style="display: none"></div>';
	}

	/**
	 * Show the JSON/REST parameters boxes.
	 *
	 * @access public
	 */
	public static function _renderJsonScreen( $args ) {
		$id      = $args[1];
		$action	= add_query_arg(
			array(
				'action' => Visualizer_Plugin::ACTION_JSON_SET_DATA,
				'security'  => wp_create_nonce( Visualizer_Plugin::ACTION_JSON_SET_DATA . Visualizer_Plugin::VERSION ),
				'chart'  => $id,
			),
			admin_url( 'admin-ajax.php' )
		);

		$url = get_post_meta( $id, Visualizer_Plugin::CF_JSON_URL, true );
		$root = get_post_meta( $id, Visualizer_Plugin::CF_JSON_ROOT, true );

		?>
		<div id="visualizer-json-screen" style="display: none">
			<div class="visualizer-json-form">
				<form id="json-endpoint-form">
					<input
						type="url"
						id="vz-import-json-url"
						name="url"
						value="<?php echo $url; ?>"
						placeholder="<?php esc_html_e( 'Please enter the URL', 'visualizer' ); ?>"
						class="visualizer-input visualizer-remote-url">
					<button class="button button-secondary button-small" id="visualizer-json-fetch"><?php esc_html_e( 'Fetch', 'visualizer' ); ?></button>
				</form>
				<form id="json-root-form" style="<?php echo empty( $root ) ? "display: none" : "" ?>">
					<select name="root" id="vz-import-json-root">
					<?php 
						if ( ! empty( $root ) ) {
					?>
						<option value="<?php echo $root; ?>"><?php echo $root; ?></option>
					<?php
						}
					?>
					</select>
					<input type="hidden" name="url" value="<?php echo $url; ?>">
					<button class="button button-secondary button-small" id="visualizer-json-parse"><?php esc_html_e( 'Parse', 'visualizer' ); ?></button>
				</form>
				<form id="json-conclude-form" action="<?php echo $action; ?>" method="post" style="display: none" target="thehole">
					<input type="hidden" name="url">
					<input type="hidden" name="root">
					<input type="hidden" name="chart" value="<?php echo $id; ?>">
					<div class="json-table"></div>
				</form>
			</div>
		</div>
		<?php
	}

	/**
	 * Show the DB query box.
	 *
	 * @access public
	 */
	public static function _renderDbQuery( $args ) {
		$query      = $args[1];
		?>
		<div id='visualizer-db-query' style="display: none">
			<div class="visualizer-db-query-form">
				<div>
					<form id='db-query-form'>
						<textarea name='query' class='visualizer-db-query' placeholder="<?php _e( 'Your query goes here', 'visualizer' ); ?>"><?php echo $query; ?></textarea>
					</form>
					<div class='db-wizard-error'></div>
				</div>
				<div>
					<input type="button" class="button button-primary" id='visualizer-query-fetch' value='<?php _e( 'Show Results', 'visualizer' ); ?>'>
				</div>
			</div>
			<div class='db-wizard-hints'>
				<ul>
					<li><?php echo sprintf( __( 'For examples of queries and links to resources that you can use with this feature, please click %1$shere%2$s', 'visualizer' ), '<a href="' . VISUALIZER_DB_QUERY_DOC_URL . '" target="_blank">', '</a>' ); ?></li>
					<li><?php echo sprintf( __( 'Use %1$sControl+Space%2$s for autocompleting keywords or table names.', 'visualizer' ), '<span class="visualizer-emboss">', '</span>' ); ?></li>
				</ul>
			</div>
			<div class='db-wizard-results'></div>

		</div>
		<?php
	}

	/**
	 * Show the DB wizard's results table.
	 *
	 * @access public
	 */
	public static function _renderDbWizardResults( $args ) {
		$headers    = $args[1];
		$results    = $args[2];
		ob_start();
		?>
		<table cellspacing="0" width="100%" id="results">
			<thead>
				<tr>
		<?php
		foreach ( $headers as $header ) {
			echo '<th>' . $header['label'] . '</th>';
		}
		?>
				</tr>
			</thead>
			<tfoot>
			</tfoot>
			<tbody>					
		<?php
		foreach ( $results as $result ) {
			echo '<tr>';
			foreach ( $result as $r ) {
				echo '<td>' . $r . '</td>';
			}
			echo '</tr>';
		}
		?>
			</tbody>
		</table>
		<?php
		return ob_get_clean();
	}

	/**
	 * Show the DB wizard's results table.
	 *
	 * @access public
	 */
	public static function _renderJsonTable( $args ) {
		$data		= $args[1];
		$headers	= array_keys( $data[0] );
		ob_start();
		?>
		<table cellspacing="0" width="100%" class="results">
			<thead>
				<tr>
		<?php
		foreach ( $headers as $header ) {
			echo '<th>' . $header . '</th>';
		}
		?>
				</tr>
			</thead>
			<tfoot>
			</tfoot>
			<tbody>	
				<tr>
		<?php
				foreach ( $headers as $header ) {
					echo '<td><input name="header[]" type="hidden" value="' . $header . '"><select name="type[' . $header . ']">';
					echo '<option value="">' . __( 'Exclude', 'visualizer' ) . '</option>';
					echo '<option value="0" disabled>--' . __( 'OR', 'visualizer' ) . '--</option>';

					foreach( Visualizer_Source::getAllowedTypes() as $type ) {
						echo '<option value="' . $type . '">' . $type . '</option>';
					}

					echo '</select></td>';
				}
		?>
				</tr>
		<?php
		foreach ( $data as $row ) {
			echo '<tr>';
			foreach ( array_values( $row ) as $value ) {
				echo '<td>' . $value . '</td>';
			}
			echo '</tr>';
		}
		?>
			</tbody>
		</table>
		<button class="button button-primary" id="visualizer-json-conclude"><?php esc_html_e( 'Use', 'visualizer' ); ?></button>
		<?php
		return ob_get_clean();
	}
}
