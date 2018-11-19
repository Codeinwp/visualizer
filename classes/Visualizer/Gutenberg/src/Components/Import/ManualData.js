/**
 * External dependencies
 */
import ChartEditor from '../ChartEditor.js';

/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;

const {
	Component,
	Fragment
} = wp.element;

const {
	Button,
	Modal,
	PanelBody
} = wp.components;

class ManualData extends Component {
	constructor() {
		super( ...arguments );

		this.toggleModal = this.toggleModal.bind( this );

		this.state = {
			isOpen: false
		};
	}

	toggleModal() {
		this.setState({ isOpen: ! this.state.isOpen });
	}

	render() {
		return (
			( 'community' !== visualizerLocalize.isPro ) ?
				<Fragment>

					<PanelBody
						title={ __( 'Manual Data' ) }
						initialOpen={ false }
					>

						<p>{ __( 'You can manually edit the chart data using a spreadsheet like editor.' ) }</p>

						<Button
							isPrimary
							isLarge
							isBusy={ this.state.isOpen }
							onClick={ this.toggleModal }
						>
							{ __( 'View Editor' ) }
						</Button>

					</PanelBody>

					{ this.state.isOpen && (
						<Modal
							title={ 'Chart Editor' }
							onRequestClose={ this.toggleModal }
							shouldCloseOnClickOutside={ false }
						>
							<ChartEditor
								chart={ this.props.chart }
								editChartData={ this.props.editChartData }
								toggleModal={ this.toggleModal }
							/>
						</Modal>
					) }

				</Fragment>			:
				<PanelBody
					title={ __( 'Manual Data' ) }
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

export default ManualData;
