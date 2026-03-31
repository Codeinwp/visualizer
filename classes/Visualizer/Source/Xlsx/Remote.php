<?php
/**
 * Source manager for remote XLSX files loaded from a URL.
 *
 * Downloads the file to a temporary location before parsing so that
 * OpenSpout (which does not support stream wrappers for XLSX) can read it.
 * The source URL is stored in the chart's post_content so that scheduled
 * re-imports can re-fetch and re-parse the file automatically.
 *
 * @category Visualizer
 * @package Source
 *
 * @since 3.11.0
 */
class Visualizer_Source_Xlsx_Remote extends Visualizer_Source_Xlsx {

	/**
	 * Path to the downloaded temporary file.
	 *
	 * @access private
	 * @var string|false
	 */
	private $_tmpfile = false;

	/**
	 * Returns serialised data that also stores the source URL so that
	 * scheduled re-imports know where to fetch the file from.
	 *
	 * @access public
	 * @param bool $dumb Unused; kept for interface compatibility.
	 * @return string Serialised array with 'source' and 'data' keys.
	 */
	public function getData( $dumb = false ) {
		return serialize(
			array(
				'source' => $this->_filename,
				'data'   => $this->_data,
			)
		);
	}


	/**
	 * Re-populates data for scheduled refreshes.
	 *
	 * @access public
	 * @param array $data     The current data array.
	 * @param int   $chart_id The chart post ID.
	 * @return array The re-populated data or the original array.
	 */
	public function repopulateData( $data, $chart_id ) {
		if ( ! is_array( $data ) ) {
			$data = array();
		}
		return array_key_exists( 'data', $data ) ? $data['data'] : $data;
	}

	/**
	 * Re-populates series (series are stored in post meta and remain stable).
	 *
	 * @access public
	 * @param array $series   The current series array.
	 * @param int   $chart_id The chart post ID.
	 * @return array The original series array.
	 */
	public function repopulateSeries( $series, $chart_id ) {
		return $series;
	}

	/**
	 * Returns the source name.
	 *
	 * @access public
	 * @return string
	 */
	public function getSourceName() {
		return __CLASS__;
	}

	/**
	 * Downloads the remote XLSX file to a temporary path and returns that path
	 * for the parent reader to use.
	 *
	 * Enforces a maximum file size (default 10 MB, filterable via
	 * 'visualizer_xlsx_max_filesize') before handing the path to the parser,
	 * guarding against ZIP-bomb and DoS attacks.
	 *
	 * @access protected
	 * @return string Path to the temporary file, or the original URL if download failed.
	 */
	protected function _get_file_path() {
		if ( $this->_tmpfile && ! is_wp_error( $this->_tmpfile ) && is_readable( $this->_tmpfile ) ) {
			return $this->_tmpfile;
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';

		$this->_tmpfile = download_url( $this->_filename );

		if ( is_wp_error( $this->_tmpfile ) ) {
			$this->_error   = esc_html__( 'Could not download the XLSX file. Please check the URL and try again.', 'visualizer' );
			$this->_tmpfile = false;
			// Return the original URL so the parent's open() call will fail
			// gracefully and set an error rather than throwing a PHP error.
			return $this->_filename;
		}

		if ( ! is_file( $this->_tmpfile ) ) {
			$this->_tmpfile = false;
			$this->_error   = esc_html__( 'Could not access the downloaded XLSX file. Please try again.', 'visualizer' );
			return $this->_filename;
		}

		// Maximum allowed file size in bytes. Default 10 MB; override via filter.
		$max_bytes = (int) apply_filters( 'visualizer_xlsx_max_filesize', 10 * 1024 * 1024 );
		if ( filesize( $this->_tmpfile ) > $max_bytes ) {
			@unlink( $this->_tmpfile ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
			$this->_tmpfile = false;
			$this->_error   = esc_html__(
				'The XLSX file exceeds the maximum allowed size and cannot be imported.',
				'visualizer'
			);
			return $this->_filename;
		}

		return $this->_tmpfile;
	}

	/**
	 * Fetches the remote file, parses it, and cleans up the temp file.
	 *
	 * @access public
	 * @return boolean TRUE on success, FALSE on failure.
	 */
	public function fetch() {
		$result = parent::fetch();

		// Clean up the temporary file after parsing.
		if ( $this->_tmpfile && ! is_wp_error( $this->_tmpfile ) && is_file( $this->_tmpfile ) ) {
			@unlink( $this->_tmpfile ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
			$this->_tmpfile = false;
		}

		return $result;
	}
}
