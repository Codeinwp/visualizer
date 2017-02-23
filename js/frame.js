/* global prompt */
/* global visualizer */
/* global alert */

(function($) {
	$(document).ready(function() {
		$('.type-radio').change(function() {
			$('.type-label-selected').removeClass('type-label-selected');
			$(this).parent().addClass('type-label-selected');
		});

		$('.group-title').click(function() {
			var parent = $(this).parent();

			if (parent.hasClass('open')) {
				parent.removeClass('open');
			} else {
				parent.parent().find('.group.open').removeClass('open');
				parent.addClass('open');
			}
		});

		$('.view-remote-file').click(function() {
			var url = $(this).parent().find('#remote-data').val();

			if (url !== '') {
				if (/^([a-z]([a-z]|\d|\+|-|\.)*):(\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?((\[(|(v[\da-f]{1,}\.(([a-z]|\d|-|\.|_|~)|[!\$&'\(\)\*\+,;=]|:)+))\])|((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=])*)(:\d*)?)(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*|(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)|((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)|((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)){0})(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(\#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test(url)) {
					if (url.substr(url.length - 8) === '/pubhtml') {
						url = url.substring(0, url.length - 8) + '/export?format=csv';
					}

					$('#canvas').lock();
					$(this).parent().submit();
				} else {
					alert(visualizer.l10n.invalid_source);
				}
			}
		});

		$('.view-csv-file').click(function(e) {
            e.preventDefault();
			if ($.trim($(this).parent().find("#csv-file").val()) !== '') {
				$('#canvas').lock();
				$(this).parent().submit();
			}
		});

		$('#thehole').load(function() {
			$('#canvas').unlock();
		});

		$('.section-title').click(function() {
			$(this).toggleClass('open').parent().find('.section-items').toggle();
		});

		$('.more-info').click(function() {
			$(this).parent().find('.section-description:first').toggle();
			return false;
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
    };

    $.fn.unlock = function() {
        $(this).each(function() {
            $(this).find('.locker').remove();
            $(this).css('position', $(this).data('position'));
        });

        return $(this);
    };
})(jQuery);