/**
 * WordPress dependencies
 */
const { Admin, expect, RequestUtils } = require( '@wordpress/e2e-test-utils-playwright' );

/**
 * Create a chart using the Visualizer admin page.
 * 
 * @param {Admin} admin The admin utility.
 * @param {import('playwright/test').Page} page The Playwright page object.
 * 
 * @return {Promise<string>} The id of the chart created.
 */
export async function createChartWithAdmin( admin, page ) {
    await admin.visitAdminPage( 'admin.php?page=visualizer&vaction=addnew' );
    
    await page.waitForSelector('h1:text("Visualizer")', { timeout: 5000 });

    const chartView = page.frameLocator('iframe');
    const chartContainer = chartView.locator('div#type-picker');
    
    // Create a Pie chart
    await chartContainer.locator('label').filter({ hasText: 'Pie/Donut' }).click();
    await chartView.getByRole('button', { name: 'Next' }).click();

    // Extract the chart it to be returned. The id is in the element with id `visualizer-chart-id` under `data-id` attribute.
    const chartId = await page.frameLocator('iframe').locator('#visualizer-chart-id').getAttribute('data-id');

    // The string must be numeric.
    expect( chartId ).toMatch(/^\d+$/);

    await page.frameLocator('iframe').getByRole('button', { name: 'Create Chart' }).click({ timeout: 5000 });

    await waitForLibraryToLoad( page );

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