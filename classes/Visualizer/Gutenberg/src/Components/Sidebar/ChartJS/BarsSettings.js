/**
 * WordPress dependencies
 */

 /* eslint-disable camelcase */

const { __ } = wp.i18n;

const { Component } = wp.element;

const {
	CheckboxControl,
	PanelBody,
	SelectControl,
	TextControl
} = wp.components;

class BarsSettingsChartJS extends Component {
	constructor() {
		super( ...arguments );
	}

	render() {

		const settings = this.props.chart['visualizer-settings'];

		return (
			<PanelBody
				title={ __( 'Bar Settings' ) }
				initialOpen={ false }
				className="visualizer-advanced-panel"
			>

				<TextControl
					label={ __( 'Bar Percentage' ) }
					help={ __( 'Percent (0-1) of the available width each bar should be within the category width.' ) }
                    type="number"
                    max="1"
                    min="0.1"
                    step="0.1"
					value={ settings.yAxes.barPercentage_int }
					onChange={ e => {
						settings.yAxes.barPercentage_int = e;
						this.props.edit( settings );
					} }
				/>

				<TextControl
					label={ __( 'Bar Thickness' ) }
					help={ __( 'Manually set width of each bar in pixels. If set to "flex", it computes "optimal" sample widths that globally arrange bars side by side. If not set (default), bars are equally sized based on the smallest interval.' ) }
					value={ settings.yAxes.barThickness }
					onChange={ e => {
						settings.yAxes.barThickness = e;
						this.props.edit( settings );
					} }
				/>

                <CheckboxControl
                    label={ __( 'Is Stacked' ) }
                    help={ __( 'If checked, series elements are stacked.' ) }
                    checked={ settings.yAxes.stacked_bool && 'true' === settings.yAxes.stacked_bool ? true : false }
                    onChange={ e => {
                        settings.yAxes.stacked_bool = 'true';
                        if ( ! e ) {
                            settings.yAxes.stacked_bool = 'false';
                        }
                        this.props.edit( settings );
                    } }
                />

			</PanelBody>
		);
	}
}

export default BarsSettingsChartJS;
