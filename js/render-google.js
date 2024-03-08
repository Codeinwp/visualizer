/* global google */
/* global visualizer */
/* global console */

// this will store the images for each chart rendered.
var __visualizer_chart_images   = [];
var chartWrapperError = [];
var isResizeRequest = false;

(function($) {
	var gv;
    var all_charts, objects;
    // so that we know which charts belong to our library.
    var rendered_charts = [];

	function renderChart(id) {
        
        if ( ! all_charts || 0 === Object.keys( all_charts ).length ) {
            return;
        }

        var chart = all_charts[id];
        var hasAnnotation = false;

        if ( ! chart ) {
            return;
        }

        // re-render the chart only if it doesn't have annotations and it is on the front-end
        // this is to prevent the chart from showing "All series on a given axis must be of the same data type" during resize.
        // remember, some charts do not support annotations so they should not be included in this.
        var no_annotation_charts = ['tabular', 'timeline', 'gauge', 'geo', 'bubble', 'candlestick'];
        if ( undefined !== chart.settings && undefined !== chart.settings.series && undefined === chart.settings.series.length ) {
            var chartSeries = [];
            var chartSeriesValue = Object.values( chart.settings.series );
            $.each( Object.keys( chart.settings.series ), function( index, element ) {
                chartSeries[element] = chartSeriesValue[index];
            } );
            chart.settings.series = chartSeries;
        }
        if(id !== 'canvas' && typeof chart.series !== 'undefined' && typeof chart.settings.series !== 'undefined' && ! no_annotation_charts.includes(chart.type) ) {
            hasAnnotation = chart.series.length - chart.settings.series.length > 1;
        }

        if(! hasAnnotation){
            renderSpecificChart(id, all_charts[id]);
        }
    }

    function renderSpecificChart(id, chart) {
        var render, container, series, data, table, settings, i, j, row, date, axis, property, format, formatter;

        if( chart.library !== 'google' ) {
            return;
        }
      
        // Bail if the chart is already rendered or is being rendered.
        if ( ! window.isResizeRequest && ( $('#' + id).hasClass('visualizer-chart-loaded') || ( 'canvas' !== id && $('#' + id).children( ':not(.loader, style)' ).length > 0 ) ) ) {
            return;
        }
        rendered_charts[id] = 'yes';

        series = chart.series;
		data = chart.data;
		settings = chart.settings;

        container = document.getElementById(id);
        if (container == null) {
            return;
        }
		table = new gv.DataTable({cols: series});

		render = objects[id] || null;
		if (!render) {
            switch (chart.type) {
                case "tabular":
                    render = "Table";
                    break;
                case "gauge":
                case "table":
                case "timeline":
                    render  = chart.type.charAt(0).toUpperCase() + chart.type.slice(1);
                    break;
                default:
			        render = chart.type.charAt(0).toUpperCase() + chart.type.slice(1) + 'Chart';
                    break;
            }

            var controlWrapperElement = document.getElementById( "control_wrapper_" + id ) || null;
            var withControlMode = ( typeof settings.controls !== 'undefined' && '' !== settings.controls.controlType ) && controlWrapperElement ? true : false;

            if ( $( controlWrapperElement ).hasClass( 'no-filter' ) ) {
                withControlMode = false;
            }
            if ( withControlMode ) {
                // Chart wrapper.
                render = new gv.ChartWrapper({
                  'chartType': render,
                  'containerId': id,
                });
                // Chart dashboard wrapper.
                var chartWrapperElement   = document.getElementById( "chart_wrapper_" + id ) || null;
                var chartWrapper          = new gv.Dashboard( chartWrapperElement );

                // Error Handler.
                gv.events.addListener(chartWrapper, 'error', function ( err ) {
                    if ( chartWrapperError.length === 0 ) {
                        chartWrapperError.push( err );
                        gv.errors.removeError( err.id );
                        var chartContainer = $( chartWrapperElement ).find( '.visualizer-front' );
                        if ( chartContainer && chartContainer.is(':visible')) {
                            if (chartContainer.parents('div').next( '#sidebar' ).length === 0) {
                                chartContainer.addClass( 'visualizer-chart-loaded' ).addClass( 'visualizer-cw-error' );
                                $( chartWrapperElement ).addClass( 'visualizer-cw-error' );
                            }
                        }
                    } else {
                        gv.errors.removeError( err.id );
                    }
                } );
            } else {
                render = new gv[render](container);
            }
		}

        if (settings['animation'] && parseInt(settings['animation']['startup']) === 1)
        {
            settings['animation']['startup'] = true;
            settings['animation']['duration'] = parseInt(settings['animation']['duration']);
        }
        if ( settings['controls'] ) {
            settings['controls']['ui']['allowMultiple'] = 'true' === settings['controls']['allowMultiple'] ? true : false;
            settings['controls']['ui']['allowTyping'] = 'true' === settings['controls']['allowTyping'] ? true : false;
            settings['controls']['ui']['showRangeValues'] = 'true' === settings['controls']['showRangeValues'] ? true : false;
        }

        // mark roles for series that have specified a role
        // and then remove them from future processing
        // and also adjust the indices of the series array so that
        // the ones with a role are ignored
        // e.g. if there are 6 columns (0-5) out of which 1, 3 and 5 are annotations
        // the final series will only include 0, 2, 4 (reindexed as 0, 1, 2)

        // this will capture all the series indexes that became annotations.
        var series_annotations = [];
        if (settings.series) {
            var adjusted_series = [];
            for (i = 0; i < settings.series.length; i++) {
                if (!series[i + 1] || typeof settings.series[i] === 'undefined') {
                    continue;
                }
                if(typeof settings.series[i].role !== 'undefined'){
                    table.setColumnProperty(i + 1, 'role', settings.series[i].role);
                    if(settings.series[i].role === '') {
                        adjusted_series.push(settings.series[i]);
                    }else{
                        series_annotations.push(i);
                    }
                }
            }
            if(adjusted_series.length > 0){
                settings.series = adjusted_series;
            }
        }

        if ( settings['explorer_enabled'] && settings['explorer_enabled'] == 'true' ) { // jshint ignore:line
            var $explorer = {};
            $explorer['keepInBounds'] = true;

            if ( settings['explorer_actions'] ) {
                $explorer['actions'] = settings['explorer_actions'];
            }
            settings['explorer'] = $explorer;
        }

		switch (chart.type) {
			case 'pie':
				if (settings.slices) {
					for (i in settings.slices) {
						if (settings.slices[i]['color'] === '') {
							delete settings.slices[i]['color'];
						}
					}
				}
				break;
			case 'line':
			case 'bar':
			case 'column':
			case 'area':
			case 'scatter':
			case 'candlestick':
				if (settings.series) {
					for (i in settings.series) {
						if (settings.series[i]['color'] === '') {
							delete settings.series[i]['color'];
						}
					}
				}

                if (settings.series && settings.legend && settings.legend.position === "left")
                {
                    settings.targetAxisIndex = 1;
                }
				break;
			case 'geo':
				if (settings.region !== undefined && settings.region.replace(/^\s+|\s+$/g, '') === '') {
					settings['region'] = 'world';
				}
				break;
			case 'table':
			case 'tabular':
                if (parseInt(settings['pagination']) !== 1)
                {
                    delete settings['pageSize'];
                }
				break;
			case 'gauge':
				break;
			case 'bubble':
                settings.sortBubblesBySize = settings.sortBubblesBySize ? settings.sortBubblesBySize == 1 : false; // jshint ignore:line
				break;
			case 'timeline':
                settings['timeline'] = {};
                settings['timeline']['groupByRowLabel'] = settings['groupByRowLabel'] ? true : false;
                settings['timeline']['colorByRowLabel'] = settings['colorByRowLabel'] ? true : false;
                settings['timeline']['showRowLabels']   = settings['showRowLabels'] ? true : false;
                if(settings['singleColor'] !== '') {
                    settings['timeline']['singleColor'] = settings['singleColor'];
                }
				break;
			case 'combo':
				if (settings.series) {
					for (i in settings.series) {
						if (settings.series[i]['type'] === '') {
							delete settings.series[i]['type'];
						}
						if (settings.series[i]['color'] === '') {
							delete settings.series[i]['color'];
						}
					}
				}

                if (settings.series && settings.legend && settings.legend.position === "left")
                {
                    settings.targetAxisIndex = 1;
                }
				break;
			default:
				return;
		}

		if (series[0] && (series[0].type === 'date' || series[0].type === 'datetime' || series[0].type === 'timeofday')) {
			axis = false;
			switch (chart.type) {
				case 'line':
				case 'area':
				case 'scatter':
				case 'candlestick':
				case 'column':
				case 'combo':
					axis = settings.hAxis;
					break;
				case 'bar':
					axis = settings.vAxis;
					break;
			}

			if (axis) {
				for (property in axis.viewWindow) {
					date = new Date(axis.viewWindow[property]);
					if (Object.prototype.toString.call(date) === "[object Date]") {
						if (!isNaN(date.getTime())) {
							axis.viewWindow[property] = date;
							continue;
						}
					}

					delete axis.viewWindow[property];
				}
			}

            if(settings.hAxis && settings.hAxis.format == ''){
                settings.hAxis.format = 'yyyy-MM-dd';
            }
		}

        if(settings.hAxis){
            if(settings.hAxis.textStyle && settings.hAxis.textStyle !== ''){
                if(typeof(settings.hAxis.textStyle) === "string") {
                    settings.hAxis.textStyle = {color: settings.hAxis.textStyle};
                }
            }
        }

        if(settings.vAxis){
            if(settings.vAxis.textStyle && settings.vAxis.textStyle !== ''){
                if(typeof(settings.vAxis.textStyle) === "string") {
                    settings.vAxis.textStyle = {color: settings.vAxis.textStyle};
                }
            }
        }

        for (i = 0; i < data.length; i++) {
			row = [];
			for (j = 0; j < series.length; j++) {
                switch(series[j].type) {
                    case 'string':
                        if(data[i][j] === ''){
                            data[i][j] = null;
                        }
                        break;
                    case 'boolean':
                        if (typeof data[i][j] === 'string'){
                            data[i][j] = data[i][j] === 'true';
                        }
                        break;
                    case 'date':
                        // fall-through.
                    case 'datetime':
                        if (typeof data[i][j] === 'string') {
                            var dateParse = Date.parse(data[i][j].replace(/-/g, '/'));
                            if ( isNaN( dateParse ) ) {
                                dateParse = Date.parse(data[i][j]);
                            }
                            date = new Date( dateParse );
                        } else if(typeof data[i][j] === 'object') {
                            date = data[i][j];
                        }
                        data[i][j] = null;
                        if (Object.prototype.toString.call(date) === "[object Date]") {
                            if (!isNaN(date.getTime())) {
                                data[i][j] = date;
                            }
                        }
                        break;
				}
				row.push(data[i][j]);
			}
			table.addRow(row);
        }

		if (settings.series) {
            switch(chart.type){
                case 'table':
                case 'tabular':
                    for(i in settings.series){
                        i = parseInt(i);
                        if (!series[i + 1]) {
                            continue;
                        }
                        format_data(id, table, series[i + 1].type, settings.series[i].format, i + 1);
                    }
                    break;
                default:
                    for (i = 0; i < settings.series.length; i++) {
                        if (!series[i + 1] || typeof settings.series[i] === 'undefined') {
                            continue;
                        }
                        var seriesIndexToUse = i + 1;

                        // if an annotation "swallowed" a series, use the following one.
                        if(series_annotations.includes(i)){
                            seriesIndexToUse++;
                        }
                        if ( series[seriesIndexToUse] ) {
                            format_data(id, table, series[seriesIndexToUse].type, settings.series[i].format, seriesIndexToUse);
                        }
                    }
                    break;
            }
		} else if (chart.type === 'pie' && settings.format && settings.format !== '') {
            format_data(id, table, 'number', settings.format, 1);
        }

        if(settings.hAxis && series[0]) {
       	    format_data(id, table, series[0].type, settings.hAxis.format, 0);
        }

        override(settings);

        gv.events.addListener(render, 'ready', function () {
            var arr = id.split('-');
            render = typeof render.getChart !== 'undefined' ? render.getChart() : render;
            __visualizer_chart_images[ arr[0] + '-' + arr[1] ] = '';

            if (render.container && $(render.container).is(':visible')) {
                if ($(render.container).parents('div').next( '#sidebar' ).length === 0) {
                    $(render.container).addClass( 'visualizer-chart-loaded' );
                }
            }

            try{
                if ( typeof render.getImageURI !== 'undefined' ) {
                    var img = render.getImageURI();
                    __visualizer_chart_images[ arr[0] + '-' + arr[1] ] = img;
                    $('body').trigger('visualizer:render:chart', {id: arr[1], image: img});
                    if ( $( '#chart-img' ).length ) {
                        $( '#chart-img' ).val( img );
                    }
                }
            }catch(error){
                var canvas = document.getElementById( 'canvas' );
                domtoimage.toPng(canvas)
                .then(function ( img ) {
                    __visualizer_chart_images[ arr[0] + '-' + arr[1] ] = img;
                    $('body').trigger('visualizer:render:chart', {id: arr[1], image: img});
                    if ( $( '#chart-img' ).length ) {
                        $( '#chart-img' ).val( img );
                    }
                })
                .catch(function (error) {
                  console.warn('render.getImageURI not defined for ' + arr[0] + '-' + arr[1]);
                });
            }
        });

        if ( withControlMode ) {
           // alert( chart.is_library_page );
            // Create a control wrapper, passing some options.
            var controlWrapper = new gv.ControlWrapper( {
                containerId: 'control_wrapper_' + id,
                controlType: settings.controls.controlType,
                'options': settings.controls,
            } );

            $('body').trigger('visualizer:chart:settings:extend', {id: id, chart: chart, settings: settings, data: table});
            render.setOptions(settings);
            chartWrapper.bind(controlWrapper, render);
            chartWrapper.draw(table);

            gv.events.addListener(controlWrapper, 'ready', function ( err ) {
                if ( 'vertical' === settings.controls.ui.orientation ) {
                    if ( 'canvas' === id ) {
                        jQuery( '#' + id ).addClass( 'vz-vertical' );
                    }
                } else {
                    jQuery( '#' + id ).removeClass( 'vz-vertical' );
                }
            });
        } else {
            $('body').trigger('visualizer:chart:settings:extend', {id: id, chart: chart, settings: settings, data: table});
            render.draw(table, settings);
        }
	}

    function format_data(id, table, type, format, index) {
        if (!format || format === '') {
            return;
        }

        var formatter = null;
        switch (type) {
            case 'number':
                formatter = new gv.NumberFormat({pattern: format});
                break;
            case 'date':
            case 'datetime':
            case 'timeofday':
                formatter = new gv.DateFormat({pattern: format});
                break;
        }

        if (formatter) {
            formatter.format(table, index);
        }

        var arr = id.split('-');
        $('body').trigger('visualizer:format:chart', {id: parseInt(arr[1]), data: table, column: index});

    }

    function override(settings) {
        if (settings.manual) {
            try{
                var options = JSON.parse(settings.manual);
                $.extend(settings, options);
                delete settings.manual;
            }catch(error){
                console.error("Error while adding manual configuration override " + settings.manual);
            }
        }
    }

	function render() {
		for (var id in (all_charts || {})) {
            var chartElement = document.getElementById( id );
            if (chartElement && chartElement.offsetParent) {
		      renderChart(id);
            }
		}
	}

    var resizeTimeout;

	$(document).ready(function() {
		$(window).resize(function(e) {
			clearTimeout(resizeTimeout);
            window.isResizeRequest = 'undefined' !== typeof e.originalEvent ? true : false;
			resizeTimeout = setTimeout(render, 100);
		});

        resizeHiddenContainers(true);

        if ( $( '.visualizer-hidden-container' ).length ) {
            setInterval( function() {
                $( '.visualizer-hidden-container' ).find(".visualizer-front:not(.visualizer-chart-loaded)").resize();
            }, 500 );
        }

        window.addEventListener( 'orientationchange', function() {
            $( '.visualizer-chart-loaded' ).removeClass( 'visualizer-chart-loaded' ).resize();
        }, false);
    });

    $(window).on('load', function(){
        $( '.visualizer-front:not(.visualizer-chart-loaded)' ).resize();
        resizeHiddenContainers(true);
    });

    function resizeHiddenContainers(everytime){
        $(".visualizer-front").parents().each(function(){
            if(!$(this).is(":visible") && !$(this).hasClass("visualizer-hidden-container")){
                $(this).addClass("visualizer-hidden-container");
            }
        });

        var mutateObserver = new MutationObserver(function(records) {
            records.forEach(function(record) {
                if(record.attributeName === "style" || record.attributeName === "class"){
                    var element         = $(record.target);
                    var displayStyle    = window.getComputedStyle(element[0]).getPropertyValue("display");
                    if(element.hasClass("visualizer-hidden-container-resized") || displayStyle === "none") { return ; }
                    element.find(".visualizer-front").resize();
                    if(!everytime) {
                    	element.addClass("visualizer-hidden-container-resized");
                    }
                }
            });
        });

        $('.visualizer-hidden-container').each(function(){
            mutateObserver.observe($(this)[0], {attributes: true});
        });
	}

    $('body').on('visualizer:render:chart:start', function(event, v){
        var $chart_types = ['corechart', 'geochart', 'gauge', 'table', 'timeline'];
        if(v.is_front == true){ // jshint ignore:line
            // check what all chart types to load.
            $chart_types = [];
            $.each(v.charts, function(i, c){
                var $type = c.type;
                switch($type){
                    case 'bar':
                    case 'column':
                    case 'line':
                    case 'area':
                    case 'stepped area':
                    case 'bubble':
                    case 'pie':
                    case 'donut':
                    case 'combo':
                    case 'candlestick':
                    case 'histogram':
                    case 'scatter':
                    case 'bubble':
                        $type = 'corechart';
                        break;
                    case 'geo':
                        $type = 'geochart';
                        break;
                    case 'tabular':
                    case 'table':
                        $type = 'table';
                        break;
                    case 'dataTable':
                    case 'polarArea':
                    case 'radar':
                        $type = null;
                        break;
                }
                if($type != null){
                    $chart_types.push($type);
                }
            });
        }

        objects = {};
        if ( 'object' === typeof google ) {
            $chart_types.push( 'controls' );
            google.load( 'visualization', '51', {packages: $chart_types, mapsApiKey: v.map_api_key, 'language' : v.language,
                callback: function () {
                    gv = google.visualization;
                    all_charts = v.charts;
                    if(v.is_front == true && typeof v.id !== 'undefined'){ // jshint ignore:line
                        if ( document.getElementById( v.id ).offsetParent !== null ) {
                            renderChart(v.id);
                        }
                    } else {
                        render();
                    }
                }
            });
        }
    });

    $('body').on('visualizer:render:specificchart:start', function(event, v){
        objects = {};
        gv = google.visualization;
        renderSpecificChart(v.id, v.chart);
    });

    $('body').on('visualizer:render:currentchart:update', function(event, v){
        renderChart('canvas');
    });

    // front end actions
    // 'image' is also called from the library
    $('body').on('visualizer:action:specificchart', function(event, v){
        var id = v.id;
        if(typeof rendered_charts[id] === 'undefined'){
            return;
        }
        var arr = id.split('-');
        var img = __visualizer_chart_images[ arr[0] + '-' + arr[1] ];
        switch(v.action){
            case 'print':
                // for charts that have no rendered image defined, we print the data instead.
                var html = v.data;
                if(img !== ''){
                    html = "<html><body><img src='" + img + "' /></body></html>";
                }
                $('body').trigger('visualizer:action:specificchart:defaultprint', {data: html});
                break;
            case 'image':
                if(img !== ''){
                    var $a = $("<a>"); // jshint ignore:line
                    $a.attr("href", img);
                    $("body").append($a);
                    $a.attr("download", v.dataObj.name);
                    $a[0].click();
                    $a.remove();
                }else{
                    console.warn("No image generated");
                }
                break;
        }
    });

    var timer = 0;
    $( document ).on( 'input', '.series-linewidth', function() {
        var seriesLineWidth = $( this );
        clearTimeout( timer );
        timer = setTimeout( function() {
            if ( seriesLineWidth.val() != '' && seriesLineWidth.val() <= 0 ) {
                seriesLineWidth.val( '0.1' );
            }
        }, 700 );
    } );
})(jQuery);
