/* global google */
/* global visualizer */
/* global console */
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
			switch (chart.type) {
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
		} else if (chart.type === 'pie' && settings.format && settings.format !== '') {
            formatter = new g.visualization.NumberFormat({pattern: settings.format});
            formatter.format(table, 1);
        }

        v.override(settings);

        render.draw(table, settings);
	};

    v.override = function(settings) {
        if (settings.manual) {
            try{
                var options = JSON.parse(settings.manual);
                jQuery.extend(settings, options);
                delete settings.manual;
            }catch(error){
                console.error("Error while adding manual configuration override " + settings.manual);
            }
        }
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
        initActionsButtons();
    });

    $(window).load(function(){
        resizeHiddenContainers(true);
    });

    function initActionsButtons() {
        if($('a.visualizer-action[data-visualizer-type=copy]').length > 0) {
            var clipboard = new Clipboard('a.visualizer-action[data-visualizer-type=copy]'); // jshint ignore:line
            clipboard.on('success', function(e) {
                window.alert(v.i10n['copied']);
            });
        }
        $('a.visualizer-action[data-visualizer-type!=copy]').on('click', function(e) {
            var type    = $(this).attr( 'data-visualizer-type' );
            var chart   = $(this).attr( 'data-visualizer-chart-id' );
            var lock    = $('.visualizer-front.visualizer-front-' + chart);
            lock.lock();
            e.preventDefault();
            $.ajax({
                url     : v.rest_url.replace('#id#', chart).replace('#type#', type),
                success: function(data) {
                    if (data && data.data) {
                        switch(type){
                            case 'csv':
                                var a = document.createElement("a");
                                document.body.appendChild(a);
                                a.style = "display: none";
                                var blob = new Blob([data.data.csv], {type: $(this).attr( 'data-visualizer-mime' ) }),
                                    url = window.URL.createObjectURL(blob);
                                a.href = url;
                                a.download = data.data.name;
                                a.click();
                                setTimeout(function () {
                                    window.URL.revokeObjectURL(url);
                                }, 100);
                                break;
                            case 'xls':
                                var $a = $("<a>");
                                $a.attr("href",data.data.csv);
                                $("body").append($a);
                                $a.attr("download",data.data.name);
                                $a[0].click();
                                $a.remove();
                                break;
                            case 'print':
                                var iframe = $('<iframe>').attr("name", "print-visualization").attr("id", "print-visualization").css("position", "absolute");
                                iframe.appendTo($('body'));
                                var iframe_doc = iframe.get(0).contentWindow || iframe.get(0).contentDocument.document || iframe.get(0).contentDocument;
                                iframe_doc.document.open();
                                iframe_doc.document.write(data.data.csv);
                                iframe_doc.document.close();
                                setTimeout(function(){
                                    window.frames['print-visualization'].focus();
                                    window.frames['print-visualization'].print();
                                    iframe.remove();
                                }, 500);
                                break;
                            default:
                                if(window.visualizer_perform_action) {
                                    window.visualizer_perform_action(type, chart, data.data);
                                }
                                break;
                        }
                    }
                    lock.unlock();
                }
            });
        });
    }

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

(function ($) {
    $.fn.lock = function () {
        $(this).each(function () {
            var $this = $(this);
            var position = $this.css('position');

            if (!position) {
                position = 'static';
            }

            switch (position) {
                case 'absolute':
                case 'relative':
                    break;
                default:
                    $this.css('position', 'relative');
                    break;
            }
            $this.data('position', position);

            var width = $this.width(),
                height = $this.height();

            var locker = $('<div class="locker"></div>');
            locker.width(width).height(height);

            var loader = $('<div class="locker-loader"></div>');
            loader.width(width).height(height);

            locker.append(loader);
            $this.append(locker);
            $(window).resize(function () {
                $this.find('.locker,.locker-loader').width($this.width()).height($this.height());
            });
        });

        return $(this);
    };

    $.fn.unlock = function () {
        $(this).each(function () {
            $(this).find('.locker').remove();
            $(this).css('position', $(this).data('position'));
        });

        return $(this);
    };
})(jQuery);