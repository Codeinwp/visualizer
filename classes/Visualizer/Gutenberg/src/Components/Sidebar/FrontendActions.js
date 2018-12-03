/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;

const {
	Component,
	Fragment
} = wp.element;

const {
	CheckboxControl,
	PanelBody
} = wp.components;

class FrontendActions extends Component {
	constructor() {
		super( ...arguments );
	}

	componentDidMount() {
		const settings = this.props.chart['visualizer-settings'];

		if ( settings.actions === undefined ) {
			settings.actions = [];
		}

		this.props.edit( settings );
	}

	render() {

		const settings = this.props.chart['visualizer-settings'];

		return (
			<PanelBody
				title={ __( 'Frontend Actions' ) }
				initialOpen={ false }
				className="visualizer-advanced-panel"
			>

				{ ( settings.actions !== undefined &&

					<Fragment>

						<CheckboxControl
							label={ __( 'Print' ) }
							help={ __( 'To enable printing the data.' ) }
							checked={ ( 0 <= settings.actions.indexOf( 'print' ) ) }
							onChange={ e => {
								if ( 0 <= settings.actions.indexOf( 'print' ) ) {
									const index = settings.actions.indexOf( 'print' );
									if ( -1 !== index ) {
										settings.actions.splice( index, 1 );
									}
								} else {
									settings.actions.push( 'print' );
								}
								this.props.edit( settings );
							} }
						/>

						<CheckboxControl
							label={ __( 'CSV' ) }
							help={ __( 'To enable downloading the data as a CSV.' ) }
							checked={ ( 0 <= settings.actions.indexOf( 'csv;application/csv' ) ) }
							onChange={ e => {
								if ( 0 <= settings.actions.indexOf( 'csv;application/csv' ) ) {
									const index = settings.actions.indexOf( 'csv;application/csv' );
									if ( -1 !== index ) {
										settings.actions.splice( index, 1 );
									}
								} else {
									settings.actions.push( 'csv;application/csv' );
								}
								this.props.edit( settings );
							} }
						/>

						<CheckboxControl
							label={ __( 'Excel' ) }
							help={ __( 'To enable downloading the data as an Excel spreadsheet.' ) }
							checked={ ( 0 <= settings.actions.indexOf( 'xls;application/vnd.ms-excel' ) ) }
							onChange={ e => {
								if ( 0 <= settings.actions.indexOf( 'xls;application/vnd.ms-excel' ) ) {
									const index = settings.actions.indexOf( 'xls;application/vnd.ms-excel' );
									if ( -1 !== index ) {
										settings.actions.splice( index, 1 );
									}
								} else {
									settings.actions.push( 'xls;application/vnd.ms-excel' );
								}
								this.props.edit( settings );
							} }
						/>

						<CheckboxControl
							label={ __( 'Copy' ) }
							help={ __( 'To enable copying the data to the clipboard.' ) }
							checked={ ( 0 <= settings.actions.indexOf( 'copy' ) ) }
							onChange={ e => {
								if ( 0 <= settings.actions.indexOf( 'copy' ) ) {
									const index = settings.actions.indexOf( 'copy' );
									if ( -1 !== index ) {
										settings.actions.splice( index, 1 );
									}
								} else {
									settings.actions.push( 'copy' );
								}
								this.props.edit( settings );
							} }
						/>

					</Fragment>

				) }

			</PanelBody>
		);
	}
}

export default FrontendActions;
