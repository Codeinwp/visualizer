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

function createPopup() {

    var overlay = document.createElement('div');
    overlay.id = 'overlay-visualizer';
    overlay.style.position = 'fixed';
    overlay.style.top = '0';
    overlay.style.left = '0';
    overlay.style.width = '100%';
    overlay.style.height = '100%';
    overlay.style.backgroundColor = 'rgba(0, 0, 0, 0.5)';
    overlay.style.zIndex = '999';
    document.body.appendChild(overlay);

    var link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css';
    document.head.appendChild(link);

    var popup = document.createElement('div');

    var closeIcon = document.createElement('i');
    closeIcon.classList.add('dashicons', 'dashicons-no', 'close-icon');
    closeIcon.addEventListener('click', function() {
        document.body.removeChild(overlay);
        popup.style.display = 'none';
    });
    closeIcon.style.position = 'absolute';
    closeIcon.style.top = '10px';
    closeIcon.style.right = '10px';
    closeIcon.style.cursor = 'pointer';
    closeIcon.style.color = '#333';
    closeIcon.classList.add('fas', 'fa-times', 'close-icon');
    popup.appendChild(closeIcon);

    popup.style.display = 'none';

    popup.style.position = 'fixed';
    popup.style.top = '50%';
    popup.style.left = '50%';
    popup.style.transform = 'translate(-50%, -50%)';
    popup.style.backgroundColor = '#fff';
    popup.style.zIndex = '1000';
    popup.style.backgroundColor = '#fff';
    popup.style.padding = '20px';
    popup.style.border = '1px solid #ccc';
    popup.style.borderRadius = '8px';
    popup.style.boxShadow = '0 2px 4px rgba(0,0,0,0.1)';
    popup.style.zIndex = '1000';
    popup.style.maxWidth = '400px';
    popup.style.fontWeight = 'bold';
    popup.style.marginBottom = '20px';

    var heading = document.createElement('h1');
    heading.textContent = 'Alert!';
    heading.style.color = '#d32f2f';
    heading.style.textAlign = 'left';
    heading.style.marginBottom = '10px';
    popup.appendChild(heading);


    var message = document.createElement('p');
    message.textContent = 'In order to edit premium charts, benefit from updates and support for Visualizer Premium plugin, please renew your license code or activate it.';
    message.style.wordWrap = 'break-word';
    message.style.width = '250px';
    popup.appendChild(message);


    var buttonsContainer = document.createElement('div');
    buttonsContainer.style.display = 'grid';
    buttonsContainer.style.gridTemplateColumns = '1fr'; // Single column layout
    buttonsContainer.style.gap = '10px';
    buttonsContainer.style.textAlign = 'center';
    buttonsContainer.style.marginTop = '20px';


    var link1 = document.createElement('a');
    link1.href = 'https://store.themeisle.com/';
    link1.target = '_blank';
    var button1 = document.createElement('button');
    button1.innerHTML = '<span style="margin-right: 8px;" class="fas fa-shopping-cart "></span> Renew License'; // Add shopping cart icon
    button1.style.padding = '10px 20px';
    button1.style.cursor = 'pointer';
    button1.style.backgroundColor = '#008CBA';
    button1.style.color = 'white';
    button1.style.border = 'none';
    button1.style.textAlign = 'center';
    button1.style.textDecoration = 'none';
    button1.style.display = 'inline-block';
    button1.style.fontSize = '16px';
    button1.style.width = '200px';
    button1.style.height = '50px';
    button1.style.borderRadius = '4px';
    button1.style.transition = 'background-color 0.3s';
    button1.style.fontWeight = 'bold';
    button1.style.marginTop = '10px';
    link1.appendChild(button1);
    buttonsContainer.appendChild(link1);


    var link2 = document.createElement('a');
    link2.href = '/wp-admin/options-general.php#visualizer_pro_license';
    var button2 = document.createElement('button');
    button2.innerHTML = '<span style="margin-right: 8px;" class="fas fa-key"></span> Activate License';
    button2.style.padding = '10px 20px';
    button2.style.cursor = 'pointer';
    button2.style.backgroundColor = '#4CAF50';
    button2.style.color = 'white';
    button2.style.border = 'none';
    button2.style.textAlign = 'center';
    button2.style.textDecoration = 'none';
    button2.style.display = 'inline-block';
    button2.style.fontSize = '16px';
    button2.style.width = '200px';
    button2.style.height = '50px';
    button2.style.borderRadius = '4px';
    button2.style.transition = 'background-color 0.3s';
    button2.style.fontWeight = 'bold';
    button2.style.marginTop = '10px';
    link2.appendChild(button2);
    buttonsContainer.appendChild(link2);

   popup.appendChild(buttonsContainer);

    document.body.appendChild(popup);

    popup.style.display = 'block';
}

(function ($, vmv, vu) {
    var resizeTimeout;

    $.fn.adjust = function () {
        return $(this).each(function () {
            var width = $('#visualizer-library').width(),
                margin = width * 0.02;

            width *= 0.305;
            $(this).prev( '.visualizer-chart-title' ).width(width - 14);
            var ChartHeight = width * 0.93;
            if ( $( '.visualizer-nochart-canvas' ).length === 0 ) {
                ChartHeight   = width * 0.78;
                if ( $( '#visualizer-sidebar' ).hasClass('one-columns') ) {
                    ChartHeight   = width * 0.92;
                }
            }
            $(this).width(width).height( ChartHeight );
        });
    };

    $('.visualizer-chart-canvas').adjust();

    $(document).ready(function () {
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

            if ( ! visualizer.is_pro_user && element.classList.contains('is_pro') ) {
                createPopup();
                e.preventDefault();
                e.stopPropagation();
                return;
            }

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

        $( '.visualizer-languages-list' ).on( 'click', '[data-lang_code]', function() {
            if ( $(this).find( 'i' ).hasClass( 'otgs-ico-add' ) ) {
                vu.create = vu.create + '&lang=' + $(this).data('lang_code') + '&parent_chart_id=' + $(this).data('chart');
                $('.add-new-chart').click();
            } else {
                vu.edit = vu.edit + '&lang=' + $(this).data('lang_code') + '&chart=' + $(this).data('chart');
                $('.visualizer-chart-edit').click();
            }
        } );

        $('.add-new-chart').click(function () {
            var wnd = window,
                view = new vmv.Chart({action: vu.create});
            vu.create = vu.create.replace(/[\?&]lang=[^&]+/, '').replace(/[\?&]parent_chart_id=[^&]+/, '');

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

            if ( ! visualizer.is_pro_user && element.classList.contains('is_pro') ) {
                createPopup();
                return;
            }

            var wnd = window;
            var view = new vmv.Chart( {
                action: vu.edit.indexOf('&chart') != -1 ? vu.edit : vu.edit + '&chart=' + $(this).attr('data-chart')
            } );
            vu.edit = vu.edit.replace(/[\?&]lang=[^&]+/, '');

            wnd.send_to_editor = function () {
                wnd.location.href = wnd.location.href.replace(/vaction/, '');
            };

            view.open();

            return false;
        });
        $(".visualizer-chart-clone").on("click", function ( event ) {
            if ( ! visualizer.is_pro_user && element.classList.contains('is_pro') ) {
                createPopup();
                event.preventDefault();
            }
        });

        $(".visualizer-chart-export").on("click", function () {

            if ( ! visualizer.is_pro_user && element.classList.contains('is_pro') ) {
                createPopup();
                return;
            }

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
            if ( ! visualizer.is_pro_user && element.classList.contains('is_pro') ) {
                createPopup();
                return;
            }
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
        $('.visualizer-chart:not(.visualizer-chart-display), .visualizer-library-pagination').fadeIn(500);
    });
})(jQuery, visualizer.media.view, visualizer.urls);
