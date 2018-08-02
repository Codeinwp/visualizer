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
            default: '',
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

                        consoleLog("got chart data for " + $id + $random);
                        consoleLog(data);
                        consoleLog("triggering visualizer:gutenberg:renderinline:chart");

                        jQuery('body').trigger('visualizer:gutenberg:renderinline:chart', {id: 'visualizer-' + data.chart_id + '-' + data.random, charts: data.charts});
                        removeTemporaryState();
                    }
            );
        };

        const registerTriggers = () => {
            jQuery('body').off('visualizer:gutenberg:loading:chart').on('visualizer:internal:loading:chart', function(event, data){
                props.setAttributes( { label: vjs.i10n.loading, html: vjs.i10n.loading } );
            });
            jQuery('body').off('visualizer:gutenberg:render:chart').on('visualizer:gutenberg:render:chart', function(event, data){
                consoleLog(data);
                props.setAttributes( {  label: '', html: data.data.html, chart_id: data.data.chart_id, random: data.data.random } );
                consoleLog("triggering visualizer:gutenberg:renderinline:chart");
                jQuery('body').trigger('visualizer:gutenberg:renderinline:chart', {id: 'visualizer-' + data.data.chart_id + '-' + data.data.random, charts: data.data.charts});
                removeTemporaryState();
            });
        }

        const innerHTML = () => {
            return { __html: props.attributes.html };
        }

        const getInspectorControls = () => {
            if(!! props.isSelected){
                return <InspectorControls> 
                        <div className={ props.attributes.spinner }>
                            <Spinner />
                        </div>
                    </InspectorControls>;
            }
            return null;
        }

        registerTriggers();

        if(getTemporaryState(props.attributes.chart_id, props.attributes.random) === 0){
            if(typeof(props.attributes.chart_id) == "undefined" || props.attributes.chart_id === -1){
                getCreateChartScreen();
            } else {
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

$(document).on('ready', function(){
    doMisc();

    function doMisc() {
        $('body').on('change', '.gutenberg-create-chart-source', function(e){
            var form = $(this).parents("form");
            var value = $(this).val();
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

        $('body').on('click', '.gutenberg-create-chart', function(e){
            $('body').trigger('visualizer:gutenberg:loading:chart', {});

            var form = $(this).parents("form");
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

            $.ajax({
                url     : vjs.urls.create_chart,
                data    : data,
                method  : 'POST',
                processData: false,
			    contentType: false,
                beforeSend: function ( xhr ) {
				    xhr.setRequestHeader( 'X-WP-Nonce', vjs.nonce );
			    },
                success : function(data){
                    $('body').trigger('visualizer:gutenberg:render:chart', {data: data});
                }
            });
        });

        
    }

});