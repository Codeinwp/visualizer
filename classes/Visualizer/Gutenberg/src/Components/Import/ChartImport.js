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
	PanelBody,
	Placeholder,
	SelectControl,
	Spinner
} = wp.components;

class ChartImport extends Component {
	constructor() {
		super( ...arguments );

		this.state = {
			id: '',
			charts: []
		};
	}

	async componentDidMount() {
		let charts = await apiFetch({ path: 'wp/v2/visualizer/?per_page=100' });
		let id;

		charts = charts.map( ( i, index ) => {
			let label = i['chart_data']['visualizer-settings'].title ? i['chart_data']['visualizer-settings'].title : `#${i.id}`;

			if ( 0 === index ) {
				id = i.id;
			}

			return {
				value: i.id,
				label
			};
		});

		this.setState({
			id,
			charts
		});
	}

	render() {
		return (
			( 'community' !== visualizerLocalize.isPro ) ?
				<PanelBody
					title={ __( 'Import from other chart' ) }
					initialOpen={ false }
				>

					{ 1 <= ( this.state.charts ).length ?
						<Fragment>

							<SelectControl
								label={ __( 'You can import here data from your previously created charts.' ) }
								value={ this.state.id }
								options={ this.state.charts }
								onChange={ id => this.setState({ id }) }
							/>

							<Button
								isPrimary
								isBusy={ 'getChartData' === this.props.isLoading }
								onClick={ () => this.props.getChartData( this.state.id ) }
							>
								{ __( 'Import Chart' ) }
							</Button>

						</Fragment>				:
						<Placeholder>
							<Spinner />
						</Placeholder>
					}

				</PanelBody>			:
				<PanelBody
					title={ __( 'Import from other chart' ) }
					icon="lock"
					initialOpen={ false }
				>

					<p>{ __( 'Enable this feature in PRO version!' ) }</p>

					<Button
						isPrimary
						href={ visualizerLocalize.proTeaser }
						target="_blank"
					>
						{ __( 'Buy Now' ) }
					</Button>

				</PanelBody>
		);
	}
}

export default ChartImport;
