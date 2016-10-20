/* global visualizer */
(function($, v) {
	var timeout;

	$(document).ready(function() {
        $('#settings-button').click(function() {
			$('#settings-form').submit();
		});

		function updateChart() {
			clearTimeout(timeout);
			timeout = setTimeout(function() {
				var settings = $('#settings-form').serializeObject();

				delete settings['width'];
				delete settings['height'];

				v.charts.canvas.settings = settings;
				v.render();
			}, 1000);
		}

		$('.control-text').change(updateChart).keyup(updateChart);
		$('.control-select, .control-checkbox').change(updateChart);
		$('.color-picker-hex').wpColorPicker({
			change: updateChart,
			clear: updateChart
		});
	});
})(jQuery, visualizer);

(function($) {
	$.fn.serializeObject = function() {
		var self = this,
			json = {},
			push_counters = {},
			patterns = {
				"validate": /^[a-zA-Z][a-zA-Z0-9_]*(?:\[(?:\d*|[a-zA-Z0-9_]+)\])*$/,
				"key": /[a-zA-Z0-9_]+|(?=\[\])/g,
				"push": /^$/,
				"fixed": /^\d+$/,
				"named": /^[a-zA-Z0-9_]+$/
			};

		this.build = function(base, key, value) {
			base[key] = value;
			return base;
		};

		this.push_counter = function(key) {
			if (push_counters[key] === undefined) {
				push_counters[key] = 0;
			}
			return push_counters[key]++;
		};

		$.each($(this).serializeArray(), function() {
			// skip invalid keys
			if (!patterns.validate.test(this.name)) {
				return;
			}

			var k,
				keys = this.name.match(patterns.key),
				merge = this.value,
				reverse_key = this.name;

			while ((k = keys.pop()) !== undefined) {
				// adjust reverse_key
				reverse_key = reverse_key.replace(new RegExp("\\[" + k + "\\]$"), '');

				if (k.match(patterns.push)) {
					// push
					merge = self.build([], self.push_counter(reverse_key), merge);
				} else if (k.match(patterns.fixed)) {
					// fixed
					merge = self.build([], k, merge);
				} else if (k.match(patterns.named)) {
					// named
					merge = self.build({}, k, merge);
				}
			}

			json = $.extend(true, json, merge);
		});

		return json;
	};
})(jQuery);