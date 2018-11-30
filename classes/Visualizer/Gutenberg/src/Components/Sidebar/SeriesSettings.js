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

class SeriesSettings extends Component {
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

								{ ( -1 >= [ 'table', 'pie' ].indexOf( type ) ) && (
									<SelectControl
										label={ __( 'Visible In Legend' ) }
										help={ __( 'Determines whether the series has to be presented in the legend or not.' ) }
										value={ settings.series[index].visibleInLegend ? settings.series[index].visibleInLegend : '1' }
										options={ [
											{ label: __( 'Yes' ), value: '1' },
											{ label: __( 'No' ), value: '0' }
										] }
										onChange={ e => {
											settings.series[index].visibleInLegend = e;
											this.props.edit( settings );
										} }
									/>
								) }

								{ ( -1 >= [ 'table', 'candlestick', 'combo', 'column', 'bar' ].indexOf( type ) ) && (

									<Fragment>

										<TextControl
											label={ __( 'Line Width' ) }
											help={ __( 'Overrides the global line width value for this series.' ) }
											value={ settings.series[index].lineWidth }
											onChange={ e => {
												settings.series[index].lineWidth = e;
												this.props.edit( settings );
											} }
										/>

										<TextControl
											label={ __( 'Point Size' ) }
											help={ __( 'Overrides the global point size value for this series.' ) }
											value={ settings.series[index].pointSize }
											onChange={ e => {
												settings.series[index].pointSize = e;
												this.props.edit( settings );
											} }
										/>

									</Fragment>

								) }

								{ ( -1 >= [ 'candlestick'  ].indexOf( type ) ) &&

									( 'number' === series[i].type ) ? (

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

									) :

									( 'date' === series[i].type ) && (

										<Fragment>

											<TextControl
												label={ __( 'Date Format' ) }
												help={ __( 'Enter custom format pattern to apply to this series value.' ) }
												placeholder="dd LLLL yyyy"
												value={ settings.series[index].format }
												onChange={ e => {
													settings.series[index].format = e;
													this.props.edit( settings );
												} }
											/>

											<p>
												{ __( 'This is a subset of the date formatting ' ) }
												<ExternalLink href="http://userguide.icu-project.org/formatparse/datetime#TOC-Date-Time-Format-Syntax">
													{ __( 'ICU date and time format.' ) }
												</ExternalLink>
											</p>

										</Fragment>

									)

								}

								{ ( 0 <= [ 'scatter', 'line' ].indexOf( type ) ) && (

									<SelectControl
										label={ __( 'Curve Type' ) }
										help={ __( 'Determines whether the series has to be presented in the legend or not.' ) }
										value={ settings.series[index].curveType ? settings.series[index].curveType : 'none' }
										options={ [
											{ label: __( 'Straight line without curve' ), value: 'none' },
											{ label: __( 'The angles of the line will be smoothed' ), value: 'function' }
										] }
										onChange={ e => {
											settings.series[index].curveType = e;
											this.props.edit( settings );
										} }
									/>

								) }

								{ ( 0 <= [ 'area' ].indexOf( type ) ) && (

									<TextControl
										label={ __( 'Area Opacity' ) }
										help={ __( 'The opacity of the colored area, where 0.0 is fully transparent and 1.0 is fully opaque.' ) }
										value={ settings.series[index].areaOpacity }
										onChange={ e => {
											settings.series[index].areaOpacity = e;
											this.props.edit( settings );
										} }
									/>

								) }

								{ ( 0 <= [ 'combo' ].indexOf( type ) ) && (

									<SelectControl
										label={ __( 'Chart Type' ) }
										help={ __( 'Select the type of chart to show for this series.' ) }
										value={ settings.series[index].type ? settings.series[index].type : 'area' }
										options={ [
											{ label: __( 'Area' ), value: 'area' },
											{ label: __( 'Bar' ), value: 'bars' },
											{ label: __( 'Candlesticks' ), value: 'candlesticks' },
											{ label: __( 'Line' ), value: 'line' },
											{ label: __( 'Stepped Area' ), value: 'steppedArea' }
										] }
										onChange={ e => {
											settings.series[index].type = e;
											this.props.edit( settings );
										} }
									/>

								) }

								{ ( -1 >= [ 'table'  ].indexOf( type ) ) && (

									<BaseControl
										label={ __( 'Color' ) }
									>
										<ColorPalette
											value={ settings.series[index].color }
											onChange={ e => {
												settings.series[index].color = e;
												this.props.edit( settings );
											} }
										/>
									</BaseControl>

								) }

							</PanelBody>
						);
					}
					) }

			</PanelBody>
		);
	}
}

export default SeriesSettings;
