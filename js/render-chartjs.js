/* global console */
/* global visualizer */
/* global Chart */
/* global numeral */
/* global moment */

(function($) {
    var all_charts;
    // so that we know which charts belong to our library.
    var rendered_charts = [];

    function renderChart(id, v) {
        renderSpecificChart(id, all_charts[id], v);
    }

    function renderSpecificChart(id, chart, v) {
        var render, container, series, data, datasets, settings, i, j, row, date, axis, property, format, formatter, type, rows, cols, labels;

        if(chart.library !== 'chartjs'){
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

        // eliminate the jitter/flicker while editing charts.
        if(v.is_front == false){ // jshint ignore:line
            $('#' + id).empty();
        }

        if($('#' + id + ' canvas').length === 0){
            $('#' + id).append($('<canvas width="100%" height="90%"></canvas>'));
        }

        var context = $('#' + id + ' canvas')[0].getContext('2d');

        type = chart.type;
        switch (chart.type) {
            case 'column':
                type = 'bar';
                break;
            case 'bar':
                type = 'horizontalBar';
                break;
            case 'pie':
                // donut is not a setting but a separate chart type.
                if(typeof settings['custom'] !== 'undefined' && settings['custom']['donut'] === 'true'){
                    type = 'doughnut';
                }
                break;
        }

        rows = [];
        datasets = [];
        labels = [];

        for (i = 0; i < data.length; i++) {
			row = [];
			for (j = 0; j < series.length; j++) {
				if (series[j].type === 'date' || series[j].type === 'datetime') {                  
					date = new Date(data[i][j]);
					data[i][j] = null;
					if (Object.prototype.toString.call(date) === "[object Date]") {
						if (!isNaN(date.getTime())) {
							data[i][j] = date;
						}
					}
				}
                row.push(format_data(data[i][j], j, settings, series));
			}
            rows.push(row);
        }

        // transpose
        for (j = 0; j < series.length; j++) {
            row = [];
            for (i = 0; i < rows.length; i++) {
                if(j === 0){
                    labels.push(rows[i][j]);
                }else{
                    row.push(rows[i][j]);
                }
            }
            if(row.length > 0){
                var $attributes = {label: series[j].label, data: row};
                switch(chart.type){
                    case 'pie':
                    case 'polarArea':
                        $.extend($attributes, {label: labels});
                        handlePieSeriesSettings($attributes, rows, settings, chart);
                        break;
                    default:
                        handleSeriesSettings($attributes, j - 1, settings, chart);
                }
                datasets.push($attributes);
            }
        }

        if(v.is_front == false){ // jshint ignore:line
            // this line needs to be included twice. This is not an error.
            // if this one is removed, the preview gets messed up.
            // if this line is included all the time, in this very place (out of the if), the front-end gets messed up.
            $.extend(settings, { responsive: true, maintainAspectRatio: false });
        }

        handleSettings(settings, chart);

        var chartjs = new Chart(context, {
            type: type,
            data: {
                labels: labels,
                datasets: datasets
            },
            options: settings
        });

        // this line needs to be included twice. This is not an error.
        $.extend(settings, { responsive: true, maintainAspectRatio: false });

        // chart area
        if(v.is_front == true){ // jshint ignore:line
            $('#' + id).css('position', 'relative');
            var height = settings.height.indexOf('%') === -1 ? ( settings.height + 'px' ) : settings.height;
            var width = settings.width.indexOf('%') === -1 ? ( settings.width + 'px' ) : settings.width;
            if(settings.height){
                chartjs.canvas.parentNode.style.height = height;
                $('#' + id + ' canvas').css('height', height);
            }
            if(settings.width){
                chartjs.canvas.parentNode.style.width = width;
                $('#' + id + ' canvas').css('width', width);
            }
        }

        // allow user to extend the settings.
        $('body').trigger('visualizer:chart:settings:extend', {id: id, chart: chart, settings: settings});

        $('.loader').remove();
    }

    function handleSettings(settings, chart){
        if(typeof settings === 'undefined'){
            return;
        }

        // handle some defaults/idiosyncrasies.
        if(typeof settings['animation'] !== 'undefined' && parseInt(settings['animation']['duration']) === 0){
            settings['animation']['duration'] = 1000;
        }

        if(typeof settings['title'] !== 'undefined' && settings['title']['text'] !== ''){
            settings['title']['display'] = true;
        }

        if(typeof settings['tooltip'] !== 'undefined' && typeof settings['tooltip']['intersect'] !== 'undefined'){
            // jshint ignore:line
            settings['tooltip']['intersect'] = settings['tooltip']['intersect'] == true || parseInt(settings['tooltip']['intersect']) === 1;  // jshint ignore:line
        }

        if(typeof settings['fontName'] !== 'undefined' && settings['fontName'] !== ''){
            Chart.defaults.global.defaultFontFamily = settings['fontName'];
            delete settings['fontName'];
        }

        if(typeof settings['fontSize'] !== 'undefined' && settings['fontSize'] !== ''){
            Chart.defaults.global.defaultFontSize = settings['fontSize'];
            delete settings['fontSize'];
        }

        // handle legend defaults.
        if(typeof settings['legend'] !== 'undefined' && typeof settings['legend']['labels'] !== 'undefined') {
            for(var i in settings['legend']['labels']){
                if(settings['legend']['labels'][i] !== 'undefined' && settings['legend']['labels'][i] === ''){
                    delete settings['legend']['labels'][i];
                }
            }
        }

        handleAxes(settings, chart);

        override(settings, chart);
    }

    function handleAxes(settings, chart){
        if(typeof settings['yAxes'] !== 'undefined' && typeof settings['xAxes'] !== 'undefined'){
            // stacking has to be defined on both axes.
            if(typeof settings['yAxes']['stacked_bool'] !== 'undefined'){
                settings['xAxes']['stacked_bool'] = 'true';
            }
            if(typeof settings['xAxes']['stacked_bool'] !== 'undefined'){
                settings['yAxes']['stacked_bool'] = 'true';
            }
        }
        configureAxes(settings, 'yAxes', chart);
        configureAxes(settings, 'xAxes', chart);
    }

    function configureAxes(settings, axis, chart) {
        if(typeof settings[axis] !== 'undefined'){
            var $features = {};
            for(var i in settings[axis]){
                var $o = {};
                if(Array.isArray(settings[axis][i]) || typeof settings[axis][i] === 'object'){
                    for(var j in settings[axis][i]){
                        var $val = '';
                        if(j === 'labelString'){ 
                            $o['display'] = true;
                            $val = settings[axis][i][j];
                        }else if(i === 'ticks'){
                            // number values under ticks need to be converted to numbers or the library throws a JS error.
                            $val = parseFloat(settings[axis][i][j]);
                            if(isNaN($val)){
                                $val = '';
                            }
                        } else {
                            $val = settings[axis][i][j];
                        }
                        if($val !== ''){
                            $o[j] = $val;
                        }
                    }
                }else{
                    // usually for attributes that have primitive values.
                    var array = i.split('_');
                    var dataType = 'string';
                    var dataValue = settings[axis][i];
                    if(array.length === 2){
                        dataType = array[1];
                    }

                    if(settings[axis][i] === ''){
                        continue;
                    }
                    switch(dataType){
                        case 'bool':
                            dataValue = dataValue === 'true' ? true : false;
                            break;
                        case 'int':
                            dataValue = parseFloat(dataValue);
                            break;
                    }
                    $o = dataValue;
                    // remove the type suffix to get the name of the setting.
                    i = i.replace(/_bool/g, '').replace(/_int/g, '');
                }
                $features[i] = $o;
            }
            var $scales = {};
            $scales['scales'] = {};
            $scales['scales'][axis] = [];
            if(typeof settings['scales'] !== 'undefined' && typeof settings[axis + 'set'] === 'undefined'){
                $scales['scales'] = settings['scales'];
                if(typeof settings['scales'][axis] !== 'undefined'){
                    $scales['scales'][axis] = settings['scales'][axis];
                }
            }
            if(typeof $scales['scales'][axis] === 'undefined'){
                $scales['scales'][axis] = [];
            }
            var $axis = $scales['scales'][axis];

            $axis.push($features);
            $.extend(settings, $scales);

            // to prevent duplication, indicates that the axis has been set.
            var $custom = {};
            $custom[axis + 'set'] = 'yes';
            $.extend(settings, $custom);
        }

        // format the axes labels.
        if(typeof settings[axis + '_format'] !== 'undefined' && settings[axis + '_format'] !== ''){
            var format = settings[axis + '_format'];
            switch(axis){
                case 'xAxes':
                    settings.scales.xAxes[0].ticks.callback = function(value, index, values){
                        return format_datum(value, format);
                    };
                    break;
                case 'yAxes':
                    settings.scales.yAxes[0].ticks.callback = function(value, index, values){
                        return format_datum(value, format);
                    };
                    break;
            }
            delete settings[axis + '_format'];
        }
        delete settings[axis];
    }

    function handlePieSeriesSettings($attributes, rows, settings, chart){
        if(typeof settings.slices === 'undefined'){
            return;
        }

        var atts = [];
        // collect all the types of attributes
        for(var j in settings.slices[0]){
            // weight screws up the rendering for some reason, so we will ignore it.
            if(j === 'weight') {
                continue;
            }
            atts.push(j);
        }

        for (j = 0; j < atts.length; j++) {
            var values = [];
            for (var i = 0; i < rows.length; i++) {
                if(typeof settings.slices[i] !== 'undefined' && typeof settings.slices[i][atts[j]] !== 'undefined'){
                    values.push(settings.slices[i][atts[j]]);
                }
            }
            var object = {};
            object[ atts[ j ] ] = values;
            $.extend($attributes, object);
        }
    }

    function handleSeriesSettings($attributes, j, settings, chart){
        if(typeof settings.series === 'undefined' || typeof settings.series[j] === 'undefined'){
            return;
        }
        for(var i in settings.series[j]){
            var $attribute = {};
            $attribute[i] = settings.series[j][i];
            $.extend($attributes, $attribute);
        }
    }

    function format_datum(datum, format, type){
        if(format === '' || format === null || typeof format === 'undefined'){
            return datum;
        }
        // if there is no type, this is probably coming from the axes formatting.
        var removeDollar = true;
        if(typeof type === 'undefined' || type === null){
            // we will determine type on the basis of the presence or absence of #.
            type = 'date';
            if(format.indexOf('#') !== -1){
                type = 'number';
            }
            removeDollar = false;
        }

        switch(type) {
            case 'number':
                // numeral.js works on 0 instead of # so we just replace that in the ICU pattern set.
                format = format.replace(/#/g, '0');
                // we also replace all instance of '$' as that is more relevant for ticks.
                if(removeDollar){
                    format = format.replace(/\$/g, '');
                }
                datum = numeral(datum).format(format);
                break;
            case 'date':
            case 'datetime':
            case 'timeofday':
                datum = moment(datum).format(format);
                break;
        }
        return datum;
    }

    function format_data(datum, j, settings, series){
        j = j - 1;
        var format = typeof settings.series !== 'undefined' && typeof settings.series[j] !== 'undefined' ? settings.series[j].format : '';
        return format_datum(datum, format, series[j + 1].type);
    }

    function override(settings, chart) {
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


    function render(v) {
        for (var id in (all_charts || {})) {
            renderChart(id, v);
        }
    }

    $('body').on('visualizer:render:chart:start', function(event, v){
        all_charts = v.charts;

        if(v.is_front == true && typeof v.id !== 'undefined'){ // jshint ignore:line
            renderChart(v.id, v);
        } else {
            render(v);
        }

        // for some reason this needs to be introduced here for dynamic preview updates to work.
        v.update = function(){
            renderChart('canvas', v);
        };

    });

    $('body').on('visualizer:render:specificchart:start', function(event, v){
        renderSpecificChart(v.id, v.chart, v.v);
    });

    $('body').on('visualizer:render:currentchart:update', function(event, v){
        var data = v || event.detail;
        renderChart('canvas', data.visualizer);
    });

    // front end actions
    // 'image' is also called from the library
    $('body').on('visualizer:action:specificchart', function(event, v){
        var id = v.id;
        if(typeof rendered_charts[id] === 'undefined'){
            return;
        }
        var canvas = $('#' + id + ' canvas');
        switch(v.action){
            case 'print':
                var win = window.open();
                win.document.write("<br><img src='" + canvas[0].toDataURL() + "'/>");
                win.document.close();
                win.onload = function () { win.print(); setTimeout(win.close, 500); };
                break;
            case 'image':
                var img = canvas[0].toDataURL();
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

})(jQuery);


