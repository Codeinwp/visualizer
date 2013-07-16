(function(v, g) {
	var gv;

	v.objects = {};

	v.renderChart = function(id) {
		var chart, container, series, data, table, settings, i, j, row, date;

		series = v.charts[id].series;
		data = v.charts[id].data;
		settings = v.charts[id].settings;

		container = document.getElementById(id);
		table = new gv.DataTable({cols: series});

		chart = v.objects[id] || null;
		if (!chart) {
			chart = new gv[v.charts[id].type.charAt(0).toUpperCase() + v.charts[id].type.slice(1) + 'Chart'](container);
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

        chart.draw(table, settings);
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