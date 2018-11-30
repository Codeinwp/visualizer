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

class HorizontalAxisSettings extends Component {
	constructor() {
		super( ...arguments );
	}

	render() {

		const type = this.props.chart['visualizer-chart-type'];

		const settings = this.props.chart['visualizer-settings'];

		return (
			<PanelBody
				title={ __( 'Horizontal Axis Settings' ) }
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
						value={ settings.hAxis.title }
						onChange={ e => {
							settings.hAxis.title = e;
							this.props.edit( settings );
						} }
					/>

					<SelectControl
						label={ __( 'Text Position' ) }
						help={ __( 'Position of the horizontal axis text, relative to the chart area.' ) }
						value={ settings.hAxis.textPosition ? settings.hAxis.textPosition : 'out' }
						options={ [
							{ label: __( 'Inside the chart' ), value: 'in' },
							{ label: __( 'Outside the chart' ), value: 'out' },
							{ label: __( 'None' ), value: 'none' }
						] }
						onChange={ e => {
							settings.hAxis.textPosition = e;
							this.props.edit( settings );
						} }
					/>

					<SelectControl
						label={ __( 'Direction' ) }
						help={ __( 'The direction in which the values along the horizontal axis grow.' ) }
						value={ settings.hAxis.direction ? settings.hAxis.direction : '1' }
						options={ [
							{ label: __( 'Identical Direction' ), value: '1' },
							{ label: __( 'Reverse Direction' ), value: '-1' }
						] }
						onChange={ e => {
							settings.hAxis.direction = e;
							this.props.edit( settings );
						} }
					/>

					<BaseControl
						label={ __( 'Base Line Color' ) }
					>
						<ColorPalette
							value={ settings.hAxis.baselineColor }
							onChange={ e => {
								settings.hAxis.baselineColor = e;
								this.props.edit( settings );
							} }
						/>
					</BaseControl>

					<BaseControl
						label={ __( 'Axis Text Color' ) }
					>
						<ColorPalette
							value={ settings.hAxis.textStyle.color || settings.hAxis.textStyle }
							onChange={ e => {
								settings.hAxis.textStyle = {};
								settings.hAxis.textStyle.color = e;
								this.props.edit( settings );
							} }
						/>
					</BaseControl>

					{ ( -1 >= [ 'column' ].indexOf( type ) ) && (
						<Fragment>
							<TextControl
								label={ __( 'Number Format' ) }
								help={ __( 'Enter custom format pattern to apply to horizontal axis labels.' ) }
								value={ settings.hAxis.format }
								onChange={ e => {
									settings.hAxis.format = e;
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
							title={ __( 'Grid Lines' ) }
							className="visualizer-inner-sections"
							initialOpen={ false }
						>

							<TextControl
								label={ __( 'Count' ) }
								help={ __( 'The number of horizontal gridlines inside the chart area. Minimum value is 2. Specify -1 to automatically compute the number of gridlines.' ) }
								value={ settings.hAxis.gridlines.count }
								onChange={ e => {
									settings.hAxis.gridlines.count = e;
									this.props.edit( settings );
								} }
							/>

							<BaseControl
								label={ __( 'Color' ) }
							>
								<ColorPalette
									value={ settings.hAxis.gridlines.color }
									onChange={ e => {
										settings.hAxis.gridlines.color = e;
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
								help={ __( 'The number of horizontal minor gridlines between two regular gridlines.' ) }
								value={ settings.hAxis.minorGridlines.count }
								onChange={ e => {
									settings.hAxis.minorGridlines.count = e;
									this.props.edit( settings );
								} }
							/>

							<BaseControl
								label={ __( 'Color' ) }
							>
								<ColorPalette
									value={ settings.hAxis.minorGridlines.color }
									onChange={ e => {
										settings.hAxis.minorGridlines.color = e;
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
								value={ settings.hAxis.viewWindow.max }
								onChange={ e => {
									settings.hAxis.viewWindow.max = e;
									this.props.edit( settings );
								} }
							/>

							<TextControl
								label={ __( 'Minimum Value' ) }
								help={ __( 'The minimum vertical data value to render.' ) }
								value={ settings.hAxis.viewWindow.min }
								onChange={ e => {
									settings.hAxis.viewWindow.min = e;
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

export default HorizontalAxisSettings;
