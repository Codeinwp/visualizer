/* global visualizer */
(function (wpmv) {
    var vm, vmv;

    vm = visualizer.media = {};
    vmv = vm.view = {};

    vmv.Chart = wpmv.MediaFrame.extend({
        initialize: function () {
            var self = this;

            _.defaults(self.options, {
                action: '',
                state: 'iframe:visualizer'
            });

            wpmv.MediaFrame.prototype.initialize.apply(self, arguments);

            wpmv.settings.tabUrl = self.options.action;
            self.createIframeStates();
        },

        open: function () {
            wpmv.MediaFrame.prototype.open.apply(this, arguments);
            this.$el.addClass('hide-menu');
        }
    });
})(wp.media.view);

(function ($, vmv, vu) {
    var resizeTimeout;

    $.fn.adjust = function () {
        return $(this).each(function () {
            var width = $('#visualizer-library').width(),
                margin = width * 0.02;

            width *= 0.305;
            $(this).width(width - 14).height(width * 0.75).parent().css('margin-right', margin + 'px').css('margin-bottom', margin + 'px');
        });
    };

    $('.visualizer-chart-canvas').adjust();

    $(document).ready(function () {
        $('.visualizer-chart, .visualizer-library-pagination').fadeIn(500);

        $('.visualizer-chart-shortcode').click(function (e) {
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

        $('.add-new-h2').click(function () {
            var wnd = window,
                view = new vmv.Chart({action: vu.create});

            wnd.send_to_editor = function () {
                wnd.location.href = vu.base;
            };
            view.open();

            return false;
        });

        $('.visualizer-chart-edit').click(function () {
            var wnd = window,
                view = new vmv.Chart({action: vu.edit + '&chart=' + $(this).attr('data-chart')});

            wnd.send_to_editor = function () {
                wnd.location.reload();
            };

            view.open();

            return false;
        });

        $(".visualizer-chart-export").on("click", function () {
            $.ajax({
                url: $(this).attr("data-chart"),
                method: "get",
                success: function (data, textStatus, jqXHR) {
                    var a = document.createElement("a");
                    document.body.appendChild(a);
                    a.style = "display: none";
                    var blob = new Blob([data.data.csv], {type: "application/csv"}),
                        url = window.URL.createObjectURL(blob);
                    a.href = url;
                    a.download = data.data.name;
                    a.click();
                    setTimeout(function () {
                        window.URL.revokeObjectURL(url);
                    }, 100);
                }
            });
            return false;
        });
        $(window).resize(function () {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(function () {
                $('.visualizer-chart-canvas').adjust();
            }, 100);
        });
    });
})(jQuery, visualizer.media.view, visualizer.urls);