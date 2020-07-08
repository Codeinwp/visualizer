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
						<input type="hidden" name="chart_id" value="<?php echo $args[2]; ?>">
						<?php do_action( 'visualizer_db_query_add_layout', $args ); ?>
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
					<?php do_action( 'visualizer_db_query_add_hints', $args ); ?>
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
		$headers = get_post_meta( $id, Visualizer_Plugin::CF_JSON_HEADERS, true );
		if ( empty( $headers ) ) {
			$headers = array();
		}
		if ( $headers && empty( $headers['method'] ) ) {
			$headers['method'] = 'get';
		}
		$methods = apply_filters( 'visualizer_json_request_methods', array( 'GET', 'POST' ) );

		// open the headers by default?
		$headers_open = $headers && array_key_exists( 'auth', $headers ) && ( array_key_exists( 'username', $headers['auth'] ) && ! empty( $headers['auth']['username'] ) ) || ( ! empty( $headers['auth'] ) && is_string( $headers['auth'] ) );
		?>
		<div id="visualizer-json-screen" style="display: none">
			<div class="visualizer-json-form">
				<h3 class="viz-step step1"><?php _e( 'STEP 1: Specify the JSON endpoint/URL', 'visualizer' ); ?></h3>
				<div>
					<form id="json-endpoint-form">
						<div class="json-wizard-hints">
							<ul class="info">
								<li><?php echo sprintf( __( 'If you want to add authentication or headers to the endpoint or change the request in any way, please refer to our document %1$shere%2$s.', 'visualizer' ), '<a href="https://docs.themeisle.com/article/1043-visualizer-how-to-extend-rest-endpoints-with-json-response" target="_blank">', '</a>' ); ?></li>
							</ul>
						</div>

						<input
							type="url"
							id="vz-import-json-url"
							name="url"
							value="<?php echo esc_url( $url ); ?>"
							placeholder="<?php esc_html_e( 'Please enter the URL', 'visualizer' ); ?>"
							class="visualizer-input json-form-element">
						<button class="button button-secondary button-small" id="visualizer-json-fetch"><?php esc_html_e( 'Fetch Endpoint', 'visualizer' ); ?></button>
						
						<div class="visualizer-json-subform">
							<h3 class="viz-substep <?php echo $headers_open ? 'open' : ''; ?>"><?php _e( 'Headers', 'visualizer' ); ?></h3>
							<div class="json-wizard-headers">
								<div class="json-wizard-header">
									<div><?php _e( 'Request Type', 'visualizer' ); ?></div>
									<div>
										<select name="method" class="json-form-element">
										<?php foreach ( $methods as $method ) { ?>
											<option value="<?php echo $method; ?>" <?php $headers && selected( $headers['method'], $method ); ?>><?php echo $method; ?></option>
										<?php } ?>
										</select>
									</div>
								</div>
								<div class="json-wizard-header">
									<div><?php _e( 'Credentials', 'visualizer' ); ?></div>
									<div>
										<input
											type="text"
											id="vz-import-json-username"
											name="username"
											value="<?php echo ( array_key_exists( 'auth', $headers ) && array_key_exists( 'username', $headers['auth'] ) ? $headers['auth']['username'] : '' ); ?>"
											placeholder="<?php esc_html_e( 'Username/Access Key', 'visualizer' ); ?>"
											class="json-form-element">								
										&
										<input
											type="password"
											id="vz-import-json-password"
											name="password"
											value="<?php echo ( array_key_exists( 'auth', $headers ) && array_key_exists( 'password', $headers['auth'] ) ? $headers['auth']['password'] : '' ); ?>"
											placeholder="<?php esc_html_e( 'Password/Secret Key', 'visualizer' ); ?>"
											class="json-form-element">	
									</div>
								</div>
								<div class="json-wizard-header">
									<div></div>
									<div><?php esc_html_e( 'OR', 'visualizer' ); ?></div>
								</div>
								<div class="json-wizard-header">
									<div><?php esc_html_e( 'Authorization', 'visualizer' ); ?></div>
									<div>
										<input
											type="text"
											id="vz-import-json-auth"
											name="auth"
											value="<?php echo ( array_key_exists( 'auth', $headers ) && is_string( $headers['auth'] ) ? $headers['auth'] : '' ); ?>"
											placeholder="<?php esc_html_e( 'e.g. SharedKey <AccountName>:<Signature>', 'visualizer' ); ?>"
											class="visualizer-input json-form-element">
									</div>
								</div>
							</div>
						</div>
					</form>
				</div>
				<h3 class="viz-step step2 <?php echo empty( $url ) ? 'ui-state-disabled' : ''; ?> json-root-form"><?php _e( 'STEP 2: Choose the JSON root', 'visualizer' ); ?></h3>
				<div>
					<form id="json-root-form">
						<input type="hidden" name="chart" value="<?php echo $id; ?>">
						<select name="root" id="vz-import-json-root" class="json-form-element">
						<?php
						if ( ! empty( $root ) ) {
							?>
							<option value="<?php echo esc_attr( $root ); ?>"><?php echo str_replace( Visualizer_Source_Json::TAG_SEPARATOR, Visualizer_Source_Json::TAG_SEPARATOR_VIEW, $root ); ?></option>
							<?php
						}
						?>
						</select>
						<button class="button button-secondary button-small" id="visualizer-json-parse"><?php esc_html_e( 'Parse Endpoint', 'visualizer' ); ?></button>
					</form>
				</div>
				<h3 class="viz-step step3 <?php echo empty( $root ) ? 'ui-state-disabled' : ''; ?>"><?php _e( 'STEP 3: Specify miscellaneous parameters', 'visualizer' ); ?></h3>
				<div>
					<form id="json-conclude-form-helper">
						<div class="<?php echo apply_filters( 'visualizer_pro_upsell_class', 'only-pro-feature' ); ?>">
							<div class="json-pagination">
								<select name="paging" id="vz-import-json-paging" class="json-form-element" data-template='<?php echo sprintf( 'Get results from the first %d pages using %s', apply_filters( 'visualizer_json_fetch_pages', 5, $url ), '?' ); ?>'>
									<option value="0" class="static"><?php _e( 'Get results from the first page only', 'visualizer' ); ?></option>
								<?php
								if ( ! empty( $paging ) ) {
									?>
									<option value="<?php echo esc_attr( $paging ); ?>"><?php echo sprintf( 'Get results from the first %d pages using %s', apply_filters( 'visualizer_json_fetch_pages', 5, $url ), str_replace( Visualizer_Source_Json::TAG_SEPARATOR, Visualizer_Source_Json::TAG_SEPARATOR_VIEW, $paging ) ); ?></option>
									<?php
								}
								?>
								</select>
								<?php
								if ( ! Visualizer_Module::is_pro() ) {
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
						<div class="json-wizard-hints html-table-editor-hints">
							<ul class="info">
								<li><?php _e( 'If you see Invalid Data in the table, you may have selected the wrong root to fetch data from. Please select an alternative from the JSON root dropdown.', 'visualizer' ); ?></li>
								<li><?php _e( 'Select whether to include the data in the chart. Each column selected will form one series.', 'visualizer' ); ?></li>
								<li><?php _e( 'If a column is selected to be included, specify its data type.', 'visualizer' ); ?></li>
								<li><?php _e( 'You can use drag/drop to reorder the columns but this column position is not saved. So when you reload the table, you may have to reorder again.', 'visualizer' ); ?></li>
								<li><?php _e( 'You can select any number of columns but the chart type selected will determine how many will display in the chart.', 'visualizer' ); ?></li>
								<li><?php _e( 'Once you have made your selection, click \'Show Chart\' on the right to view the chart.', 'visualizer' ); ?></li>
							</ul>
						</div>
						<div class="json-table"></div>
						<button class="button button-primary" style="display: none" id="visualizer-json-conclude"></button>
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
						<input type="hidden" name="editor-type" value="table">
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
		$csv = apply_filters( Visualizer_Plugin::FILTER_GET_CHART_DATA_AS, array(), $chart_id, 'csv-display' );
		$data = '';
		if ( ! empty( $csv ) && isset( $csv['string'] ) ) {
			$data = str_replace( PHP_EOL, "\n", stripslashes( $csv['string'] ) );
		}
		?>
		<div class="viz-simple-editor-type viz-text-editor">
			<textarea id="edited_text"><?php echo $data; ?></textarea>
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
			$data       = Visualizer_Module::get_chart_data( $chart, $type );
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
				echo '<select name="type[' . $header . ']"  class="viz-select-data-type">';
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
		// for remote sources, the data exists inside 'data'.
		if ( array_key_exists( 'data', $data ) ) {
			$data = $data['data'];
		}

		foreach ( $data as $row ) {
			echo '<tr>';
			echo '<th>' . __( 'Value', 'visualizer' ) . '</th>';
			$index = 0;
			if ( empty( $row ) ) {
				echo '<td></td>';
				continue;
			}
			foreach ( array_values( $row ) as $value ) {
				if ( $editable_data ) {
					echo '<td><input type="text" name="data' . $index++ . '[]" value="' . esc_attr( stripslashes( $value ) ) . '"></td>';
				} else {
					echo '<td>' . ( is_array( $value ) ? __( 'Invalid Data', 'visualizer' ) : $value ) . '</td>';
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

	/**
	 * Generates the permissions tab.
	 *
	 * @access private
	 */
	private static function _renderPermissions( $args ) {
		$chart_id = $args[1];

		// ignore for unit tests because Travis throws the error "Indirect modification of overloaded property Visualizer_Render_Page_Data::$permissions has no effect".
		if ( defined( 'WP_TESTS_DOMAIN' ) ) {
			return;
		}

		$permissions    = apply_filters( 'visualizer_pro_get_permissions', null, $chart_id );
		if ( is_array( $permissions ) ) {
			$permissions = $permissions['permissions'];
		} else {
			$permissions = array();
		}
		if ( ! isset( $permissions['read'] ) ) {
			$permissions['read'] = 'all';
		}
		if ( ! isset( $permissions['edit'] ) ) {
			$permissions['edit'] = 'roles';
			$permissions['edit-specific'] = 'administrator';
		}

		Visualizer_Render_Sidebar::_renderGroupStart( esc_html__( 'Permissions', 'visualizer' ) . '<span class="dashicons dashicons-lock"></span>', '', apply_filters( 'visualizer_pro_upsell_class', 'only-pro-feature', 'chart-permissions' ), 'vz-permissions' );
			echo '<div style="position: relative">';
			Visualizer_Render_Sidebar::_renderSectionStart();
				Visualizer_Render_Sidebar::_renderSectionDescription( esc_html__( 'Configure permissions for the chart.', 'visualizer' ) );
			Visualizer_Render_Sidebar::_renderSectionEnd();

			Visualizer_Render_Sidebar::_renderSectionStart( esc_html__( 'Who can see this chart?', 'visualizer' ), false );
				Visualizer_Render_Sidebar::_renderSelectItem(
					'',
					'permissions[read]',
					$permissions['read'],
					array(
						'all'       => esc_html__( 'All Users', 'visualizer' ),
						'users'     => esc_html__( 'Select Users', 'visualizer' ),
						'roles'     => esc_html__( 'Select Roles', 'visualizer' ),
					),
					'',
					false,
					array( 'visualizer-permission', 'visualizer-permission-type', 'visualizer-permission-read' ),
					array(
						'permission-type' => 'read',
					)
				);

				$options    = apply_filters( 'visualizer_pro_get_permissions_data', array(), isset( $permissions['read'] ) ? $permissions['read'] : 'roles' );

				Visualizer_Render_Sidebar::_renderSelectItem(
					'',
					'permissions[read-specific][]',
					isset( $permissions['read-specific'] ) ? $permissions['read-specific'] : array(),
					$options,
					'',
					true,
					array( 'visualizer-permission', 'visualizer-permission-specific', 'visualizer-permission-read-specific' )
				);
			Visualizer_Render_Sidebar::_renderSectionEnd();

			Visualizer_Render_Sidebar::_renderSectionStart( esc_html__( 'Who can edit this chart?', 'visualizer' ), false );
				Visualizer_Render_Sidebar::_renderSelectItem(
					'',
					'permissions[edit]',
					$permissions['edit'],
					array(
						'all'       => esc_html__( 'All Users', 'visualizer' ),
						'users'     => esc_html__( 'Select Users', 'visualizer' ),
						'roles'     => esc_html__( 'Select Roles', 'visualizer' ),
					),
					'',
					false,
					array( 'visualizer-permission', 'visualizer-permission-type', 'visualizer-permission-edit' ),
					array(
						'permission-type' => 'edit',
					)
				);

				$options    = apply_filters( 'visualizer_pro_get_permissions_data', array(), isset( $permissions['edit'] ) ? $permissions['edit'] : 'roles' );

				Visualizer_Render_Sidebar::_renderSelectItem(
					'',
					'permissions[edit-specific][]',
					isset( $permissions['edit-specific'] ) ? $permissions['edit-specific'] : array(),
					$options,
					'',
					true,
					array( 'visualizer-permission', 'visualizer-permission-specific', 'visualizer-permission-edit-specific' )
				);
			Visualizer_Render_Sidebar::_renderSectionEnd();
			echo apply_filters( 'visualizer_pro_upsell', '', 'chart-permissions' );
			echo '</div>';
		Visualizer_Render_Sidebar::_renderGroupEnd();
	}

	/**
	 * Show the Settings tab for this chart.
	 *
	 * @access public
	 */
	public static function _renderTabAdvanced( $args ) {
		$sidebar = $args[2];
		?>
			<ul class="viz-group-wrapper full-height">
				<li class="viz-group open" id="vz-chart-settings">
					<ul class="viz-group-content">
						<ul class="viz-group-wrapper">
						<form id="settings-form" action="<?php echo add_query_arg( 'nonce', wp_create_nonce() ); ?>" method="post">
							<?php echo $sidebar; ?>
							<?php self::_renderPermissions( $args ); ?>
							<input type="hidden" name="save" value="1">
						</form>
						<form id="cancel-form" action="<?php echo add_query_arg( 'nonce', wp_create_nonce() ); ?>" method="post">
							<input type="hidden" name="cancel" value="1">
						</form>
						</ul>
					</ul>
				</li>
		<?php
	}

	/**
	 * Show the Docs tab for this chart.
	 *
	 * @access public
	 */
	public static function _renderTabHelp( $args ) {
		$chart_id = $args[1];
		$type   = get_post_meta( $chart_id, Visualizer_Plugin::CF_CHART_TYPE, true );
		switch ( $type ) {
			case 'tabular':
				$type = 'table';
				break;
			case 'polarArea':
				$type = 'polar-area';
				break;
			case 'radar':
				$type = 'radar-spider';
				break;
		}

		$displayType = str_replace( '-', '/', $type );
		?>
			<ul class="viz-group-wrapper full-height">
				<li class="viz-group open" id="vz-chart-help">
					<ul class="viz-group-wrapper">
		<?php
		// open this tab by default.
		Visualizer_Render_Sidebar::_renderGroupStart( esc_html__( 'Documentation', 'visualizer' ), '', 'open' );
			Visualizer_Render_Sidebar::_renderSectionStart( esc_html__( 'General', 'visualizer' ), true );
		?>
				<h4><span class="dashicons dashicons-editor-help"></span><a href="<?php echo VISUALIZER_MAIN_DOC; ?>" target="_blank"><?php _e( 'Main documentation page', 'visualizer' ); ?></a></h4>
				<h4><span class="dashicons dashicons-media-code"></span><a href="<?php echo VISUALIZER_CODE_SNIPPETS_URL; ?>" target="_blank"><?php _e( 'Custom code snippets', 'visualizer' ); ?></a></h4>
		<?php
			Visualizer_Render_Sidebar::_renderSectionEnd();
			Visualizer_Render_Sidebar::_renderSectionStart( sprintf( __( '%s chart', 'visualizer' ), ucwords( $displayType ) ), true );
		?>
				<h4><span class="dashicons dashicons-video-alt2"></span>&nbsp;<a href="<?php echo str_replace( '#', "$type-chart", VISUALIZER_DEMO_URL ); ?>" target="_blank"><?php _e( 'View demo', 'visualizer' ); ?></a></h4>
				<h4><span class="dashicons dashicons-search"></span><a href="<?php echo str_replace( '#', $type, VISUALIZER_DOC_COLLECTION ); ?>" target="_blank"><?php echo sprintf( __( 'Articles containing "%s"', 'visualizer' ), $displayType ); ?></a></h4>
		<?php
			Visualizer_Render_Sidebar::_renderSectionEnd();
		Visualizer_Render_Sidebar::_renderGroupEnd();
		?>
					</ul>
				</li>
			</ul>

		<?php
	}

	/**
	 * Show the Data Source tab for this chart.
	 *
	 * @access public
	 */
	public static function _renderTabBasic( $args ) {
		$chart_id = $args[1];

		$upload_link = add_query_arg(
			array(
				'action' => Visualizer_Plugin::ACTION_UPLOAD_DATA,
				'nonce'  => wp_create_nonce(),
				'chart'  => $chart_id,
			),
			admin_url( 'admin-ajax.php' )
		);

		// this will allow us to open the correct source tab by default.
		$source_of_chart    = strtolower( get_post_meta( $chart_id, Visualizer_Plugin::CF_SOURCE, true ) );
		// both import from wp and import from db have the same source so we need to differentiate.
		$filter_config      = get_post_meta( $chart_id, Visualizer_Plugin::CF_FILTER_CONFIG, true );
		// if filter config is present, then its import from wp.
		if ( ! empty( $filter_config ) ) {
			$source_of_chart .= '_wp';
		}
		$editor_type    = get_post_meta( $chart_id, Visualizer_Plugin::CF_EDITOR, true );
		if ( $editor_type ) {
			$source_of_chart = 'visualizer_source_manual';
		}

		$type   = get_post_meta( $chart_id, Visualizer_Plugin::CF_CHART_TYPE, true );
		$lib    = get_post_meta( $chart_id, Visualizer_Plugin::CF_CHART_LIBRARY, true );
		?>
			<ul class="viz-group-wrapper full-height">
				<li class="viz-group open" id="vz-chart-source">
					<ul class="viz-group-content">
						<ul class="viz-group-wrapper">
							<!-- import from file -->
							<li class="viz-group visualizer_source_csv">
								<h2 class="viz-group-title viz-sub-group visualizer-src-tab"><?php _e( 'Import data from file', 'visualizer' ); ?></h2>
								<div class="viz-group-content">
									<p class="viz-group-description"><?php esc_html_e( 'Select and upload your data CSV file here. The first row of the CSV file should contain the column headings. The second one should contain series type (string, number, boolean, date, datetime, timeofday).', 'visualizer' ); ?></p>
									<p class="viz-group-description viz-info-msg"><b><?php echo sprintf( __( 'If you are unsure about how to format your data CSV then please take a look at this sample: %1$s %2$s%3$s. If you are using non-English characters, please make sure you save the file in UTF-8 encoding.', 'visualizer' ), '<a href="' . VISUALIZER_ABSURL . 'samples/' . $type . '.csv" target="_blank">', $type, '.csv</a>' ); ?></b></p>
									<form id="vz-csv-file-form" action="<?php echo $upload_link; ?>" method="post"
										  target="thehole" enctype="multipart/form-data">
										<input type="hidden" id="remote-data" name="remote_data">
										<div class="">
											<input type="file" id="csv-file" name="local_data">
										</div>
										<input type="button" class="button button-primary" id="vz-import-file"
											   value="<?php _e( 'Import', 'visualizer' ); ?>">
									</form>
								</div>
							</li>
							<!-- import from url -->
							<li class="viz-group visualizer-import-url visualizer_source_csv_remote visualizer_source_json">
								<h2 class="viz-group-title viz-sub-group visualizer-src-tab"><?php _e( 'Import data from URL', 'visualizer' ); ?></h2>
								<ul class="viz-group-content">
									<!-- import from csv url -->
									<li class="viz-subsection">
										<span class="viz-section-title"><?php _e( 'Import from CSV', 'visualizer' ); ?></span>
										<div class="viz-section-items section-items">
											<p class="viz-group-description"><?php echo sprintf( __( 'You can use this to import data from a remote CSV file or %1$sGoogle Spreadsheet%2$s.', 'visualizer' ), '<a href="https://docs.themeisle.com/article/607-how-can-i-populate-data-from-google-spreadsheet" target="_blank" >', '</a>' ); ?> </p>
											<p class="viz-group-description viz-info-msg"><b><?php echo sprintf( __( 'If you are unsure about how to format your data CSV then please take a look at this sample: %1$s %2$s%3$s. If you are using non-English characters, please make sure you save the file in UTF-8 encoding.', 'visualizer' ), '<a href="' . VISUALIZER_ABSURL . 'samples/' . $type . '.csv" target="_blank">', $type, '.csv</a>' ); ?></b></p>
											<form id="vz-one-time-import" action="<?php echo $upload_link; ?>" method="post"
												  target="thehole" enctype="multipart/form-data">
												<div class="remote-file-section">
													<input type="url" id="vz-schedule-url" name="remote_data" value="<?php echo esc_attr( get_post_meta( $chart_id, Visualizer_Plugin::CF_CHART_URL, true ) ); ?>" placeholder="<?php esc_html_e( 'Please enter the URL of CSV file', 'visualizer' ); ?>" class="visualizer-input visualizer-remote-url">
												</div>
												<select name="vz-import-time" id="vz-import-time" class="visualizer-select">
												<?php
												$hours     = get_post_meta( $chart_id, Visualizer_Plugin::CF_CHART_SCHEDULE, true );
												$schedules = apply_filters(
													'visualizer_chart_schedules', array(
														'-1' => __( 'One-time', 'visualizer' ),
													),
													'csv',
													$chart_id
												);
												foreach ( $schedules as $num => $name ) {
													// phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
													$extra = $num == $hours ? 'selected' : '';
													?>
													<option value="<?php echo $num; ?>" <?php echo $extra; ?>><?php echo $name; ?></option>
													<?php
												}
													do_action( 'visualizer_chart_schedules_spl', 'csv', $chart_id, 1 );
												?>
												</select>

												<?php
												if ( ! Visualizer_Module::is_pro() ) {
													?>
													<input type="button" id="view-remote-file" class="button button-primary" value="<?php _e( 'Import', 'visualizer' ); ?>">
													<?php
												} else {
													?>
												<input type="button" id="vz-save-schedule" class="button button-primary" value="<?php _e( 'Import & Save schedule', 'visualizer' ); ?>">
													<?php
												}
												?>
											</form>
										</div>
									</li>
									<!-- import from json url -->
									<li class="viz-subsection">
									<span class="viz-section-title visualizer_source_json"><?php _e( 'Import from JSON', 'visualizer' ); ?></span>
										<div class="viz-section-items section-items">
											<p class="viz-group-description"><?php _e( 'You can choose here to import/synchronize your chart data with a remote JSON source. For more info check <a href="https://docs.themeisle.com/article/1052-how-to-generate-charts-from-json-data-rest-endpoints" target="_blank" >this</a> tutorial', 'visualizer' ); ?></p>
											<form id="vz-import-json" action="<?php echo $upload_link; ?>" method="post" target="thehole" enctype="multipart/form-data">
												<div class="remote-file-section">
														<?php
														$bttn_label = 'visualizer_source_json' === $source_of_chart ? __( 'Modify Parameters', 'visualizer' ) : __( 'Create Parameters', 'visualizer' );
														if ( Visualizer_Module::is_pro() ) {
															?>
													<p class="viz-group-description"><?php _e( 'How often do you want to check the URL', 'visualizer' ); ?></p>
													<select name="time" id="vz-json-time" class="visualizer-select json-form-element" data-chart="<?php echo $chart_id; ?>">
															<?php
															$hours     = get_post_meta( $chart_id, Visualizer_Plugin::CF_JSON_SCHEDULE, true );
															$schedules = apply_filters(
																'visualizer_chart_schedules', array(
																	'-1' => __( 'One-time', 'visualizer' ),
																),
																'json',
																$chart_id
															);
															foreach ( $schedules as $num => $name ) {
																// phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
																$extra = $num == $hours ? 'selected' : '';
																?>
																<option value="<?php echo $num; ?>" <?php echo $extra; ?>><?php echo $name; ?></option>
																<?php
															}
															do_action( 'visualizer_chart_schedules_spl', 'json', $chart_id, 1 );
															?>
														</select>
															<?php
														}
														?>
												</div>

												<input type="button" id="json-chart-button" class="button button-secondary show-chart-toggle"
												value="<?php echo $bttn_label; ?>" data-current="chart"
												data-t-filter="<?php _e( 'Show Chart', 'visualizer' ); ?>"
												data-t-chart="<?php echo $bttn_label; ?>">
												<?php
												if ( Visualizer_Module::is_pro() ) {
													?>
												<input type="button" id="json-chart-save-button" class="button button-primary "
												value="<?php _e( 'Save Schedule', 'visualizer' ); ?>">
													<?php
												}
												?>
											</form>
										</div>
									</li>
								</ul>
							</li>
							<!-- import from chart -->
							<li class="viz-group viz-import-from-other <?php echo apply_filters( 'visualizer_pro_upsell_class', 'only-pro-feature' ); ?>">
								<h2 class="viz-group-title viz-sub-group"
									data-current="chart"><?php _e( 'Import from other chart', 'visualizer' ); ?><span
											class="dashicons dashicons-lock"></span></h2>
								<div class="viz-group-content edit-data-content">
									<div>
										<p class="viz-group-description"><?php _e( 'You can import here data from your previously created charts', 'visualizer' ); ?></p>
										<form>
											<select name="vz-import-from-chart" id="chart-id" class="visualizer-select">
												<?php
												$fetch_link        = add_query_arg(
													array(
														'action' => Visualizer_Module::is_pro() ? Visualizer_Pro::ACTION_FETCH_DATA : '',
														'nonce'  => wp_create_nonce(),
													),
													admin_url( 'admin-ajax.php' )
												);
												$query_args_charts = array(
													'post_type'      => Visualizer_Plugin::CPT_VISUALIZER,
													'posts_per_page' => 300,
													'no_found_rows'  => true,
												);
												$charts            = array();
												$query             = new WP_Query( $query_args_charts );
												while ( $query->have_posts() ) {
													$chart    = $query->next_post();
													$settings = get_post_meta( $chart->ID, Visualizer_Plugin::CF_SETTINGS, true );

													$title       = '#' . $chart->ID;
													if ( ! empty( $settings['title'] ) ) {
														$title  = $settings['title'];
													}
													// for ChartJS, title is an array.
													if ( is_array( $title ) && isset( $title['text'] ) ) {
														$title = $title['text'];
													}
													if ( empty( $title ) ) {
														$title  = '#' . $chart->ID;
													}

													?>
													<option value="<?php echo $chart->ID; ?>"><?php echo $title; ?></option>
													<?php
												}
												?>

											</select>
										</form>
										<input type="button" id="existing-chart" class="button button-primary"
											   value="<?php _e( 'Import Chart', 'visualizer' ); ?>"
											   data-viz-link="<?php echo $fetch_link; ?>">
										<?php echo apply_filters( 'visualizer_pro_upsell', '' ); ?>
									</div>
								</div>
							</li>

							<?php
								$save_filter = add_query_arg(
									array(
										'action' => Visualizer_Plugin::ACTION_SAVE_FILTER_QUERY,
										'security'  => wp_create_nonce( Visualizer_Plugin::ACTION_SAVE_FILTER_QUERY . Visualizer_Plugin::VERSION ),
										'chart'  => $chart_id,
									), admin_url( 'admin-ajax.php' )
								);
							?>
							<!-- import from WordPress -->
							<li class="viz-group visualizer_source_query_wp <?php echo apply_filters( 'visualizer_pro_upsell_class', 'only-pro-feature', 'import-wp' ); ?> ">
								<h2 class="viz-group-title viz-sub-group"><?php _e( 'Import from WordPress', 'visualizer' ); ?><span
											class="dashicons dashicons-lock"></span></h2>
								<div class="viz-group-content edit-data-content">
									<div>
										<p class="viz-group-description"><?php _e( 'You can import data from WordPress here.', 'visualizer' ); ?></p>
										<form id="vz-filter-wizard" action="<?php echo $save_filter; ?>" method="post" target="thehole">
											<p class="viz-group-description"><?php _e( 'How often do you want to refresh the data from WordPress.', 'visualizer' ); ?></p>
											<select name="refresh" id="vz-filter-import-time" class="visualizer-select">
											<?php
											$bttn_label = 'visualizer_source_query_wp' === $source_of_chart ? __( 'Modify Filter', 'visualizer' ) : __( 'Create Filter', 'visualizer' );
											$hours     = get_post_meta( $chart_id, Visualizer_Plugin::CF_DB_SCHEDULE, true );
											$schedules = apply_filters(
												'visualizer_chart_schedules', array(
													'-1' => __( 'One-time', 'visualizer' ),
												),
												'wp',
												$chart_id
											);
											foreach ( $schedules as $num => $name ) {
												// phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
												$extra = $num == $hours ? 'selected' : '';
												?>
												<option value="<?php echo $num; ?>" <?php echo $extra; ?>><?php echo $name; ?></option>
													<?php
											}
											do_action( 'visualizer_chart_schedules_spl', 'wp', $chart_id, 1 );
											?>
											</select>

											<input type="button" id="filter-chart-button" class="button button-secondary show-chart-toggle" value="<?php echo $bttn_label; ?>" data-current="chart" data-t-filter="<?php _e( 'Show Chart', 'visualizer' ); ?>" data-t-chart="<?php echo $bttn_label; ?>">
											<input type="button" id="db-filter-save-button" class="button button-primary" value="<?php _e( 'Save Schedule', 'visualizer' ); ?>">
											<?php echo apply_filters( 'visualizer_pro_upsell', '', 'db-query' ); ?>
										</form>
										<?php echo apply_filters( 'visualizer_pro_upsell', '', 'import-wp' ); ?>
									</div>
								</div>
							</li>

							<?php
								$save_query = add_query_arg(
									array(
										'action' => Visualizer_Plugin::ACTION_SAVE_DB_QUERY,
										'security'  => wp_create_nonce( Visualizer_Plugin::ACTION_SAVE_DB_QUERY . Visualizer_Plugin::VERSION ),
										'chart'  => $chart_id,
									), admin_url( 'admin-ajax.php' )
								);
							?>
							<!-- import from db -->
							<li class="viz-group visualizer_source_query <?php echo apply_filters( 'visualizer_pro_upsell_class', 'only-pro-feature', 'db-query' ); ?>">
							<h2 class="viz-group-title viz-sub-group"><?php _e( 'Import from database', 'visualizer' ); ?><span
								class="dashicons dashicons-lock"></span></h2>
							<div class="viz-group-content edit-data-content">
							<div>
								<p class="viz-group-description"><?php _e( 'You can import data from the database here.', 'visualizer' ); ?></p>
								<form id="vz-db-wizard" action="<?php echo $save_query; ?>" method="post" target="thehole">
									<p class="viz-group-description"><?php _e( 'How often do you want to refresh the data from the database.', 'visualizer' ); ?></p>
									<select name="refresh" id="vz-db-import-time" class="visualizer-select">
									<?php
									$bttn_label = 'visualizer_source_query' === $source_of_chart ? __( 'Modify Query', 'visualizer' ) : __( 'Create Query', 'visualizer' );
									$hours     = get_post_meta( $chart_id, Visualizer_Plugin::CF_DB_SCHEDULE, true );
									$schedules = apply_filters(
										'visualizer_chart_schedules', array(
											'-1' => __( 'One-time', 'visualizer' ),
										),
										'db',
										$chart_id
									);
									foreach ( $schedules as $num => $name ) {
										// phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
										$extra = $num == $hours ? 'selected' : '';
										?>
										<option value="<?php echo $num; ?>" <?php echo $extra; ?>><?php echo $name; ?></option>
											<?php
									}
									do_action( 'visualizer_chart_schedules_spl', 'db', $chart_id, 1 );
									?>
									</select>
									<input type="hidden" name="params" id="viz-db-wizard-params">

									<input type="button" id="db-chart-button" class="button button-secondary show-chart-toggle" value="<?php echo $bttn_label; ?>" data-current="chart" data-t-filter="<?php _e( 'Show Chart', 'visualizer' ); ?>" data-t-chart="<?php echo $bttn_label; ?>">
									<input type="button" id="db-chart-save-button" class="button button-primary" value="<?php _e( 'Save Schedule', 'visualizer' ); ?>">
									<?php echo apply_filters( 'visualizer_pro_upsell', '', 'db-query' ); ?>
								</form>
							</div>
							</div>
							</li>

							<!-- manual -->
							<li class="viz-group visualizer_source_manual">
								<h2 class="viz-group-title viz-sub-group visualizer-editor-tab" data-current="chart"><?php _e( 'Manual Data', 'visualizer' ); ?>
									<span class="dashicons dashicons-lock"></span>
								</h2>
								<div class="viz-group-content edit-data-content">
									<form id="editor-form" action="<?php echo $upload_link; ?>" method="post" target="thehole">
										<input type="hidden" id="chart-data" name="chart_data">
										<input type="hidden" id="chart-data-src" name="chart_data_src">

										<?php if ( Visualizer_Module::can_show_feature( 'simple-editor' ) ) { ?>
										<div>
											<p class="viz-group-description viz-editor-selection">
												<?php _e( 'Use the', 'visualizer' ); ?>
												<select name="editor-type" id="viz-editor-type">
													<?php
													if ( empty( $editor_type ) ) {
														$editor_type = Visualizer_Module::is_pro() ? 'excel' : 'text';
													}
													foreach ( apply_filters( 'visualizer_editors', array( 'text' => __( 'Text', 'visualizer' ), 'table' => __( 'Simple', 'visualizer' ) ) ) as $e_type => $e_label ) {
														?>
													<option value="<?php echo $e_type; ?>" <?php selected( $editor_type, $e_type ); ?> ><?php echo $e_label; ?></option>
													<?php } ?>
												</select>
												<?php _e( 'editor to manually edit the chart data.', 'visualizer' ); ?>
											</p>
											<input type="button" id="editor-undo" class="button button-secondary" style="display: none" value="<?php _e( 'Undo Changes', 'visualizer' ); ?>">
											<input type="button" id="editor-button" class="button button-primary "
												   value="<?php _e( 'Edit Data', 'visualizer' ); ?>" data-current="chart"
												   data-t-editor="<?php _e( 'Show Chart', 'visualizer' ); ?>"
												   data-t-chart="<?php _e( 'Edit Data', 'visualizer' ); ?>"
											>
											<p class="viz-group-description viz-info-msg"><?php echo sprintf( __( 'Please make sure you click \'Show Chart\' before you save the chart.', 'visualizer' ) ); ?></p>
										</div>
										<?php } else { ?>
											<input type="button" id="editor-undo" class="button button-secondary" style="display: none" value="<?php _e( 'Undo Changes', 'visualizer' ); ?>">
											<input type="button" id="editor-chart-button" class="button button-primary "
												   value="<?php _e( 'View Editor', 'visualizer' ); ?>" data-current="chart"
												   data-t-editor="<?php _e( 'Show Chart', 'visualizer' ); ?>"
												   data-t-chart="<?php _e( 'View Editor', 'visualizer' ); ?>"
											>
											<p class="viz-group-description viz-info-msg"><?php echo sprintf( __( 'Please make sure you click \'Show Chart\' before you save the chart.', 'visualizer' ) ); ?></p>
										<?php } ?>
									</form>
								</div>
							</li>
						</ul>
						</li>
					</ul>
			</ul>

			<span id="visualizer-chart-id" data-id="<?php echo $chart_id; ?>" data-chart-source="<?php echo esc_attr( $source_of_chart ); ?>" data-chart-type="<?php echo esc_attr( $type ); ?>" data-chart-lib="<?php echo esc_attr( $lib ); ?>"></span>
			<iframe id="thehole" name="thehole"></iframe>
		<?php
	}
}
