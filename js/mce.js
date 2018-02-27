/* global tinymce */
/* jshint unused:false */
(function($) {
	tinymce.PluginManager.add('visualizer_mce_button', function( editor, url ) {
		editor.addButton( 'visualizer_mce_button', {
			title: editor.getLang( 'visualizer_tinymce_plugin.plugin_label' ),
			label: editor.getLang( 'visualizer_tinymce_plugin.plugin_label' ),
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

})(jQuery);
