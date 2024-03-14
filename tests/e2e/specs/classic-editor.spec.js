/**
 * WordPress dependencies
 */
const { test, expect } = require( '@wordpress/e2e-test-utils-playwright' );

/**
 * Internal dependencies
 */
const { deleteAllCharts, createChartWithAdmin } = require('../utils/common');

test.describe( 'Charts with Classic Editor', () => {
    test.beforeAll( async ( { requestUtils } ) => {
        await deleteAllCharts( requestUtils );
        await requestUtils.activatePlugin( 'classic-editor' );
    } );

    test.afterAll( async ( { requestUtils } ) => {
        await requestUtils.deactivatePlugin( 'classic-editor' );
    } );

    test('insert chart into a Post via Media', async({ admin, page }) => {
        const chartId = await createChartWithAdmin( admin, page );
        await admin.visitAdminPage( 'post-new.php' );
        
        await page.getByRole('button', { name: 'Add Media' }).click();
        await page.getByRole('tab', { name: 'Visualizations' }).click();

        await page.waitForSelector('.visualizer-library-chart', {timeout: 5000});

        await page.locator('.visualizer-library-chart').click({ force: true});

        await expect( page.frameLocator('iframe[title*="Rich Text Area"]').getByText(`[visualizer id="${chartId}"]`) ).toBeVisible();
    });
});