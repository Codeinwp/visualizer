/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;

const { Component } = wp.element;

const {
	Button,
	Modal,
	PanelBody,
	SelectControl
} = wp.components;

/**
 * Internal dependencies
 */
import SQLEditor from './SQLEditor.js';

class DataImport extends Component {
	constructor() {
		super( ...arguments );
		this.save = this.save.bind( this );

		this.state = {
			isOpen: false
		};
	}

	save( query, name, series, data ) {
		this.props.databaseImportData( query, name, series, data );
		this.setState({ isOpen: false });
	}

	render() {
		if ( ( 'business' !== visualizerLocalize.isPro ) ) {
			return (
				<PanelBody
					title={ __( 'Import data from database' ) }
					icon="lock"
					initialOpen={ false }
				>

					<p>{ __( 'Upgrade your license to at least the DEVELOPER version to activate this feature!' ) }</p>

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

		return (
			<PanelBody
				title={ __( 'Import data from database' ) }
				initialOpen={ false }
			>

				<p>{ __( 'You can import data from the database here.' ) }</p>

				<p>{ __( 'How often do you want to refresh the data from the database.' ) }</p>

				<SelectControl
					label={ __( 'How often do you want to check the url?' ) }
					value={ this.props.chart['visualizer-db-schedule'] ? this.props.chart['visualizer-db-schedule'] : 0 }
					options={ [
						{ label: __( 'Live' ), value: '0' },
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
					onClick={ () => this.setState({ isOpen: true }) }
				>
					{ __( 'Create Query' ) }
				</Button>

				{ this.state.isOpen && (
					<Modal
						title={ __( 'Import from database' ) }
						onRequestClose={ () => this.setState({ isOpen: false }) }
						className="visualizer-db-query-modal"
						shouldCloseOnClickOutside={ false }
					>
						<SQLEditor
							chart={ this.props.chart }
							changeQuery={ this.props.changeQuery }
							save={ this.save }
						/>
					</Modal>
				)}

			</PanelBody>
		);
	}
}

export default DataImport;
