/**
 * WordPress dependencies
 */
const {
	Component,
	Fragment
} = wp.element;

class DataTables extends Component {
	constructor() {
		super( ...arguments );

		this.initDataTable = this.initDataTable.bind( this );

		this.table;
	}

	componentDidMount() {
		this.initDataTable( this.props.columns, this.props.rows );
	};

	componentWillUnmount() {
		this.table.destroy();
	}

	componentDidUpdate( prevProps ) {
		if ( this.props !== prevProps ) {
			if ( this.props.options.responsive_bool !== prevProps.options.responsive_bool ) {
				if ( 'true' === prevProps.options.responsive_bool ) {
					document.getElementById( `dataTable-instances-${ this.props.id }` ).classList.remove( 'collapsed' );
				}
			}

			this.table.destroy();
			document.getElementById( `dataTable-instances-${ this.props.id }` ).innerHTML = '';
			this.initDataTable( this.props.columns, this.props.rows );
		}
	}

	initDataTable( tableColumns, tableRow ) {
		const settings = this.props.options;

		const columns = tableColumns.map( i => {
			let type = i.type;

			switch ( i.type ) {
			case 'number':
				type = 'num';
				break;
			case 'date':
			case 'datetime':
			case 'timeofday':
				type = 'date';
				break;
			}

			return {
				title: i.label,
				data: i.label,
				type: type,
				render: ( data ) => {
					if ( 'date' === type ) {
						return moment( data ).format( 'MM-DD-YYYY' );
					}

					return data;
				}
			};
		});

		const data = tableRow.map( i => {
			const row = {};

			columns.forEach( ( j, n ) => {
				row[j.data] = i[n];
			});

			return row;
		});

		this.table = jQuery( `#dataTable-instances-${ this.props.id }` ).DataTable({
			destroy: true,
			data: data,
			columns: columns,
			paging: 'true' === settings.paging_bool ? true : false,
			pageLength: settings.pageLength_int || 10,
			pagingType: settings.pagingType,
			ordering: 'false' === settings.ordering_bool ? false : true,
			fixedHeader: 'true' === settings.fixedHeader_bool ? true : false,
			scrollCollapse: this.props.chartsScreen && true || 'true' === settings.scrollCollapse_bool ? true : false,
			scrollY: this.props.chartsScreen && 180 || ( 'true' === settings.scrollCollapse_bool && Number( settings.scrollY_int ) || false ),
			responsive: this.props.chartsScreen && true || 'true' === settings.responsive_bool ? true : false,
			searching: false,
			select: false,
			lengthChange: false,
			bFilter: false,
			bInfo: false
		});
	}

	render() {
		const settings = this.props.options;

		return (
			<Fragment>
				{ settings.customcss && (
					<style>
						{ `#dataTable-instances-${ this.props.id } tr.odd {
							${ settings.customcss.oddTableRow.color ?  `color: ${ settings.customcss.oddTableRow.color } !important;` : '' }
							${ settings.customcss.oddTableRow['background-color'] ?  `background-color: ${ settings.customcss.oddTableRow['background-color'] } !important;` : '' }
							${ settings.customcss.oddTableRow.transform ?  `transform: rotate( ${ settings.customcss.oddTableRow.transform }deg ) !important;` : '' }
						}

						#dataTable-instances-${ this.props.id } tr.even {
							${ settings.customcss.evenTableRow.color ?  `color: ${ settings.customcss.evenTableRow.color } !important;` : '' }
							${ settings.customcss.evenTableRow['background-color'] ?  `background-color: ${ settings.customcss.evenTableRow['background-color'] } !important;` : '' }
							${ settings.customcss.evenTableRow.transform ?  `transform: rotate( ${ settings.customcss.evenTableRow.transform }deg ) !important;` : '' }
						}

						#dataTable-instances-${ this.props.id } tr td,
						#dataTable-instances-${ this.props.id }_wrapper tr th {
							${ settings.customcss.tableCell.color ?  `color: ${ settings.customcss.tableCell.color } !important;` : '' }
							${ settings.customcss.tableCell['background-color'] ?  `background-color: ${ settings.customcss.tableCell['background-color'] } !important;` : '' }
							${ settings.customcss.tableCell.transform ?  `transform: rotate( ${ settings.customcss.tableCell.transform }deg ) !important;` : '' }
						}` }
					</style>
				) }

				<table id={ `dataTable-instances-${ this.props.id }` }></table>
			</Fragment>
		);
	}
}

export default DataTables;
