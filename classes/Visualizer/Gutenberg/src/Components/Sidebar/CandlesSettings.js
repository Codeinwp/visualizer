/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;

const { Component } = wp.element;

const { ColorPalette } = wp.editor;

const {
	BaseControl,
	PanelBody,
	SelectControl,
	TextControl
} = wp.components;

class CandlesSettings extends Component {
	constructor() {
		super( ...arguments );
	}

	render() {

		const settings = this.props.chart['visualizer-settings'];

		return (
			<PanelBody
				title={ __( 'Candles Settings' ) }
				initialOpen={ false }
				className="visualizer-advanced-panel"
			>

				<PanelBody
					title={ __( 'General Settings' ) }
					className="visualizer-inner-sections"
					initialOpen={ false }
				>

					<SelectControl
						label={ __( 'Focus Target' ) }
						help={ __( 'The type of the entity that receives focus on mouse hover. Also affects which entity is selected by mouse click.' ) }
						value={ settings.focusTarget ? settings.focusTarget : 'datum' }
						options={ [
							{ label: __( 'Focus on a single data point.' ), value: 'datum' },
							{ label: __( 'Focus on a grouping of all data points along the major axis.' ), value: 'category' }
						] }
						onChange={ e => {
							settings.focusTarget = e;
							this.props.edit( settings );
						} }
					/>

					<SelectControl
						label={ __( 'Selection Mode' ) }
						help={ __( 'Determines how many data points an user can select on a chart.' ) }
						value={ settings.selectionMode ? settings.selectionMode : 'single' }
						options={ [
							{ label: __( 'Single data point' ), value: 'single' },
							{ label: __( 'Multiple data points' ), value: 'multiple' }
						] }
						onChange={ e => {
							settings.selectionMode = e;
							this.props.edit( settings );
						} }
					/>

					<SelectControl
						label={ __( 'Aggregation Target' ) }
						help={ __( 'Determines how multiple data selections are rolled up into tooltips. To make it working you need to set multiple selection mode and tooltip trigger to display it when an user selects an element.' ) }
						value={ settings.aggregationTarget ? settings.aggregationTarget : 'auto' }
						options={ [
							{ label: __( 'Group selected data by x-value' ), value: 'category' },
							{ label: __( 'Group selected data by series' ), value: 'series' },
							{ label: __( 'Group selected data by x-value if all selections have the same x-value, and by series otherwise' ), value: 'auto' },
							{ label: __( 'Show only one tooltip per selection' ), value: 'none' }
						] }
						onChange={ e => {
							settings.aggregationTarget = e;
							this.props.edit( settings );
						} }
					/>

				</PanelBody>

				<PanelBody
					title={ __( 'Failing Candles' ) }
					className="visualizer-inner-sections"
					initialOpen={ false }
				>

					<TextControl
						label={ __( 'Stroke Width' ) }
						help={ __( 'The stroke width of falling candles.' ) }
						value={ settings.candlestick.fallingColor.strokeWidth }
						onChange={ e => {
							settings.candlestick.fallingColor.strokeWidth = e;
							this.props.edit( settings );
						} }
					/>

					<BaseControl
						label={ __( 'Stroke Color' ) }
					>
						<ColorPalette
							value={ settings.candlestick.fallingColor.stroke }
							onChange={ e => {
								settings.candlestick.fallingColor.stroke = e;
								this.props.edit( settings );
							} }
						/>
					</BaseControl>

					<BaseControl
						label={ __( 'Fill Color' ) }
					>
						<ColorPalette
							value={ settings.candlestick.fallingColor.fill }
							onChange={ e => {
								settings.candlestick.fallingColor.fill = e;
								this.props.edit( settings );
							} }
						/>
					</BaseControl>

				</PanelBody>

				<PanelBody
					title={ __( 'Rising Candles' ) }
					className="visualizer-inner-sections"
					initialOpen={ false }
				>

					<TextControl
						label={ __( 'Stroke Width' ) }
						help={ __( 'The stroke width of rising candles.' ) }
						value={ settings.candlestick.risingColor.strokeWidth }
						onChange={ e => {
							settings.candlestick.risingColor.strokeWidth = e;
							this.props.edit( settings );
						} }
					/>

					<BaseControl
						label={ __( 'Stroke Color' ) }
					>
						<ColorPalette
							value={ settings.candlestick.risingColor.stroke }
							onChange={ e => {
								settings.candlestick.risingColor.stroke = e;
								this.props.edit( settings );
							} }
						/>
					</BaseControl>

					<BaseControl
						label={ __( 'Fill Color' ) }
					>
						<ColorPalette
							value={ settings.candlestick.risingColor.fill }
							onChange={ e => {
								settings.candlestick.risingColor.fill = e;
								this.props.edit( settings );
							} }
						/>
					</BaseControl>

				</PanelBody>

			</PanelBody>
		);
	}
}

export default CandlesSettings;
