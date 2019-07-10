/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;

const { apiFetch } = wp;

const {
	Component,
	Fragment
} = wp.element;

const {
	Button,
	PanelBody,
	SelectControl
} = wp.components;

class ChartPermissions extends Component {
	constructor() {
		super( ...arguments );

		this.getPermissionData = this.getPermissionData.bind( this );

		this.state = {
			users: [],
			roles: []
		};
	}

	async componentDidMount() {
		if ( 'business' === visualizerLocalize.isPro ) {
			const permissions = this.props.chart['visualizer-permissions'];

			if ( permissions.permissions !== undefined ) {
				if ( permissions.permissions.read !== undefined && permissions.permissions.edit !== undefined ) {
					if ( 'users' === permissions.permissions.read || 'users' === permissions.permissions.edit ) {
						let data = await apiFetch({ path: '/visualizer/v1/get-permission-data?type=users' });
						this.setState({ users: data });
					}

					if ( 'roles' === permissions.permissions.read || 'roles' === permissions.permissions.edit ) {
						let data = await apiFetch({ path: '/visualizer/v1/get-permission-data?type=roles' });
						this.setState({ roles: data });
					}
				}
			}
		}
	}

	async getPermissionData( type ) {
		if ( 'business' === visualizerLocalize.isPro ) {
			if ( 'users' === type && 0 === ( this.state.users ).length ) {
				let data = await apiFetch({ path: `/visualizer/v1/get-permission-data?type=${type}` });
				this.setState({ users: data });
			}

			if ( 'roles' === type && 0 === ( this.state.roles ).length ) {
				let data = await apiFetch({ path: `/visualizer/v1/get-permission-data?type=${type}` });
				this.setState({ roles: data });
			}
		}
	}

	render() {

		let permissions;

		if ( 'business' === visualizerLocalize.isPro ) {
			permissions = this.props.chart['visualizer-permissions'];
		}

		return (
			<Fragment>

				{ ( 'business' === visualizerLocalize.isPro ) ?
					<PanelBody
						title={ __( 'Who can see this chart?' ) }
						initialOpen={ false }
					>

						<SelectControl
							label={ __( 'Select who can view the chart on the front-end.' ) }
							value={ permissions.permissions.read }
							options={ [
								{ value: 'all', label: 'All Users' },
								{ value: 'users', label: 'Select Users' },
								{ value: 'roles', label: 'Select Roles' }
							] }
							onChange={ e => {
								permissions.permissions.read = e;
								this.props.edit( permissions );
								if ( 'users' === e || 'roles' === e ) {
									this.getPermissionData( e );
								}
							} }
						/>

						{ ( 'users' === permissions.permissions.read || 'roles' === permissions.permissions.read ) &&
							<SelectControl
								multiple
								value={ permissions.permissions['read-specific'] }
								options={ 'users' === permissions.permissions.read && this.state.users || 'roles' === permissions.permissions.read && this.state.roles }
								onChange={ e => {
									permissions.permissions['read-specific'] = e;
									this.props.edit( permissions );
								} }
							/>
						}

					</PanelBody>				:
					<PanelBody
						title={ __( 'Who can see this chart?' ) }
						icon="lock"
						initialOpen={ false }
					>

						<p>{ __( 'Enable this feature in BUSINESS version!' ) }</p>

						<Button
							isPrimary
							href={ visualizerLocalize.proTeaser }
							target="_blank"
						>
							{ __( 'Buy Now' ) }
						</Button>

					</PanelBody>
				}

				{ ( 'business' === visualizerLocalize.isPro ) ?
					<PanelBody
						title={ __( 'Who can edit this chart?' ) }
						initialOpen={ false }
					>

						<SelectControl
							label={ __( 'Select who can edit the chart on the front-end.' ) }
							value={ permissions.permissions.edit }
							options={ [
								{ value: 'all', label: 'All Users' },
								{ value: 'users', label: 'Select Users' },
								{ value: 'roles', label: 'Select Roles' }
							] }
							onChange={ e => {
								permissions.permissions.edit = e;
								this.props.edit( permissions );
								if ( 'users' === e || 'roles' === e ) {
									this.getPermissionData( e );
								}
							} }
						/>

						{ ( 'users' === permissions.permissions.edit || 'roles' === permissions.permissions.edit ) &&
							<SelectControl
								multiple
								value={ permissions.permissions['edit-specific'] }
								options={ 'users' === permissions.permissions.edit && this.state.users || 'roles' === permissions.permissions.edit && this.state.roles }
								onChange={ e => {
									permissions.permissions['edit-specific'] = e;
									this.props.edit( permissions );
								} }
							/>
						}

					</PanelBody>				:
					<PanelBody
						title={ __( 'Who can edit this chart?' ) }
						icon="lock"
						initialOpen={ false }
					>

						<p>{ __( 'Enable this feature in BUSINESS version!' ) }</p>

						<Button
							isPrimary
							href={ visualizerLocalize.proTeaser }
							target="_blank"
						>
							{ __( 'Buy Now' ) }
						</Button>

					</PanelBody>
				}

			</Fragment>
		);
	}
}

export default ChartPermissions;
