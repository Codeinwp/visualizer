/* global elementorFrontend, jQuery */
/**
 * Elementor preview handler for Visualizer charts.
 *
 * @since 3.11.16
 */
// Guard against the script being injected more than once into the preview iframe.
if ( ! window.visualizerElementorPreview ) {
window.visualizerElementorPreview = true;

( function ( $ ) {
	'use strict';

	/**
	 * Poll until `condition()` returns true, then call `callback`.
	 * Gives up after `maxAttempts` × 100 ms.
	 */
	function waitFor( condition, callback, maxAttempts ) {
		maxAttempts = maxAttempts === undefined ? 50 : maxAttempts;
		if ( condition() ) {
			callback();
			return;
		}
		if ( maxAttempts <= 0 ) {
			return;
		}
		setTimeout( function () {
			waitFor( condition, callback, maxAttempts - 1 );
		}, 100 );
	}

	/**
	 * Given the Elementor widget wrapper element, extract chart data from the
	 * embedded JSON script element and trigger chart rendering.
	 */
	function renderWidget( widgetEl ) {
		var $scope  = $( widgetEl );
		var $dataEl = $scope.find( 'script.visualizer-chart-data[type="application/json"]' );

		if ( ! $dataEl.length ) {
			return;
		}

		var elementId = $dataEl.attr( 'data-element-id' );
		var chartEntry;

		try {
			chartEntry = JSON.parse( $dataEl.text() );
		} catch ( e ) {
			return;
		}

		if ( ! elementId || ! chartEntry ) {
			return;
		}

		window.visualizer        = window.visualizer || {};
		window.visualizer.charts = window.visualizer.charts || {};
		window.visualizer.charts[ elementId ] = chartEntry;

		// Build the viz object that render-google.js / render-chartjs.js expect.
		// is_front:true tells render-google.js to call renderChart(id) for just
		// this element rather than render() for all charts.
		var viz = $.extend( {}, window.visualizer, { id: elementId, is_front: true } );

		function doTrigger() {
			$( '#' + elementId ).removeClass( 'viz-facade-loaded' );
			$( 'body' ).trigger( 'visualizer:render:chart:start', viz );
		}

		if ( chartEntry.library === 'google' || chartEntry.library === 'GoogleCharts' ) {
			// render-google.js bails silently when typeof google !== 'object'.
			// Poll until the Google Charts loader script has executed.
			waitFor( function () { return typeof google === 'object'; }, doTrigger );
		} else {
			doTrigger();
		}
	}

	/**
	 * Scan a subtree for visualizer-chart widgets and render each one.
	 */
	function scanAndRender( root ) {
		var $root = $( root );

		if ( $root.is( '[data-widget_type="visualizer-chart.default"]' ) ) {
			renderWidget( root );
		}

		$root.find( '[data-widget_type="visualizer-chart.default"]' ).each( function () {
			renderWidget( this );
		} );
	}

	$( document ).ready( function () {
		var observer = new MutationObserver( function ( mutations ) {
			// Collect widget elements to render, de-duplicating within the batch.
			var toRender = [];
			var seen     = window.WeakSet ? new WeakSet() : null;
			var seenList = [];

			// Enqueue a widget element for rendering, skipping duplicates.
			function enqueue( el ) {
				if ( ! el ) {
					return;
				}
				if ( seen ) {
					if ( seen.has( el ) ) {
						return;
					}
					seen.add( el );
					toRender.push( el );
					return;
				}
				if ( seenList.indexOf( el ) !== -1 ) {
					return;
				}
				seenList.push( el );
				toRender.push( el );
			}

			mutations.forEach( function ( mutation ) {
				mutation.addedNodes.forEach( function ( node ) {
					if ( node.nodeType !== 1 ) {
						return;
					}

					// Only react when chart data was injected, not when chart
					// rendering (SVG / canvas) mutates the DOM — that would loop.
					var hasData = ( node.matches && node.matches( 'script.visualizer-chart-data[type="application/json"]' ) ) ||
					              ( node.querySelector && node.querySelector( 'script.visualizer-chart-data[type="application/json"]' ) );
					if ( ! hasData ) {
						return;
					}

					// Node is the widget wrapper or contains one (new widget added).
					if ( $( node ).is( '[data-widget_type="visualizer-chart.default"]' ) ) {
						enqueue( node );
					}
					$( node ).find( '[data-widget_type="visualizer-chart.default"]' ).each( function () {
						enqueue( this );
					} );

					// Node is inner content of an existing widget (chart switched).
					// Look upward for the widget wrapper.
					enqueue( $( node ).closest( '[data-widget_type="visualizer-chart.default"]' )[ 0 ] );
				} );
			} );

			toRender.forEach( function ( el ) {
				renderWidget( el );
			} );
		} );

		observer.observe( document.documentElement, { childList: true, subtree: true } );

		// Handle widgets already present on initial load.
		scanAndRender( document.body );
	} );

	// Register Elementor's element_ready hook as a secondary trigger.
	// Called both immediately (in case elementorFrontend is already initialised)
	// and on the init event (in case it fires after this script loads).
	// The guard prevents double-registration if both code paths fire.
	var elementorHookRegistered = false;
	function registerElementorHook() {
		if ( elementorHookRegistered ) {
			return;
		}
		if ( typeof elementorFrontend !== 'undefined' && elementorFrontend.hooks ) {
			elementorFrontend.hooks.addAction(
				'frontend/element_ready/visualizer-chart.default',
				function ( $scope ) { renderWidget( $scope[ 0 ] ); }
			);
			elementorHookRegistered = true;
		}
	}
	registerElementorHook();
	window.addEventListener( 'elementor/frontend/init', registerElementorHook );
}( jQuery ) );
} // end visualizerElementorPreview guard
