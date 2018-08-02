/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, {
/******/ 				configurable: false,
/******/ 				enumerable: true,
/******/ 				get: getter
/******/ 			});
/******/ 		}
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 0);
/******/ })
/************************************************************************/
/******/ ([
/* 0 */
/***/ (function(module, exports) {

var _this = this;

var __ = wp.i18n.__;
var _wp$blocks = wp.blocks,
    registerBlockType = _wp$blocks.registerBlockType,
    Editable = _wp$blocks.Editable;
var InspectorControls = wp.editor.InspectorControls;
var _wp$components = wp.components,
    ToggleControl = _wp$components.ToggleControl,
    SelectControl = _wp$components.SelectControl,
    Spinner = _wp$components.Spinner;


var el = wp.element.createElement;

var consoleLog = function consoleLog(msg) {
    console.log(msg);
};

registerBlockType('visualizer/chart', {
    title: vjs.i10n.plugin,
    icon: 'index-card',
    category: 'common',
    supports: {
        html: false
    },
    attributes: {
        // chart id
        chart_id: {
            type: 'number',
            default: -1
        },
        // the random number that is added to create the container id
        random: {
            type: 'number',
            default: -1
        },
        inspector_loading: {
            type: 'number',
            default: -1
        },
        // the class of the spinner container.
        spinner: {
            type: 'string',
            default: 'v-form-spinner'
        },
        // contains the html to be shown in the block.
        html: {
            type: 'string',
            default: ''
        },
        // the label to show in gutenberg.
        label: {
            type: 'string',
            default: ''
        }
    },
    edit: function edit(props) {
        // temporary state machine: START
        var getTemporaryStateID = function getTemporaryStateID($id, $random) {
            return 'v-temp-' + $id + '-' + $random;
        };

        var createTemporaryState = function createTemporaryState($id, $random) {
            jQuery('<div id="' + getTemporaryStateID($id, $random) + '">').remove().insertAfter('body');
            setTemporaryState($id, $random, 0);
        };

        var removeTemporaryState = function removeTemporaryState() {
            jQuery('#' + getTemporaryStateID(-1, -1)).remove();
            jQuery('#' + getTemporaryStateID(0, 0)).remove();
        };

        var getTemporaryState = function getTemporaryState($id, $random) {
            if (jQuery('#' + getTemporaryStateID($id, $random)).length === 0) {
                createTemporaryState($id, $random);
            }
            return parseInt(jQuery('#' + getTemporaryStateID($id, $random)).val());
        };

        var setTemporaryState = function setTemporaryState($id, $random, $value) {
            if (jQuery('#' + getTemporaryStateID($id, $random)).length === 0) {
                createTemporaryState($id, $random);
            }
            jQuery('#' + getTemporaryStateID($id, $random)).val($value);
        };
        // temporary state machine: END

        var getCreateChartScreen = function getCreateChartScreen() {
            if (getTemporaryState(0, 0) === 1) {
                return;
            }
            setTemporaryState(0, 0, 1);

            props.setAttributes({ label: vjs.i10n.loading });

            wp.apiRequest({ path: vjs.urls.create_form }).then(function (data) {
                if (_this.unmounting) {
                    props.setAttributes({ label: '' });
                    return data;
                }

                props.setAttributes({ label: '', html: data.html, chart_id: data.chart_id });
            });
        };

        var getChartData = function getChartData($id, $random) {
            if (getTemporaryState($id, $random) === 1) {
                return;
            }
            setTemporaryState($id, $random, 1);

            consoleLog("getting chart data for " + $id + $random);

            props.setAttributes({ label: vjs.i10n.loading });

            wp.apiRequest({ path: vjs.urls.get_chart.replace('#', $id).replace('#', $random) }).then(function (data) {
                if (_this.unmounting) {
                    props.setAttributes({ label: '' });
                    return data;
                }

                props.setAttributes({ label: '' });

                consoleLog("got chart data for " + $id + $random);
                consoleLog(data);
                consoleLog("triggering visualizer:gutenberg:renderinline:chart");

                jQuery('body').trigger('visualizer:gutenberg:renderinline:chart', { id: 'visualizer-' + data.chart_id + '-' + data.random, charts: data.charts });
                removeTemporaryState();
            });
        };

        var registerTriggers = function registerTriggers() {
            jQuery('body').off('visualizer:gutenberg:loading:chart').on('visualizer:internal:loading:chart', function (event, data) {
                props.setAttributes({ label: vjs.i10n.loading, html: vjs.i10n.loading });
            });
            jQuery('body').off('visualizer:gutenberg:render:chart').on('visualizer:gutenberg:render:chart', function (event, data) {
                consoleLog(data);
                props.setAttributes({ label: '', html: data.data.html, chart_id: data.data.chart_id, random: data.data.random });
                consoleLog("triggering visualizer:gutenberg:renderinline:chart");
                jQuery('body').trigger('visualizer:gutenberg:renderinline:chart', { id: 'visualizer-' + data.data.chart_id + '-' + data.data.random, charts: data.data.charts });
                removeTemporaryState();
            });
        };

        var innerHTML = function innerHTML() {
            return { __html: props.attributes.html };
        };

        var getInspectorControls = function getInspectorControls() {
            if (!!props.isSelected) {
                return wp.element.createElement(
                    InspectorControls,
                    null,
                    wp.element.createElement(
                        'div',
                        { className: props.attributes.spinner },
                        wp.element.createElement(Spinner, null)
                    )
                );
            }
            return null;
        };

        registerTriggers();

        if (getTemporaryState(props.attributes.chart_id, props.attributes.random) === 0) {
            if (typeof props.attributes.chart_id == "undefined" || props.attributes.chart_id === -1) {
                getCreateChartScreen();
            } else {
                getChartData(props.attributes.chart_id, props.attributes.random);
            }
        }

        return [wp.element.createElement(
            'div',
            { className: props.className },
            props.attributes.label
        ), getInspectorControls(), wp.element.createElement('div', { className: props.className, dangerouslySetInnerHTML: innerHTML() })];
    },
    save: function save(props) {
        return null;
    }
});

$(document).on('ready', function () {
    doMisc();

    function doMisc() {
        $('body').on('change', '.gutenberg-create-chart-source', function (e) {
            var form = $(this).parents("form");
            var value = $(this).val();
            form.find(".gutenberg-create-chart-source-attributes span").hide();
            form.find(".gutenberg-create-chart-source-attributes span[data-source='" + value + "']").show();
            var enctype = form.find(".gutenberg-create-chart-source-attributes span[data-source='" + value + "']").attr("data-form-enctype");
            form.attr("enctype", enctype);

            var type = form.find('.gutenberg-create-chart-type');
            type.show();
            if ('existing' === value) {
                type.hide();
            }
        });

        $('body').on('click', '.gutenberg-create-chart', function (e) {
            $('body').trigger('visualizer:gutenberg:loading:chart', {});

            var form = $(this).parents("form");
            var src = form.find('.gutenberg-create-chart-source').val();
            var type = form.find('.gutenberg-create-chart-type').val();
            var data = new FormData();
            data.append('type', type);
            data.append('source', src);

            switch (src) {
                case 'csv':
                    var file = form.find('.visualizer-data-source-file')[0].files[0];
                    data.append('file', file);
                    break;
                case 'url':
                    data.append('remote_data', form.find('.gutenberg-create-chart-remote').val());
                    break;
                case 'chart':
                    data.append('chart', form.find('.gutenberg-create-chart-chart').val());
                    break;
                case 'existing':
                    data.append('chart', form.find('.gutenberg-create-chart-existing').val());
                    break;
            }

            $.ajax({
                url: vjs.urls.create_chart,
                data: data,
                method: 'POST',
                processData: false,
                contentType: false,
                beforeSend: function beforeSend(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', vjs.nonce);
                },
                success: function success(data) {
                    $('body').trigger('visualizer:gutenberg:render:chart', { data: data });
                }
            });
        });
    }
});

/***/ })
/******/ ]);