/*!
 * Variation Gallery for WooCommerce
 *
 * Author: Emran Ahmed ( emran.bd.08@gmail.com )
 * Date: 9/29/2026, 6:08:47 PM
 * Released under the GPLv3 license.
 */
/******/ (function() { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./src/js/WooVariationGalleryAdmin.js":
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   WooVariationGalleryAdmin: function() { return /* binding */ WooVariationGalleryAdmin; }
/* harmony export */ });
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _regenerator() { /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/babel/babel/blob/main/packages/babel-helpers/LICENSE */ var e, t, r = "function" == typeof Symbol ? Symbol : {}, n = r.iterator || "@@iterator", o = r.toStringTag || "@@toStringTag"; function i(r, n, o, i) { var c = n && n.prototype instanceof Generator ? n : Generator, u = Object.create(c.prototype); return _regeneratorDefine2(u, "_invoke", function (r, n, o) { var i, c, u, f = 0, p = o || [], y = !1, G = { p: 0, n: 0, v: e, a: d, f: d.bind(e, 4), d: function d(t, r) { return i = t, c = 0, u = e, G.n = r, a; } }; function d(r, n) { for (c = r, u = n, t = 0; !y && f && !o && t < p.length; t++) { var o, i = p[t], d = G.p, l = i[2]; r > 3 ? (o = l === n) && (u = i[(c = i[4]) ? 5 : (c = 3, 3)], i[4] = i[5] = e) : i[0] <= d && ((o = r < 2 && d < i[1]) ? (c = 0, G.v = n, G.n = i[1]) : d < l && (o = r < 3 || i[0] > n || n > l) && (i[4] = r, i[5] = n, G.n = l, c = 0)); } if (o || r > 1) return a; throw y = !0, n; } return function (o, p, l) { if (f > 1) throw TypeError("Generator is already running"); for (y && 1 === p && d(p, l), c = p, u = l; (t = c < 2 ? e : u) || !y;) { i || (c ? c < 3 ? (c > 1 && (G.n = -1), d(c, u)) : G.n = u : G.v = u); try { if (f = 2, i) { if (c || (o = "next"), t = i[o]) { if (!(t = t.call(i, u))) throw TypeError("iterator result is not an object"); if (!t.done) return t; u = t.value, c < 2 && (c = 0); } else 1 === c && (t = i["return"]) && t.call(i), c < 2 && (u = TypeError("The iterator does not provide a '" + o + "' method"), c = 1); i = e; } else if ((t = (y = G.n < 0) ? u : r.call(n, G)) !== a) break; } catch (t) { i = e, c = 1, u = t; } finally { f = 1; } } return { value: t, done: y }; }; }(r, o, i), !0), u; } var a = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} t = Object.getPrototypeOf; var c = [][n] ? t(t([][n]())) : (_regeneratorDefine2(t = {}, n, function () { return this; }), t), u = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(c); function f(e) { return Object.setPrototypeOf ? Object.setPrototypeOf(e, GeneratorFunctionPrototype) : (e.__proto__ = GeneratorFunctionPrototype, _regeneratorDefine2(e, o, "GeneratorFunction")), e.prototype = Object.create(u), e; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, _regeneratorDefine2(u, "constructor", GeneratorFunctionPrototype), _regeneratorDefine2(GeneratorFunctionPrototype, "constructor", GeneratorFunction), GeneratorFunction.displayName = "GeneratorFunction", _regeneratorDefine2(GeneratorFunctionPrototype, o, "GeneratorFunction"), _regeneratorDefine2(u), _regeneratorDefine2(u, o, "Generator"), _regeneratorDefine2(u, n, function () { return this; }), _regeneratorDefine2(u, "toString", function () { return "[object Generator]"; }), (_regenerator = function _regenerator() { return { w: i, m: f }; })(); }
function _regeneratorDefine2(e, r, n, t) { var i = Object.defineProperty; try { i({}, "", {}); } catch (e) { i = 0; } _regeneratorDefine2 = function _regeneratorDefine(e, r, n, t) { function o(r, n) { _regeneratorDefine2(e, r, function (e) { return this._invoke(r, n, e); }); } r ? i ? i(e, r, { value: n, enumerable: !t, configurable: !t, writable: !t }) : e[r] = n : (o("next", 0), o("throw", 1), o("return", 2)); }, _regeneratorDefine2(e, r, n, t); }
function asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }
function _asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { asyncGeneratorStep(a, r, o, _next, _throw, "next", n); } function _throw(n) { asyncGeneratorStep(a, r, o, _next, _throw, "throw", n); } _next(void 0); }); }; }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
/*global woo_variation_gallery_admin */
var WooVariationGalleryAdmin = function ($) {
  var WooVariationGalleryAdmin = /*#__PURE__*/function () {
    function WooVariationGalleryAdmin() {
      _classCallCheck(this, WooVariationGalleryAdmin);
    }
    return _createClass(WooVariationGalleryAdmin, null, [{
      key: "GWPAdmin",
      value: function GWPAdmin() {
        if ($().gwp_deactivate_popup) {
          $().gwp_deactivate_popup('woo-variation-gallery');
        }
      }
    }, {
      key: "StartMigration",
      value: function StartMigration() {
        var intervalId;
        var $progress = $('#migration-progress-text');
        var $progressSpinner = $('#progress-spinner');
        var doneText = $progress.data('done-text');
        var checkMigrationStatus = /*#__PURE__*/function () {
          var _ref = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee() {
            var progress;
            return _regenerator().w(function (_context) {
              while (1) switch (_context.n) {
                case 0:
                  _context.n = 1;
                  return wp.apiFetch({
                    path: '/woo-variation-gallery/v1/migration-progress'
                  });
                case 1:
                  progress = _context.v;
                  if (Number(progress) >= 100) {
                    clearInterval(intervalId);
                    $progressSpinner.removeClass('is-active').css('display', 'none');
                    $progress.data('is-running', 0);
                  }
                  $progress.text("".concat(progress).concat(doneText));
                case 2:
                  return _context.a(2);
              }
            }, _callee);
          }));
          return function checkMigrationStatus() {
            return _ref.apply(this, arguments);
          };
        }();
        $('.woo-variation-gallery-migration-start').on('click', /*#__PURE__*/function () {
          var _ref2 = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee2(event) {
            var confirmText, startText, actionId, agreeToMigrate;
            return _regenerator().w(function (_context2) {
              while (1) switch (_context2.n) {
                case 0:
                  event.preventDefault();
                  confirmText = $(this).data('confirm-message');
                  startText = $(this).data('started');
                  actionId = $(this).data('action');
                  agreeToMigrate = confirm(confirmText);
                  if (agreeToMigrate) {
                    _context2.n = 1;
                    break;
                  }
                  return _context2.a(2);
                case 1:
                  $(this).prop('disabled', true);
                  $progress.text(startText);
                  $progressSpinner.addClass('is-active').css('display', 'inline-block');
                  $progress.data('is-running', 1);
                  _context2.n = 2;
                  return wp.apiFetch({
                    path: "/wc/v3/system_status/tools/".concat(actionId),
                    method: 'PUT'
                  });
                case 2:
                  intervalId = setInterval(checkMigrationStatus, 2000);
                case 3:
                  return _context2.a(2);
              }
            }, _callee2, this);
          }));
          return function (_x) {
            return _ref2.apply(this, arguments);
          };
        }());
        if (Number($progress.data('is-running')) > 0) {
          intervalId = setInterval(checkMigrationStatus, 2000);
        }
      }
    }, {
      key: "HandleDiv",
      value: function HandleDiv() {
        // Meta-Boxes - Open/close

        $(document.body).on('click', '.woo-variation-gallery-wrapper .handle-div', function () {
          $(this).closest('.woo-variation-gallery-postbox').toggleClass('closed');
          var ariaExpandedValue = !$(this).closest('.woo-variation-gallery-postbox').hasClass('closed');
          $(this).attr('aria-expanded', ariaExpandedValue);
        });
      }
    }, {
      key: "ImageUploader",
      value: function ImageUploader() {
        $(document).off('click', '.add-woo-variation-gallery-image');
        $(document).off('click', '.remove-woo-variation-gallery-image');
        $(document).on('click', '.add-woo-variation-gallery-image', this.AddImage);
        $(document).on('click', '.remove-woo-variation-gallery-image', this.RemoveImage);
        $('.woocommerce_variation').each(function () {
          var optionsWrapper = $(this).find('.options:first');
          var galleryWrapper = $(this).find('.woo-variation-gallery-wrapper');
          galleryWrapper.insertBefore(optionsWrapper);
        });
        $(document).trigger('woo_variation_gallery_admin_image_uploader_attached', this);
      }
    }, {
      key: "AddImage",
      value: function AddImage(event) {
        var _this = this;
        event.preventDefault();
        event.stopPropagation();
        var frame;
        var product_variation_id = $(this).data('product_variation_id');
        var loop = $(this).data('product_variation_loop');
        if (typeof wp !== 'undefined' && wp.media && wp.media.editor) {
          // If the media frame already exists, reopen it.
          if (frame) {
            frame.open();
            return;
          }

          // Create the media frame.
          frame = wp.media({
            title: woo_variation_gallery_admin.choose_image,
            button: {
              text: woo_variation_gallery_admin.add_image
            },
            /*states : [
                new wp.media.controller.Library({
                    title      : woo_variation_gallery_admin.choose_image,
                    filterable : 'all',
                    multiple   : 'add'
                })
            ],*/
            library: {
              type: ['image'] // [ 'video', 'image' ]
            }
            // multiple : true
            // multiple : 'add'
          });

          // When an image is selected, run a callback.
          frame.on('select', function () {
            var images = frame.state().get('selection').toJSON();
            var html = images.map(function (image) {
              if (image.type === 'image') {
                var _thumbnail$url;
                var id = image.id,
                  _image$sizes = image.sizes,
                  _image$sizes2 = _image$sizes === void 0 ? {} : _image$sizes,
                  thumbnail = _image$sizes2.thumbnail,
                  url = image.url;
                var imageUrl = (_thumbnail$url = thumbnail === null || thumbnail === void 0 ? void 0 : thumbnail.url) !== null && _thumbnail$url !== void 0 ? _thumbnail$url : url;
                var template = wp.template('woo-variation-gallery-image');
                return template({
                  id: id,
                  url: imageUrl,
                  product_variation_id: product_variation_id,
                  loop: loop
                });
              }
            }).join('');
            $(_this).parent().prev().find('.woo-variation-gallery-images').append(html);

            // Variation Changed
            WooVariationGalleryAdmin.Sortable();
            WooVariationGalleryAdmin.VariationChanged(_this);
            _.delay(function () {
              WooVariationGalleryAdmin.ProNotice(_this);
            }, 5);
          });

          // Finally, open the modal.
          frame.open();
        }
      }
    }, {
      key: "VariationChanged",
      value: function VariationChanged($el) {
        $($el).closest('.woocommerce_variation').addClass('variation-needs-update');
        $('button.cancel-variation-changes, button.save-variation-changes').removeAttr('disabled');
        $('#variable_product_options').trigger('woocommerce_variations_input_changed');

        // Dokan Support
        $($el).closest('.dokan-product-variation-itmes').addClass('variation-needs-update');
        $('.dokan-product-variation-wrapper').trigger('dokan_variations_input_changed');
        $(document).trigger('woo_variation_gallery_admin_variation_changed', this);
      }
    }, {
      key: "ProNotice",
      value: function ProNotice($el) {
        var total = $($el).closest('.woo-variation-gallery-wrapper').find('.woo-variation-gallery-images > li').length;
        $($el).closest('.woo-variation-gallery-wrapper').find('.woo-variation-gallery-images > li').each(function (i, el) {
          if (i >= 2) {
            $(el).remove();
            $($el).closest('.woo-variation-gallery-wrapper').find('.woo-variation-gallery-pro-button').show();
          } else {
            $($el).closest('.woo-variation-gallery-wrapper').find('.woo-variation-gallery-pro-button').hide();
          }
        });
      }
    }, {
      key: "RemoveImage",
      value: function RemoveImage(event) {
        var _this2 = this;
        event.preventDefault();
        event.stopPropagation();

        // Variation Changed
        WooVariationGalleryAdmin.VariationChanged(this);
        _.delay(function () {
          WooVariationGalleryAdmin.ProNotice(_this2);
          $(_this2).parent().remove();
        }, 1);
      }
    }, {
      key: "Sortable",
      value: function Sortable() {
        $('.woo-variation-gallery-images').sortable({
          items: 'li.image',
          cursor: 'move',
          scrollSensitivity: 40,
          forcePlaceholderSize: true,
          forceHelperSize: false,
          helper: 'clone',
          opacity: 0.65,
          placeholder: 'woo-variation-gallery-sortable-placeholder',
          start: function start(event, ui) {
            ui.item.css('background-color', '#F6F6F6');
          },
          stop: function stop(event, ui) {
            ui.item.removeAttr('style');
          },
          update: function update() {
            // Variation Changed
            WooVariationGalleryAdmin.VariationChanged(this);
          }
        });
      }
    }]);
  }();
  return WooVariationGalleryAdmin;
}(jQuery);


/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/define property getters */
/******/ 	!function() {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = function(exports, definition) {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	!function() {
/******/ 		__webpack_require__.o = function(obj, prop) { return Object.prototype.hasOwnProperty.call(obj, prop); }
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	!function() {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = function(exports) {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	}();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry needs to be wrapped in an IIFE because it needs to be isolated against other modules in the chunk.
!function() {
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _interopRequireWildcard(e, t) { if ("function" == typeof WeakMap) var r = new WeakMap(), n = new WeakMap(); return (_interopRequireWildcard = function _interopRequireWildcard(e, t) { if (!t && e && e.__esModule) return e; var o, i, f = { __proto__: null, "default": e }; if (null === e || "object" != _typeof(e) && "function" != typeof e) return f; if (o = t ? n : r) { if (o.has(e)) return o.get(e); o.set(e, f); } for (var _t in e) "default" !== _t && {}.hasOwnProperty.call(e, _t) && ((i = (o = Object.defineProperty) && Object.getOwnPropertyDescriptor(e, _t)) && (i.get || i.set) ? o(f, _t, i) : f[_t] = e[_t]); return f; })(e, t); }
jQuery(function ($) {
  Promise.resolve().then(function () {
    return _interopRequireWildcard(__webpack_require__("./src/js/WooVariationGalleryAdmin.js"));
  }).then(function (_ref) {
    var WooVariationGalleryAdmin = _ref.WooVariationGalleryAdmin;
    // WooVariationGalleryAdmin.ImageUploader();
    // WooVariationGalleryAdmin.Sortable();

    // WooVariationGalleryAdmin.GWPAdmin();
    WooVariationGalleryAdmin.HandleDiv();
    WooVariationGalleryAdmin.ImageUploader();
    WooVariationGalleryAdmin.StartMigration();
    $('#woocommerce-product-data').on('woocommerce_variations_loaded', function () {
      WooVariationGalleryAdmin.ImageUploader();
      WooVariationGalleryAdmin.Sortable();
    });
    $('#variable_product_options').on('woocommerce_variations_added', function () {
      WooVariationGalleryAdmin.ImageUploader();
      WooVariationGalleryAdmin.Sortable();
      // WooVariationGalleryAdmin.HandleDiv();
    });

    // Dokan Pro Support
    $('.dokan-product-variation-wrapper').on('dokan_variations_loaded dokan_variations_added', function () {
      WooVariationGalleryAdmin.ImageUploader();
      WooVariationGalleryAdmin.Sortable();
      //WooVariationGalleryAdmin.HandleDiv();
    });
    $(document).trigger('woo_variation_gallery_admin_loaded');
  });
}); // end of jquery main wrapper
}();
/******/ })()
;