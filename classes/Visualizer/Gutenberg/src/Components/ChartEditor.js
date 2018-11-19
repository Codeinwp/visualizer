/**
 * External dependencies
 */
import { HotTable } from '@handsontable/react';

import 'handsontable/dist/handsontable.full.css';

/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;

const { Component } = wp.element;

const {
	Button,
	ButtonGroup
} = wp.components;

class ChartEditor extends Component {
	constructor() {
		super( ...arguments );

		this.data = [];

		this.dates = [];

		this.types = [ 'string', 'number', 'boolean', 'date', 'datetime', 'timeofday' ];
	}

	componentWillMount() {
		this.data[this.data.length] = [];
		this.data[this.data.length] = [];

		this.props.chart['visualizer-series'].map( ( i, index ) => {
			this.data[0][ index ] = i.label;
			this.data[1][ index ] = i.type;
			if ( 'date' === i.type ) {
				this.dates.push( index );
			}
		});

		this.props.chart['visualizer-data'].map( ( i ) => {
			this.data[this.data.length] = i;
		});
	}

	render() {

		return (
			<div className="visualizer-chart-editor">
				<HotTable
					data={ this.data }
					allowInsertRow={ true }
					contextMenu={ true }
					rowHeaders={ true }
					colHeaders={ true }
					allowInvalid={ false }
					className="htEditor"
					cells={ ( row, col, prop ) => {
						let cellProperties;
						if ( 1 === row ) {
							cellProperties = {
								type: 'autocomplete',
								source: this.types,
								strict: false
							};
						}
						if ( 0 <= ( this.dates ).indexOf( col ) && 1 < row ) {
							cellProperties = {
								type: 'date',
								dateFormat: 'YYYY-MM-DD',
								correctFormat: true
							};
						}
						return cellProperties;
					} }
				/>

				<ButtonGroup>
					<Button
						isDefault
						isLarge
						onClick={ this.props.toggleModal }
					>
						{ __( 'Close' ) }
					</Button>
					<Button
						isPrimary
						isLarge
						onClick={ e => {
							this.props.toggleModal();
							this.props.editChartData( this.data, 'Visualizer_Source_Csv' );
						} }
					>
						{ __( 'Save' ) }
					</Button>
				</ButtonGroup>
			</div>
		);
	}
}

export default ChartEditor;
