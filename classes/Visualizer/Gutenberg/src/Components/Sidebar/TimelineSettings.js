/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;

const { Component } = wp.element;

const { ColorPalette } = wp.editor;

const {
	BaseControl,
	CheckboxControl,
	PanelBody
} = wp.components;

class TimelineSettings extends Component {
	constructor() {
		super( ...arguments );

		this.mapValues = this.mapValues.bind( this );
	}

	mapValues( settings, objName ) {
		if ( settings.timeline === undefined ) {
			return settings[ objName ];
		}
		return settings.timeline[ objName ];
	}

	render() {

		const settings = this.props.chart['visualizer-settings'];
		this.mapValues( settings, 'showRowLabels' );

		return (
			<PanelBody
				title={ __( 'Timeline Settings' ) }
				initialOpen={ false }
				className="visualizer-advanced-panel"
			>

				<CheckboxControl
					label={ __( 'Show Row Label' ) }
					help={ __( 'If checked, shows the category/row label.' ) }
					checked={ Number( this.mapValues( settings, 'showRowLabels' ) ) }
					onChange={ e => {
						if ( settings.timeline === undefined ) {
							settings.timeline = {};
						}
						settings.timeline.showRowLabels = ! Number( this.mapValues( settings, 'showRowLabels' ) );
						this.props.edit( settings );
					} }
				/>

				<CheckboxControl
					label={ __( 'Group by Row Label' ) }
					help={ __( 'If checked, groups the bars on the basis of the category/row label.' ) }
					checked={ Number( this.mapValues( settings, 'groupByRowLabel' ) ) }
					onChange={ e => {
						if ( settings.timeline === undefined ) {
							settings.timeline = {};
						}
						settings.timeline.groupByRowLabel = ! Number( this.mapValues( settings, 'groupByRowLabel' ) );
						this.props.edit( settings );
					} }
				/>

				<CheckboxControl
					label={ __( 'Color by Row Label' ) }
					help={ __( 'If checked, colors every bar on the row the same.' ) }
					checked={ Number( this.mapValues( settings, 'colorByRowLabel' ) ) }
					onChange={ e => {
						if ( settings.timeline === undefined ) {
							settings.timeline = {};
						}
						settings.timeline.colorByRowLabel = ! Number( this.mapValues( settings, 'colorByRowLabel' ) );
						this.props.edit( settings );
					} }
				/>

				<BaseControl
					label={ __( 'Single Color' ) }
				>
					<ColorPalette
						value={ this.mapValues( settings, 'singleColor' ) }
						onChange={ e => {
							if ( settings.timeline === undefined ) {
								settings.timeline = {};
							}
							settings.timeline.singleColor = e;
							this.props.edit( settings );
						} }
					/>
				</BaseControl>

			</PanelBody>
		);
	}
}

export default TimelineSettings;
