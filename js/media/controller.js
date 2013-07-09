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
			this.chart = new wpmmv.Chart();
			this.chart.on('change:type', this.refresh, this);
		},

		refresh: function() {
			this.frame.toolbar.get().refresh();
		}
	});
})(wp.media);