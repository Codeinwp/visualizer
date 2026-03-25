/**
 * D3.js frontend renderer for Visualizer.
 *
 * Listens to the `visualizer:render:chart:start` event fired by render-facade.js.
 * For charts with library === 'd3', retrieves the stored D3 code, converts the
 * series/data arrays to plain objects, and executes the code via new Function.
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

/**
 * Render a single D3 chart into its container element.
 *
 * @param {string}  id    - DOM element ID of the container
 * @param {object}  chart - chart entry from visualizer.charts
 */
function renderD3Chart( id, chart ) {
	const container = document.getElementById( id );
	if ( ! container ) return;

	const code = typeof chart.code === 'string' ? chart.code : null;

	if ( ! code ) {
		container.innerHTML = '<p style="color:#c00;padding:12px">No chart code found.</p>';
		return;
	}

	const values = toD3Values( chart.series, chart.data );

	function doRender() {
		try {
			// eslint-disable-next-line no-new-func
			new Function( 'd3', 'topojson', 'container', 'data', code )( d3, topojson, container, values );
		} catch ( err ) {
			container.innerHTML = '<p style="color:#c00;padding:12px">Chart render error: ' + err.message + '</p>';
		}
	}

	// Double requestAnimationFrame — ensures browser has completed layout before measuring.
	requestAnimationFrame( () => requestAnimationFrame( doRender ) );
}

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

function handleImageAction( id, name, action ) {
	const container = document.getElementById( id );
	if ( ! container ) return;

	const canvas = container.querySelector( 'canvas' );
	if ( canvas && typeof canvas.toDataURL === 'function' ) {
		const img = canvas.toDataURL( 'image/png' );
		if ( action === 'print' ) {
			const win = window.open();
			win.document.write( "<br><img src='" + img + "'/>" );
			win.document.close();
			win.onload = function () { win.print(); setTimeout( win.close, 500 ); };
		} else {
			downloadDataUrl( img, name );
		}
		return;
	}

	const svg = container.querySelector( 'svg' );
	if ( ! svg ) return;

	svgToPng( svg, ( img ) => {
		if ( ! img ) return;
		if ( action === 'print' ) {
			const win = window.open();
			win.document.write( "<br><img src='" + img + "'/>" );
			win.document.close();
			win.onload = function () { win.print(); setTimeout( win.close, 500 ); };
		} else {
			downloadDataUrl( img, name );
		}
	} );
}

( function ( $ ) {
	$( 'body' ).on( 'visualizer:render:chart:start', function ( e, viz ) {
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

	$( 'body' ).on( 'visualizer:action:specificchart', function ( event, v ) {
		if ( v.action !== 'image' && v.action !== 'print' ) return;
		handleImageAction( v.id, v?.dataObj?.name, v.action );
	} );
} )( jQuery );
