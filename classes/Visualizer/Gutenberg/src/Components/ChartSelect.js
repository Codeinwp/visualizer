/**
 * External dependencies
 */
import { Chart } from 'react-google-charts';

import DataTable from './DataTable.js';

import FileImport from './Import/FileImport.js';

import RemoteImport from './Import/RemoteImport.js';

import ChartImport from './Import/ChartImport.js';

import DataImport from './Import/DataImport.js';

import ManualData from './Import/ManualData.js';

import Sidebar from './Sidebar.js';

import ChartPermissions from './ChartPermissions.js';

import PanelButton from './PanelButton.js';

import merge from 'merge';

import { compact, formatDate, isValidJSON, formatData, googleChartPackages } from '../utils.js';

/**
 * WordPress dependencies
 */
const { startCase } = lodash;

const { __ } = wp.i18n;

const { apiFetch } = wp;

const {
	Button
} = wp.components;

const {
	Component,
	Fragment
} = wp.element;

const { InspectorControls } = wp.blockEditor || wp.editor;

let visualizerMedia, visualizerMediaView;
visualizerMedia = visualizer.media = {};
visualizerMediaView = visualizerMedia.view = {};

visualizerMediaView.Chart = wp.media.view.MediaFrame.extend(
	{
		initialize: function() {
			const self = this;

			_.defaults(
				self.options, {
					action: '',
					id: 'visualizer',
					state: 'iframe:visualizer',
					title: 'Visualizer'
				}
			);

			wp.media.view.MediaFrame.prototype.initialize.apply( self, arguments );

			wp.media.view.settings.tab = 'Visualizer';
			wp.media.view.settings.tabUrl = self.options.action;
			self.createIframeStates();
		},

		createIframeStates: function( passedOptions ) {
			const self = this;
			wp.media.view.MediaFrame.prototype.createIframeStates.apply( self, arguments );

			self.state( self.options.state ).set(
				_.defaults(
					{
						tab: self.options.id,
						src: self.options.action + '&tab=' + self.options.id,
						title: self.options.title,
						content: 'iframe',
						menu: 'default'
					}, passedOptions
				)
			);

		},

		open: function() {
			try {
				wp.media.view.MediaFrame.prototype.open.apply( this, arguments );
			} catch ( error ) {
				console.error( error );
			}
		}
	}
);

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
		const { legacyBlockEdit, blockEditDoc } = window.visualizerLocalize;
		let chartVersion = 'undefined' !== typeof google.visualization ? google.visualization.Version : 'current';

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

		const openEditChart = ( chartId ) => {
			const baseURL = ( window.visualizerLocalize.chartEditUrl ) ? window.visualizerLocalize.chartEditUrl : '';
			let view = new visualizerMediaView.Chart(
				{
					action: `${baseURL}?action=visualizer-edit-chart&library=yes&chart=` + chartId
				}
			);
			const updateChartState = async() => {
				await this.setState({
					isLoading: 'getChart'
				});

				let result = await apiFetch({ path: `wp/v2/visualizer/${chartId}` });
				await this.props.editSettings( result['chart_data']['visualizer-settings']);
				await this.props.getChartData( chartId );

				await this.setState({ route: 'home', chart: result['chart_data'], isModified: true, isLoading: true });
			};
			// eslint-disable-next-line camelcase
			window.send_to_editor = function() {
				updateChartState().then( () => {
					view.close();
				});
			};

			view.open();
		};

		return (
			<Fragment>
				{ 'home' === this.state.route &&
					<InspectorControls>
						{ ! legacyBlockEdit && (
							<div className="viz-edit-chart-new">
								<Button
									isPrimary={true}
									onClick={ () => {
 openEditChart( this.props.id );
} }> Edit Chart </ Button>
								<p dangerouslySetInnerHTML={{__html: blockEditDoc}}></p>
							</div>
						) }

						{ legacyBlockEdit && (
							<>

						<ManualData chart={ this.props.chart } editChartData={ this.props.editChartData } />

						<FileImport
							chart={ this.props.chart }
							readUploadedFile={ this.props.readUploadedFile }
						/>

								<RemoteImport
									id={ this.props.id }
									chart={ this.props.chart }
									editURL={ this.props.editURL }
									isLoading={ this.props.isLoading }
									uploadData={ this.props.uploadData }
									editSchedule={ this.props.editSchedule }
									editJSONSchedule={ this.props.editJSONSchedule }
									editJSONURL={ this.props.editJSONURL }
									editJSONHeaders={ this.props.editJSONHeaders }
									editJSONRoot={ this.props.editJSONRoot }
									editJSONPaging={ this.props.editJSONPaging }
									JSONImportData={ this.props.JSONImportData }
								/>

								<ChartImport getChartData={ this.props.getChartData } isLoading={ this.props.isLoading } />

								<DataImport
									chart={ this.props.chart }
									editSchedule={ this.props.editDatabaseSchedule }
									databaseImportData={ this.props.databaseImportData }
								/>

						<PanelButton
							label={ __( 'Advanced Options' ) }
                            className="visualizer-advanced-options"
							icon="admin-tools"
							onClick={ () => this.setState({ route: 'showAdvanced' }) }
						/>

								<PanelButton
									label={ __( 'Chart Permissions' ) }
									icon="admin-users"
									onClick={ () => this.setState({ route: 'showPermissions' }) }
								/>
							</>
						) }
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
							<Sidebar chart={ this.props.chart } attributes={ this.props.attributes } edit={ this.props.editSettings } />
						}

						{ 'showPermissions' === this.state.route &&
							<ChartPermissions chart={ this.props.chart } edit={ this.props.editPermissions } />
						}
					</InspectorControls>
				}

				<div className="visualizer-settings__chart" data-chart-type={ chart }>

					{ ( null !== this.props.chart ) &&

						( 'DataTable' === data['visualizer-chart-library']) ? (
							<DataTable
								id={ this.props.id }
								rows={ data['visualizer-data'] }
								columns={ data['visualizer-series'] }
								options={ data['visualizer-settings'] }
							/>
						) : ( '' !== data['visualizer-data-exploded'] ? (
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
						)
					) }

                     <div className="visualizer-settings__charts-footer"><sub>
                        { footer }
                     </sub></div>

				</div>
			</Fragment>
		);
	}
}

export default ChartSelect;
