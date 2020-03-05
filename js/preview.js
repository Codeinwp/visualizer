/* global visualizer */
/* global console */
/* global vizSettingsHaveChanged */
/* global vizHaveSettingsChanged */

(function($, v) {
	var timeout;

	$(document).ready(function() {
        // when data is impported using csv/url, update the hidden data and the advanced settings sidebar.
        // editor and sidebar are both JSON objects
        window.vizUpdateHTML = function( editor, sidebar ) {
            $('.viz-simple-editor').remove();
            $('#content').append(editor.html);
            $('#settings-form .viz-group').remove();
            $('#settings-form').append(sidebar.html);

            $('#settings-form .control-text').change(updateChart).keyup(updateChart);
            $('#settings-form .control-select, #settings-form .control-checkbox, #settings-form .control-check').change(updateChart);
            $('#settings-form .color-picker-hex').wpColorPicker({
                change: updateChart,
                clear: updateChart
            });
            $('#settings-form textarea[name="manual"]').change(validateJSON).keyup(validateJSON);
            vizSettingsHaveChanged(false);
        };

        $('#settings-button').click(function() {
			$('#settings-form').submit();
		});

        // this portion captures if the settings have changed so that tabs can handle that information.
        var viz_settings_have_changed = false;

        window.vizHaveSettingsChanged = function(){
            return viz_settings_have_changed;
        };

        window.vizSettingsHaveChanged = function(value){
            viz_settings_have_changed = value;
        };
        // this portion captures if the settings have changed so that tabs can handle that information.

		function updateChart() {
			clearTimeout(timeout);
			timeout = setTimeout(function() {
				var settings = $('#settings-form').serializeObject();

				delete settings['width'];
				delete settings['height'];

				v.charts.canvas.settings = settings;
                $('body').trigger('visualizer:render:currentchart:update', {visualizer: v});
                vizSettingsHaveChanged(true);
			}, 1000);
		}

        function validateJSON() {
            $('#visualizer-error-manual').remove();
            try{
                var options = JSON.parse($(this).val());
            }catch(error){
                $('<div class="visualizer-error" id="visualizer-error-manual">Invalid JSON: ' + error + '</div>').insertAfter($(this));
            }
        }

		$('.control-text').change(updateChart).keyup(updateChart);
		$('.control-select, .control-checkbox, .control-check').change(updateChart);
		$('.color-picker-hex').wpColorPicker({
			change: updateChart,
			clear: updateChart
		});
		$('textarea[name="manual"]').change(validateJSON).keyup(validateJSON);

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