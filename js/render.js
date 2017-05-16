/* global google */
/* global visualizer */
(function(v, g) {
	var gv;

	v.objects = {};

	v.renderChart = function(id) {
		var chart, render, container, series, data, table, settings, i, j, row, date, axis, property, format, formatter;

		chart = v.charts[id];
		series = chart.series;
		data = chart.data;
		settings = chart.settings;

		container = document.getElementById(id);
        if (container == null) {
            return;
        }
		table = new gv.DataTable({cols: series});

		render = v.objects[id] || null;
		if (!render) {
            switch (chart.type) {
                case "gauge":
                case "table":
                case "timeline":
                    render  = chart.type.charAt(0).toUpperCase() + chart.type.slice(1);
                    break;
                default:
			        render = chart.type.charAt(0).toUpperCase() + chart.type.slice(1) + 'Chart';
                    break;
            }

			render = new gv[render](container);
		}

		switch (v.charts[id].type) {
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
                if (parseInt(settings['pagination']) !== 1)
                {
                    delete settings['pageSize'];
                }
				break;
			case 'gauge':
				break;
			case 'timeline':
                settings['timeline'] = [];
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

		if (series[0] && (series[0].type === 'date' || series[0].type === 'datetime')) {
			axis = false;
			switch (v.charts[id].type) {
				case 'line':
				case 'area':
				case 'scatter':
				case 'candlestick':
				case 'column':
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
		}

        if(settings.hAxis){
            if(settings.hAxis.textStyle && settings.hAxis.textStyle !== ''){
                settings.hAxis.textStyle = {color: settings.hAxis.textStyle};
            }
        }

        if(settings.vAxis){
            if(settings.vAxis.textStyle && settings.vAxis.textStyle !== ''){
                settings.vAxis.textStyle = {color: settings.vAxis.textStyle};
            }
        }

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
				row.push(data[i][j]);
			}
			table.addRow(row);
        }

		if (settings.series) {
			for (i = 0; i < settings.series.length; i++) {
				format = settings.series[i].format;
				if (!format || format === '' || !series[i + 1]) {
					continue;
				}

				formatter = null;
				switch (series[i + 1].type) {
					case 'number':
						formatter = new g.visualization.NumberFormat({pattern: format});
						break;
					case 'date':
					case 'datetime':
					case 'timeofday':
						formatter = new g.visualization.DateFormat({pattern: format});
						break;
				}

				if (formatter) {
					formatter.format(table, i + 1);
				}
			}
		}

        render.draw(table, settings);
	};

	v.render = function() {
		for (var id in (v.charts || {})) {
			v.renderChart(id);
		}
	};

	g.charts.load("current", {packages: ["corechart", "geochart", "gauge", "table", "timeline"], mapsApiKey: v.map_api_key});
	g.charts.setOnLoadCallback(function() {
		gv = g.visualization;
		v.render();
	});
})(visualizer, google);

(function($, v) {
	var resizeTimeout;

	$(document).ready(function() {
		$(window).resize(function() {
			clearTimeout(resizeTimeout);
			resizeTimeout = setTimeout(v.render, 100);
		});

        resizeHiddenContainers(true);
    });

    $(window).load(function(){
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

})(jQuery, visualizer);