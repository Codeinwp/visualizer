(function(wpmv, wpmvt) {
	var wpmvvl, wpmvtv, wpmvtvb;

	wpmvvl = wpmv.visualizer.Library;

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
						controller: self,
						priority: -100
					}),
					pagination: new wpmvvl.Pagination({
						controller: self,
						priority: 100
					})
				}
			});

			wpmvt.prototype.initialize.apply(self, arguments);
		}
	});

	/**
	 * =========================================================================
	 * Builders toolbars
	 * =========================================================================
	 */
	wpmvtvb = wpmvtv.builder = {};

	wpmvtvb.Types = wpmvt.extend({
		initialize: function() {
			var self = this;

			_.defaults(self.options, {
				close: false,
				items: {
					custom_event: {
						text: wpmv.l10n.visualizer.button.selecttype,
						style: 'primary',
						priority: 80,
						requires: false,
						click: self.selectType
					}
				}
			});

			wpmvt.prototype.initialize.apply(self, arguments);
		},

		selectType: function() {
			this.trigger('visualizer:builder:settype');
		}
	});
})(wp.media.view, wp.media.view.Toolbar);