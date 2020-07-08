/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;

const {
	Component,
	Fragment
} = wp.element;

const { ColorPalette } = wp.blockEditor || wp.editor;

const {
	BaseControl,
	PanelBody,
	TextControl
} = wp.components;

class RowCellSettings extends Component {
	constructor() {
		super( ...arguments );
	}

	render() {

		const settings = this.props.chart['visualizer-settings'];

		const type = this.props.chart['visualizer-chart-type'];
		const library = this.props.chart['visualizer-chart-library'];

		return (
			<PanelBody
				title={ __( 'Row/Cell Settings' ) }
				initialOpen={ false }
				className="visualizer-advanced-panel"
			>

				{ ( 'DataTable' === library ) ? (
					<Fragment>

						<PanelBody
							title={ __( 'Odd Table Row' ) }
							className="visualizer-inner-sections"
							initialOpen={ false }
						>

							<BaseControl
								label={ __( 'Background Color' ) }
							>
								<ColorPalette
									value={ settings.customcss.oddTableRow['background-color'] }
									onChange={ e => {
										settings.customcss.oddTableRow['background-color'] = e;
										this.props.edit( settings );
									} }
								/>
							</BaseControl>

							<BaseControl
								label={ __( 'Color' ) }
							>
								<ColorPalette
									value={ settings.customcss.oddTableRow.color }
									onChange={ e => {
										settings.customcss.oddTableRow.color = e;
										this.props.edit( settings );
									} }
								/>
							</BaseControl>

							<TextControl
								label={ __( 'Text Orientation' ) }
								help={ __( 'In degrees.' ) }
								type="number"
								value={ settings.customcss.oddTableRow.transform }
								onChange={ e => {
									settings.customcss.oddTableRow.transform = e;
									this.props.edit( settings );
								} }
							/>

						</PanelBody>

						<PanelBody
							title={ __( 'Even Table Row' ) }
							className="visualizer-inner-sections"
							initialOpen={ false }
						>

							<BaseControl
								label={ __( 'Background Color' ) }
							>
								<ColorPalette
									value={ settings.customcss.evenTableRow['background-color'] }
									onChange={ e => {
										settings.customcss.evenTableRow['background-color'] = e;
										this.props.edit( settings );
									} }
								/>
							</BaseControl>

							<BaseControl
								label={ __( 'Color' ) }
							>
								<ColorPalette
									value={ settings.customcss.evenTableRow.color }
									onChange={ e => {
										settings.customcss.evenTableRow.color = e;
										this.props.edit( settings );
									} }
								/>
							</BaseControl>

							<TextControl
								label={ __( 'Text Orientation' ) }
								help={ __( 'In degrees.' ) }
								type="number"
								value={ settings.customcss.evenTableRow.transform }
								onChange={ e => {
									settings.customcss.evenTableRow.transform = e;
									this.props.edit( settings );
								} }
							/>

						</PanelBody>

						<PanelBody
							title={ __( 'Table Cell' ) }
							className="visualizer-inner-sections"
							initialOpen={ false }
						>

							<BaseControl
								label={ __( 'Background Color' ) }
							>
								<ColorPalette
									value={ settings.customcss.tableCell['background-color'] }
									onChange={ e => {
										settings.customcss.tableCell['background-color'] = e;
										this.props.edit( settings );
									} }
								/>
							</BaseControl>

							<BaseControl
								label={ __( 'Color' ) }
							>
								<ColorPalette
									value={ settings.customcss.tableCell.color }
									onChange={ e => {
										settings.customcss.tableCell.color = e;
										this.props.edit( settings );
									} }
								/>
							</BaseControl>

							<TextControl
								label={ __( 'Text Orientation' ) }
								help={ __( 'In degrees.' ) }
								type="number"
								value={ settings.customcss.tableCell.transform }
								onChange={ e => {
									settings.customcss.tableCell.transform = e;
									this.props.edit( settings );
								} }
							/>

						</PanelBody>

					</Fragment>
				) : (
					<Fragment>

						<PanelBody
							title={ __( 'Header Row' ) }
							className="visualizer-inner-sections"
							initialOpen={ false }
						>

							<BaseControl
								label={ __( 'Background Color' ) }
							>
								<ColorPalette
									value={ settings.customcss.headerRow['background-color'] }
									onChange={ e => {
										settings.customcss.headerRow['background-color'] = e;
										this.props.edit( settings );
									} }
								/>
							</BaseControl>

							<BaseControl
								label={ __( 'Color' ) }
							>
								<ColorPalette
									value={ settings.customcss.headerRow.color }
									onChange={ e => {
										settings.customcss.headerRow.color = e;
										this.props.edit( settings );
									} }
								/>
							</BaseControl>

							<TextControl
								label={ __( 'Text Orientation' ) }
								help={ __( 'In degrees.' ) }
								type="number"
								value={ settings.customcss.headerRow.transform }
								onChange={ e => {
									settings.customcss.headerRow.transform = e;
									this.props.edit( settings );
								} }
							/>

						</PanelBody>

						<PanelBody
							title={ __( 'Table Row' ) }
							className="visualizer-inner-sections"
							initialOpen={ false }
						>

							<BaseControl
								label={ __( 'Background Color' ) }
							>
								<ColorPalette
									value={ settings.customcss.tableRow['background-color'] }
									onChange={ e => {
										settings.customcss.tableRow['background-color'] = e;
										this.props.edit( settings );
									} }
								/>
							</BaseControl>

							<BaseControl
								label={ __( 'Color' ) }
							>
								<ColorPalette
									value={ settings.customcss.tableRow.color }
									onChange={ e => {
										settings.customcss.tableRow.color = e;
										this.props.edit( settings );
									} }
								/>
							</BaseControl>

							<TextControl
								label={ __( 'Text Orientation' ) }
								help={ __( 'In degrees.' ) }
								type="number"
								value={ settings.customcss.tableRow.transform }
								onChange={ e => {
									settings.customcss.tableRow.transform = e;
									this.props.edit( settings );
								} }
							/>

						</PanelBody>

						<PanelBody
							title={ __( 'Odd Table Row' ) }
							className="visualizer-inner-sections"
							initialOpen={ false }
						>

							<BaseControl
								label={ __( 'Background Color' ) }
							>
								<ColorPalette
									value={ settings.customcss.oddTableRow['background-color'] }
									onChange={ e => {
										settings.customcss.oddTableRow['background-color'] = e;
										this.props.edit( settings );
									} }
								/>
							</BaseControl>

							<BaseControl
								label={ __( 'Color' ) }
							>
								<ColorPalette
									value={ settings.customcss.oddTableRow.color }
									onChange={ e => {
										settings.customcss.oddTableRow.color = e;
										this.props.edit( settings );
									} }
								/>
							</BaseControl>

							<TextControl
								label={ __( 'Text Orientation' ) }
								help={ __( 'In degrees.' ) }
								type="number"
								value={ settings.customcss.oddTableRow.transform }
								onChange={ e => {
									settings.customcss.oddTableRow.transform = e;
									this.props.edit( settings );
								} }
							/>

						</PanelBody>

						<PanelBody
							title={ __( 'Selected Table Row' ) }
							className="visualizer-inner-sections"
							initialOpen={ false }
						>

							<BaseControl
								label={ __( 'Background Color' ) }
							>
								<ColorPalette
									value={ settings.customcss.selectedTableRow['background-color'] }
									onChange={ e => {
										settings.customcss.selectedTableRow['background-color'] = e;
										this.props.edit( settings );
									} }
								/>
							</BaseControl>

							<BaseControl
								label={ __( 'Color' ) }
							>
								<ColorPalette
									value={ settings.customcss.selectedTableRow.color }
									onChange={ e => {
										settings.customcss.selectedTableRow.color = e;
										this.props.edit( settings );
									} }
								/>
							</BaseControl>

							<TextControl
								label={ __( 'Text Orientation' ) }
								help={ __( 'In degrees.' ) }
								type="number"
								value={ settings.customcss.selectedTableRow.transform }
								onChange={ e => {
									settings.customcss.selectedTableRow.transform = e;
									this.props.edit( settings );
								} }
							/>

						</PanelBody>

						<PanelBody
							title={ __( 'Hover Table Row' ) }
							className="visualizer-inner-sections"
							initialOpen={ false }
						>

							<BaseControl
								label={ __( 'Background Color' ) }
							>
								<ColorPalette
									value={ settings.customcss.hoverTableRow['background-color'] }
									onChange={ e => {
										settings.customcss.hoverTableRow['background-color'] = e;
										this.props.edit( settings );
									} }
								/>
							</BaseControl>

							<BaseControl
								label={ __( 'Color' ) }
							>
								<ColorPalette
									value={ settings.customcss.hoverTableRow.color }
									onChange={ e => {
										settings.customcss.hoverTableRow.color = e;
										this.props.edit( settings );
									} }
								/>
							</BaseControl>

							<TextControl
								label={ __( 'Text Orientation' ) }
								help={ __( 'In degrees.' ) }
								type="number"
								value={ settings.customcss.hoverTableRow.transform }
								onChange={ e => {
									settings.customcss.hoverTableRow.transform = e;
									this.props.edit( settings );
								} }
							/>

						</PanelBody>

						<PanelBody
							title={ __( 'Header Cell' ) }
							className="visualizer-inner-sections"
							initialOpen={ false }
						>

							<BaseControl
								label={ __( 'Background Color' ) }
							>
								<ColorPalette
									value={ settings.customcss.headerCell['background-color'] }
									onChange={ e => {
										settings.customcss.headerCell['background-color'] = e;
										this.props.edit( settings );
									} }
								/>
							</BaseControl>

							<BaseControl
								label={ __( 'Color' ) }
							>
								<ColorPalette
									value={ settings.customcss.headerCell.color }
									onChange={ e => {
										settings.customcss.headerCell.color = e;
										this.props.edit( settings );
									} }
								/>
							</BaseControl>

							<TextControl
								label={ __( 'Text Orientation' ) }
								help={ __( 'In degrees.' ) }
								type="number"
								value={ settings.customcss.headerCell.transform }
								onChange={ e => {
									settings.customcss.headerCell.transform = e;
									this.props.edit( settings );
								} }
							/>

						</PanelBody>

						<PanelBody
							title={ __( 'Table Cell' ) }
							className="visualizer-inner-sections"
							initialOpen={ false }
						>

							<BaseControl
								label={ __( 'Background Color' ) }
							>
								<ColorPalette
									value={ settings.customcss.tableCell['background-color'] }
									onChange={ e => {
										settings.customcss.tableCell['background-color'] = e;
										this.props.edit( settings );
									} }
								/>
							</BaseControl>

							<BaseControl
								label={ __( 'Color' ) }
							>
								<ColorPalette
									value={ settings.customcss.tableCell.color }
									onChange={ e => {
										settings.customcss.tableCell.color = e;
										this.props.edit( settings );
									} }
								/>
							</BaseControl>

							<TextControl
								label={ __( 'Text Orientation' ) }
								help={ __( 'In degrees.' ) }
								type="number"
								value={ settings.customcss.tableCell.transform }
								onChange={ e => {
									settings.customcss.tableCell.transform = e;
									this.props.edit( settings );
								} }
							/>

						</PanelBody>

					</Fragment>
				) }

			</PanelBody>
		);
	}
}

export default RowCellSettings;
