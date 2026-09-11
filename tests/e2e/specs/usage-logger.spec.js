/**
 * WordPress dependencies
 */
const { test, expect } = require( '@wordpress/e2e-test-utils-playwright' );

/**
 * Internal dependencies
 */
const { deleteAllCharts } = require( '../utils/common' );

/**
 * Regression tests for https://github.com/Codeinwp/visualizer/issues/1359
 *
 * A published chart whose `visualizer-settings` meta is a string (instead of
 * the sanitized settings array) crashed `Visualizer_Module_Setup::getUsage()`
 * with a PHP 8 TypeError, aborting the whole SDK usage collection request.
 * The logger must tolerate such charts and still report the others.
 */
test.describe( 'Usage logger', () => {
	let corruptedId;
	let manualId;

	test.beforeAll( async ( { requestUtils } ) => {
		// The assertions below count charts, so start from a clean library.
		await deleteAllCharts( requestUtils );

		// A chart whose settings meta is a corrupted string value.
		const corrupted = await requestUtils.rest( {
			method: 'POST',
			path: '/wp/v2/visualizer',
			data: { title: 'Corrupted settings chart', status: 'publish' },
		} );
		corruptedId = corrupted.id;
		await requestUtils.rest( {
			method: 'POST',
			path: `/visualizer-e2e/v1/chart-settings/${ corruptedId }`,
			data: { settings: 'corrupted string settings' },
		} );

		// A healthy chart with a manual configuration, which must still be counted.
		const manual = await requestUtils.rest( {
			method: 'POST',
			path: '/wp/v2/visualizer',
			data: { title: 'Manual config chart', status: 'publish' },
		} );
		manualId = manual.id;
		await requestUtils.rest( {
			method: 'POST',
			path: `/visualizer-e2e/v1/chart-settings/${ manualId }`,
			data: { settings: { manual: '{"colors": ["#000"]}' } },
		} );
	} );

	test.afterAll( async ( { requestUtils } ) => {
		for ( const id of [ corruptedId, manualId ] ) {
			if ( id ) {
				await requestUtils.rest( { method: 'DELETE', path: `/wp/v2/visualizer/${ id }`, params: { force: true } } );
			}
		}
	} );

	test( 'survives a chart whose settings meta is a string', async ( { requestUtils } ) => {
		// Before the fix this request died with a TypeError (HTTP 500).
		const usage = await requestUtils.rest( { method: 'GET', path: '/visualizer-e2e/v1/usage' } );

		expect( usage.manual_config ).toBe( 1 );
		expect( Object.values( usage.types ).reduce( ( a, b ) => a + b, 0 ) ).toBe( 2 );
	} );
} );
