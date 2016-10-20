/* global google */
(function(wpmv, g) {
	var mediaFrame, wpmvtv;

	wpmvtv = wpmv.toolbar.visualizer;
	mediaFrame = wpmv.MediaFrame.Post;

	g.charts.load("current", { packages: ["corechart", "geochart", "gauge", "table", "timeline"] });

	wpmv.MediaFrame.Post = mediaFrame.extend({
		initialize: function() {
			var self = this;

			mediaFrame.prototype.initialize.apply(self, arguments);

			self.states.add([
				new wp.media.controller.Visualizer({
					id: 'visualizer',
					menu: 'default',
					title: wpmv.l10n.visualizer.controller.title,
					priority: 200,
					type: 'link',
					src: wpmv.l10n.visualizer.buildurl
				})
			]);

			self.on('router:create:visualizer', self.createRouter, self);
			self.on('router:render:visualizer', self.visualizerRouter, self);

			self.on('content:create:library', self.contentCreateLibrary, self);
			self.on('content:create:builder', self.iframeContent, self);
		},

		visualizerRouter: function(view) {
			view.set({
				builder: {
					text: wpmv.l10n.visualizer.routers.create,
					priority: 40
				},
				library: {
					text: wpmv.l10n.visualizer.routers.library,
					priority: 20
				}
			});
		},

		contentCreateLibrary: function(region) {
			var self = this;

			self.toolbar.set(new wpmvtv.Library({controller: self}));
			self.$el.removeClass('hide-toolbar');

			region.view = new wpmv.visualizer.Library({
				controller: self,
				collection: self.state().library
			});
		}
	});
})(wp.media.view, google);
