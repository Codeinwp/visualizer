/**
 * External dependencies
 */
import { Chart } from 'react-google-charts';

import merge from 'merge';

import { compact, formatDate, isValidJSON } from '../utils.js';

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

const { BlockControls } = wp.editor;

class ChartRender extends Component {
	constructor() {
		super( ...arguments );
	}

	render() {

		let chart;

		let data = formatDate( JSON.parse( JSON.stringify( this.props.chart ) ) );

		if ( 0 <= [ 'gauge', 'table', 'timeline' ].indexOf( this.props.chart['visualizer-chart-type']) ) {
			chart = startCase( this.props.chart['visualizer-chart-type']);
		} else {
			chart = `${ startCase( this.props.chart['visualizer-chart-type']) }Chart`;
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
						/>

					</Fragment>
				}

			</div>
		);
	}
}

export default ChartRender;
