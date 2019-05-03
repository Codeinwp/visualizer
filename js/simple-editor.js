/* global visualizer1 */

(function($, v) {

    $(document).ready(function(){
        onReady();
        initTable();
    });

    function onReady() {
        $( '#editor-chart-button' ).on( 'click', function(){
            $('.viz-simple-editor-type').hide();
            if($("#simple-editor-type").is(':checked')){
                switch($("#simple-editor-type").val()) {
                    case 'textarea':
                        showText($(this));
                        break;
                }
            } else {
                showTable($(this));
            }
        });
    }

    function showText(button) {
        if( button.attr( 'data-current' ) === 'chart'){
            button.val( button.attr( 'data-t-editor' ) );
            button.html( button.attr( 'data-t-editor' ) );
            button.attr( 'data-current', 'editor' );
            $('p.simple-editor-type').hide();
            $( '.viz-text-editor' ).css("z-index", "9999").show();
            $( '#canvas' ).css("z-index", "-100").hide();
            $('.viz-simple-editor').css("z-index", "9999").show();
        }else{
            button.val( button.attr( 'data-t-chart' ) );
            button.html( button.attr( 'data-t-chart' ) );
            button.attr( 'data-current', 'chart' );
            $( '.viz-text-editor' ).hide();
            $('p.simple-editor-type').show();
            $( '#canvas' ).css("z-index", "1").show();
            $('.viz-simple-editor').hide();
        }

        $( '#viz-text-editor-button').on('click', function(e){
            $( '#editor-chart-button' ).attr("disabled", "disabled");
            $('#chart-data').val($('#edited_text').val());
            $('#canvas').lock();
            $('#editor-form').submit();
            $( '#editor-chart-button' ).removeAttr("disabled");
            $( '#editor-chart-button' ).trigger('click');
        });
    }

    function initTable() {
        setTimeout(function(){
            $('body').trigger('visualizer:db:editor:table:init', {});
            $( '#canvas' ).unlock();
        }, 1000);

        // when the data is set and the chart is updated, toggle the screen so that the chart is shown
        $('#table-editor-form').on( 'submit', function(e){
            $( '#editor-chart-button' ).trigger('click');
            $('#canvas').lock();
        });
    }

    function showTable(button) {
        if( button.attr( 'data-current' ) === 'chart'){
            button.val( button.attr( 'data-t-editor' ) );
            button.html( button.attr( 'data-t-editor' ) );
            button.attr( 'data-current', 'editor' );
            $( '.viz-table-editor' ).css("z-index", "9999").show();
            $('body').trigger('visualizer:db:editor:table:redraw', {});
            $( '#canvas' ).css("z-index", "-100").hide();
            $('p.simple-editor-type').hide();
            $('.viz-simple-editor').css("z-index", "9999").show();
        }else{
            button.val( button.attr( 'data-t-chart' ) );
            button.html( button.attr( 'data-t-chart' ) );
            button.attr( 'data-current', 'chart' );
            $( '.viz-table-editor' ).hide();
            $( '#canvas' ).css("z-index", "1").show();
            $('.viz-simple-editor').hide();
            $('p.simple-editor-type').show();
        }
    }

})(jQuery, visualizer1);