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
	wpmvtv.Builder = wpmvt.extend({
		initialize: function() {
			var self = this;

			_.defaults(self.options, {
				close: false,
				items: {
					button: {
						text: wpmvlvb.selecttype,
						style: 'primary',
						priority: 80,
						requires: false,
						click: self.selectType
					}
				}
			});

			wpmvt.prototype.initialize.apply(self, arguments);
		},

		refresh: function() {
			var self = this,
				type = self.controller.state().chart.get('type');

			if (!_.isUndefined(type)) {
				self.get('button').$el.text(wpmvlvb.create);
			}

			wpmvt.prototype.refresh.apply(self, arguments);
		},

		selectType: function() {
			this.controller.trigger('visualizer:builder:settype');
		}
	});
})(wp.media.view, wp.media.view.Toolbar);