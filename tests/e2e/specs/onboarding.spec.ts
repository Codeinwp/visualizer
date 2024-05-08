/**
 * WordPress dependencies
 */
import {test, expect} from '@wordpress/e2e-test-utils-playwright';

const { deleteAllCharts } = require('../utils/common');

async function setOption( page, optionName, optionValue ) {
    await page.waitForSelector( `#${ optionName }` );
    const previousValue = await page.$eval(
        `#${ optionName }`,
        ( element ) => element.value
    );

    await page.focus( `#${ optionName }` );
    await page.type( `#${ optionName }`, optionValue );

    await Promise.all( [
        page.click( '#Update' ),
        page.waitForNavigation( { waitUntil: 'networkidle0' } ),
    ] );
}

test.describe( 'Onboarding', () => {

    test.beforeEach( async ( { page, admin, requestUtils } ) => {
        await deleteAllCharts( requestUtils );
        await admin.visitAdminPage( 'options.php' );
        await setOption( page,'visualizer_fresh_install', '1' );
    } );

    test( 'Check the setup wizard', async ( { page, admin} ) => {
        await admin.visitAdminPage( 'admin.php?page=visualizer-setup-wizard#step-1' );

        // Step 1
        await expect(page.getByLabel('Pie/Donut chart', { exact: true })).toBeVisible();
        await page.getByLabel('Bar chart', { exact: true }).check();
        await expect(page.getByLabel('Line chart', { exact: true })).toBeVisible();
        await expect(page.getByLabel('Bar chart', { exact: true })).toBeVisible();
        await page.getByLabel('Next', { exact: true }).click();
        await page.getByLabel('Next', { exact: true }).click();
        await page.getByLabel('Next', { exact: true }).click();
        await page.getByLabel('Next', { exact: true }).click();
        await expect(page.getByText('Discover the power of PRO! 11')).toBeVisible();
        await expect(page.getByRole('button', { name: 'Save And Continue ' })).toBeVisible();
        await expect(page.getByRole('link', { name: ' Go to dashboard' })).toBeVisible();
        await page.getByRole('button', { name: 'Save And Continue ' }).click();

        // Step 2
        await expect(page.getByLabel('You\'re almost done! We use').locator('h2')).toContainText('You\'re almost done!');
        await expect(page.getByRole('heading', { name: 'Importing demo data' })).toBeVisible();
        await expect(page.getByText('Done! Demo data has been successfully imported.')).toBeVisible();
        await expect(page.getByLabel('You\'re almost done! We use')).toContainText('Import data from other charts, WordPress, databases, or manual data entries using Visualizer');
        await page.getByRole('button', { name: 'Continue ' }).click();

        // Step 3
        await expect(page.getByRole('heading')).toContainText('Insert a chart into the draft page');
        await expect(page.getByRole('button', { name: 'Create a draft page We will' })).toBeVisible();
        await page.getByRole('button', { name: 'Create a draft page We will' }).click();
        await expect(page.getByRole('heading', { name: 'Chart preview' })).toBeVisible();
        await page.getByRole('button', { name: 'Create a draft page We will' }).click();
        await page.getByRole('button', { name: 'Save And Continue ' }).click();

        // Step 4
        await expect(page.getByRole('heading')).toContainText('Extra Features');
        await expect(page.getByRole('button', { name: 'Enable perfomance features' })).toBeVisible();
        await page.getByRole('button', { name: 'Enable perfomance features' }).click();
        await expect(page.getByLabel('Extra Features We\'re').getByRole('listitem')).toContainText('Improve your website speed and images by 80% with Optimole');
        await expect(page.getByRole('button', { name: 'Improve now' })).toBeVisible();
        await page.getByRole('checkbox').uncheck();
        await expect(page.getByLabel('Extra Features We\'re')).toContainText('Skip Improvement');
        await page.getByRole('button', { name: 'Skip Improvement' }).click();

        // Step 5
        await expect(page.getByRole('heading')).toContainText('Updates, tutorials, special offers, and more');
        await expect(page.getByLabel('Updates, tutorials, special')).toContainText('Get exclusive access of Visualizer newsletter');
        await expect(page.getByLabel('Updates, tutorials, special').locator('img')).toBeVisible();
        await expect(page.getByPlaceholder('test1@xx.com')).toBeVisible();
        await page.getByPlaceholder('test1@xx.com').click();
        await page.getByPlaceholder('test1@xx.com').fill('test@email.com');
        await expect(page.getByLabel('Updates, tutorials, special')).toContainText('Send Me Access');
        await expect(page.getByLabel('Updates, tutorials, special')).toContainText('Skip, Don’t give me access');
        await page.getByRole('button', { name: 'Skip, Don’t give me access' }).click();
    } );

} );
