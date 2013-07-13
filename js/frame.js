(function($) {
	$(document).ready(function() {
		$('.type-radio').change(function() {
			$('.type-label-selected').removeClass('type-label-selected');
			$(this).parent().addClass('type-label-selected');
		});

		$('.group-title').click(function() {
			$(this).parent().toggleClass('open');
		});

		$('#csv-file').change(function() {
			if ($.trim($(this).val()) != '') {
				$('#content').lock();
				$('#csv-form').submit();
			}
		});

		$('#thehole').load(function() {
			$('#content').unlock();
		});
	});
})(jQuery);

(function($) {
    $.fn.lock = function() {
        $(this).each(function() {
            var $this = $(this);
            var position = $this.css('position');

            if (!position) {
                position = 'static';
            }

            switch(position) {
                case 'absolute':
                case 'relative':
                    break;
                default:
                    $this.css('position', 'relative');
                    break;
            }
            $this.data('position', position);

            var width = $this.width(),
                height = $this.height();

            var locker = $('<div class="locker"></div>');
            locker.width(width).height(height);

            var loader = $('<div class="locker-loader"></div>');
            loader.width(width).height(height);

            locker.append(loader);
            $this.append(locker);
            $(window).resize(function() {
                $this.find('.locker,.locker-loader').width($this.width()).height($this.height());
            });
        });

        return $(this);
    }

    $.fn.unlock = function() {
        $(this).each(function() {
            $(this).find('.locker').remove();
            $(this).css('position', $(this).data('position'));
        });

        return $(this);
    }
})(jQuery);