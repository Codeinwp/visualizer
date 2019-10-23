/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;

const { apiRequest } = wp;

const {
	Button,
	ExternalLink
} = wp.components;

const {
	Component,
	Fragment
} = wp.element;


class SQLEditor extends Component {
	constructor() {
		super( ...arguments );

		this.onSave = this.onSave.bind( this );

		this.state = {
			isLoading: false,
			success: false,
			query: '',
			name: '',
			series: {},
			data: []
		};
	}

	componentDidMount() {
		const editor = wp.CodeMirror || CodeMirror;
		const textarea = document.querySelector( '.visualizer-db-query' );
		const cm = editor.fromTextArea( textarea, {
			autofocus: true,
			mode: 'text/x-mysql',
			lineWrapping: true,
			dragDrop: false,
			matchBrackets: true,
			autoCloseBrackets: true,
			extraKeys: { 'Ctrl-Space': 'autocomplete' },
			hintOptions: { tables: visualizerLocalize.sqlTable }
		});

		cm.on( 'inputRead', () => {
			cm.save();
		});
	}

	async onSave() {
		const textarea = document.querySelector( '.visualizer-db-query' ).value;
		const result = document.querySelector( '#visualizer-db-query-table' );
		result.innerHTML = '';

		await this.setState({ isLoading: true });

		let response = await apiRequest({
			path: '/visualizer/v1/get-query-data',
			data: {
				query: textarea
			},
			method: 'GET'
		});

		await this.setState({
			isLoading: false,
			success: response.success,
			query: textarea,
			name: response.data.name || '',
			series: response.data.series || {},
			data: response.data.data || []
		});

		result.innerHTML = response.data.table || response.data.msg;

		if ( this.state.success ) {
			jQuery( '#results' ).DataTable({
				paging: false
			});
		}
	}

	render() {
		return (
			<Fragment>
				<textarea
					className="visualizer-db-query"
					placeholder={ __( 'Your query goes here…' ) }
				>
					{ this.props.chart['visualizer-db-query'] }
				</textarea>

				<div className="visualizer-db-query-actions">
					<Button
						isLarge
						isDefault
						isBusy={ this.state.isLoading }
						onClick={ this.onSave }
					>
						{ __( 'Show Results' ) }
					</Button>

					<Button
						isLarge
						isPrimary
						disabled={ ! this.state.success }
						onClick={ () => this.props.save( this.state.query, this.state.name, this.state.series, this.state.data ) }
					>
						{ __( 'Save' ) }
					</Button>
				</div>

				<ul>
					<li>
						<ExternalLink href="https://docs.themeisle.com/article/970-visualizer-sample-queries-to-generate-charts">
							{ __( 'Examples of queries and links to resources that you can use with this feature.' ) }
						</ExternalLink>
					</li>
					<li>{ __( 'Use Control+Space for autocompleting keywords or table names.' ) }</li>
				</ul>

				<div
					id="visualizer-db-query-table"
					className={ ! this.state.success && 'db-wizard-error' }
				>
				</div>
			</Fragment>
		);
	}
}

export default SQLEditor;
