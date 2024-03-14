/**
 * WordPress dependencies
 */
const { Admin, expect, RequestUtils } = require( '@wordpress/e2e-test-utils-playwright' );

const path = require('path');
const os = require('os');

/**
 * Available chart types in Visualizer with Chart.js library.
 * Listed in the order they appear in the selector.
 */
export const CHART_JS_LABELS = {
    table: 'Table',
    pie: 'Pie/Donut',
    line: 'Line',
    bar: 'Bar',
    area: 'Area',
    geo: 'Geo',
    column: 'Column',
    bubble: 'Bubble',
    scatter: 'Scatter',
    gauge: 'Gauge',
    candlestick: 'Candlestick',
    timeline: 'Timeline',
    combo: 'Combo',
    polarArea: 'Polar Area',
    radar: 'Radar/Spider',
};

// NOTE: table takes longer to process it might stop the creation of the next chart. So it's better to create it last.
export const CHART_JS_FREE_CHARTS = [ 'line', 'bar', 'pie', 'table' ];

/**
 * Create a chart using the Visualizer admin page.
 * 
 * @param {Admin} admin The admin utility.
 * @param {import('playwright/test').Page} page The Playwright page object.
 * @param {string} chart_label The label of the chart to be created. Defaults to 'Pie/Donut'.
 * 
 * @return {Promise<string>} The id of the chart created.
 */
export async function createChartWithAdmin( admin, page, chart_label = CHART_JS_LABELS.pie ) {
    await admin.visitAdminPage( 'admin.php?page=visualizer&vaction=addnew' );

    await page.waitForURL( '**/admin.php?page=visualizer&vaction=addnew' );
    await page.waitForSelector('h1:text("Visualizer")', { timeout: 5000 });

    await selectChartAdmin( page.frameLocator('iframe'), chart_label );

    // Wait a little for the request to be processed.
    await expect( page.frameLocator('iframe').getByRole('heading', { name: 'Import data from URL' })).toBeVisible({ timeout: 5000 });
    
    const chartId = await page.frameLocator('iframe').locator('#visualizer-chart-id').getAttribute('data-id');

    // The string must be numeric.
    expect( chartId ).toMatch(/^\d+$/);

    await page.frameLocator('iframe').getByRole('button', { name: 'Create Chart' }).click({ timeout: 5000 });

    await page.waitForURL( '**/admin.php?page=visualizer', { timeout: 5000 } );

    return chartId;
}

/**
 * Delete all charts from the Visualizer plugin.
 * 
 * @param {RequestUtils} requestUtils The request utility.
 * @see https://github.com/WordPress/gutenberg/blob/8f2fad4c0aac7483624d7ab32be7499ce78e6998/packages/e2e-test-utils-playwright/src/request-utils/posts.ts#L26
 */
export async function deleteAllCharts( requestUtils ) {
    const posts = await requestUtils.rest( {
        path: '/wp/v2/visualizer',
        params: {
            per_page: 100,
            status: 'publish,future,draft,pending,private,trash',
        },
    } );
   
    await Promise.all(
        posts.map( ( post ) =>
            requestUtils.rest( {
                method: 'DELETE',
                path: `/wp/v2/visualizer/${ post.id }`,
                params: {
                    force: true,
                },
            } )
        )
    );
}

/**
 * Wait for the Visualizer library to load.
 * 
 * @param {Page} page The Playwright page object.
 * 
 * @return {Promise<void>} 
 */
export async function waitForLibraryToLoad( page ) {
    await expect( page.getByRole('heading', { name: 'Visualizer Library' }) ).toBeVisible({ timeout: 5000 });
}

/**
 * Create all free charts available in the Visualizer plugin.
 * 
 * @param {Admin} admin The admin utility. 
 * @param {*} page The Playwright page object.
 * @returns {Promise<string[]>} The ids of the charts created.
 */
export async function createAllFreeCharts( admin, page ) {
    const chartIds = [];

    for (const chart_label of CHART_JS_FREE_CHARTS) {
        const id = await createChartWithAdmin( admin, page, CHART_JS_LABELS[ chart_label ] );
        chartIds.push( id );
    }

    return chartIds;
}

/**
 * Get the path to the asset file.
 * 
 * @param {string} assetName The name of the asset file. (example.csv) 
 * @returns {string} The path to the asset file.
 */
export function getAssetFilePath( assetName ) {
    return path.join( __dirname, '..', 'assets', assetName )
}

/**
 * Select the chart in the Visualizer admin page.
 * 
 * @param {import('playwright/test').FrameLocator} iframeLocator The iframe locator that contains the chart picker.
 * @param {string} chartLabel The label of the chart to be selected.
 */
export async function selectChartAdmin(iframeLocator, chartLabel) {
    const labelRegex = new RegExp(`^${chartLabel}$`);
    await expect( iframeLocator.locator('div#type-picker').locator('label').filter({ hasText: labelRegex }) ).toBeVisible({ timeout: 5000 });
    await iframeLocator.locator('div#type-picker').locator('label').filter({ hasText: labelRegex }).click();
    await expect( iframeLocator.getByRole('button', { name: 'Next' }) ).toBeVisible({ timeout: 5000 });
    await iframeLocator.getByRole('button', { name: 'Next' }).click();
}