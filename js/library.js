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
		$(window).resize(function() {
			clearTimeout(resizeTimeout);
			resizeTimeout = setTimeout(function() {
				$('.visualizer-chart-canvas').adjust();
			}, 100);
		});
	});
})(jQuery);