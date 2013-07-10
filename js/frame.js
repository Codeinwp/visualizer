(function($) {
	$(document).ready(function() {
		$('.type-radio').change(function() {
			$('.type-label-selected').removeClass('type-label-selected');
			$(this).parent().addClass('type-label-selected');
		});
	});
})(jQuery);