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

class ColorAxis extends Component {
	constructor() {
		super( ...arguments );
	}

	render() {

		const settings = this.props.chart['visualizer-settings'];

		return (
			<PanelBody
				title={ __( 'Color Axis' ) }
				initialOpen={ false }
				className="visualizer-advanced-panel"
			>

				<TextControl
					label={ __( 'Minimum Values' ) }
					help={ __( 'Determines the minimum values of color axis.' ) }
					value={ settings.colorAxis.minValue }
					onChange={ e => {
						settings.colorAxis.minValue = e;
						this.props.edit( settings );
					} }
				/>

				<TextControl
					label={ __( 'Maximum Values' ) }
					help={ __( 'Determines the maximum values of color axis.' ) }
					value={ settings.colorAxis.maxValue }
					onChange={ e => {
						settings.colorAxis.maxValue = e;
						this.props.edit( settings );
					} }
				/>

				<BaseControl
					label={ __( 'Minimum Value' ) }
				>
					<ColorPalette
						value={ settings.colorAxis.colors[0] }
						onChange={ e => {
							settings.colorAxis.colors[0] = e;
							this.props.edit( settings );
						} }
					/>
				</BaseControl>

				<BaseControl
					label={ __( 'Intermediate Value' ) }
				>
					<ColorPalette
						value={ settings.colorAxis.colors[1] }
						onChange={ e => {
							settings.colorAxis.colors[1] = e;
							this.props.edit( settings );
						} }
					/>
				</BaseControl>

				<BaseControl
					label={ __( 'Maximum Value' ) }
				>
					<ColorPalette
						value={ settings.colorAxis.colors[2] }
						onChange={ e => {
							settings.colorAxis.colors[2] = e;
							this.props.edit( settings );
						} }
					/>
				</BaseControl>

				<BaseControl
					label={ __( 'Dateless Region' ) }
				>
					<ColorPalette
						value={ settings.datalessRegionColor }
						onChange={ e => {
							settings.datalessRegionColor = e;
							this.props.edit( settings );
						} }
					/>
				</BaseControl>

			</PanelBody>
		);
	}
}

export default ColorAxis;
