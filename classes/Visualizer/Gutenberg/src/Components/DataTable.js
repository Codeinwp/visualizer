/**
 * WordPress dependencies
 */
const { Component } = wp.element;

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
			this.table.destroy();
			document.getElementById( `dataTable-instances-${ this.props.id }` ).innerHTML = '';
			this.initDataTable( this.props.columns, this.props.rows );
		}
	}

	initDataTable( tableColumns, tableRow ) {
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
			data: data,
			columns: columns,
			destroy: true,
			paging: false,
			searching: false,
			ordering: true,
			select: false,
			lengthChange: false,
			responsive: this.props.chartsScreen ? true : false
		});
	}

	render() {
		return (
			<table id={ `dataTable-instances-${ this.props.id }` }></table>
		);
	}
}

export default DataTables;
