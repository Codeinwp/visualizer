/**
 * External dependencies
 */
import JSONInput from 'react-json-editor-ajrm';

import locale from 'react-json-editor-ajrm/locale/en';

import { isValidJSON } from '../../utils.js';

/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;

const { Component } = wp.element;

const {
	ExternalLink,
	PanelBody
} = wp.components;

class ManualConfiguration extends Component {
	constructor() {
		super( ...arguments );

		this.isValidJSON = this.isValidJSON.bind( this );
	}

	isValidJSON( obj ) {
		try {
			JSON.parse( obj );
		} catch ( e ) {
			return false;
		}
		return true;
	}

	render() {

		let chart;

		const settings = this.props.chart['visualizer-settings'];

		if ( 0 <= [ 'gauge', 'table', 'timeline' ].indexOf( this.props.chart['visualizer-chart-type']) ) {
			chart = this.props.chart['visualizer-chart-type'];
		} else {
			chart = `${ this.props.chart['visualizer-chart-type'] }chart`;
		}

		return (
			<PanelBody
				title={ __( 'Manual Configuration' ) }
				initialOpen={ false }
				className="visualizer-advanced-panel"
			>

				<p>{ __( 'Configure the graph by providing configuration variables right from the Google Visualization API.' ) }</p>

				<p>
					<ExternalLink href={ `https://developers.google.com/chart/interactive/docs/gallery/${chart}#configuration-options` }>
						{ __( 'Google Visualization API' ) }
					</ExternalLink>
				</p>

				<JSONInput
					locale={ locale }
					theme="light_mitsuketa_tribute"
					placeholder={
						isValidJSON( settings.manual ) ?
							JSON.parse( settings.manual ) :
							{}
					}
					width="100%"
					height="250px"
					style={ {
						errorMessage: {
							height: '100%',
							fontSize: '10px'
						},
						container: {
							border: '1px solid #ddd',
							boxShadow: 'inset 0 1px 2px rgba(0,0,0,.07)'
						},
						labelColumn: {
							background: '#F5F5F5',
							width: 'auto',
							padding: '5px 10px 5px 10px'
						}
					} }
					onChange={ e => {
						if ( false === e.error ) {
							settings.manual = e.json;
							this.props.edit( settings );
						}
					} }
				/>


			</PanelBody>
		);
	}
}

export default ManualConfiguration;
