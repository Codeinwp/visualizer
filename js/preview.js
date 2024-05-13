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

        // Update google chart filter control.
        window.vizUpdateFilterControl = function() {
        	$( '#control_wrapper_canvas' ).removeClass( 'no-filter' );
			var controlsOpt = $( '.vz-controls-opt' ).map(
				function() {
					var val = $(this).val();
					return '' !== val && 'false' !== val ? val : false ;
				}
				).get();
			controlsOpt = controlsOpt.filter( function(el) { return el; } );
			if ( controlsOpt.length === 0 ) {
				$( '#control_wrapper_canvas' ).addClass( 'no-filter' ).html('');
			}
        }

		/**
		 * Capture the relevant form data.
		 * 
		 * @returns {Object} The captured form data.
		 */
		function captureFormData() {
			const formData = new FormData(document.querySelector('#settings-form'));
			const featureData = {};

			formData.forEach((value, optionKey) => {
				if ( 
					! value || 
					( ! ['permissions', 'controls', 'manual', 'lazy'].some( key => optionKey.startsWith(key) ) )
				) {
					return;
				}

				if ( optionKey.endsWith('[]') ) {
					if( !featureData[optionKey] ) {
						featureData[optionKey] = [];
					}
					featureData[optionKey].push(value);
				} else {
					featureData[optionKey] = value;
				}
			});

			for (const key in featureData) {
				if (Array.isArray(featureData[key])) {
					featureData[key] = featureData[key].join(','); // We can not send arrays in the tracking data.
				}
			}

			return featureData;
		}

		const initialCaptureData = captureFormData();

		/**
		 * Capture feature usage.
		 */
		async function trackSavedData() {
			const savedData = captureFormData();

			// Remove default values.
			for (const key in savedData) {

				if ( key === 'lazy_load_chart' ) {
					continue;
				}

				if ( savedData[key] === initialCaptureData[key] ) {
					delete savedData[key];
				}
			}

			const hasPermission = Object.keys(savedData).some(key => key.startsWith('permissions')).length > 0;
			const hasControls = Object.keys(savedData).some(key => key.startsWith('controls')).length > 0;

			const featureData = {
				lazy: savedData.lazy_load_chart,
				permissions: hasPermission ? 'used' : 'ignored',
				controls: hasControls ? 'used' : 'ignored',
			};

			if (savedData.manual) {
				featureData['manualConfig'] = savedData.manual;
			}

			const urlParams = new URLSearchParams(window.location.search);

			window?.tiTrk?.with('visualizer')?.add({
				feature: 'chart-edit',
				featureComponent: 'saved-data',
				featureData,
				groupId: urlParams.get('chart') ?? ''
			});

			// Do not make the user to wait too long for the event to be uploaded.
			const timer = new Promise((resolve) => setTimeout(resolve, 500));
			await Promise.race([timer, window?.tiTrk?.uploadEvents()]);
		}

		document.querySelector('#settings-button')?.addEventListener('click', async function() {
			if( typeof window.tiTrk !== 'undefined' ) {
				try {
					await trackSavedData();
				} catch (e) {
					console.warn(e);
				}
			}

			// Get and send the chart ID (used by Visualizer Block).
			var urlParams = new URLSearchParams(window.location.search);
			var chartID = urlParams.get('chart');
			if ( chartID ) {
				window.parent.postMessage({ chartID: chartID}, '*');
			}

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
			vizUpdateFilterControl();
			clearTimeout(timeout);
			timeout = setTimeout(function() {
				var settings = $('#settings-form').serializeObject();
				settings = JSON.stringify( settings ).replace( /<\/?[^>]+(>|$)/g, '' );
				settings = JSON.parse( settings );
				delete settings['width'];
				delete settings['height'];

				v.charts.canvas.settings = settings;
                $('body').trigger('visualizer:render:currentchart:update', {visualizer: v});
                vizSettingsHaveChanged(true);
			}, 1500);
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