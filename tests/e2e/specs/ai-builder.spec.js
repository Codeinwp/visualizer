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
