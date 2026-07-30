/**
 * WordPress dependencies
 */
const { test, expect } = require( '@wordpress/e2e-test-utils-playwright' );

let chartId;

test.describe( 'Custom CSS sanitization', () => {
	test.beforeAll( async ( { requestUtils } ) => {
		const chart = await requestUtils.rest( {
			method: 'POST',
			path: '/wp/v2/visualizer',
			data: { title: 'Custom CSS payload chart', status: 'publish' },
		} );
		chartId = chart.id;

		await requestUtils.rest( {
			method: 'POST',
			path: `/visualizer-e2e/v1/chart-settings/${ chartId }`,
			data: {
				settings: {
					customcss: {
						title: {
							color: 'red</style><script>window.vizXss=1</script><style>',
							'font-size': '12px',
						},
					},
				},
			},
		} );
	} );

	test.afterAll( async ( { requestUtils } ) => {
		if ( chartId ) {
			await requestUtils.rest( { method: 'DELETE', path: `/wp/v2/visualizer/${ chartId }`, params: { force: true } } );
		}
	} );

	test( 'strips tags from chart custom CSS on the library page', async ( { admin, page } ) => {
		await admin.visitAdminPage( 'admin.php?page=visualizer' );

		const styleBlock = page.locator( `#customcss-visualizer-${ chartId }` );
		await expect( styleBlock ).toHaveCount( 1 );
		// Legitimate rules survive sanitization. <style> has no innerText, so read textContent.
		const css = await styleBlock.textContent();
		expect( css ).toContain( 'font-size: 12px' );
		expect( css ).not.toContain( '<script' );
		// The injected script must not have executed.
		expect( await page.evaluate( () => window.vizXss ) ).toBeUndefined();
	} );
} );
