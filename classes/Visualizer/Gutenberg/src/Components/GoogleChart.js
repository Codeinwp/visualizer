/**
 * External dependencies
 */
import { Chart } from 'react-google-charts';

/**
 * WordPress dependencies
 */
const {
	Component,
	createRef
} = wp.element;

/**
 * Google chart that draws into its own element, even inside the iframed editor canvas.
 */
class GoogleChart extends Component {
	constructor() {
		super( ...arguments );

		this.container = createRef();
		this.bindContainer = this.bindContainer.bind( this );
	}

	/**
	 * Google Charts resolves a container id in its own window, which is not the editor canvas.
	 *
	 * @param {{ draw: ( container?: HTMLElement ) => void }} chartWrapper Wrapper created by react-google-charts.
	 */
	bindContainer( chartWrapper ) {
		const draw = chartWrapper.draw.bind( chartWrapper );
		chartWrapper.draw = ( container ) => draw( container || this.container.current );
	}

	render() {
		return (
			<Chart
				{ ...this.props }
				rootProps={ { ref: this.container } }
				getChartWrapper={ this.bindContainer }
			/>
		);
	}
}

export default GoogleChart;
