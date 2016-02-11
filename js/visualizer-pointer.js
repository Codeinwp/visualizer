jQuery(document).ready( function($) {
    visualizer_pointer_open_pointer(0);
    function visualizer_pointer_open_pointer(i) {
        if(visualizer.pointers[i]) {
            pointer = visualizer.pointers[i];
            options = $.extend(pointer.options, {
                close: function () {
                    $.post(ajaxurl, {
                        pointer: pointer.pointer_id,
                        action: 'dismiss-wp-pointer'
                    });
                }
            });
            $(pointer.target).pointer(options).pointer('open');
        }
    }
});