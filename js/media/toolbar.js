(function(wpmv, wpmvt) {
	var wpmvvl;

	wpmvvl = wpmv.visualizer.Library;

	wpmvt.Visualizer = wpmvt.extend({
		initialize: function() {
			_.defaults( this.options, { close: false });
			wpmvt.prototype.initialize.apply( this, arguments );
		}
	});

	wpmvt.Visualizer.Items = {
		Library: function(controller) {
			return {
				type_filter: new wpmvvl.Types({
					controller: controller,
					priority: -100
				}),
				pagination: new wpmvvl.Pagination({
					controller: controller,
					priority: 100
				})
			};
		},
		Builder: function(controller) {
			return {
				custom_event: {
					text: wpmv.l10n.visualizer.button.selecttype,
					style: 'primary',
					priority: 80,
					requires: false,
					click: function() {
						controller.trigger('visualizer:builder:settype');
					}
				}
			};
		}
	};
})(wp.media.view, wp.media.view.Toolbar);