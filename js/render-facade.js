(function($, v){
    $(document).ready(function(){
        $('body').trigger('visualizer:render:chart:start', v);
    });
})(jQuery, visualizer);