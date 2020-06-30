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

class ColumnSettings extends Component {
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

        if ( ! settings.series ) {
            return;
        }

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

		const settings = this.props.chart['visualizer-settings'];

		const series = this.props.chart['visualizer-series'];

		const type = this.props.chart['visualizer-chart-type'];

        if ( ! settings.series ) {
            return null;
        }

		return (
			<PanelBody
				title={ __( 'Column Settings' ) }
				initialOpen={ false }
				className="visualizer-advanced-panel"
			>

				{ Object.keys( settings.series )
					.map( ( i ) => {

                        // don't show string columns.
                        if ( 'string' === series[i].type ) {
                            return null;
                        }

						return (
							<PanelBody
								title={ series[i].label }
								className="visualizer-inner-sections"
								initialOpen={ false }
							>

								{ 0 <= [ 'date', 'datetime', 'timeofday' ].indexOf( series[i].type ) && (
									<Fragment>
										<TextControl
											label={ __( 'Display Date Format' ) }
											help={ __( 'Enter custom format pattern to apply to this series value.' ) }
											value={ settings.series[i].format ? settings.series[i].format.to : '' }
											onChange={ e => {
												if ( ! settings.series[i].format ) {
													settings.series[i].format = {};
												}
												settings.series[i].format.to = e;
												this.props.edit( settings );
											} }
										/>

										<TextControl
											label={ __( 'Source Date Format' ) }
											help={ __( 'What format is the source date in?' ) }
											value={ settings.series[i].format ? settings.series[i].format.from : '' }
											onChange={ e => {
												if ( ! settings.series[i].format ) {
													settings.series[i].format = {};
												}
												settings.series[i].format.from = e;
												this.props.edit( settings );
											} }
										/>

										<p>
											{ __( 'You can find more info on ' ) }
											<ExternalLink href="https://momentjs.com/docs/#/displaying/">
												{ __( 'date and time formats here.' ) }
											</ExternalLink>
										</p>
									</Fragment>
								) }

								{ ( 'number' === series[i].type ) && (
									<Fragment>
										<TextControl
											label={ __( 'Thousands Separator' ) }
											value={ settings.series[i].format ? settings.series[i].format.thousands : '' }
											onChange={ e => {
												if ( ! settings.series[i].format ) {
													settings.series[i].format = {};
												}
												settings.series[i].format.thousands = e;
												this.props.edit( settings );
											} }
										/>

										<TextControl
											label={ __( 'Decimal Separator' ) }
											value={ settings.series[i].format ? settings.series[i].format.decimal : '' }
											onChange={ e => {
												if ( ! settings.series[i].format ) {
													settings.series[i].format = {};
												}
												settings.series[i].format.decimal = e;
												this.props.edit( settings );
											} }
										/>

										<TextControl
											label={ __( 'Precision' ) }
											help={ __( 'Round values to how many decimal places?' ) }
											value={ settings.series[i].format ? settings.series[i].format.precision : '' }
											type="number"
                                            min="0"
											onChange={ e => {
												if ( 100 < e ) {
													return;
												}

												if ( ! settings.series[i].format ) {
													settings.series[i].format = {};
												}
												settings.series[i].format.precision = e;
												this.props.edit( settings );
											} }
										/>

										<TextControl
											label={ __( 'Prefix' ) }
											value={ settings.series[i].format ? settings.series[i].format.prefix : '' }
											onChange={ e => {
												if ( ! settings.series[i].format ) {
													settings.series[i].format = {};
												}
												settings.series[i].format.prefix = e;
												this.props.edit( settings );
											} }
										/>

										<TextControl
											label={ __( 'Suffix' ) }
											value={ settings.series[i].format ? settings.series[i].format.suffix : '' }
											onChange={ e => {
												if ( ! settings.series[i].format ) {
													settings.series[i].format = {};
												}
												settings.series[i].format.suffix = e;
												this.props.edit( settings );
											} }
										/>
									</Fragment>
								) }

								{ ( 'boolean' === series[i].type ) && (
									<Fragment>
										<TextControl
											label={ __( 'Truthy value' ) }
                                            help= { __( 'Provide the HTML entity code for the value the table should display when the value of the column is true. e.g. tick mark (Code: &#10004;) instead of true' ) }
											value={ settings.series[i].format ? settings.series[i].format.truthy : '' }
											onChange={ e => {
												if ( ! settings.series[i].truthy ) {
													settings.series[i].truthy = {};
												}
												settings.series[i].format.truthy = e;
												this.props.edit( settings );
											} }
										/>
										<TextControl
											label={ __( 'Falsy value' ) }
                                            help= { __( 'Provide the HTML entity code for the value the table should display when the value of the column is false. e.g. cross mark (Code: &#10006;) instead of false' ) }
											value={ settings.series[i].format ? settings.series[i].format.falsy : '' }
											onChange={ e => {
												if ( ! settings.series[i].falsy ) {
													settings.series[i].falsy = {};
												}
												settings.series[i].format.falsy = e;
												this.props.edit( settings );
											} }
										/>

									</Fragment>
								) }

							</PanelBody>
						);
					}
					) }

			</PanelBody>
		);
	}
}

export default ColumnSettings;
