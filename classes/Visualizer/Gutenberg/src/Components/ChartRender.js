/**
 * External dependencies
 */
import { Chart } from 'react-google-charts';

import DataTable from './DataTable.js';

import merge from 'merge';

import { compact, formatDate, isValidJSON, formatData } from '../utils.js';

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

	render() {
		let chart, footer;

		let data = formatDate( JSON.parse( JSON.stringify( this.props.chart ) ) );

		if ( 0 <= [ 'gauge', 'tabular', 'timeline' ].indexOf( this.props.chart['visualizer-chart-type']) ) {
			if ( 'DataTable' === data['visualizer-chart-library']) {
				chart = data['visualizer-chart-type'];
			} else {
                chart = this.props.chart['visualizer-chart-type'];
                if ( 'tabular' === chart ) {
                    chart = 'table';
                }
                chart = startCase( chart );
			}
		} else {
			chart = `${ startCase( this.props.chart['visualizer-chart-type']) }Chart`;
		}

        if ( data['visualizer-data-exploded']) {
            footer = __( 'Annotations in this chart may not display here but they will display in the front end.' );
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

						{ ( 'DataTable' === data['visualizer-chart-library']) ? (
							<DataTable
								id={ this.props.id }
								rows={ data['visualizer-data'] }
								columns={ data['visualizer-series'] }
								options={ data['visualizer-settings'] }
							/>
						) : ( '' !== data['visualizer-data-exploded'] ? (
							<Chart
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
							/>
                        ) : (
							<Chart
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
							/>
						) ) }

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
