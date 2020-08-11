/* global console */
/* global visualizer */
/* global jQuery */

(function($, visualizer){

    function initActionsButtons(v) {
        if($('a.visualizer-chart-shortcode').length > 0) {
            var clipboard1 = new ClipboardJS('a.visualizer-chart-shortcode'); // jshint ignore:line
            clipboard1.on('success', function(e) {
                window.alert(v.i10n['copied']);
            });
        }

        if($('a.visualizer-action[data-visualizer-type=copy]').length > 0) {
            $('a.visualizer-action[data-visualizer-type=copy]').on('click', function(e) {
                e.preventDefault();
            });
            var clipboard = new ClipboardJS('a.visualizer-action[data-visualizer-type=copy]'); // jshint ignore:line
            clipboard.on('success', function(e) {
                window.alert(v.i10n['copied']);
            });
        }
        $('a.visualizer-action[data-visualizer-type!=copy]').off('click').on('click', function(e) {
            var type    = $(this).attr( 'data-visualizer-type' );
            var chart   = $(this).attr( 'data-visualizer-chart-id' );
            var container   = $(this).attr( 'data-visualizer-container-id' );
            var lock    = $('.visualizer-front.visualizer-front-' + chart);
            var mime    = $(this).attr( 'data-visualizer-mime' );
            lock.lock();
            e.preventDefault();
            $.ajax({
                url     : v.rest_url.replace('#id#', chart).replace('#type#', type),
                beforeSend: function ( xhr ) {
                    xhr.setRequestHeader( 'X-WP-Nonce', v.wp_nonce );
                },
                success: function(data) {
                    if (data && data.data) {
                        switch(type){
                            case 'csv':
                                var blob = new Blob([data.data.csv], {type: mime });
                                if(window.navigator.msSaveOrOpenBlob){
                                    window.navigator.msSaveOrOpenBlob(blob, data.data.name);
                                } else {
                                    var url = window.URL.createObjectURL(blob);
                                    var $a = $("<a>");
                                    $a.attr("href", url);
                                    $("body").append($a);
                                    $a.attr("download", data.data.name);
                                    $a[0].click();
                                    setTimeout(function () {
                                        window.URL.revokeObjectURL(url);
                                        $a.remove();
                                    }, 100);
                                }
                                break;
                            case 'xls':
                                if(window.navigator.msSaveOrOpenBlob){
                                    blob = new Blob([s2ab(atob(data.data.raw))], {type: '' });
                                    window.navigator.msSaveOrOpenBlob(blob, data.data.name);
                                } else {
                                    var $a = $("<a>"); // jshint ignore:line
                                    $a.attr("href", data.data.csv);
                                    $("body").append($a);
                                    $a.attr("download", data.data.name);
                                    $a[0].click();
                                    $a.remove();
                                }
                                break;
                            case 'print':
                            case 'image':
                                $('body').trigger('visualizer:action:specificchart', {action: type, id: container, data: data.data.csv, dataObj: data.data});
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

    function s2ab(s) {
        var buf = new ArrayBuffer(s.length);
        var view = new Uint8Array(buf);
        for (var i=0; i !== s.length; ++i) {
            view[i] = s.charCodeAt(i) & 0xFF;
        }
        return buf;
    }

    $(document).ready(function(){

        // for updating the currently displayed chart (preview mode)
        window.vizUpdateChartPreview = function(){
            var event = new CustomEvent('visualizer:render:currentchart:update', {detail: {visualizer: visualizer}});
            document.body.dispatchEvent(event);
        };

        // facade loads N times in the library and front end (where N = the number of different chart libraries supported)
        // so all charts are also loaded N times
        // this will ensure that no matter how many times facade is loaded, it initializes all charts only once.
        // fixed as part of the issue to add annotations.
        if(localStorage.getItem( 'viz-facade-loaded' ) === '1'){
            // prevent library from hanging.
            setTimeout( function(){
                localStorage.removeItem( 'viz-facade-loaded' );
            }, 2000);
            return;
        }
        localStorage.setItem( 'viz-facade-loaded', '1');
        // remove the flag so that repeated loading of the library does not cause problems.
        setTimeout( function(){
            localStorage.removeItem( 'viz-facade-loaded' );
        }, 2000);
        
        initChartDisplay();
        initActionsButtons(visualizer);
        registerDefaultActions();
    });

    function initChartDisplay() {
        if(visualizer.is_front == true){ // jshint ignore:line
            displayChartsOnFrontEnd();
        }else{
            showChart();
        }
    }

    /**
     * If an `id` is provided, that particular chart will load.
     */
    function showChart(id) {
        // clone the visualizer object so that the original object is not affected.
        var viz = Object.assign({}, visualizer);
        if(id){
            viz.id = id;
        }
        $('body').trigger('visualizer:render:chart:start', viz);
    }

    function displayChartsOnFrontEnd() {
        // display all charts that are NOT to be lazy-loaded.
        $('div.visualizer-front:not(.visualizer-lazy)').each(function(index, element){
            var id = $(element).attr('id');
            showChart(id);
        });

        // interate through all charts that are to be lazy-loaded and observe each one.
        $('div.visualizer-front.visualizer-lazy').each(function(index, element){
            var id = $(element).attr('id');
            var limit = $(element).attr('data-lazy-limit');
            if(!limit){
                limit = 300;
            }
            var target = document.querySelector('#' + id);

            // configure the intersection observer instance
            var intersectionObserverOptions = {
              root: null,
              rootMargin: limit + 'px',
              threshold: 0
            };
            
            var observer = new IntersectionObserver(function(entries) {
                    entries.forEach(function(entry) {
                        if (entry.isIntersecting) {
                            observer.unobserve(target);
                            showChart($(entry.target).attr('id'));
                        }
                    });
            }, intersectionObserverOptions);

            // start observing.
            observer.observe(target);
        });
    }

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
