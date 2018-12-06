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

class SlicesSettings extends Component {
	constructor() {
		super( ...arguments );
	}

	componentDidMount() {

		/**
		 * We use deep-clean to remove all empty properties which causes a major issue with Slices Settings.
		 * So add a dummy property to make sure `slices` object isn't empty.
		 * Should be removed before saving the data.
		 */
		const settings = this.props.chart['visualizer-settings'];

		Object.keys( settings.slices )
			.map( ( i ) => {
				if ( settings.slices[i] !== undefined ) {
					settings.slices[i].temp = 1;
				}
			}
			);

		this.props.edit( settings );
	}

	render() {

		const settings = this.props.chart['visualizer-settings'];

		const data = this.props.chart['visualizer-data'];

		return (
			<PanelBody
				title={ __( 'Slices Settings' ) }
				initialOpen={ false }
				className="visualizer-advanced-panel"
			>

				{ Object.keys( settings.slices )
					.map( ( i ) => {
						return (
							<PanelBody
								title={ data[i][0] }
								className="visualizer-inner-sections"
								initialOpen={ false }
							>

								<TextControl
									label={ __( 'Slice Offset' ) }
									help={ __( 'How far to separate the slice from the rest of the pie, from 0.0 (not at all) to 1.0 (the pie\'s radius).' ) }
									value={ settings.slices[i].offset }
									onChange={ e => {
										settings.slices[i].offset = e;
										this.props.edit( settings );
									} }
								/>

								<BaseControl
									label={ __( 'Format' ) }
								>
									<ColorPalette
										value={ settings.slices[i].color }
										onChange={ e => {
											settings.slices[i].color = e;
											this.props.edit( settings );
										} }
									/>
								</BaseControl>

							</PanelBody>
						);
					}
					) }

			</PanelBody>
		);
	}
}

export default SlicesSettings;
