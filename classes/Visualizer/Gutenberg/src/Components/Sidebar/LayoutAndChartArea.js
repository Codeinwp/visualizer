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
	CheckboxControl,
	PanelBody,
	SelectControl,
	TextControl
} = wp.components;

class LayoutAndChartArea extends Component {
	constructor() {
		super( ...arguments );
	}

	render() {

		const type = this.props.chart['visualizer-chart-type'];

		const settings = this.props.chart['visualizer-settings'];

		return (
			<PanelBody
				title={ __( 'Layout And Chart Area' ) }
				initialOpen={ false }
				className="visualizer-advanced-panel"
			>

				<PanelBody
					title={ __( 'Layout' ) }
					className="visualizer-inner-sections"
					initialOpen={ false }
				>

					<TextControl
						label={ __( 'Width of Chart' ) }
						help={ __( 'Determines the total width of the chart.' ) }
						value={ settings.width }
						onChange={ e => {
							settings.width = e;
							this.props.edit( settings );
						} }
					/>

					<TextControl
						label={ __( 'Height of Chart' ) }
						help={ __( 'Determines the total height of the chart.' ) }
						value={ settings.height }
						onChange={ e => {
							settings.height = e;
							this.props.edit( settings );
						} }
					/>

					{ ( 0 <= [ 'geo' ].indexOf( type ) ) && (

						<SelectControl
							label={ __( 'Keep Aspect Ratio' ) }
							help={ __( 'If yes, the map will be drawn at the largest size that can fit inside the chart area at its natural aspect ratio. If only one of the width and height options is specified, the other one will be calculated according to the aspect ratio. If no, the map will be stretched to the exact size of the chart as specified by the width and height options.' ) }
							value={ settings.keepAspectRatio ? settings.isStacked : '1' }
							options={ [
								{ label: __( 'Yes' ), value: '1' },
								{ label: __( 'No' ), value: '0' }
							] }
							onChange={ e => {
								settings.keepAspectRatio = e;
								this.props.edit( settings );
							} }
						/>

					) }

					{ ( -1 >= [ 'gauge' ].indexOf( type ) ) && (

						<Fragment>

							<TextControl
								label={ __( 'Stroke Width' ) }
								help={ __( 'The chart border width in pixels.' ) }
								value={ settings.backgroundColor.strokeWidth }
								onChange={ e => {
									settings.backgroundColor.strokeWidth = e;
									this.props.edit( settings );
								} }
							/>

							<BaseControl
								label={ __( 'Stroke Color' ) }
							>
								<ColorPalette
									value={ settings.backgroundColor.stroke }
									onChange={ e => {
										settings.backgroundColor.stroke = e;
										this.props.edit( settings );
									} }
								/>
							</BaseControl>

							<BaseControl
								label={ __( 'Background Color' ) }
							>
								<ColorPalette
									value={ settings.backgroundColor.fill }
									onChange={ e => {
										settings.backgroundColor.fill = e;
										this.props.edit( settings );
									} }
								/>
							</BaseControl>

							<CheckboxControl
								label={ __( 'Transparent Background?' ) }
								checked={ 'transparent' === settings.backgroundColor.fill }
								onChange={ e => {
									settings.backgroundColor.fill = ( 'transparent' === settings.backgroundColor.fill ? '' : 'transparent' );
									this.props.edit( settings );
								} }
							/>

						</Fragment>

					) }

				</PanelBody>

				{ ( -1 >= [ 'geo', 'gauge' ].indexOf( type ) ) && (

					<PanelBody
						title={ __( 'Chart Area' ) }
						className="visualizer-inner-sections"
						initialOpen={ false }
					>

						<TextControl
							label={ __( 'Left Margin' ) }
							help={ __( 'Determines how far to draw the chart from the left border.' ) }
							value={ settings.chartArea.left }
							onChange={ e => {
								settings.chartArea.left = e;
								this.props.edit( settings );
							} }
						/>

						<TextControl
							label={ __( 'Top Margin' ) }
							help={ __( 'Determines how far to draw the chart from the top border.' ) }
							value={ settings.chartArea.top }
							onChange={ e => {
								settings.chartArea.top = e;
								this.props.edit( settings );
							} }
						/>

						<TextControl
							label={ __( 'Width Of Chart Area' ) }
							help={ __( 'Determines the width of the chart area.' ) }
							value={ settings.chartArea.width }
							onChange={ e => {
								settings.chartArea.width = e;
								this.props.edit( settings );
							} }
						/>

						<TextControl
							label={ __( 'Height Of Chart Area' ) }
							help={ __( 'Determines the hight of the chart area.' ) }
							value={ settings.chartArea.height }
							onChange={ e => {
								settings.chartArea.height = e;
								this.props.edit( settings );
							} }
						/>

					</PanelBody>

				) }

			</PanelBody>
		);
	}
}

export default LayoutAndChartArea;
