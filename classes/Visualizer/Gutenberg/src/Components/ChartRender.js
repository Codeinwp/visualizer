/**
 * External dependencies
 */
import { Chart } from 'react-google-charts';

import DataTable from './DataTable.js';

import merge from 'merge';

import { compact, formatDate, isValidJSON, formatData, googleChartPackages } from '../utils.js';

/**
 * WordPress dependencies
 */
const { startCase } = lodash;

const { __ } = wp.i18n;

const {
	Component,
	Fragment
} = wp.element;

const {
	Button,
	Dashicon,
	Toolbar,
	Tooltip
} = wp.components;

const { BlockControls } = wp.blockEditor || wp.editor;

class ChartRender extends Component {
	constructor() {
		super( ...arguments );
	}

	getD3ContainerId() {
		return `visualizer-d3-${ this.props.id }`;
	}

	isD3Chart( chart ) {
		return chart && chart['visualizer-chart-library'] && 'd3' === chart['visualizer-chart-library'].toLowerCase();
	}

	renderD3Chart() {
		const chart = this.props.chart;
		if ( ! this.isD3Chart( chart ) ) {
			return;
		}

		if ( 'undefined' === typeof jQuery ) {
			return;
		}

		const containerId = this.getD3ContainerId();
		const data = formatDate( JSON.parse( JSON.stringify( chart ) ) );
		const code = chart['visualizer-d3-code'] || chart.code || '';
		const payload = {
			id: containerId,
			charts: {
				[ containerId ]: {
					library: 'd3',
					code,
					series: data['visualizer-series'],
					data: data['visualizer-data']
				}
			}
		};

		jQuery( 'body' ).trigger( 'visualizer:render:chart:start', payload );
	}

	componentDidMount() {
		this.renderD3Chart();
	}

	componentDidUpdate( prevProps ) {
		if ( prevProps.chart !== this.props.chart ) {
			this.renderD3Chart();
		}
	}

	render() {

		let chartVersion = 'undefined' !== typeof google ? google.visualization.Version : 'current';

		let chart, footer;

		let data = formatDate( JSON.parse( JSON.stringify( this.props.chart ) ) );
		const isD3 = this.isD3Chart( data );

		if ( ! isD3 && 0 <= [ 'gauge', 'tabular', 'timeline' ].indexOf( this.props.chart['visualizer-chart-type']) ) {
			if ( 'DataTable' === data['visualizer-chart-library'] ) {
				chart = data['visualizer-chart-type'];
			} else {
                chart = this.props.chart['visualizer-chart-type'];
                if ( 'tabular' === chart ) {
                    chart = 'table';
                }
                chart = startCase( chart );
			}
		} else if ( ! isD3 ) {
			chart = `${ startCase( this.props.chart['visualizer-chart-type']) }Chart`;
		}

        if ( data['visualizer-data-exploded']) {
            footer = __( 'Annotations in this chart may not display here but they will display in the front end.' );
        }

		if ( this.props.chart['visualizer-series'] && 0 <= [ 'date', 'datetime', 'timeofday' ].indexOf( this.props.chart['visualizer-series'][0].type ) ) {
			if ( this.props.chart['visualizer-settings'] && ( this.props.chart['visualizer-settings'].hAxis && '' == this.props.chart['visualizer-settings'].hAxis.format ) ) {
				this.props.chart['visualizer-settings'].hAxis.format = 'YYYY-MM-dd';
			}
		}

		return (
			<div className={ this.props.className }>

				{ ( null !== this.props.chart ) &&
					<Fragment>

						<BlockControls key="toolbar-controls">
							<Toolbar
								className='components-toolbar'
							>
								<Tooltip text={ __( 'Edit Chart' ) }>
									<Button
										className="components-icon-button components-toolbar__control edit-pie-chart"
										onClick={ this.props.editChart }
									>
										<Dashicon icon={ 'edit' } />
									</Button>
								</Tooltip>
							</Toolbar>
						</BlockControls>

						{ isD3 ? (
							<div
								id={ this.getD3ContainerId() }
								className="visualizer-d3-preview"
								style={ { height: '500px' } }
							/>
						) : ( 'DataTable' === data['visualizer-chart-library']) ? (
							<DataTable
								id={ this.props.id }
								rows={ data['visualizer-data'] }
								columns={ data['visualizer-series'] }
								options={ data['visualizer-settings'] }
							/>
						) : (
							<Chart
								chartVersion={ chartVersion }
								chartType={ chart }
								rows={ data['visualizer-data'] }
								columns={ data['visualizer-series'] }
								options={
									isValidJSON( this.props.chart['visualizer-settings'].manual ) ?
										merge( compact( this.props.chart['visualizer-settings']), JSON.parse( this.props.chart['visualizer-settings'].manual ) ) :
										compact( this.props.chart['visualizer-settings'])
								}
								height="500px"
                                formatters={ formatData( data ) }
								chartPackages={ googleChartPackages }
							/>
						) }

                         <div className="visualizer-settings__charts-footer"><sub>
                            { footer }
                         </sub></div>

					</Fragment>
				}

			</div>
		);
	}
}

export default ChartRender;
