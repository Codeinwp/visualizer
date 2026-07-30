/**
 * WordPress dependencies
 */
const { test, expect } = require( '@wordpress/e2e-test-utils-playwright' );

/**
 * Internal dependencies
 */
const { deleteAllCharts, createChartWithAdmin } = require( '../utils/common' );

/**
 * Regression tests for https://github.com/Codeinwp/visualizer/issues/1319
 *
 * Charts with lazy rendering enabled load their scripts only after the first
 * user interaction, via `script[data-visualizer-script]` placeholders. The
 * loader must preserve the WordPress dependency order: if render-facade.js
 * executes before the chart renderer (render-google.js etc.) has registered
 * its `visualizer:render:chart:start` listener, the render event is lost and
 * the chart stays blank forever.
 */
test.describe( 'Lazy rendered charts (frontend)', () => {
	test.beforeEach( async ( { requestUtils } ) => {
		await deleteAllCharts( requestUtils );
	} );

	async function createChartOnPost( admin, page, requestUtils ) {
		// Lazy rendering is forced on by tests/e2e/config/force-lazy-render.php
		// (mapped as an mu-plugin in .wp-env.json).
		const chartId = await createChartWithAdmin( admin, page );

		const post = await requestUtils.createPost( {
			title: 'Lazy render',
			content: `[visualizer id="${ chartId }" lazy="no" class=""]`,
			status: 'publish',
		} );

		return { chartId, post };
	}

	async function triggerLazyLoader( page ) {
		// Assert lazy rendering is on via the container class, not the
		// `data-visualizer-script` placeholders: those are swapped away by the
		// first user interaction, and Chromium fires a real `mouseover` right
		// after navigation whenever the cursor sits over the page.
		await expect( page.locator( '.visualizer-lazy-render' ).first() ).toBeAttached();

		// The loader starts on the first user interaction.
		await page.evaluate( () => window.dispatchEvent( new Event( 'scroll' ) ) );
	}

	test( 'chart renders when the renderer script loads after render-facade.js', async ( { admin, page, requestUtils } ) => {
		const { chartId, post } = await createChartOnPost( admin, page, requestUtils );

		// Delay the chart renderer scripts so render-facade.js would win the
		// load race — the deterministic reproduction of issue #1319.
		await page.route( /js\/render-(google|chartjs|datatables)\.js/, async ( route ) => {
			await new Promise( ( resolve ) => setTimeout( resolve, 2000 ) );
			await route.continue();
		} );

		await page.goto( `/?p=${ post.id }` );
		await triggerLazyLoader( page );

		const chart = page.locator( `.visualizer-front-${ chartId }` ).first();
		await expect( chart ).toHaveClass( /visualizer-chart-loaded/, { timeout: 15000 } );
		await expect( chart.locator( 'svg, canvas, table' ).first() ).toBeVisible();
	} );

	test( 'chart renders without artificial script delay', async ( { admin, page, requestUtils } ) => {
		const { chartId, post } = await createChartOnPost( admin, page, requestUtils );

		await page.goto( `/?p=${ post.id }` );
		await triggerLazyLoader( page );

		const chart = page.locator( `.visualizer-front-${ chartId }` ).first();
		await expect( chart ).toHaveClass( /visualizer-chart-loaded/, { timeout: 15000 } );
		await expect( chart.locator( 'svg, canvas, table' ).first() ).toBeVisible();
	} );
} );
