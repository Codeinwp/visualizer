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
    //console.log(msg);
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
        ui_loading: {
            type: 'number',
            default: -1
        },
        // the class of the spinner container.
        spinner: {
            type: 'string',
            default: 'pf-form-spinner'
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
        var getCreateChartScreen = function getCreateChartScreen() {
            if (props.attributes.ui_loading === 1) {
                return;
            }

            props.setAttributes({ ui_loading: 1, spinner: 'pf-form-spinner pf-form-loading', label: vjs.i10n.loading });

            wp.apiRequest({ path: vjs.urls.create_form }).then(function (data) {
                if (_this.unmounting) {
                    props.setAttributes({ ui_loading: 0, spinner: 'pf-form-spinner', label: '' });
                    return data;
                }

                props.setAttributes({ ui_loading: 0, spinner: 'pf-form-spinner', html: data.html, label: '' });
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
        if (props.attributes.chart_id === -1 && props.attributes.ui_loading === -1) {
            getCreateChartScreen();
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
        });

        $('body').on('click', '.gutenberg-create-chart', function (e) {
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
                    console.log(data.html);
                    form.parent().html(data.html);
                }
            });
        });
    }
});

/***/ })
/******/ ]);