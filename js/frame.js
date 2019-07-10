/* global prompt */
/* global visualizer */
/* global alert */
/* global ajaxurl */
/* global CodeMirror */

(function ($) {
    $(window).on('load', function(){
        // scroll to the selected chart type.
        if($('label.type-label.type-label-selected').length > 0) {
            $('label.type-label.type-label-selected')[0].scrollIntoView();
        }
    });

    $(document).ready(function () {
        // open the correct source tab/sub-tab.
        var source = $('#visualizer-chart-id').attr('data-chart-source');
        $('li.viz-group.' + source).addClass('open');
        $('li.viz-group.' + source + ' span.viz-section-title.' + source).addClass('open');
        $('li.viz-group.' + source + ' span.viz-section-title.' + source + '.open').parent().find('div.viz-section-items').show();

        init_permissions();

        if(typeof visualizer !== 'undefined' && visualizer.is_pro) {
            init_db_import();
            init_filter_import();
        }

        init_json_import();

        init_editor_table();

        // update the manual configuation link to point to the correct chart type.
        var type = $('#visualizer-chart-id').attr('data-chart-type');
        var chart_type_in_api_link  = type + 'chart';
        switch (type) {
            case "gauge":
            case "table":
            case "timeline":
                chart_type_in_api_link = type;
                break;
        }

        if($('span.viz-gvlink').length > 0) {
            $('span.viz-gvlink').html($('span.viz-gvlink').html().replace('?', chart_type_in_api_link));
        }

        $('.type-radio').change(function () {
            $('.type-label-selected').removeClass('type-label-selected');
            $(this).parent().addClass('type-label-selected');
        });

        $('#vz-chart-settings h2').click(function () {
            $("#vz-chart-source").hide();
            $("#vz-chart-permissions").removeClass('open').addClass('bottom-fixed');
            $(this).parent().removeClass('bottom-fixed').addClass('open');
	        $("#vz-chart-permissions .viz-group-header").hide();
            return false;
        });
        $('#vz-chart-settings .customize-section-back').click(function () {
            $("#vz-chart-source").show();
            $(this).parent().parent().removeClass('open').addClass('bottom-fixed');

            return false;
        });
        $('.viz-group-title').click(function () {
            var parent = $(this).parent();

            if (parent.hasClass('open')) {
                parent.removeClass('open');
            } else {
                parent.parent().find('.viz-group.open').removeClass('open');
                parent.addClass('open');
            }
        });
        $('#view-remote-file').click(function () {
            var url = $(this).parent().find('#remote-data').val();

            if (url !== '') {
                if (/^([a-z]([a-z]|\d|\+|-|\.)*):(\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?((\[(|(v[\da-f]{1,}\.(([a-z]|\d|-|\.|_|~)|[!\$&'\(\)\*\+,;=]|:)+))\])|((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=])*)(:\d*)?)(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*|(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)|((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)|((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)){0})(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(\#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test(url)) {
                    if (url.substr(url.length - 8) === '/pubhtml') {
                        url = url.substring(0, url.length - 8) + '/export?format=csv';
                    }

                    $('#canvas').lock();
                    $(this).parent().submit();
                } else {
                    alert(visualizer.l10n.invalid_source);
                }
            }
        });

        $('#vz-import-file').click(function (e) {
            e.preventDefault();
            if ($.trim($(this).parent().find("#csv-file").val()) !== '') {
                $('#canvas').lock();
                $(this).parent().submit();
            }
        });

        $('#thehole').load(function () {
            $('#canvas').unlock();
        });

        $('.viz-section-title').click(function () {
            $(this).toggleClass('open').parent().find('.viz-section-items').toggle();
        });

        $('.more-info').click(function () {
            $(this).parent().find('.viz-section-description:first').toggle();
            return false;
        });

        $('#cancel-button').click(function () {
            $('#cancel-form').submit();
        });

    });

    function init_permissions(){
        $('#vz-chart-permissions h2').click(function () {
            $("#vz-chart-source").hide();
            $("#vz-chart-permissions .viz-group-header").show();
            $("#vz-chart-settings").removeClass('open').addClass('bottom-fixed');

            $('#settings-button').click(function(e) {
                e.preventDefault();
                $('#permissions-form').submit();
                $('#settings-form').submit();
            });

            $(this).parent().removeClass('bottom-fixed').addClass('open');

            return false;
        });
        $('#vz-chart-permissions .customize-section-back').click(function () {
            $("#vz-chart-source").show();

            $("#vz-chart-permissions .viz-group-header").hide();
            $('#settings-button').click(function(e) {
                e.preventDefault();
                $('#settings-form').submit();
            });

            $(this).parent().parent().removeClass('open').addClass('bottom-fixed');

            return false;
        });

        $('.visualizer-permission').chosen({
            width               : '50%',
            search_contains     : true
        });
        
        $('.visualizer-permission-type').each(function(x, y){
            var type    = $(y).attr('data-visualizer-permission-type');
            var child   = $('.visualizer-permission-' + type + '-specific');
            if($(y).val() === 'all'){
                child.next('div.chosen-container').hide();
                return;
            }
        });
        
        $('.visualizer-permission-type').on('change', function(evt, params) {
            var type    = $(this).attr('data-visualizer-permission-type');
            var child   = $('.visualizer-permission-' + type + '-specific');
            child.empty();
            if(params.selected === 'all'){
                child.next('div.chosen-container').hide();
                return;
            } else {
                child.next('div.chosen-container').show();
            }
            child.append('<option value="">' + visualizer.l10n['loading'] + '</option>').trigger('chosen:updated');
            $.ajax({
                url     : visualizer.ajax['url'],
                method  : 'post',
                data    : {
                    'action'    : visualizer.ajax['actions']['permissions'],
                    'nonce'     : visualizer.ajax['nonces']['permissions'],
                    'type'      : params.selected
                },
                success : function(d, textStatus, XMLHttpRequest){
                    if(d.success) {
                        child.empty();
                        $.each(d.data, function(k, v){
                            child.append('<option value="' + k + '">' + v + '</option>');
                        });
                        child.trigger('chosen:updated');
                    }
                }
            });
        });
    }

    // https://codemirror.net/
    function init_db_import_component(){
        var table_columns = visualizer.db_query.tables;
        var code_mirror = wp.CodeMirror || CodeMirror;
        var cm = code_mirror.fromTextArea($('.visualizer-db-query').get(0), {
                    value: $('.visualizer-db-query').val(),
                    autofocus: true,
                    mode: 'text/x-mysql',
                    lineWrapping: true,
                    dragDrop: false,
                    matchBrackets: true,
                    autoCloseBrackets: true,
                    extraKeys: {"Ctrl-Space": "autocomplete"},
                    hintOptions: { tables: table_columns }
        });

        // force refresh so that the query shows on first time load. Otherwise you have to click on the editor for it to show.
        $('body').on('visualizer:db:query:focus', function(event, data){
            cm.refresh();
        });

        cm.focus();
        
        // update text area.
        cm.on('inputRead', function(x, y){
            cm.save();
        });

        // backspace and delete do not register so the text box does not get empty if the entire query is deleted
        // from the editor. Let's force this.
        $('body').on('visualizer:db:query:update', function(event, data){
            cm.save();
        });
    }

    function init_filter_import() {
        $( '#db-filter-save-button' ).on( 'click', function(){
            $('#vz-filter-wizard').submit();
        });
    }

    function init_db_import(){
        $( '#visualizer-db-query' ).css("z-index", "-1").hide();

        init_db_import_component();

        $('#visualizer-query-fetch').on('click', function(e){

            $('body').trigger('visualizer:db:query:update', {});
            if($('.visualizer-db-query').val() === ''){
                return;
            }
            
            start_ajax($('#visualizer-db-query'));
            $('.db-wizard-results').empty();
            $('.db-wizard-error').empty();
            $.ajax({
                url     : ajaxurl,
                method  : 'post',
                data    : {
                    'action'    : visualizer.ajax['actions']['db_get_data'],
                    'security'  : visualizer.ajax['nonces']['db_get_data'],
                    'params'    : $('#db-query-form').serialize()
                },
                success : function(data){
                    if(data.success){
                        $('.db-wizard-results').html(data.data.table);
                        $('#results').DataTable({
                            "paging":   false
                        });
                    }else{
                        $('.db-wizard-error').html(data.data.msg);
                    }
                },
                complete: function(){
                    end_ajax($('#visualizer-db-query'));
                }
            });
        });

        $( '#db-chart-button' ).on( 'click', function(){
            $('#content').css('width', 'calc(100% - 300px)');
            if( $(this).attr( 'data-current' ) === 'chart'){
                $(this).val( $(this).attr( 'data-t-filter' ) );
                $(this).html( $(this).attr( 'data-t-filter' ) );
                $(this).attr( 'data-current', 'filter' );
                $( '.visualizer-editor-lhs' ).hide();
                $( '#visualizer-db-query' ).css("z-index", "9999").show();
                $('body').trigger('visualizer:db:query:focus', {});
                $( '#canvas' ).hide();
            }else{
                var filter_button = $(this);
                $( '#visualizer-db-query' ).css("z-index", "-1").hide();
                $('#canvas').lock();
                filter_button.val( filter_button.attr( 'data-t-chart' ) );
                filter_button.html( filter_button.attr( 'data-t-chart' ) );
                filter_button.attr( 'data-current', 'chart' );
                $( '#canvas' ).css("z-index", "1").show();
                $( '#db-chart-save-button' ).trigger('click');
            }
        } );

        $( '#db-chart-save-button' ).on( 'click', function(){
            $('#viz-db-wizard-params').val($('#db-query-form').serialize());
            $('#vz-db-wizard').submit();
        });
    }

    function init_json_import(){
        var regex = new RegExp(visualizer.json_tag_separator, 'g');

        $( '#visualizer-json-screen' ).css("z-index", "-1").hide();
        $('.visualizer-json-form').accordion({
            heightStyle: 'content',
            active: 0
        });

        // toggle between chart and create/modify parameters
        $( '#json-chart-button' ).on( 'click', function(){
            $('#content').css('width', 'calc(100% - 300px)');
            if( $(this).attr( 'data-current' ) === 'chart'){
                $(this).val( $(this).attr( 'data-t-filter' ) );
                $(this).html( $(this).attr( 'data-t-filter' ) );
                $(this).attr( 'data-current', 'filter' );
                $( '.visualizer-editor-lhs' ).hide();
                $( '#visualizer-json-screen' ).css("z-index", "9999").show();
                $( '#canvas' ).hide();
            }else{
                var filter_button = $(this);
                $( '#visualizer-json-screen' ).css("z-index", "-1").hide();
                $('#canvas').lock();
                filter_button.val( filter_button.attr( 'data-t-chart' ) );
                filter_button.html( filter_button.attr( 'data-t-chart' ) );
                filter_button.attr( 'data-current', 'chart' );
                $( '#canvas' ).css("z-index", "1").show();
                $('#canvas').unlock();
            }
        } );

        // fetch the roots for the provided endpoint
        $( '#visualizer-json-fetch' ).on( 'click', function(e){
            e.preventDefault();
            $('.visualizer-json-form').accordion( 'option', 'active', 0 );
            $('.visualizer-json-form h3.viz-step:not(.step1)').addClass('ui-state-disabled');
            $('.json-table').html('');
            start_ajax( $( '#visualizer-json-screen' ) );
            $.ajax({
                url     : ajaxurl,
                method  : 'post',
                data    : {
                    'action'    : visualizer.ajax['actions']['json_get_roots'],
                    'security'  : visualizer.ajax['nonces']['json_get_roots'],
                    'params'    : $('#json-endpoint-form').serialize()
                },
                success : function(data){
                    if(data.success){
                        $('#json-root-form [name="url"]').val(data.data.url);
                        $('#vz-import-json-root').empty();
                        $.each(data.data.roots, function(i, name){
                            $('#vz-import-json-root').append('<option value="' + name + '">' + name.replace(regex, visualizer.json_tag_separator_view) + '</option>');
                        });
                        $('#json-root-form').fadeIn('medium');
                        json_accordion_activate(1, true);
                    }else{
                        alert(visualizer.l10n.json_error);
                    }
                },
                complete: function(){
                    end_ajax($('#visualizer-json-screen'));
                }
            });
        });

        // fetch the data for the chosen root
        $( '#visualizer-json-parse' ).on( 'click', function(e){
            e.preventDefault();
            $('.visualizer-json-form h3.viz-step:not(.step1):not(.step2)').addClass('ui-state-disabled');
            $('.json-table').html('');
            start_ajax( $( '#visualizer-json-screen' ) );
            $.ajax({
                url     : ajaxurl,
                method  : 'post',
                data    : {
                    'action'    : visualizer.ajax['actions']['json_get_data'],
                    'security'  : visualizer.ajax['nonces']['json_get_data'],
                    'params'    : $('#json-root-form').serialize()
                },
                success : function(data){
                    if(data.success){
                        $('#vz-import-json-paging option:not(.static)').remove();
                        if(data.data.paging.length > 0){
                            var $template = $('#vz-import-json-paging').attr('data-template');
                            $.each(data.data.paging, function(i, name){
                                var display = name.replace(regex, visualizer.json_tag_separator_view);
                                display = $template.replace('?', display);
                                $('#vz-import-json-paging').append('<option value="' + name + '">' + display + '</option>');
                            });
                            $('.json-pagination').show();
                        }
                        $('#json-conclude-form [name="url"]').val(data.data.url);
                        $('#json-conclude-form [name="root"]').val(data.data.root);
                        $('#json-conclude-form .json-table').html(data.data.table);

                        var $table = create_editor_table( '#json-conclude-form' );

                        json_accordion_activate(3, true);
                        json_accordion_activate(2, false);
                        $table.columns.adjust().draw();
                    }else{
                        alert(visualizer.l10n.json_error);
                    }
                },
                complete: function(){
                    end_ajax($('#visualizer-json-screen'));
                }
            });
        });

        // when the data is set and the chart is updated, toggle the screen so that the chart is shown
        $('#json-conclude-form').on( 'submit', function(e){
            // populate the form elements that are in the misc tab.
            $('#json-conclude-form-helper .json-form-element').each(function(x, y){
                $('#json-conclude-form').append('<input type="hidden" name="' + y.name + '" value="' + y.value + '">');
            });
            $( '#json-chart-button' ).trigger('click');
            $('#canvas').lock();
        });

        // update the schedule
        $('#json-chart-save-button').on('click', function(e){
            e.preventDefault();
            $('#canvas').lock();
            $.ajax({
                url     : ajaxurl,
                method  : 'post',
                data    : {
                    'action'    : visualizer.ajax['actions']['json_set_schedule'],
                    'security'  : visualizer.ajax['nonces']['json_set_schedule'],
                    'chart'     : $('#vz-json-time').attr('data-chart'),
                    'time'      : $('#vz-json-time').val()
                },
                success : function(data){
                    // do nothing.
                },
                complete: function(){
                    $('#canvas').unlock();
                }
            });
        });

    }

    function init_editor_table() {
        $('body').on('visualizer:db:editor:table:init', function(event, data){
            var $table = create_editor_table('.viz-table-editor');
            $('body').on('visualizer:db:editor:table:redraw', function(event, data){
                $table.draw();
            });
        });
    }

    function create_editor_table(element) {
        var settings = {
            paging: false,
            searching: false,
            ordering: false,
            select: false,
            scrollX: "100%",
            scrollY: "400px",
            info: false,
            colReorder: {
                fixedColumnsLeft: 1
            }
        };

        // show column visibility button only when more than 6 columns are found (including the Label column)
        if($(element + ' table.viz-editor-table thead tr th').length > 6){
            $.extend( settings, { 
                dom: 'Bt',
                buttons: [
                    {
                        extend: 'colvis',
                        columns: ':gt(0)',
                        collectionLayout: 'four-column'
                    }
                ]
            } );
        }

        $.extend( $.fn.dataTable.defaults, settings );
        var $table = $(element + ' .viz-editor-table').DataTable();
        return $table;
    }

    function json_accordion_activate($step, $activate){
        $('.visualizer-json-form h3.viz-step.step' + ( $step + 1 )).removeClass('ui-state-disabled');
        if($activate){
            $('.visualizer-json-form').accordion( 'option', 'active', $step );
        }
    }

    function start_ajax(element){
        element.lock();
    }

    function end_ajax(element){
        element.unlock();
    }

})(jQuery);

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