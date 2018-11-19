/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;

const { Component } = wp.element;

const {
	PanelBody,
	SelectControl,
	TextControl
} = wp.components;

class MagnifyingGlass extends Component {
	constructor() {
		super( ...arguments );
	}

	render() {

		const settings = this.props.chart['visualizer-settings'];

		return (
			<PanelBody
				title={ __( 'Magnifying Glass' ) }
				initialOpen={ false }
				className="visualizer-advanced-panel"
			>

				<SelectControl
					label={ __( 'Enabled' ) }
					help={ __( 'If yes, when the user lingers over a cluttered marker, a magnifiying glass will be opened.' ) }
					value={ settings.magnifyingGlass.enable ? settings.magnifyingGlass.enable : '1' }
					options={ [
						{ label: __( 'Yes' ), value: '1' },
						{ label: __( 'No' ), value: '0' }
					] }
					onChange={ e => {
						settings.magnifyingGlass.enable = e;
						this.props.edit( settings );
					} }
				/>

				<TextControl
					label={ __( 'Zoom Factor' ) }
					help={ __( 'The zoom factor of the magnifying glass. Can be any number greater than 0.' ) }
					value={ settings.magnifyingGlass.zoomFactor }
					onChange={ e => {
						settings.magnifyingGlass.zoomFactor = e;
						this.props.edit( settings );
					} }
				/>

			</PanelBody>
		);
	}
}

export default MagnifyingGlass;
