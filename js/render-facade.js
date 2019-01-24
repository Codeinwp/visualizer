/* global console */
/* global visualizer */
/* global jQuery */

(function($, visualizer){

    function initActionsButtons(v) {
        if($('a.visualizer-action[data-visualizer-type=copy]').length > 0) {
            var clipboard = new Clipboard('a.visualizer-action[data-visualizer-type=copy]'); // jshint ignore:line
            clipboard.on('success', function(e) {
                window.alert(v.i10n['copied']);
            });
        }
        $('a.visualizer-action[data-visualizer-type!=copy]').off('click').on('click', function(e) {
            var type    = $(this).attr( 'data-visualizer-type' );
            var chart   = $(this).attr( 'data-visualizer-chart-id' );
            var container   = $(this).attr( 'data-visualizer-container-id' );
            var lock    = $('.visualizer-front.visualizer-front-' + chart);
            lock.lock();
            e.preventDefault();
            $.ajax({
                url     : v.rest_url.replace('#id#', chart).replace('#type#', type),
                success: function(data) {
                    if (data && data.data) {
                        switch(type){
                            case 'csv':
                                var a = document.createElement("a");
                                document.body.appendChild(a);
                                a.style = "display: none";
                                var blob = new Blob([data.data.csv], {type: $(this).attr( 'data-visualizer-mime' ) }),
                                    url = window.URL.createObjectURL(blob);
                                a.href = url;
                                a.download = data.data.name;
                                a.click();
                                setTimeout(function () {
                                    window.URL.revokeObjectURL(url);
                                }, 100);
                                break;
                            case 'xls':
                                var $a = $("<a>");
                                $a.attr("href",data.data.csv);
                                $("body").append($a);
                                $a.attr("download",data.data.name);
                                $a[0].click();
                                $a.remove();
                                break;
                            case 'print':
                                $('body').trigger('visualizer:action:specificchart', {action: 'print', id: container, data: data.data.csv});
                                break;
                            default:
                                if(window.visualizer_perform_action) {
                                    window.visualizer_perform_action(type, chart, data.data);
                                }
                                break;
                        }
                    }
                    lock.unlock();
                }
            });
        });
    }

    $(document).ready(function(){
        $('body').trigger('visualizer:render:chart:start', visualizer);
        initActionsButtons(visualizer);
        registerDefaultActions();
    });

    function registerDefaultActions(){
        $('body').off('visualizer:action:specificchart:defaultprint').on('visualizer:action:specificchart:defaultprint', function(event, v){
            var iframe = $('<iframe>').attr("name", "print-visualization").attr("id", "print-visualization").css("position", "absolute");
            iframe.appendTo($('body'));
            var iframe_doc = iframe.get(0).contentWindow || iframe.get(0).contentDocument.document || iframe.get(0).contentDocument;
            iframe_doc.document.open();
            iframe_doc.document.write(v.data);
            iframe_doc.document.close();
            setTimeout(function(){
                window.frames['print-visualization'].focus();
                window.frames['print-visualization'].print();
                iframe.remove();
            }, 500);
        });
    }
})(jQuery, visualizer);

(function ($) {
    $.fn.lock = function () {
        $(this).each(function () {
            var $this = $(this);
            var position = $this.css('position');

            if (!position) {
                position = 'static';
            }

            switch (position) {
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
            $(window).resize(function () {
                $this.find('.locker,.locker-loader').width($this.width()).height($this.height());
            });
        });

        return $(this);
    };

    $.fn.unlock = function () {
        $(this).each(function () {
            $(this).find('.locker').remove();
            $(this).css('position', $(this).data('position'));
        });

        return $(this);
    };
})(jQuery);