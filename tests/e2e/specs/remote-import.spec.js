/**
 * WordPress dependencies
 */
const { test, expect } = require( '@wordpress/e2e-test-utils-playwright' );

/**
 * Internal dependencies
 */
const { deleteAllCharts, getAssetFilePath, CHART_JS_LABELS, selectChartAdmin } = require('../utils/common');

/**
 * The remote-import UI is Pro-gated, but the admin-ajax endpoint runs in the free
 * plugin, so these tests drive the endpoint directly (issue #591 SSRF fix).
 *
 * On error the endpoint responds with `alert("...")`; on success it assigns
 * `win.visualizer.charts.canvas.series` / `.data`.
 */
test.describe( 'Remote import (secure fetch)', () => {
    test.beforeEach( async ( { admin, requestUtils, page } ) => {
        await deleteAllCharts( requestUtils );
        page.setDefaultTimeout( 5000 );
    } );

    /**
     * Opens the classic builder and returns the upload-data admin-ajax URL
     * (carries the nonce and chart id) from the one-time-import form.
     */
    async function getUploadAction( admin, page ) {
        await admin.visitAdminPage( 'admin.php?page=visualizer&vaction=addnew' );
        await page.waitForURL( '**/admin.php?page=visualizer&vaction=addnew' );
        await page.getByRole('button', { name: 'Classic Builder Step-by-step' }).click();
        await page.waitForSelector('h1:text("Visualizer")');

        await selectChartAdmin( page.frameLocator('iframe'), CHART_JS_LABELS.table );

        return page.frameLocator('iframe').locator('#vz-one-time-import').getAttribute('action');
    }

    async function importFromUrl( page, action, url ) {
        const response = await page.request.post( action, {
            form: {
                remote_data: url,
                'vz-import-time': '-1',
            },
        } );
        return response.text();
    }

    test( 'blocks import from a non-public address', async ( { admin, page } ) => {
        const action = await getUploadAction( admin, page );

        const body = await importFromUrl( page, action, 'http://169.254.169.254/latest/meta-data/data.csv' );

        expect( body ).toContain( 'alert(' );
        expect( body ).not.toContain( 'canvas.series' );
    } );

    test( 'imports a CSV served from the site itself', async ( { admin, page, requestUtils } ) => {
        const media = await requestUtils.uploadMedia( getAssetFilePath( 'pie.csv' ) );
        const action = await getUploadAction( admin, page );

        // Inside the wp-env container the mapped port is unreachable; Apache serves the
        // same site on port 80, so PHP can only fetch the file through that.
        const containerUrl = media.source_url.replace( /:\d+\//, '/' );
        const body = await importFromUrl( page, action, containerUrl );

        expect( body ).not.toContain( 'alert(' );
        expect( body ).toContain( 'canvas.series' );
    } );
} );
