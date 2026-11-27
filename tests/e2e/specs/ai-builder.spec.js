/**
 * WordPress dependencies
 */
const { test, expect } = require( '@wordpress/e2e-test-utils-playwright' );

/**
 * Internal dependencies
 */
const { waitForLibraryToLoad } = require( '../utils/common' );

test.describe( 'AI Chart Builder chooser', () => {
	test.beforeEach( async ( { admin, page } ) => {
		await admin.visitAdminPage( 'admin.php?page=visualizer' );
		await waitForLibraryToLoad( page );
	} );

	test( 'opens chooser modal from Add New', async ( { page } ) => {
		await page.locator( '.add-new-chart' ).first().click();

		const chooser = page.locator( '.viz-chooser-overlay' );
		await expect( chooser ).toBeVisible();
		await expect( page.locator( '#viz-chooser-title' ) ).toHaveText( 'Create a New Chart' );
		await expect( page.locator( '.viz-chooser-option--ai' ) ).toBeVisible();
		await expect( page.locator( '.viz-chooser-option' ).filter( { hasText: 'Classic Builder' } ) ).toBeVisible();
	} );

	test( 'closes chooser modal via close button', async ( { page } ) => {
		await page.locator( '.add-new-chart' ).first().click();
		await expect( page.locator( '.viz-chooser-overlay' ) ).toBeVisible();

		await page.locator( '.viz-chooser-close' ).click();
		await expect( page.locator( '.viz-chooser-overlay' ) ).toHaveCount( 0 );
	} );

	test( 'opens AI builder from chooser and shows key sections', async ( { page } ) => {
		await page.locator( '.add-new-chart' ).first().click();
		await expect( page.locator( '.viz-chooser-overlay' ) ).toBeVisible();

		await page.locator( '.viz-chooser-option--ai' ).click();
		await expect( page.locator( '.viz-ai-builder-modal' ) ).toBeVisible();
		await expect( page.locator( '.viz-ai-builder-header' ) ).toContainText( 'Visualizer' );
		await expect( page.getByText( 'Data source' ) ).toBeVisible();
		await expect( page.getByText('Describe your chart', { exact: true }) ).toBeVisible();

		await page.locator( '.viz-ai-builder-close' ).click();
		await expect( page.locator( '.viz-ai-builder-modal' ) ).toHaveCount( 0 );
	} );
} );

test.describe( 'AI Chart Builder preview', () => {
	test( 'runs saved chart code in the sandbox, not in the admin page', async ( { admin, page } ) => {
		await admin.visitAdminPage( 'admin.php?page=visualizer' );
		await waitForLibraryToLoad( page );

		// Save a chart whose code changes the title of the top window when it can reach it.
		const chartId = await page.evaluate( async () => {
			const { ajaxUrl, nonce } = window.vizAIBuilder;
			const post = async ( fields ) => {
				const response = await fetch( ajaxUrl, { method: 'POST', credentials: 'same-origin', body: new URLSearchParams( fields ) } );
				return ( await response.json() ).data;
			};
			const created = await post( { action: 'visualizer-ai-create', nonce } );
			await post( { action: 'visualizer-ai-upload', nonce: created.upload_nonce, chart_id: created.chart_id, source_type: 'csv_string', csv_data: 'a,b\nstring,number\nx,1' } );
			await post( {
				action: 'visualizer-ai-save',
				nonce,
				chart_id: created.chart_id,
				title: 'Sandbox check',
				code: 'd3.select( container ).append( "svg" ).append( "rect" ); window.top.document.title = "code ran in the admin page";',
			} );
			return created.chart_id;
		} );

		await admin.visitAdminPage( 'admin.php?page=visualizer' );
		await waitForLibraryToLoad( page );
		const title = await page.title();
		await page.locator( `.visualizer-chart-edit[data-chart="${ chartId }"]` ).click();

		const preview = page.frameLocator( '.viz-ai-builder-modal iframe[sandbox="allow-scripts"]' );
		await expect( preview.locator( 'svg rect' ) ).toHaveCount( 1 );
		expect( await page.title() ).toBe( title );
	} );
} );
