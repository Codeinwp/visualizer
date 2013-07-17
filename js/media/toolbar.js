(function(wpmv, wpmvt) {
	var wpmvvl, wpmvtv, wpmvlvb;

	wpmvvl = wpmv.visualizer.Library;
	wpmvlvb = wpmv.l10n.visualizer.button;

	wpmv.toolbar = wpmv.toolbar || {};
	wpmvtv = wpmv.toolbar.visualizer = {};

	/**
	 * =========================================================================
	 * Library Toolbar
	 * =========================================================================
	 */
	wpmvtv.Library = wpmvt.extend({
		initialize: function() {
			var self = this;

			_.defaults(self.options, {
				close: false,
				items: {
					type_filter: new wpmvvl.Types({
						controller: self.controller,
						priority: -100
					}),
					pagination: new wpmvvl.Pagination({
						controller: self.controller,
						priority: 100
					})
				}
			});

			wpmvt.prototype.initialize.apply(self, arguments);
		}
	});
})(wp.media.view, wp.media.view.Toolbar);