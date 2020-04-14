/**
 * WordPress dependencies
 */

 /* eslint-disable camelcase */

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

class VerticalAxisSettingsChartJS extends Component {
	constructor() {
		super( ...arguments );
	}


	render() {
		const type = this.props.chart['visualizer-chart-type'];

		const settings = this.props.chart['visualizer-settings'];

		return (
			<PanelBody
				title={ __( 'Vertical Axis Settings' ) }
				initialOpen={ false }
				className="visualizer-advanced-panel"
			>

				<PanelBody
					title={ __( 'General Settings' ) }
					className="visualizer-inner-sections"
					initialOpen={ false }
				>

					<TextControl
						label={ __( 'Axis Title' ) }
						help={ __( 'The title of the horizontal axis.' ) }
						value={ settings.yAxes.scaleLabel.labelString }
						onChange={ e => {
							settings.yAxes.scaleLabel.labelString = e;
							this.props.edit( settings );
						} }
					/>

					<PanelBody
						title={ __( 'Font Styles' ) }
						className="visualizer-inner-sections"
						initialOpen={ false }
					>

						<SelectControl
							label={ __( 'Font Family' ) }
							help={ __( 'The default font family for all text in the chart.' ) }
							value={ settings.yAxes.scaleLabel.fontFamily ? settings.yAxes.scaleLabel.fontFamily : 'Arial' }
							options={ [
								{ label: __( 'Arial' ), value: 'Arial' },
								{ label: __( 'Sans Serif' ), value: 'Sans Serif' },
								{ label: __( 'Serif' ), value: 'serif' },
								{ label: __( 'Arial' ), value: 'Arial' },
								{ label: __( 'Wide' ), value: 'Arial black' },
								{ label: __( 'Narrow' ), value: 'Arial Narrow' },
								{ label: __( 'Comic Sans MS' ), value: 'Comic Sans MS' },
								{ label: __( 'Courier New' ), value: 'Courier New' },
								{ label: __( 'Garamond' ), value: 'Garamond' },
								{ label: __( 'Georgia' ), value: 'Georgia' },
								{ label: __( 'Tahoma' ), value: 'Tahoma' },
								{ label: __( 'Verdana' ), value: 'Verdana' }
							] }
							onChange={ e => {
								settings.yAxes.scaleLabel.fontFamily = e;
								this.props.edit( settings );
							} }
						/>

						<SelectControl
							label={ __( 'Font Size' ) }
							help={ __( 'The default font size for all text in the chart.' ) }
							value={ settings.yAxes.scaleLabel.fontSize ? settings.yAxes.scaleLabel.fontSize : '15' }
							options={ [
								{ label: '7', value: '7' },
								{ label: '8', value: '8' },
								{ label: '9', value: '9' },
								{ label: '10', value: '10' },
								{ label: '11', value: '11' },
								{ label: '12', value: '12' },
								{ label: '13', value: '13' },
								{ label: '14', value: '14' },
								{ label: '15', value: '15' },
								{ label: '16', value: '16' },
								{ label: '17', value: '17' },
								{ label: '18', value: '18' },
								{ label: '19', value: '19' },
								{ label: '20', value: '20' }
							] }
							onChange={ e => {
								settings.yAxes.scaleLabel.fontSize = e;
								this.props.edit( settings );
							} }
						/>

                        <BaseControl
                            label={ __( 'Font Color' ) }
                        >
                            <ColorPalette
                                value={ settings.yAxes.scaleLabel.fontColor }
                                onChange={ e => {
                                    settings.yAxes.scaleLabel.fontColor = e;
                                    this.props.edit( settings );
                                } }
                            />
                        </BaseControl>


					</PanelBody>

					{ ( -1 >= [ 'column' ].indexOf( type ) ) && (
						<Fragment>
							<TextControl
								label={ __( 'Number Format' ) }
								help={ __( 'Enter custom format pattern to apply to horizontal axis labels.' ) }
								value={ settings.yAxes_format }
								onChange={ e => {
									settings.yAxes_format = e;
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

							<p>
								{ __( 'For date axis labels, this is a subset of the date formatting ' ) }
								<ExternalLink href="http://userguide.icu-project.org/formatparse/datetime#TOC-Date-Time-Format-Syntax">
									{ __( 'ICU date and time format.' ) }
								</ExternalLink>
							</p>
						</Fragment>
					) }

				</PanelBody>

				{ ( -1 >= [ 'column' ].indexOf( type ) ) && (

					<Fragment>

						<PanelBody
							title={ __( 'Tick Settings' ) }
							className="visualizer-inner-sections"
							initialOpen={ false }
						>

							<TextControl
								label={ __( 'Mininum Tick' ) }
								help={ __( 'Adjustment used when calculating the minimum data value.' ) }
								value={ settings.yAxes.ticks.suggestedMin }
								onChange={ e => {
									settings.yAxes.ticks.suggestedMin = e;
									this.props.edit( settings );
								} }
							/>

							<TextControl
								label={ __( 'Maximum Tick' ) }
								help={ __( 'Adjustment used when calculating the maximum data value.' ) }
								value={ settings.yAxes.ticks.suggestedMax }
								onChange={ e => {
									settings.yAxes.ticks.suggestedMax = e;
									this.props.edit( settings );
								} }
							/>

							<TextControl
								label={ __( 'Maximum number of ticks to show' ) }
								help={ __( 'Maximum number of ticks and gridlines to show.' ) }
                                type="number"
								value={ settings.yAxes.ticks.maxTicksLimit }
								onChange={ e => {
									settings.yAxes.ticks.maxTicksLimit = e;
									this.props.edit( settings );
								} }
							/>

							<TextControl
								label={ __( 'Step size' ) }
								help={ __( 'User defined fixed step size for the scale.' ) }
                                type="number"
								value={ settings.yAxes.ticks.stepSize }
								onChange={ e => {
									settings.yAxes.ticks.stepSize = e;
									this.props.edit( settings );
								} }
							/>

						</PanelBody>

					</Fragment>

				)}

			</PanelBody>
		);
	}
}

export default VerticalAxisSettingsChartJS;
