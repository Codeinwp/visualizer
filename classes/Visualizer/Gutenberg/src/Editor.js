/**
 * External dependencies
 */
import Charts from './Components/Charts.js';

import ChartSelect from './Components/ChartSelect.js';

import ChartRender from './Components/ChartRender.js';

import { CSVToArray } from './utils.js';

/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;

const {
	apiFetch,
	apiRequest
} = wp;

const {
	Component,
	Fragment
} = wp.element;

const {
	Button,
	ButtonGroup,
	Dashicon,
	Placeholder,
	Spinner
} = wp.components;

class Editor extends Component {
	constructor() {
		super( ...arguments );

		this.getChart = this.getChart.bind( this );
		this.editChart = this.editChart.bind( this );
		this.editSettings = this.editSettings.bind( this );
		this.editPermissions = this.editPermissions.bind( this );
		this.readUploadedFile = this.readUploadedFile.bind( this );
		this.editURL = this.editURL.bind( this );
		this.editSchedule = this.editSchedule.bind( this );
		this.uploadData = this.uploadData.bind( this );
		this.getChartData = this.getChartData.bind( this );
		this.editChartData = this.editChartData.bind( this );
		this.updateChart = this.updateChart.bind( this );

		this.state = {

			/**
			 * Block Route Status
			 *
			 * home - Initial screen.
			 * showCharts - Display list of charts to pick.
			 * chartSelect - Chart selected.
			 * renderChart - Chart render.
			 */
			route: ( this.props.attributes.route ? this.props.attributes.route : 'home' ),
			chart: null,
			isModified: false,
			isLoading: false,
			isScheduled: false
		};
	}

	async componentDidMount() {

		// Fetch review again if block loaded after saving.
		if ( this.props.attributes.id ) {
			let result = await apiFetch({ path: `wp/v2/visualizer/${this.props.attributes.id}` });

			this.setState({
				chart: result['chart_data']
			});
		}
	}

	async getChart( id ) {
		await this.setState({
			isLoading: 'getChart'
		});

		let result = await apiFetch({ path: `wp/v2/visualizer/${id}` });

		this.setState({
			route: 'chartSelect',
			chart: result['chart_data'],
			isLoading: false
		});

		this.props.setAttributes({
			id,
			route: 'chartSelect'
		});
	}

	editChart() {
		this.setState({ route: 'chartSelect' });
		this.props.setAttributes({ route: 'chartSelect' });
	}

	editSettings( settings ) {
		let chart = { ...this.state.chart };
		chart['visualizer-settings'] = settings;
		this.setState({
			chart,
			isModified: true
		});
	}

	editPermissions( permissions ) {
		let chart = { ...this.state.chart };
		chart['visualizer-permissions'] = permissions;
		this.setState({
			chart,
			isModified: true
		});
	}

	readUploadedFile( e ) {
		const fileTobeRead = e.current.files[0];
		const fileReader = new FileReader();
		fileReader.onload = () => {
			const data = CSVToArray( fileReader.result );
			this.editChartData( data, 'Visualizer_Source_Csv' );
		};
		fileReader.readAsText( fileTobeRead );
	}

	editURL( url ) {
		let chart = { ...this.state.chart };
		chart['visualizer-chart-url'] = url;
		this.setState({ chart });
	}

	editSchedule( schedule ) {
		let chart = { ...this.state.chart };
		chart['visualizer-chart-schedule'] = schedule;
		this.setState({ chart });
	}

	uploadData( scheduled = false ) {
		this.setState({
			isLoading: 'uploadData',
			isScheduled: scheduled
		});

		apiRequest({ path: `/visualizer/v1/upload-data?url=${this.state.chart['visualizer-chart-url']}`, method: 'POST' }).then(
			( data ) => {
				if ( 2 <= Object.keys( data ).length ) {
					let chart = { ...this.state.chart };

					chart['visualizer-source'] = 'Visualizer_Source_Csv_Remote';
					chart['visualizer-default-data'] = 0;
					chart['visualizer-series'] = data.series;
					chart['visualizer-data'] = data.data;

					let series = chart['visualizer-series'];
					let settings = chart['visualizer-settings'];
					let map = series;
					let fieldName = 'series';

					if ( 'pie' === chart['visualizer-chart-type']) {
						map = chart['visualizer-data'];
						fieldName = 'slices';
					}

					map.map( ( i, index ) => {
						if ( 'pie' !== chart['visualizer-chart-type'] && 0 === index ) {
							return;
						}

						const seriesIndex = 'pie' !== chart['visualizer-chart-type'] ? index - 1 : index;

						if ( settings[fieldName][seriesIndex] === undefined ) {
							settings[fieldName][seriesIndex] = {};
							settings[fieldName][seriesIndex].temp = 1;
						}
					});

					settings[fieldName] = settings[fieldName].filter( ( i, index ) => {
						const length = 'pie' !== chart['visualizer-chart-type'] ? map.length - 1 : map.length;
						return index < length;
					});

					chart['visualizer-settings'] = settings;

					this.setState({
						chart,
						isModified: true,
						isLoading: false
					});

					return data;
				}

				this.setState({
					isLoading: false
				});
			},
			( err ) => {
				this.setState({
					isLoading: false
				});

				return err;
			}
		);
	}

	async getChartData( id ) {
		await this.setState({
			isLoading: 'getChartData'
		});

		let result = await apiFetch({ path: `wp/v2/visualizer/${id}` });

		let chart = { ...this.state.chart };

		chart['visualizer-source'] = 'Visualizer_Source_Csv';
		chart['visualizer-default-data'] = 0;
		chart['visualizer-series'] = result['chart_data']['visualizer-series'];
		chart['visualizer-data'] = result['chart_data']['visualizer-data'];

		this.setState({
			isLoading: false,
			chart
		});
	}

	editChartData( chartData, type ) {
		let chart = { ...this.state.chart };
		let series = [];
		let settings = { ...chart['visualizer-settings'] };

		chartData[0].map( ( i, index ) => {
			series[index] = {
				label: i,
				type: chartData[1][index]
			};
		});

		chartData.splice( 0, 2 );

		let map = series;
		let fieldName = 'series';

		if ( 'pie' === chart['visualizer-chart-type']) {
			map = chartData;
			fieldName = 'slices';
		}

		map.map( ( i, index ) => {
			if ( 'pie' !== chart['visualizer-chart-type'] && 0 === index ) {
				return;
			}

			const seriesIndex = 'pie' !== chart['visualizer-chart-type'] ? index - 1 : index;

			if ( settings[fieldName][seriesIndex] === undefined ) {
				settings[fieldName][seriesIndex] = {};
				settings[fieldName][seriesIndex].temp = 1;
			}
		});

		settings[fieldName] = settings[fieldName].filter( ( i, index ) => {
			const length = 'pie' !== chart['visualizer-chart-type'] ? map.length - 1 : map.length;
			return index < length;
		});

		chart['visualizer-source'] = type;
		chart['visualizer-default-data'] = 0;
		chart['visualizer-data'] = chartData;
		chart['visualizer-series'] = series;
		chart['visualizer-settings'] = settings;
		chart['visualizer-chart-url'] = '';

		this.setState({
			chart,
			isModified: true,
			isScheduled: false
		});
	}

	updateChart() {
		this.setState({ isLoading: 'updateChart' });

		const data = this.state.chart;

		if ( false === this.state.isScheduled ) {
			data['visualizer-chart-schedule'] = '';
		}

		let fieldName = 'series';

		if ( 'pie' === data['visualizer-chart-type']) {
			fieldName = 'slices';
		}

		Object.keys( data['visualizer-settings'][fieldName])
			.map( i => {
				if ( data['visualizer-settings'][fieldName][i] !== undefined ) {
					if ( data['visualizer-settings'][fieldName][i].temp !== undefined ) {
						delete data['visualizer-settings'][fieldName][i].temp;
					}
				}
			}
			);

		apiRequest({ path: `/visualizer/v1/update-chart?id=${this.props.attributes.id}`, method: 'POST', data: data }).then(
			( data ) => {

				this.setState({
					isLoading: false,
					isModified: false
				});

				return data;
			},
			( err ) => {
				return err;
			}
		);
	}

	render() {
		if ( 'renderChart' === this.state.route && null !== this.state.chart ) {
			return (
				<ChartRender
					chart={ this.state.chart }
					className={ this.props.className }
					editChart={ this.editChart }
				/>
			);
		}

		return (
			<div className="visualizer-settings">

				<div className="visualizer-settings__title">

					<Dashicon icon="chart-pie" />
					{ __( 'Visualizer' ) }

				</div>

				{ 'home' === this.state.route && (
					<div className="visualizer-settings__content">

						<div className="visualizer-settings__content-description">
							{ __( 'Make a new chart or display an existing one?' ) }
						</div>

						<div className="visualizer-settings__content-option locked">

							<span className="visualizer-settings__content-option-title">
								{ __( 'Create a new chart (coming soon)' ) }
							</span>

							<div className="visualizer-settings__content-option-icon">
								<Dashicon icon="lock" />
							</div>

						</div>

						<div
							className="visualizer-settings__content-option"
							onClick={ () => {
								this.setState({ route: 'showCharts' });
								this.props.setAttributes({ route: 'showCharts' });
							} }
						>

							<span className="visualizer-settings__content-option-title">
								{ __( 'Display an existing chart' ) }
							</span>

							<div className="visualizer-settings__content-option-icon">
								<Dashicon icon="arrow-right-alt2" />
							</div>

						</div>

					</div>
				) }

				{ ( ( 'getChart' === this.state.isLoading ) || ( 'chartSelect' === this.state.route && null === this.state.chart ) || ( 'renderChart' === this.state.route && null === this.state.chart ) ) && (
					<Placeholder>
						<Spinner/>
					</Placeholder>
				) }

				{ ( 'showCharts' === this.state.route && false === this.state.isLoading ) && <Charts getChart={ this.getChart }/> }

				{ ( 'chartSelect' === this.state.route && null !== this.state.chart ) &&
					<ChartSelect
						chart={ this.state.chart }
						editSettings={ this.editSettings }
						editPermissions={ this.editPermissions }
						url={ this.state.url }
						readUploadedFile={ this.readUploadedFile }
						editURL={ this.editURL }
						editSchedule={ this.editSchedule }
						uploadData={ this.uploadData }
						getChartData={ this.getChartData }
						editChartData={ this.editChartData }
						isLoading={ this.state.isLoading }
					/>
				}

				<div className="visualizer-settings__controls">

					{ ( 'showCharts' === this.state.route || 'chartSelect' === this.state.route ) &&
						<ButtonGroup>

							<Button
								isDefault
								isLarge
								onClick={ () => {
									let route;
									if ( 'showCharts' === this.state.route ) {
										route = 'home';
									} else if ( 'chartSelect' === this.state.route ) {
										route = 'showCharts';
									}
									this.setState({ route });
									this.props.setAttributes({ route });
								} }
							>
								{ __( 'Back' ) }
							</Button>

							{ 'chartSelect' === this.state.route &&
								<Fragment>

									{ false === this.state.isModified ?
										<Button
											isDefault
											isLarge
											onClick={ () => {
												this.setState({ route: 'renderChart' });
												this.props.setAttributes({ route: 'renderChart' });
											} }
										>
											{ __( 'Done' ) }
										</Button>									:
										<Button
											isPrimary
											isLarge
											isBusy={ 'updateChart' === this.state.isLoading }
											disabled={ 'updateChart' === this.state.isLoading }
											onClick={ this.updateChart }
										>
											{ __( 'Save' ) }
										</Button>
									}

								</Fragment>
							}

						</ButtonGroup>
					}

				</div>
			</div>
		);
	}
}

export default Editor;
