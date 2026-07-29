/**
 * WordPress dependencies
 */
const { test, expect } = require( '@wordpress/e2e-test-utils-playwright' );

const CONTRIBUTOR = { username: 'viz_wiz_contributor', password: 'viz-wiz-contributor-pass', email: 'viz-wiz-contributor@example.com' };

let contributorId;

test.describe( 'Setup wizard authorization', () => {
	test.beforeAll( async ( { requestUtils } ) => {
		const contributor = await requestUtils.rest( {
			method: 'POST',
			path: '/wp/v2/users',
			data: { ...CONTRIBUTOR, roles: [ 'contributor' ] },
		} );
		contributorId = contributor.id;
	} );

	test.afterAll( async ( { requestUtils } ) => {
		if ( contributorId ) {
			await requestUtils.rest( { method: 'DELETE', path: `/wp/v2/users/${ contributorId }`, params: { force: true, reassign: 1 } } );
		}
	} );

	test( 'denies wizard dismissal for non-admin users', async ( { browser } ) => {
		const context = await browser.newContext( { baseURL: test.info().project.use.baseURL } );
		const page = await context.newPage();
		await page.goto( '/wp-login.php' );
		await page.fill( '#user_login', CONTRIBUTOR.username );
		await page.fill( '#user_pass', CONTRIBUTOR.password );
		await page.click( '#wp-submit' );
		await page.waitForURL( '**/wp-admin/**' );

		const response = await page.goto( '/wp-admin/admin.php?action=visualizer_dismiss_wizard&status=1' );
		expect( response.status() ).toBe( 403 );

		await context.close();
	} );

	test( 'requires a nonce for wizard dismissal', async ( { page } ) => {
		// Logged in as admin (default storage state) but without a nonce.
		const response = await page.goto( '/wp-admin/admin.php?action=visualizer_dismiss_wizard&status=1' );
		expect( response.status() ).toBe( 403 );
	} );
} );
