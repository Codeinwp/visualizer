/**
 * WordPress dependencies
 */
const { test, expect } = require( '@wordpress/e2e-test-utils-playwright' );

test.describe( 'Chart Creating for Gutenberg', () => {

    test.beforeAll( async ( { requestUtils } ) => {
        requestUtils.deactivatePlugin( 'classic-editor' );
    } );

    test( 'create a chart then insert into a Post', async ( { admin, page, editor } ) => {
        await admin.visitAdminPage( 'admin.php?page=visualizer&vaction=addnew' );

        await page.waitForSelector('h1:text("Visualizer")', { timeout: 5000 });
        expect( page.getByRole('heading', { name: 'Visualizer' }) ).not.toBeNull();

        const chartView = page.frameLocator('iframe');
        await expect( chartView.getByRole('button', { name: 'Next' }) ).toBeVisible();
        const chartContainer = chartView.locator('div#type-picker');

        // There should be 15 chart types available. From which 11 are PRO.
        await expect( chartContainer.locator('div.type-box').count() ).resolves.toBe( 15 );
        await expect( chartContainer.locator('span.visualizder-pro-label').count() ).resolves.toBe( 11 );

        // Create a Pie chart
        await chartContainer.locator('label').filter({ hasText: 'Pie/Donut' }).click();
        await chartView.getByRole('button', { name: 'Next' }).click();

        await expect( page.frameLocator('iframe').getByRole('button', { name: 'Create Chart' }) ).toBeVisible();

        await page.frameLocator('iframe').getByRole('button', { name: 'Create Chart' }).click();  
        
        await expect( page.getByRole('heading', { name: 'Visualizer Library Add New' }) ).toBeVisible({ timeout: 5000 });

        // There should at least one chart created in the Library.
        await expect( page.locator('.visualizer-chart').count() ).resolves.toBeGreaterThan( 0 );

        // Create a new post and insert the first available chart.
        await admin.createNewPost();
        await editor.insertBlock( { name: 'visualizer/chart'} );
        await page.locator('div').filter({ hasText: /^Display an existing chart$/ }).click();
        await page.locator('.visualizer-settings__charts-controls').first().click();

        // Check if it was inserted correctly then enter view mode for the block.
        expect( page.getByLabel('Block: Visualizer Chart').getByText('Visualizer') ).not.toBeNull();
        await page.getByRole('button', { name: 'Save', exact: true }).click();
        await page.getByRole('button', { name: 'Done' }).click();

        // Check if the Chart did not crash the editor.
        expect( page.locator('.wp-block-visualizer-chart').count() ).resolves.toBe( 1 );
    } );
	
    test('insert a Chart block into a Post', async ( { admin, editor, page } ) => {
        await admin.createNewPost();
        await editor.insertBlock( { name: 'visualizer/chart'} );

        // Check chart selection options are available.
        expect( page.getByText('Make a new chart or display') ).toBeVisible();
        expect( page.getByRole('link', { name: 'Create a new chart' }) ).toBeVisible();
        expect( page.locator('div').filter({ hasText: /^Display an existing chart$/ }) ).toBeVisible();
    } );
} );
