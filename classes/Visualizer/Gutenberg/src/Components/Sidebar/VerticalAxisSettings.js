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

class VerticalAxisSettings extends Component {
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
						help={ __( 'The title of the Vertical axis.' ) }
						value={ settings.vAxis.title }
						onChange={ e => {
							settings.vAxis.title = e;
							this.props.edit( settings );
						} }
					/>

					<SelectControl
						label={ __( 'Text Position' ) }
						help={ __( 'Position of the Vertical axis text, relative to the chart area.' ) }
						value={ settings.vAxis.textPosition ? settings.vAxis.textPosition : 'out' }
						options={ [
							{ label: __( 'Inside the chart' ), value: 'in' },
							{ label: __( 'Outside the chart' ), value: 'out' },
							{ label: __( 'None' ), value: 'none' }
						] }
						onChange={ e => {
							settings.vAxis.textPosition = e;
							this.props.edit( settings );
						} }
					/>

					<SelectControl
						label={ __( 'Direction' ) }
						help={ __( 'The direction in which the values along the Vertical axis grow.' ) }
						value={ settings.vAxis.direction ? settings.vAxis.direction : '1' }
						options={ [
							{ label: __( 'Identical Direction' ), value: '1' },
							{ label: __( 'Reverse Direction' ), value: '-1' }
						] }
						onChange={ e => {
							settings.vAxis.direction = e;
							this.props.edit( settings );
						} }
					/>

					<BaseControl
						label={ __( 'Base Line Color' ) }
					>
						<ColorPalette
							value={ settings.vAxis.baselineColor }
							onChange={ e => {
								settings.vAxis.baselineColor = e;
								this.props.edit( settings );
							} }
						/>
					</BaseControl>

					<BaseControl
						label={ __( 'Axis Text Color' ) }
					>
						<ColorPalette
							value={ settings.vAxis.textStyle.color || settings.vAxis.textStyle }
							onChange={ e => {
								settings.vAxis.textStyle = {};
								settings.vAxis.textStyle.color = e;
								this.props.edit( settings );
							} }
						/>
					</BaseControl>

					{ ( -1 >= [ 'column' ].indexOf( type ) ) && (
						<Fragment>

							<TextControl
								label={ __( 'Number Format' ) }
								help={ __( 'Enter custom format pattern to apply to Vertical axis labels.' ) }
								value={ settings.vAxis.format }
								onChange={ e => {
									settings.vAxis.format = e;
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

				{ ( -1 >= [ 'bar' ].indexOf( type ) ) && (

					<Fragment>

						<PanelBody
							title={ __( 'Grid Lines' ) }
							className="visualizer-inner-sections"
							initialOpen={ false }
						>

							<TextControl
								label={ __( 'Count' ) }
								help={ __( 'The number of Vertical gridlines inside the chart area. Minimum value is 2. Specify -1 to automatically compute the number of gridlines.' ) }
								value={ settings.vAxis.gridlines.count }
								onChange={ e => {
									settings.vAxis.gridlines.count = e;
									this.props.edit( settings );
								} }
							/>

							<BaseControl
								label={ __( 'Color' ) }
							>
								<ColorPalette
									value={ settings.vAxis.gridlines.color }
									onChange={ e => {
										settings.vAxis.gridlines.color = e;
										this.props.edit( settings );
									} }
								/>
							</BaseControl>

						</PanelBody>

						<PanelBody
							title={ __( 'Minor Grid Lines' ) }
							className="visualizer-inner-sections"
							initialOpen={ false }
						>

							<TextControl
								label={ __( 'Count' ) }
								help={ __( 'The number of Vertical minor gridlines between two regular gridlines.' ) }
								value={ settings.vAxis.minorGridlines.count }
								onChange={ e => {
									settings.vAxis.minorGridlines.count = e;
									this.props.edit( settings );
								} }
							/>

							<BaseControl
								label={ __( 'Color' ) }
							>
								<ColorPalette
									value={ settings.vAxis.minorGridlines.color }
									onChange={ e => {
										settings.vAxis.minorGridlines.color = e;
										this.props.edit( settings );
									} }
								/>
							</BaseControl>

						</PanelBody>

						<PanelBody
							title={ __( 'View Window' ) }
							className="visualizer-inner-sections"
							initialOpen={ false }
						>

							<TextControl
								label={ __( 'Maximun Value' ) }
								help={ __( 'The maximum vertical data value to render.' ) }
								value={ settings.vAxis.viewWindow.max }
								onChange={ e => {
									settings.vAxis.viewWindow.max = e;
									this.props.edit( settings );
								} }
							/>

							<TextControl
								label={ __( 'Minimum Value' ) }
								help={ __( 'The minimum vertical data value to render.' ) }
								value={ settings.vAxis.viewWindow.min }
								onChange={ e => {
									settings.vAxis.viewWindow.min = e;
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

export default VerticalAxisSettings;
