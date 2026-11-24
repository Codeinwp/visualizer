/**
 * D3 renderer must draw into a container that lives in another document,
 * such as the iframed block editor canvas, and hear that sandbox's messages.
 *
 * @jest-environment jsdom
 */

/* eslint-disable no-undef */

const path = require( 'path' );

describe( 'D3 renderer container in another document', () => {
	let renderHandlers;
	let rafSpy;

	beforeEach( () => {
		jest.resetModules();
		document.body.innerHTML = '';
		renderHandlers = [];

		global.jQuery = () => ( {
			on( event, fn ) {
				if ( 'visualizer:render:chart:start' === event ) {
					renderHandlers.push( fn );
				}
				return this;
			},
		} );
		window.vizD3Renderer = { iframeJsUrl: 'https://example.test/iframe.js' };
		rafSpy = jest.spyOn( window, 'requestAnimationFrame' ).mockImplementation( ( cb ) => cb() );

		require( path.resolve( __dirname, '../src/index.js' ) );
	} );

	afterEach( () => {
		rafSpy.mockRestore();
		delete window.vizD3Renderer;
		delete global.jQuery;
	} );

	function trigger( viz ) {
		renderHandlers.forEach( ( fn ) => fn( {}, viz ) );
	}

	it( 'renders into the passed container and answers the sandbox from its window', () => {
		const canvas = document.createElement( 'iframe' );
		document.body.appendChild( canvas );
		const canvasDocument = canvas.contentDocument;
		const container = canvasDocument.createElement( 'div' );
		container.id = 'visualizer-d3-7';
		canvasDocument.body.appendChild( container );

		trigger( {
			id: 'visualizer-d3-7',
			container,
			charts: {
				'visualizer-d3-7': { library: 'd3', code: 'return 1;', series: [], data: [] },
			},
		} );

		const sandbox = container.querySelector( 'iframe[data-viz-id="visualizer-d3-7"]' );
		expect( sandbox ).not.toBeNull();
		expect( sandbox.getAttribute( 'sandbox' ) ).toBe( 'allow-scripts' );
		expect( document.querySelector( 'iframe[data-viz-id]' ) ).toBeNull();

		const postMessage = jest.spyOn( sandbox.contentWindow, 'postMessage' ).mockImplementation( () => {} );

		const ready = new canvas.contentWindow.MessageEvent( 'message', { data: { type: 'iframe-ready' } } );
		Object.defineProperty( ready, 'source', { value: sandbox.contentWindow } );
		canvas.contentWindow.dispatchEvent( ready );

		expect( postMessage ).toHaveBeenCalledWith(
			expect.objectContaining( { type: 'render', code: 'return 1;' } ),
			'*'
		);
	} );

	it( 'still finds the container by id when none is passed', () => {
		const container = document.createElement( 'div' );
		container.id = 'visualizer-d3-8';
		document.body.appendChild( container );

		trigger( {
			id: 'visualizer-d3-8',
			charts: {
				'visualizer-d3-8': { library: 'd3', code: 'return 1;', series: [], data: [] },
			},
		} );

		expect( container.querySelector( 'iframe[data-viz-id="visualizer-d3-8"]' ) ).not.toBeNull();
	} );
} );
