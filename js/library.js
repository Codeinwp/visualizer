(function($) {
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

		$(window).resize(function() {
			clearTimeout(resizeTimeout);
			resizeTimeout = setTimeout(function() {
				$('.visualizer-chart-canvas').adjust();
			}, 100);
		});
	});
})(jQuery);