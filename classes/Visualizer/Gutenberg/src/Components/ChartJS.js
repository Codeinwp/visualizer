/**
 * External dependencies
 */
import uuidv4 from 'uuid';

import {merge} from 'lodash';

import { defaults, Scatter, Bubble, Polar, Radar, HorizontalBar, Bar, Line, Pie, Doughnut } from 'react-chartjs-2';

/**
 * WordPress dependencies
 */
const {
	Component,
	Fragment
} = wp.element;

class ChartJS extends Component {
	constructor() {
		super( ...arguments );

		this.initChart = this.initChart.bind( this );
		this.renderChart = this.renderChart.bind( this );
		this.formatDatum = this.formatDatum.bind( this );
		this.formatData = this.formatData.bind( this );
		this.handleSettings = this.handleSettings.bind( this );
		this.dataRenderer = this.dataRenderer.bind( this );
		this.handleAxes = this.handleAxes.bind( this );
		this.configureAxes = this.configureAxes.bind( this );
		this.handlePieSeriesSettings = this.handlePieSeriesSettings.bind( this );
		this.handleSeriesSettings = this.handleSeriesSettings.bind( this );

		this.chart;
		this.uniqueId = uuidv4();
	}

	componentDidMount() {
	};

	componentWillUnmount() {
	}

	componentDidUpdate( prevProps ) {
	}

	initChart() {
	}

	render() {
        this.chart = this.renderChart();
		return (
			<Fragment>
        { this.chart }
			</Fragment>
		);
    }

	renderChart() {
        const data = this.dataRenderer();

		const settings = this.props.options;
        let id = `chartjs-${ this.props.id }-${ this.uniqueId }`;
        let type = this.props.chartType;
/*
        switch ( this.props.chartType ) {
            case 'line':
                return <Line data={ data } id={ id } />;
            case 'scatter':
                return <Scatter data={ data } id={ id } />;
            case 'radar':
                return <Radar data={ data } id={ id } />;
            case 'polar':
                return <Polar data={ data } id={ id } />;
            case 'bubble':
                return <Bubble data={ data } id={ id } />;
            case 'column':
                return <Bar data={ data } id={ id } />;
            case 'bar':
                return <HorizontalBar data={ data } id={ id  } />;
            case 'pie':
                // donut is not a setting but a separate chart type.
                if ( 'undefined' !== typeof settings['custom'] && 'true' === settings['custom']['donut']) {
                    return <Doughnut data={ data } id={ id } />;
                }
                return <Pie data={ data } id={ id } />;
        }
*/

        switch ( this.props.chartType ) {
            case 'column':
                type = 'bar';
            case 'bar':
                type = 'horizontalBar';
            case 'pie':
                // donut is not a setting but a separate chart type.
                if ( 'undefined' !== typeof settings['custom'] && 'true' === settings['custom']['donut']) {
                    type = 'doughnut';
                }
        }

        const CustomChart = type.substring( 0, 1 ).toUpperCase() + type.substring( 1 );

		return <CustomChart data={ data } id={ id } />;
	}

    /**************** the below functions are taken from js/render-chartjs.js and formatted as per react and eslint *********************/
    dataRenderer() {
        let rows = [];
        let datasets = [];
        let labels = [];

        for ( let i = 0; i < this.props.data.length; i++ ) {
			let row = [];
			for ( let j = 0; j < this.props.series.length; j++ ) {
                let data = this.props.data;
				if ( 'date' === this.props.series[j].type || 'datetime' === this.props.series[j].type ) {
					date = new Date( this.props.data[i][j]);
					data[i][j] = null;
					if ( '[object Date]' === Object.prototype.toString.call( date ) ) {
						if ( ! isNaN( date.getTime() ) ) {
							data[i][j] = date;
						}
					}
				}
                row.push( this.formatData( data[i][j], j, this.props.options, this.props.series ) );
			}
            rows.push( row );
        }

        // transpose
        for ( let j = 0; j < this.props.series.length; j++ ) {
            let row = [];
            for ( let i = 0; i < rows.length; i++ ) {
                if ( 0 === j ) {
                    labels.push( rows[i][j]);
                } else {
                    row.push( rows[i][j]);
                }
            }
            if ( 0 < row.length ) {
                let $attributes = {label: this.props.series[j].label, data: row};
                switch ( this.props.chartType ) {
                    case 'pie':
                    case 'polarArea':
                        merge( $attributes, {label: labels});
                        this.handlePieSeriesSettings( $attributes, rows, this.props.options );
                        break;
                    default:
                        this.handleSeriesSettings( $attributes, j - 1, this.props.options );
                }
                datasets.push( $attributes );
            }
        }

        this.handleSettings( this.props.options );

        return {
                labels: labels,
                datasets: datasets
            };
    }

    formatDatum( datum, format, type ) {
        if ( '' === format || null === format || 'undefined' === typeof format ) {
            return datum;
        }
        // if there is no type, this is probably coming from the axes formatting.
        let removeDollar = true;
        if ( 'undefined' === typeof type || null === type ) {
            // we will determine type on the basis of the presence or absence of #.
            type = 'date';
            if ( -1 !== format.indexOf( '#' ) ) {
                type = 'number';
            }
            removeDollar = false;
        }

        switch ( type ) {
            case 'number':
                // numeral.js works on 0 instead of # so we just replace that in the ICU pattern set.
                format = format.replace( /#/g, '0' );
                // we also replace all instance of '$' as that is more relevant for ticks.
                if ( removeDollar ) {
                    format = format.replace( /\$/g, '' );
                }
                datum = numeral( datum ).format( format );
                break;
            case 'date':
            case 'datetime':
            case 'timeofday':
                datum = moment( datum ).format( format );
                break;
        }
        return datum;
    }

    formatData( datum, j, settings, series ) {
        j = j - 1;
        let format = 'undefined' !== typeof settings.series  && 'undefined' !== typeof settings.series[j] ? settings.series[j].format : '';
        return this.formatDatum( datum, format, series[j + 1].type );
    }

    handleSettings( settings ) {
        if ( 'undefined' === typeof settings ) {
            return;
        }

        // handle some defaults/idiosyncrasies.
        if ( 'undefined' !== typeof settings['animation'] && 0 === parseInt( settings['animation']['duration']) ) {
            settings['animation']['duration'] = 1000;
        }

        if ( 'undefined' !== typeof settings['title'] && '' !== settings['title']['text']) {
            settings['title']['display'] = true;
        }

        if ( 'undefined' !== typeof settings['tooltip'] && 'undefined' !== typeof settings['tooltip']['intersect']) {
            // jshint ignore:line
            settings['tooltip']['intersect'] = true == settings['tooltip']['intersect'] || 1 === parseInt( settings['tooltip']['intersect']);  // jshint ignore:line
        }

        if ( 'undefined' !== typeof settings['fontName'] && '' !== settings['fontName']) {
            defaults.global.defaultFontFamily = settings['fontName'];
            delete settings['fontName'];
        }

        if ( 'undefined' !== typeof settings['fontSize'] && '' !== settings['fontSize']) {
            defaults.global.defaultFontSize = settings['fontSize'];
            delete settings['fontSize'];
        }

        // handle legend defaults.
        if ( 'undefined' !== typeof settings['legend'] &&  'undefined' !== typeof settings['legend']['labels']) {
            for ( let i in settings['legend']['labels']) {
                if ( 'undefined' !== settings['legend']['labels'][i] && '' === settings['legend']['labels'][i]) {
                    delete settings['legend']['labels'][i];
                }
            }
        }

        this.handleAxes( settings );
    }

    handleAxes( settings ) {
        if ( 'undefined' !== typeof settings['yAxes'] &&  'undefined' !== typeof settings['xAxes']) {
            // stacking has to be defined on both axes.
            if ( 'undefined' !== typeof settings['yAxes']['stacked_bool']) {
                settings['xAxes']['stacked_bool'] = 'true';
            }
            if ( 'undefined' !== typeof settings['xAxes']['stacked_bool']) {
                settings['yAxes']['stacked_bool'] = 'true';
            }
        }
        this.configureAxes( settings, 'yAxes' );
        this.configureAxes( settings, 'xAxes' );
    }

    configureAxes( settings, axis ) {
        if ( 'undefined' !== typeof settings[axis]) {
            let $features = {};
            for ( let i in settings[axis]) {
                let $o = {};
                if ( Array.isArray( settings[axis][i]) || 'object' === typeof settings[axis][i]) {
                    for ( let j in settings[axis][i]) {
                        let $val = '';
                        if ( 'labelString' === j ) {
                            $o['display'] = true;
                            $val = settings[axis][i][j];
                        } else if ( 'ticks' === i ) {
                            // number values under ticks need to be converted to numbers or the library throws a JS error.
                            $val = parseFloat( settings[axis][i][j]);
                            if ( isNaN( $val ) ) {
                                $val = '';
                            }
                        } else {
                            $val = settings[axis][i][j];
                        }
                        if ( '' !== $val ) {
                            $o[j] = $val;
                        }
                    }
                } else {
                    // usually for attributes that have primitive values.
                    let array = i.split( '_' );
                    let dataType = 'string';
                    let dataValue = settings[axis][i];
                    if ( 2 === array.length ) {
                        dataType = array[1];
                    }

                    if ( '' === settings[axis][i]) {
                        continue;
                    }
                    switch ( dataType ) {
                        case 'bool':
                            dataValue = 'true' === dataValue ? true : false;
                            break;
                        case 'int':
                            dataValue = parseFloat( dataValue );
                            break;
                    }
                    $o = dataValue;
                    // remove the type suffix to get the name of the setting.
                    i = i.replace( /_bool/g, '' ).replace( /_int/g, '' );
                }
                $features[i] = $o;
            }
            let $scales = {};
            $scales['scales'] = {};
            $scales['scales'][axis] = [];
            if ( 'undefined' !== typeof settings['scales'] && 'undefined' === typeof settings[axis + 'set']) {
                $scales['scales'] = settings['scales'];
                if ( 'undefined' !== typeof settings['scales'][axis]) {
                    $scales['scales'][axis] = settings['scales'][axis];
                }
            }
            if ( 'undefined' === typeof $scales['scales'][axis]) {
                $scales['scales'][axis] = [];
            }
            let $axis = $scales['scales'][axis];

            $axis.push( $features );
            merge( settings, $scales );

            // to prevent duplication, indicates that the axis has been set.
            let $custom = {};
            $custom[axis + 'set'] = 'yes';
            merge( settings, $custom );
        }

        // format the axes labels.
        if ( 'undefined' !== typeof settings[axis + '_format'] && '' !== settings[axis + '_format']) {
            let format = settings[axis + '_format'];
            switch ( axis ) {
                case 'xAxes':
                    settings.scales.xAxes[0].ticks.callback = function( value, index, values ) {
                        return this.formatDatum( value, format );
                    };
                    break;
                case 'yAxes':
                    settings.scales.yAxes[0].ticks.callback = function( value, index, values ) {
                        return this.formatDatum( value, format );
                    };
                    break;
            }
            delete settings[axis + '_format'];
        }
        delete settings[axis];
    }

    handlePieSeriesSettings( $attributes, rows, settings ) {
        if ( 'undefined' === typeof settings.slices ) {
            return;
        }

        let atts = [];

        // collect all the types of attributes
        for ( let j in settings.slices[0]) {
            // weight screws up the rendering for some reason, so we will ignore it.
            if ( 'weight' === j ) {
                continue;
            }
            atts.push( j );
        }

        for ( let j = 0; j < atts.length; j++ ) {
            let values = [];
            for ( let i = 0; i < rows.length; i++ ) {
                if ( 'undefined' !== typeof settings.slices[i] && 'undefined' !== typeof settings.slices[i][atts[j]]) {
                    values.push( settings.slices[i][atts[j]]);
                }
            }
            let object = {};
            object[ atts[ j ] ] = values;
            merge( $attributes, object );
        }
    }

    handleSeriesSettings( $attributes, j, settings ) {
        if ( 'undefined' === typeof settings.series || 'undefined' === typeof settings.series[j]) {
            return;
        }
        for ( let i in settings.series[j]) {
            let $attribute = {};
            $attribute[i] = settings.series[j][i];
            merge( $attributes, $attribute );
        }
    }

/**/
}

export default ChartJS;
