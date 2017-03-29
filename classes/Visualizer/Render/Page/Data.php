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
		if ( defined( 'Visualizer_Pro' ) ) {
			global $Visualizer_Pro;
			$Visualizer_Pro->_addEditor();
		}
		// Added by Ash/Upwork
		echo '<div id="canvas">';
		echo '<img src="', VISUALIZER_ABSURL, 'images/ajax-loader.gif" class="loader">';
		echo '</div>';
	}

	/**
	 * Renders sidebar content.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _renderSidebarContent() {
		$upload_link = add_query_arg( array(
			'action' => Visualizer_Plugin::ACTION_UPLOAD_DATA,
			'nonce'  => wp_create_nonce(),
			'chart'  => $this->chart->ID,
		), admin_url( 'admin-ajax.php' ) );

		?>
		<iframe id="thehole" name="thehole"></iframe>
		<ul class="group-wrapper full-height">
			<li class="group group-category open" id="vz-chart-source">
				<div class="group-header">
					<h2 class="group-title main-group"><?php _e( 'Chart Data', 'visualizer' ); ?></h2>
				</div>
				<ul class="group-content">
					<ul class="group-wrapper">
						<li class="group">
							<h2 class="group-title sub-group visualizer-src-tab"><?php _e( 'Import data from file', 'visualizer' ); ?></h2>
							<div class="group-content">
								<p class="group-description"><?php esc_html_e( 'Select and upload your data CSV file here. The first row of the CSV file should contain the column headings. The second one should contain series type (string, number, boolean, date, datetime, timeofday).', 'visualizer' ); ?></p>
								<p class="group-description"><?php sprintf( __( 'If you are unsure about how to format your data CSV then please take a look at this sample: %1$s %2$s%3$s', 'visualizer' ), '<a href="' . VISUALIZER_ABSURL . 'samples/' . $this->type . '.csv" target="_blank">' , $this->type , '.csv</a>' ); ?></p>
								<form id="vz-csv-file-form" action="<?php echo $upload_link ?>" method="post"
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
						<li class="group">
							<h2 class="group-title sub-group visualizer-src-tab"><?php _e( 'Import data from URL', 'visualizer' ); ?></h2>
							<ul class="group-content">
								<li class="subsection">
									<span class="section-title"><?php _e( 'One time import', 'visualizer' ); ?></span>

									<div class="section-items">
										<p class="group-description"><?php _e( 'You can use this to import data from a remote CSV file. The first row of the CSV file should contain the column headings. The second one should contain series type (string, number, boolean, date, datetime, timeofday).', 'visualizer' ); ?> </p>
										<p class="group-description"> <?php _e( 'You can also import data from Google Spreadsheet, for more info check <a href="https://github.com/Codeinwp/visualizer/wiki/How-can-I-populate-data-from-Google-Spreadsheet%3F" target="_blank" >this</a> tutorial', 'visualizer' ); ?></p>
										<form id="vz-one-time-import" action="<?php echo $upload_link ?>" method="post"
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
								<li class="subsection <?php echo apply_filters( 'visualizer_pro_upsell_class','only-pro-feature' ); ?>">
								<span class="section-title"><?php _e( 'Schedule Import', 'visualizer' ); ?><span
											class="dashicons dashicons-lock"></span></span>
									<div class="section-items">
										<p class="group-description"><?php _e( 'You can choose here to synchronize your chart data with a remote CSV file.', 'visualizer' ); ?> </p>
										<p class="group-description"> <?php _e( 'You can also synchronize with your Google Spreadsheet file, for more info check <a href="https://github.com/Codeinwp/visualizer/wiki/How-can-I-populate-data-from-Google-Spreadsheet%3F" target="_blank" >this</a> tutorial', 'visualizer' ); ?></p>
										<p class="group-description"> <?php _e( 'We will update the chart data based on your time interval preference by overwritting the current data with the one from the URL.', 'visualizer' ); ?></p>
										<form id="vz-schedule-import" action=" " method="post"
											  target="thehole" enctype="multipart/form-data">
											<div class="remote-file-section">
												<input type="url" id="remote-data" name="remote_data"
													   placeholder="<?php esc_html_e( 'Please enter the URL of CSV file', 'visualizer' ); ?>"
													   class="visualizer-input">
												<p><?php _e( 'How often do you want to check the url', 'visualizer' ); ?></p>
												<select name="vz-import-time" id="vz-import-time"
														class="visualizer-select">
													<option value="3600"><?php _e( 'Each hour', 'visualizer' ); ?></option>
													<option value="43200"><?php _e( 'Each 12 hours', 'visualizer' ); ?></option>
													<option value="86400"><?php _e( 'Each day', 'visualizer' ); ?></option>
													<option value="259200"><?php _e( 'Each 3 days', 'visualizer' ); ?></option>
												</select>
											</div>
											<input type="button" id="vz-save-schedule" class="button button-primary"
												   value="<?php _e( 'Save schedule', 'visualizer' ); ?>">


											<?php echo apply_filters( 'visualizer_pro_upsell', '' ); ?>
										</form>
									</div>
								</li>
							</ul>
						</li>
						<li class="group <?php echo apply_filters( 'visualizer_pro_upsell_class','only-pro-feature' ); ?> ">
							<h2 class="group-title sub-group"
								data-current="chart"><?php _e( 'Import from other chart', 'visualizer' ); ?><span
										class="dashicons dashicons-lock"></span></h2>
							<div class="group-content edit-data-content">
								<div>
									<p class="group-description"><?php _e( 'You can import here data from your previously created charts', 'visualizer' ); ?></p>
									<form>
										<select name="vz-import-from-chart" id="vz-import-from-chart"
												class="visualizer-select">
											<option value="#"><?php _e( 'Chart #123', 'visualizer' ); ?></option>
										</select>
									</form>
									<?php echo apply_filters( 'visualizer_pro_upsell', '' ); ?>
								</div>
							</div>
						</li>
						<li class="group <?php echo apply_filters( 'visualizer_pro_upsell_class','only-pro-feature' ); ?>">
							<h2 class="group-title sub-group visualizer-editor-tab"
								data-current="chart"><?php _e( 'Edit current data', 'visualizer' ); ?><span
										class="dashicons dashicons-lock"></span></h2>
								<form id="editor-form" action="<?php echo $upload_link?>" method="post" target="thehole">
									<input type="hidden" id="chart-data" name="chart_data">
								</form>

							<div class="group-content edit-data-content">
								<div>
									<p class="group-description"><?php _e( 'You can manually edit the chart data using the spreadsheet like editor.', 'visualizer' ); ?></p>
									<input type="button" id="editor-chart-button" class="button button-primary "
										   value="<?php _e( 'View Editor', 'visualizer' ); ?>" data-current="chart" data-t-editor="<?php _e( 'Show Chart', 'visualizer' );?>" data-t-chart="<?php _e( 'View Editor', 'visualizer' );?>">

									<?php echo apply_filters( 'visualizer_pro_upsell', '' ); ?>
								</div>
							</div>
						</li>
					</ul>
					</li>
				</ul>
			<li class="group group-category bottom-fixed  " id="vz-chart-settings">
				<h2><?php _e( 'Advanced Settings', 'visualizer' ); ?></h2>
				<div class="group-header">
					<button class="customize-section-back" tabindex="0"></button>
					<h3 class="group-title main-group"><?php _e( 'Chart Settings', 'visualizer' ); ?></h3>
				</div>
				<ul class="group-content">
					<form id="settings-form" action="<?php echo add_query_arg( 'nonce', wp_create_nonce() ); ?>"
						  method="post">
						<?php echo $this->sidebar; ?>
					</form>
				</ul>
			</li>
			<li class=" group bottom-fixed" id="vz-chart-review">
				<a href="https://wordpress.org/support/plugin/visualizer/reviews/?filter=5#new-post"
				   target="_blank"><?php _e( 'Rate our plugin', 'visualizer' ); ?></a>
			</li>
			<li class="group bottom-fixed" id="vz-chart-copyright">Visualizer &copy; 2014</li>
		</ul>
		<?php
		// changed by Ash/Upwork
	}

	/**
	 * Renders toolbar content.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _renderToolbar() {
		// changed by Ash/Upwork
		echo '<div class="toolbar-div">';
		echo '<a class="button button-large" href="', add_query_arg( 'tab', 'types' ), '">';
		esc_html_e( 'Back', 'visualizer' );
		echo '</a>';
		echo '</div>';
		echo '<input type="submit" id="settings-button" class="button button-primary button-large push-right" value="', $this->button, '">';

		echo '</div>';

	}

}
