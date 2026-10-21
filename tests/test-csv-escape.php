<?php
/**
 * CSV escape character handling.
 *
 * @package     visualizer
 * @subpackage  Tests
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/**
 * Test the CSV escape character passed to fgetcsv()/fputcsv().
 */
class Test_Csv_Escape extends WP_UnitTestCase {

	/**
	 * Temporary CSV file.
	 *
	 * @var string
	 */
	private $file = '';

	/**
	 * Remove the temporary CSV file.
	 */
	public function tearDown(): void {
		if ( $this->file ) {
			wp_delete_file( $this->file );
		}
		parent::tearDown();
	}

	/**
	 * CSV import keeps the backslash escape and logs no deprecation.
	 */
	public function test_csv_import_uses_backslash_escape(): void {
		$this->file = wp_tempnam( 'visualizer' );
		file_put_contents( $this->file, "Label,Value\nstring,number\n\"say \\\"hi\\\"\",1\n" );

		$deprecations = array();
		set_error_handler(
			static function ( int $errno, string $errstr ) use ( &$deprecations ): bool {
				$deprecations[] = $errstr;
				return true;
			},
			E_DEPRECATED
		);
		try {
			$source = new Visualizer_Source_Csv( $this->file );
			$this->assertTrue( $source->fetch() );
		} finally {
			restore_error_handler();
		}

		$this->assertSame( array(), $deprecations );
		$this->assertSame( 'say "hi"', $source->getRawData()[0][0] );
	}
}
