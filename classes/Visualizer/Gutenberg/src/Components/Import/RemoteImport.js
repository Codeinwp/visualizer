/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;

const { Component } = wp.element;

const {
	Button,
	ExternalLink,
	PanelBody,
	SelectControl,
	TextControl
} = wp.components;

class RemoteImport extends Component {
	constructor() {
		super( ...arguments );
	}

	render() {
		return (
			<PanelBody
				title={ __( 'Import data from URL' ) }
				initialOpen={ false }
				className="visualizer-advanced-panel"
			>

				<PanelBody
					title={ __( 'One Time Import' ) }
					className="visualizer-inner-sections"
					initialOpen={ false }
				>

					<p>{ __( 'You can use this to import data from a remote CSV file. The first row of the CSV file should contain the column headings. The second one should contain series type (string, number, boolean, date, datetime, timeofday).' ) }</p>
					<p>
						{ __( 'If you are unsure about how to format your data CSV then please take a look at this sample: ' ) }
						<ExternalLink href={ `${visualizerLocalize.absurl}samples/${this.props.chart['visualizer-chart-type']}.csv` }>
							{ `${this.props.chart['visualizer-chart-type']}.csv` }
						</ExternalLink>
					</p>
					<p>{ __( 'You can also import data from Google Spreadsheet.' ) }</p>

					<TextControl
						placeholder={ __( 'Please enter the URL of your CSV file' ) }
						value={ this.props.chart['visualizer-chart-url'] ? this.props.chart['visualizer-chart-url'] : '' }
						onChange={ this.props.editURL }
					/>

					<Button
						isPrimary
						isLarge
						isBusy={ 'uploadData' === this.props.isLoading }
						disabled={ 'uploadData' === this.props.isLoading }
						onClick={ () => this.props.uploadData( false ) }
					>
						{ __( 'Import Data' ) }
					</Button>

				</PanelBody>

				{ ( 'business' === visualizerLocalize.isPro ) ?
					<PanelBody
						title={ __( 'Schedule Import' ) }
						className="visualizer-inner-sections"
						initialOpen={ false }
					>

						<p>{ __( 'You can choose here to synchronize your chart data with a remote CSV file. ' ) }</p>
						<p>{ __( 'You can also synchronize with your Google Spreadsheet file.' ) }</p>
						<p>{ __( 'We will update the chart data based on your time interval preference by overwritting the current data with the one from the URL.' ) }</p>

						<TextControl
							placeholder={ __( 'Please enter the URL of your CSV file' ) }
							value={ this.props.chart['visualizer-chart-url'] ? this.props.chart['visualizer-chart-url'] : '' }
							onChange={ this.props.editURL }
						/>

						<SelectControl
							label={ __( 'How often do you want to check the url?' ) }
							value={ this.props.chart['visualizer-chart-schedule'] ? this.props.chart['visualizer-chart-schedule'] : 1 }
							options={ [
								{ label: __( 'Each hour' ), value: '1' },
								{ label: __( 'Each 12 hours' ), value: '12' },
								{ label: __( 'Each day' ), value: '24' },
								{ label: __( 'Each 3 days' ), value: '72' }
							] }
							onChange={ this.props.editSchedule }
						/>

						<Button
							isPrimary
							isLarge
							isBusy={ 'uploadData' === this.props.isLoading }
							disabled={ 'uploadData' === this.props.isLoading }
							onClick={ () => this.props.uploadData( true ) }
						>
							{ __( 'Save Schedule' ) }
						</Button>

					</PanelBody>				:
					<PanelBody
						title={ __( 'Schedule Import' ) }
						icon="lock"
						className="visualizer-inner-sections"
						initialOpen={ false }
					>

						<p>{ __( 'Enable this feature in BUSINESS version!' ) }</p>

						<Button
							isPrimary
							href={ visualizerLocalize.proTeaser }
							target="_blank"
						>
							{ __( 'Buy Now' ) }
						</Button>

					</PanelBody>
				}

			</PanelBody>
		);
	}
}

export default RemoteImport;
