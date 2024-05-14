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

        expect( await page.frameLocator('iframe').locator('#vz-chart-source .viz-group-title .dashicons-lock').count() ).toBe( 5 );


        const uploadFileUpsell = page.frameLocator('iframe').locator('#vz-chart-source .visualizer_source_csv .only-pro-inner a');
        let href = await uploadFileUpsell.getAttribute('href');
        let searchParams = new URLSearchParams(href);
        expect( searchParams.get('utm_campaign') ).toBe('import-file');

        const remoteImportUpsell = page.frameLocator('iframe').locator('#vz-chart-source .visualizer_source_json .only-pro-inner a').first();
        href = await remoteImportUpsell.getAttribute('href');
        searchParams = new URLSearchParams(href);
        expect( searchParams.get('utm_campaign') ).toBe('import-url');

        const otherChartUpsell = page.frameLocator('iframe').locator('#vz-chart-source .viz-import-from-other .only-pro-inner a');
        href = await otherChartUpsell.getAttribute('href');
        searchParams = new URLSearchParams(href);
        expect( searchParams.get('utm_campaign') ).toBe('import-chart');

        const wpImportUpsell = page.frameLocator('iframe').locator('#vz-chart-source .visualizer_source_query_wp .only-pro-inner a');
        href = await wpImportUpsell.getAttribute('href');
        searchParams = new URLSearchParams(href);
        expect( searchParams.get('utm_campaign') ).toBe('import-wp');
        await page.frameLocator('iframe').getByRole('heading', { name: /Import from WordPress/ }).click();
        await expect(page.frameLocator('iframe').locator('#vz-chart-source')).toContainText('Upgrade to PRO to activate this feature!');

        const dbImportUpsell = page.frameLocator('iframe').locator('#vz-chart-source .visualizer_source_query .only-pro-inner a');
        href = await dbImportUpsell.getAttribute('href');
        searchParams = new URLSearchParams(href);
        expect( searchParams.get('utm_campaign') ).toBe('db-query');

        await page.frameLocator('iframe').getByRole('heading', { name: /Import from database/ }).click();
        await expect(page.frameLocator('iframe').locator('#vz-db-wizard')).toContainText('Upgrade to Plus plan to activate this feature!');
        await expect(page.frameLocator('iframe').locator('#vz-db-wizard')).toContainText('Upgrade Now');

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
        await page.frameLocator('iframe').getByRole('heading', { name: /Permissions/ }).click();
        await expect(page.frameLocator('iframe').locator('#vz-db-wizard')).toContainText('Upgrade to Plus plan to activate this feature!');
        await expect(page.frameLocator('iframe').locator('#vz-db-wizard')).toContainText('Upgrade Now');
    });

    test( 'featured tab in Install Plugin (SDK)', async ( { admin, page } ) => {
        await admin.visitAdminPage( 'plugin-install.php' );

        // Those should be visible only when a PRO product is installed.
        await expect( page.getByText('Image Optimization by Optimole') ).toBeHidden();
        await expect( page.locator('#the-list div').filter({ hasText: 'Otter Blocks' }).nth(1) ).toBeHidden();

        await expect( page.getByLabel('Install Image Optimization by') ).toBeHidden();
        await expect( page.getByLabel('Install Otter Blocks') ).toBeHidden();
    });
} );
