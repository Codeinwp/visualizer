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
	 * Show the JSON/REST parameters boxes.
	 *
	 * @access public
	 */
	public static function _renderJsonScreen( $args ) {
		$id      = $args[1];
		$action = add_query_arg(
			array(
				'action' => Visualizer_Plugin::ACTION_JSON_SET_DATA,
				'security'  => wp_create_nonce( Visualizer_Plugin::ACTION_JSON_SET_DATA . Visualizer_Plugin::VERSION ),
				'chart'  => $id,
			),
			admin_url( 'admin-ajax.php' )
		);

		$url = get_post_meta( $id, Visualizer_Plugin::CF_JSON_URL, true );
		$root = get_post_meta( $id, Visualizer_Plugin::CF_JSON_ROOT, true );
		$paging = get_post_meta( $id, Visualizer_Plugin::CF_JSON_PAGING, true );

		?>
		<div id="visualizer-json-screen" style="display: none">
			<div class="visualizer-json-form">
				<h3 class="viz-step step1"><?php _e( 'STEP 1: Specify the JSON endpoint/URL', 'visualizer' ); ?></h3>
				<div>
					<form id="json-endpoint-form">
						<div class="json-wizard-hints">
							<ul class="info">
								<li><?php echo sprintf( __( 'If you want to add authentication, add headers to the endpoint or change the request in any way, please refer to our document %1$shere%2$s.', 'visualizer' ), '<a href="https://docs.themeisle.com/article/1043-visualizer-how-to-extend-rest-endpoints-with-json-response" target="_blank">', '</a>' ); ?></li>
							</ul>
						</div>

						<input
							type="url"
							id="vz-import-json-url"
							name="url"
							value="<?php echo $url; ?>"
							placeholder="<?php esc_html_e( 'Please enter the URL', 'visualizer' ); ?>"
							class="visualizer-input">
						<button class="button button-secondary button-small" id="visualizer-json-fetch"><?php esc_html_e( 'Fetch Endpoint', 'visualizer' ); ?></button>
					</form>
				</div>
				<h3 class="viz-step step2 <?php echo empty( $url ) ? 'ui-state-disabled' : ''; ?> json-root-form"><?php _e( 'STEP 2: Choose the JSON root', 'visualizer' ); ?></h3>
				<div>
					<form id="json-root-form">
						<input type="hidden" name="chart" value="<?php echo $id; ?>">
						<select name="root" id="vz-import-json-root">
						<?php
						if ( ! empty( $root ) ) {
							?>
							<option value="<?php echo $root; ?>"><?php echo str_replace( Visualizer_Source_Json::TAG_SEPARATOR, Visualizer_Source_Json::TAG_SEPARATOR_VIEW, $root ); ?></option>
							<?php
						}
						?>
						</select>
						<input type="hidden" name="url" value="<?php echo $url; ?>">
						<button class="button button-secondary button-small" id="visualizer-json-parse"><?php esc_html_e( 'Parse Endpoint', 'visualizer' ); ?></button>
					</form>
				</div>
				<h3 class="viz-step step3 <?php echo empty( $root ) ? 'ui-state-disabled' : ''; ?>"><?php _e( 'STEP 3: Specify miscellaneous parameters', 'visualizer' ); ?></h3>
				<div>
					<form id="json-conclude-form-helper">
						<div class="<?php echo apply_filters( 'visualizer_pro_upsell_class', 'only-pro-feature' ); ?>">
							<div class="json-pagination">
								<select name="paging" id="vz-import-json-paging" class="json-form-element" data-template='<?php echo sprintf( 'Get first %d pages using %s', apply_filters( 'visualizer_json_fetch_pages', 5, $url ), '?' ); ?>'>
									<option value="0" class="static"><?php _e( 'Don\'t use pagination', 'visualizer' ); ?></option>
								<?php
								if ( ! empty( $paging ) ) {
									?>
									<option value="<?php echo $paging; ?>"><?php echo sprintf( 'Get first %d pages using %s', apply_filters( 'visualizer_json_fetch_pages', 5, $url ), str_replace( Visualizer_Source_Json::TAG_SEPARATOR, Visualizer_Source_Json::TAG_SEPARATOR_VIEW, $paging ) ); ?></option>
									<?php
								}
								?>
								</select>
								<?php
								if ( ! VISUALIZER_PRO ) {
									?>
								<br/>
								<br/>
								<br/>
								<br/>
									<?php
								}
								?>
								<?php echo apply_filters( 'visualizer_pro_upsell', '' ); ?>
							</div>
						</div>
					</form>
				</div>
				<h3 class="viz-step step4 ui-state-disabled"><?php _e( 'STEP 4: Select the data to display in the chart', 'visualizer' ); ?></h3>
				<div>
					<form id="json-conclude-form" action="<?php echo $action; ?>" method="post" target="thehole">
						<input type="hidden" name="url">
						<input type="hidden" name="root">
						<input type="hidden" name="chart" value="<?php echo $id; ?>">

						<div class="json-wizard-hints html-table-editor-hints">
							<ul class="info">
								<li><?php _e( 'Select whether to include the data in the chart. Each column selected will form one series.', 'visualizer' ); ?></li>
								<li><?php _e( 'If a column is selected to be included, specify its data type.', 'visualizer' ); ?></li>
								<li><?php _e( 'You can use drag/drop to reorder the columns but this column position is not saved. So when you reload the table, you may have to reorder again.', 'visualizer' ); ?></li>
								<li><?php _e( 'You can select any number of columns but the chart type selected will determine how many will display in the chart.', 'visualizer' ); ?></li>
							</ul>
						</div>
						<div class="json-table"></div>
						<button class="button button-primary" id="visualizer-json-conclude"><?php esc_html_e( 'Save &amp; Show Chart', 'visualizer' ); ?></button>
					</form>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Show the simple editor(s).
	 *
	 * @access public
	 */
	public static function _renderSimpleEditorScreen( $args ) {
		$chart_id   = $args[1];
		$action = add_query_arg(
			array(
				'action' => Visualizer_Plugin::ACTION_UPLOAD_DATA,
				'nonce'  => wp_create_nonce(),
				'chart'  => $chart_id,
			),
			admin_url( 'admin-ajax.php' )
		);
		?>
			<div class="viz-simple-editor">
				<div class="viz-simple-editor-type viz-table-editor">
					<form id="table-editor-form" action="<?php echo $action; ?>" method="post" target="thehole">
						<div class="html-table-editor-hints">
							<ul class="info">
								<li><?php _e( 'Select whether to include the data in the chart. Each column selected will form one series.', 'visualizer' ); ?></li>
								<li><?php _e( 'If a column is selected to be included, specify its data type.', 'visualizer' ); ?></li>
								<li><?php _e( 'You can use drag/drop to reorder the columns but this column position is not saved. So when you reload the table, you may have to reorder again.', 'visualizer' ); ?></li>
								<li><?php _e( 'You can select any number of columns but the chart type selected will determine how many will display in the chart.', 'visualizer' ); ?></li>
							</ul>
						</div>
						<div class="viz-html-table">
							<?php Visualizer_Render_Layout::show( 'editor-table', null, $chart_id, 'viz-html-table', true, true ); ?>
						</div>
						<input type="hidden" name="table_data" value="yes">
						<button class="button button-primary" id="visualizer-save-html-table"><?php esc_html_e( 'Save &amp; Show Chart', 'visualizer' ); ?></button>
					</form>
				</div>

				<?php Visualizer_Render_Layout::show( 'text-editor', $chart_id ); ?>

			</div>
		<?php

	}

	/**
	 * Show the text area editor.
	 *
	 * @access public
	 */
	public static function _renderTextEditor( $args ) {
		$chart_id = $args[1];
		$csv = apply_filters( Visualizer_Plugin::FILTER_GET_CHART_DATA_AS, array(), $chart_id, 'csv' );
		$data = '';
		if ( ! empty( $csv ) && isset( $csv['string'] ) ) {
			$data = str_replace( PHP_EOL, "\n", $csv['string'] );
		}
		?>
		<div class="viz-simple-editor-type viz-text-editor">
			<textarea id="edited_text"><?php echo $data; ?></textarea>
			<button id="viz-text-editor-button" class="button button-primary"><?php _e( 'Save &amp; Show Chart', 'visualizer' ); ?></button>
		</div>
		<?php
	}

	/**
	 * Show the JSON endpoint's parsed table.
	 *
	 * @access public
	 */
	public static function _renderEditorTable( $args ) {
		$data       = $args[1];
		$chart_id   = $args[2];
		$class      = $args[3];
		$echo       = $args[4];
		$editable_data = $args[5];
		$series     = get_post_meta( $chart_id, Visualizer_Plugin::CF_SERIES, true );
		$headers    = array();

		if ( is_null( $data ) ) {
			foreach ( $series as $column ) {
				$headers[] = $column['label'];
			}
			$chart      = get_post( $chart_id );
			$type       = get_post_meta( $chart_id, Visualizer_Plugin::CF_CHART_TYPE, true );
			$data       = apply_filters( Visualizer_Plugin::FILTER_GET_CHART_DATA, unserialize( html_entity_decode( $chart->post_content ) ), $type );
		} else {
			$headers    = array_keys( $data[0] );
		}

		$classes    = implode( ' ', array( 'viz-editor-table', $class ) );

		if ( $series ) {
			$temp   = $series;
			$series = array();
			foreach ( $temp as $array ) {
				$series[ $array['label'] ] = $array['type'];
			}
		}
		if ( ! $echo ) {
			ob_start();
		}
		?>
		<table cellspacing="0" width="100%" class="results cell-border stripe <?php echo $classes; ?>">
			<thead>
				<tr>
					<th><?php _e( 'Label', 'visualizer' ); ?></th>
		<?php
		foreach ( $headers as $header ) {
			if ( $editable_data ) {
				echo '<th><input type="text" name="header[]" value="' . esc_attr( $header ) . '"></th>';
			} else {
				echo '<th>' . $header . '</th>';
			}
		}
		?>
				</tr>
			</thead>
			<tbody>	
				<tr>
					<th><?php _e( 'Data Type', 'visualizer' ); ?></th>
		<?php
		$index = 0;
		foreach ( $headers as $header ) {
			echo '<td>';
			if ( $editable_data ) {
				echo '<select name="type[]">';
			} else {
				echo '<input name="header[]" type="hidden" value="' . $header . '">';
				echo '<select name="type[' . $header . ']">';
			}
			echo '<option value="" title="' . __( 'Exclude from chart', 'visualizer' ) . '">' . __( 'Exclude', 'visualizer' ) . '</option>';
			echo '<option value="0" disabled title="' . __( 'Include in chart and select data type', 'visualizer' ) . '">--' . __( 'OR', 'visualizer' ) . '--</option>';

			foreach ( Visualizer_Source::getAllowedTypes() as $type ) {
				$selected = array_key_exists( $header, $series ) && $type === $series[ $header ] ? 'selected' : '';
				echo '<option value="' . $type . '" ' . $selected . '>' . $type . '</option>';
			}

			echo '</select></td>';
		}
		?>
				</tr>
		<?php
		foreach ( $data as $row ) {
			echo '<tr>';
			echo '<th>' . __( 'Value', 'visualizer' ) . '</th>';
			$index = 0;
			foreach ( array_values( $row ) as $value ) {
				if ( $editable_data ) {
					echo '<td><input type="text" name="data' . $index++ . '[]" value="' . esc_attr( $value ) . '"></td>';
				} else {
					echo '<td>' . $value . '</td>';
				}
			}

			echo '</tr>';
		}
		?>
			</tbody>
		</table>
		<?php
		if ( ! $echo ) {
			return ob_get_clean();
		}
	}
}
