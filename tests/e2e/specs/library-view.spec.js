/**
 * WordPress dependencies
 */
const { test, expect } = require( '@wordpress/e2e-test-utils-playwright' );

/**
 * Internal dependencies
 */
const { createChartWithAdmin, deleteAllCharts, waitForLibraryToLoad } = require( '../utils/common' );

test.describe( 'Library View Toggle', () => {

	test.beforeEach( async ( { admin, requestUtils } ) => {
		await deleteAllCharts( requestUtils );
		// Explicitly start in grid view and reset user meta preference.
		await admin.visitAdminPage( 'admin.php?page=visualizer&view=grid' );
	} );

	test.afterEach( async ( { admin } ) => {
		// Reset view preference to grid so other test suites start in a clean state.
		await admin.visitAdminPage( 'admin.php?page=visualizer&view=grid' );
	} );

	test( 'view toggle buttons are visible', async ( { page } ) => {
		await expect( page.locator( '.viz-view-toggle-group' ) ).toBeVisible();
		await expect( page.locator( 'a.viz-view-toggle' ).count() ).resolves.toBe( 2 );

		// Grid toggle is active by default.
		await expect( page.locator( 'a.viz-view-toggle.active[title="Grid View"]' ) ).toBeVisible();
		await expect( page.locator( 'a.viz-view-toggle:not(.active)[title="List View"]' ) ).toBeVisible();
	} );

	test( 'default view is grid', async ( { page } ) => {
		await expect( page.locator( '#visualizer-library.view-grid' ) ).toBeVisible();
		await expect( page.locator( '#visualizer-library.view-list' ) ).toHaveCount( 0 );
	} );

	test( 'clicking list toggle navigates to list view and flips active state', async ( { page } ) => {
		await page.locator( 'a.viz-view-toggle[title="List View"]' ).click();
		await page.waitForURL( '**/admin.php**view=list**' );

		await expect( page ).toHaveURL( /view=list/ );
		// Active state should flip.
		await expect( page.locator( 'a.viz-view-toggle.active[title="List View"]' ) ).toBeVisible();
		await expect( page.locator( 'a.viz-view-toggle:not(.active)[title="Grid View"]' ) ).toBeVisible();
	} );

	test( 'list view table has correct column headers', async ( { admin, page } ) => {
		await createChartWithAdmin( admin, page );
		await admin.visitAdminPage( 'admin.php?page=visualizer&view=list' );
		await waitForLibraryToLoad( page );

		const headers = page.locator( 'table.viz-charts-table thead th' );
		await expect( headers ).toHaveCount( 5 );
		await expect( headers.nth( 0 ) ).toHaveText( 'ID' );
		await expect( headers.nth( 1 ) ).toHaveText( 'Title' );
		await expect( headers.nth( 2 ) ).toHaveText( 'Type' );
		await expect( headers.nth( 3 ) ).toHaveText( 'Shortcode' );
		await expect( headers.nth( 4 ) ).toHaveText( 'Actions' );
	} );

	test( 'list view shows chart data in table row', async ( { admin, page } ) => {
		const chartId = await createChartWithAdmin( admin, page );
		await admin.visitAdminPage( 'admin.php?page=visualizer&view=list' );
		await waitForLibraryToLoad( page );

		const row = page.locator( 'tr.viz-list-row' ).filter( { hasText: `#${ chartId }` } );
		await expect( row ).toBeVisible();

		// Shortcode cell contains the correct chart ID.
		await expect( row.locator( 'code.viz-shortcode-display' ) ).toContainText( `[visualizer id="${ chartId }"` );

		// All action buttons are present.
		await expect( row.locator( 'a.visualizer-chart-edit' ) ).toBeVisible();
		await expect( row.locator( 'a.visualizer-chart-delete' ) ).toBeVisible();
		await expect( row.locator( 'a.visualizer-chart-clone' ) ).toBeVisible();
		await expect( row.locator( 'a.visualizer-chart-export' ) ).toBeVisible();
		await expect( row.locator( 'a.visualizer-chart-shortcode' ) ).toBeVisible();
	} );

	test( 'list view does not render chart canvas elements', async ( { admin, page } ) => {
		const chartId = await createChartWithAdmin( admin, page );
		await admin.visitAdminPage( 'admin.php?page=visualizer&view=list' );
		await waitForLibraryToLoad( page );

		// No canvas/chart preview elements — list view skips rendering them.
		await expect( page.locator( `#visualizer-${ chartId }` ) ).toHaveCount( 0 );
		await expect( page.locator( '.visualizer-chart-canvas' ) ).toHaveCount( 0 );
	} );

	test( 'clicking shortcode display copies shortcode to clipboard', async ( { admin, page } ) => {
		const chartId = await createChartWithAdmin( admin, page );
		await admin.visitAdminPage( 'admin.php?page=visualizer&view=list' );
		await waitForLibraryToLoad( page );

		const row = page.locator( 'tr.viz-list-row' ).filter( { hasText: `#${ chartId }` } );
		await row.locator( 'code.viz-shortcode-display' ).click();

		const clipboard = await page.evaluate( () => navigator.clipboard.readText() );
		expect( clipboard ).toMatch( new RegExp( `\\[visualizer id="${ chartId }"` ) );
	} );

	test( 'view preference persists across page visits via user meta', async ( { admin, page } ) => {
		await createChartWithAdmin( admin, page );
		// createChartWithAdmin lands on admin.php?page=visualizer — chart exists, grid view (user meta = grid).

		// Switch to list view — saves 'list' to user meta.
		await page.locator( 'a.viz-view-toggle[title="List View"]' ).click();
		await page.waitForURL( '**/admin.php**view=list**' );
		await waitForLibraryToLoad( page );

		// Navigate back without ?view= param — user meta should restore list view directly.
		await admin.visitAdminPage( 'admin.php?page=visualizer' );
		await waitForLibraryToLoad( page );

		await expect( page.locator( '#visualizer-library.view-list' ) ).toBeVisible();
		await expect( page.locator( 'table.viz-charts-table' ) ).toBeVisible();
	} );

	test( 'switching back to grid view works', async ( { admin, page } ) => {
		await createChartWithAdmin( admin, page );
		await admin.visitAdminPage( 'admin.php?page=visualizer&view=list' );
		await waitForLibraryToLoad( page );
		await expect( page.locator( '#visualizer-library.view-list' ) ).toBeVisible();

		await page.locator( 'a.viz-view-toggle[title="Grid View"]' ).click();
		await page.waitForURL( '**/admin.php**view=grid**' );
		await waitForLibraryToLoad( page );

		await expect( page.locator( '#visualizer-library.view-grid' ) ).toBeVisible();
		await expect( page.locator( 'table.viz-charts-table' ) ).toHaveCount( 0 );
	} );

	test( 'applying filters preserves the current view', async ( { admin, page } ) => {
		await createChartWithAdmin( admin, page );
		await admin.visitAdminPage( 'admin.php?page=visualizer&view=list' );
		await waitForLibraryToLoad( page );

		// Submit the filter form — the hidden view input should carry list view through.
		await page.getByRole( 'button', { name: 'Apply Filters' } ).click();
		await waitForLibraryToLoad( page );

		await expect( page.locator( '#visualizer-library.view-list' ) ).toBeVisible();
	} );

} );
