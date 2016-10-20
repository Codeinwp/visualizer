(function($, wpmm, wpmvlv) {
	wpmm.visualizer = {};

	wpmm.visualizer.Chart = Backbone.Model.extend({
		sync: function(method, model, options) {
			if ('delete' === method) {
				options = options || {};
				options.data = _.extend( options.data || {}, {
					action:  wpmvlv.actions.delete_chart,
					chart: model.get('id'),
					nonce: wpmvlv.nonce
				});

				return wp.media.ajax( options );
			} else {
				return;
			}
		}
	});
})(jQuery, wp.media.model, wp.media.view.l10n.visualizer);
