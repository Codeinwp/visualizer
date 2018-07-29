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
    //console.log(msg);
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
        ui_loading: {
            type: 'number',
            default: -1
        },
        // the class of the spinner container.
        spinner: {
            type: 'string',
            default: 'pf-form-spinner',
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
        },
    },
    edit: props => {
        const getCreateChartScreen = () => {
            if(props.attributes.ui_loading === 1){
                return;
            }

            props.setAttributes( { ui_loading: 1, spinner: 'pf-form-spinner pf-form-loading', label: vjs.i10n.loading } );

            wp.apiRequest( { path: vjs.urls.create_form } )
                .then(
                    (data) => {
                        if ( this.unmounting ) {
                            props.setAttributes( { ui_loading: 0, spinner: 'pf-form-spinner', label: '' } );
                            return data;
                        }

                        props.setAttributes( { ui_loading: 0, spinner: 'pf-form-spinner', html: data.html, label: '' } );
                    }
            );
        };

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
        if(props.attributes.chart_id === -1 && props.attributes.ui_loading === -1){
            getCreateChartScreen();
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
        });

        $('body').on('click', '.gutenberg-create-chart', function(e){
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
                    console.log(data.html);
                    form.parent().html(data.html);
                }
            });
        });

        
    }

});