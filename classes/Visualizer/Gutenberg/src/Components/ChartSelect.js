/**
 * External dependencies
 */
import { Chart } from 'react-google-charts';

import FileImport from './Import/FileImport.js';

import RemoteImport from './Import/RemoteImport.js';

import ChartImport from './Import/ChartImport.js';

import ManualData from './Import/ManualData.js';

import Sidebar from './Sidebar.js';

import ChartPermissions from './ChartPermissions.js';

import PanelButton from './PanelButton.js';

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

const { InspectorControls } = wp.editor;

class ChartSelect extends Component {
	constructor() {
		super( ...arguments );

		this.state = {

			/**
			 * Sidebar Route Status
			 *
			 * home - Initial screen.
			 * showAdvanced - Show Advanced Options.
			 */
			route: 'home'
		};
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
			<Fragment>
				{ 'home' === this.state.route &&
					<InspectorControls>

						<FileImport
							chart={ this.props.chart }
							readUploadedFile={ this.props.readUploadedFile }
						/>

						<RemoteImport
							chart={ this.props.chart }
							editURL={ this.props.editURL }
							isLoading={ this.props.isLoading }
							uploadData={ this.props.uploadData }
							editSchedule={ this.props.editSchedule }
						/>

						<ChartImport getChartData={ this.props.getChartData } isLoading={ this.props.isLoading } />

						<ManualData chart={ this.props.chart } editChartData={ this.props.editChartData } />

						<PanelButton
							label={ __( 'Advanced Options' ) }
							icon="admin-tools"
							onClick={ () => this.setState({ route: 'showAdvanced' }) }
						/>

						<PanelButton
							label={ __( 'Chart Permissions' ) }
							icon="admin-users"
							onClick={ () => this.setState({ route: 'showPermissions' }) }
						/>
					</InspectorControls>
				}

				{ ( 'showAdvanced' === this.state.route || 'showPermissions' === this.state.route ) &&
					<InspectorControls>
						<PanelButton
							label={ __( 'Chart Settings' ) }
							onClick={ () => this.setState({ route: 'home' }) }
							isBack={ true }
						/>

						{ 'showAdvanced' === this.state.route &&
							<Sidebar chart={ this.props.chart } edit={ this.props.editSettings } />
						}

						{ 'showPermissions' === this.state.route &&
							<ChartPermissions chart={ this.props.chart } edit={ this.props.editPermissions } />
						}
					</InspectorControls>
				}

				<div className="visualizer-settings__chart">

					{ ( null !== this.props.chart ) &&
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
					}

				</div>
			</Fragment>
		);
	}
}

export default ChartSelect;
