import isPlainObject from 'is-plain-object';

import deepFilter from 'deep-filter';

// Import WordPress dependencies
const {
	apiFetch
} = wp;

// Format Date of Chart Data
export const formatDate = ( data ) => {
	Object.keys( data['visualizer-series']).map( i => {
		if ( data['visualizer-series'][i].type !== undefined && 'date' === data['visualizer-series'][i].type ) {
			Object.keys( data['visualizer-data']).map( o => {
				return data['visualizer-data'][o][i] = new Date( data['visualizer-data'][o][i]);
			});
		}
	});
	return data;
};

// A fork of deep-compact package as it had some issues
// NOTE: This method is likely to create problems.
// Problem Scenario #1:
// - A table has 5 columns (series). Say the 1st column is Date and others are Numbers.
// - If the 1st columns format (series.format) is provided, DataTable.js gets 6 (0-5) series.
// - BUT if the 1st columns format (series.format) is empty, DataTable.js gets 5 (1-4) series.
// That is why when sending options to DataTable.js, filterChart method has not been used.
const notEmpty = value => {
	let key;

	if ( Array.isArray( value ) ) {
		return 0 < value.length;
	}

	if ( isPlainObject( value ) ) {
		for ( key in value ) {
			return true;
		}

		return false;
	}

	if ( 'string' === typeof value ) {
		return 0 < value.length;
	}

	return null != value;
};

export const compact = value => deepFilter( value, notEmpty );

// Remove chart size-related properies for Chart List
export const filterCharts = value => {
	value.width = '';
	value.height = '';
	value.backgroundColor = {};
	value.chartArea = {};

	return compact( value, notEmpty );
};

// Check if JSON object is valid or not
export const isValidJSON = obj => {
	try {
		JSON.parse( obj );
	} catch ( e ) {
		return false;
	}
	return true;
};

// Convert CSV data to Array
// Source: https://www.bennadel.com/blog/1504-ask-ben-parsing-csv-strings-with-javascript-exec-regular-expression-command.htm
export const CSVToArray = ( strData, strDelimiter ) => {
	strDelimiter = ( strDelimiter || ',' );

	const objPattern = new RegExp(
		( '(\\' + strDelimiter + '|\\r?\\n|\\r|^)' +  '(?:\'([^\']*(?:\'\'[^\']*)*)\'|' + '([^\'\\' + strDelimiter + '\\r\\n]*))' ), 'gi' );

	const arrData = [ [] ];

	let arrMatches = null;

	while ( arrMatches = objPattern.exec( strData ) ) {

		const strMatchedDelimiter = arrMatches[ 1 ];

		if ( strMatchedDelimiter.length && strMatchedDelimiter !== strDelimiter ) {
			arrData.push([]);
		}

		let strMatchedValue;

		if ( arrMatches[ 2 ]) {
			strMatchedValue = arrMatches[ 2 ].replace( new RegExp( '\'\'', 'g' ), '\'' );
		} else {
			strMatchedValue = arrMatches[ 3 ];
		}

		arrData[ arrData.length - 1 ].push( strMatchedValue );
	}

	return ( arrData );
};


export const isChecked = ( settings, param ) => {
    return true === settings[param] || 'true' === settings[param] || '1' === settings[param] || 1 === settings[param];
};


export const formatData = ( chart ) => {
    let library = chart['visualizer-chart-library'];

    switch ( library ) {
        case 'GoogleCharts':
            return formatDataForGoogleCharts( chart );
    }
};

export const formatDataForGoogleCharts = ( chart ) => {
    let settings = chart['visualizer-settings'];
    let type = chart['visualizer-chart-type'];
    let series = chart['visualizer-series'];

    let formatters = [];

    if ( settings.series ) {
        switch ( type ) {
            case 'tabular':
                for  ( let i in settings.series ) {
                    i = parseInt( i );
                    if ( ! series[i + 1]) {
                        continue;
                    }
                    if ( settings.series[i].format && '' !== settings.series[i].format ) {
                        let col = i + 1;
                        let formatter = getFormatterForGoogle( series[i + 1].type );
                        if ( formatter ) {
                            formatters.push({ type: formatter, options: { pattern: settings.series[i].format }, column: col });
                        }
                    }
                }
                break;
            default:
                for ( let i = 0; i < settings.series.length; i++ ) {
                    if ( ! series[i + 1] || 'undefined' === typeof settings.series[i]) {
                        continue;
                    }
                    if ( settings.series[i].format && '' !== settings.series[i].format ) {
                        let col = i + 1;
                        let formatter = getFormatterForGoogle( series[i + 1].type );
                        if ( formatter ) {
                            formatters.push({ type: formatter, options: { pattern: settings.series[i].format }, column: col });
                        }
                    }
                }
                break;
        }
    } else if ( 'pie' === type && settings.format && '' !== settings.format ) {
        formatters.push({ type: getFormatterForGoogle( 'number' ), options: { pattern: settings.format }, column: 1 });
    }

    if ( settings.hAxis && series[0]) {
        let formatter = getFormatterForGoogle( series[0].type );
        if ( formatter ) {
            formatters.push({ type: formatter, options: { pattern: settings.hAxis.format }, column: 0 });
        }
    }

    return formatters;
};

export const getFormatterForGoogle = ( dataType ) => {
    switch ( dataType ) {
        case 'number':
            return 'NumberFormat';
        case 'date':
        case 'datetime':
        case 'timeofday':
            return 'DateFormat';
    }
    return null;
};

export const getColorCode = ( color ) => {
	if ( -1 === color.indexOf( '#' ) ) {
		let getCssVar = color.match( /\((.*)\)/ ).pop();
		if ( getCssVar ) {
			let style = getComputedStyle( document.body );
			return style.getPropertyValue( getCssVar );
		}
	}
	return color;
};

// Google Chart Packages
export const googleChartPackages = [ 'corechart', 'geochart', 'gauge', 'table', 'timeline', 'controls' ];

/**
 * This function extends the wp.media.view.MediaFrame class to create a custom frame for Visualizer Charts creation/editing.
 *
 * @returns {wp.media.view.MediaFrame} The extended MediaFrame object.
 *
 * @example
 *
 * const popupBuilder = buildChartPopup;
 * const popup = new popupBuilder();
 * popup.open();
 */
export const buildChartPopup = () => {
    return wp.media.view.MediaFrame.extend(
        {
            initialize: function() {
                const self = this;

                _.defaults(
                    self.options, {
                        action: '',
                        id: 'visualizer',
                        state: 'iframe:visualizer',
                        title: 'Visualizer'
                    }
                );

                wp.media.view.MediaFrame.prototype.initialize.apply( self, arguments );

                wp.media.view.settings.tab = 'Visualizer';
                wp.media.view.settings.tabUrl = self.options.action;
                self.createIframeStates();
            },

            createIframeStates: function( passedOptions ) {
                const self = this;
                wp.media.view.MediaFrame.prototype.createIframeStates.apply( self, arguments );

                self.state( self.options.state ).set(
                    _.defaults(
                        {
                            tab: self.options.id,
                            src: self.options.action + '&tab=' + self.options.id,
                            title: self.options.title,
                            content: 'iframe',
                            menu: 'default'
                        }, passedOptions
                    )
                );

            },

            open: function() {
                try {
                    wp.media.view.MediaFrame.prototype.open.apply( this, arguments );
                } catch ( error ) {
                    console.error( error );
                }
            }
        }
    );
};

/**
 * Try to get the chart data from the server.
 *
 * If the status is not 'publish', it will retry.
 *
 * @param {number|string} chartId The chart ID.
 * @returns {Promise<{result: Object, status: string}>} The chart data and status.
 */
export async function tryGetPublishedChartData( chartId ) {
    let result = await apiFetch({ path: `wp/v2/visualizer/${chartId}` });
    const numRetries = 8;

    let attempt = 0;
    while (
        result &&
        undefined !== result.status &&
        'publish' !== result.status &&
        numRetries > attempt
    ) {
        await new Promise( resolve => setTimeout( resolve, 750 ) );
        result = await apiFetch({ path: `wp/v2/visualizer/${chartId}` });
        attempt++;
    }

    return {
        result: result,
        chartStatus: result.status ? result.status : 'auto-draft'
    };
}
