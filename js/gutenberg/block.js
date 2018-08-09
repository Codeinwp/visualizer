const { __ } = wp.i18n;
const {
	registerBlockType,
	Editable,
} = wp.blocks;

const {
	InspectorControls,
} = wp.editor;

const { 
    ToggleControl,
    SelectControl,
    Spinner,
} = wp.components;

var el = wp.element.createElement;

const consoleLog = msg => {
    console.log(msg);
}

registerBlockType( 'visualizer/chart', {
	title: vjs.i10n.plugin,
	icon: 'index-card',
	category: 'common',
	supports: {
		html: false,
	},
    attributes: {
        // chart id
        chart_id: {
            type: 'number',
            default: -1
        },
        // the random number that is added to create the container id
        random: {
            type: 'number',
            default: -1
        },
        inspector_loading: {
            type: 'number',
            default: -1
        },
        // the inspector HTML.
        inspector: {
            type: 'string',
            default: ''
        },
        // the class of the spinner container.
        spinner: {
            type: 'string',
            default: 'v-form-spinner',
        },
        // contains the html to be shown in the block.
        html: {
            type: 'string',
            default: '',
        },
        // the label to show in gutenberg.
        label: {
            type: 'string',
            default: vjs.i10n.loading,
        }
    },
    edit: props => {
        // temporary state machine: START
        const getTemporaryStateID = ($id, $random) => {
            return 'v-temp-' + $id + '-' + $random;
        }

        const createTemporaryState = ($id, $random) => {
            jQuery('<div id="' + getTemporaryStateID($id, $random) + '">').remove().insertAfter('body');
            setTemporaryState($id, $random, 0);
        }

        const removeTemporaryState = () => {
            jQuery('#' + getTemporaryStateID(-1, -1)).remove();
            jQuery('#' + getTemporaryStateID(0, 0)).remove();
        }

        const getTemporaryState = ($id, $random) => {
            if(jQuery('#' + getTemporaryStateID($id, $random)).length === 0){
                createTemporaryState($id, $random);
            }
            return parseInt( jQuery('#' + getTemporaryStateID($id, $random)).val() );
        }

        const setTemporaryState = ($id, $random, $value) => {
            if(jQuery('#' + getTemporaryStateID($id, $random)).length === 0){
                createTemporaryState($id, $random);
            }
            jQuery('#' + getTemporaryStateID($id, $random)).val($value);
        }
        // temporary state machine: END

        const getCreateChartScreen = () => {
            if(getTemporaryState(0, 0) === 1){
                return;
            }
            setTemporaryState(0, 0, 1);

            props.setAttributes( { label: vjs.i10n.loading } );

            wp.apiRequest( { path: vjs.urls.create_form } )
                .then(
                    (data) => {
                        if ( this.unmounting ) {
                            props.setAttributes( { label: '' } );
                            return data;
                        }

                        props.setAttributes( { label: '', html: data.html, chart_id: data.chart_id } );
                    }
            );
        };

        const getChartData = ($id, $random) => {
            if(getTemporaryState($id, $random) === 1){
                return;
            }
            setTemporaryState($id, $random, 1);

            consoleLog("getting chart data for " + $id + $random);

            props.setAttributes( { label: vjs.i10n.loading } );

            wp.apiRequest( { path: vjs.urls.get_chart.replace('#', $id).replace('#', $random) } )
                .then(
                    (data) => {
                        if ( this.unmounting ) {
                            props.setAttributes( { label: '' } );
                            return data;
                        }

                        props.setAttributes( { label: '' } );

                        consoleLog("got chart data for " + $id + $random);consoleLog(data);
                        props.setAttributes( { inspector: data.settings } );
                        consoleLog("triggering visualizer:gutenberg:renderinline:chart with");consoleLog(props.attributes);

                        jQuery('body').trigger('visualizer:gutenberg:renderinline:chart', {id: 'visualizer-' + data.chart_id + '-' + data.random, charts: data.charts});
                        removeTemporaryState();
                    }
            );
        };

        const updatePreview = ($settings) => {
            jQuery('body').trigger('visualizer:gutenberg:update:settings', {settings: $settings, id: 'visualizer-' + props.attributes.chart_id + '-' + props.attributes.random});
        }

        const registerTriggers = () => {
            // update the preview
            jQuery('body').off('visualizer:gutenberg:update:preview').on('visualizer:gutenberg:update:preview', function(event, data){
                updatePreview(data);
            });
            // the loading state
            jQuery('body').off('visualizer:gutenberg:loading:chart').on('visualizer:gutenberg:loading:chart', function(event, data){
                props.setAttributes( { label: vjs.i10n.loading, html: '' } );
            });
            // render chart after creation
            jQuery('body').off('visualizer:gutenberg:render:chart').on('visualizer:gutenberg:render:chart', function(event, data){
                props.setAttributes( {  label: '', html: data.data.html, chart_id: data.data.chart_id, random: data.data.random } );
            });
        }

        const innerHTML = () => {
            return { __html: props.attributes.html };
        }

        const settingsInnerHTML = () => {
            return { __html: props.attributes.inspector };
        }

        const isChartValid = () => {
            return ! ( typeof(props.attributes.chart_id) == 'undefined' || props.attributes.chart_id === -1 );
        }

        const getInspectorControls = () => {
            if(!! props.isSelected && isChartValid()){
                return <InspectorControls> 
                        <div className={ props.attributes.spinner }>
                            <Spinner />
                        </div>
                        <div className={ props.className } dangerouslySetInnerHTML={ settingsInnerHTML() }></div>
                    </InspectorControls>;
            }
            return null;
        }

        registerTriggers();

        if(getTemporaryState(props.attributes.chart_id, props.attributes.random) === 0){
            if(!isChartValid()){
                getCreateChartScreen();
            } else {
                consoleLog("calling getChartData for " + props.attributes.chart_id);
                getChartData(props.attributes.chart_id, props.attributes.random);
            }
        }

        return [
            <div className={ props.className }>{ props.attributes.label }</div>,
            getInspectorControls(),
            <div className={ props.className } dangerouslySetInnerHTML={ innerHTML() }></div>,
        ];
    },
    save: props => {
       return null;
    },
} );

jQuery(document).on('ready', function(){
    handleCreateChart();
    handleSettings();
    handleChangeSettings();

    function handleChangeSettings(){
        jQuery('body')
            .on('change keyup', 'form.settings-form .control-text', updateChart)
            .on('change', 'form.settings-form .control-select, form.settings-form .control-checkbox', updateChart)
            .on('change keyup', 'form.settings-form textarea[name="manual"]', validateJSON)
            .on('click', 'form.settings-form .save-settings', saveSettings);
    }

    function saveSettings(e) {
        e.preventDefault();
        var settings = jQuery(this).parents('form.settings-form').serialize();
        jQuery.ajax({
            url     : ajaxurl,
            data    : {
                settings    : settings,
                action      : vjs.ajax.save_settings,
                id          : jQuery(this).parents('form.settings-form').attr('data-chart-id'),
                nonce       : vjs.ajax.nonce
            },
            method  : 'POST',
            success : function(data){
            }
        });
    }

	var timeout;

    function updateChart() {
        var settings = jQuery(this).parents('form.settings-form').serializeObject();
        clearTimeout(timeout);
        timeout = setTimeout(function() {
            consoleLog("triggering visualizer:gutenberg:update:preview");
            jQuery('body').trigger('visualizer:gutenberg:update:preview', settings);
        }, 1000);
    }

    function validateJSON() {
        jQuery(this).parents('form.settings-form').find('.visualizer-error-manual').remove();
        try{
            var options = JSON.parse(jQuery(this).val());
        }catch(error){
            jQuery('<div class="visualizer-error visualizer-error-manual">Invalid JSON: ' + error + '</div>').insertAfter(jQuery(this));
        }
    }

    function handleSettings(){
        jQuery('body').on('click', '.viz-group-title', function() {
            var parent = jQuery(this).parent();

            if (parent.hasClass('open')) {
                parent.removeClass('open');
            } else {
                parent.parent().find('.viz-group.open').removeClass('open');
                parent.addClass('open');
            }

            // TODO: this does not seem to work. If we choose x then y then z, color y is shown. The n-1 color is shown. But double click works.
            jQuery(this).parents('form.settings-form').find('.color-picker-hex').wpColorPicker({
                change: updateChart,
                clear: updateChart
            });

        }).on('click', '.viz-section-title', function() {
            jQuery(this).toggleClass('open').parent().find('.viz-section-items').toggle();
        }).on('click', '.more-info', function() {
            jQuery(this).parent().find('.viz-section-description:first').toggle();
            return false;
        });
    }

    function handleCreateChart() {
        jQuery('body').on('change', '.gutenberg-create-chart-source', function(e){
            var form = jQuery(this).parents("form");
            var value = jQuery(this).val();
            form.find(".gutenberg-create-chart-source-attributes span").hide();
            form.find(".gutenberg-create-chart-source-attributes span[data-source='" + value + "']").show();
            var enctype = form.find(".gutenberg-create-chart-source-attributes span[data-source='" + value + "']").attr("data-form-enctype");
            form.attr("enctype", enctype);

            var type = form.find('.gutenberg-create-chart-type');
            type.show();
            if('existing' === value){
                type.hide();
            }
        });

        jQuery('body').on('click', '.gutenberg-create-chart', function(e){
            jQuery('body').trigger('visualizer:gutenberg:loading:chart', {});

            var form = jQuery(this).parents("form");
            var src = form.find('.gutenberg-create-chart-source').val();
            var type = form.find('.gutenberg-create-chart-type').val();
            var data = new FormData();
            data.append( 'type', type );
            data.append( 'source', src );

            switch(src){
                case 'csv':
                    var file = form.find( '.visualizer-data-source-file' )[0].files[0];
                    data.append( 'file', file );
                    break;
                case 'url':
                    data.append( 'remote_data', form.find('.gutenberg-create-chart-remote').val() );
                    break;
                case 'chart':
                    data.append( 'chart', form.find('.gutenberg-create-chart-chart').val() );
                    break;
                case 'existing':
                    data.append( 'chart', form.find('.gutenberg-create-chart-existing').val() );
                    break;
            }

            jQuery.ajax({
                url     : vjs.urls.create_chart,
                data    : data,
                method  : 'POST',
                processData: false,
			    contentType: false,
                beforeSend: function ( xhr ) {
				    xhr.setRequestHeader( 'X-WP-Nonce', vjs.nonce );
			    },
                success : function(data){
                    consoleLog("triggering visualizer:gutenberg:render:chart for " + src);
                    jQuery('body').trigger('visualizer:gutenberg:render:chart', {data: data});
                }
            });
        });
    }
});

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