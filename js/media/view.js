(function($, wpm) {
	var libraryWidth, libraryHeight, wpmv, wpmV, wpmvv, wpmvvl, wpmvvb, l10n;

	wpmv = wpm.view;
	wpmV = wpm.View;
	wpmvv = wpmv.visualizer = {};
	l10n = wpmv.l10n.visualizer;

	/**
	 * =========================================================================
	 * COMMON
	 * =========================================================================
	 */

	wpmvv.Chart = wpmV.extend({
		className: 'visualizer-library-chart-canvas',

		constructor: function(options) {
			this.id = 'visualizer-chart-' + options.model.get('id');
			wpmV.apply(this, arguments);
		},

		render: function() {
			var self = this, obj, chart;

			self.$el
				.width(self.options.width)
				.height(self.options.height)
				.css('background-image', 'none');

			obj = self.model.get('data');
			if (_.isUndefined(obj.options)) {
				return;
			}

			obj.options.width = self.options.width || obj.options.width;
			obj.options.height = self.options.height || obj.options.height;

			switch (obj.type) {
				case 'pie':
				case 'line':
				case 'bar':
				case 'column':
				case 'area':
				case 'geo':
				case 'scatter':
				case 'candlestick':
					chart = new google.visualization[obj.type.charAt(0).toUpperCase() + obj.type.slice(1) + 'Chart'](self.el);
					break;
				case 'gauge':
					chart = new google.visualization.Gauge(self.el);
					break;
				default:
					return;
			}

			chart.draw(self._prepareData(obj), self['_' + obj.type + 'Options'](obj));
		},

		_prepareData: function(obj) {
			var data_table, data_view, i, j, series_len, data_len, row, rows;

			data_table = new google.visualization.DataTable();

			for (i = 0, series_len = obj.series.length; i < series_len; i++) {
				data_table.addColumn(obj.series[i]);
			}

			for (i = 0, data_len = obj.data.length; i < data_len; i++) {
				row = [];
				for (j = 0; j < series_len; j++) {
					row.push(this._format(obj.series[j].type, obj.data[i][j]));
				}
				data_table.addRow(row);
			}

			switch (obj.type) {
				case 'pie':
					data_view = new google.visualization.DataView(data_table);
					data_view.setColumns([{
						calc: function(dataTable, row) { return dataTable.getFormattedValue(row, 0); },
						type: 'string'
					}, 1]);
					break;
				case 'column':
				case 'bar':
					data_view = new google.visualization.DataView(data_table);
					rows = [{
						calc: function(dataTable, row) { return dataTable.getFormattedValue(row, 0); },
						type: 'string'
					}];
					for (i = 1; i < series_len; i++) rows.push(i);
					data_view.setColumns(rows);
					break;
				case 'scatter':
					data_view = new google.visualization.DataView(data_table);
					rows = [];
					for (i = 0; i < series_len; i++) rows.push((function(column_num) {
						return {
							calc: function(dataTable, row) {
								var val = $.trim(dataTable.getValue(row, column_num));
								return val == '' ? null : parseFloat(val);
							},
							type: 'number',
							label: data_table.getColumnLabel(column_num)
						};
					})(i));
					data_view.setColumns(rows);
					break;
				default:
					data_view = data_table;
					break;
			}

			return data_view;
		},

		_format: function(type, value) {
			var date, formatter, _float, _int, value;
			switch (type) {
				case 'boolean':
					return value ? true : false;
				case 'number':
					formatter = new google.visualization.NumberFormat({ pattern: '#,###' });
					_float = parseFloat(value);
					_int = parseInt(value);
					value = _int == _float ? _int : _float;
					return { v: value, f: formatter.formatValue(value) };
				case 'date':
				case 'datetime':
					date = new Date(value);
					if (Object.prototype.toString.call(date) === "[object Date]") {
						if (isNaN(date.getTime())) {
							return null;
						}
					} else {
						return null;
					}
					return date;
				case 'timeofday':
					date = new Date('16 Mar 1984 ' + value);
					if (Object.prototype.toString.call(date) === "[object Date]") {
						if (isNaN(date.getTime())) {
							return null;
						}
					} else {
						return null;
					}
					return [date.getHours(), date.getMinutes(), date.getSeconds(), date.getMilliseconds()];
			}
			return value;
		},

		_defaultOptions: function(settings) {
			return {
				title: settings.title || '',
				width: settings.width || 'auto',
				height: settings.height || 'auto',
				fontName: settings.font_name || 'Arial',
				fontSize: settings.font_size || 'automatic',
				backgroundColor: {
					fill: settings.background_color || 'white',
					stroke: settings.stroke_color || '#666',
					strokeWidth: settings.stroke_width || 0
				},
				chartArea: {
					left: settings.chart_area_left || 'auto',
					top: settings.chart_area_top || 'auto',
					width: settings.chart_area_width || 'auto',
					height: settings.chart_area_height || 'auto'
				},
				legend: {
					position: settings.legend_position || 'right'
				}
			};
		},

		_lineOptions: function(obj) {
			var settings, options, series, i, legend = {}, title = {};

			settings = obj.options || {};
			options = this._defaultOptions(settings);

			options['axisTitlesPosition'] = settings.axis_titles_position || 'out';
			options['curveType'] = settings.curve_type || 'none';
			options['lineWidth'] = settings.line_width || 2;
			options['pointSize'] = settings.point_size || 0;

			if (settings.legend_color) legend['color'] = settings.legend_color;
			if (parseInt(settings.legend_font_size) > 0) legend['fontSize'] = settings.legend_font_size;
			options['legend']['textStyle'] = legend;
			options['legend']['alignment'] = settings.legend_aligment || false;

			if (settings.title_color) title['color'] = settings.title_color;
			if (parseInt(settings.title_font_size) > 0) title['fontSize'] = settings.title_font_size;
			options['titleTextStyle'] = title;
			options['titlePosition'] = settings.title_position || 'out';

			options['hAxis'] = {
				title: settings.xaxis_title || '',
				textPosition: settings.xtext_position || 'out',
				baselineColor: settings.xbaseline_color || 'black',
				gridlines: {
					color: settings.xgridlines_color || '#CCC',
					count: settings.xgridlines_count || '5'
				},
				direction: settings.reverse || '1',
				logScale: settings.xlog_scale || false,
				viewWindow: {
					min: settings.ymin_val || false,
					max: settings.ymax_val || false
				}
			}

			options['vAxis'] = {
				title: settings.yaxis_title || '',
				textPosition: settings.ytext_position || 'out',
				baselineColor: settings.ybaseline_color || 'black',
				gridlines: {
					color: settings.ygridlines_color || '#CCC',
					count: settings.ygridlines_count || '5'
				},
				logScale: settings.ylog_scale || false,
				viewWindow: {
					min: settings.xmin_val || false,
					max: settings.xmax_val || false
				}
			}

			options['series'] = {};
			for (i = 1; i < obj.series.length; i++) {
				series = {};
				if (settings['series_' + i + '_curve_type'])        series['curveType'] = settings['series_' + i + '_curve_type'];
				if (settings['series_' + i + '_line_width'])        series['lineWidth'] = settings['series_' + i + '_line_width'];
				if (settings['series_' + i + '_point_size'])        series['pointSize'] = settings['series_' + i + '_point_size'];
				if (settings['series_' + i + '_color'])             series['color'] = settings['series_' + i + '_color'];
				if (settings['series_' + i + '_visible_in_legend']) series['visibleInLegend'] = settings['series_' + i + '_visible_in_legend'];
				options.series[i - 1] = series;
			}

			return options;
		},

		_areaOptions: function(obj) {
			var series, i, settings, options;

			settings = obj.options || {};
			options = this._defaultOptions(settings);

			options['axisTitlesPosition'] = settings.axis_titles_position || 'out';
			options['isStacked'] = settings.is_stacked == '1' ? true : false;
			options['lineWidth'] = settings.line_width || 2;
			options['pointSize'] = settings.point_size || 0;
			options['areaOpacity'] = settings.area_opacity || 0.3;

			options['hAxis'] = {
				title: settings.xaxis_title || '',
				textPosition: settings.xtext_position || 'out',
				baselineColor: settings.xbaseline_color || 'black',
				gridlines: {
					color: settings.xgridlines_color || '#CCC',
					count: settings.xgridlines_count || '5'
				}
			};

			options['vAxis'] = {
				title: settings.yaxis_title || '',
				textPosition: settings.ytext_position || 'out',
				baselineColor: settings.ybaseline_color || 'black',
				gridlines: {
					color: settings.ygridlines_color || '#CCC',
					count: settings.ygridlines_count || '5'
				}
			};

			options['series'] = {};
			for (i = 1; i < obj.series.length; i++) {
				series = {};
				if (settings['series_' + i + '_area_opacity'])      series['areaOpacity'] = settings['series_' + i + '_area_opacity'];
				if (settings['series_' + i + '_line_width'])        series['lineWidth'] = settings['series_' + i + '_line_width'];
				if (settings['series_' + i + '_point_size'])        series['pointSize'] = settings['series_' + i + '_point_size'];
				if (settings['series_' + i + '_color'])             series['color'] = settings['series_' + i + '_color'];
				if (settings['series_' + i + '_visible_in_legend']) series['visibleInLegend'] = settings['series_' + i + '_visible_in_legend'];
				options.series[i - 1] = series;
			}

			return options;
		},

		_pieOptions: function(obj) {
			var settings, options;

			settings = obj.options || {};
			options = this._defaultOptions(settings);

			options['is3D'] = settings.is_3d == '1' ? true : false;
			options['reverseCategories'] = settings.reverse == '1' ? true : false;

			options['pieSliceBorderColor'] = settings.slice_border_color || 'white';
			options['pieSliceText'] = settings.slice_text || 'percentage';

			options['sliceVisibilityThreshold'] = parseFloat(settings.visibility_threshold) || 1 / 720;
			options['pieResidueSliceColor'] = settings.residue_slice_color || 'white';
			options['pieResidueSliceLabel'] = $.trim(settings.residue_slice_label) != ''
				? settings.residue_slice_label
				: 'Other';

			return options;
		},

		_candlestickOptions: function(obj) {
			var settings, options;

			settings = obj.options || {};
			options = this._defaultOptions(settings);

			options['axisTitlesPosition'] = settings.axis_titles_position || 'out';

			options['hAxis'] = {
				title: settings.xaxis_title || '',
				textPosition: settings.xtext_position || 'out'
			};

			options['vAxis'] = {
				title: settings.yaxis_title || '',
				textPosition: settings.ytext_position || 'out',
				baselineColor: settings.ybaseline_color || 'black',
				gridlines: {
					color: settings.ygridlines_color || '#CCC',
					count: settings.ygridlines_count || '5'
				}
			};

			return options;
		},

		_gaugeOptions: function(obj) {
			var settings, options;

			settings = obj.options || {};
			options = this._defaultOptions(settings);

			options['min'] = settings.min || 0;
			options['max'] = settings.max || 100;

			options['greenColor'] = settings.green_color || '#109618';
			options['greenFrom'] = settings.green_from || '';
			options['greenTo'] = settings.green_to || '';

			options['yellowColor'] = settings.yellow_color || '#109618';
			options['yellowFrom'] = settings.yellow_from || '';
			options['yellowTo'] = settings.yellow_to || '';

			options['redColor'] = settings.red_color || '#109618';
			options['redFrom'] = settings.red_from || '';
			options['redTo'] = settings.red_to || '';

			options['minorTicks'] = settings.minor_ticks || 2;

			return options;
		},

		_scatterOptions: function(obj) {
			var settings, options;

			settings = obj.options || {};
			options = this._defaultOptions(settings);

			options['axisTitlesPosition'] = settings.axis_titles_position || 'out';
			options['curveType'] = settings.curve_type || 'none';
			options['lineWidth'] = settings.line_width || 0;
			options['pointSize'] = settings.point_size || 7;

			options['hAxis'] = {
				title: settings.xaxis_title || '',
				textPosition: settings.xtext_position || 'out',
				baselineColor: settings.xbaseline_color || 'black',
				gridlines: {
					color: settings.xgridlines_color || '#CCC',
					count: settings.xgridlines_count || '5'
				}
			};

			options['vAxis'] = {
				title: settings.yaxis_title || '',
				textPosition: settings.ytext_position || 'out',
				baselineColor: settings.ybaseline_color || 'black',
				gridlines: {
					color: settings.ygridlines_color || '#CCC',
					count: settings.ygridlines_count || '5'
				}
			};

			return options;
		},

		_geoOptions: function(obj) {
			var settings, options;

			settings = obj.options || {};
			options = this._defaultOptions(settings);

			options['region'] = settings.region || 'world';
			options['displayMode'] = settings.display_mode || 'auto';
			options['datalessRegionColor'] = settings.dataless_region_color || 'F5F5F5';
			options['keepAspectRatio'] = true;
			options['resolution'] = settings.resolution || 'countries';

			options['colorAxis'] = {
				minValue: settings.min_value || false,
				maxValue: settings.max_value || false,
				colors: [
					settings.min_value_color || '#99BF93',
					settings.mid_value_color || '#58B551',
					settings.max_value_color || '#059100'
				]
			};

			return options;
		},

		_columnOptions: function(obj) {
			var series, i, settings, options;

			settings = obj.options || {};
			options = this._defaultOptions(settings);

			options['axisTitlesPosition'] = settings.axis_titles_position || 'out';
			options['isStacked'] = settings.is_stacked == '1' ? true : false;

			options['hAxis'] = {
				title: settings.xaxis_title || '',
				textPosition: settings.xtext_position || 'out'
			};

			options['vAxis'] = {
				title: settings.yaxis_title || '',
				textPosition: settings.ytext_position || 'out',
				baselineColor: settings.ybaseline_color || 'black',
				gridlines: {
					color: settings.ygridlines_color || '#CCC',
					count: settings.ygridlines_count || '5'
				}
			};

			options['series'] = {};
			for (i = 1; i < obj.series.length; i++) {
				series = {};
				if (settings['series_' + i + '_color'])             series['color'] = settings['series_' + i + '_color'];
				if (settings['series_' + i + '_visible_in_legend']) series['visibleInLegend'] = settings['series_' + i + '_visible_in_legend'];
				options.series[i - 1] = series;
			}

			return options;
		},

		_barOptions: function(obj) {
			var series, i, settings, options;

			settings = obj.options || {};
			options = this._defaultOptions(settings);

			options['axisTitlesPosition'] = settings.axis_titles_position || 'out';
			options['isStacked'] = settings.is_stacked == '1' ? true : false;

			options['hAxis'] = {
				title: settings.xaxis_title || '',
				textPosition: settings.xtext_position || 'out',
				baselineColor: settings.xbaseline_color || 'black',
				gridlines: {
					color: settings.xgridlines_color || '#CCC',
					count: settings.xgridlines_count || '5'
				}
			};

			options['vAxis'] = {
				title: settings.yaxis_title || '',
				textPosition: settings.ytext_position || 'out'
			};

			options['series'] = {};
			for (i = 1; i < obj.series.length; i++) {
				series = {};
				if (settings['series_' + i + '_color'])             series['color'] = settings['series_' + i + '_color'];
				if (settings['series_' + i + '_visible_in_legend']) series['visibleInLegend'] = settings['series_' + i + '_visible_in_legend'];
				options.series[i - 1] = series;
			}

			return options;
		}
	});

	/**
	 * =========================================================================
	 * LIBRARY
	 * =========================================================================
	 */

	wpmvvl = wpmvv.Library = wpmV.extend({
		id: 'visualizer-library-view',
		className: 'visualizer-clearfix',

		initialize: function() {
			var self = this;

			_.defaults(self.options, {
				filter: 'all',
				page: 1
			});

			self.controller.on('visualizer:library:filter', self.onFilterChanged, self);
			self.controller.on('visualizer:library:page', self.onPageChanged, self);
			self.collection.on('reset', self.renderCollection, self);

			self.resetCollection();
		},

		onFilterChanged: function(filter) {
			this.options.filter = filter;
			this.options.page = 1;
			this.resetCollection();
		},

		onPageChanged: function(page) {
			this.options.page = page;
			this.resetCollection();
		},

		renderCollection: function() {
			var self = this;

			self.views.dispose();
			self.$el.html('');
			self.collection.each(self.addChart, self);
		},

		addChart: function(chart) {
			var self = this,
				view = new wpmvvl.Chart({ model: chart });

			self.$el.append(view.$el);
			self.views.set('#visualizer-chart-' + chart.get('id'), view, { silent: true });
			view.render();
		},

		resetCollection: function() {
			var self = this;

			self.collection.fetch({
				silent: false,
				data: {
					filter: self.options.filter,
					page: self.options.page
				},
				statusCode: {
					200: function(response) {
						var paginationView = self.controller.toolbar.get('toolbar').get('pagination');

						if (self.options.page > response.total) {
							self.options.page = response.total;
							self.resetCollection();
						} else {
							paginationView.options.page = self.options.page;
							paginationView.options.total = response.total || 1;
							paginationView.render();
						}
					}
				}
			});
		}
	});

	wpmvvl.Chart = wpmV.extend({
		className: 'visualizer-library-chart',
		template: wpm.template('visualizer-library-chart'),

		events: {
			'click .visualizer-library-chart-delete': 'deleteChart',
			'click .visualizer-library-chart-insert': 'insertChart',
			'click .visualizer-library-chart-clone': 'cloneChart',
			'click .visualizer-library-chart-shortcode': 'selectShortcode'
		},

		initialize: function() {
			var self = this;

			if (!libraryWidth && !libraryHeight) {
				libraryWidth = $('#visualizer-library-view').width() / 3 - 40;
				libraryHeight = libraryWidth * 3 / 4;

				libraryWidth = Math.floor(libraryWidth);
				libraryHeight = Math.floor(libraryHeight);
			}

			self._view = new wpmvv.Chart({
				model: self.model,
				width: libraryWidth,
				height: libraryHeight
			});

			self.$el.html(self.template(self.model.toJSON())).prepend(self._view.$el);
			self.views.set('#' + self._view.id, self._view, { silent: true });
		},

		render: function() {
			this._view.render();
		},

		deleteChart: function() {
			var self = this;

			if (showNotice.warn()) {
				self.model.destroy({
					wait: true,
					success: function() {
						self.views.parent.resetCollection();
					}
				});
			}
		},

		insertChart: function() {
			wpm.editor.insert('[visualizer id="' + this.model.get('id') + '"]');
		},

		cloneChart: function() {
			console.log('clone chart #' + this.model.get('id'));
		},

		selectShortcode: function(e) {
			var range, selection;

			if (window.getSelection && document.createRange) {
				selection = window.getSelection();
				range = document.createRange();
				range.selectNodeContents(e.target);
				selection.removeAllRanges();
				selection.addRange(range);
			} else if (document.selection && document.body.createTextRange) {
				range = document.body.createTextRange();
				range.moveToElementText(e.target);
				range.select();
			}
		}
	});

	wpmvvl.Types = wpmV.extend({
		tagName: 'select',
		className: 'visualizer-library-filters',

		events: {
			change: 'onFilterChange'
		},

		initialize: function() {
			var self = this;

			self.createFilters();
			self.$el.html(_.chain(self.filters).map(function(filter) {
				return {
					el: self.make('option', {value: filter.key}, filter.text),
					priority: filter.priority || 50
				};
			}).sortBy('priority').pluck('el').value());
		},

		createFilters: function() {
			var self = this;

			self.filters = {};
			_.each(['all', 'pie', 'line', 'area', 'bar', 'column', 'geo', 'scatter', 'gauge', 'candlestick'], function(type, i) {
				self.filters[type] = {
					text: l10n.library.filters[type],
					key: type,
					priority: (i + 1) * 10
				};
			});
		},

		onFilterChange: function() {
			this.controller.trigger('visualizer:library:filter', this.el.value);
		}
	});

	wpmvvl.Pagination = wpmV.extend({
		id: 'visualizer-library-pagination',
		tagName: 'ul',

		events: {
			'click a.visualizer-library-pagination-page': 'onPageChange'
		},

		initialize: function() {
			_.defaults(this.options, {
				total: 1,
				page: 1
			});
		},

		render: function() {
			var self, items;

			self = this;
			if (self.options.page <= 1 && self.options.total <= 1) {
				self.$el.html('');
				return;
			}

			items = self._pagination(self.options.page, self.options.total, 7);

			self.$el.html(_.chain(items).map(function(item) {
				var content, className;

				content = item == '...' || item == self.options.page
					? self.make('span', { class: 'visualizer-library-pagination-page' }, item)
					: self.make('a', { class: 'visualizer-library-pagination-page', href: 'javascript:;', 'data-page': item }, item);

				className = item == self.options.page
					? 'visualizer-library-pagination-item visualizer-library-pagination-active'
					: 'visualizer-library-pagination-item';

				return self.make('li', { class: className }, content);
			}).value());
		},

		_pagination: function(current, total, max) {
			var i, tmp, pagenation = [];

			if ( total <= max ) {
				for ( i = 1; i <= total; i++ ) {
					pagenation.push(i);
				}
			} else {
				tmp = current - Math.floor( max / 2 );

				if ( max % 2 == 0 ) {
					tmp++;
				}

				if ( tmp < 1 ) {
					tmp = 1;
				}

				if ( tmp + max > total ) {
					tmp = total - max + 1;
				}

				for ( i = 1; i <= max; i++ ) {
					pagenation.push(tmp++);
				}

				if ( pagenation[0] != 1 ) {
					pagenation[0] = 1;
					pagenation[1] = '...';
				}

				if ( pagenation[max - 1] != total ) {
					pagenation[max - 1] = total;
					pagenation[max - 2] = '...';
				}
			}

			return pagenation;
		},

		onPageChange: function(e) {
			this.controller.trigger('visualizer:library:page', $(e.target).data('page'));
		}
	});
})(jQuery, wp.media);