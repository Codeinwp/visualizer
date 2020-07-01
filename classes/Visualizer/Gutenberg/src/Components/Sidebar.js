/**
 * External dependencies
 */
import GeneralSettings from './Sidebar/GeneralSettings.js';
import InstanceSettings from './Sidebar/InstanceSettings.js';
import HorizontalAxisSettings from './Sidebar/HorizontalAxisSettings.js';
import VerticalAxisSettings from './Sidebar/VerticalAxisSettings.js';
import PieSettings from './Sidebar/PieSettings.js';
import ResidueSettings from './Sidebar/ResidueSettings.js';
import LinesSettings from './Sidebar/LinesSettings.js';
import BarsSettings from './Sidebar/BarsSettings.js';
import CandlesSettings from './Sidebar/CandlesSettings.js';
import MapSettings from './Sidebar/MapSettings.js';
import ColorAxis from './Sidebar/ColorAxis.js';
import SizeAxis from './Sidebar/SizeAxis.js';
import MagnifyingGlass from './Sidebar/MagnifyingGlass.js';
import GaugeSettings from './Sidebar/GaugeSettings.js';
import TimelineSettings from './Sidebar/TimelineSettings.js';
import TableSettings from './Sidebar/TableSettings.js';
import RowCellSettings from './Sidebar/RowCellSettings.js';
import ComboSettings from './Sidebar/ComboSettings.js';
import SeriesSettings from './Sidebar/SeriesSettings.js';
import SlicesSettings from './Sidebar/SlicesSettings.js';
import BubbleSettings from './Sidebar/BubbleSettings.js';
import ColumnSettings from './Sidebar/ColumnSettings.js';
import LayoutAndChartArea from './Sidebar/LayoutAndChartArea.js';
import FrontendActions from './Sidebar/FrontendActions.js';
import ManualConfiguration from './Sidebar/ManualConfiguration.js';

/**
 * WordPress dependencies
 */
const {
	Component,
	Fragment
} = wp.element;

class Sidebar extends Component {
	constructor() {
		super( ...arguments );
	}

	render() {

		const type = this.props.chart['visualizer-chart-type'];
		const library = this.props.chart['visualizer-chart-library'];

		return (
			<Fragment>

				<InstanceSettings chart={ this.props.chart } attributes={ this.props.attributes } edit={ this.props.edit } />

				<GeneralSettings chart={ this.props.chart } edit={ this.props.edit } />

				{ ( -1 >= [ 'tabular', 'dataTable', 'gauge', 'geo', 'pie', 'timeline' ].indexOf( type ) ) && (
					<HorizontalAxisSettings chart={ this.props.chart } edit={ this.props.edit } />
				) }

				{ ( -1 >= [ 'tabular', 'dataTable', 'gauge', 'geo', 'pie', 'timeline' ].indexOf( type ) ) && (
					<VerticalAxisSettings chart={ this.props.chart } edit={ this.props.edit } />
				) }

				{ ( 0 <= [ 'pie' ].indexOf( type ) ) && (
					<Fragment>

						<PieSettings chart={ this.props.chart } edit={ this.props.edit } />

						<ResidueSettings chart={ this.props.chart } edit={ this.props.edit } />

					</Fragment>
				) }

				{ ( 0 <= [ 'area', 'scatter', 'line' ].indexOf( type ) ) && (
					<LinesSettings chart={ this.props.chart } edit={ this.props.edit } />
				) }

				{ ( 0 <= [ 'bar', 'column' ].indexOf( type ) ) && (
					<BarsSettings chart={ this.props.chart } edit={ this.props.edit } />
				) }

				{ ( 0 <= [ 'candlestick' ].indexOf( type ) ) && (
					<CandlesSettings chart={ this.props.chart } edit={ this.props.edit } />
				) }

				{ ( 0 <= [ 'geo' ].indexOf( type ) ) && (
					<Fragment>

						<MapSettings chart={ this.props.chart } edit={ this.props.edit } />

						<ColorAxis chart={ this.props.chart } edit={ this.props.edit } />

						<SizeAxis chart={ this.props.chart } edit={ this.props.edit } />

						<MagnifyingGlass chart={ this.props.chart } edit={ this.props.edit } />

					</Fragment>
				) }

				{ ( 0 <= [ 'gauge' ].indexOf( type ) ) && (
					<GaugeSettings chart={ this.props.chart } edit={ this.props.edit } />
				) }

				{ ( 0 <= [ 'timeline' ].indexOf( type ) ) && (
					<TimelineSettings chart={ this.props.chart } edit={ this.props.edit } />
				) }

				{ ( 0 <= [ 'tabular', 'dataTable' ].indexOf( type ) ) && (
					<Fragment>

						<TableSettings chart={ this.props.chart } edit={ this.props.edit } />

						<RowCellSettings chart={ this.props.chart } edit={ this.props.edit } />

					</Fragment>
				) }

				{ ( 0 <= [ 'combo' ].indexOf( type ) ) && (
					<ComboSettings chart={ this.props.chart } edit={ this.props.edit } />
				) }

				{ ( -1 >= [ 'timeline', 'bubble', 'gauge', 'geo', 'pie', 'tabular', 'dataTable' ].indexOf( type ) ) && (
					<SeriesSettings chart={ this.props.chart } edit={ this.props.edit } />
				) }

				{ ( 'tabular' === type && 'GoogleCharts' === library ) && (
					<SeriesSettings chart={ this.props.chart } edit={ this.props.edit } />
				) }

				{ ( 0 <= [ 'bubble' ].indexOf( type ) ) && (
					<BubbleSettings chart={ this.props.chart } edit={ this.props.edit } />
				) }


				{ ( 0 <= [ 'pie' ].indexOf( type ) ) && (
					<SlicesSettings chart={ this.props.chart } edit={ this.props.edit } />
				) }

				{ ( 'DataTable' === library ) && (
					<ColumnSettings chart={ this.props.chart } edit={ this.props.edit } />
				) }

				{ ( 'DataTable' !== library ) && (
					<LayoutAndChartArea chart={ this.props.chart } edit={ this.props.edit } />
				) }

				<FrontendActions chart={ this.props.chart } edit={ this.props.edit } />

				{ ( 'DataTable' !== library ) && (
					<ManualConfiguration chart={ this.props.chart } edit={ this.props.edit } />
				) }
			</Fragment>
		);
	}
}

export default Sidebar;
