(function($, v) {

    function onReady() {
        $( '#editor-chart-button' ).on( 'click', function(){
            if( $(this).attr( 'data-current' ) === 'chart'){
                $(this).val( $(this).attr( 'data-t-editor' ) );
                $(this).html( $(this).attr( 'data-t-editor' ) );
                $(this).attr( 'data-current', 'editor' );
                $( '#chart-editor' ).css("z-index", "9999").show();
                $( '#canvas' ).css("z-index", "-100").hide();
            }else{
                $(this).val( $(this).attr( 'data-t-chart' ) );
                $(this).html( $(this).attr( 'data-t-chart' ) );
                $(this).attr( 'data-current', 'chart' );
                $( '#chart-editor' ).hide();
                $( '#canvas' ).css("z-index", "1").show();
            }
        } );

        $( '#viz-text-editor-button').on('click', function(e){
            $( '#editor-chart-button' ).attr("disabled", "disabled");
            $('#chart-data').val($('#edited_text').val());
            $('#canvas').lock();
            $('#editor-form').submit();
            $( '#editor-chart-button' ).removeAttr("disabled");
            $( '#editor-chart-button' ).trigger('click');
        });
    }

    $(document).ready(function(){
        onReady();
    });

})(jQuery, visualizer1);