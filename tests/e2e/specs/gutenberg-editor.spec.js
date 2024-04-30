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
        
        const popupOpened = new Promise(resolve => {
            page.on('popup', async () => {
                resolve(true);
            });
        });

        await page.getByLabel('Editor content').locator('a').click({ force: true});

        // Check if a new page is opened for chart creation.
        expect(await popupOpened).toBe(true);
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
        await page.locator('div').filter({ hasText: /^Display an existing chart$/ }).click();
        await page.locator('.visualizer-settings__charts-controls').first().click();
        
        expect( page.getByLabel('Block: Visualizer Chart').getByText('Visualizer') ).not.toBeNull();

        expect( page.getByRole('button', { name: 'Import data from file' }) ).toBeVisible();

        await page.getByRole('button', { name: 'Import data from file' }).click();
        await page.getByRole('button', { name: 'Import data from URL' }).click();

        await page.getByRole('button', { name: 'Advanced Options' }).click();
        await page.getByRole('button', { name: 'Chart Settings' }).click();

        await page.getByRole('button', { name: 'Chart Permissions' }).click();
        await page.getByRole('button', { name: 'Chart Settings' }).click();

        // Check if the block is still visible and has not crashed.
        await expect( page.getByRole('button', { name: 'Save', exact: true }) ).toBeVisible();
    } );
} );
