/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;

const { Component } = wp.element;

const {
	CheckboxControl,
	PanelBody,
	SelectControl,
	TextControl
} = wp.components;

class TableSettings extends Component {
	constructor() {
		super( ...arguments );
	}

	render() {

		const settings = this.props.chart['visualizer-settings'];

		return (
			<PanelBody
				title={ __( 'Table Settings' ) }
				initialOpen={ false }
				className="visualizer-advanced-panel"
			>

				<SelectControl
					label={ __( 'Enable Pagination' ) }
					help={ __( 'To enable paging through the data.' ) }
					value={ settings.page ? settings.page : 'disable' }
					options={ [
						{ label: __( 'Enable' ), value: 'enable' },
						{ label: __( 'Disable' ), value: 'disable' }
					] }
					onChange={ e => {
						settings.page = e;
						this.props.edit( settings );
					} }
				/>

				<TextControl
					label={ __( 'Number of rows per page' ) }
					help={ __( 'The number of rows in each page, when paging is enabled.' ) }
					type="number"
					value={ settings.pageSize }
					onChange={ e => {
						settings.pageSize = e;
						this.props.edit( settings );
					} }
				/>

				<SelectControl
					label={ __( 'Disable Sort' ) }
					help={ __( 'To disable sorting on columns.' ) }
					value={ settings.sort ? settings.sort : 'enable' }
					options={ [
						{ label: __( 'Enable' ), value: 'enable' },
						{ label: __( 'Disable' ), value: 'disable' }
					] }
					onChange={ e => {
						settings.sort = e;
						this.props.edit( settings );
					} }
				/>

				<TextControl
					label={ __( 'Freeze Columns' ) }
					help={ __( 'The number of columns from the left that will be frozen.' ) }
					type="number"
					value={ settings.frozenColumns }
					onChange={ e => {
						settings.frozenColumns = e;
						this.props.edit( settings );
					} }
				/>

				<CheckboxControl
					label={ __( 'Allow HTML' ) }
					help={ __( 'If enabled, formatted values of cells that include HTML tags will be rendered as HTML.' ) }
					checked={ Number( settings.allowHtml ) }
					onChange={ e => {
						settings.allowHtml = ! Number( settings.allowHtml );
						this.props.edit( settings );
					} }
				/>

				<CheckboxControl
					label={ __( 'Right to Left table' ) }
					help={ __( 'Adds basic support for right-to-left languages.' ) }
					checked={ Number( settings.rtlTable ) }
					onChange={ e => {
						settings.rtlTable = ! Number( settings.rtlTable );
						this.props.edit( settings );
					} }
				/>

			</PanelBody>
		);
	}
}

export default TableSettings;
