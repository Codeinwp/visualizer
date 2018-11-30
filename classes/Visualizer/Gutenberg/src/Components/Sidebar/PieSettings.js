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
	SelectControl,
	TextControl
} = wp.components;

class PieSettings extends Component {
	constructor() {
		super( ...arguments );
	}

	render() {

		const settings = this.props.chart['visualizer-settings'];

		return (
			<PanelBody
				title={ __( 'Pie Settings' ) }
				initialOpen={ false }
				className="visualizer-advanced-panel"
			>

				<Fragment>

					<TextControl
						label={ __( 'Number Format' ) }
						help={ __( 'Enter custom format pattern to apply to chart labels.' ) }
						value={ settings.format }
						onChange={ e => {
							settings.format = e;
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

				<SelectControl
					label={ __( 'Is 3D' ) }
					help={ __( 'If set to yes, displays a three-dimensional chart.' ) }
					value={ settings.is3D ? settings.is3D : '0' }
					options={ [
						{ label: __( 'Yes' ), value: '1' },
						{ label: __( 'No' ), value: '0' }
					] }
					onChange={ e => {
						settings.is3D = e;
						this.props.edit( settings );
					} }
				/>

				<SelectControl
					label={ __( 'Reverse Categories' ) }
					help={ __( 'If set to yes, will draw slices counterclockwise.' ) }
					value={ settings.reverseCategories ? settings.reverseCategories : '0' }
					options={ [
						{ label: __( 'Yes' ), value: '1' },
						{ label: __( 'No' ), value: '0' }
					] }
					onChange={ e => {
						settings.reverseCategories = e;
						this.props.edit( settings );
					} }
				/>

				<SelectControl
					label={ __( 'Slice Text' ) }
					help={ __( 'The content of the text displayed on the slice.' ) }
					value={ settings.pieSliceText ? settings.pieSliceText : 'percentage' }
					options={ [
						{ label: __( 'The percentage of the slice size out of the total' ), value: 'percentage' },
						{ label: __( 'The quantitative value of the slice' ), value: 'value' },
						{ label: __( 'The name of the slice' ), value: 'label' },
						{ label: __( 'The quantitative value and percentage of the slice' ), value: 'value-and-percentage' },
						{ label: __( 'No text is displayed' ), value: 'none' }
					] }
					onChange={ e => {
						settings.pieSliceText = e;
						this.props.edit( settings );
					} }
				/>

				<TextControl
					label={ __( 'Pie Hole' ) }
					help={ __( 'If between 0 and 1, displays a donut chart. The hole with have a radius equal to number times the radius of the chart. Only applicable when the chart is two-dimensional.' ) }
					placeholder={ __( '0.5' ) }
					value={ settings.pieHole }
					onChange={ e => {
						settings.pieHole = e;
						this.props.edit( settings );
					} }
				/>

				<TextControl
					label={ __( 'Start Angle' ) }
					help={ __( 'The angle, in degrees, to rotate the chart by. The default of 0 will orient the leftmost edge of the first slice directly up.' ) }
					value={ settings.pieStartAngle }
					onChange={ e => {
						settings.pieStartAngle = e;
						this.props.edit( settings );
					} }
				/>

				<BaseControl
					label={ __( 'Slice Border Color' ) }
				>
					<ColorPalette
						value={ settings.pieSliceBorderColor }
						onChange={ e => {
							settings.pieSliceBorderColor = e;
							this.props.edit( settings );
						} }
					/>
				</BaseControl>

			</PanelBody>
		);
	}
}

export default PieSettings;
