/* global console */

(function($) {
    var all_charts;

    function renderChart(id, v) {
        var chart, render, container, series, data, table, settings, i, j, row, date, axis, property, format, formatter;

        chart = all_charts[id];

        if(chart.library !== 'datatables'){
            return;
        }

        series = chart.series;
        data = chart.data;

        container = document.getElementById(id);
        if (container == null) {
            return;
        }

        if($('#' + id).find('table.dataTable').length === 0){
            $('#' + id).append($('<table class="dataTable table table-striped"></table>'));
        }

        var cols = [];
        for (j = 0; j < series.length; j++) {
            var col = { title: series[j].label, data: series[j].label, type: series[j].type };
            if(typeof chart.settings['cssClassNames'] !== 'undefined' && typeof chart.settings['cssClassNames']['tableCell'] !== 'undefined'){
                col['className'] = chart.settings['cssClassNames']['tableCell'];
            }
            cols.push(col);
        }

        var rows = [];
        for (i = 0; i < data.length; i++) {
            row = [];
            for (j = 0; j < series.length; j++) {
                row[ series[j].label ] = data[i][j];
            }
            rows.push(row);
        }

        var settings = {
            destroy: true,
            paging: false,
            searching: false,
            ordering: true,
            select: false,
            lengthChange: false
        };

        if(typeof v.page_type !== 'undefined'){
            switch(v.page_type){
                case 'library':
                    $.extend( settings, { 
                            scrollX: 150,
                            scrollY: 180,
                            scrollCollapse: true
                    } );
                    break;
                case 'frontend':
                case 'chart':
                    // empty.
                    break;
            }
        }

        var select = {
            info: false
        };
        $.extend( settings, { select } );

        // in preview mode, cssClassNames will not exist when a color is changed.
        if(typeof chart.settings['cssClassNames'] !== 'undefined'){
            if(typeof chart.settings['cssClassNames']['oddTableRow'] !== 'undefined'){
                $.extend($.fn.dataTable.ext.classes, { sStripeOdd: chart.settings['cssClassNames']['oddTableRow'] } );
            }

            if(typeof chart.settings['cssClassNames']['evenTableRow'] !== 'undefined'){
                $.extend($.fn.dataTable.ext.classes, { sStripeEven: chart.settings['cssClassNames']['evenTableRow'] } );
            }

            if(typeof chart.settings['cssClassNames']['selectedTableItem'] !== 'undefined'){
                $.extend( select, { 
                    className: chart.settings['cssClassNames']['selectedTableItem']
                } );
            }
        }

        for (var i in chart.settings) {
            var valoo = chart.settings[i];

            // remove the type suffix to get the name of the setting.
            i = i.replace(/_bool/g, '');

            switch(valoo){
                case 'true':
                    valoo = true;
                    break;
                case 'false':
                    valoo = false;
                    break;
            }

            // if the setting name has an '_' this means it is a sub-setting e.g. select_items means { select: { items: ... } }.
            var array = i.split('_');
            if(array.length === 2){
                i = eval( array[0] );
                i[ array[1] ] = valoo;
            }
            settings[i] = valoo;
        }

        $.extend( $.fn.dataTable.defaults, settings );

        // allow user to extend the settings.
        $('body').trigger('visualizer:chart:settings:extend', {id: id, chart: chart, settings: settings});

        table = $('#' + id + ' table.dataTable');
        table.DataTable( {
            data: rows,
            columns: cols,
        } );
    }

    function render(v) {
        for (var id in (all_charts || {})) {
            renderChart(id, v);
        }
    }

    if(typeof visualizer !== 'undefined'){
        // called while updating the chart.
        visualizer.update = function(){
            renderChart('canvas', visualizer);
        };
    }

    $('body').on('visualizer:render:chart:start', function(event, v){
        all_charts = v.charts;
        render(v);
    });


})(jQuery);

