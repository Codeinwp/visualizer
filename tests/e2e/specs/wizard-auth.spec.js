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
		// Empty storage state: browser.newContext() otherwise inherits the
		// project's admin cookies, so a login that silently failed would leave
		// the request authenticated as admin and the assertion below moot.
		const context = await browser.newContext( {
			baseURL: test.info().project.use.baseURL,
			storageState: { cookies: [], origins: [] },
		} );
		// POST wp-login with redirects off: the auth cookie is set by the 302
		// response, so we never load the wp-admin dashboard (can stall in CI).
		// No `testcookie` field: WP only accepts it when the context already
		// holds wordpress_test_cookie, which this one deliberately does not.
		const loginResponse = await context.request.post( '/wp-login.php', {
			form: {
				log: CONTRIBUTOR.username,
				pwd: CONTRIBUTOR.password,
				'wp-submit': 'Log In',
			},
			maxRedirects: 0,
		} );
		expect( loginResponse.status() ).toBe( 302 );

		const page = await context.newPage();
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
