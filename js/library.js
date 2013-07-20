(function(wpmv) {
	var vm, vmv;

	vm = visualizer.media = {};
	vmv = vm.view = {};

	vmv.Chart = wpmv.MediaFrame.extend({
		initialize: function() {
			var self = this;

			_.defaults(self.options, {
				action: '',
				state: 'iframe:visualizer'
			});

			wpmv.MediaFrame.prototype.initialize.apply(self, arguments);

			wpmv.settings.tabUrl = self.options.action;
			self.createIframeStates();
		},

		open: function() {
			wpmv.MediaFrame.prototype.open.apply(this, arguments);
			this.$el.addClass('hide-menu');
		}
	});
})(wp.media.view);

(function($, vmv) {
	var resizeTimeout;

	$.fn.adjust = function() {
		var width = $('#visualizer-library').width(),
			margin = width * 0.02;

		width *= 0.305;
		$(this).width(width - 14).height(width * 0.75).parent().css('margin-right', margin + 'px').css('margin-bottom', margin + 'px');
	}

	$('.visualizer-chart-canvas').adjust();

	$(document).ready(function() {
		$('.visualizer-chart-shortcode').click(function(e) {
			var range, selection;

			if (window.getSelection && document.createRange) {
				selection = window.getSelection();
				range = document.createRange();
				range.selectNodeContents(e.target);
				selection.removeAllRanges();
				selection.addRange(range);
			} else if (document.selection && document.body.createTextRange) {
				range = document.body.createTextRange();
				range.moveToElementText(e.target);
				range.select();
			}
		});

		$('.add-new-h2').click(function() {
			var view = new vmv.Chart({action: visualizer.actions.create});
			view.open();
			return false;
		});

		$(window).resize(function() {
			clearTimeout(resizeTimeout);
			resizeTimeout = setTimeout(function() {
				$('.visualizer-chart-canvas').adjust();
			}, 100);
		});
	});
})(jQuery, visualizer.media.view);