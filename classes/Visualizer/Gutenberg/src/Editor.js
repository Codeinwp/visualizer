/**
 * External dependencies
 */
import Charts from './Components/Charts.js';

import ChartSelect from './Components/ChartSelect.js';

import ChartRender from './Components/ChartRender.js';

import { buildChartPopup, tryGetPublishedChartData } from './utils.js';

/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;

const { apiFetch } = wp;

const {
	Component,
	Fragment
} = wp.element;

const {
	Button,
	ButtonGroup,
	Dashicon,
	Placeholder,
	Notice,
	Spinner
} = wp.components;

class Editor extends Component {
	constructor() {
		super( ...arguments );

		this.getChart = this.getChart.bind( this );
		this.editChart = this.editChart.bind( this );
		this.editSettings = this.editSettings.bind( this );
		this.getChartData = this.getChartData.bind( this );
		this.updateChart = this.updateChart.bind( this );
		this.createChart = this.createChart.bind( this );

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
			isLoading: false
		};
	}

	async componentDidMount() {

		// Fetch review again if block loaded after saving.
		if ( this.props.attributes.id ) {
			let result = await apiFetch({ path: `wp/v2/visualizer/${this.props.attributes.id}` }).catch( function( error ) {
            });

            if ( result ) {
                this.setState({
                    chart: result['chart_data']
                });
            } else {

                // if the chart is not found.
                this.setState({
                    route: 'error'
                });
            }
		}
	}

	async getChart( id ) {
		await this.setState({
			isLoading: 'getChart'
		});

		const chartDataRequest = await tryGetPublishedChartData( id );

		if ( 'publish' !== chartDataRequest.chartStatus ) {
			this.setState({ route: 'showCharts', isLoading: false });
			this.props.setAttributes({ route: 'showCharts' });
			return;
		}

		this.setState({
			route: 'chartSelect',
			chart: chartDataRequest.result['chart_data'],
			isLoading: true,
			isModified: true
		});

		this.props.setAttributes({
			id,
			route: 'chartSelect',
            lazy: -1
		});
	}

	editChart() {
		this.setState({ route: 'chartSelect' });
		this.props.setAttributes({ route: 'chartSelect' });
	}

	editSettings( settings ) {
		let chart = { ...this.state.chart };
		if ( '1' !== settings.pagination ) {
			delete settings.pageSize;
		}
		chart['visualizer-settings'] = settings;
		this.setState({
			chart,
			isModified: true
		});
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

	updateChart() {
		this.setState({ isLoading: 'updateChart' });

		const data = this.state.chart;

		let dataChartStylingOption = 'series';

		if ( 'pie' === data['visualizer-chart-type']) {
			dataChartStylingOption = 'slices';
		}

        // no series for bubble and timeline charts.
		if (
			undefined !== data['visualizer-settings'][dataChartStylingOption] &&
			-1 >= [ 'bubble', 'timeline' ].indexOf( data['visualizer-chart-type'])
		) {
            Object.keys( data['visualizer-settings'][dataChartStylingOption])
                .map( i => {
                    if ( data['visualizer-settings'][dataChartStylingOption][i] !== undefined ) {
                        if ( data['visualizer-settings'][dataChartStylingOption][i].temp !== undefined ) {
                            delete data['visualizer-settings'][dataChartStylingOption][i].temp;
                        }
                    }
                }
            );
        }

		apiRequest({ path: `/visualizer/v1/update-chart?id=${ this.props.attributes.id }`, method: 'POST', data: data }).then(
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

	/**
	 * Create a new chart via popup.
	 *
	 * @returns {void}
	 */
	async createChart() {

		// Use the same popup like in Chart Library.
		const createChartPopup = new ( buildChartPopup() )({
			action: visualizerLocalize.createChart
		});

		// eslint-disable-next-line camelcase
		window.send_to_editor = () => {
			createChartPopup.close();
		};

		window.parent.addEventListener( 'message', ( event ) => {
			if ( 'visualizer:mediaframe:close' === event.data ) {
				createChartPopup.close();
			} else if ( event.data.chartID && null === this.state.chart ) {
				const chartID = parseInt(  event.data.chartID, 10 );
				this.getChart( chartID );
			}
		}, false );

		createChartPopup.open();
	}

	render() {
		if ( 'error' === this.state.route ) {
			return (
				<Notice
					status="error"
					isDismissible={ false }
				>
                        <Dashicon icon="chart-pie" />
                        { __( 'This chart is not available; it might have been deleted. Please delete this block and resubmit your chart.' ) }
				</Notice>
			);
		}

		if ( '1' === visualizerLocalize.isFullSiteEditor ) {
			return (
				<Notice
					status="error"
					isDismissible={ false }
				>
                        <Dashicon icon="chart-pie" />
                        { __( 'Visualizer block charts are currently not available for selection here, you must visit the library, get the shortcode, and add the chart here in a shortcode tag.' ) }
				</Notice>
			);
		}

		if ( 'renderChart' === this.state.route && null !== this.state.chart ) {
			return (
				<ChartRender
					id={ this.props.attributes.id }
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

						{ /* You can apply "locked" class to lock any of the following list items. */ }

						<a
							onClick={ this.createChart }
							target="_blank"
							className="visualizer-settings__content-option"
						>

							<span className="visualizer-settings__content-option-title">
								{ __( 'Create a new chart' ) }
							</span>

							<div className="visualizer-settings__content-option-icon">
								<Dashicon icon="arrow-right-alt2" />
							</div>

						</a>

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
						id={ this.props.attributes.id }
						chart={ this.state.chart }
						editSettings={ this.editSettings }
						getChartData={ this.getChartData }
					/>
				}

				<div className="visualizer-settings__controls">

					{ ( 'showCharts' === this.state.route || 'chartSelect' === this.state.route ) &&
						<ButtonGroup>

							<Button
								variant="secondary"
								isLarge
								onClick={ () => {
									let route;
									if ( 'showCharts' === this.state.route ) {
										route = 'home';
									} else if ( 'chartSelect' === this.state.route ) {
										route = 'showCharts';
									}
									this.setState({ route, isLoading: false });
									this.props.setAttributes({ route });
								} }
							>
								{ __( 'Back' ) }
							</Button>

							{ 'chartSelect' === this.state.route &&
								<Fragment>

									{ false === this.state.isModified ?
										<Button
											variant="secondary"
											isLarge
                                            className="visualizer-bttn-done"
											onClick={ () => {
												this.setState({ route: 'renderChart', isModified: true });
												this.props.setAttributes({ route: 'renderChart' });
											} }
										>
											{ __( 'Done' ) }
										</Button>									:
										<Button
											isPrimary
											isLarge
                                            className="visualizer-bttn-save"
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
