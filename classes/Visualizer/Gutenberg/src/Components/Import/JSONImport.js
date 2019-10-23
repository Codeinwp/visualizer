/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;

const {
	apiFetch,
	apiRequest
} = wp;

const { Component } = wp.element;

const {
	Button,
	ExternalLink,
	Modal,
	PanelBody,
	SelectControl,
	TextControl
} = wp.components;

class JSONImport extends Component {
	constructor() {
		super( ...arguments );

		this.openModal = this.openModal.bind( this );
		this.initTable = this.initTable.bind( this );
		this.onToggle = this.onToggle.bind( this );
		this.getJSONRoot = this.getJSONRoot.bind( this );
		this.getJSONData = this.getJSONData.bind( this );
		this.getTableData = this.getTableData.bind( this );

		this.state = {
			isOpen: false,
			isLoading: false,
			isFirstStepOpen: true,
			isSecondStepOpen: false,
			isThirdStepOpen: false,
			isFourthStepOpen: false,
			endpointRoots: [],
			endpointPaging: [],
			table: null
		};
	}

	async openModal() {
		await this.setState({ isOpen: true });

		const table = document.querySelector( '#visualizer-json-query-table' );

		if ( this.state.isFourthStepOpen && null !== this.state.table ) {
			table.innerHTML = this.state.table;
			this.initTable();
		}
	}

	initTable() {
		jQuery( '#visualizer-json-query-table table' ).DataTable({
			paging: false,
			searching: false,
			ordering: false,
			select: false,
			scrollX: '600px',
			scrollY: '400px',
			info: false,
			colReorder: {
				fixedColumnsLeft: 1
			},
			dom: 'Bt',
			buttons: [
				{
					extend: 'colvis',
					columns: ':gt(0)',
					collectionLayout: 'four-column'
				}
			]
		});
	}

	async onToggle( value ) {
		if ( null === this.state.table && ( this.state.endpointRoots && 0 < this.state.endpointRoots.length ) && ( 'isFirstStepOpen' === value || 'isSecondStepOpen' === value ) ) {
			this.setState({
				isFirstStepOpen: ( 'isFirstStepOpen' === value ? true : false ),
				isSecondStepOpen: ( 'isSecondStepOpen' === value ? true : false ),
				isThirdStepOpen: false,
				isFourthStepOpen: false
			});
		}

		if ( null !== this.state.table ) {
			await this.setState({
				isFirstStepOpen: ( 'isFirstStepOpen' === value ? true : false ),
				isSecondStepOpen: ( 'isSecondStepOpen' === value ? true : false ),
				isThirdStepOpen: ( 'isThirdStepOpen' === value ? true : false ),
				isFourthStepOpen: ( 'isFourthStepOpen' === value ? true : false )
			});

			if ( 'isFourthStepOpen' === value ) {
				const table = document.querySelector( '#visualizer-json-query-table' );

				if ( this.state.isFourthStepOpen ) {
					table.innerHTML = this.state.table;
					this.initTable();
				}
			}
		}
	}

	async getJSONRoot() {
		this.setState({
			isLoading: true,
			endpointRoots: [],
			endpointPaging: [],
			table: null
		});

		let response = await apiFetch({ path: `/visualizer/v1/get-json-root?url=${ this.props.chart['visualizer-json-url'] }` });

		if ( response.success ) {
			const roots = response.data.roots.map( root => {
				return {
					label: root.replace( />/g, ' ➤ ' ),
					value: root
				};
			});

			this.setState({
				isLoading: false,
				isFirstStepOpen: false,
				isSecondStepOpen: true,
				endpointRoots: roots
			});
		} else {
			this.setState({ isLoading: false });
			alert( response.data.msg );
		}
	}

	async getJSONData() {
		this.setState({ isLoading: true });

		let response = await apiRequest({
			path: `/visualizer/v1/get-json-data?url=${ this.props.chart['visualizer-json-url'] }&chart=${ this.props.id }`,
			data: {
				root: this.props.chart['visualizer-json-root'] || this.state.endpointRoots[0].value
			},
			method: 'GET'
		});

		if ( response.success ) {
			const paging = [
				{
					label: __( 'Don\'t use pagination' ),
					value: 0
				}
			];

			if ( response.data.paging && 'root>next' === response.data.paging[0]) {
				paging.push(
					{
						label: __( 'Get first 5 pages using root ➤ next' ),
						value: 'root>next'
					}
				);
			}

			this.setState({
				isLoading: false,
				isSecondStepOpen: false,
				isFourthStepOpen: true,
				endpointPaging: paging,
				table: response.data.table
			});

			const table = document.querySelector( '#visualizer-json-query-table' );
			table.innerHTML = response.data.table;

			this.initTable();
		} else {
			this.setState({ isLoading: false });
			alert( response.data.msg );
		}
	}

	async getTableData() {
		this.setState({ isLoading: true });

		const columns = document.querySelectorAll( '#visualizer-json-query-table input' );
		const select = document.querySelectorAll( '#visualizer-json-query-table select' );
		const header = [];
		const type = {};

		columns.forEach( column => header.push( column.value ) );
		select.forEach( el => type[el.name] = el.value );

		let response = await apiRequest({
			path: '/visualizer/v1/set-json-data',
			data: {
				url: this.props.chart['visualizer-json-url'],
				root: this.props.chart['visualizer-json-root'] || this.state.endpointRoots[0].value,
				paging: this.props.chart['visualizer-json-paging'] || 0,
				header,
				...type
			},
			method: 'GET'
		});

		if ( response.success ) {
			this.props.JSONImportData( response.data.name, response.data.series, response.data.data );

			this.setState({
				isOpen: false,
				isLoading: false
			});
		} else {
			alert( response.data.msg );

			this.setState({ isLoading: false });
		}
	}

	render() {
		return (
			<PanelBody
				title={ __( 'Import from JSON' ) }
				className="visualizer-inner-sections"
				initialOpen={ false }
			>
				<p>{ __( 'You can choose here to import or synchronize your chart data with a remote JSON source.' ) }</p>

				<p>
					<ExternalLink href="https://docs.themeisle.com/article/1052-how-to-generate-charts-from-json-data-rest-endpoints">
						{ __( 'For more info check this tutorial.' ) }
					</ExternalLink>
				</p>

				<SelectControl
					label={ __( 'How often do you want to check the url?' ) }
					value={ this.props.chart['visualizer-json-schedule'] ? this.props.chart['visualizer-json-schedule'] : 1 }
					options={ [
						{ label: __( 'One-time' ), value: '-1' },
						{ label: __( 'Live' ), value: '0' },
						{ label: __( 'Each hour' ), value: '1' },
						{ label: __( 'Each 12 hours' ), value: '12' },
						{ label: __( 'Each day' ), value: '24' },
						{ label: __( 'Each 3 days' ), value: '72' }
					] }
					onChange={ this.props.editSchedule }
				/>

				<Button
					isPrimary
					isLarge
					onClick={ this.openModal }
				>
					{ __( 'Modify Parameters' ) }
				</Button>

				{ this.state.isOpen && (
					<Modal
						title={ __( 'Import from JSON' ) }
						className="visualizer-json-query-modal"
						shouldCloseOnClickOutside={ false }
						onRequestClose={ () => {
							this.setState({
								isOpen: false,
								isTableRendered: false
							});
						} }
					>
						<PanelBody
							title={ __( 'Step 1: Specify the JSON endpoint/URL' ) }
							opened={ this.state.isFirstStepOpen }
							onToggle={ () => this.onToggle( 'isFirstStepOpen' ) }
						>
							<p>{ __( 'If you want to add authentication, add headers to the endpoint or change the request in any way, please refer to our document here:' ) }</p>

							<p>
								<ExternalLink href="https://docs.themeisle.com/article/1043-visualizer-how-to-extend-rest-endpoints-with-json-response">
									{ __( 'How to extend REST endpoints with JSON response' ) }
								</ExternalLink>
							</p>

							<TextControl
								placeholder={ __( 'Please enter the URL of your JSON file' ) }
								value={ this.props.chart['visualizer-json-url'] ? this.props.chart['visualizer-json-url'] : '' }
								onChange={ this.props.editJSONURL }
							/>

							<Button
								isPrimary
								isLarge
								isBusy={ this.state.isLoading }
								disabled={ this.state.isLoading }
								onClick={ this.getJSONRoot }
							>
								{ __( 'Fetch Endpoint' ) }
							</Button>
						</PanelBody>

						<PanelBody
							title={ __( 'Step 2: Choose the JSON root' ) }
							initialOpen={ false }
							opened={ this.state.isSecondStepOpen }
							onToggle={ () => this.onToggle( 'isSecondStepOpen' ) }
						>
							<SelectControl
								value={ this.props.chart['visualizer-json-root'] }
								options={ this.state.endpointRoots }
								onChange={ this.props.editJSONRoot }
							/>

							<Button
								isPrimary
								isLarge
								isBusy={ this.state.isLoading }
								disabled={ this.state.isLoading }
								onClick={ this.getJSONData }
							>
								{ __( 'Parse Endpoint' ) }
							</Button>
						</PanelBody>

						<PanelBody
							title={ __( 'Step 3: Specify miscellaneous parameters' ) }
							initialOpen={ false }
							opened={ this.state.isThirdStepOpen }
							onToggle={ () => this.onToggle( 'isThirdStepOpen' ) }
						>
							{ ( 'community' !== visualizerLocalize.isPro ) ? (
								<SelectControl
									value={ this.props.chart['visualizer-json-paging'] || 0 }
									options={ this.state.endpointPaging }
									onChange={ this.props.editJSONPaging }
								/>
							) : (
								<p>{ __( 'Enable this feature in PRO version!' ) }</p>
							) }
						</PanelBody>

						<PanelBody
							title={ __( 'Step 4: Select the data to display in the chart' ) }
							initialOpen={ false }
							opened={ this.state.isFourthStepOpen }
							onToggle={ () => this.onToggle( 'isFourthStepOpen' ) }
						>
							<ul>
								<li>{ __( 'Select whether to include the data in the chart. Each column selected will form one series.' ) }</li>
								<li>{ __( 'If a column is selected to be included, specify its data type.' ) }</li>
								<li>{ __( 'You can use drag/drop to reorder the columns but this column position is not saved. So when you reload the table, you may have to reorder again.' ) }</li>
								<li>{ __( 'You can select any number of columns but the chart type selected will determine how many will display in the chart.' ) }</li>
							</ul>

							<div id="visualizer-json-query-table"></div>

							<Button
								isPrimary
								isLarge
								isBusy={ this.state.isLoading }
								disabled={ this.state.isLoading }
								onClick={ this.getTableData }
							>
								{ __( 'Save & Show Chart' ) }
							</Button>
						</PanelBody>
					</Modal>
				)}

			</PanelBody>
		);
	}
}

export default JSONImport;
