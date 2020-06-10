/* global visualizer */
/* global alert */

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
            try{
                wpmv.MediaFrame.prototype.open.apply(this, arguments);
            }catch(error){
                alert(visualizer.i10n.conflict);
            }
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

        // clears the filters in the library form and submits.
        $('#viz-lib-reset').on('click', function(e){
            e.preventDefault();
            $(this).parent('form')[0].reset();
            $(this).parent('form').find('.viz-filter').each(function(index, el){
                var tag = $(el).prop('tagName').toLowerCase() + (typeof $(el).attr('type') !== 'undefined' ? $(el).attr('type').toLowerCase() : '');
                switch(tag){
                    case 'select':
                        $(el).prop('selectedIndex', 0);
                        break;
                    case 'inputtext':
                        $(el).val('');
                        break;
                }

            });
            $(this).parent('form').submit();
        });

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

        $('.add-new-chart').click(function () {
            var wnd = window,
                view = new vmv.Chart({action: vu.create});

            window.parent.addEventListener('message', function(event){
                switch(event.data) {
                    case 'visualizer:mediaframe:close':
                        view.close();
                        break;
                }
            }, false);

            // remove the 'type' while refreshing the library page on creation of a new chart.
            // this is to avoid cases where users have filtered for chart type A and end up creating chart type B
            // remove 'vaction' as well so that additional actions are removed
            wnd.send_to_editor = function () {
                wnd.location.href = vu.base.replace(/type=[a-zA-Z]*/, '').replace(/vaction/, '');
            };
            view.open();

            return false;
        });

        $('.visualizer-chart-edit').click(function () {
            var wnd = window,
                view = new vmv.Chart({action: vu.edit + '&chart=' + $(this).attr('data-chart')});

            wnd.send_to_editor = function () {
                wnd.location.href = wnd.location.href.replace(/vaction/, '');
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

        $(".visualizer-chart-image").on("click", function () {
            $('body').trigger('visualizer:action:specificchart', {action: 'image', id: $(this).attr("data-chart"), data: null, dataObj: {name: $(this).attr("data-chart-title")}});
            return false;
        });

        // if vaction=addnew is found as a GET request parameter, show the modal.
        if(location.href.indexOf('vaction=addnew') !== -1){
            $('.add-new-chart').first().trigger('click');
        }

        $(window).resize(function () {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(function () {
                $('.visualizer-chart-canvas').adjust();
            }, 100);
        });

        $('.visualizer-error i.error').on('click', function(){
            alert( $(this).attr('data-viz-error') );
        });
    });
})(jQuery, visualizer.media.view, visualizer.urls);