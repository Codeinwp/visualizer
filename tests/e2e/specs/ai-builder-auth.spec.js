/**
 * WordPress dependencies
 */
const { test, expect } = require( '@wordpress/e2e-test-utils-playwright' );

const CONTRIBUTOR = { username: 'viz_contributor', password: 'viz-contributor-pass', email: 'viz-contributor@example.com' };
const EDITOR = { username: 'viz_editor', password: 'viz-editor-pass', email: 'viz-editor@example.com' };

let adminChartId;
let contributorChartId;
let contributorId;
let editorId;

/**
 * Log in via wp-login in a fresh context and return the page.
 *
 * Uses a raw POST with redirects off: the auth cookie is set by the 302
 * response, so we never have to load the wp-admin dashboard (which can
 * stall on external feed widgets in CI).
 *
 * The context starts with an empty storage state: browser.newContext()
 * otherwise inherits the project's admin cookies, so a login that silently
 * failed would leave these requests running as admin. That also means no
 * wordpress_test_cookie, hence no `testcookie` field in the form.
 */
async function loginAs( browser, baseURL, credentials ) {
	const context = await browser.newContext( {
		baseURL,
		storageState: { cookies: [], origins: [] },
	} );
	const response = await context.request.post( '/wp-login.php', {
		form: {
			log: credentials.username,
			pwd: credentials.password,
			'wp-submit': 'Log In',
		},
		maxRedirects: 0,
	} );
	if ( response.status() !== 302 ) {
		throw new Error( `Login as ${ credentials.username } failed with status ${ response.status() }` );
	}
	const page = await context.newPage();
	return { context, page };
}

/**
 * Read the AI Builder nonce localized on the Visualizer library page.
 */
async function getAiNonce( page ) {
	await page.goto( '/wp-admin/admin.php?page=visualizer' );
	return page.evaluate( () => {
		if ( window.vizAIBuilder && window.vizAIBuilder.nonce ) {
			return window.vizAIBuilder.nonce;
		}
		const match = document.documentElement.innerHTML.match( /"nonce":"([a-f0-9]+)"/ );
		return match ? match[ 1 ] : null;
	} );
}

/**
 * Call an admin-ajax action using the page's session cookies.
 */
async function aiAjax( page, action, data ) {
	const response = await page.request.post( '/wp-admin/admin-ajax.php', {
		form: { action, ...data },
	} );
	return { status: response.status(), body: await response.json() };
}

test.describe( 'AI Builder authorization', () => {
	test.beforeAll( async ( { requestUtils } ) => {
		const contributor = await requestUtils.rest( {
			method: 'POST',
			path: '/wp/v2/users',
			data: { ...CONTRIBUTOR, roles: [ 'contributor' ] },
		} );
		contributorId = contributor.id;

		const editor = await requestUtils.rest( {
			method: 'POST',
			path: '/wp/v2/users',
			data: { ...EDITOR, roles: [ 'editor' ] },
		} );
		editorId = editor.id;

		const chart = await requestUtils.rest( {
			method: 'POST',
			path: '/wp/v2/visualizer',
			data: { title: 'Admin chart', status: 'publish' },
		} );
		adminChartId = chart.id;
	} );

	test.afterAll( async ( { requestUtils } ) => {
		for ( const id of [ adminChartId, contributorChartId ] ) {
			if ( id ) {
				await requestUtils.rest( { method: 'DELETE', path: `/wp/v2/visualizer/${ id }`, params: { force: true } } );
			}
		}
		for ( const [ id, user ] of [ [ contributorId, CONTRIBUTOR ], [ editorId, EDITOR ] ] ) {
			if ( id ) {
				await requestUtils.rest( { method: 'DELETE', path: `/wp/v2/users/${ id }`, params: { force: true, reassign: 1 } } );
			}
		}
	} );

	test( 'denies read/write/nonce endpoints on another user\'s chart', async ( { browser } ) => {
		const baseURL = test.info().project.use.baseURL;
		const { context, page } = await loginAs( browser, baseURL, CONTRIBUTOR );
		const nonce = await getAiNonce( page );
		expect( nonce ).toBeTruthy();

		for ( const action of [ 'visualizer-ai-fetch', 'visualizer-ai-chart-nonce', 'visualizer-ai-save' ] ) {
			const { status, body } = await aiAjax( page, action, { nonce, chart_id: adminChartId, code: 'x' } );
			expect( status, action ).toBe( 403 );
			expect( body.data.message, action ).toBe( 'Unauthorized.' );
		}

		await context.close();
	} );

	test( 'denies polling a workflow owned by someone else', async ( { browser } ) => {
		const baseURL = test.info().project.use.baseURL;
		const { context, page } = await loginAs( browser, baseURL, CONTRIBUTOR );
		const nonce = await getAiNonce( page );

		const { status, body } = await aiAjax( page, 'visualizer-ai-status', { nonce, workflow_id: 'foreign-workflow-id' } );
		expect( status ).toBe( 403 );
		expect( body.data.message ).toBe( 'Unauthorized.' );

		await context.close();
	} );

	test( 'allows a contributor to use their own chart', async ( { browser } ) => {
		const baseURL = test.info().project.use.baseURL;
		const { context, page } = await loginAs( browser, baseURL, CONTRIBUTOR );
		const nonce = await getAiNonce( page );

		const created = await aiAjax( page, 'visualizer-ai-create', { nonce } );
		expect( created.body.success ).toBe( true );
		contributorChartId = created.body.data.chart_id;

		const fetched = await aiAjax( page, 'visualizer-ai-fetch', { nonce, chart_id: contributorChartId } );
		expect( fetched.body.success ).toBe( true );

		await context.close();
	} );

	test( 'allows an editor to read another user\'s chart', async ( { browser } ) => {
		const baseURL = test.info().project.use.baseURL;
		const { context, page } = await loginAs( browser, baseURL, EDITOR );
		const nonce = await getAiNonce( page );

		const { body } = await aiAjax( page, 'visualizer-ai-fetch', { nonce, chart_id: adminChartId } );
		expect( body.success ).toBe( true );

		await context.close();
	} );
} );
