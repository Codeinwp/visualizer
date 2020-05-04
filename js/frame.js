/* global prompt */
/* global visualizer */
/* global alert */
/* global confirm */
/* global ajaxurl */
/* global CodeMirror */
/* global vizHaveSettingsChanged */

(function ($) {
    $(window).on('load', function(){
        // scroll to the selected chart type.
        if($('label.type-label.type-label-selected').length > 0) {
            $('label.type-label.type-label-selected')[0].scrollIntoView();
        }
    });

    $(document).ready(function () {
        onReady();
    });

    function initTabs(){
        $( "#sidebar" ).lock();
        $( "#viz-tabs" ).tabs({
            create: function( event, ui ) {
                $(this).addClass('done');
                $( "#sidebar" ).unlock();
            },
            beforeActivate: function( event, ui ) {
                // if settings have changed in tab #2 and tab #1 is being activated, warn the user.
                if(vizHaveSettingsChanged() && $(ui.newTab.context).attr('id').includes('viz-tab-basic')){
                    return confirm(visualizer.l10n.save_settings);
                }
            }
        });
    }

    function onReady() {
        initTabs();

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

        // collapse other open sections of this group
        $(document).on('click', '.viz-group-title', function () {
            var parent = $(this).parent();

            if (parent.hasClass('open')) {
                parent.removeClass('open');
            } else {
                parent.parent().find('.viz-group.open').removeClass('open');
                parent.addClass('open');
            }

            /*
             * if the user wants to perform an action and click that tab to change the source
             * and the chart is no longer showing because that particular LHS screen is showing
             * e.g. create parameters, import json, db query box etc.
             * we need to make sure we cancel the current process WITHOUT saving
             * and then show the chart as it was
             * let's close the LHS window and show the chart that is hidden
             */
            $('body').trigger('visualizer:change:action');
        });

        // collapse other open subsections of this section
        $(document).on('click', '.viz-section-title', function () {
            var grandparent = $(this).parent().parent();
            grandparent.find('.viz-section-title.open ~ .viz-section-items').hide();
            grandparent.find('.viz-section-title.open').removeClass('open');
        });

        $('#view-remote-file').click(function () {
            var url = $(this).parent().find('#vz-schedule-url').val();

            if (url !== '') {
                if (url.indexOf('localhost') !== -1 || /^([a-z]([a-z]|\d|\+|-|\.)*):(\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?((\[(|(v[\da-f]{1,}\.(([a-z]|\d|-|\.|_|~)|[!\$&'\(\)\*\+,;=]|:)+))\])|((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=])*)(:\d*)?)(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*|(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)|((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)|((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)){0})(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(\#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test(url)) {
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

        $(document).on('click', '.viz-section-title', function () {
            $(this).toggleClass('open').parent().find('.viz-section-items').toggle();
        });

        $(document).on('click', '.more-info', function () {
            $(this).parent().find('.viz-section-description:first').toggle();
            return false;
        });

        $('#cancel-button').click(function () {
            $('#cancel-form').submit();
        });

        init_type_vs_library();

        $('.viz-abort').on('click', function(e){
            e.preventDefault();
            window.parent.postMessage('visualizer:mediaframe:close', '*');
        });

    }

    function init_type_vs_library() {
        var $data = $('select.viz-select-library').attr('data-type-vs-library');
        if(typeof $data === 'undefined' || $data.length === 0){
            return;
        }
        var $typeVsLibrary = JSON.parse( $('select.viz-select-library').attr('data-type-vs-library') );
        // disable all unsupported libraries for the chart type.
        $('input.type-radio').on('click', function(){
            enable_libraries_for($(this).val(), $typeVsLibrary);
        });

        enable_libraries_for($('input.type-radio:checked').val(), $typeVsLibrary);
    }

    function enable_libraries_for($type, $typeVsLibrary) {
        $('select.viz-select-library option').addClass('disabled').attr('disabled', 'disabled');
        var $libs = $typeVsLibrary[$type];
        $.each($libs, function( i, $lib ) {
            $('select.viz-select-library option[value="' + $lib + '"]').removeClass('disabled').removeAttr('disabled');
        });
        $('select.viz-select-library').val( $('select.viz-select-library option:not(.disabled)').val() );
    }

    function init_permissions(){
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
        
        // clear the editor.
        $('body').on('visualizer:db:query:setvalue', function(event, data){
            cm.setValue(data.value);
            cm.clearHistory();
            cm.refresh();
        });

        // set an option at runtime?
        $('body').on('visualizer:db:query:changeoption', function(event, data){
            cm.setOption(data.name, data.value);
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

            $('body').off('visualizer:change:action').on('visualizer:change:action', function(e){
                var filter_button = $( '#db-chart-button' );
                $( '#visualizer-db-query' ).css("z-index", "-1").hide();
                filter_button.val( filter_button.attr( 'data-t-chart' ) );
                filter_button.html( filter_button.attr( 'data-t-chart' ) );
                filter_button.attr( 'data-current', 'chart' );
                $( '#canvas' ).css("z-index", "1").show();
            });

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
            // submit only if a query has been provided.
            if($('#db-query-form .visualizer-db-query').val().length > 0){
                $('#viz-db-wizard-params').val($('#db-query-form').serialize());
                $('#vz-db-wizard').submit();
            }else{
                $('#canvas').unlock();
            }
        });
    }

    function init_json_import(){
        if(typeof visualizer === 'undefined'){
            return;
        }

        var regex = new RegExp(visualizer.json_tag_separator, 'g');

        $( '#visualizer-json-screen' ).css("z-index", "-1").hide();
        $('.visualizer-json-form').accordion({
            heightStyle: 'content',
            active: 0
        });
        $('.visualizer-json-subform').accordion({
            heightStyle: 'content',
            active: false,
            collapsible: true
        });
        
        // open the accordions by default if they are indicated with the 'open' class.
        $('.visualizer-json-subform .viz-substep.open').each(function(i, e){
            $('.visualizer-json-subform').accordion( "option", "active", i );
        });

        // toggle between chart and create/modify parameters
        $( '#json-chart-button' ).on( 'click', function(){

            $('body').off('visualizer:change:action').on('visualizer:change:action', function(e){
                var filter_button = $( '#json-chart-button' );
                $( '#visualizer-json-screen' ).css("z-index", "-1").hide();
                filter_button.val( filter_button.attr( 'data-t-chart' ) );
                filter_button.html( filter_button.attr( 'data-t-chart' ) );
                filter_button.attr( 'data-current', 'chart' );
                $( '#canvas' ).css("z-index", "1").show();
            });

            $('#content').css('width', 'calc(100% - 100px)');
            if( $(this).attr( 'data-current' ) === 'chart'){
                // toggle from chart to LHS form
                $(this).val( $(this).attr( 'data-t-filter' ) );
                $(this).html( $(this).attr( 'data-t-filter' ) );
                $(this).attr( 'data-current', 'filter' );
                $( '.visualizer-editor-lhs' ).hide();
                $( '#visualizer-json-screen' ).css("z-index", "9999").show();
                $( '#canvas' ).hide();
            }else{
                // toggle from LHS form to chart
                $( '#json-conclude-form' ).trigger('submit');
            }
        } );

        $('body').on('visualizer:json:form:submit', function() {
            var filter_button = $( '#json-chart-button' );
            $( '#visualizer-json-screen' ).css("z-index", "-1").hide();
            $('#canvas').lock();
            filter_button.val( filter_button.attr( 'data-t-chart' ) );
            filter_button.html( filter_button.attr( 'data-t-chart' ) );
            filter_button.attr( 'data-current', 'chart' );
            end_ajax( $( '#visualizer-json-screen' ) );
            $( '#canvas' ).css("z-index", "1").show();
        });


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
                        $('#vz-import-json-root').empty();
                        $.each(data.data.roots, function(i, name){
                            $('#vz-import-json-root').append('<option value="' + name + '">' + name.replace(regex, visualizer.json_tag_separator_view) + '</option>');
                        });
                        $('#json-root-form').fadeIn('medium');
                        json_accordion_activate(1, true);
                    }else{
                        alert(data.data.msg);
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
                    'params'    : $('#json-root-form, #json-endpoint-form').serialize()
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
                        $('#json-conclude-form .json-table').html(data.data.table);

                        var $table = create_editor_table( '#json-conclude-form' );

                        json_accordion_activate(3, true);
                        json_accordion_activate(2, false);
                        $table.columns.adjust().draw();
                    }else{
                        alert(data.data.msg);
                    }
                },
                complete: function(){
                    end_ajax($('#visualizer-json-screen'));
                }
            });
        });

        // when the data is set and the chart is updated, toggle the screen so that the chart is shown
        $('#json-conclude-form').on( 'submit', function(e){
            // at least one column has to be selected as non-excluded.
            var count_selected = 0;
            $('select.viz-select-data-type').each(function(i, element){
                if($(element).prop('selectedIndex') > 0){
                    count_selected++;
                }
            });
            if(count_selected === 0){
                alert(visualizer.l10n.select_columns);
                return false;
            }

            // populate the form elements that are in the other tabs.
            $('#json-conclude-form-helper .json-form-element, #json-endpoint-form .json-form-element, #json-root-form .json-form-element, #vz-import-json .json-form-element').each(function(x, y){
                $('#json-conclude-form').append('<input type="hidden" name="' + y.name + '" value="' + y.value + '">');
            });

            $('body').trigger('visualizer:json:form:submit');
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
            var $table = create_editor_table('.viz-table-editor', data.config);
            $('body').on('visualizer:db:editor:table:redraw', function(event, data){
                $table.draw();
            });
        });
    }

    function create_editor_table(element, config) {
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
        if(config){
            $.extend( settings, config );
        }

        var $table = $(element + ' .viz-editor-table').DataTable(settings);
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