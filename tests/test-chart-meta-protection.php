<?php
/**
 * Chart meta protection tests.
 *
 * @package Visualizer
 */
class Test_Visualizer_Chart_Meta_Protection extends WP_XMLRPC_UnitTestCase {

	/**
	 * Chart meta keys that select a data source or name a resource.
	 *
	 * @return array<string, array{string}>
	 */
	public function chart_meta_keys() {
		return array(
			'source'       => array( Visualizer_Plugin::CF_SOURCE ),
			'db query'     => array( Visualizer_Plugin::CF_DB_QUERY ),
			'chart image'  => array( Visualizer_Plugin::CF_CHART_IMAGE ),
			'remote db'    => array( Visualizer_Plugin::CF_REMOTE_DB_PARAMS ),
			'report user'  => array( Visualizer_Plugin::CF_WOOCOMMERCE_REPORT_USER ),
			'd3 code'      => array( Visualizer_Module_AIBuilder::CF_D3_CODE ),
		);
	}

	/**
	 * The author of a chart cannot write chart meta through the WordPress meta capabilities.
	 *
	 * @dataProvider chart_meta_keys
	 *
	 * @param string $meta_key Meta key.
	 */
	public function test_chart_meta_is_not_writable_through_meta_capabilities( $meta_key ) {
		$user_id  = self::factory()->user->create( array( 'role' => 'contributor' ) );
		$chart_id = self::factory()->post->create(
			array(
				'post_type'   => Visualizer_Plugin::CPT_VISUALIZER,
				'post_author' => $user_id,
				'post_status' => 'draft',
			)
		);

		$this->assertTrue( user_can( $user_id, 'edit_post', $chart_id ) );
		foreach ( array( 'add_post_meta', 'edit_post_meta', 'delete_post_meta' ) as $capability ) {
			$this->assertFalse( user_can( $user_id, $capability, $chart_id, $meta_key ), $capability );
		}
		$this->assertTrue( user_can( $user_id, 'add_post_meta', $chart_id, 'my-note' ) );
	}

	/**
	 * A chart created through XML-RPC does not get the data source that its custom fields ask for.
	 */
	public function test_xmlrpc_cannot_set_the_data_source_of_a_chart() {
		$this->make_user_by_role( 'contributor' );

		$chart_id = $this->myxmlrpcserver->wp_newPost(
			array(
				1,
				'contributor',
				'contributor',
				array(
					'post_type'     => Visualizer_Plugin::CPT_VISUALIZER,
					'post_status'   => 'draft',
					'custom_fields' => array(
						array(
							'key'   => Visualizer_Plugin::CF_SOURCE,
							'value' => 'Visualizer_Source_Query',
						),
						array(
							'key'   => Visualizer_Plugin::CF_DB_QUERY,
							'value' => 'SELECT user_login FROM wp_users',
						),
					),
				),
			)
		);

		$this->assertNotIXRError( $chart_id );
		$this->assertSame( '', get_post_meta( (int) $chart_id, Visualizer_Plugin::CF_SOURCE, true ) );
		$this->assertSame( '', get_post_meta( (int) $chart_id, Visualizer_Plugin::CF_DB_QUERY, true ) );
	}

	/**
	 * Queries that read or write a file on the database server.
	 *
	 * @return array<string, array{string}>
	 */
	public function file_queries() {
		return array(
			'LOAD_FILE'     => array( "SELECT LOAD_FILE('/etc/passwd') AS a" ),
			'INTO OUTFILE'  => array( "SELECT 'x' AS a INTO OUTFILE '/tmp/visualizer-test.txt'" ),
			'INTO DUMPFILE' => array( "SELECT 'x' AS a INTO  DUMPFILE '/tmp/visualizer-test.txt'" ),
		);
	}

	/**
	 * The database source refuses a query that reads or writes a file.
	 *
	 * @dataProvider file_queries
	 *
	 * @param string $query SQL query.
	 */
	public function test_database_source_refuses_file_access( $query ) {
		$source = new Visualizer_Source_Query( $query );

		$this->assertFalse( $source->fetch( false ) );
		$this->assertSame( 'Only SELECT queries are allowed', $source->get_error() );
	}

}
