/**
 * Security regression: D3 renderer "print"/"image" action must not let a
 * compromised chart iframe break out of its sandbox.
 *
 * D3 chart code runs inside <iframe sandbox="allow-scripts"> (null origin) and
 * returns the exported image to the parent over postMessage. That `dataUrl` is
 * therefore attacker-controlled. Previously index.js wrote it unescaped into a
 * freshly opened, SAME-ORIGIN popup:
 *
 *     const win = window.open();                               // about:blank => site origin
 *     win.document.write( "<br><img src='" + dataUrl + "'/>" );
 *
 * so a hostile "dataUrl" could inject active markup (e.g. <img onerror=...>) into
 * the site's own origin. A Contributor (edit_post on their own draft chart, no
 * unfiltered_html) could store such chart code -> stored-XSS privilege escalation.
 *
 * The handler now validates the value with isSafeImageDataUrl() and builds the
 * <img> via the DOM API instead of string concatenation. This test drives the
 * REAL index.js module and asserts the breakout is blocked while a legitimate
 * export still renders.
 *
 * @jest-environment jsdom
 */

/* eslint-disable no-undef */

const path = require( 'path' );

describe( 'D3 renderer print/image action', () => {
	let actionHandlers;
	let openSpy;

	beforeEach( () => {
		jest.resetModules();
		actionHandlers = {};

		// Minimal jQuery shim: index.js only uses `$( 'body' ).on( event, fn )`.
		global.jQuery = () => ( {
			on( event, fn ) {
				( actionHandlers[ event ] = actionHandlers[ event ] || [] ).push( fn );
				return this;
			},
		} );

		// window.open() returns a popup backed by a real (detached) document so
		// createElement/appendChild/write behave exactly as in a browser.
		openSpy = jest.spyOn( window, 'open' ).mockImplementation( () => {
			const popupDoc = document.implementation.createHTMLDocument( '' );
			return { document: popupDoc, print() {}, close() {} };
		} );

		// Load the real module (registers the body event handlers via the shim).
		require( path.resolve( __dirname, '../src/index.js' ) );
	} );

	afterEach( () => {
		openSpy.mockRestore();
	} );

	/**
	 * Stub the container/iframe lookups the code performs, with a MALICIOUS
	 * iframe contentWindow that answers 'export-image' with an attacker-chosen
	 * dataUrl. Real <iframe> nodes are avoided so jsdom creates no browsing
	 * contexts; we only satisfy getElementById()/querySelector().
	 *
	 * @param {string} id      Container id.
	 * @param {string} dataUrl The value the compromised iframe returns.
	 */
	function setupChart( id, dataUrl ) {
		const evilContentWindow = {
			postMessage( msg ) {
				if ( ! msg || msg.type !== 'export-image' ) return;
				const reply = new window.MessageEvent( 'message', {
					data: { type: 'export-image-result', dataUrl },
				} );
				Object.defineProperty( reply, 'source', { value: evilContentWindow } );
				window.dispatchEvent( reply );
			},
		};

		const fakeIframe = { contentWindow: evilContentWindow };
		const fakeContainer = {
			querySelector: ( sel ) => ( sel.indexOf( 'iframe' ) !== -1 ? fakeIframe : null ),
		};

		jest.spyOn( document, 'getElementById' ).mockImplementation( ( wanted ) =>
			wanted === id ? fakeContainer : null
		);
	}

	function firePrint( id ) {
		actionHandlers[ 'visualizer:action:specificchart' ].forEach( ( fn ) =>
			fn( {}, { action: 'print', id, dataObj: { name: 'chart' } } )
		);
	}

	it( 'blocks a hostile dataUrl: no popup, no injected markup', () => {
		const payload = "x'/><img src=z onerror=\"window.__xss_fired=true\">";
		setupChart( 'viz-evil', payload );

		firePrint( 'viz-evil' );

		// The value fails validation before window.open(), so no popup is created.
		expect( openSpy ).not.toHaveBeenCalled();
		expect( window.__xss_fired ).toBeUndefined();
	} );

	it( 'renders a legitimate image export as a single safe <img>', () => {
		setupChart( 'viz-safe', 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUg==' );

		firePrint( 'viz-safe' );

		expect( openSpy ).toHaveBeenCalledTimes( 1 );
		const popupDoc = openSpy.mock.results[ 0 ].value.document;
		const imgs = popupDoc.querySelectorAll( 'img' );
		expect( imgs.length ).toBe( 1 );
		expect( imgs[ 0 ].getAttribute( 'src' ) ).toBe(
			'data:image/png;base64,iVBORw0KGgoAAAANSUhEUg=='
		);
		expect( popupDoc.querySelector( 'img[onerror]' ) ).toBeNull();
	} );
} );
