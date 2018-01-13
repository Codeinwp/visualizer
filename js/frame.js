/* global prompt */
/* global visualizer */
/* global alert */
/* global ajaxurl */

(function ($) {
    $(document).ready(function () {
        init_permissions();
        init_db_import();

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

    function init_db_import(){
        $( '#visualizer-db-wizard' ).css("z-index", "-1").hide();

        // add the first where clause.
        $('.db-wizard-templates').append($('.db-wizard-where-template').clone().removeClass('db-wizard-where-template').show());

        $('.db-wizard-query select').chosen({
            width               : '50%',
            search_contains     : true
        });

        // trigger for new where clauses.
        $('.db-wizard-where-template-add').on('click', function(e){
            e.preventDefault();
            $('.db-wizard-templates').append($('.db-wizard-where-template').clone().removeClass('db-wizard-where-template').addClass('visualizer-select-where-added').show());
            $('.db-wizard-templates .visualizer-select-where-added select').chosen({
                width               : '50%',
                search_contains     : true
            });

            // show the condition operator and operand.
            $('.db-wizard-templates .visualizer-select-where-added select.visualizer-select-where').on('change', function(evt, params) {
                display_where_clause(evt, params, $(this));
            });

            $('.db-wizard-templates .visualizer-select-where-added .db-wizard-where-template-remove').on('click', function(e){
                $(this).parent().remove();
            });

            $('.db-wizard-templates .visualizer-select-where-added').removeClass('visualizer-select-where-added');
        });

        // show the condition operator and operand.
        $('.visualizer-select-where').on('change', function(evt, params) {
            display_where_clause(evt, params, $(this));
        });

        $('.db-wizard-where-template-remove').on('click', function(e){
            $(this).parent().remove();
        });

        if($('.visualizer-select-select :selected').length === 0){
            $('#visualizer-query-fetch').attr('disabled', 'disabled');
        }

        $('.visualizer-select-select').on('change', function(evt, params) {
            if($('.visualizer-select-select :selected').length > 0){
                $('#visualizer-query-fetch').removeAttr('disabled');
            }else{
                $('#visualizer-query-fetch').attr('disabled', 'disabled');
            }
        });

        // get the columns when table is selected.
        $('.visualizer-select-from').on('change', function(evt, params) {
            start_ajax($('#visualizer-db-wizard'));
            $('.db-wizard-templates').empty();
            $('.db-wizard-where-template-add').trigger('click');
            $('.visualizer-select-select').empty().trigger('chosen:updated');
            $('.visualizer-select-group').empty().trigger('chosen:updated');
            $('.visualizer-select-order').empty().trigger('chosen:updated');
            $('.visualizer-select-limit').val('');
            $('.db-wizard-results').empty();
            $('#visualizer-query-fetch').attr('disabled', 'disabled');

            $.ajax({
                url     : ajaxurl,
                method  : 'post',
                data    : {
                    'action'    : visualizer.ajax['actions']['db_get_cols'],
                    'security'  : visualizer.ajax['nonces']['db_get_cols'],
                    'table'     : params.selected
                },
                success : function(data){
                    var where = $('.visualizer-select-where');
                    var select = $('.visualizer-select-select');
                    var group = $('.visualizer-select-group');
                    var order = $('.visualizer-select-order');

                    where.empty().append('<option value=""></option>');
                    select.empty().append('<option value=""></option>');
                    group.empty().append('<option value=""></option>');
                    order.empty().append('<option value=""></option>');

                    // populate the SELECT clause.
                    $(visualizer.db_wizard.select).each(function(i, clause){
                        if(clause.indexOf('#') === -1){
                            select.append($('<option value="' + clause + '">' + clause + '</option>'));
                        }else{
                            $(data.data.columns).each(function(i, col){
                                var val = clause.replace(/#/g, col.name);
                                select.append($('<option value="' + val + '">' + val + '</option>'));
                            });
                        }
                    });

                    // populate the WHERE, ORDER and GROUPBY clauses.
                    $(data.data.columns).each(function(i, col){
                        $(visualizer.db_wizard.where).each(function(i, clause){
                            if(clause.indexOf('#') !== -1){
                                var val = clause.replace(/#/g, col.name);
                                where.append($('<option value="' + val + '" data-type="' + col.type + '">' + val + '</option>'));
                            }
                        });
                        $(visualizer.db_wizard.group).each(function(i, clause){
                            if(clause.indexOf('#') !== -1){
                                var val = clause.replace(/#/g, col.name);
                                group.append($('<option value="' + val + '">' + val + '</option>'));
                            }
                        });
                        $(visualizer.db_wizard.order).each(function(i, clause){
                            if(clause.indexOf('#') !== -1){
                                var val = clause.replace(/#/g, col.name);
                                order.append($('<option value="' + val + '">' + val + '</option>'));
                            }
                        });
                    });
                    where.trigger('chosen:updated');
                    select.trigger('chosen:updated');
                    group.trigger('chosen:updated');
                    order.trigger('chosen:updated');
                },
                complete: function(){
                    end_ajax($('#visualizer-db-wizard'));
                }
            });
        });

        $('#visualizer-query-fetch').on('click', function(e){
            start_ajax($('#visualizer-db-wizard'));
            $.ajax({
                url     : ajaxurl,
                method  : 'post',
                data    : {
                    'action'    : visualizer.ajax['actions']['db_get_data'],
                    'security'  : visualizer.ajax['nonces']['db_get_data'],
                    'params'    : $('#db-wizard-form').serialize()
                },
                success : function(data){
                    $('.db-wizard-error').contents().filter(function(){ return this.nodeType === 3; }).empty();
                    $('.db-wizard-results').empty();
                    if(data.success){
                        $('.db-wizard-results').html(data.data.table);
                        $('#db-query').html(data.data.query);
                        $('#results').DataTable({
                            "paging":   false
                        });
                    }else{
                        $('.db-wizard-error .query').html(data.data.query);
                        $('.db-wizard-error .msg').html(data.data.msg);
                    }
                },
                complete: function(){
                    end_ajax($('#visualizer-db-wizard'));
                }
            });
        });

        $( '#db-chart-button' ).on( 'click', function(){
            if( $(this).attr( 'data-current' ) === 'chart'){
                $(this).val( $(this).attr( 'data-t-filter' ) );
                $(this).html( $(this).attr( 'data-t-filter' ) );
                $(this).attr( 'data-current', 'filter' );
                $( '.visualizer-editor-lhs' ).hide();
                $( '#visualizer-db-wizard' ).css("z-index", "9999").show();
                $( '#canvas' ).hide();
            }else{
                var filter_button = $(this);
                $( '#visualizer-db-wizard' ).css("z-index", "-1").hide();
                $('#canvas').lock();
                filter_button.val( filter_button.attr( 'data-t-chart' ) );
                filter_button.html( filter_button.attr( 'data-t-chart' ) );
                filter_button.attr( 'data-current', 'chart' );
                $( '#canvas' ).css("z-index", "1").show();
                $( '#db-chart-save-button' ).trigger('click');
            }
        } );

        $( '#db-chart-save-button' ).on( 'click', function(){
            $('#viz-db-wizard-params').val($('#db-wizard-form').serialize());
            $('#vz-db-wizard').submit();
        });
    }

    function display_where_clause(evt, params, where){
        var type = where.find('option[value="' + params.selected + '"]').attr('data-type');
        where.parent().parent().find('.select-condition').removeClass('active');
        where.parent().parent().find('.select-condition.select-condition-' + type).addClass('active');
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