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

class BarsSettings extends Component {
	constructor() {
		super( ...arguments );
	}

	render() {

		const settings = this.props.chart['visualizer-settings'];

		return (
			<PanelBody
				title={ __( 'Bars Settings' ) }
				initialOpen={ false }
				className="visualizer-advanced-panel"
			>

				<SelectControl
					label={ __( 'Focus Target' ) }
					help={ __( 'The type of the entity that receives focus on mouse hover. Also affects which entity is selected by mouse click.' ) }
					value={ settings.focusTarget ? settings.focusTarget : 'datum' }
					options={ [
						{ label: __( 'Focus on a single data point.' ), value: 'datum' },
						{ label: __( 'Focus on a grouping of all data points along the major axis.' ), value: 'category' }
					] }
					onChange={ e => {
						settings.focusTarget = e;
						this.props.edit( settings );
					} }
				/>

				<SelectControl
					label={ __( 'Is Stacked' ) }
					help={ __( 'If set to yes, series elements are stacked.' ) }
					value={ settings.isStacked ? settings.isStacked : '0' }
					options={ [
						{ label: __( 'Yes' ), value: '1' },
						{ label: __( 'No' ), value: '0' }
					] }
					onChange={ e => {
						settings.isStacked = e;
						this.props.edit( settings );
					} }
				/>

				<TextControl
					label={ __( 'Bars Opacity' ) }
					help={ __( 'Bars transparency, with 1.0 being completely opaque and 0.0 fully transparent.' ) }
					value={ settings.dataOpacity }
					onChange={ e => {
						settings.dataOpacity = e;
						this.props.edit( settings );
					} }
				/>

			</PanelBody>
		);
	}
}

export default BarsSettings;
