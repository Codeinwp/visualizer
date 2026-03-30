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
        await page.addStyleTag({ content: '#wpfooter{display:none !important;}' });

        // Step 1
        await expect(page.getByLabel('Pie/Donut chart', { exact: true })).toBeVisible();
        await page.getByLabel('Bar chart', { exact: true }).check();
        await expect(page.getByLabel('Line chart', { exact: true })).toBeVisible();
        await expect(page.getByLabel('Bar chart', { exact: true })).toBeVisible();
        await expect(page.getByRole('button', { name: /Save And Continue/i })).toBeVisible();
        await expect(page.getByRole('link', { name: ' Go to dashboard' })).toBeVisible();
        await page.getByRole('button', { name: /Save And Continue/i }).click();

        // Step 2
        await expect(page.getByRole('heading', { name: 'Insert a chart into the draft page' })).toBeVisible();
        await expect(page.getByRole('button', { name: /Save And Continue/i })).toBeVisible();
        await page.getByRole('button', { name: /Save And Continue/i }).click();

        // Step 3
        await expect(page.getByRole('heading', { name: 'You\'re all set!' })).toBeVisible();
        await expect(page.getByText('Boost your website speed')).toBeVisible();
        await expect(page.getByText('Build better pages')).toBeVisible();
        await expect(page.getByText('Cache your pages')).toBeVisible();
        await expect(page.getByText('Stay in the loop')).toBeVisible();

        const optimoleCheckbox = page.locator('#enable_performance');
        if (await optimoleCheckbox.isEnabled()) {
            await optimoleCheckbox.uncheck();
        }
        const otterCheckbox = page.locator('#enable_otter_blocks');
        if (await otterCheckbox.isEnabled()) {
            await otterCheckbox.uncheck();
        }
        const spcCheckbox = page.locator('#enable_page_cache');
        if (await spcCheckbox.isEnabled()) {
            await spcCheckbox.uncheck();
        }
        await page.locator('#enable_newsletter').uncheck();

        await expect(page.getByRole('button', { name: 'Finish Setup' })).toBeVisible();
    } );

} );
