/* global console */
/* global visualizer */

(function($) {
    var all_charts;
    // so that we know which charts belong to our library.
    var rendered_charts = [];

    function renderChart(id, v) {
        renderSpecificChart(id, all_charts[id], v);
    }

    function renderSpecificChart(id, chart, v) {
        var render, container, series, data, table, settings, i, j, row, date, axis, property, format, formatter, type, rows, cols;

        if(chart.library !== 'datatables'){
            return;
        }
        rendered_charts[id] = 'yes';

        series = chart.series;
        data = chart.data;

        container = document.getElementById(id);
        if (container == null) {
            return;
        }

        if($('#' + id).find('table.visualizer-data-table').length > 0){
            $('#' + id).empty();
        }
        $('#' + id).append($('<table class="dataTable visualizer-data-table table table-striped"></table>'));

        settings = {
            destroy: true,
            paging: false,
            searching: false,
            ordering: true,
            select: false,
            lengthChange: false,
            buttons: {
                buttons: [{
                  extend: 'print',
                  text: '',
                  title: ''
                }]
            },
            dom: 'Bfrtip',
        };

        if(typeof v.page_type !== 'undefined'){
            switch(v.page_type){
                case 'post':
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
        // we cannot use { select } below because https://github.com/Codeinwp/visualizer/issues/357
        $.extend( settings, { info: false } ); // jshint ignore:line

        var stripe = ['', ''];

        // in preview mode, cssClassNames will not exist when a color is changed.
        if(typeof chart.settings['cssClassNames'] !== 'undefined'){
            if(typeof chart.settings['cssClassNames']['oddTableRow'] !== 'undefined'){
                stripe[0] = chart.settings['cssClassNames']['oddTableRow'];
            }

            if(typeof chart.settings['cssClassNames']['evenTableRow'] !== 'undefined'){
                stripe[1] = chart.settings['cssClassNames']['evenTableRow'];
            }

            if(typeof chart.settings['cssClassNames']['selectedTableItem'] !== 'undefined'){
                $.extend( select, { 
                    className: chart.settings['cssClassNames']['selectedTableItem']
                } );
            }
        }

        for (i in chart.settings) {
            var valoo = chart.settings[i];

            // remove the type suffix to get the name of the setting.
            i = i.replace(/_bool/g, '').replace(/_int/g, '');

            switch(valoo){
                case 'true':
                    valoo = true;
                    break;
                case 'false':
                    valoo = false;
                    break;
                default:
                    if(parseInt(valoo) > 0){
                        valoo = parseInt(valoo);
                    }
            }

            // if the setting name has an '_' this means it is a sub-setting e.g. select_items means { select: { items: ... } }.
            var array = i.split('_');
            if(array.length === 2){
                i = eval( array[0] ); // jshint ignore:line
                i[ array[1] ] = valoo;
            }
            settings[i] = valoo;
        }

        $.extend( $.fn.dataTable.defaults, settings );

        cols = [];
        for (j = 0; j < series.length; j++) {
            type = series[j].type;
            switch(type){
                case 'number':
                    type = 'num';
                    break;
                case 'date':
                case 'datetime':
                case 'timeofday':
                    type = 'date';
                    break;
            }

            render = addRenderer(series[j].type, settings.series, j);

            var col = { title: series[j].label, data: series[j].label, type: type, render: render };
            if(typeof chart.settings['cssClassNames'] !== 'undefined' && typeof chart.settings['cssClassNames']['tableCell'] !== 'undefined'){
                col['className'] = chart.settings['cssClassNames']['tableCell'];
            }
            cols.push(col);
        }

        rows = [];
        for (i = 0; i < data.length; i++) {
            row = [];
            for (j = 0; j < series.length; j++) {
                var datum = data[i][j];
                row[ series[j].label ] = datum;
            }
            rows.push(row);
        }

        // allow user to extend the settings.
        $('body').trigger('visualizer:chart:settings:extend', {id: id, chart: chart, settings: settings});

        table = $('#' + id + ' table.visualizer-data-table');
        table.DataTable( {
            data: rows,
            columns: cols,
            stripeClasses: stripe,
        } );
        $('.loader').remove();
    }

    function addRenderer(type, series, index){
        var render = null;
        if(typeof series === 'undefined' || typeof series[index] === 'undefined' || typeof series[index].format === 'undefined' ){
            return render;
        }

        series = series[index];

        /* jshint ignore:start */
        switch(type){
            case 'number':
                var parts = ['', '', '', '', ''];
                if(typeof series.format.thousands !== ''){
                    parts[0] = series.format.thousands;
                }
                if(typeof series.format.decimal !== ''){
                    parts[1] = series.format.decimal;
                }
                if(typeof series.format.precision !== ''){
                    parts[2] = series.format.precision;
                }
                if(typeof series.format.prefix !== ''){
                    parts[3] = series.format.prefix;
                }
                if(typeof series.format.suffix !== ''){
                    parts[4] = series.format.suffix;
                }
                render = $.fn.dataTable.render.number(parts[0], parts[1], parts[2], parts[3], parts[4]);
                break;
            case 'date':
            case 'datetime':
            case 'timeofday':
                if(typeof series.format.to !== 'undefined' && typeof series.format.from !== 'undefined' && series.format.from != '' && series.format.to !== ''){
                    render = $.fn.dataTable.render.moment(series.format.from, series.format.to);
                }
                break;
        }
        /* jshint ignore:end */
        return render;
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

    $('body').on('visualizer:render:specificchart:start', function(event, v){
        renderSpecificChart(v.id, v.chart, v.v);
    });

    // front end actions
    $('body').on('visualizer:action:specificchart', function(event, v){
        switch(v.action){
            case 'print':
                var id = v.id;
                if(typeof rendered_charts[id] === 'undefined'){
                    return;
                }
                $('#' + id + ' .buttons-print').trigger('click');
                break;
        }
    });

})(jQuery);


/* jshint ignore:start */

/**
 * Date / time formats often from back from server APIs in a format that you
 * don't wish to display to your end users (ISO8601 for example). This rendering
 * helper can be used to transform any source date / time format into something
 * which can be easily understood by your users when reading the table, and also
 * by DataTables for sorting the table.
 *
 * The [MomentJS library](http://momentjs.com/) is used to accomplish this and
 * you simply need to tell it which format to transfer from, to and specify a
 * locale if required.
 *
 * This function should be used with the `dt-init columns.render` configuration
 * option of DataTables.
 *
 * It accepts one, two or three parameters:
 *
 *     $.fn.dataTable.render.moment( to );
 *     $.fn.dataTable.render.moment( from, to );
 *     $.fn.dataTable.render.moment( from, to, locale );
 *
 * Where:
 *
 * * `to` - the format that will be displayed to the end user
 * * `from` - the format that is supplied in the data (the default is ISO8601 -
 *   `YYYY-MM-DD`)
 * * `locale` - the locale which MomentJS should use - the default is `en`
 *   (English).
 *
 *  @name datetime
 *  @summary Convert date / time source data into one suitable for display
 *  @author [Allan Jardine](http://datatables.net)
 *  @requires DataTables 1.10+
 *
 *  @example
 *    // Convert ISO8601 dates into a simple human readable format
 *    $('#example').DataTable( {
 *      columnDefs: [ {
 *        targets: 1,
 *        render: $.fn.dataTable.render.moment( 'Do MMM YYYYY' )
 *      } ]
 *    } );
 *
 *  @example
 *    // Specify a source format - in this case a unix timestamp
 *    $('#example').DataTable( {
 *      columnDefs: [ {
 *        targets: 2,
 *        render: $.fn.dataTable.render.moment( 'X', 'Do MMM YY' )
 *      } ]
 *    } );
 *
 *  @example
 *    // Specify a source format and locale
 *    $('#example').DataTable( {
 *      columnDefs: [ {
 *        targets: 2,
 *        render: $.fn.dataTable.render.moment( 'YYYY/MM/DD', 'Do MMM YY', 'fr' )
 *      } ]
 *    } );
 */
(function( factory ) {
    "use strict";
 
    if ( typeof define === 'function' && define.amd ) {
        // AMD
        define( ['jquery'], function ( $ ) {
            return factory( $, window, document );
        } );
    }
    else if ( typeof exports === 'object' ) {
        // CommonJS
        module.exports = function (root, $) {
            if ( ! root ) {
                root = window;
            }
 
            if ( ! $ ) {
                $ = typeof window !== 'undefined' ?
                    require('jquery') :
                    require('jquery')( root );
            }
 
            return factory( $, root, root.document );
        };
    }
    else {
        // Browser
        factory( jQuery, window, document );
    }
}
(function( $, window, document ) {
 
 
$.fn.dataTable.render.moment = function ( from, to, locale ) {
    // Argument shifting
    if ( arguments.length === 1 ) {
        locale = 'en';
        to = from;
        from = 'YYYY-MM-DD';
    }
    else if ( arguments.length === 2 ) {
        locale = 'en';
    }
 
    return function ( d, type, row ) {
        if (!d) return null;
        var m = window.moment( d, from, locale, true );
 
        // Order and type get a number value from Moment, everything else
        // sees the rendered value
        return m.format( type === 'sort' || type === 'type' ? 'x' : to );
    };
};
 
 
}));

/* jshint ignore:end */