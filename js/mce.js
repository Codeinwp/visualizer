/* global tinymce */
/* jshint unused:false */
(function($) {
	tinymce.PluginManager.add('visualizer_mce_button', function( editor, url ) {
		editor.addButton( 'visualizer_mce_button', {
			title: getTranslation( editor, 'plugin_label' ),
			label: getTranslation( editor, 'plugin_label' ),
			icon: 'visualizer-icon',
			onclick: function() {
				var frame = wp.media({
					frame:'post',
					state:'visualizer'
				});
				frame.open( );
			}
		});
	});

    /**
     * Gets the translation from the editor (when classic editor is enabled)
     * OR
     * from the settings array inside the editor (when classic block inside gutenberg)
     */
    function getTranslation(editor, slug){
        var string = editor.getLang('visualizer_tinymce_plugin.' + slug);
        // if the string is the same as the slug being requested for, look in the settings.
        if(string === '{#visualizer_tinymce_plugin.' + slug + '}'){
            string = editor.settings.visualizer_tinymce_plugin[slug];
        }
        return string;
    }

})(jQuery);
