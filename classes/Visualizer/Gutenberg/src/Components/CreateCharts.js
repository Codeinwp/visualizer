/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
const { startCase } = lodash;

const { __ } = wp.i18n;

const { apiFetch } = wp;

const {
	Component,
	Fragment
} = wp.element;

const {
	Button,
	Dashicon,
	Placeholder,
	Spinner
} = wp.components;

class CreateCharts extends Component {
	constructor() {
		super( ...arguments );
	}

	render() {

		return (
			<div className="visualizer-settings__charts">
				<div className="visualizer-settings__charts-grid">

					{ Object.keys( visualizerLocalize.charts ).map( i => {
						return (
							<div className="visualizer-settings__charts-single">
								<div className="visualizer-settings__charts-title">
									{ visualizerLocalize.charts[i].name }
								</div>

								<div
									className={ classnames(
										'visualizer-settings__create-charts',
										`type-box-${ i }`
									) }
								>
								</div>

								<div
									className="visualizer-settings__charts-controls"
									title={ __( 'Insert Chart' ) }

								>
									<Dashicon icon="upload"></Dashicon>
								</div>
							</div>
						);
					}) }

				</div>
			</div>
		);
	}
}

export default CreateCharts;
