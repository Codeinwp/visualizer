/**
 * WordPress dependencies
 */
const { test, expect } = require( '@wordpress/e2e-test-utils-playwright' );

/**
 * Internal dependencies
 */
const { deleteAllCharts, getAssetFilePath, CHART_JS_LABELS, selectChartAdmin } = require('../utils/common');

test.describe( 'Upsell', () => {
    test.beforeEach( async ( { admin, requestUtils, page } ) => {
        await deleteAllCharts( requestUtils );
        await admin.visitAdminPage( 'admin.php?page=visualizer' );
        page.setDefaultTimeout( 5000 );
    } );

    test( 'chart selection on admin', async ( { admin, page } ) => {
        await admin.visitAdminPage( 'admin.php?page=visualizer&vaction=addnew' );
        await page.waitForURL( '**/admin.php?page=visualizer&vaction=addnew' );
        await page.waitForSelector('h1:text("Visualizer")');

        expect( await page.frameLocator('iframe').locator('.pro-upsell').count() ).toBe( 11 );
       
        const proUpsellElements = await page.frameLocator('iframe').locator('a.pro-upsell').all();
        
        for (const element of proUpsellElements) {
            const href = await element.getAttribute('href');
            const searchParams = new URLSearchParams(href);
            expect( searchParams.get('utm_campaign') ).toBe('charttypes');
        }
    } );

    test( 'chart settings on admin', async ( { admin, page } ) => {
        await admin.visitAdminPage( 'admin.php?page=visualizer&vaction=addnew' );
        await page.waitForURL( '**/admin.php?page=visualizer&vaction=addnew' );
        await page.waitForSelector('h1:text("Visualizer")');
        await selectChartAdmin( page.frameLocator('iframe'), CHART_JS_LABELS.pie );

        await expect( page.frameLocator('iframe').locator( '#viz-tabs' ) ).toBeVisible();

        expect( await page.frameLocator('iframe').locator('#vz-chart-source .viz-group-title .dashicons-lock').count() ).toBe( 4 );

        const otherChartUpsell = page.frameLocator('iframe').locator('#vz-chart-source .viz-import-from-other .only-pro-inner a');
        let href = await otherChartUpsell.getAttribute('href');
        let searchParams = new URLSearchParams(href);
        expect( searchParams.get('utm_campaign') ).toBe('import-chart');

        const wpImportUpsell = page.frameLocator('iframe').locator('#vz-chart-source .visualizer_source_query_wp .only-pro-inner a');
        href = await wpImportUpsell.getAttribute('href');
        searchParams = new URLSearchParams(href);
        expect( searchParams.get('utm_campaign') ).toBe('import-wp');

        const dbImportUpsell = page.frameLocator('iframe').locator('#vz-chart-source .visualizer_source_query .only-pro-inner a');
        href = await dbImportUpsell.getAttribute('href');
        searchParams = new URLSearchParams(href);
        expect( searchParams.get('utm_campaign') ).toBe('db-query');

        const manualDataUpsell = page.frameLocator('iframe').locator('#vz-chart-source .visualizer_source_manual .only-pro-inner a');
        href = await manualDataUpsell.getAttribute('href');
        searchParams = new URLSearchParams(href);
        expect( searchParams.get('utm_campaign') ).toBe('manual-data');

        await page.frameLocator('iframe').getByRole('link', { name: 'Settings' }).click();

        const dataFilterConfigurationUpsell = page.frameLocator('iframe').locator('#vz-data-controls .only-pro-inner a');
        href = await dataFilterConfigurationUpsell.getAttribute('href');
        searchParams = new URLSearchParams(href);
        expect( searchParams.get('utm_campaign') ).toBe('data-filter-configuration');

        const frontendActionsUpsell = page.frameLocator('iframe').locator('#vz-frontend-actions .only-pro-inner a');
        href = await frontendActionsUpsell.getAttribute('href');
        searchParams = new URLSearchParams(href);
        expect( searchParams.get('utm_campaign') ).toBe('frontend-actions');

        const chartPermissionsUpsell = page.frameLocator('iframe').locator('#vz-permissions .only-pro-inner a');
        href = await chartPermissionsUpsell.getAttribute('href');
        searchParams = new URLSearchParams(href);
        expect( searchParams.get('utm_campaign') ).toBe('chart-permissions');
    });
} );