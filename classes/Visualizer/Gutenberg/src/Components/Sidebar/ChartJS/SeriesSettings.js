/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;

const {
	Component,
	Fragment
} = wp.element;

const { ColorPalette } = wp.blockEditor || wp.editor;

const {
	BaseControl,
	ExternalLink,
	PanelBody,
	SelectControl,
	TextControl
} = wp.components;

class SeriesSettingsChartJS extends Component {
	constructor() {
		super( ...arguments );
	}

	componentDidMount() {

		/**
		 * We use deep-clean to remove all empty properties which causes a major issue with Series Settings.
		 * So add a dummy property to make sure `series` object isn't empty.
		 * Should be removed before saving the data.
		 */
		const settings = this.props.chart['visualizer-settings'];

		Object.keys( settings.series )
			.map( i => {
				if ( settings.series[i] !== undefined ) {
					settings.series[i].temp = 1;
				}
			}
			);

		this.props.edit( settings );
	}

	render() {

		const type = this.props.chart['visualizer-chart-type'];

		const settings = this.props.chart['visualizer-settings'];

		const series = this.props.chart['visualizer-series'];

		return (
			<PanelBody
				title={ __( 'Series Settings' ) }
				initialOpen={ false }
				className="visualizer-advanced-panel"
			>

				{ Object.keys( settings.series )
					.map( ( i, index ) => {
						i++;
						return (
							<PanelBody
								title={ series[i].label }
								className="visualizer-inner-sections"
								initialOpen={ false }
							>

                                <Fragment>

                                    <TextControl
                                        label={ __( 'Format' ) }
                                        help={ __( 'Enter custom format pattern to apply to this series value.' ) }
                                        value={ settings.series[index].format }
                                        onChange={ e => {
                                            settings.series[index].format = e;
                                            this.props.edit( settings );
                                        } }
                                    />

                                    <p>
                                        { __( 'For number axis labels, this is a subset of the formatting ' ) }
                                        <ExternalLink href="http://icu-project.org/apiref/icu4c/classDecimalFormat.html#_details">
                                            { __( 'ICU pattern set.' ) }
                                        </ExternalLink>
                                        { __( ' For instance, $#,###.## will display values $1,234.56 for value 1234.56. Pay attention that if you use #%% percentage format then your values will be multiplied by 100.' ) }
                                    </p>

                                </Fragment>

                            { ( 0 <= [ 'line', 'radar' ].indexOf( type ) ) && (
                                <Fragment>
                                    <BaseControl
                                        label={ __( 'Point stroke Color' ) }
                                    >
                                        <ColorPalette
                                            value={ settings.series[index].borderColor }
                                            onChange={ e => {
                                                settings.series[index].borderColor = e;
                                                this.props.edit( settings );
                                            } }
                                        />
                                    </BaseControl>

                                    <BaseControl
                                        label={ __( 'Point fill Color' ) }
                                    >
                                        <ColorPalette
                                            value={ settings.series[index].backgroundColor }
                                            onChange={ e => {
                                                settings.series[index].backgroundColor = e;
                                                this.props.edit( settings );
                                            } }
                                        />
                                    </BaseControl>

                                    <TextControl
                                        label={ __( 'Point stroke width' ) }
                                        value={ settings.series[index].borderWidth }
                                        type="number"
                                        min="0"
                                        onChange={ e => {
                                            settings.series[index].borderWidth = e;
                                            this.props.edit( settings );
                                        } }
                                    />

                                    <SelectControl
                                        label={ __( 'Point style' ) }
                                        value={ settings.series[index].pointStyle }
                                        options={ [
                                            { label: __( 'Circle' ), value: 'circle' },
                                            { label: __( 'Cross' ), value: 'cross' },
                                            { label: __( 'Dash' ), value: 'dash' },
                                            { label: __( 'Line' ), value: 'line' },
                                            { label: __( 'Rectangle' ), value: 'rect' },
                                            { label: __( 'Star' ), value: 'star' },
                                            { label: __( 'Triangle' ), value: 'triangle' }
                                        ] }
                                        onChange={ e => {
                                            settings.series[index].pointStyle = e;
                                            this.props.edit( settings );
                                        } }
                                    />
                                </Fragment>
                            ) }


                            { ( 0 <= [ 'column', 'bar' ].indexOf( type ) ) && (
                                <Fragment>
                                    <BaseControl
                                        label={ __( 'Bar border color' ) }
                                    >
                                        <ColorPalette
                                            value={ settings.series[index].borderColor }
                                            onChange={ e => {
                                                settings.series[index].borderColor = e;
                                                this.props.edit( settings );
                                            } }
                                        />
                                    </BaseControl>

                                    <BaseControl
                                        label={ __( 'Bar background color' ) }
                                    >
                                        <ColorPalette
                                            value={ settings.series[index].backgroundColor }
                                            onChange={ e => {
                                                settings.series[index].backgroundColor = e;
                                                this.props.edit( settings );
                                            } }
                                        />
                                    </BaseControl>

                                    <BaseControl
                                        label={ __( 'Bar background hover color' ) }
                                    >
                                        <ColorPalette
                                            value={ settings.series[index].hoverBackgroundColor }
                                            onChange={ e => {
                                                settings.series[index].hoverBackgroundColor = e;
                                                this.props.edit( settings );
                                            } }
                                        />
                                    </BaseControl>

                                    <TextControl
                                        label={ __( 'Bar border width' ) }
                                        value={ settings.series[index].borderWidth }
                                        type="number"
                                        min="0"
                                        onChange={ e => {
                                            settings.series[index].borderWidth = e;
                                            this.props.edit( settings );
                                        } }
                                    />
                                </Fragment>
                            ) }


							</PanelBody>
						);
					}
                ) }

			</PanelBody>
		);
	}
}

export default SeriesSettingsChartJS;
