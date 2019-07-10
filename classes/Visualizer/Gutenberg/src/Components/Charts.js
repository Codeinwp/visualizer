/**
 * External dependencies
 */
import { Chart } from 'react-google-charts';

import { formatDate, filterCharts } from '../utils.js';

/**
 * WordPress dependencies
 */
const { startCase } = lodash;

const { __ } = wp.i18n;

const { apiFetch } = wp;

const {
	Component,
	Fragment
} = wp.element;

const {
	Button,
	Dashicon,
	Placeholder,
	Spinner
} = wp.components;

class Charts extends Component {
	constructor() {
		super( ...arguments );

		this.loadMoreCharts = this.loadMoreCharts.bind( this );

		this.state = {
			charts: null,
			isBusy: false,
			chartsLoaded: false
		};
	}

	async componentDidMount() {

		// Fetch review again if block loaded after saving.
		let result = await apiFetch({ path: 'wp/v2/visualizer/?per_page=6' });
		this.setState({ charts: result });
	}

	async loadMoreCharts() {
		const offset = ( this.state.charts ).length;
		let chartsLoaded = this.state.chartsLoaded;

		this.setState({ isBusy: true });

		let result = await apiFetch({ path: `wp/v2/visualizer/?per_page=6&offset=${ offset }` });

		if ( 6 > result.length ) {
			chartsLoaded = true;
		}

		this.setState({
			charts: this.state.charts.concat( result ),
			isBusy: false,
			chartsLoaded
		});
	}

	render() {

		const { charts, isBusy, chartsLoaded } = this.state;

		return (
			<div className="visualizer-settings__charts">

				{
					( null !== charts ) ?
						( 1 <= charts.length ) ?
							<Fragment>

								<div className="visualizer-settings__charts-grid">

									{ ( Object.keys( charts ) ).map( i => {

										const data = formatDate( charts[i]['chart_data']);

										let title, chart;

										if ( data['visualizer-settings'].title ) {
											title = data['visualizer-settings'].title;
										} else {
											title = `#${charts[i].id}`;
										}

										if ( 0 <= [ 'gauge', 'table', 'timeline', 'dataTable' ].indexOf( data['visualizer-chart-type']) ) {
											if ( 'dataTable' === data['visualizer-chart-type']) {
												chart = data['visualizer-chart-type'];
											} else {
												chart = startCase( data['visualizer-chart-type']);
											}
										} else {
											chart = `${ startCase( data['visualizer-chart-type']) }Chart`;
										}

										if ( 'dataTable' === chart ) {
											return;
										}

										return (
											<div className="visualizer-settings__charts-single">

												<div className="visualizer-settings__charts-title">
													{ title }
												</div>

												<Chart
													chartType={ chart }
													rows={ data['visualizer-data'] }
													columns={ data['visualizer-series'] }
													options={ filterCharts( data['visualizer-settings']) }
												/>

												<div
													className="visualizer-settings__charts-controls"
													title={ __( 'Insert Chart' ) }
													onClick={ () => this.props.getChart( charts[i].id ) }
												>
													<Dashicon icon="upload"></Dashicon>
												</div>

											</div>
										);
									}) }
								</div>

								{ ! chartsLoaded && (
									<Button
										isPrimary
										isLarge
										onClick={ this.loadMoreCharts }
										isBusy={ isBusy }
									>
										{ __( 'Load More' ) }
									</Button>
								) }

							</Fragment>						:
							<p className="visualizer-no-charts">
								{ __( 'No charts found.' ) }
							</p>					:
						<Placeholder>
							<Spinner/>
						</Placeholder>
				}

			</div>
		);
	}
}

export default Charts;
