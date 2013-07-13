(function(v, g) {
	var gv;

	v.objects = {};

	v.renderChart = function(id) {
		var chart, container, series, data, table, i, j, row, date;

		series = v.charts[id].series;
		data = v.charts[id].data;
		chart = v.objects[id] || null;
		container = document.getElementById(id);
		table = new gv.DataTable({cols: series});

		switch (v.charts[id].type) {
			case 'pie':
				if (!chart) chart = new gv.PieChart(container);
				break;
			case 'line':
				if (!chart) chart = new gv.LineChart(container);
				break;
			case 'bar':
				if (!chart) chart = new gv.BarChart(container);
				break;
			case 'column':
				if (!chart) chart = new gv.ColumnChart(container);
				break;
			case 'area':
				if (!chart) chart = new gv.AreaChart(container);
				break;
			case 'geo':
				if (!chart) chart = new gv.GeoChart(container);
				break;
			case 'scatter':
				if (!chart) chart = new gv.ScatterChart(container);
				break;
			case 'gauge':
				if (!chart) chart = new gv.Gauge(container);
				break;
			case 'candlestick':
				if (!chart) chart = new gv.CandlestickChart(container);
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

        chart.draw(table, {});
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