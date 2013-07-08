(function(wpm) {
	var wpmmv, wpmc;

	wpmc = wpm.controller;
	wpmmv = wpm.model.visualizer;

	wpmc.Visualizer = wpmc.State.extend({
		defaults: {
			toolbar: 'visualizer',
			content: 'builder',
			sidebar: 'visualizer',
			router: 'visualizer'
		},

		initialize: function() {
			this.library = new wpmmv.Charts();
		},

		createChart: function() {
			return this.chart = new wpmmv.Chart();
		}
	});
})(wp.media);