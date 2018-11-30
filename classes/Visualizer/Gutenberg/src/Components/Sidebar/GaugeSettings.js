/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;

const {
	Component,
	Fragment
} = wp.element;

const { ColorPalette } = wp.editor;

const {
	BaseControl,
	ExternalLink,
	PanelBody,
	TextControl
} = wp.components;

class GaugeSettings extends Component {
	constructor() {
		super( ...arguments );
	}

	render() {

		const settings = this.props.chart['visualizer-settings'];

		return (
			<PanelBody
				title={ __( 'Gauge Settings' ) }
				initialOpen={ false }
				className="visualizer-advanced-panel"
			>

				<PanelBody
					title={ __( 'Tick Settings' ) }
					className="visualizer-inner-sections"
					initialOpen={ false }
				>

					<TextControl
						label={ __( 'Minimum Values' ) }
						help={ __( 'Determines the minimum values of gauge.' ) }
						value={ settings.min }
						onChange={ e => {
							settings.min = e;
							this.props.edit( settings );
						} }
					/>

					<TextControl
						label={ __( 'Maximum Values' ) }
						help={ __( 'Determines the maximum values of gauge.' ) }
						value={ settings.max }
						onChange={ e => {
							settings.max = e;
							this.props.edit( settings );
						} }
					/>

					<TextControl
						label={ __( 'Minor Ticks' ) }
						help={ __( 'The number of minor tick section in each major tick section.' ) }
						value={ settings.minorTicks }
						onChange={ e => {
							settings.minorTicks = e;
							this.props.edit( settings );
						} }
					/>

					<Fragment>

						<TextControl
							label={ __( 'Number Format' ) }
							help={ __( 'Enter custom format pattern to apply to this series value.' ) }
							value={ settings.series[0].format }
							onChange={ e => {
								settings.series[0].format = e;
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

				</PanelBody>

				<PanelBody
					title={ __( 'Green Color' ) }
					className="visualizer-inner-sections"
					initialOpen={ false }
				>

					<TextControl
						label={ __( 'Minimum Range' ) }
						help={ __( 'The lowest values for a range marked by a green color.' ) }
						value={ settings.greenFrom }
						onChange={ e => {
							settings.greenFrom = e;
							this.props.edit( settings );
						} }
					/>

					<TextControl
						label={ __( 'Maximum Range' ) }
						help={ __( 'The highest values for a range marked by a green color.' ) }
						value={ settings.greenTo }
						onChange={ e => {
							settings.greenTo = e;
							this.props.edit( settings );
						} }
					/>

					<BaseControl
						label={ __( 'Green Color' ) }
					>
						<ColorPalette
							value={ settings.greenColor }
							onChange={ e => {
								settings.greenColor = e;
								this.props.edit( settings );
							} }
						/>
					</BaseControl>

				</PanelBody>

				<PanelBody
					title={ __( 'Yellow Color' ) }
					className="visualizer-inner-sections"
					initialOpen={ false }
				>

					<TextControl
						label={ __( 'Minimum Range' ) }
						help={ __( 'The lowest values for a range marked by a yellow color.' ) }
						value={ settings.yellowFrom }
						onChange={ e => {
							settings.yellowFrom = e;
							this.props.edit( settings );
						} }
					/>

					<TextControl
						label={ __( 'Maximum Range' ) }
						help={ __( 'The highest values for a range marked by a yellow color.' ) }
						value={ settings.yellowTo }
						onChange={ e => {
							settings.yellowTo = e;
							this.props.edit( settings );
						} }
					/>

					<BaseControl
						label={ __( 'Yellow Color' ) }
					>
						<ColorPalette
							value={ settings.yellowColor }
							onChange={ e => {
								settings.yellowColor = e;
								this.props.edit( settings );
							} }
						/>
					</BaseControl>

				</PanelBody>

				<PanelBody
					title={ __( 'Red Color' ) }
					className="visualizer-inner-sections"
					initialOpen={ false }
				>

					<TextControl
						label={ __( 'Minimum Range' ) }
						help={ __( 'The lowest values for a range marked by a red color.' ) }
						value={ settings.redFrom }
						onChange={ e => {
							settings.redFrom = e;
							this.props.edit( settings );
						} }
					/>

					<TextControl
						label={ __( 'Maximum Range' ) }
						help={ __( 'The highest values for a range marked by a red color.' ) }
						value={ settings.redTo }
						onChange={ e => {
							settings.redTo = e;
							this.props.edit( settings );
						} }
					/>

					<BaseControl
						label={ __( 'Red Color' ) }
					>
						<ColorPalette
							value={ settings.redColor }
							onChange={ e => {
								settings.redColor = e;
								this.props.edit( settings );
							} }
						/>
					</BaseControl>

				</PanelBody>

			</PanelBody>
		);
	}
}

export default GaugeSettings;
