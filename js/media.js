(function(wpmv, g) {
	var mediaFrame, wpmvtg;

	wpmvtg = wpmv.Toolbar.Visualizer;
	mediaFrame = wpmv.MediaFrame.Post;

	g.load("visualization", "1", { packages: ["corechart", "geochart", "gauge"] });

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
					type: 'link'
				})
			]);

			self.on('router:create:visualizer', self.createRouter, self);
			self.on('router:render:visualizer', self.visualizerRouter, self);

			self.on('content:create:library', self.contentCreateLibrary, self);
			self.on('content:create:builder', self.contentCreateBuilder, self);
		},

		visualizerRouter: function(view) {
			view.set({
				builder: {
					text: wpmv.l10n.visualizer.routers.create,
					priority: 20
				},
				library: {
					text: wpmv.l10n.visualizer.routers.library,
					priority: 40
				}
			});
		},

		contentCreateLibrary: function(region) {
			var self = this;

			self.toolbar.set(new wpmvtg({
				controller: self,
				items: wpmvtg.Items.Library(self)
			}));

			region.view = new wpmv.visualizer.Library({
				controller: self,
				collection: self.state().library
			});
		},

		contentCreateBuilder: function(region) {
			var self = this;

			self.toolbar.set(new wpmvtg({
				controller: self,
				items: wpmvtg.Items.Builder(self)
			}));

			region.view = new wpmv.visualizer.Builder({
				controller: self,
				model: self.state().createChart()
			});
		}
	});
})(wp.media.view, google);
