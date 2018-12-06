/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;

const {
	Component,
	Fragment
} = wp.element;

const {
	ExternalLink,
	PanelBody,
	TextControl
} = wp.components;

class SizeAxis extends Component {
	constructor() {
		super( ...arguments );
	}

	render() {

		const settings = this.props.chart['visualizer-settings'];

		return (
			<PanelBody
				title={ __( 'Size Axis' ) }
				initialOpen={ false }
				className="visualizer-advanced-panel"
			>

				<TextControl
					label={ __( 'Minimum Values' ) }
					help={ __( 'Determines the minimum values of size axis.' ) }
					value={ settings.sizeAxis.minValue }
					onChange={ e => {
						settings.sizeAxis.minValue = e;
						this.props.edit( settings );
					} }
				/>

				<TextControl
					label={ __( 'Maximum Values' ) }
					help={ __( 'Determines the maximum values of size axis.' ) }
					value={ settings.sizeAxis.maxValue }
					onChange={ e => {
						settings.sizeAxis.maxValue = e;
						this.props.edit( settings );
					} }
				/>

				<TextControl
					label={ __( 'Minimum Marker Radius' ) }
					help={ __( 'Determines the radius of the smallest possible bubbles, in pixels.' ) }
					value={ settings.sizeAxis.minSize }
					onChange={ e => {
						settings.sizeAxis.minSize = e;
						this.props.edit( settings );
					} }
				/>

				<TextControl
					label={ __( 'Maximum Marker Radius' ) }
					help={ __( 'Determines the radius of the largest possible bubbles, in pixels.' ) }
					value={ settings.sizeAxis.maxSize }
					onChange={ e => {
						settings.sizeAxis.maxSize = e;
						this.props.edit( settings );
					} }
				/>

				<TextControl
					label={ __( 'Marker Opacity' ) }
					help={ __( 'The opacity of the markers, where 0.0 is fully transparent and 1.0 is fully opaque.' ) }
					value={ settings.markerOpacity }
					onChange={ e => {
						settings.markerOpacity = e;
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
		);
	}
}

export default SizeAxis;
