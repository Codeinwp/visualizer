/**
 * WordPress dependencies
 */
const { test, expect } = require( '@wordpress/e2e-test-utils-playwright' );

/**
 * Internal dependencies
 */
const { deleteAllCharts, getAssetFilePath, CHART_JS_LABELS, selectChartAdmin } = require('../utils/common');

test.describe( 'Data Free Sources', () => {
    test.beforeEach( async ( { admin, requestUtils, page } ) => {
        await deleteAllCharts( requestUtils );
        await admin.visitAdminPage( 'admin.php?page=visualizer' );
        page.setDefaultTimeout( 5000 );
    } );

    test( 'manual import', async ( { admin, page } ) => {
        await admin.visitAdminPage( 'admin.php?page=visualizer&vaction=addnew' );
        await page.waitForURL( '**/admin.php?page=visualizer&vaction=addnew' );
        await page.waitForSelector('h1:text("Visualizer")');
        
        await selectChartAdmin( page.frameLocator('iframe'), CHART_JS_LABELS.table );

        const manualDataTab = page.frameLocator('iframe').locator('li.viz-group.visualizer_source_manual');

        // add `open`class to the tab to make it visible.
        await manualDataTab.evaluate( ( node ) => node.classList.add('open') );

        await page.frameLocator('iframe').getByLabel('Month: activate to sort').click();
        await page.frameLocator('iframe').getByRole('button', { name: 'Edit Data' }).click();

        await page.frameLocator('iframe').getByRole('button', { name: 'Show Chart' }).click();
        await page.waitForTimeout( 500 );
        await page.frameLocator('iframe').getByRole('button', { name: 'Create Chart' }).click();
     
        await expect( page.locator('table').filter({ hasText: /^MonthBoliviaEcuadorMadagascarPapua New GuineaRwanda$/ }).getByLabel('Month: activate to sort') ).toBeVisible();
    });
} );