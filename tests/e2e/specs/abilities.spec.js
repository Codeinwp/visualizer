/**
 * WordPress dependencies
 */
const { test, expect } = require( '@wordpress/e2e-test-utils-playwright' );

/**
 * Internal dependencies
 */
const { deleteAllCharts } = require( '../utils/common' );

const UPSERT_PATH = '/wp-abilities/v1/abilities/visualizer/upsert-chart/run';

// Keeps the admin file helpers unloaded, as on sites without SDK promotions.
const HEADERS = { 'X-Visualizer-E2E-Skip-Promotions': '1' };

const CHART_INPUT = {
	type: 'line',
	columns: [
		{ name: 'Month', type: 'string' },
		{ name: 'Sales', type: 'number' },
	],
	rows: [
		[ 'Jan', 10 ],
		[ 'Feb', 20 ],
	],
};

test.describe( 'Chart abilities over REST', () => {
	test.beforeEach( async ( { requestUtils } ) => {
		await deleteAllCharts( requestUtils );
	} );

	test.afterAll( async ( { requestUtils } ) => {
		await deleteAllCharts( requestUtils );
	} );

	test( 'upsert-chart dry run returns the parsed preview from manual data', async ( {
		requestUtils,
	} ) => {
		const result = await requestUtils.rest( {
			method: 'POST',
			path: UPSERT_PATH,
			headers: HEADERS,
			data: { input: { ...CHART_INPUT, dry_run: true } },
		} );

		expect( result.dry_run ).toBe( true );
		expect( result.valid ).toBe( true );
		expect( result.parsed_preview ).toBeTruthy();
		expect( JSON.stringify( result.parsed_preview ) ).toContain( 'Feb' );
	} );

	test( 'upsert-chart creates a chart from manual data', async ( {
		requestUtils,
	} ) => {
		const result = await requestUtils.rest( {
			method: 'POST',
			path: UPSERT_PATH,
			headers: HEADERS,
			data: { input: CHART_INPUT },
		} );

		expect( result.created ).toBe( true );
		expect( result.id ).toBeGreaterThan( 0 );

		const chart = await requestUtils.rest( {
			path: `/wp/v2/visualizer/${ result.id }`,
			params: { context: 'edit' },
		} );
		expect( chart.status ).toBe( 'publish' );
	} );
} );
