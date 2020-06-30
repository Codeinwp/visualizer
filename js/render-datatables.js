/* global console */
/* global visualizer */

(function($) {
    var all_charts;
    // so that we know which charts belong to our library.
    var rendered_charts = [];

    // save the settings corresponding to cssClassNames so that when a table is being edited, they stripes etc. don't get reset
    var cssClassNames;

    function renderChart(id, v) {
        var chart = all_charts[id];
        if(chart.library !== 'datatables'){
            return;
        }
        cssClassNames = null;
        renderSpecificChart(id, chart, v);
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

        var $classes = 'dataTable visualizer-data-table table table-striped';

        if(typeof v.page_type !== 'undefined'){
            switch(v.page_type){
                case 'post':
                    // fall-through.
                case 'library':
                    // for smaller screens...
                    if(window.innerWidth < 1500){
                        delete chart.settings['scrollX'];
                        delete chart.settings['scrollY'];
                        delete chart.settings['scrollY_int'];
                        $.extend( settings, {
                            scrollX: 150,
                            scrollY: (( chart.settings['responsive_bool'] === 'true' ? 0.8 : 0.5 ) * parseInt($(container).css('height').replace('px',''))),
                            scrollCollapse: true
                        } );
                    }else{
                        if(parseInt(chart.settings['scrollY_int']) > 180){
                            chart.settings['scrollY_int'];
                        }
                        delete chart.settings['scrollX'];
                        $.extend( settings, {
                            scrollX: 150,
                            scrollY: 180,
                        } );
                    }
                    break;
                case 'chart':
                    delete chart.settings['scrollX']; // jshint ignore:line
                    // fall-through.
                case 'frontend':
                    // empty.
                    break;
            }
        }

        if(typeof chart.settings['scrollX'] !== 'undefined'){
            if(chart.settings['scrollX'] == 'true'){ // jshint ignore:line
                $classes = $classes + ' nowrap';
            }
        }

        $('#' + id).append($('<table class="' + $classes + '"></table>'));

        var select = {
            info: false
        };
        // we cannot use { select } below because https://github.com/Codeinwp/visualizer/issues/357
        $.extend( settings, { info: false } ); // jshint ignore:line

        var stripe = ['', ''];

        if(cssClassNames !== null){
            chart.settings['cssClassNames'] = cssClassNames;
        }

        // in preview mode, cssClassNames will not exist when a color is changed.
        if(typeof chart.settings['cssClassNames'] !== 'undefined'){
            cssClassNames = chart.settings['cssClassNames'];
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

        // remove this so that the eval does not fail.
        delete chart.settings['internal_title'];

        var additional = [];
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
                    if(i === 'pageLength' && (valoo === '' || parseInt(valoo) < 0)){
                        valoo = 1;
                    }
            }

            // if the setting name has an '_' this means it is a sub-setting e.g. select_items means { select: { items: ... } }.
            var array = i.split('_');
            if(array.length === 2){
                i = eval( array[0] ); // jshint ignore:line
                i[ array[1] ] = valoo;
                additional[ array[0] ] = i;
                if(array[0] === 'select' && array[1] === 'info' ){
                    // enable main info or the selection info will not show.
                    $.extend( settings, {info: true} );
                }
            } else {
                settings[i] = valoo;
            }
        }
        $.extend( settings, additional );

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
                // datum could be undefined for dynamic data (e.g. through json).
                if(typeof datum === 'undefined'){
                    datum = data[i][series[j].label];
                }
                row[ series[j].label ] = datum;
            }
            rows.push(row);
        }

        $.extend( settings, {
            data: rows,
            columns: cols,
            stripeClasses: stripe,
        } );

        // allow user to extend the settings.
        $('body').trigger('visualizer:chart:settings:extend', {id: id, chart: chart, settings: settings});

        table = $('#' + id + ' table.visualizer-data-table');
        table.DataTable( settings );

        // header row is handled here as the class is added dynamically to it (after the table is rendered).
        if(typeof chart.settings['cssClassNames'] !== 'undefined'){
            if(typeof chart.settings['cssClassNames']['headerRow'] !== 'undefined'){
                $('#' + id + ' table thead tr').addClass( chart.settings['cssClassNames']['headerRow'] );
            }
        }

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
                if(typeof series.format.precision !== '' && parseInt(series.format.precision) > 0){
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
            default:
                render = $.fn.dataTable.render.extra = function ( data, type, row ) {
                    if((data === true || data === 'true') && typeof series.format !== 'undefined' && series.format.truthy !== ''){
                        data = series.format.truthy.replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, '');
                    }
                    if((data === false || data === 'false') && typeof series.format !== 'undefined' && series.format.falsy !== ''){
                        data = series.format.falsy.replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, '');
                    }
                    return data;
                }
        }
        /* jshint ignore:end */

        return render;
    }

    function render(v) {
        for (var id in (all_charts || {})) {
            renderChart(id, v);
        }
    }

    $('body').on('visualizer:render:chart:start', function(event, v){
        all_charts = v.charts;

        if(v.is_front == true && typeof v.id !== 'undefined'){ // jshint ignore:line
            renderChart(v.id, v);
        } else {
            render(v);
        }
    });

    $('body').on('visualizer:render:specificchart:start', function(event, v){
        renderSpecificChart(v.id, v.chart, v.v);
    });

    $('body').on('visualizer:render:currentchart:update', function(event, v){
        var data = v || event.detail;
        renderSpecificChart('canvas', all_charts['canvas'], data.visualizer);
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