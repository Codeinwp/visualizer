/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;

const { Component } = wp.element;

const { ColorPalette } = wp.editor;

const {
	BaseControl,
	PanelBody,
	TextControl
} = wp.components;

class ResidueSettings extends Component {
	constructor() {
		super( ...arguments );
	}

	render() {

		const settings = this.props.chart['visualizer-settings'];

		return (
			<PanelBody
				title={ __( 'Residue Settings' ) }
				initialOpen={ false }
				className="visualizer-advanced-panel"
			>

				<TextControl
					label={ __( 'Visibility Threshold' ) }
					help={ __( 'The slice relative part, below which a slice will not show individually. All slices that have not passed this threshold will be combined to a single slice, whose size is the sum of all their sizes. Default is not to show individually any slice which is smaller than half a degree.' ) }
					placeholder={ __( '0.001388889' ) }
					value={ settings.sliceVisibilityThreshold }
					onChange={ e => {
						settings.sliceVisibilityThreshold = e;
						this.props.edit( settings );
					} }
				/>

				<TextControl
					label={ __( 'Residue Slice Label' ) }
					help={ __( 'A label for the combination slice that holds all slices below slice visibility threshold.' ) }
					value={ settings.pieResidueSliceLabel }
					onChange={ e => {
						settings.pieResidueSliceLabel = e;
						this.props.edit( settings );
					} }
				/>

				<BaseControl
					label={ __( 'Residue Slice Color' ) }
				>
					<ColorPalette
						value={ settings.pieResidueSliceColor }
						onChange={ e => {
							settings.pieResidueSliceColor = e;
							this.props.edit( settings );
						} }
					/>
				</BaseControl>

			</PanelBody>
		);
	}
}

export default ResidueSettings;
