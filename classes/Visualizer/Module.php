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
 * Base class for all modules. Implements routine methods required by all modules.
 *
 * @category Visualizer
 * @package Module
 *
 * @since 1.0.0
 */
class Visualizer_Module {

	/**
	 * The instance of wpdb class.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @var wpdb
	 */
	protected $_wpdb = null;

	/**
	 * The plugin instance.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @var Visualizer_Plugin
	 */
	protected $_plugin = null;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @global wpdb $wpdb Current database connection.
	 *
	 * @access public
	 * @param Visualizer_Plugin $plugin The instance of the plugin.
	 */
	public function __construct( Visualizer_Plugin $plugin ) {
		global $wpdb;

		$this->_wpdb = $wpdb;
		$this->_plugin = $plugin;

		$this->_addFilter( Visualizer_Plugin::FILTER_UNDO_REVISIONS, 'undoRevisions', 10, 2 );
		$this->_addFilter( Visualizer_Plugin::FILTER_HANDLE_REVISIONS, 'handleExistingRevisions', 10, 2 );

	}

	/**
	 * Registers an action hook.
	 *
	 * @since 1.0.0
	 * @uses add_action() To register action hook.
	 *
	 * @access protected
	 * @param string $tag The name of the action to which the $method is hooked.
	 * @param string $method The name of the method to be called.
	 * @param bool   $methodClass The root of the method.
	 * @param int    $priority optional. Used to specify the order in which the functions associated with a particular action are executed (default: 10). Lower numbers correspond with earlier execution, and functions with the same priority are executed in the order in which they were added to the action.
	 * @param int    $accepted_args optional. The number of arguments the function accept (default 1).
	 * @return Visualizer_Module
	 */
	protected function _addAction( $tag, $method, $methodClass = null, $priority = 10, $accepted_args = 1 ) {
		add_action( $tag, array( $methodClass ? $methodClass : $this, $method ), $priority, $accepted_args );
		return $this;
	}

	/**
	 * Registers AJAX action hook.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param string  $tag The name of the AJAX action to which the $method is hooked.
	 * @param string  $method Optional. The name of the method to be called. If the name of the method is not provided, tag name will be used as method name.
	 * @param bool    $methodClass The root of the method.
	 * @param boolean $private Optional. Determines if we should register hook for logged in users.
	 * @param boolean $public Optional. Determines if we should register hook for not logged in users.
	 * @return Visualizer_Module
	 */
	protected function _addAjaxAction( $tag, $method = '', $methodClass = null, $private = true, $public = false ) {
		if ( $private ) {
			$this->_addAction( 'wp_ajax_' . $tag, $method, $methodClass );
		}

		if ( $public ) {
			$this->_addAction( 'wp_ajax_nopriv_' . $tag, $method, $methodClass );
		}

		return $this;
	}

	/**
	 * Registers a filter hook.
	 *
	 * @since 1.0.0
	 * @uses add_filter() To register filter hook.
	 *
	 * @access protected
	 * @param string $tag The name of the filter to hook the $method to.
	 * @param type   $method The name of the method to be called when the filter is applied.
	 * @param int    $priority optional. Used to specify the order in which the functions associated with a particular action are executed (default: 10). Lower numbers correspond with earlier execution, and functions with the same priority are executed in the order in which they were added to the action.
	 * @param int    $accepted_args optional. The number of arguments the function accept (default 1).
	 * @return Visualizer_Module
	 */
	protected function _addFilter( $tag, $method, $priority = 10, $accepted_args = 1 ) {
		add_filter( $tag, array( $this, $method ), $priority, $accepted_args );
		return $this;
	}

	/**
	 * Registers a hook for shortcode tag.
	 *
	 * @since 1.0.0
	 * @uses add_shortcode() To register shortcode hook.
	 *
	 * @access protected
	 * @param string $tag Shortcode tag to be searched in post content.
	 * @param string $method Hook to run when shortcode is found.
	 * @return Visualizer_Module
	 */
	protected function _addShortcode( $tag, $method ) {
		add_shortcode( $tag, array( $this, $method ) );
		return $this;
	}

	/**
	 * Extracts the data for a chart and prepares it for the given type.
	 *
	 * @access public
	 * @param int    $chart_id The chart id.
	 * @param string $type The exported type.
	 */
	public function _getDataAs( $chart_id, $type ) {
		$final       = null;
		$success    = false;
		if ( $chart_id ) {
			$chart   = get_post( $chart_id );
			$success = $chart && $chart->post_type == Visualizer_Plugin::CPT_VISUALIZER;
		}
		if ( $success ) {
			$settings = get_post_meta( $chart_id, Visualizer_Plugin::CF_SETTINGS, true );
			$rows   = array();
			$series = get_post_meta( $chart_id, Visualizer_Plugin::CF_SERIES, true );
			$data   = unserialize( $chart->post_content );
			if ( ! empty( $series ) ) {
				$row = array();
				foreach ( $series as $array ) {
					$row[] = $array['label'];
				}
				$rows[] = $row;
				$row    = array();
				foreach ( $series as $array ) {
					$row[] = $array['type'];
				}
				$rows[] = $row;
			}
			if ( ! empty( $data ) ) {
				foreach ( $data as $array ) {
					// ignore strings
					if ( ! is_array( $array ) ) {
						continue;
					}
					// if this is an array of arrays...
					if ( is_array( $array[0] ) ) {
						foreach ( $array as $arr ) {
							$rows[] = $arr;
						}
					} else {
						// just an array
						$rows[] = $array;
					}
				}
			}

			$filename   = isset( $settings['title'] ) && ! empty( $settings['title'] ) ? $settings['title'] : 'visualizer#' . $chart_id;

			switch ( $type ) {
				case 'csv':
					$final   = $this->_getCSV( $rows, $filename );
					break;
				case 'xls':
					$final   = $this->_getExcel( $rows, $filename );
					break;
				case 'print':
					$final   = $this->_getHTML( $rows );
					break;
			}
		}
		return $final;
	}

	/**
	 * Prepares a CSV.
	 *
	 * @access private
	 * @param array  $rows The array of data.
	 * @param string $filename The name of the file to use.
	 */
	private function _getCSV( $rows, $filename ) {
		$filename .= '.csv';

		$fp = tmpfile();
		// support for MS Excel
		fprintf( $fp, $bom = ( chr( 0xEF ) . chr( 0xBB ) . chr( 0xBF ) ) );
		foreach ( $rows as $row ) {
			fputcsv( $fp, $row );
		}
		rewind( $fp );
		$csv = '';
		while ( ( $array = fgetcsv( $fp ) ) !== false ) {
			if ( strlen( $csv ) > 0 ) {
				$csv .= PHP_EOL;
			}
			$csv .= implode( ',', $array );
		}
		fclose( $fp );

		return array(
			'csv'  => $csv,
			'name' => $filename,
		);
	}

	/**
	 * Prepares an Excel file.
	 *
	 * @access private
	 * @param array  $rows The array of data.
	 * @param string $filename The name of the file to use.
	 */
	private function _getExcel( $rows, $filename ) {
		// PHPExcel does not like sheet names longer than 31 characters.
		$chart      = substr( $filename, 0, 30 );
		$filename   .= '.xlsx';

		$xlsData    = '';
		if ( class_exists( 'PHPExcel' ) ) {
			$doc        = new PHPExcel();
			$doc->getActiveSheet()->fromArray( $rows, null, 'A1' );
			$doc->getActiveSheet()->setTitle( sanitize_title( $chart ) );
			$doc        = apply_filters( 'visualizer_excel_doc', $doc );
			$writer = PHPExcel_IOFactory::createWriter( $doc, 'Excel2007' );
			ob_start();
			$writer->save( 'php://output' );
			$xlsData = ob_get_contents();
			ob_end_clean();
		} else {
			do_action( 'themeisle_log_event', Visualizer_Plugin::NAME, 'Class PHPExcel does not exist!', 'error', __FILE__, __LINE__ );
			error_log( 'Class PHPExcel does not exist!' );
		}
		return array(
			'csv'  => 'data:application/vnd.ms-excel;base64,' . base64_encode( $xlsData ),
			'name' => $filename,
		);
	}

	/**
	 * Prepares an HTML table.
	 *
	 * @access private
	 * @param array $rows The array of data.
	 */
	private function _getHTML( $rows ) {
		$css        = '
					table.visualizer-print {
						border-collapse: collapse;
					}
					table.visualizer-print, table.visualizer-print th, table.visualizer-print td {
						border: 1px solid #000;
					}
		';
		$html       = '';
		$html       .= '
		<html>
			<head>
				<style>
					' . apply_filters( 'visualizer_print_css', $css ) . '
				</style>
			</head>
			<body>';

		$table      = '<table class="visualizer-print">';
		$index      = 0;
		foreach ( $rows as $row ) {
			$table  .= '<tr>';
			foreach ( $row as $col ) {
				if ( $index === 0 ) {
					$table  .= '<th>' . $col . '</th>';
				} else {
					$table  .= '<td>' . $col . '</td>';
				}
			}
			$table  .= '</tr>';
			$index++;
		}
		$table      .= '</table>';

		$html       .= apply_filters( 'visualizer_print_table', $table ) . '
			</body>
		</html>';
		return array(
			'csv'  => $html,
		);
	}

	/**
	 * Undo revisions for the chart, and if necessary, restore the earliest version.
	 *
	 * @return bool If any revisions were found.
	 */
	public final function undoRevisions( $chart_id, $restore = false ) {
		do_action( 'themeisle_log_event', Visualizer_Plugin::NAME, sprintf( 'undoRevisions for %d with%s restore', $chart_id, ( $restore ? '' : 'out' ) ), 'debug', __FILE__, __LINE__ );

		$revisions = wp_get_post_revisions( $chart_id, array( 'order' => 'ASC' ) );
		if ( count( $revisions ) > 1 ) {
			$revision_ids = array_keys( $revisions );

			do_action( 'themeisle_log_event', Visualizer_Plugin::NAME, sprintf( 'found %d revisions = %s', count( $revisions ), print_r( $revision_ids, true ) ), 'debug', __FILE__, __LINE__ );

			// when we restore, a new revision is likely to be created. so, let's disable revisions for the time being.
			add_filter( 'wp_revisions_to_keep', '__return_false' );

			if ( $restore ) {
				// restore to the oldest one i.e. the first one.
				wp_restore_post_revision( array_shift( $revision_ids ) );
			}

			// delete all revisions.
			foreach ( $revision_ids as $id ) {
				wp_delete_post_revision( $id );
			}

			return true;
		}
		return false;
	}

	/**
	 * If existing revisions exist for the chart, restore the earliest version and then create a new revision to initiate editing.
	 */
	public final function handleExistingRevisions( $chart_id, $chart ) {
		do_action( 'themeisle_log_event', Visualizer_Plugin::NAME, sprintf( 'handleExistingRevisions for %d', $chart_id ), 'debug', __FILE__, __LINE__ );

		// undo revisions.
		$revisions_found    = $this->undoRevisions( $chart_id, true );

		// create revision for the edit action.
		wp_save_post_revision( $chart_id );

		if ( $revisions_found ) {
			// fetch chart data again in case it was updated by an earlier revision.
			$chart = get_post( $chart_id );
		}
		return $chart;
	}

	/**
	 * Returns the language of the locale.
	 *
	 * @access protected
	 */
	protected function get_language() {
		$locale = get_locale();
		if ( empty( $locale ) ) {
			return '';
		}
		$array  = explode( '_', $locale );
		if ( count( $array ) < 2 ) {
			return '';
		}
		return reset( $array );
	}

	/**
	 * Gets/creates the JS where user-specific customizations can be/have been added.
	 */
	protected function get_user_customization_js() {
		// use this as the JS file in case we are not able to create the file in uploads.
		$default    = VISUALIZER_ABSURL . 'js/customization.js';

		$uploads    = wp_get_upload_dir();
		$specific   = $uploads['baseurl'] . '/visualizer/customization.js';

		// for testing on user sites (before we send them the correctly customized file).
		if ( VISUALIZER_TEST_JS_CUSTOMIZATION ) {
			return $default;
		}

		require_once( ABSPATH . 'wp-admin/includes/file.php' );
		WP_Filesystem();
		global $wp_filesystem;

		$dir    = $wp_filesystem->wp_content_dir() . 'uploads/visualizer';
		$file   = $wp_filesystem->wp_content_dir() . 'uploads/visualizer/customization.js';

		if ( $wp_filesystem->is_readable( $file ) ) {
			return $specific;
		}

		if ( $wp_filesystem->exists( $file ) && ! $wp_filesystem->is_readable( $file ) ) {
			do_action( 'themeisle_log_event', Visualizer_Plugin::NAME, sprintf( 'Unable to read file %s', $file ), 'error', __FILE__, __LINE__ );
			return $default;
		}

		if ( ! $wp_filesystem->exists( $dir ) ) {
			if ( ( $done = $wp_filesystem->mkdir( $dir ) ) === false ) {
				do_action( 'themeisle_log_event', Visualizer_Plugin::NAME, sprintf( 'Unable to create directory %s', $dir ), 'error', __FILE__, __LINE__ );
				return $default;
			}
		}

		// if file does not exist, copy.
		if ( ! $wp_filesystem->exists( $file ) ) {
			$src    = str_replace( ABSPATH, $wp_filesystem->abspath(), VISUALIZER_ABSPATH . '/js/customization.js' );
			if ( ( $done = $wp_filesystem->copy( $src, $file ) ) === false ) {
				do_action( 'themeisle_log_event', Visualizer_Plugin::NAME, sprintf( 'Unable to copy file %s to %s', $src, $file ), 'error', __FILE__, __LINE__ );
				return $default;
			}
		}

		return $specific;
	}

}
