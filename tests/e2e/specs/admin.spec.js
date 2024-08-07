/**
 * WordPress dependencies
 */
const { test, expect } = require( '@wordpress/e2e-test-utils-playwright' );

/**
 * Internal dependencies
 */
const { createChartWithAdmin, deleteAllCharts, waitForLibraryToLoad, createAllFreeCharts, CHART_JS_LABELS, selectChartAdmin } = require('../utils/common');

test.describe( 'Chart Library', () => {

    test.beforeEach( async ( { admin, requestUtils } ) => {
        await deleteAllCharts( requestUtils );
        await admin.visitAdminPage( 'admin.php?page=visualizer' );
        // Delate all the post types with type visualizer.
    } );

    test( 'check Add New button', async ( { page} ) => {
        await expect(page.getByRole('heading', { name: 'Visualizer Library Add New' }).getByRole('link')).toBeVisible();
    } );

    test( 'check filters options', async ( { page } ) => {

        // CHART TYPE FILTER
        await expect( page.locator('select[name="type"]') ).toBeVisible();
        await expect( page.locator('select[name="type"] option').count() ).resolves.toBe( 15 + 1 );

        // LIBRARY FILTER
        const libraryOptions = ['ChartJs', 'DataTable', 'GoogleCharts', 'All libraries'];
        await expect( page.locator('select[name="library"]') ).toBeVisible();
        await expect( page.locator('select[name="library"] option').count() ).resolves.toBe( libraryOptions.length );
        for (const option of libraryOptions) {
            await expect( page.locator('select[name="library"] option').filter({ hasText: option }).count() ).resolves.toBe( 1 );
        }

        // DATE FILTER
        const dateOptions = ['Yesterday', 'Last Week', 'Last Month', 'Last Year', 'All dates'];
        await expect( page.locator('select[name="date"]') ).toBeVisible();
        await expect( page.locator('select[name="date"] option').count() ).resolves.toBe( dateOptions.length );
        for (const option of dateOptions) {
            await expect( page.locator('select[name="date"] option').filter({ hasText: option }).count() ).resolves.toBe( 1 );
        }

        // SOURCES FILTER
        const sourcesOptions = ['All sources', 'Database', 'JSON', 'Local CSV', 'Remote CSV', 'WordPress'];
        await expect( page.locator('select[name="source"]') ).toBeVisible();
        await expect( page.locator('select[name="source"] option').count() ).resolves.toBe( sourcesOptions.length );
        for (const option of sourcesOptions) {
            await expect( page.locator('select[name="source"] option').filter({ hasText: option }).count() ).resolves.toBe( 1 );
        }

        // TITLE FILTER
        await expect( page.getByPlaceholder('Enter title') ).toBeVisible();

        // ORDERBY FILTER
        const orderbyOptions = ['Date', 'Title', 'Order By'];
        await expect( page.locator('select[name="orderby"]') ).toBeVisible();
        await expect( page.locator('select[name="orderby"] option').count() ).resolves.toBe( orderbyOptions.length );
        for (const option of orderbyOptions) {
            await expect( page.locator('select[name="orderby"] option').filter({ hasText: option }).count() ).resolves.toBe( 1 );
        }

        // ORDER FILTER
        const orderOptions = ['Ascending', 'Descending'];
        await expect( page.locator('select[name="order"]') ).toBeVisible();
        await expect( page.locator('select[name="order"] option').count() ).resolves.toBe( orderOptions.length );
        for (const option of orderOptions) {
            await expect( page.locator('select[name="order"] option').filter({ hasText: option }).count() ).resolves.toBe( 1 );
        }

        // ACTIONS
        await expect( page.getByRole('button', { name: 'Apply Filters' }) ).toBeVisible();
        await expect( page.getByRole('button', { name: 'Clear Filters' }) ).toBeVisible();
    } );

    test('create a chart', async ( { page, admin } ) => {
        const chartId = await createChartWithAdmin( admin, page );
        await admin.visitAdminPage( 'admin.php?page=visualizer' );
        await expect( page.locator(`#visualizer-${chartId}`).count() ).resolves.toBeGreaterThan( 0 );

        // Check shortcode generated.
        const chartContainer = page.locator(`.visualizer-chart`, { has: page.locator(`#visualizer-${chartId}`) });
        expect( chartContainer ).toBeVisible();
        await chartContainer.locator('a.visualizer-chart-shortcode').click();
        let shortcodeClipboard = await page.evaluate("navigator.clipboard.readText()");
        expect(shortcodeClipboard).toMatch(/\[visualizer\sid="\d+"\sclass=""]/);

    } );

    test('clone/duplicate a chart', async ( { page, admin } ) => {
        const chartId = await createChartWithAdmin( admin, page );
        await admin.visitAdminPage( 'admin.php?page=visualizer' );

        // Count the current charts, then compare after cloning.
        const chartsCount = await page.locator('.visualizer-chart').count();

        // Select the chart and clone it.
        const chartContainer = page.locator(`.visualizer-chart`, { has: page.locator(`#visualizer-${chartId}`) });
        expect( chartContainer ).toBeVisible();
        await chartContainer.locator('a.visualizer-chart-clone').click({ timeout: 5000 });

        await waitForLibraryToLoad( page );
        const newChartsCount = await page.locator('.visualizer-chart').count();

        expect( newChartsCount ).toBeGreaterThan( chartsCount );
    } );

    test('delete a chart', async ( { page, admin } ) => {
        const chartId = await createChartWithAdmin( admin, page );
        await admin.visitAdminPage( 'admin.php?page=visualizer' );

        await expect( page.locator(`#visualizer-${chartId}`).count() ).resolves.toBeGreaterThan( 0 );

        // Accept the dialog to delete the chart.
        page.on('dialog', dialog => dialog.accept());

        // Select the chart and delete it.
        const chartContainer = page.locator(`.visualizer-chart`, { has: page.locator(`#visualizer-${chartId}`) });
        expect( chartContainer ).toBeVisible();
        await chartContainer.locator('a.visualizer-chart-delete').click({ timeout: 5000 });

        await waitForLibraryToLoad( page );
        await expect( page.locator(`#visualizer-${chartId}`).count() ).resolves.toBe( 0 );
    } );

    test( 'create all free charts', async ( { admin, page } ) => {
        const chartsId = await createAllFreeCharts( admin, page );
        await waitForLibraryToLoad( page );

        await expect( page.locator('.visualizer-chart').count() ).resolves.toBe( 4 );
        for (const chartId of chartsId) {
            await expect( page.locator(`#visualizer-${chartId}`).count() ).resolves.toBeGreaterThan( 0 );
        }
    } );

    test( 'cancel second step of chart creation', async ( { admin, page } ) => {
        await admin.visitAdminPage( 'admin.php?page=visualizer&vaction=addnew' );
        await page.waitForURL( '**/admin.php?page=visualizer&vaction=addnew' );
        await page.waitForSelector('h1:text("Visualizer")');

        await selectChartAdmin( page.frameLocator('iframe'), CHART_JS_LABELS.table );
       
        const backendTitle = await page.frameLocator('iframe').locator('input[name="backend-title"]').inputValue();

        await page.frameLocator('iframe').getByRole('button', { name: 'Cancel' }).click();
        await waitForLibraryToLoad( page );

        // The iframe should be closed.
        await expect( page.frameLocator('iframe').getByRole('button', { name: 'Create Chart' }) ).toBeHidden();

        // The chart should not be created.
        await expect( page.locator('.visualizer-chart-title').filter({ hasText: backendTitle }).count() ).resolves.toBe( 0 );
    } );

    test( 'chart filtering', async ( { admin, page } ) => {
        await admin.visitAdminPage( 'admin.php?page=visualizer&vaction=addnew' );
        await page.waitForURL( '**/admin.php?page=visualizer&vaction=addnew' );
        await page.waitForSelector('h1:text("Visualizer")');

        await page.frameLocator('iframe').locator('label').filter({ hasText: 'Pie/Donut' }).click();

        expect( page.locator('.viz-hidden') ).toHaveCount(0) // No hidden charts by default.

        await page.frameLocator('iframe').getByRole('combobox').selectOption({ value: 'ChartJS' });

        await expect( page.frameLocator('iframe').locator('.viz-hidden').count() ).resolves.toBeGreaterThan( 0 ); // We should have hidden charts.
    });

    test( 'check info panel', async ( { admin, page } ) => {
        await admin.visitAdminPage( 'admin.php?page=visualizer&vaction=addnew' );
        await page.waitForURL( '**/admin.php?page=visualizer&vaction=addnew' );
        await page.waitForSelector('h1:text("Visualizer")');

        await selectChartAdmin( page.frameLocator('iframe'), CHART_JS_LABELS.pie );

        await expect( page.frameLocator('iframe').locator('#viz-shortcode') ).toBeVisible();
        await expect( page.frameLocator('iframe').getByRole('button', { name: 'Copy' }) ).toBeVisible();
        await expect( page.frameLocator('iframe').locator('#viz-backend-name') ).toBeVisible();

        // Check if the shortcode is copied to the clipboard.
        const shortcode = await page.frameLocator('iframe').locator('#viz-shortcode').inputValue();
        await page.frameLocator('iframe').getByRole('button', { name: 'Copy' }).click();
        const clipboardValue = await page.evaluate(() => navigator.clipboard.readText());
        expect( clipboardValue ).toBe( shortcode );

        const backendName = 'Test Backend Name';
        const chartName   = 'Test Chart Name';

        await page.frameLocator('iframe').locator('#viz-backend-name').fill(backendName);
        const titleValue = await page.frameLocator('iframe').locator('#settings-form input[name="backend-title"]').inputValue();
        expect( titleValue ).toBe( backendName );

        await page.frameLocator('iframe').getByRole('link', { name: 'Settings' }).click();
        await page.frameLocator('iframe').getByRole('heading', { name: 'General Settings' }).click();
        await page.frameLocator('iframe').getByText('Title', { exact: true }).click();

        await page.frameLocator('iframe').locator('input[name="title"]').fill(chartName);
        const internalNameValue = await page.frameLocator('iframe').locator('#viz-backend-name').inputValue();
        expect( internalNameValue ).not.toBe( chartName ); // Backend title should not be changed by chart title.

        await page.frameLocator('iframe').getByRole('button', { name: 'Create Chart' }).click();
        await waitForLibraryToLoad( page );

        await expect( page.getByText(backendName) ).toBeVisible();
        await expect( page.locator('g').filter({ hasText: 'Test Chart Name' }).locator('rect') ).toBeVisible();
    } );
} );

test.describe( 'Support', () => {
    test.beforeEach( async ( { admin } ) => {
        await admin.visitAdminPage( 'admin.php?page=viz-support' );
    } );

    test( 'check Support tab', async ( { page } ) => {
        await expect( page.getByRole('link', { name: 'Support', exact: true }) ).toBeVisible();
        await expect( page.getByRole('heading', { name: 'Welcome to Visualizer!' }) ).toBeVisible();
        await expect( page.getByRole('heading', { name: 'Documentation' }) ).toBeVisible();
        await expect( page.getByRole('heading', { name: 'Need help?' }) ).toBeVisible();
    } );

    test('help us improve tab', async ( { page } ) => {
        await page.getByRole('link', { name: 'Help us improve!' }).click();
        await expect( page.getByRole('heading', { name: 'Answer a few questions for us' }) ).toBeVisible();
        await expect( page.getByRole('link', { name: 'survey' }) ).toBeVisible();
    } );

    test('check Chart selection', async ( { page, admin } ) => {
        await admin.visitAdminPage( 'admin.php?page=visualizer' );
        await page.getByRole('heading', { name: 'Visualizer Library Add New' }).getByRole('link').click();
        await expect(page.frameLocator('iframe').getByText('Select Library for charts')).toBeVisible();
        await expect(page.frameLocator('iframe').getByRole('combobox')).toBeVisible();
        await expect(page.frameLocator('iframe').locator('#chart-select')).toContainText('Select Library for charts');
        // Check Default Option
        await expect(page.frameLocator('iframe').getByRole('combobox')).toHaveValue('GoogleCharts');

        // Check disabled Options
        await expect(page.frameLocator('iframe').locator('#chart-select > select option')).toHaveCount(3);
        await expect(page.frameLocator('iframe').locator('#chart-select > select option')).toHaveText( [ "DataTable", "Google Charts", "ChartJS" ] );
    } );
} );
