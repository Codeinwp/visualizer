/**
 * D3.js sandboxed iframe renderer.
 *
 * Loaded inside an <iframe sandbox="allow-scripts"> so chart code runs in a
 * null-origin context — it cannot access the parent page's cookies, localStorage,
 * or DOM, which eliminates the persistent-XSS risk of executing stored D3 code
 * in the main page context.
 *
 * Responds to postMessage commands from the parent:
 *   { type: 'render',       code, series, data }
 *   { type: 'export-image' }
 *
 * Sends postMessage replies to the parent:
 *   { type: 'iframe-ready' }
 *   { type: 'export-image-result', dataUrl }
 */
import * as d3 from 'd3';
import * as topojson from 'topojson-client';

/** Convert Visualizer series + data arrays to plain objects for D3. */
function toD3Values( series, data ) {
	if ( ! Array.isArray( series ) || ! Array.isArray( data ) ) return [];
	return data.map( ( row ) => {
		const obj = {};
		series.forEach( ( col, i ) => {
			obj[ col.label ] = row[ i ];
		} );
		return obj;
	} );
}

function svgToPng( svg, callback ) {
	const rect = svg.getBoundingClientRect();
	const width = parseFloat( svg.getAttribute( 'width' ) ) || rect.width || 800;
	const height = parseFloat( svg.getAttribute( 'height' ) ) || rect.height || 600;
	const clone = svg.cloneNode( true );
	clone.setAttribute( 'width', width );
	clone.setAttribute( 'height', height );
	const serializer = new XMLSerializer();
	const svgText = serializer.serializeToString( clone );
	const svgDataUrl = 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent( svgText );

	const img = new Image();
	img.onload = () => {
		const canvas = document.createElement( 'canvas' );
		canvas.width = width;
		canvas.height = height;
		const ctx = canvas.getContext( '2d' );
		ctx.fillStyle = '#ffffff';
		ctx.fillRect( 0, 0, width, height );
		ctx.drawImage( img, 0, 0 );
		callback( canvas.toDataURL( 'image/png' ) );
	};
	img.onerror = () => callback( null );
	img.src = svgDataUrl;
}

window.addEventListener( 'message', function ( event ) {
	const msg = event.data;
	if ( ! msg || typeof msg !== 'object' ) return;

	if ( msg.type === 'render' ) {
		const { code, series, data } = msg;
		const container = document.getElementById( 'chart' );
		if ( ! code || ! container ) return;

		const values = toD3Values( series, data );
		try {
			// eslint-disable-next-line no-new-func
			new Function( 'd3', 'topojson', 'container', 'data', code )( d3, topojson, container, values );
		} catch ( err ) {
			container.innerHTML =
				'<p style="color:#c00;padding:12px">Chart render error: ' + err.message + '</p>';
		}
	}

	if ( msg.type === 'export-image' ) {
		const canvas = document.querySelector( 'canvas' );
		if ( canvas && typeof canvas.toDataURL === 'function' ) {
			event.source.postMessage(
				{ type: 'export-image-result', dataUrl: canvas.toDataURL( 'image/png' ) },
				'*'
			);
			return;
		}
		const svg = document.querySelector( 'svg' );
		if ( svg ) {
			svgToPng( svg, ( dataUrl ) => {
				event.source.postMessage( { type: 'export-image-result', dataUrl }, '*' );
			} );
		}
	}
} );

// Signal readiness to the parent so it knows when to send the render command.
window.parent.postMessage( { type: 'iframe-ready' }, '*' );
