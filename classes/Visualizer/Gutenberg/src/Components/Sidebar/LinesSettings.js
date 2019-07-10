/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;

const {
	Component,
	Fragment
} = wp.element;

const {
	PanelBody,
	SelectControl,
	TextControl
} = wp.components;

class LinesSettings extends Component {
	constructor() {
		super( ...arguments );
	}

	render() {

		const type = this.props.chart['visualizer-chart-type'];

		const settings = this.props.chart['visualizer-settings'];

		return (
			<PanelBody
				title={ __( 'Lines Settings' ) }
				initialOpen={ false }
				className="visualizer-advanced-panel"
			>

				<TextControl
					label={ __( 'Line Width' ) }
					help={ __( 'Data line width in pixels. Use zero to hide all lines.' ) }
					value={ settings.lineWidth }
					onChange={ e => {
						settings.lineWidth = e;
						this.props.edit( settings );
					} }
				/>

				<TextControl
					label={ __( 'Point Size' ) }
					help={ __( 'Diameter of displayed points in pixels. Use zero to hide all the points.' ) }
					value={ settings.pointSize }
					onChange={ e => {
						settings.pointSize = e;
						this.props.edit( settings );
					} }
				/>

				{ ( -1 >= [ 'area' ].indexOf( type ) ) && (
					<SelectControl
						label={ __( 'Curve Type' ) }
						help={ __( 'Determines whether the series has to be presented in the legend or not.' ) }
						value={ settings.curveType ? settings.curveType : 'none' }
						options={ [
							{ label: __( 'Straight line without curve' ), value: 'none' },
							{ label: __( 'The angles of the line will be smoothed' ), value: 'function' }
						] }
						onChange={ e => {
							settings.curveType = e;
							this.props.edit( settings );
						} }
					/>
				) }

				{ ( -1 >= [ 'scatter' ].indexOf( type ) ) && (
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
				) }

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

				<TextControl
					label={ __( 'Point Opacity' ) }
					help={ __( 'The transparency of data points, with 1.0 being completely opaque and 0.0 fully transparent.' ) }
					value={ settings.dataOpacity }
					onChange={ e => {
						settings.dataOpacity = e;
						this.props.edit( settings );
					} }
				/>

				{ ( -1 >= [ 'scatter', 'line' ].indexOf( type ) ) && (
					<Fragment>
						<TextControl
							label={ __( 'Area Opacity' ) }
							help={ __( 'The default opacity of the colored area under an area chart series, where 0.0 is fully transparent and 1.0 is fully opaque. To specify opacity for an individual series, set the area opacity value in the series property.' ) }
							value={ settings.areaOpacity }
							onChange={ e => {
								settings.areaOpacity = e;
								this.props.edit( settings );
							} }
						/>

						<SelectControl
							label={ __( 'Is Stacked' ) }
							help={ __( 'If set to yes, series elements are stacked.' ) }
							value={ settings.isStacked ? settings.isStacked : '0' }
							options={ [
								{ label: __( 'Yes' ), value: '1' },
								{ label: __( 'No' ), value: '0' }
							] }
							onChange={ e => {
								settings.isStacked = e;
								this.props.edit( settings );
							} }
						/>
					</Fragment>
				) }

				{ ( -1 >= [ 'scatter', 'area' ].indexOf( type ) ) && (
					<SelectControl
						label={ __( 'Interpolate Nulls' ) }
						help={ __( 'Whether to guess the value of missing points. If yes, it will guess the value of any missing data based on neighboring points. If no, it will leave a break in the line at the unknown point.' ) }
						value={ settings.interpolateNulls ? settings.interpolateNulls : '0' }
						options={ [
							{ label: __( 'Yes' ), value: '1' },
							{ label: __( 'No' ), value: '0' }
						] }
						onChange={ e => {
							settings.interpolateNulls = e;
							this.props.edit( settings );
						} }
					/>
				) }

			</PanelBody>
		);
	}
}

export default LinesSettings;
