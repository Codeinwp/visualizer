(function(v, g) {
	var gv;

	v.objects = {};

	v.renderChart = function(id) {
		var chart, render, container, series, data, table, settings, i, j, row, date;

		chart = v.charts[id];
		series = chart.series;
		data = chart.data;
		settings = chart.settings;

		container = document.getElementById(id);
		table = new gv.DataTable({cols: series});

		render = v.objects[id] || null;
		if (!render) {
			render = chart.type == 'gauge'
				? 'Gauge'
				: chart.type.charAt(0).toUpperCase() + chart.type.slice(1) + 'Chart';

			render = new gv[render](container);
		}

		switch (v.charts[id].type) {
			case 'pie':
				if (settings.slices) {
					for (i in settings.slices) {
						if (settings.slices[i]['color'] == '') {
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
						if (settings.series[i]['color'] == '') {
							delete settings.series[i]['color'];
						}
					}
				}
				break;
			case 'geo':
				if (settings.region != undefined && settings.region.replace(/^\s+|\s+$/g, '') == '') {
					settings['region'] = 'world';
				}
				break;
			case 'gauge':
				break;
			default:
				return;
		}

        for (i = 0; i < data.length; i++) {
			row = [];
			for (j = 0; j < series.length; j++) {
				if (series[j].type == 'date' || series[j].type == 'datetime') {
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

        render.draw(table, settings);
	};

	v.render = function() {
		for (var id in (v.charts || {})) {
			v.renderChart(id);
		}
	};

	g.load("visualization", "1", {packages: ["corechart", "geochart", "gauge"]});
	g.setOnLoadCallback(function() {
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
	});
})(jQuery, visualizer);