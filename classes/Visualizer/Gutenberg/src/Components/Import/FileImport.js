/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;

const { Component } = wp.element;

const {
	Button,
	ExternalLink,
	PanelBody
} = wp.components;

class FileImport extends Component {
	constructor() {
		super( ...arguments );

		this.uploadInput = React.createRef();
		this.fileUploaded = this.fileUploaded.bind( this );
		this.uploadImport = this.uploadImport.bind( this );

		this.state = {
			uploadLabel: __( 'Upload' )
		};
	}

	fileUploaded( e ) {
		if ( 'text/csv' === e.target.files[0].type ) {
			this.setState({ uploadLabel: __( 'Upload' ) });
		}
	}

	uploadImport() {
		this.props.readUploadedFile( this.uploadInput );
		this.setState({ uploadLabel: __( 'Uploaded' ) });
	}

	render() {
		return (
			<PanelBody
				title={ __( 'Import data from file' ) }
				initialOpen={ false }
			>

				<p>{ __( 'Select and upload your data CSV file here. The first row of the CSV file should contain the column headings. The second one should contain series type (string, number, boolean, date, datetime, timeofday).' ) }</p>
				<p>
					{ __( 'If you are unsure about how to format your data CSV then please take a look at this sample: ' ) }
					<ExternalLink href={ `${visualizerLocalize.absurl}samples/${this.props.chart['visualizer-chart-type']}.csv` }>
						{ `${this.props.chart['visualizer-chart-type']}.csv` }
					</ExternalLink>
				</p>

				<input
					type="file"
					accept="text/csv"
					ref={ this.uploadInput }
					onChange={ this.fileUploaded }
				/>

				<Button
					isPrimary
					onClick={ this.uploadImport }
				>
					{ this.state.uploadLabel }
				</Button>

			</PanelBody>
		);
	}
}

export default FileImport;
