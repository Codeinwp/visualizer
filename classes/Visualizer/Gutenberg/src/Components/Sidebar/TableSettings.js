/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;

const {
	Component,
	Fragment
} = wp.element;

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
		const library = this.props.chart['visualizer-chart-library'];

		return (
			<PanelBody
				title={ __( 'Table Settings' ) }
				initialOpen={ false }
				className="visualizer-advanced-panel"
			>

				{ ( 'DataTable' === library ) ? (
					<Fragment>
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

						<TextControl
							label={ __( 'Number of rows per page' ) }
							help={ __( 'The number of rows in each page, when paging is enabled.' ) }
							type="number"
							value={ settings.pageLength_int }
							placeholder={ 10 }
							onChange={ e => {
								settings.pageLength_int = e; // eslint-disable-line camelcase
								this.props.edit( settings );
							} }
						/>

						<SelectControl
							label={ __( 'Pagination type' ) }
							help={ __( 'Determines what type of pagination options to show.' ) }
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

						<CheckboxControl
							label={ __( 'Scroll Collapse' ) }
							help={ __( 'Allow the table to reduce in height when a limited number of rows are shown.' ) }
							checked={ 'true' === settings.scrollCollapse_bool ? true : false }
							onChange={ e => {
								settings.scrollCollapse_bool = 'true'; // eslint-disable-line camelcase
								if ( ! e ) {
									settings.scrollCollapse_bool = 'false'; // eslint-disable-line camelcase
								}
								this.props.edit( settings );
							} }
						/>

						{ ( 'true' === settings.scrollCollapse_bool ) && (
							<TextControl
								label={ __( 'Vertical Height' ) }
								help={ __( 'Vertical scrolling will constrain the table to the given height.' ) }
								type="number"
								value={ settings.scrollY_int }
								placeholder={ 300 }
								onChange={ e => {
									settings.scrollY_int = e; // eslint-disable-line camelcase
									this.props.edit( settings );
								} }
							/>
						) }

						<CheckboxControl
							label={ __( 'Disable Sort' ) }
							help={ __( 'To disable sorting on columns.' ) }
							checked={ 'false' === settings.ordering_bool ? true : false }
							onChange={ e => {
								settings.ordering_bool = 'true'; // eslint-disable-line camelcase
								if ( e ) {
									settings.ordering_bool = 'false'; // eslint-disable-line camelcase
								}
								this.props.edit( settings );
							} }
						/>

						<CheckboxControl
							label={ __( 'Freeze Header/Footer' ) }
							help={ __( 'Freeze the header and footer.' ) }
							checked={ 'true' === settings.fixedHeader_bool ? true : false }
							onChange={ e => {
								settings.fixedHeader_bool = 'true'; // eslint-disable-line camelcase
								if ( ! e ) {
									settings.fixedHeader_bool = 'false'; // eslint-disable-line camelcase
								}
								this.props.edit( settings );
							} }
						/>

						<CheckboxControl
							label={ __( 'Responsive' ) }
							help={ __( 'Enable the table to be responsive.' ) }
							checked={ 'true' === settings.responsive_bool ? true : false }
							onChange={ e => {
								settings.responsive_bool = 'true'; // eslint-disable-line camelcase
								if ( ! e ) {
									settings.responsive_bool = 'false'; // eslint-disable-line camelcase
								}
								this.props.edit( settings );
							} }
						/>
					</Fragment>
				) : (
					<Fragment>
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
					</Fragment>
				) }
			</PanelBody>
		);
	}
}

export default TableSettings;
