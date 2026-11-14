const assert = require( 'node:assert/strict' );
const path = require( 'path' );
const { afterEach, beforeEach, describe, it } = require( 'node:test' );

describe( 'DataTables nested settings', () => {
	let handlers;
	let capturedSettings;
	let alertCalls;

	beforeEach( () => {
		handlers = {};
		capturedSettings = null;
		alertCalls = 0;

		global.window = {};
		global.document = {
			getElementById: () => ( {} ),
		};
		global.alert = () => {
			alertCalls++;
		};

		const collection = {
			length: 0,
			hasClass: () => false,
			children: () => ( { length: 0 } ),
			find: () => ( { length: 0 } ),
			empty() {
				return this;
			},
			append() {
				return this;
			},
			css: () => '400px',
			addClass() {
				return this;
			},
			remove() {
				return this;
			},
			on( event, callback ) {
				handlers[ event ] = callback;
				return this;
			},
			trigger() {
				return this;
			},
			DataTable( settings ) {
				capturedSettings = settings;
				return this;
			},
		};

		const jquery = () => collection;
		jquery.extend = ( target, ...sources ) => Object.assign( target, ...sources );
		jquery.fn = {
			dataTable: {
				render: {
					number: () => null,
				},
			},
		};
		global.jQuery = jquery;

		const renderer = path.resolve( __dirname, '../../js/render-datatables.js' );
		delete require.cache[ renderer ];
		require( renderer );
	} );

	afterEach( () => {
		delete global.alert;
		delete global.document;
		delete global.jQuery;
		delete global.window;
	} );

	function renderWithSettings( settings ) {
		handlers[ 'visualizer:render:specificchart:start' ]( {}, {
			id: 'chart',
			chart: {
				library: 'datatables',
				settings,
				series: [ { label: 'Value', type: 'string' } ],
				data: [ [ 'Example' ] ],
			},
			v: { page_type: 'frontend' },
		} );
	}

	it( 'does not execute an unsupported nested setting name', () => {
		assert.doesNotThrow( () => renderWithSettings( { 'alert(1)_x': 'value' } ) );
		assert.equal( alertCalls, 0 );
	} );

	it( 'continues to apply the supported select nested setting', () => {
		renderWithSettings( { select_info: 'true' } );
		assert.equal( capturedSettings.select.info, true );
		assert.equal( capturedSettings.info, true );
	} );
} );
