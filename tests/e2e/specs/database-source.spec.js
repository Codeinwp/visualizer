/**
 * WordPress dependencies
 */
const { test, expect } = require( '@wordpress/e2e-test-utils-playwright' );

/**
 * Internal dependencies
 */
const {
	CHART_JS_LABELS,
	deleteAllCharts,
	selectChartAdmin,
} = require( '../utils/common' );

async function showQueryResults( page, chartEditor, expectedValue ) {
	const [ response ] = await Promise.all( [
		page.waitForResponse( ( candidate ) => {
			const request = candidate.request();

			return (
				candidate.url().includes( '/wp-admin/admin-ajax.php' ) &&
				request.method() === 'POST' &&
				request
					.postData()
					?.includes( 'action=visualizer-fetch-db-data' )
			);
		} ),
		chartEditor.getByRole( 'button', { name: 'Show Results' } ).click(),
	] );

	expect( response.ok() ).toBe( true );
	await expect( chartEditor.locator( '.db-wizard-results' ) ).toContainText(
		expectedValue
	);
}

test.describe( 'Database source', () => {
	const postIds = [];

	test.beforeEach( async ( { page, requestUtils } ) => {
		await deleteAllCharts( requestUtils );
		page.setDefaultTimeout( 20000 );
	} );

	test.afterEach( async ( { requestUtils } ) => {
		await Promise.all(
			postIds.splice( 0 ).map( ( postId ) =>
				requestUtils
					.rest( {
						method: 'DELETE',
						path: `/wp/v2/posts/${ postId }`,
						params: { force: true },
					} )
					.catch( () => {} )
			)
		);
	} );

	test( 'saves previewed one-time data when saving the chart', async ( {
		admin,
		context,
		page,
		requestUtils,
	}, testInfo ) => {
		test.setTimeout( 180000 );

		await context.addCookies( [
			{
				name: 'visualizer_e2e_database_source',
				value: '1',
				url: testInfo.project.use.baseURL || 'http://localhost:8889',
			},
		] );

		const sourcePost = await requestUtils.createPost( {
			title: 'ISSUE_1329_OLD',
			status: 'publish',
		} );
		postIds.push( sourcePost.id );

		await admin.visitAdminPage(
			'admin.php?page=visualizer&vaction=addnew'
		);
		await page
			.getByRole( 'button', { name: 'Classic Builder Step-by-step' } )
			.click();

		const chartEditor = page.frameLocator( 'iframe' );
		await chartEditor
			.locator( 'div#type-picker' )
			.waitFor( { state: 'visible', timeout: 20000 } );
		await selectChartAdmin( chartEditor, CHART_JS_LABELS.table );

		await chartEditor
			.locator( '#visualizer-chart-id' )
			.waitFor( { state: 'attached', timeout: 20000 } );
		const chartId = await chartEditor
			.locator( '#visualizer-chart-id' )
			.getAttribute( 'data-id' );
		expect( chartId ).toMatch( /^\d+$/ );

		await chartEditor
			.getByRole( 'button', { name: /Import from database/ } )
			.click();
		await chartEditor
			.locator( '#db-chart-button' )
			.evaluate( ( button ) => button.click() );

		const query = `SELECT post_title AS value FROM wp_posts WHERE ID = ${ sourcePost.id }`;
		await chartEditor
			.locator( '.CodeMirror' )
			.evaluate(
				( editor, sql ) => editor.CodeMirror.setValue( sql ),
				query
			);

		await showQueryResults( page, chartEditor, 'ISSUE_1329_OLD' );

		const [ saveResponse ] = await Promise.all( [
			page.waitForResponse(
				( candidate ) =>
					candidate
						.url()
						.includes( 'action=visualizer-save-db-query' ) &&
					candidate.request().method() === 'POST'
			),
			chartEditor
				.locator( '#db-chart-button' )
				.evaluate( ( button ) => button.click() ),
		] );
		expect( saveResponse.ok() ).toBe( true );
		await expect( chartEditor.locator( '#canvas' ) ).toContainText(
			'ISSUE_1329_OLD',
			{ timeout: 20000 }
		);
		await chartEditor
			.getByRole( 'button', { name: 'Create Chart' } )
			.click();
		await expect(
			page.getByRole( 'heading', { name: 'Visualizer Library' } )
		).toBeVisible( { timeout: 20000 } );

		const chartPost = await requestUtils.createPost( {
			title: 'Issue 1329 chart',
			content: `[visualizer id="${ chartId }"]`,
			status: 'publish',
		} );
		postIds.push( chartPost.id );

		await page.goto( chartPost.link );
		await page.mouse.wheel( 0, 500 );
		await expect(
			page.getByRole( 'cell', { name: 'ISSUE_1329_OLD' } )
		).toBeVisible();

		await requestUtils.rest( {
			method: 'POST',
			path: `/wp/v2/posts/${ sourcePost.id }`,
			data: { title: 'ISSUE_1329_NEW' },
		} );

		await admin.visitAdminPage( 'admin.php?page=visualizer' );
		await page
			.locator( `.visualizer-chart-edit[data-chart="${ chartId }"]` )
			.click();
		await chartEditor
			.locator( '#db-chart-button' )
			.evaluate( ( button ) => button.click() );
		await showQueryResults( page, chartEditor, 'ISSUE_1329_NEW' );

		await chartEditor.getByRole( 'button', { name: 'Save Chart' } ).click();
		await expect(
			page.getByRole( 'dialog', { name: 'Visualizer' } )
		).toHaveCount( 0, { timeout: 30000 } );

		await page.goto( chartPost.link );
		await page.mouse.wheel( 0, 500 );
		await expect(
			page.getByRole( 'cell', { name: 'ISSUE_1329_NEW' } )
		).toBeVisible();
	} );
} );
