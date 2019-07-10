/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;

const { Component } = wp.element;

const {
	ExternalLink,
	PanelBody,
	SelectControl,
	TextControl
} = wp.components;

class MapSettings extends Component {
	constructor() {
		super( ...arguments );
	}

	render() {

		const settings = this.props.chart['visualizer-settings'];

		return (
			<PanelBody
				title={ __( 'Map Settings' ) }
				initialOpen={ false }
				className="visualizer-advanced-panel"
			>

				<PanelBody
					title={ __( 'API' ) }
					className="visualizer-inner-sections"
					initialOpen={ false }
				>

					<TextControl
						label={ __( 'API Key' ) }
						help={ __( 'Add the Google Maps API key.' ) }
						value={ settings['map_api_key'] }
						onChange={ e => {
							settings['map_api_key'] = e;
							this.props.edit( settings );
						} }
					/>

					<ExternalLink
						href="https://developers.google.com/maps/documentation/javascript/get-api-key"
					>
						{ __( 'Get API Keys' ) }
					</ExternalLink>

				</PanelBody>

				<PanelBody
					title={ __( 'Region' ) }
					className="visualizer-inner-sections"
					initialOpen={ false }
				>

					<ul className="visualizer-list">
						<li>{ __( 'A map of the entire world using \'world\'.' ) }</li>
						<li>
							{ __( 'A continent or a sub-continent, specified by its 3-digit code, e.g., \'011\' for Western Africa. ' ) }
							<ExternalLink
								href="https://google-developers.appspot.com/chart/interactive/docs/gallery/geochart#Continent_Hierarchy"
							>
								{ __( 'More info here.' ) }
							</ExternalLink>
						</li>
						<li>
							{ __( 'A country, specified by its ISO 3166-1 alpha-2 code, e.g., \'AU\' for Australia. ' ) }
							<ExternalLink
								href="http://en.wikipedia.org/wiki/ISO_3166-1_alpha-2"
							>
								{ __( 'More info here.' ) }
							</ExternalLink>
						</li>
						<li>
							{ __( 'A state in the United States, specified by its ISO 3166-2:US code, e.g., \'US-AL\' for Alabama. Note that the resolution option must be set to either \'provinces\' or \'metros\'. ' ) }
							<ExternalLink
								href="http://en.wikipedia.org/wiki/ISO_3166-2:US"
							>
								{ __( 'More info here.' ) }
							</ExternalLink>
						</li>
					</ul>

					<TextControl
						label={ __( 'Reigion' ) }
						help={ __( 'Configure the region area to display on the map. (Surrounding areas will be displayed as well.)' ) }
						value={ settings.region }
						onChange={ e => {
							settings.region = e;
							this.props.edit( settings );
						} }
					/>

				</PanelBody>

				<PanelBody
					title={ __( 'Resolution' ) }
					className="visualizer-inner-sections"
					initialOpen={ false }
				>

					<ul className="visualizer-list">
						<li>{ __( '\'countries\' - Supported for all regions, except for US state regions.' ) }</li>
						<li>{ __( '\'provinces\' - Supported only for country regions and US state regions. Not supported for all countries; please test a country to see whether this option is supported.' ) }</li>
						<li>{ __( '\'metros\' - Supported for the US country region and US state regions only.' ) }</li>
					</ul>

					<SelectControl
						label={ __( 'Resolution' ) }
						help={ __( 'The resolution of the map borders.' ) }
						value={ settings.resolution ? settings.resolution : 'countries' }
						options={ [
							{ label: __( 'Countries' ), value: 'countries' },
							{ label: __( 'Provinces' ), value: 'provinces' },
							{ label: __( 'Metros' ), value: 'metros' }
						] }
						onChange={ e => {
							settings.resolution = e;
							this.props.edit( settings );
						} }
					/>

				</PanelBody>

				<PanelBody
					title={ __( 'Display Mode' ) }
					className="visualizer-inner-sections"
					initialOpen={ false }
				>

					<ul className="visualizer-list">
						<li>{ __( '\'auto\' - Choose based on the format of the data.' ) }</li>
						<li>{ __( '\'regions\' - This is a region map.' ) }</li>
						<li>{ __( '\'markers\' - This is a marker map.' ) }</li>
					</ul>

					<SelectControl
						label={ __( 'Display Mode' ) }
						help={ __( 'Determines which type of map this is.' ) }
						value={ settings.displayMode ? settings.displayMode : 'auto' }
						options={ [
							{ label: __( 'Auto' ), value: 'auto' },
							{ label: __( 'Regions' ), value: 'regions' },
							{ label: __( 'Markers' ), value: 'markers' }
						] }
						onChange={ e => {
							settings.displayMode = e;
							this.props.edit( settings );
						} }
					/>

				</PanelBody>

				<PanelBody
					title={ __( 'Tooltip' ) }
					className="visualizer-inner-sections"
					initialOpen={ false }
				>

					<SelectControl
						label={ __( 'Trigger' ) }
						help={ __( 'Determines the user interaction that causes the tooltip to be displayed.' ) }
						value={ settings.tooltip.trigger ? settings.tooltip.trigger : 'focus' }
						options={ [
							{ label: __( 'The tooltip will be displayed when the user hovers over an element' ), value: 'focus' },
							{ label: __( 'The tooltip will not be displayed' ), value: 'none' }
						] }
						onChange={ e => {
							settings.tooltip.trigger = e;
							this.props.edit( settings );
						} }
					/>

				</PanelBody>

			</PanelBody>
		);
	}
}

export default MapSettings;
