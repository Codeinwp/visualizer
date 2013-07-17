(function(wpm) {
	var wpmmv, wpmc;

	wpmc = wpm.controller;
	wpmmv = wpm.model.visualizer;

	wpmc.Visualizer = wpmc.State.extend({
		defaults: {
			toolbar: 'visualizer',
			content: 'library',
			sidebar: 'visualizer',
			router: 'visualizer'
		},

		initialize: function() {
			this.library = new wpmmv.Charts();
		}
	});
})(wp.media);