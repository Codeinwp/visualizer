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

function createPopupProBlocker( $ , e ) {
    if ( ! visualizer.is_pro_user && e.target.classList.contains('viz-is-pro-chart') ) {
        $("#overlay-visualizer").css("display", "block");
        $(".vizualizer-renew-notice-popup").css("display", "block");
        return true;
    }
    return false;
}

(function ($, vmv, vu) {
    var resizeTimeout;

    $.fn.adjust = function () {
        if ( $( '#visualizer-library' ).hasClass( 'view-list' ) ) {
            return this;
        }
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

        // Copy shortcode when clicking the code display in list view.
        $( document ).on( 'click', '.viz-shortcode-display', function () {
            var text = $( this ).text();
            var el   = this;

            // Fallback copy method using a temporary textarea. Returns true on success.
            var fallbackCopy = function ( value ) {
                var ta = document.createElement( 'textarea' );
                ta.value = value;
                document.body.appendChild( ta );
                ta.select();
                var succeeded = false;
                try {
                    succeeded = document.execCommand( 'copy' );
                } catch ( e ) {
                    succeeded = false;
                }
                document.body.removeChild( ta );
                return succeeded;
            };

            // Apply temporary "copied" feedback.
            var showCopiedFeedback = function () {
                $( el ).addClass( 'viz-shortcode-copied' );
                setTimeout( function () {
                    $( el ).removeClass( 'viz-shortcode-copied' );
                }, 1200 );
            };

            if ( navigator.clipboard && navigator.clipboard.writeText ) {
                navigator.clipboard.writeText( text ).then( function () {
                    // Clipboard API succeeded.
                    showCopiedFeedback();
                } ).catch( function () {
                    // Clipboard API failed; fall back to textarea method.
                    if ( fallbackCopy( text ) ) {
                        showCopiedFeedback();
                    }
                } );
            } else {
                // No Clipboard API; use fallback directly.
                if ( fallbackCopy( text ) ) {
                    showCopiedFeedback();
                }
            }
        } );

        $('.visualizer-chart-shortcode').click(function (event) {

            if ( createPopupProBlocker( $, event ) ) {
                event.preventDefault();
                event.stopPropagation();
                return;
            }

            var range, selection;

            if (window.getSelection && document.createRange) {
                selection = window.getSelection();
                range = document.createRange();
                range.selectNodeContents(event.target);
                selection.removeAllRanges();
                selection.addRange(range);
            } else if (document.selection && document.body.createTextRange) {
                range = document.body.createTextRange();
                range.moveToElementText(event.target);
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

        function openClassicBuilder() {
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
        }

        function closeAddNewMenus() {
            $('.viz-add-new-group').removeClass('is-open');
            $('.viz-add-new-toggle').attr('aria-expanded', 'false');
            $('.viz-add-new-menu').attr('aria-hidden', 'true');
        }

        $(document).on('click', function() {
            closeAddNewMenus();
        });

        $(document).on('click', '.viz-add-new-toggle', function(event) {
            event.preventDefault();
            event.stopPropagation();
            event.stopImmediatePropagation();
            var $group = $(this).closest('.viz-add-new-group');
            var isOpen = $group.hasClass('is-open');
            closeAddNewMenus();
            if ( ! isOpen ) {
                $group.addClass('is-open');
                $(this).attr('aria-expanded', 'true');
                $group.find('.viz-add-new-menu').attr('aria-hidden', 'false');
            }
            return false;
        });

        $(document).on('click', '.viz-add-new-item', function(event) {
            event.preventDefault();
            event.stopPropagation();
            event.stopImmediatePropagation();
            var builder = $(this).data('viz-builder');
            closeAddNewMenus();

            if ( builder === 'ai' ) {
                if ( typeof window.vizOpenAIBuilderNew === 'function' ) {
                    window.vizOpenAIBuilderNew();
                    return;
                }
                if ( typeof window.vizOpenChartChooser === 'function' ) {
                    var classicCallback = function() {
                        openClassicBuilder();
                    };
                    window.vizOpenChartChooser( classicCallback );
                    return;
                }
            }

            openClassicBuilder();
        });

        $('.add-new-chart').click(function () {
            // Hook for the React chart builder chooser modal.
            // If the React app is loaded it sets window.vizOpenChartChooser and handles routing.
            // It will call the classicCallback if the user picks the Classic Builder.
            if (typeof window.vizOpenChartChooser === 'function') {
                var classicCallback = function() {
                    openClassicBuilder();
                };
                window.vizOpenChartChooser(classicCallback);
                return false;
            }
            openClassicBuilder();
            return false;
        });

        $('.visualizer-chart-edit').click(function (event) {

            if ( createPopupProBlocker( $, event ) ) {
                return;
            }

            // D3/AI charts open in the AI Builder instead of the classic modal.
            if ( $(this).data('library') === 'd3' && typeof window.vizOpenAIBuilderEdit === 'function' ) {
                window.vizOpenAIBuilderEdit( $(this).attr('data-chart') );
                return false;
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
            if ( createPopupProBlocker( $, event ) ) {
                event.preventDefault();
            }
        });

        $(".visualizer-chart-export").on("click", function (event) {

            if ( createPopupProBlocker( $, event ) ) {
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

        $(".visualizer-chart-image").on("click", function (event) {
            if ( createPopupProBlocker( $, event ) ) {
                return;
            }
            $('body').trigger('visualizer:action:specificchart', {action: 'image', id: $(this).attr("data-chart"), data: null, dataObj: {name: $(this).attr("data-chart-title")}});
            return false;
        });

        // if vaction=addnew is found as a GET request parameter, show the modal.
        if(location.href.indexOf('vaction=addnew') !== -1){
            $('.add-new-chart').first().trigger('click');
        }

        //if vaction=edit is found as a GET request parameter, show the modal.
        if(location.href.indexOf('vaction=edit') !== -1 && location.href.indexOf('chart=') !== -1){
            const chartId = location.href.split('chart=')[1].split('&')[0];
            $('.visualizer-chart-edit').attr('data-chart', chartId).trigger('click');
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


document.querySelectorAll('.visualizer-chart').forEach(function (chart) {
    const translatable = chart.querySelector('.visualizer-languages-list');
    if ( ! translatable ) {
        return;
    }

    const chartId = chart.querySelector('.visualizer-chart-canvas')?.id?.replace('visualizer-', '');

    if ( ! chartId ) {
        return;
    }

    const translatableActions = translatable.querySelectorAll('[data-lang_code]');
    translatableActions.forEach(function (action) {
        action.addEventListener('click', function () {
            window?.tiTrk?.with('visualizer')?.add({
                feature: 'chart-library',
                featureComponent: 'chart-language-translations-used',
                groupId: chartId,
            });
        });
    });
});
