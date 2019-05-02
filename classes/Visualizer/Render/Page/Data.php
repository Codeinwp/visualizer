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
		if ( VISUALIZER_PRO ) {
			global $Visualizer_Pro;
			$Visualizer_Pro->_addEditor( $this->chart->ID );
			if ( method_exists( $Visualizer_Pro, '_addFilterWizard' ) ) {
				$Visualizer_Pro->_addFilterWizard( $this->chart->ID );
			}
		} else {
			Visualizer_Render_Layout::show( 'simple-editor-screen', $this->chart->ID );
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
		$type               = get_post_meta( $this->chart->ID, Visualizer_Plugin::CF_CHART_TYPE, true );
		?>
		<span id="visualizer-chart-id" data-id="<?php echo $this->chart->ID; ?>" data-chart-source="<?php echo $source_of_chart; ?>" data-chart-type="<?php echo $type; ?>"></span>
		<iframe id="thehole" name="thehole"></iframe>
		<ul class="viz-group-wrapper full-height">
			<li class="viz-group viz-group-category open" id="vz-chart-source">
				<div class="viz-group-header">
					<h2 class="viz-group-title viz-main-group"><?php _e( 'Chart Data', 'visualizer' ); ?></h2>
				</div>
				<ul class="viz-group-content">
					<ul class="viz-group-wrapper">
						<li class="viz-group visualizer_source_csv">
							<h2 class="viz-group-title viz-sub-group visualizer-src-tab"><?php _e( 'Import data from file', 'visualizer' ); ?></h2>
							<div class="viz-group-content">
								<p class="viz-group-description"><?php esc_html_e( 'Select and upload your data CSV file here. The first row of the CSV file should contain the column headings. The second one should contain series type (string, number, boolean, date, datetime, timeofday).', 'visualizer' ); ?></p>
								<p class="viz-group-description"><?php echo sprintf( __( 'If you are unsure about how to format your data CSV then please take a look at this sample: %1$s %2$s%3$s', 'visualizer' ), '<a href="' . VISUALIZER_ABSURL . 'samples/' . $this->type . '.csv" target="_blank">', $this->type, '.csv</a>' ); ?></p>
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
						<li class="viz-group visualizer-import-url visualizer_source_csv_remote visualizer_source_json">
							<h2 class="viz-group-title viz-sub-group visualizer-src-tab"><?php _e( 'Import data from URL', 'visualizer' ); ?></h2>
							<ul class="viz-group-content">
								<li class="viz-subsection">
									<span class="viz-section-title"><?php _e( 'One time import', 'visualizer' ); ?></span>

									<div class="viz-section-items section-items">
										<p class="viz-group-description"><?php _e( 'You can use this to import data from a remote CSV file. The first row of the CSV file should contain the column headings. The second one should contain series type (string, number, boolean, date, datetime, timeofday).', 'visualizer' ); ?> </p>
										<p class="viz-group-description"><?php echo sprintf( __( 'If you are unsure about how to format your data CSV then please take a look at this sample: %1$s %2$s%3$s', 'visualizer' ), '<a href="' . VISUALIZER_ABSURL . 'samples/' . $this->type . '.csv" target="_blank">', $this->type, '.csv</a>' ); ?></p>
										<p class="viz-group-description"> <?php _e( 'You can also import data from Google Spreadsheet, for more info check <a href="https://docs.themeisle.com/article/607-how-can-i-populate-data-from-google-spreadsheet" target="_blank" >this</a> tutorial', 'visualizer' ); ?></p>
										<form id="vz-one-time-import" action="<?php echo $upload_link; ?>" method="post"
											  target="thehole" enctype="multipart/form-data">
											<div class="remote-file-section">
												<input type="url" id="remote-data" name="remote_data"
													   placeholder="<?php esc_html_e( 'Please enter the URL of CSV file', 'visualizer' ); ?>"
													   class="visualizer-input">

											</div>
											<input type="button" id="view-remote-file" class="button button-primary"
												   value="<?php _e( 'Import', 'visualizer' ); ?>">
										</form>
									</div>
								</li>
								<li class="viz-subsection <?php echo apply_filters( 'visualizer_pro_upsell_class', 'only-pro-feature', 'schedule-chart' ); ?>">
								<span class="viz-section-title visualizer-import-url-schedule"><?php _e( 'Schedule Import', 'visualizer' ); ?>
									<span
											class="dashicons dashicons-lock"></span></span>
									<div class="viz-section-items section-items">
										<p class="viz-group-description"><?php _e( 'You can choose here to synchronize your chart data with a remote CSV file.', 'visualizer' ); ?> </p>
										<p class="viz-group-description"> <?php _e( 'You can also synchronize with your Google Spreadsheet file, for more info check <a href="https://docs.themeisle.com/article/607-how-can-i-populate-data-from-google-spreadsheet" target="_blank" >this</a> tutorial', 'visualizer' ); ?></p>
										<p class="viz-group-description"> <?php _e( 'We will update the chart data based on your time interval preference by overwriting the current data with the one from the URL.', 'visualizer' ); ?></p>
										<form id="vz-schedule-import" action="<?php echo $upload_link; ?>" method="post"
											  target="thehole" enctype="multipart/form-data">
											<div class="remote-file-section">
												<input type="url" id="vz-schedule-url" name="remote_data"
													   value="<?php echo get_post_meta( $this->chart->ID, Visualizer_Plugin::CF_CHART_URL, true ); ?>"
													   placeholder="<?php esc_html_e( 'Please enter the URL of CSV file', 'visualizer' ); ?>"
													   class="visualizer-input visualizer-remote-url">
												<p class="viz-group-description"><?php _e( 'How often do you want to check the url', 'visualizer' ); ?></p>
												<select name="vz-import-time" id="vz-import-time"
														class="visualizer-select">
													<?php
													$hours     = get_post_meta( $this->chart->ID, Visualizer_Plugin::CF_CHART_SCHEDULE, true );
													$schedules = apply_filters(
														'visualizer_chart_schedules', array(
															'1'  => __( 'Each hour', 'visualizer' ),
															'12' => __( 'Each 12 hours', 'visualizer' ),
															'24' => __( 'Each day', 'visualizer' ),
															'72' => __( 'Each 3 days', 'visualizer' ),
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
													?>
												</select>
											</div>
											<input type="button" id="vz-save-schedule" class="button button-primary"
												   value="<?php _e( 'Save schedule', 'visualizer' ); ?>">

											<?php echo apply_filters( 'visualizer_pro_upsell', '', 'schedule-chart' ); ?>
										</form>
									</div>
								</li>

								<li class="viz-subsection">
								<span class="viz-section-title visualizer_source_json"><?php _e( 'Import from JSON/REST', 'visualizer' ); ?>
									<span class="dashicons dashicons-lock"></span></span>
									<div class="viz-section-items section-items">
										<p class="viz-group-description"><?php _e( 'You can choose here to import/synchronize your chart data with a remote JSON/REST source. For more info check <a href="https://docs.themeisle.com/article/1052-how-to-generate-charts-from-json-data-rest-endpoints" target="_blank" >this</a> tutorial', 'visualizer' ); ?></p>
										<form id="vz-import-json" action="<?php echo $upload_link; ?>" method="post" target="thehole" enctype="multipart/form-data">
											<div class="remote-file-section">
													<?php
													$bttn_label = 'visualizer_source_json' === $source_of_chart ? __( 'Modify Parameters', 'visualizer' ) : __( 'Create Parameters', 'visualizer' );
													if ( VISUALIZER_PRO ) {
														?>
												<p class="viz-group-description"><?php _e( 'How often do you want to check the URL', 'visualizer' ); ?></p>
												<select name="vz-json-time" id="vz-json-time" class="visualizer-select" data-chart="<?php echo $this->chart->ID; ?>">
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
														?>
													</select>
														<?php
													}
													?>
											</div>

											<input type="button" id="json-chart-button" class="button button-secondary "
											value="<?php echo $bttn_label; ?>" data-current="chart"
											data-t-filter="<?php _e( 'Show Chart', 'visualizer' ); ?>"
											data-t-chart="<?php echo $bttn_label; ?>">
											<?php
											if ( VISUALIZER_PRO ) {
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
						<li class="viz-group <?php echo apply_filters( 'visualizer_pro_upsell_class', 'only-pro-feature' ); ?>">
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
													'action' => ( VISUALIZER_PRO ) ? Visualizer_Pro::ACTION_FETCH_DATA : '',
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
												?>
												<option value="<?php echo $chart->ID; ?>"><?php echo empty( $settings['title'] ) ? '#' . $chart->ID : $settings['title']; ?></option>
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
						<li class="viz-group visualizer_source_query_wp <?php echo apply_filters( 'visualizer_pro_upsell_class', 'only-pro-feature', 'schedule-chart' ); ?> ">
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
											'visualizer_schedules', array(
												'0'  => __( 'Live', 'visualizer' ),
												'1'  => __( 'Each hour', 'visualizer' ),
												'12' => __( 'Each 12 hours', 'visualizer' ),
												'24' => __( 'Each day', 'visualizer' ),
												'72' => __( 'Each 3 days', 'visualizer' ),
											)
										);
										foreach ( $schedules as $num => $name ) {
											// phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
											$extra = $num == $hours ? 'selected' : '';
											?>
											<option value="<?php echo $num; ?>" <?php echo $extra; ?>><?php echo $name; ?></option>
												<?php
										}
										?>
										</select>

										<input type="button" id="filter-chart-button" class="button button-secondary" value="<?php echo $bttn_label; ?>" data-current="chart" data-t-filter="<?php _e( 'Show Chart', 'visualizer' ); ?>" data-t-chart="<?php echo $bttn_label; ?>">
										<input type="button" id="db-filter-save-button" class="button button-primary" value="<?php _e( 'Save Schedule', 'visualizer' ); ?>">
										<?php echo apply_filters( 'visualizer_pro_upsell', '', 'db-query' ); ?>
									</form>
									<?php echo apply_filters( 'visualizer_pro_upsell', '', 'schedule-chart' ); ?>
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
									'visualizer_schedules', array(
										'0'  => __( 'Live', 'visualizer' ),
										'1'  => __( 'Each hour', 'visualizer' ),
										'12' => __( 'Each 12 hours', 'visualizer' ),
										'24' => __( 'Each day', 'visualizer' ),
										'72' => __( 'Each 3 days', 'visualizer' ),
									)
								);
								foreach ( $schedules as $num => $name ) {
									// phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
									$extra = $num == $hours ? 'selected' : '';
									?>
									<option value="<?php echo $num; ?>" <?php echo $extra; ?>><?php echo $name; ?></option>
										<?php
								}
								?>
								</select>
								<input type="hidden" name="params" id="viz-db-wizard-params">

								<input type="button" id="db-chart-button" class="button button-secondary" value="<?php echo $bttn_label; ?>" data-current="chart" data-t-filter="<?php _e( 'Show Chart', 'visualizer' ); ?>" data-t-chart="<?php echo $bttn_label; ?>">
								<input type="button" id="db-chart-save-button" class="button button-primary" value="<?php _e( 'Save Schedule', 'visualizer' ); ?>">
								<?php echo apply_filters( 'visualizer_pro_upsell', '', 'db-query' ); ?>
							</form>
						</div>
						</div>
						</li>

						<?php
							// we will auto-open the manual data feature only when source is empty.
						?>
						<li class="viz-group <?php echo empty( $source_of_chart ) ? 'open' : ''; ?> ">
							<h2 class="viz-group-title viz-sub-group visualizer-editor-tab"
								data-current="chart"><?php _e( 'Manual Data', 'visualizer' ); ?><span
										class="dashicons dashicons-lock"></span></h2>
							<form id="editor-form" action="<?php echo $upload_link; ?>" method="post" target="thehole">
								<input type="hidden" id="chart-data" name="chart_data">
								<input type="hidden" id="chart-data-src" name="chart_data_src">
							</form>

							<div class="viz-group-content edit-data-content">
								<div>
									<p class="viz-group-description"><?php echo sprintf( __( 'You can manually edit the chart data using the %s editor.', 'visualizer' ), VISUALIZER_PRO ? 'spreadsheet like' : 'simple' ); ?></p>
									<?php if ( ! VISUALIZER_PRO ) { ?>
										<p class="viz-group-description simple-editor-type"><input type="checkbox" id="simple-editor-type" value="textarea"><label for="simple-editor-type"><?php _e( 'Use text area editor instead', 'visualizer' ); ?></label></p>
									<?php } ?>
									<input type="button" id="editor-chart-button" class="button button-primary "
										   value="<?php _e( 'View Editor', 'visualizer' ); ?>" data-current="chart"
										   data-t-editor="<?php _e( 'Show Chart', 'visualizer' ); ?>"
										   data-t-chart="<?php _e( 'View Editor', 'visualizer' ); ?>">
								</div>
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
				<h2><span class="dashicons dashicons-editor-help"></span><a href="https://docs.themeisle.com/category/657-visualizer" target="_blank"><?php _e( 'Docs', 'visualizer' ); ?></a></h2>
			</li>

			<?php $this->getPermissionsLink( $this->chart->ID ); ?>

			<li class="viz-group bottom-fixed" id="vz-chart-copyright">Visualizer &copy; <?php echo date( 'Y', current_time( 'timestamp' ) ); ?></li>
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
			apply_filters( 'visualizer_pro_upsell_class', 'only-pro-feature', 'chart-permissions' )
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
			apply_filters( 'visualizer_pro_upsell_class', 'only-pro-feature', 'chart-permissions' )
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
			echo '<input type="submit" id="cancel-button" class="button button-secondary button-large push-left" value="', $this->cancel_button, '">';
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
		Visualizer_Render_Layout::show( 'db-query', $query );
		Visualizer_Render_Layout::show( 'json-screen', $this->chart->ID );
	}

}
