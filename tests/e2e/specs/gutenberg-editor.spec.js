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

    test('check available action on block creation', async ( { admin, editor, page } ) => {
        await admin.createNewPost();
        await editor.insertBlock( { name: 'visualizer/chart'} );

        // Check chart selection options are available.
        await expect( page.getByText('Make a new chart or display') ).toBeVisible();
        await expect( page.getByLabel('Editor content').locator('a') ).toBeVisible();
        await expect( page.locator('div').filter({ hasText: /^Display an existing chart$/ }) ).toBeVisible();
    } );

    test('new chart creation', async ( { admin, editor, page } ) => {
        await admin.createNewPost();
        await editor.insertBlock( { name: 'visualizer/chart'} );

        await expect( page.getByText('Make a new chart or display') ).toBeVisible();
        await expect( page.getByLabel('Editor content').locator('a') ).toBeVisible();

        await page.getByLabel('Editor content').locator('a').click({ force: true});

        // Create chart via popup.
        await page.frameLocator('iframe').getByRole('button', { name: 'Next' }).click();
        await page.frameLocator('iframe').getByRole('button', { name: 'Create Chart' }).click();

        await expect( page.getByRole('button', { name: 'Save', exact: true }) ).toBeVisible();
        await page.getByRole('button', { name: 'Save', exact: true }).click();
        await expect( page.getByRole('button', { name: 'Done' }) ).toBeVisible();
        await page.getByRole('button', { name: 'Done' }).click();

        await expect( page.locator('.wp-block-visualizer-chart').count() ).resolves.toBe( 1 );
        await expect( page.getByRole('button', { name: 'Done' }) ).toBeHidden();

    } );

    test( 'insert an existing chart', async ( { admin, page, editor } ) => {
        await createChartWithAdmin( admin, page );

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

    test( 'check block Sidebar/Inspector tabs', async ( { admin, editor, page } ) => {
        await createChartWithAdmin( admin, page );

        // Create a new post and insert the first available chart.
        await admin.createNewPost();
        await editor.insertBlock( { name: 'visualizer/chart'} );

        // Switch to the legacy controls.
        await page.evaluate(() => {
            window.visualizerLocalize.legacyBlockEdit = true;
        });

        await page.locator('div').filter({ hasText: /^Display an existing chart$/ }).click();
        await page.locator('.visualizer-settings__charts-controls').first().click();

        expect( page.getByLabel('Block: Visualizer Chart').getByText('Visualizer') ).not.toBeNull();

        expect( page.getByRole('button', { name: 'Import data from file' }) ).toBeVisible();

        await page.getByRole('button', { name: 'Import data from file' }).click();

        await page.getByRole('button', { name: 'Import data from URL' }).click();
        await page.getByRole('button', { name: 'One Time' }).click();
        await page.getByRole('button', { name: 'Schedule Import' }).click();
        await page.getByRole('button', { name: 'Import from JSON' }).click();

        await page.getByRole('button', { name: 'Import from other chart' }).click();

        await page.getByRole('button', { name: 'Import data from database' }).click();

        const upgradeLinks = await page.locator('a').filter({ hasText: 'Upgrade Now' }).count();
        expect( upgradeLinks ).toBe( 6 );

        await page.getByRole('button', { name: 'Advanced Options' }).click();
        await page.getByRole('button', { name: 'Chart Settings' }).click();

        await page.getByRole('button', { name: 'Chart Permissions' }).click();
        await page.getByRole('button', { name: 'Chart Settings' }).click();

        // Check if the block is still visible and has not crashed.
        await expect( page.getByRole('button', { name: 'Save', exact: true }) ).toBeVisible();
    } );

    test( 'check block Edit new button', async ( { admin, editor, page } ) => {
        await createChartWithAdmin( admin, page );

        // Create a new post and insert the first available chart.
        await admin.createNewPost();
        await editor.insertBlock( { name: 'visualizer/chart'} );

        await page.locator('div').filter({ hasText: /^Display an existing chart$/ }).click();
        await page.locator('.visualizer-settings__charts-controls').first().click();

        expect( page.getByLabel('Block: Visualizer Chart').getByText('Visualizer') ).not.toBeNull();

        await expect(page.getByRole('button', { name: 'Edit Chart' })).toBeVisible();
        await expect(page.getByLabel('Editor settings').getByRole('paragraph')).toContainText('The editor for managing chart settings has been removed from the block editor. You can find more information in this documentation');
        await page.getByRole('button', { name: 'Edit Chart' }).click();
        //await page.goto('http://localhost:8889/wp-admin/post.php?post=29&action=edit');
        await expect(page.getByLabel('Visualizer', { exact: true }).locator('h1')).toContainText('Visualizer');
        await page.getByRole('button', { name: ' Close dialog' }).click();
    } );

    test( 'check widgets', async ( { admin, editor, page } ) => {
        await createChartWithAdmin( admin, page );

        await admin.visitAdminPage( 'widgets.php' );

        await page.getByLabel('Close', { exact: true }).click();
        await page.getByLabel('Toggle block inserter').click();
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
