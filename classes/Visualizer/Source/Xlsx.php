<?php
/**
 * Source manager for local XLSX files.
 *
 * Reads the first sheet of an XLSX file using the OpenSpout library,
 * which is already bundled as a dependency of this plugin.
 * Legacy .xls (BIFF) files are not supported; OpenSpout's XLSX reader
 * only handles the Office Open XML (.xlsx) format.
 *
 * Expected sheet layout (same convention as CSV import):
 *   Row 1 – column labels
 *   Row 2 – column types (string, number, boolean, date, datetime, timeofday)
 *   Row 3+ – data rows
 *
 * @category Visualizer
 * @package Source
 *
 * @since 3.11.0
 */
class Visualizer_Source_Xlsx extends Visualizer_Source {

	/**
	 * The path to the XLSX file.
	 *
	 * @access protected
	 * @var string
	 */
	protected $_filename;

	/**
	 * Constructor.
	 *
	 * @access public
	 * @param string $filename Path to the XLSX file.
	 */
	public function __construct( $filename = '' ) {
		$this->_filename = trim( (string) $filename );
	}

	/**
	 * Fetches information from the XLSX file and builds the series/data arrays.
	 *
	 * @access public
	 * @return boolean TRUE on success, FALSE on failure.
	 */
	public function fetch() {
		if ( empty( $this->_filename ) ) {
			$this->_error = esc_html__( 'No file provided. Please try again.', 'visualizer' );
			return false;
		}

		if ( ! class_exists( 'OpenSpout\Reader\Common\Creator\ReaderEntityFactory' ) ) {
			$this->_error = esc_html__( 'The OpenSpout library is required to import XLSX files but could not be found. Please contact support.', 'visualizer' );
			return false;
		}

		$reader = \OpenSpout\Reader\Common\Creator\ReaderEntityFactory::createXLSXReader();
		try {
			$reader->open( $this->_get_file_path() );

			$all_rows = array();
			foreach ( $reader->getSheetIterator() as $sheet ) {
				foreach ( $sheet->getRowIterator() as $row ) {
					$row_data = array();
					foreach ( $row->getCells() as $cell ) {
						$value = $cell->getValue();
						// Convert non-string scalars to string for uniform handling;
						// _normalizeData() will cast them to the correct type later.
						$row_data[] = is_null( $value ) ? null : (string) $value;
					}
					$all_rows[] = $row_data;
				}
				break; // Only read the first sheet.
			}
		} catch ( \Exception $e ) {
			$reader->close();
			$this->_error = sprintf(
				/* translators: %s - the exception message. */
				esc_html__( 'Could not read the XLSX file: %s', 'visualizer' ),
				$e->getMessage()
			);
			return false;
		}

		$reader->close();

		if ( count( $all_rows ) < 2 ) {
			$this->_error = esc_html__( 'File should have a heading row (1st row) and a data type row (2nd row). Please try again.', 'visualizer' );
			return false;
		}

		$labels = array_filter( $all_rows[0] );
		$types  = array_filter( $all_rows[1] );

		if ( ! $labels || ! $types ) {
			$this->_error = esc_html__( 'File should have a heading row (1st row) and a data type row (2nd row). Please try again.', 'visualizer' );
			return false;
		}

		$types = array_map( 'trim', $types );
		if ( ! self::_validateTypes( $types ) ) {
			$this->_error = esc_html__( 'Invalid data types detected in the data type row (2nd row). Please try again.', 'visualizer' );
			return false;
		}

		// Build the series array from row 1 (labels) and row 2 (types).
		$label_values = $all_rows[0];
		$type_values  = $all_rows[1];
		$col_count    = count( $label_values );

		for ( $i = 0; $i < $col_count; $i++ ) {
			$default_type = ( $i === 0 ) ? 'string' : 'number';
			$label        = isset( $label_values[ $i ] ) ? $this->toUTF8( (string) $label_values[ $i ] ) : '';
			$type         = isset( $type_values[ $i ] ) && ! empty( $type_values[ $i ] ) ? trim( $type_values[ $i ] ) : $default_type;

			$this->_series[] = array(
				'label' => sanitize_text_field( wp_strip_all_tags( $label ) ),
				'type'  => $type,
			);
		}

		// Parse data rows (row 3 onwards).
		for ( $r = 2, $total = count( $all_rows ); $r < $total; $r++ ) {
			$this->_data[] = $this->_normalizeData( $all_rows[ $r ] );
		}

		return true;
	}

	/**
	 * Returns the file path to open with the reader.
	 * Subclasses may override this to supply a locally-downloaded copy.
	 *
	 * @access protected
	 * @return string
	 */
	protected function _get_file_path() {
		return $this->_filename;
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
}
