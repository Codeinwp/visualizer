<?php

class Visualizer_Source_Csv extends Visualizer_Source {

	/**
	 * The path to the file with data.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @var string
	 */
	private $_filename;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param string $filename The path to the file.
	 */
	public function __construct( $filename ) {
		$this->_filename = $filename;
	}

	/**
	 * Fetches series information.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @param resource $handle The file handle resource.
	 */
	private function _fetchSeries( $handle ) {
		// read column titles
		$labels = fgetcsv( $handle );

		// read series types
		$types = fgetcsv( $handle );

		if ( !$labels || !$types ) {
			return false;
		}

		for ( $i = 0, $len = count( $labels ); $i < $len; $i++ ) {
			$this->_series[] = array(
				'label' => $labels[$i],
				'type'  => isset( $types[$i] ) ? $types[$i] : 'string',
			);
		}

		return true;
	}

	/**
	 * Fetches information from source, parses it and builds series and data arrays.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return boolean TRUE on success, otherwise FALSE.
	 */
	public function fetch() {
		// set line endings auto detect mode
		@ini_set( 'auto_detect_line_endings', true );

		// read file and fill arrays
		$handle = fopen( $this->_filename, 'rb' );
		if ( $handle ) {
			// fetch series
			if ( !$this->_fetchSeries( $handle ) ) {
				return false;
			}

			// fetch data
			while ( ( $data = fgetcsv( $handle ) ) !== false ) {
				$this->_data[] = $this->_normalizeData( $data );
			}

			// close file handle
			fclose( $handle );
		}

		return true;
	}

	/**
	 * Returns source name.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return string The name of source.
	 */
	public function getSourceName() {
		return __CLASS__;
	}

}