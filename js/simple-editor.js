/* global visualizer1 */

(function($, v) {

    $(document).ready(function(){
        onReady();
        initTable();
    });

    function onReady() {
        $( '#editor-button' ).on( 'click', function(e){
            switch($("#viz-editor-type").val()) {
                case 'text':
                    showTextEditor($(this));
                    break;
                case 'table':
                    showTableEditor($(this));
                    break;
                default:
                    $('body').trigger('visualizer:chart:edit');
            }
        });
    }

    function showTextEditor(button) {
        if( button.attr( 'data-current' ) === 'chart'){

            $('body').off('visualizer:change:action').on('visualizer:change:action', function(e){
                button.val( button.attr( 'data-t-chart' ) );
                button.html( button.attr( 'data-t-chart' ) );
                button.attr( 'data-current', 'chart' );
                $('p.viz-editor-selection').show();
                $('.viz-text-editor').hide();
                $('.viz-simple-editor').hide();
                $( '#canvas' ).css('z-index', '1').show();
            });

            // showing the editor
            button.val( button.attr( 'data-t-editor' ) );
            button.html( button.attr( 'data-t-editor' ) );
            button.attr( 'data-current', 'editor' );
            $('p.viz-editor-selection').hide();
            $('.viz-text-editor').css('z-index', '9999').show();
            $('.viz-simple-editor').css('z-index', '9999').show();
            $( '#canvas' ).css('z-index', '-100').hide();
        }else{
            // showing the chart
            $('#chart-data').val($('#edited_text').val());
            $('#canvas').lock();
            $('#editor-form').submit();

            button.val( button.attr( 'data-t-chart' ) );
            button.html( button.attr( 'data-t-chart' ) );
            button.attr( 'data-current', 'chart' );
            $('p.viz-editor-selection').show();
            $('.viz-text-editor').hide();
            $('.viz-simple-editor').hide();
            $( '#canvas' ).css('z-index', '1').show();
        }
    }

    function initTable() {
        setTimeout(function(){
            $('body').trigger('visualizer:db:editor:table:init', {config: { buttons: [] } });
            $( '#canvas' ).unlock();
        }, 1000);
    }

    function showTableEditor(button) {
        if( button.attr( 'data-current' ) === 'chart'){

            $('body').off('visualizer:change:action').on('visualizer:change:action', function(e){
                button.val( button.attr( 'data-t-chart' ) );
                button.html( button.attr( 'data-t-chart' ) );
                button.attr( 'data-current', 'chart' );
                $('p.viz-editor-selection').show();
                $('.viz-table-editor').hide();
                $('.viz-simple-editor').hide();
                $( '#canvas' ).css('z-index', '1').show();
            });

            // showing the editor
            button.val( button.attr( 'data-t-editor' ) );
            button.html( button.attr( 'data-t-editor' ) );
            button.attr( 'data-current', 'editor' );
            $('p.viz-editor-selection').hide();
            $( '.viz-table-editor' ).css("z-index", "9999").show();
            $('.viz-simple-editor').css('z-index', '9999').show();
            $('body').trigger('visualizer:db:editor:table:redraw', {});
            $( '#canvas' ).css("z-index", "-100").hide();
        }else{
            $('#canvas').lock();
            $('#table-editor-form').submit();

            // showing the chart
            button.val( button.attr( 'data-t-chart' ) );
            button.html( button.attr( 'data-t-chart' ) );
            button.attr( 'data-current', 'chart' );
            $('p.viz-editor-selection').show();
            $('.viz-table-editor').hide();
            $('.viz-simple-editor').hide();
            $( '#canvas' ).css('z-index', '1').show();
        }
    }

})(jQuery, visualizer1);