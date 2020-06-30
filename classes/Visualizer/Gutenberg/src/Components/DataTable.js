/**
 * External dependencies
 */
import uuidv4 from 'uuid';

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
		this.dataRenderer = this.dataRenderer.bind( this );

		this.table;
		this.uniqueId = uuidv4();
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
					document.getElementById( `dataTable-instances-${ this.props.id }-${ this.uniqueId }` ).classList.remove( 'collapsed' );
				}
			}

			this.table.destroy();
			document.getElementById( `dataTable-instances-${ this.props.id }-${ this.uniqueId }` ).innerHTML = '';
			this.initDataTable( this.props.columns, this.props.rows );
		}
	}

	initDataTable( tableColumns, tableRow ) {
		const settings = this.props.options;
		const columns = tableColumns.map( ( i, index ) => {
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
				render: this.dataRenderer( type, index )
			};
		});

		const data = tableRow.map( i => {
			const row = {};

			columns.forEach( ( j, n ) => {
                var datum = i[n];

                // datum could be undefined for dynamic data (e.g. through json).
                if ( 'undefined' === typeof datum ) {
                    datum = i[j.data];
                }
				row[j.data] = datum;
			});

			return row;
		});

		this.table = jQuery( `#dataTable-instances-${ this.props.id }-${ this.uniqueId }` ).DataTable({
			destroy: true,
			data: data,
			columns: columns,
			paging: 'true' === settings.paging_bool ? true : false,
			pageLength: settings.pageLength_int || 10,
			pagingType: settings.pagingType,
			ordering: 'false' === settings.ordering_bool ? false : true,
			fixedHeader: 'true' === settings.fixedHeader_bool ? true : false,
			scrollCollapse: !! this.props.chartsScreen || 'true' === settings.scrollCollapse_bool ? true : false,
			scrollY: this.props.chartsScreen && 180 || ( 'true' === settings.scrollCollapse_bool && Number( settings.scrollY_int ) || false ),
			responsive: !! this.props.chartsScreen || 'true' === settings.responsive_bool ? true : false,
			searching: false,
			select: false,
			lengthChange: false,
			bFilter: false,
			bInfo: false
		});
	}

	dataRenderer( type, index ) {
		const settings = this.props.options;

        let renderer = null;
		if ( 'undefined' === typeof settings.series || 'undefined' === typeof settings.series[index] || 'undefined' === typeof settings.series[index].format ) {
            return renderer;
        }

        switch ( type ) {
            case 'date':
            case 'datetime':
            case 'timeofday':
                if ( settings.series[index].format && settings.series[index].format.from && settings.series[index].format.to ) {
                    renderer = jQuery.fn.dataTable.render.moment( settings.series[index].format.from, settings.series[index].format.to );
                } else {
                    renderer = jQuery.fn.dataTable.render.moment( 'MM-DD-YYYY' );
                }
                break;
            case 'num':
                const parts = [ '', '', '', '', '' ];
                if ( settings.series[index].format.thousands ) {
                    parts[0] = settings.series[index].format.thousands;
                }
                if ( settings.series[index].format.decimal ) {
                    parts[1] = settings.series[index].format.decimal;
                }
                if ( settings.series[index].format.precision && 0 < parseInt( settings.series[index].format.precision ) ) {
                    parts[2] = settings.series[index].format.precision;
                }
                if ( settings.series[index].format.prefix ) {
                    parts[3] = settings.series[index].format.prefix;
                }
                if ( settings.series[index].format.suffix ) {
                    parts[4] = settings.series[index].format.suffix;
                }
                renderer = jQuery.fn.dataTable.render.number( ...parts );
                break;
            case 'boolean':
                jQuery.fn.dataTable.render.extra = function( data, type, row ) {
                    if ( ( true === data || 'true' === data ) && '' !== settings.series[index].format.truthy ) {
                        return settings.series[index].format.truthy.replace( /<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, '' );
                    }
                    if ( ( false === data || 'false' === data ) && '' !== settings.series[index].format.falsy ) {
                        return settings.series[index].format.falsy.replace( /<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, '' );
                    }
                    return data;
                };
                renderer = jQuery.fn.dataTable.render.extra;
                break;
		}

		return renderer;
	}

	render() {
		const settings = this.props.options;

		return (
			<Fragment>
				{ settings.customcss && (
					<style>
						{ settings.customcss.oddTableRow && (
							`#dataTable-instances-${ this.props.id }-${ this.uniqueId } tr.odd {
								${ settings.customcss.oddTableRow.color ?  `color: ${ settings.customcss.oddTableRow.color } !important;` : '' }
								${ settings.customcss.oddTableRow['background-color'] ?  `background-color: ${ settings.customcss.oddTableRow['background-color'] } !important;` : '' }
								${ settings.customcss.oddTableRow.transform ?  `transform: rotate( ${ settings.customcss.oddTableRow.transform }deg ) !important;` : '' }
							}`
						)}

						{ settings.customcss.evenTableRow && (
							`#dataTable-instances-${ this.props.id }-${ this.uniqueId } tr.even {
								${ settings.customcss.evenTableRow.color ?  `color: ${ settings.customcss.evenTableRow.color } !important;` : '' }
								${ settings.customcss.evenTableRow['background-color'] ?  `background-color: ${ settings.customcss.evenTableRow['background-color'] } !important;` : '' }
								${ settings.customcss.evenTableRow.transform ?  `transform: rotate( ${ settings.customcss.evenTableRow.transform }deg ) !important;` : '' }
							}`
						)}

						{ settings.customcss.tableCell && (
							`#dataTable-instances-${ this.props.id }-${ this.uniqueId } tr td,
							#dataTable-instances-${ this.props.id }-${ this.uniqueId }_wrapper tr th {
								${ settings.customcss.tableCell.color ?  `color: ${ settings.customcss.tableCell.color } !important;` : '' }
								${ settings.customcss.tableCell['background-color'] ?  `background-color: ${ settings.customcss.tableCell['background-color'] } !important;` : '' }
								${ settings.customcss.tableCell.transform ?  `transform: rotate( ${ settings.customcss.tableCell.transform }deg ) !important;` : '' }
							}`
						)}
					</style>
				) }

				<table id={ `dataTable-instances-${ this.props.id }-${ this.uniqueId }` }></table>
			</Fragment>
		);
	}
}

export default DataTables;
