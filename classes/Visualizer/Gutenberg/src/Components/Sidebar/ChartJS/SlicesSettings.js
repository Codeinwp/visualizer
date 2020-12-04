/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;

const { Component } = wp.element;

const { ColorPalette } = wp.blockEditor || wp.editor;

const {
	BaseControl,
	PanelBody,
	TextControl
} = wp.components;

class SlicesSettingsChartJS extends Component {
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

                                <BaseControl
                                    label={ __( 'Arc border color' ) }
                                >
                                    <ColorPalette
                                        value={ settings.slices[i].borderColor }
                                        onChange={ e => {
                                            settings.slices[i].borderColor = e;
                                            this.props.edit( settings );
                                        } }
                                    />
                                </BaseControl>

                                <BaseControl
                                    label={ __( 'Arc background color' ) }
                                >
                                    <ColorPalette
                                        value={ settings.slices[i].backgroundColor }
                                        onChange={ e => {
                                            settings.slices[i].backgroundColor = e;
                                            this.props.edit( settings );
                                        } }
                                    />
                                </BaseControl>

                                <TextControl
                                    label={ __( 'Arc border width' ) }
                                    value={ settings.slices[i].borderWidth }
                                    onChange={ e => {
                                        settings.slices[i].borderWidth = e;
                                        this.props.edit( settings );
                                    } }
                                />

                                <BaseControl
                                    label={ __( 'Arc border color when hovered' ) }
                                >
                                    <ColorPalette
                                        value={ settings.slices[i].hoverBorderColor }
                                        onChange={ e => {
                                            settings.slices[i].hoverBorderColor = e;
                                            this.props.edit( settings );
                                        } }
                                    />
                                </BaseControl>

                                <BaseControl
                                    label={ __( 'Arc background color when hovered' ) }
                                >
                                    <ColorPalette
                                        value={ settings.slices[i].hoverBackgroundColor }
                                        onChange={ e => {
                                            settings.slices[i].hoverBackgroundColor = e;
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

export default SlicesSettingsChartJS;
