/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;

const { Component } = wp.element;

const {
	PanelBody,
	SelectControl
} = wp.components;

class ComboSettings extends Component {
	constructor() {
		super( ...arguments );
	}

	render() {

		const settings = this.props.chart['visualizer-settings'];

		return (
			<PanelBody
				title={ __( 'Combo Settings' ) }
				initialOpen={ false }
				className="visualizer-advanced-panel"
			>

				<SelectControl
					label={ __( 'Chart Type' ) }
					help={ __( 'Select the default chart type.' ) }
					value={ settings.seriesType ? settings.seriesType : 'area' }
					options={ [
						{ label: __( 'Area' ), value: 'area' },
						{ label: __( 'Bar' ), value: 'bars' },
						{ label: __( 'Candlesticks' ), value: 'candlesticks' },
						{ label: __( 'Line' ), value: 'line' },
						{ label: __( 'Stepped Area' ), value: 'steppedArea' }
					] }
					onChange={ e => {
						settings.seriesType = e;
						this.props.edit( settings );
					} }
				/>

			</PanelBody>
		);
	}
}

export default ComboSettings;
