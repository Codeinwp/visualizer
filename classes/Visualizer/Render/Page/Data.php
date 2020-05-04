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
		?>
		<div id="viz-tabs">
			<ul>
				<li><a href="#viz-tab-basic-content" id="viz-tab-basic"><?php _e( 'Source', 'visualizer' ); ?></a></li>
				<li><a href="#viz-tab-advanced-content" id="viz-tab-advanced"><?php _e( 'Settings', 'visualizer' ); ?></a></li>
				<li><a href="#viz-tab-help-content" id="viz-tab-help"><?php _e( 'Help', 'visualizer' ); ?></a></li>
			</ul>
			<div id="viz-tab-basic-content"><?php Visualizer_Render_Layout::show( 'tab-basic', $this->chart->ID ); ?></div>
			<div id="viz-tab-advanced-content"><?php Visualizer_Render_Layout::show( 'tab-advanced', $this->chart->ID, $this->sidebar ); ?></div>
			<div id="viz-tab-help-content"><?php Visualizer_Render_Layout::show( 'tab-help', $this->chart->ID ); ?></div>
		</div>

		<li class="viz-group bottom-fixed" id="vz-chart-copyright">
		Hate it? Love it? <a href="https://wordpress.org/support/plugin/visualizer/reviews/#new-post" target="_blank">Rate it!</a>
		<br/>
		Visualizer &copy; 
		<?php
			// phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date, WordPress.DateTime.CurrentTimeTimestamp.Requested
			echo date( 'Y', current_time( 'timestamp' ) );
		?>
		</li>
		<?php
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
