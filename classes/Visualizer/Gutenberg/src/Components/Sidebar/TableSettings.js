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

		const type = this.props.chart['visualizer-chart-type'];

		return (
			<PanelBody
				title={ __( 'Table Settings' ) }
				initialOpen={ false }
				className="visualizer-advanced-panel"
			>

				{ ( 'dataTable' === type ) ? (
					<CheckboxControl
						label={ __( 'Enable Pagination' ) }
						help={ __( 'To enable paging through the data.' ) }
						checked={ 'true' === settings.paging_bool ? true : false }
						onChange={ e => {
							settings.paging_bool = 'true'; // eslint-disable-line camelcase
							if ( ! e ) {
								settings.paging_bool = 'false'; // eslint-disable-line camelcase
							}
							this.props.edit( settings );
						} }
					/>
				) : (
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
				) }

				{ ( 'dataTable' === type ) ? (
					<TextControl
						label={ __( 'Number of rows per page' ) }
						help={ __( 'The number of rows in each page, when paging is enabled.' ) }
						type="number"
						value={ settings.pageLength_int }
						onChange={ e => {
							settings.pageLength_int = e; // eslint-disable-line camelcase
							this.props.edit( settings );
						} }
					/>
				) : (
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
				) }

				{ ( 'dataTable' === type ) && (
					<SelectControl
						label={ __( 'Pagination type' ) }
						help={ __( 'TDetermines what type of pagination options to show.' ) }
						value={ settings.pagingType }
						options={ [
							{ label: __( 'Page number buttons only' ), value: 'numbers' },
							{ label: __( '\'Previous\' and \'Next\' buttons only' ), value: 'simple' },
							{ label: __( '\'Previous\' and \'Next\' buttons, plus page numbers' ), value: 'simple_numbers' },
							{ label: __( '\'First\', \'Previous\', \'Next\' and \'Last\' buttons' ), value: 'full' },
							{ label: __( '\'First\', \'Previous\', \'Next\' and \'Last\' buttons, plus page numbers' ), value: 'full_numbers' },
							{ label: __( '\'First\' and \'Last\' buttons, plus page numbers' ), value: 'first_last_numbers' }
						] }
						onChange={ e => {
							settings.pagingType = e;
							this.props.edit( settings );
						} }
					/>
				) }

				{ ( 'dataTable' === type ) && (
					<CheckboxControl
						label={ __( 'Enable paging display length' ) }
						help={ __( 'Allow user to change the paging display length of the table.' ) }
						checked={ 'true' === settings.lengthChange_bool ? true : false }
						onChange={ e => {
							settings.lengthChange_bool = 'true'; // eslint-disable-line camelcase
							if ( ! e ) {
								settings.lengthChange_bool = 'false'; // eslint-disable-line camelcase
							}
							this.props.edit( settings );
						} }
					/>
				) }

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
