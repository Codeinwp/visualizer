/**
 * WordPress dependencies
 */
const { test, expect } = require( '@wordpress/e2e-test-utils-playwright' );

/**
 * Internal dependencies
 */
const { createChartWithAdmin, deleteAllCharts } = require('../utils/common');

test.describe( 'Charts with Gutenberg Editor', () => {

    test.beforeAll( async ( { requestUtils } ) => {
        await deleteAllCharts( requestUtils );
    } );

    test.beforeEach( async ( { page } ) => {
        page.setDefaultTimeout( 5000 );
    } );

    test('check available action on block creation', async ( { admin, editor } ) => {
        await admin.createNewPost();
        await editor.insertBlock( { name: 'visualizer/chart'} );

        // The block renders inside the editor canvas, which WordPress serves in
        // an iframe, so every locator must go through editor.canvas.
        await expect( editor.canvas.getByText('Make a new chart or display') ).toBeVisible();
        await expect( editor.canvas.locator('a.visualizer-settings__content-option').filter({ hasText: 'Create a new chart' }) ).toBeVisible();
        await expect( editor.canvas.locator('div').filter({ hasText: /^Display an existing chart$/ }) ).toBeVisible();
    } );

    test('new chart creation', async ( { admin, editor, page } ) => {
        await admin.createNewPost();
        await editor.insertBlock( { name: 'visualizer/chart'} );

        await expect( editor.canvas.getByText('Make a new chart or display') ).toBeVisible();
        const createOption = editor.canvas.locator('a.visualizer-settings__content-option').filter({ hasText: 'Create a new chart' });
        await expect( createOption ).toBeVisible();

        await createOption.click({ force: true });

        // Create chart via popup; target the wizard frame, not the canvas iframe.
        const wizard = page.frameLocator('iframe[src*="visualizer-create-chart"]');
        await wizard.getByRole('button', { name: 'Next' }).click();
        await wizard.getByRole('button', { name: 'Create Chart' }).click();

        await expect( editor.canvas.getByRole('button', { name: 'Done' }) ).toBeVisible();
        await editor.canvas.getByRole('button', { name: 'Done' }).click();

        await expect( editor.canvas.locator('.wp-block-visualizer-chart') ).toHaveCount( 1 );
        await expect( editor.canvas.getByRole('button', { name: 'Done' }) ).toBeHidden();

    } );

    test( 'insert an existing chart', async ( { admin, page, editor } ) => {
        await createChartWithAdmin( admin, page );

        // Create a new post and insert the first available chart.
        await admin.createNewPost();
        await editor.insertBlock( { name: 'visualizer/chart'} );
        await editor.canvas.locator('div').filter({ hasText: /^Display an existing chart$/ }).click();
        await editor.canvas.locator('.visualizer-settings__charts-controls').first().click();

        // Check if it was inserted correctly then enter view mode for the block.
        await expect( editor.canvas.getByLabel('Block: Visualizer Chart') ).toBeVisible();
        await editor.canvas.getByRole('button', { name: 'Done' }).click();

        // Check if the Chart did not crash the editor.
        await expect( editor.canvas.locator('.wp-block-visualizer-chart') ).toHaveCount( 1 );
    } );

    test( 'check block Edit new button', async ( { admin, editor, page } ) => {
        await createChartWithAdmin( admin, page );

        // Create a new post and insert the first available chart.
        await admin.createNewPost();
        await editor.insertBlock( { name: 'visualizer/chart'} );

        await editor.canvas.locator('div').filter({ hasText: /^Display an existing chart$/ }).click();
        await editor.canvas.locator('.visualizer-settings__charts-controls').first().click();

        await expect( editor.canvas.getByLabel('Block: Visualizer Chart') ).toBeVisible();

        // The Edit Chart button lives in the block toolbar / inspector, which
        // render in the parent document, not the canvas iframe.
        await expect( page.getByRole('button', { name: 'Edit Chart' }).first() ).toBeVisible();
        await page.getByRole('button', { name: 'Edit Chart' }).first().click();
        //await page.goto('http://localhost:8889/wp-admin/post.php?post=29&action=edit');
        await expect(page.getByLabel('Visualizer', { exact: true }).locator('h1')).toContainText('Visualizer');
        await page.getByRole('button', { name: 'Close dialog' }).click();
    } );

    test( 'check widgets', async ( { admin, editor, page } ) => {
        await createChartWithAdmin( admin, page );

        await admin.visitAdminPage( 'widgets.php' );

        await page.getByLabel('Close', { exact: true }).click();
        await page.getByLabel('Block Inserter').click();
        await page.getByPlaceholder('Search').fill('visuali');
        await page.getByRole('option', { name: ' Visualizer Chart' }).click();
        await page.locator('div').filter({ hasText: /^Display an existing chart$/ }).click();
        await page.getByTitle('Insert Chart').first().click();

        await expect(page.getByLabel('Block: Visualizer Chart')).toContainText('Visualizer');
        await expect(page.locator('rect').first()).toBeVisible();

    } );

    test( 'check widgets in the Customizer', async ( { admin, editor, page } ) => {
        await createChartWithAdmin( admin, page );

        await admin.visitAdminPage( 'customize.php' );

        await page.getByRole('heading', { name: 'Widgets' }).click();
        await page.getByRole('heading', { name: 'Footer' }).click();
        await page.getByRole('button', { name: 'Got it' }).click();
        await page.getByLabel('Document tools').getByLabel('Add block').click();
        await page.getByPlaceholder('Search', { exact: true }).fill('visualizer');
        await page.getByRole('option', { name: ' Visualizer Chart' }).click();
        await page.getByText('Display an existing chart').click();
        await page.locator('.visualizer-settings__charts-controls').first().click();
        await expect(page.getByLabel('Block: Visualizer Chart')).toContainText('Visualizer');
        await expect(page.locator('rect').first()).toBeVisible();

    } );
} );
