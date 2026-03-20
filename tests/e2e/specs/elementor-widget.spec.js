/**
 * WordPress dependencies
 */
const { test, expect } = require( '@wordpress/e2e-test-utils-playwright' );

/**
 * Internal dependencies
 */
const { createChartWithAdmin, deleteAllCharts } = require( '../utils/common' );

/**
 * How long to wait for the Elementor editor chrome (panel + preview iframe) to be ready.
 */
const ELEMENTOR_LOAD_TIMEOUT = 30000;

/**
 * How long to wait for a chart to finish rendering inside the preview iframe.
 */
const CHART_RENDER_TIMEOUT = 15000;

/**
 * Navigate to the Elementor editor for a given page ID, wait until it is ready,
 * then dismiss any first-run modals/panels Elementor shows.
 *
 * @param {import('@wordpress/e2e-test-utils-playwright').Admin} admin
 * @param {import('playwright/test').Page} page
 * @param {number} pageId
 */
async function openElementorEditor( admin, page, pageId ) {
	await admin.visitAdminPage( `post.php?post=${ pageId }&action=elementor` );
	await page.waitForSelector( '#elementor-preview-iframe', { timeout: ELEMENTOR_LOAD_TIMEOUT } );
	await page.waitForSelector( '#elementor-panel', { timeout: ELEMENTOR_LOAD_TIMEOUT } );
	await page.waitForSelector( '#elementor-panel-state-loading', { state: 'hidden', timeout: ELEMENTOR_LOAD_TIMEOUT } );

	// Dismiss any first-run modals/panels Elementor shows (notifications dialog,
	// onboarding checklist, etc.) that would block panel interactions.
	await dismissElementorModals( page );
}

/**
 * Close any Elementor modal dialogs or floating panels that appear on first launch.
 *
 * @param {import('playwright/test').Page} page
 */
async function dismissElementorModals( page ) {
	// Elementor's "What's New" / notifications lightbox — dismiss via "Skip" button.
	const skipBtn = page.locator( 'button:has-text("Skip"), button:has-text("Maybe Later")' ).first();
	if ( await skipBtn.isVisible( { timeout: 1500 } ).catch( () => false ) ) {
		await skipBtn.click();
		await page.waitForTimeout( 300 );
	}

	// Generic lightbox close button (same dialog, alternative close path).
	const lightboxClose = page.locator( '.dialog-lightbox-close-button' ).first();
	if ( await lightboxClose.isVisible( { timeout: 1000 } ).catch( () => false ) ) {
		await lightboxClose.click();
		await page.waitForTimeout( 300 );
	}

	// Onboarding / "productivity boost" checklist panel.
	const onboardingClose = page.locator( '.e-onboarding__go-pro-close-btn, [data-action="close"]' ).first();
	if ( await onboardingClose.isVisible( { timeout: 1000 } ).catch( () => false ) ) {
		await onboardingClose.click();
		await page.waitForTimeout( 300 );
	}

	// Navigator / Structure panel (opens automatically on some versions).
	const navigatorClose = page.locator( '#elementor-navigator__close' ).first();
	if ( await navigatorClose.isVisible( { timeout: 500 } ).catch( () => false ) ) {
		await navigatorClose.click();
	}
}

/**
 * Search for the Visualizer widget in the Elementor panel and drag it onto
 * the preview canvas.  Resolves once the widget wrapper is present in the iframe.
 *
 * @param {import('playwright/test').Page} page
 * @returns {Promise<void>}
 */
async function addVisualizerWidget( page ) {
	// If the panel is in widget-edit mode, press Escape to deselect and return
	// to the elements list.  The search box is the definitive indicator.
	const searchInput = page.locator( '#elementor-panel-elements-search-input' );
	if ( ! await searchInput.isVisible( { timeout: 1000 } ).catch( () => false ) ) {
		await page.keyboard.press( 'Escape' );
		await searchInput.waitFor( { timeout: 5000 } );
	}
	await searchInput.fill( '' );
	await searchInput.fill( 'visualizer' );

	// The widget card — use text filter because data-element_type lives on the
	// inner .elementor-element div, not on the .elementor-element-wrapper.
	const widgetHandle = page.locator( '.elementor-element-wrapper' )
		.filter( { hasText: 'Visualizer Chart' } )
		.first();
	await widgetHandle.waitFor( { timeout: 5000 } );

	// Clicking the widget card adds it to the page in Elementor.
	await widgetHandle.click();

	const previewFrame = page.frameLocator( '#elementor-preview-iframe' );

	// Wait until the widget wrapper appears in the preview and the panel
	// switches to the widget-settings view.
	await previewFrame
		.locator( '[data-widget_type="visualizer-chart.default"]' )
		.waitFor( { timeout: 10000 } );
}

/**
 * Select the first real chart from the widget's dropdown in the Elementor panel.
 *
 * @param {import('playwright/test').Page} page
 */
async function selectFirstChart( page ) {
	// data-setting="chart_id" is on the <select> element itself.
	// The placeholder option is last in the list so index 0 is the first real chart.
	// Use evaluate + dispatchEvent to ensure Elementor's change handler fires.
	const select = page.locator( 'select[data-setting="chart_id"]' );
	await select.waitFor( { timeout: 5000 } );
	await select.evaluate( ( el ) => {
		const firstChart = Array.from( el.options ).find( ( o ) => o.value !== '' );
		if ( firstChart ) {
			el.value = firstChart.value;
			el.dispatchEvent( new Event( 'change', { bubbles: true } ) );
			el.dispatchEvent( new Event( 'input', { bubbles: true } ) );
		}
	} );
}

// =============================================================================

test.describe( 'Elementor Widget', () => {
	/** @type {number|null} */
	let currentPageId = null;

	test.beforeAll( async ( { requestUtils } ) => {
		// Skip the entire suite when Elementor is not installed/active.
		const plugins = await requestUtils.rest( { path: '/wp/v2/plugins' } );
		const elementorActive = plugins.some(
			( p ) => p.plugin.startsWith( 'elementor/' ) && p.status === 'active'
		);
		if ( ! elementorActive ) {
			test.skip(
				true,
				'Elementor plugin is not installed or not active.'
			);
		}
	} );

	test.beforeEach( async ( { requestUtils } ) => {
		// Each test gets its own fresh page so they don't share state.
		await deleteAllCharts( requestUtils );
		const pageData = await requestUtils.rest( {
			method: 'POST',
			path: '/wp/v2/pages',
			data: { title: 'Elementor Widget Test', status: 'draft' },
		} );
		currentPageId = pageData.id;
	} );

	test.afterEach( async ( { requestUtils } ) => {
		if ( currentPageId ) {
			await requestUtils.rest( {
				method: 'DELETE',
				path: `/wp/v2/pages/${ currentPageId }`,
				params: { force: true },
			} ).catch( () => {} );
			currentPageId = null;
		}
		await deleteAllCharts( requestUtils );
	} );

	// -------------------------------------------------------------------------

	test( 'widget is visible in Elementor panel search', async ( { admin, page } ) => {
		await openElementorEditor( admin, page, currentPageId );

		await page.locator( '#elementor-panel-elements-search-input' ).fill( 'visualizer' );

		const widgetCard = page.locator( '.elementor-element-wrapper' )
			.filter( { hasText: 'Visualizer Chart' } )
			.first();

		await expect( widgetCard ).toBeVisible( { timeout: 5000 } );
		await expect( widgetCard ).toContainText( 'Visualizer Chart' );
	} );

	test( 'shows "No charts found" notice in widget panel when no charts exist', async ( { admin, page } ) => {
		// No charts — beforeEach already called deleteAllCharts.
		await openElementorEditor( admin, page, currentPageId );
		await addVisualizerWidget( page );
		const previewFrame = page.frameLocator( '#elementor-preview-iframe' );

		// The widget controls panel should show the "no charts" warning.
		await expect(
			page.locator( '.elementor-panel-alert-warning' )
		).toBeVisible( { timeout: 5000 } );

		await expect(
			page.locator( '.elementor-panel-alert-warning' )
		).toContainText( 'No charts found' );

		// The preview area shows a placeholder.
		await expect(
			previewFrame.locator( '[data-widget_type="visualizer-chart.default"] p' )
		).toContainText( 'Please select a chart' );
	} );

	test( 'shows chart select dropdown when charts exist', async ( { admin, page } ) => {
		await createChartWithAdmin( admin, page );
		await openElementorEditor( admin, page, currentPageId );
		await addVisualizerWidget( page );

		// The "Select Chart" control should appear in the panel.
		await expect(
			page.locator( '[data-setting="chart_id"]' )
		).toBeVisible( { timeout: 5000 } );

		// The info notice linking to the Visualizer dashboard should appear too.
		await expect(
			page.locator( '.elementor-panel-alert-info' )
		).toBeVisible( { timeout: 5000 } );
	} );

	test( 'chart renders in preview iframe after selecting a chart', async ( { admin, page } ) => {
		await createChartWithAdmin( admin, page );
		await openElementorEditor( admin, page, currentPageId );
		await addVisualizerWidget( page );
		const previewFrame = page.frameLocator( '#elementor-preview-iframe' );

		await selectFirstChart( page );

		await expect(
			previewFrame.locator( '[data-widget_type="visualizer-chart.default"] .visualizer-front' )
		).toBeVisible( { timeout: CHART_RENDER_TIMEOUT } );
	} );

	test( 'chart remains visible after hiding the Elementor panel', async ( { admin, page } ) => {
		// Regression test: Elementor adds `elementor-editor-preview` to the preview body
		// when "Hide Panel" is clicked, activating CSS that hides `.elementor-widget-empty`
		// widgets.  The CSS override in index.php must counter that.
		await createChartWithAdmin( admin, page );
		await openElementorEditor( admin, page, currentPageId );
		await addVisualizerWidget( page );
		const previewFrame = page.frameLocator( '#elementor-preview-iframe' );

		await selectFirstChart( page );

		const widgetWrapper = previewFrame.locator( '[data-widget_type="visualizer-chart.default"]' );
		await expect( widgetWrapper ).toBeVisible( { timeout: CHART_RENDER_TIMEOUT } );

		// Click the "Hide Panel" toggle (eye icon in the editor toolbar).
		const hidePanelToggle = page.locator(
			'#elementor-mode-switcher-preview, [title="Hide Panel"], label[for="elementor-mode-switcher-preview"]'
		).first();
		await hidePanelToggle.click();

		// The preview body gains `elementor-editor-preview` once panel is hidden.
		await previewFrame
			.locator( 'body.elementor-editor-preview' )
			.waitFor( { timeout: 5000 } );

		// Widget must still be visible — not hidden by the empty-widget CSS rule.
		await expect( widgetWrapper ).toBeVisible();
		await expect( widgetWrapper ).not.toHaveCSS( 'display', 'none' );
	} );

	test( 'chart renders when a second widget is added after initial page load', async ( { admin, page } ) => {
		// Verifies the MutationObserver path in elementor-widget-preview.js:
		// charts added mid-session (not on the initial DOM scan) must also render.
		await createChartWithAdmin( admin, page );
		await openElementorEditor( admin, page, currentPageId );

		const previewFrame = page.frameLocator( '#elementor-preview-iframe' );

		// First widget.
		await addVisualizerWidget( page );
		await selectFirstChart( page );
		await previewFrame
			.locator( '[data-widget_type="visualizer-chart.default"] .visualizer-front' )
			.waitFor( { timeout: CHART_RENDER_TIMEOUT } );

		// Second widget — triggers the MutationObserver, not the initial scan.
		await addVisualizerWidget( page );
		await selectFirstChart( page );

		await expect(
			previewFrame.locator( '[data-widget_type="visualizer-chart.default"]' )
		).toHaveCount( 2, { timeout: CHART_RENDER_TIMEOUT } );

		await expect(
			previewFrame.locator( '[data-widget_type="visualizer-chart.default"] .visualizer-front' ).last()
		).toBeVisible( { timeout: CHART_RENDER_TIMEOUT } );
	} );
} );
