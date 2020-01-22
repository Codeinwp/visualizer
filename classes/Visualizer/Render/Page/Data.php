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
 * Renders chart data setup page.
 *
 * @category Visualizer
 * @package Render
 * @subpackage Page
 *
 * @since 1.0.0
 */
class Visualizer_Render_Page_Data extends Visualizer_Render_Page {

	/**
	 * Renders page content.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _renderContent() {
		// Added by Ash/Upwork
		if ( Visualizer_Module::can_show_feature( 'simple-editor' ) ) {
			Visualizer_Render_Layout::show( 'simple-editor-screen', $this->chart->ID );
		}

		if ( Visualizer_Module::is_pro() ) {
			do_action( 'visualizer_add_editor_etc', $this->chart->ID );

			if ( Visualizer_Module::is_pro() && Visualizer_Module::is_pro_older_than( '1.9.0' ) ) {
				global $Visualizer_Pro;
				$Visualizer_Pro->_addEditor( $this->chart->ID );
				if ( method_exists( $Visualizer_Pro, '_addFilterWizard' ) ) {
					$Visualizer_Pro->_addFilterWizard( $this->chart->ID );
				}
			}
		}

		$this->add_additional_content();

		// Added by Ash/Upwork
		echo '<div id="canvas">';
		echo '<img src="', VISUALIZER_ABSURL, 'images/ajax-loader.gif" class="loader">';
		echo '</div>';
		echo $this->custom_css;
	}

	/**
	 * Renders sidebar content.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _renderSidebarContent() {
		$upload_link = add_query_arg(
			array(
				'action' => Visualizer_Plugin::ACTION_UPLOAD_DATA,
				'nonce'  => wp_create_nonce(),
				'chart'  => $this->chart->ID,
			),
			admin_url( 'admin-ajax.php' )
		);

		// this will allow us to open the correct source tab by default.
		$source_of_chart    = strtolower( get_post_meta( $this->chart->ID, Visualizer_Plugin::CF_SOURCE, true ) );
		// both import from wp and import from db have the same source so we need to differentiate.
		$filter_config      = get_post_meta( $this->chart->ID, Visualizer_Plugin::CF_FILTER_CONFIG, true );
		// if filter config is present, then its import from wp.
		if ( ! empty( $filter_config ) ) {
			$source_of_chart .= '_wp';
		}
		$editor_type    = get_post_meta( $this->chart->ID, Visualizer_Plugin::CF_EDITOR, true );
		if ( $editor_type ) {
			$source_of_chart = 'visualizer_source_manual';
		}

		$type               = get_post_meta( $this->chart->ID, Visualizer_Plugin::CF_CHART_TYPE, true );
		$lib               = get_post_meta( $this->chart->ID, Visualizer_Plugin::CF_CHART_LIBRARY, true );
		?>
		<span id="visualizer-chart-id" data-id="<?php echo $this->chart->ID; ?>" data-chart-source="<?php echo esc_attr( $source_of_chart ); ?>" data-chart-type="<?php echo esc_attr( $type ); ?>" data-chart-lib="<?php echo esc_attr( $lib ); ?>"></span>
		<iframe id="thehole" name="thehole"></iframe>
		<ul class="viz-group-wrapper full-height">
			<li class="viz-group viz-group-category open" id="vz-chart-source">
				<div class="viz-group-header">
					<h2 class="viz-group-title viz-main-group"><?php _e( 'Chart Data', 'visualizer' ); ?></h2>
				</div>
				<ul class="viz-group-content">
					<ul class="viz-group-wrapper">
						<!-- import from file -->
						<li class="viz-group visualizer_source_csv">
							<h2 class="viz-group-title viz-sub-group visualizer-src-tab"><?php _e( 'Import data from file', 'visualizer' ); ?></h2>
							<div class="viz-group-content">
								<p class="viz-group-description"><?php esc_html_e( 'Select and upload your data CSV file here. The first row of the CSV file should contain the column headings. The second one should contain series type (string, number, boolean, date, datetime, timeofday).', 'visualizer' ); ?></p>
								<p class="viz-group-description viz-info-msg"><b><?php echo sprintf( __( 'If you are unsure about how to format your data CSV then please take a look at this sample: %1$s %2$s%3$s. If you are using non-English characters, please make sure you save the file in UTF-8 encoding.', 'visualizer' ), '<a href="' . VISUALIZER_ABSURL . 'samples/' . $this->type . '.csv" target="_blank">', $this->type, '.csv</a>' ); ?></b></p>
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
										<p class="viz-group-description viz-info-msg"><b><?php echo sprintf( __( 'If you are unsure about how to format your data CSV then please take a look at this sample: %1$s %2$s%3$s. If you are using non-English characters, please make sure you save the file in UTF-8 encoding.', 'visualizer' ), '<a href="' . VISUALIZER_ABSURL . 'samples/' . $this->type . '.csv" target="_blank">', $this->type, '.csv</a>' ); ?></b></p>
										<form id="vz-one-time-import" action="<?php echo $upload_link; ?>" method="post"
											  target="thehole" enctype="multipart/form-data">
											<div class="remote-file-section">
												<input type="url" id="vz-schedule-url" name="remote_data" value="<?php echo esc_attr( get_post_meta( $this->chart->ID, Visualizer_Plugin::CF_CHART_URL, true ) ); ?>" placeholder="<?php esc_html_e( 'Please enter the URL of CSV file', 'visualizer' ); ?>" class="visualizer-input visualizer-remote-url">
											</div>
											<select name="vz-import-time" id="vz-import-time" class="visualizer-select">
											<?php
											$hours     = get_post_meta( $this->chart->ID, Visualizer_Plugin::CF_CHART_SCHEDULE, true );
											$schedules = apply_filters(
												'visualizer_chart_schedules', array(
													'-1' => __( 'One-time', 'visualizer' ),
												),
												'csv',
												$this->chart->ID
											);
											foreach ( $schedules as $num => $name ) {
												// phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
												$extra = $num == $hours ? 'selected' : '';
												?>
												<option value="<?php echo $num; ?>" <?php echo $extra; ?>><?php echo $name; ?></option>
												<?php
											}
												do_action( 'visualizer_chart_schedules_spl', 'csv', $this->chart->ID, 1 );
											?>
											</select>

											<input type="button" id="view-remote-file" class="button <?php echo Visualizer_Module::is_pro() ? 'button-secondary' : 'button-primary'; ?>" value="<?php _e( 'Import', 'visualizer' ); ?>">
											<?php
											if ( Visualizer_Module::is_pro() ) {
												?>
											<input type="button" id="vz-save-schedule" class="button button-primary" value="<?php _e( 'Save schedule', 'visualizer' ); ?>">
												<?php
											}
											?>
										</form>
									</div>
								</li>
								<!-- import from json url -->
								<li class="viz-subsection">
								<span class="viz-section-title visualizer_source_json"><?php _e( 'Import from JSON', 'visualizer' ); ?>
									<span class="dashicons dashicons-lock"></span></span>
									<div class="viz-section-items section-items">
										<p class="viz-group-description"><?php _e( 'You can choose here to import/synchronize your chart data with a remote JSON source. For more info check <a href="https://docs.themeisle.com/article/1052-how-to-generate-charts-from-json-data-rest-endpoints" target="_blank" >this</a> tutorial', 'visualizer' ); ?></p>
										<form id="vz-import-json" action="<?php echo $upload_link; ?>" method="post" target="thehole" enctype="multipart/form-data">
											<div class="remote-file-section">
													<?php
													$bttn_label = 'visualizer_source_json' === $source_of_chart ? __( 'Modify Parameters', 'visualizer' ) : __( 'Create Parameters', 'visualizer' );
													if ( Visualizer_Module::is_pro() ) {
														?>
												<p class="viz-group-description"><?php _e( 'How often do you want to check the URL', 'visualizer' ); ?></p>
												<select name="time" id="vz-json-time" class="visualizer-select json-form-element" data-chart="<?php echo $this->chart->ID; ?>">
														<?php
														$hours     = get_post_meta( $this->chart->ID, Visualizer_Plugin::CF_JSON_SCHEDULE, true );
														$schedules = apply_filters(
															'visualizer_chart_schedules', array(
																'-1' => __( 'One-time', 'visualizer' ),
															),
															'json',
															$this->chart->ID
														);
														foreach ( $schedules as $num => $name ) {
															// phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
															$extra = $num == $hours ? 'selected' : '';
															?>
															<option value="<?php echo $num; ?>" <?php echo $extra; ?>><?php echo $name; ?></option>
															<?php
														}
														do_action( 'visualizer_chart_schedules_spl', 'json', $this->chart->ID, 1 );
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
									'chart'  => $this->chart->ID,
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
										$hours     = get_post_meta( $this->chart->ID, Visualizer_Plugin::CF_DB_SCHEDULE, true );
										$schedules = apply_filters(
											'visualizer_chart_schedules', array(
												'-1' => __( 'One-time', 'visualizer' ),
											),
											'wp',
											$this->chart->ID
										);
										foreach ( $schedules as $num => $name ) {
											// phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
											$extra = $num == $hours ? 'selected' : '';
											?>
											<option value="<?php echo $num; ?>" <?php echo $extra; ?>><?php echo $name; ?></option>
												<?php
										}
										do_action( 'visualizer_chart_schedules_spl', 'wp', $this->chart->ID, 1 );
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
									'chart'  => $this->chart->ID,
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
								$hours     = get_post_meta( $this->chart->ID, Visualizer_Plugin::CF_DB_SCHEDULE, true );
								$schedules = apply_filters(
									'visualizer_chart_schedules', array(
										'-1' => __( 'One-time', 'visualizer' ),
									),
									'db',
									$this->chart->ID
								);
								foreach ( $schedules as $num => $name ) {
									// phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
									$extra = $num == $hours ? 'selected' : '';
									?>
									<option value="<?php echo $num; ?>" <?php echo $extra; ?>><?php echo $name; ?></option>
										<?php
								}
								do_action( 'visualizer_chart_schedules_spl', 'db', $this->chart->ID, 1 );
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
			<li class="viz-group viz-group-category bottom-fixed sidebar-footer-link" id="vz-chart-settings">
				<h2><span class="dashicons dashicons-admin-tools"></span><?php _e( 'Advanced', 'visualizer' ); ?></h2>
				<div class="viz-group-header">
					<button class="customize-section-back" tabindex="0"></button>
					<h3 class="viz-group-title viz-main-group"><?php _e( 'Chart Settings', 'visualizer' ); ?></h3>
				</div>
				<ul class="viz-group-content">
					<form id="settings-form" action="<?php echo add_query_arg( 'nonce', wp_create_nonce() ); ?>"
						  method="post">
						<?php echo $this->sidebar; ?>
						<input type="hidden" name="save" value="1">
					</form>
					<form id="cancel-form" action="<?php echo add_query_arg( 'nonce', wp_create_nonce() ); ?>" method="post">
						<input type="hidden" name="cancel" value="1">
					</form>
				</ul>
			</li>

			<li class="viz-group viz-group-category bottom-fixed sidebar-footer-link" id="vz-chart-docs">
				<h2><span class="dashicons dashicons-editor-help"></span><a href="<?php echo VISUALIZER_MAIN_DOC; ?>" target="_blank"><?php _e( 'Docs', 'visualizer' ); ?></a></h2>
			</li>

			<?php $this->getPermissionsLink( $this->chart->ID ); ?>

			<li class="viz-group bottom-fixed" id="vz-chart-copyright">Visualizer &copy; 
			<?php
				// phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date, WordPress.DateTime.CurrentTimeTimestamp.Requested
				echo date( 'Y', current_time( 'timestamp' ) );
			?>
			</li>
		</ul>
		<?php
		// changed by Ash/Upwork
	}

	/**
	 * Generates the permissions link.
	 *
	 * @access private
	 * @param int $id The chart id.
	 */
	private function getPermissionsLink( $id ) {
		$permissions    = apply_filters( 'visualizer_pro_get_permissions', null, $id );
		if ( $permissions ) {
			foreach ( $permissions as $k => $v ) {
				$this->$k   = $v;
			}
		}
		?>
		<li class="viz-group viz-group-category bottom-fixed sidebar-footer-link" id="vz-chart-permissions">
			<h2><span class="dashicons dashicons-admin-users"></span><?php _e( 'Permissions', 'visualizer' ); ?></h2>
			<div class="viz-group-header">
				<button class="customize-section-back" tabindex="0"></button>
				<h3 class="viz-group-title viz-main-group"><?php _e( 'Chart Settings', 'visualizer' ); ?></h3>
			</div>
			<ul class="viz-group-content">
				<form id="permissions-form" target="thehole" action="
				<?php
				echo add_query_arg(
					array(
						'nonce' => wp_create_nonce(),
						'tab' => 'permissions',
					),
					remove_query_arg( 'tab', $_SERVER['REQUEST_URI'] )
				);
				?>
" method="post">
					<?php $this->permissionsSidebar(); ?>
				</form>
			</ul>
		</li>
		<?php
	}

	/**
	 * Generates the permissions form.
	 *
	 * @access private
	 */
	private function permissionsSidebar() {
		// ignore for unit tests because Travis throws the error "Indirect modification of overloaded property Visualizer_Render_Page_Data::$permissions has no effect".
		if ( defined( 'WP_TESTS_DOMAIN' ) ) {
			return;
		}
		Visualizer_Render_Sidebar::_renderGroupStart(
			esc_html__( 'Who can see this chart?', 'visualizer' ) . '<span
										class="dashicons dashicons-lock"></span>',
			'',
			'viz-chart-perm-view ' . apply_filters( 'visualizer_pro_upsell_class', 'only-pro-feature', 'chart-permissions' )
		);
			Visualizer_Render_Sidebar::_renderSectionStart();
				Visualizer_Render_Sidebar::_renderSectionDescription( esc_html__( 'Select who can view the chart on the front-end.', 'visualizer' ) );

		if ( ! isset( $this->permissions['read'] ) ) {
			$this->permissions['read'] = 'all';
		}

				Visualizer_Render_Sidebar::_renderSelectItem(
					'',
					'permissions[read]',
					$this->permissions['read'],
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

				$options    = apply_filters( 'visualizer_pro_get_permissions_data', array(), isset( $this->permissions['read'] ) ? $this->permissions['read'] : 'roles' );

				Visualizer_Render_Sidebar::_renderSelectItem(
					'',
					'permissions[read-specific][]',
					isset( $this->permissions['read-specific'] ) ? $this->permissions['read-specific'] : array(),
					$options,
					'',
					true,
					array( 'visualizer-permission', 'visualizer-permission-specific', 'visualizer-permission-read-specific' )
				);
			Visualizer_Render_Sidebar::_renderSectionEnd( apply_filters( 'visualizer_pro_upsell', 'only-pro-feature', 'chart-permissions' ) );
		Visualizer_Render_Sidebar::_renderGroupEnd();

		Visualizer_Render_Sidebar::_renderGroupStart(
			esc_html__( 'Who can edit this chart?', 'visualizer' ) . '<span
										class="dashicons dashicons-lock"></span>',
			'',
			'viz-chart-perm-edit ' . apply_filters( 'visualizer_pro_upsell_class', 'only-pro-feature', 'chart-permissions' )
		);
			Visualizer_Render_Sidebar::_renderSectionStart();
				Visualizer_Render_Sidebar::_renderSectionDescription( esc_html__( 'Select who can edit the chart on the front-end.', 'visualizer' ) );

		if ( ! isset( $this->permissions['edit'] ) ) {
			$this->permissions['edit'] = 'roles';
			$this->permissions['edit-specific'] = 'administrator';
		}

				Visualizer_Render_Sidebar::_renderSelectItem(
					'',
					'permissions[edit]',
					$this->permissions['edit'],
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

				$options    = apply_filters( 'visualizer_pro_get_permissions_data', array(), isset( $this->permissions['edit'] ) ? $this->permissions['edit'] : 'roles' );

				Visualizer_Render_Sidebar::_renderSelectItem(
					'',
					'permissions[edit-specific][]',
					isset( $this->permissions['edit-specific'] ) ? $this->permissions['edit-specific'] : array(),
					$options,
					'',
					true,
					array( 'visualizer-permission', 'visualizer-permission-specific', 'visualizer-permission-edit-specific' )
				);
			Visualizer_Render_Sidebar::_renderSectionEnd( apply_filters( 'visualizer_pro_upsell', 'only-pro-feature', 'chart-permissions' ) );
		Visualizer_Render_Sidebar::_renderGroupEnd();

	}

	/**
	 * Renders toolbar content.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _renderToolbar() {
		// don't show back button at all.
		// NOTE: We can't be selective on the post_status here because when a new chart reaches the settings screen, its status changes to publish.
		if ( ! VISUALIZER_SKIP_CHART_TYPE_PAGE ) {
			echo '<div class="toolbar-div">';
			echo '<a class="button button-large" href="', add_query_arg( 'tab', 'types' ), '">';
			esc_html_e( 'Back', 'visualizer' );
			echo '</a>';
			echo '</div>';
		}
		echo '<input type="submit" id="settings-button" class="button button-primary button-large push-right" value="', $this->button, '">';
		if ( isset( $this->cancel_button ) ) {
			echo '<input type="submit" id="cancel-button" class="button button-secondary button-large push-right" value="', $this->cancel_button, '">';
		}
	}

	/**
	 * Renders the additional content.
	 *
	 * @access private
	 */
	private function add_additional_content() {
		$source = strtolower( get_post_meta( $this->chart->ID, Visualizer_Plugin::CF_SOURCE, true ) );
		$query = '';
		if ( 'visualizer_source_query' === $source ) {
			$query = get_post_meta( $this->chart->ID, Visualizer_Plugin::CF_DB_QUERY, true );
		}
		Visualizer_Render_Layout::show( 'db-query', $query, $this->chart->ID );
		Visualizer_Render_Layout::show( 'json-screen', $this->chart->ID );
	}

}
