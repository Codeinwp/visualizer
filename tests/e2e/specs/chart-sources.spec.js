/**
 * WordPress dependencies
 */
const { test, expect } = require( '@wordpress/e2e-test-utils-playwright' );
const { deleteAllCharts, getAssetFilePath, CHART_JS_LABELS, selectChartAdmin } = require('../utils/common');

test.describe( 'Data Free Sources', () => {
    test.beforeEach( async ( { admin, requestUtils, page } ) => {
        await deleteAllCharts( requestUtils );
        await admin.visitAdminPage( 'admin.php?page=visualizer' );
        page.setDefaultTimeout( 5000 );
    } );

    test( 'import a local CSV file', async ( { admin, page } ) => {
        await admin.visitAdminPage( 'admin.php?page=visualizer&vaction=addnew' );
        await page.waitForURL( '**/admin.php?page=visualizer&vaction=addnew' );
        await page.waitForSelector('h1:text("Visualizer")');
        
        await selectChartAdmin( page.frameLocator('iframe'), CHART_JS_LABELS.pie );
        await page.frameLocator('iframe').getByRole('button', { name: 'Next' }).click();
        
        // Upload the CSV file
        await expect( page.frameLocator('iframe').locator('#csv-file') ).toBeVisible();
        await page.frameLocator('iframe').locator('#csv-file').setInputFiles( getAssetFilePath( 'pie.csv' ) );
        await page.frameLocator('iframe').getByRole('button', { name: 'Import' }).click();

       
        await page.frameLocator('iframe').getByRole('button', { name: 'Create Chart' }).click();

        // Check if the chart contains the data from the CSV file
        await expect( page.locator('text').filter({ hasText: 'Warcraft' }) ).toBeVisible();
    } );

    test( 'import a remote CSV file', async ( { admin, page } ) => {
        const importURL = 'https://s3.amazonaws.com/verti-utils/samples-visualizer/area.csv';

        await admin.visitAdminPage( 'admin.php?page=visualizer&vaction=addnew' );
        await page.waitForURL( '**/admin.php?page=visualizer&vaction=addnew' );
        await page.waitForSelector('h1:text("Visualizer")');
  
        await selectChartAdmin( page.frameLocator('iframe'), CHART_JS_LABELS.area );
        await  page.frameLocator('iframe').getByRole('button', { name: 'Next' }).click();
        
        // Fill the URL of the CSV file and import it.
        await page.frameLocator('iframe').getByRole('heading', { name: 'Import data from URL' }).click();
        await page.frameLocator('iframe').getByText('Import from CSV').click();
        await page.frameLocator('iframe').getByPlaceholder('Please enter the URL of CSV').fill( importURL );
        await page.frameLocator('iframe').getByRole('button', { name: 'Import' }).click();
        await expect( page.frameLocator('iframe').getByRole('img').getByText('Bolivia') ).toBeVisible();
        
        await page.frameLocator('iframe').getByRole('button', { name: 'Create Chart' }).click();

        // Check if the chart contains the data from the CSV file
        await expect( page.locator('text').filter({ hasText: '/05/01' }) ).toBeVisible();
    } );

    test( 'import a remote JSON file', async ( { admin, page } ) => {
        const importURL = 'https://s3.amazonaws.com/verti-utils/samples-visualizer/test.json';

        await admin.visitAdminPage( 'admin.php?page=visualizer&vaction=addnew' );
        await page.waitForURL( '**/admin.php?page=visualizer&vaction=addnew' );
        await page.waitForSelector('h1:text("Visualizer")');
    
        await selectChartAdmin( page.frameLocator('iframe'), CHART_JS_LABELS.table );
        await  page.frameLocator('iframe').getByRole('button', { name: 'Next' }).click();

        await page.frameLocator('iframe').getByRole('heading', { name: 'Import data from URL' }).click();
        await page.frameLocator('iframe').getByText('Import from JSON').click();
        await page.frameLocator('iframe').getByRole('button', { name: 'Create Parameters' }).click();

        await page.frameLocator('iframe').getByPlaceholder('Please enter the URL', { exact: true }).fill( importURL );
        await page.frameLocator('iframe').getByRole('button', { name: 'Fetch Endpoint' }).click({ force: true});

        await page.frameLocator('iframe').getByRole('button', { name: 'Create Chart' }).click();

        // Check if the chart contains the data from the JSON file
        await expect( page.getByRole('gridcell', { name: '/05/01' }) ).toBeVisible();
    } );
} );