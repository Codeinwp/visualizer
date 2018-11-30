/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;

const { Component } = wp.element;

const { ColorPalette } = wp.editor;

const {
	BaseControl,
	CheckboxControl,
	PanelBody,
	SelectControl,
	TextControl
} = wp.components;

class GeneralSettings extends Component {
	constructor() {
		super( ...arguments );
	}

	render() {

		const type = this.props.chart['visualizer-chart-type'];

		const settings = this.props.chart['visualizer-settings'];

		const tooltipTriggers = [ { label: __( 'The tooltip will be displayed when the user hovers over an element' ), value: 'focus' } ];

		if ( -1 >= [ 'timeline' ].indexOf( type ) ) {
			tooltipTriggers[1] = { label: __( 'The tooltip will be displayed when the user selects an element' ), value: 'selection' };
		}

		tooltipTriggers[2] = { label: __( 'The tooltip will not be displayed' ), value: 'none' };

		return (
			<PanelBody
				title={ __( 'General Settings' ) }
				initialOpen={ false }
				className="visualizer-advanced-panel"
			>

				<PanelBody
					title={ __( 'Title' ) }
					className="visualizer-inner-sections"
					initialOpen={ false }
				>

					<TextControl
						label={ __( 'Chart Title' ) }
						help={ __( 'Text to display above the chart.' ) }
						value={ settings.title }
						onChange={ e => {
							settings.title = e;
							this.props.edit( settings );
						} }
					/>

					{ ( -1 >= [ 'table', 'gauge', 'geo', 'pie', 'timeline' ].indexOf( type ) ) && (
						<SelectControl
							label={ __( 'Chart Title Position' ) }
							help={ __( 'Where to place the chart title, compared to the chart area.' ) }
							value={ settings.titlePosition ? settings.titlePosition : 'out' }
							options={ [
								{ label: __( 'Inside the chart' ), value: 'in' },
								{ label: __( 'Outside the chart' ), value: 'out' },
								{ label: __( 'None' ), value: 'none' }
							] }
							onChange={ e => {
								settings.titlePosition = e;
								this.props.edit( settings );
							} }
						/>
					) }

					{ ( -1 >= [ 'table', 'gauge', 'geo', 'timeline' ].indexOf( type ) ) && (
						<BaseControl
							label={ __( 'Chart Title Color' ) }
						>
							<ColorPalette
								value={ settings.titleTextStyle.color }
								onChange={ e => {
									settings.titleTextStyle.color = e;
									this.props.edit( settings );
								} }
							/>
						</BaseControl>
					) }

					{ ( -1 >= [ 'table', 'gauge', 'geo', 'pie', 'timeline' ].indexOf( type ) ) && (
						<SelectControl
							label={ __( 'Axes Titles Position' ) }
							help={ __( 'Determines where to place the axis titles, compared to the chart area.' ) }
							value={ settings.axisTitlesPosition ? settings.axisTitlesPosition : 'out' }
							options={ [
								{ label: __( 'Inside the chart' ), value: 'in' },
								{ label: __( 'Outside the chart' ), value: 'out' },
								{ label: __( 'None' ), value: 'none' }
							] }
							onChange={ e => {
								settings.axisTitlesPosition = e;
								this.props.edit( settings );
							} }
						/>
					) }

				</PanelBody>

				{ ( -1 >= [ 'table', 'gauge', 'geo', 'pie', 'timeline' ].indexOf( type ) ) && (
					<PanelBody
						title={ __( 'Font Styles' ) }
						className="visualizer-inner-sections"
						initialOpen={ false }
					>

						<SelectControl
							label={ __( 'Font Family' ) }
							help={ __( 'The default font family for all text in the chart.' ) }
							value={ settings.fontName ? settings.fontName : 'Arial' }
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
								settings.fontName = e;
								this.props.edit( settings );
							} }
						/>

						<SelectControl
							label={ __( 'Font Size' ) }
							help={ __( 'The default font size for all text in the chart.' ) }
							value={ settings.fontSize ? settings.fontSize : '15' }
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
								settings.fontSize = e;
								this.props.edit( settings );
							} }
						/>

					</PanelBody>
				) }

				{ ( -1 >= [ 'table', 'gauge', 'geo', 'timeline' ].indexOf( type ) ) && (
					<PanelBody
						title={ __( 'Legend' ) }
						className="visualizer-inner-sections"
						initialOpen={ false }
					>

						<SelectControl
							label={ __( 'Position' ) }
							help={ __( 'Determines where to place the legend, compared to the chart area.' ) }
							value={ settings.legend.position ? settings.legend.position : 'right' }
							options={ [
								{ label: __( 'Left of the chart' ), value: 'left' },
								{ label: __( 'Right of the chart' ), value: 'right' },
								{ label: __( 'Above the chart' ), value: 'top' },
								{ label: __( 'Below the chart' ), value: 'bottom' },
								{ label: __( 'Inside the chart' ), value: 'in' },
								{ label: __( 'Omit the legend' ), value: 'none' }
							] }
							onChange={ e => {
								if ( 'pie' !== type ) {
									let axis = 'left' === e ? 1 : 0;

									Object.keys( settings.series ).map( i => {
										settings.series[i].targetAxisIndex = axis;
									});
								}

								settings.legend.position = e;
								this.props.edit( settings );
							} }
						/>

						<SelectControl
							label={ __( 'Alignment' ) }
							help={ __( 'Determines the alignment of the legend.' ) }
							value={ settings.legend.alignment ? settings.legend.alignment : '15' }
							options={ [
								{ label: __( 'Aligned to the start of the allocated area' ), value: 'start' },
								{ label: __( 'Centered in the allocated area' ), value: 'center' },
								{ label: __( 'Aligned to the end of the allocated area' ), value: 'end' }
							] }
							onChange={ e => {
								settings.legend.alignment = e;
								this.props.edit( settings );
							} }
						/>

						<BaseControl
							label={ __( 'Font Color' ) }
						>
							<ColorPalette
								value={ settings.legend.textStyle.color }
								onChange={ e => {
									settings.legend.textStyle.color = e;
									this.props.edit( settings );
								} }
							/>
						</BaseControl>

					</PanelBody>
				) }

				{ ( -1 >= [ 'table', 'gauge', 'geo' ].indexOf( type ) ) && (
					<PanelBody
						title={ __( 'Tooltip' ) }
						className="visualizer-inner-sections"
						initialOpen={ false }
					>

						<SelectControl
							label={ __( 'Trigger' ) }
							help={ __( 'Determines the user interaction that causes the tooltip to be displayed.' ) }
							value={ settings.tooltip.trigger ? settings.tooltip.trigger : 'focus' }
							options={ tooltipTriggers }
							onChange={ e => {
								settings.tooltip.trigger = e;
								this.props.edit( settings );
							} }
						/>

						<SelectControl
							label={ __( 'Show Color Code' ) }
							help={ __( 'If set to yes, will show colored squares next to the slice information in the tooltip.' ) }
							value={ settings.tooltip.showColorCode ? settings.tooltip.showColorCode : '0' }
							options={ [
								{ label: __( 'Yes' ), value: '1' },
								{ label: __( 'No' ), value: '0' }
							] }
							onChange={ e => {
								settings.tooltip.showColorCode = e;
								this.props.edit( settings );
							} }
						/>

						{ ( 0 <= [ 'pie' ].indexOf( type ) ) && (
							<SelectControl
								label={ __( 'Text' ) }
								help={ __( 'Determines what information to display when the user hovers over a pie slice.' ) }
								value={ settings.tooltip.text ? settings.tooltip.text : 'both' }
								options={ [
									{ label: __( 'Display both the absolute value of the slice and the percentage of the whole' ), value: 'both' },
									{ label: __( 'Display only the absolute value of the slice' ), value: 'value' },
									{ label: __( 'Display only the percentage of the whole represented by the slice' ), value: 'percentage' }
								] }
								onChange={ e => {
									settings.tooltip.text = e;
									this.props.edit( settings );
								} }
							/>
						) }

					</PanelBody>
				) }

				{ ( -1 >= [ 'table', 'gauge', 'geo', 'pie', 'timeline' ].indexOf( type ) ) && (
					<PanelBody
						title={ __( 'Animation' ) }
						className="visualizer-inner-sections"
						initialOpen={ false }
					>

						<CheckboxControl
							label={ __( 'Animate on startup?' ) }
							help={ __( 'Determines if the chart will animate on the initial draw.' ) }
							checked={ Number( settings.animation.startup ) }
							onChange={ e => {
								settings.animation.startup = e ? '1' : '0';
								this.props.edit( settings );
							} }
						/>

						<TextControl
							label={ __( 'Duration' ) }
							help={ __( 'The duration of the animation, in milliseconds.' ) }
							type="number"
							value={ settings.animation.duration }
							onChange={ e => {
								settings.animation.duration = e;
								this.props.edit( settings );
							} }
						/>

						<SelectControl
							label={ __( 'Easing' ) }
							help={ __( 'The easing function applied to the animation.' ) }
							value={ settings.animation.easing ? settings.animation.easing : 'linear' }
							options={ [
								{ label: __( 'Constant speed' ), value: 'linear' },
								{ label: __( 'Start slow and speed up' ), value: 'in' },
								{ label: __( 'Start fast and slow down' ), value: 'out' },
								{ label: __( 'Start slow, speed up, then slow down' ), value: 'inAndOut' }
							] }
							onChange={ e => {
								settings.animation.easing = e;
								this.props.edit( settings );
							} }
						/>

					</PanelBody>
				) }

			</PanelBody>
		);
	}
}

export default GeneralSettings;
