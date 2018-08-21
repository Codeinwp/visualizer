// user specific customizations
(function ($) {
    $(document).ready(function(){
        $('body').on('visualizer:format:chart', function(event, data){
            customize_format(data.id, data.data, data.column);
        });
    });

    function customize_format($id, $data, $column) {
/* example add green/red arrows to the specified column of the specified chart
        if($id === 359 && $column === 1) {
            var formatter   = new google.visualization.ArrowFormat();
            formatter.format($data, $column);
        }
*/
    }
})(jQuery);