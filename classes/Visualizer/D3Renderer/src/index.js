/**
 * D3.js frontend renderer for Visualizer.
 *
 * Listens to the `visualizer:render:chart:start` event fired by render-facade.js.
 * For charts with library === 'd3', creates a sandboxed <iframe> and delegates
 * chart execution to it via postMessage — eliminating the persistent-XSS risk of
 * running stored code via new Function() in the main page context.
 *
 * The iframe uses srcdoc so no physical HTML file is needed.  srcdoc iframes
 * always have a null origin regardless of sandbox, and sandbox="allow-scripts"
 * ensures the chart code cannot access the parent page's cookies, localStorage,
 * or DOM.
 */

function ensurePngName( name ) {
	if ( ! name ) return 'chart.png';
	return name.toLowerCase().endsWith( '.png' ) ? name : `${ name }.png`;
}

function downloadDataUrl( dataUrl, name ) {
	const link = document.createElement( 'a' );
	link.href = dataUrl;
	link.download = ensurePngName( name );
	document.body.appendChild( link );
	link.click();
	link.remove();
}

/**
 * Render a single D3 chart into its container element via a sandboxed iframe.
 *
 * @param {string} id    - DOM element ID of the container
 * @param {object} chart - chart entry from visualizer.charts
 */
function renderD3Chart( id, chart ) {
	const container = document.getElementById( id );
	if ( ! container ) return;

	const code = typeof chart.code === 'string' ? chart.code : null;

	if ( ! code ) {
		container.innerHTML = '<p style="color:#c00;padding:12px">No chart code found.</p>';
		return;
	}

	const iframeJsUrl =
		window.vizD3Renderer && window.vizD3Renderer.iframeJsUrl
			? window.vizD3Renderer.iframeJsUrl
			: null;

	if ( ! iframeJsUrl ) {
		container.innerHTML =
			'<p style="color:#c00;padding:12px">D3 renderer iframe URL not configured.</p>';
		return;
	}

	// Match the iframe dimensions to the container after layout is complete.
	function doRender() {
		const width = container.offsetWidth || 800;
		const height = container.offsetHeight || 400;

		// srcdoc iframes always have a null origin — no physical file needed.
		const srcdoc =
			'<!DOCTYPE html><html><head><meta charset="utf-8">' +
			'<style>*{margin:0;padding:0;box-sizing:border-box}body{overflow:hidden}' +
			'#chart{width:100%;height:100vh}</style></head>' +
			'<body><div id="chart"></div>' +
			'<script src="' + iframeJsUrl + '"><\/script></body></html>';

		const iframe = document.createElement( 'iframe' );
		iframe.setAttribute( 'sandbox', 'allow-scripts' );
		iframe.setAttribute( 'srcdoc', srcdoc );
		iframe.setAttribute( 'data-viz-id', id );
		iframe.style.cssText =
			'border:0;width:' + width + 'px;height:' + height + 'px;display:block;';

		// Once the iframe signals it is ready, send the render command.
		function onReady( event ) {
			if ( event.source !== iframe.contentWindow ) return;
			const msg = event.data;
			if ( ! msg || msg.type !== 'iframe-ready' ) return;
			window.removeEventListener( 'message', onReady );
			iframe.contentWindow.postMessage(
				{ type: 'render', code, series: chart.series, data: chart.data },
				'*'
			);
		}

		window.addEventListener( 'message', onReady );

		container.innerHTML = '';
		container.appendChild( iframe );
	}

	// Double requestAnimationFrame — ensures browser has completed layout before measuring.
	requestAnimationFrame( () => requestAnimationFrame( doRender ) );
}

function handleImageAction( id, name, action ) {
	const container = document.getElementById( id );
	if ( ! container ) return;

	const iframe = container.querySelector( 'iframe[data-viz-id]' );
	if ( ! iframe || ! iframe.contentWindow ) return;

	function onResult( event ) {
		if ( event.source !== iframe.contentWindow ) return;
		const msg = event.data;
		if ( ! msg || msg.type !== 'export-image-result' ) return;
		window.removeEventListener( 'message', onResult );

		const dataUrl = msg.dataUrl;
		if ( ! dataUrl ) return;

		if ( action === 'print' ) {
			const win = window.open();
			win.document.write( "<br><img src='" + dataUrl + "'/>" );
			win.document.close();
			win.onload = function () { win.print(); setTimeout( win.close, 500 ); };
		} else {
			downloadDataUrl( dataUrl, name );
		}
	}

	window.addEventListener( 'message', onResult );
	iframe.contentWindow.postMessage( { type: 'export-image' }, '*' );
}

( function ( $ ) {
	$( 'body' ).on( 'visualizer:render:chart:start', function ( _e, viz ) {
		if ( ! viz.charts ) return;

		// Frontend mode: a specific chart ID is provided.
		if ( viz.id ) {
			const chart = viz.charts[ viz.id ];
			if ( chart && chart.library === 'd3' ) {
				renderD3Chart( viz.id, chart );
			}
			return;
		}

		// Admin / batch mode: no specific ID — render all d3 charts.
		Object.keys( viz.charts ).forEach( ( id ) => {
			const chart = viz.charts[ id ];
			if ( chart && chart.library === 'd3' ) {
				renderD3Chart( id, chart );
			}
		} );
	} );

	$( 'body' ).on( 'visualizer:action:specificchart', function ( _event, v ) {
		if ( v.action !== 'image' && v.action !== 'print' ) return;
		handleImageAction( v.id, v?.dataObj?.name, v.action );
	} );
} )( jQuery );
