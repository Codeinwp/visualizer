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
	<ul class="group-wrapper">
	    <li class="group">
		    <h2 class="group-title main-group"><?php _e( 'Chart Source', 'visualizer' );?></h2>
			<ul class="group-content">
		        <ul class="group-wrapper">
		            <li class="group">
						<h2 class="group-title sub-group visualizer-src-tab"><?php _e( 'Create Chart From File', 'visualizer' );?></h2>
		                <ul class="group-content">
				            <p class="group-description"><?php esc_html_e( 'Select and upload your data CSV file here. The first row of the CSV file should contain the column headings. The second one should contain series type (string, number, boolean, date, datetime, timeofday).', 'visualizer' );?></p>
							<p class="group-description"><?php _e( sprintf( __( 'If you are unsure about how to format your data CSV then please take a look at this sample: %1$s or read how you can add Google spreadsheet in following %1$sarticle%1$s', 'visualizer' ), '<a href="' . VISUALIZER_ABSURL . 'samples/' . $this->type . '.csv" target="_blank">' . $this->type . '.csv</a>', '<a href="https://github.com/madpixelslabs/visualizer/wiki/How-can-I-populate-data-from-Google-Spreadsheet%3F" target="_blank">', '</a>' ) );?></p>
							<form id="csv-file-form" action="<?php echo $upload_link?>" method="post" target="thehole" enctype="multipart/form-data">
						        <input type="hidden" id="remote-data" name="remote_data">
								<div class="">
									<input type="file" id="csv-file" name="local_data">
									<?php esc_attr_e( 'From Computer', 'visualizer' );?>
								</div>
								<input type="button" class="view-csv-file" value="<?php _e( 'View', 'visualizer' );?>">
							</form>
						</ul>
					</li>
		            <li class="group">
						<h2 class="group-title sub-group visualizer-src-tab"><?php _e( 'Create Chart From URL', 'visualizer' );?></h2>
		                <ul class="group-content">
							<form id="remote-file-form" action="<?php echo $upload_link?>" method="post" target="thehole" enctype="multipart/form-data">
								<div class="remote-file-section">
									<input type="url" id="remote-data" name="remote_data" placeholder="<?php esc_html_e( 'Please enter the URL of CSV file:', 'visualizer' );?>">
									<div class="<?php echo defined( 'Visualizer_Pro' ) ? '' : 'just-on-pro'?>"><?php _e( 'Synchronize each hour', 'visualizer' );?><input type="checkbox" id="remote-sync" name="remote-sync" value="1"></div>
								</div>
								<input type="button" class="view-remote-file" value="<?php _e( 'View', 'visualizer' );?>">
							</form>
						</ul>
					</li>
		            <li class="group">
						<h2 class="group-title sub-group visualizer-editor-tab" data-current="chart"><?php _e( 'Add data from editor', 'visualizer' );?></h2>
		                <ul class="group-content">
<?php
if ( defined( 'Visualizer_Pro' ) ) {
	global $Visualizer_Pro;
	$Visualizer_Pro->_addFormElements( $upload_link );
} else {
	// Added by Ash/Upwork
	echo '<div class="just-on-pro"> </div>';
}
?>
						</ul>
					</li>
			</ul>
		</li>
	</ul>
	<ul class="group-wrapper">
		<li class="group">
			<h2 class="group-title main-group"><?php _e( 'Chart Settings', 'visualizer' );?></h2>
			<ul class="group-content">
				<form id="settings-form" action="<?php echo add_query_arg( 'nonce', wp_create_nonce() );?>" method="post">
				<?php echo $this->sidebar;?>
				</form>
			</ul>
		</li>
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
