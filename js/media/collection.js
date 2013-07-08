(function(wpmm) {
	wpmm.visualizer.Charts = Backbone.Collection.extend({
		model: wpmm.visualizer.Chart,

		sync: function(method, model, options) {
			if ('read' === method) {
				options = options || {};
				options.type = 'GET';
				options.data = _.extend( options.data || {}, {
					action:  wp.media.view.l10n.visualizer.actions.get_charts
				});

				return wp.media.ajax( options );
			} else {
				return Backbone.sync.apply( this, arguments );
			}
		}
	});
})(wp.media.model);