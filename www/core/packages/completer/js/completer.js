/*!
 * Completer v0.1.3
 * https://github.com/fengyuanchen/completer
 *
 * Copyright (c) 2014-2016 Fengyuan Chen
 * Released under the MIT license
 *
 * Date: 2016-06-13T12:43:37.946Z
 */

function isDefined(prom)
{
    if ((prom !== undefined) && (typeof prom !== typeof undefined)) {
        return true;
    }
    return false;
}

function isObject(prom)
{
    if (isDefined(prom)) {
        if (typeof prom === 'object') {
            return true;
        }
    }
    return false;
}

var delay = (function () {
    var timer = 0;
    return function (callback, ms) {
        clearTimeout(timer);
        timer = setTimeout(callback, ms);
    };
})();

(function (factory) {

    if (typeof define === 'function' && define.amd) {
        // AMD. Register as anonymous module.
        define(['jquery'], factory);
    } else if (typeof exports === 'object') {
        // Node / CommonJS
        factory(require('jquery'));
    } else {
        // Browser globals.
        factory(jQuery);
    }

})(function ($) {

    'use strict';

    var $window = $(window);
    var $document = $(document);
    var NAMESPACE = 'completer';
    var EVENT_RESIZE = 'resize';
    var EVENT_MOUSE_DOWN = 'mousedown';

    function Completer(element, options) {
        this.$element = $(element);
        this.options = $.extend({}, Completer.DEFAULTS, $.isPlainObject(options) && options);
        this.init();
    }

    function espace(s) {
        return s.replace(/([\.\$\^\{\[\(\|\)\*\+\?\\])/g, '\\$1');
    }

    function toRegexp(s) {
        if (typeof s === 'string' && s !== '') {
            s = espace(s);

            return new RegExp(s + '+[^' + s + ']*$', 'i');
        }

        return null;
    }

    function toArray(s) {
        if (typeof s === 'string') {
            s = s.replace(/[\{\}\[\]"']+/g, '').split(/\s*,+\s*/);
        }

        s = $.map(s, function (n) {
            return typeof n !== 'string' ? n.toString() : n;
        });

        return s;
    }

    function ValidURL(url) {
        return true;///^(https?|s?ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test(url);
    }

    Completer.prototype = {
        constructor: Completer,
        init: function () {

            var options = this.options,
                    data = options.source;

            if (data.length > 0) {

                this.minlen = options.minlen;
                this.preload = options.preload;
                this.output = options.output;

                this.regexp = toRegexp(options.separator);
                this.$completer = $(options.template);

                this.$completer.hide().appendTo('body');
                this.place();

                if(options.disableLinks == "true" || options.disableLinks == "false"){
                    this.disableLinks = options.disableLinks == "true";
                } else {
                    this.disableLinks = options.disableLinks;
                }

                if (ValidURL(data)) {

                    this.URL = data;

                    if (options.preload) {
                        this.loadAjax("", function (results) {});
                    } else {
                        this.data = [];
                    }

                } else {
                    this.URL = "";
                    this.data = toArray(options.source);
                }

                this.$element.attr('autocomplete', 'off').on({
                    focus: $.proxy(this.enable, this),
                    blur: $.proxy(this.disable, this)
                });

                /*
                 $(document).on("focus", "", function(){
                 $.proxy(this.enable, this);

                 });

                 $(document).on("blur", "",function(){
                 $.proxy(this.disable, this);
                 });
                 */

                if (this.$element.is(':focus')) {
                    this.enable();
                }

            }

        },
        enable: function () {

            if (!this.active) {
                this.active = true;
                this.$element.on({
                    keydown: $.proxy(this.keydown, this),
                    keyup: $.proxy(this.keyup, this)
                });
                this.$completer.on({
                    mousedown: $.proxy(this.mousedown, this),
                    mouseover: $.proxy(this.mouseover, this)
                });
            }

        },
        disable: function () {

            if (this.active) {
                this.active = false;
                this.$element.off({
                    keydown: this.keydown,
                    keyup: this.keyup
                });
                this.$completer.off({
                    mousedown: this.mousedown,
                    mouseover: this.mouseover
                });
            }

        },
        loadAjax: function (term, callback) {

            this.$completer.html('<div class="progress" style="position: absolute; left: 0px; top: 0px; width: 100%; height: 100%; background: rgba(0,0,0,0.6)"><div style="display: inline-block; background-color: #FFFFFF; position: absolute; left: 50%; top: 50%; transform: translate(-50%,-50%);padding: 7px;"><img src="' + basePath + '/core/packages/completer/img/validation.gif" title="loading" alt="loading"/></div></div>').show();

            var that = this;
            var extendetData = this.$element.attr("data-to-send");

            $.get(this.URL, {term: term, data: extendetData}, function (data) {

                setTimeout(function () {

                    that.$completer.hide();

                    that.data = $.parseJSON(data.autoComplete);
                    callback(true);

                }, 400);

            });

        },
        _attach: function (val, check) {

            var options = this.options;
            var separator = options.separator;
            var regexp = this.regexp;
            var part = regexp ? val.match(regexp) : null;
            var matched = [];
            var all = [];
            var that = this;
            var reg;
            var item;

            if (check) {

                if (part) {
                    part = part[0];
                    val = val.replace(regexp, '');
                    reg = new RegExp('^' + espace(part), 'i');
                }

                $.each(this.data, function (i, n) {

                    if (Object.prototype.toString.call(n) === '[object Array]') {
                        n = n[0];
                    }

                    n = separator + n;
                    item = that.template(val + n, i);

                    if (reg && reg.test(n)) {
                        matched.push(item);
                    } else {
                        all.push(item);
                    }

                });

            } else {

                $.each(this.data, function (i, n) {
                    if (Object.prototype.toString.call(n) === '[object Array]') {
                        n = n[0];
                    }
                    n = separator + n;
                    item = that.template(val + n, i);
                    all.push(item);
                });

            }

            matched = matched.length ? matched.sort() : all;

            if (options.position === 'top') {
                matched = matched.reverse();
            }

            this.fill(matched.join(''));

        },
        attach: function (val) {

            var that = this;

            if (this.minlen <= val.length) {

                if (this.URL != '') {

                    if (!this.preload) {

                        this.loadAjax(val, function () {

                            that._attach(val, false);

                        });

                    } else {

                        that._attach(val, false);
                    }

                } else {

                    that._attach(val, true);

                }

            } else {
                this.fill("");
            }

        },
        _suggest: function (val, check) {

            var reg = new RegExp(espace(val), 'i');
            var that = this;
            var matched = [];

            if (check) {

                $.each(this.data, function (i, n) {
                    if (Object.prototype.toString.call(n) === '[object Array]') {
                        n = n[0];
                    }
                    if (reg.test(n)) {
                        matched.push(n);
                    }

                });

                matched.sort(function (a, b) {
                    return a.indexOf(val) - b.indexOf(val);
                });

                $.each(matched, function (i, n) {
                    matched[i] = that.template(n, i);
                });

            } else {

                $.each(this.data, function (i, n) {
                    if (Object.prototype.toString.call(n) === '[object Array]') {
                        n = n[0];
                    }
                    matched.push(that.template(n, i));
                });

            }

            this.fill(matched.join(''));

        },
        suggest: function (val) {

            var that = this;

            if (this.minlen <= val.length) {

                if (this.URL != '') {

                    if (!this.preload) {

                        this.loadAjax(val, function () {
                            that._suggest(val, false);
                        });

                    } else {
                        that._suggest(val, false);
                    }

                } else {

                    that._suggest(val, true);

                }
            } else {
                this.fill("");
            }

        },
        template: function (text, id) {

            var tag = this.options.itemTag;

            return ('<' + tag + ' data-id="' + id + '">' + text + '</' + tag + '>');

        },
        fill: function (html) {

            var filter;
            var that = this;

            this.$completer.empty();

            if (html) {

                if (isObject(html)) {

                    var output = [];

                    $.each(html, function (index, value) {
                        output.push(that.template(value));
                    });

                    this.$completer.html(output);


                } else {
                    this.$completer.html(html);
                }

                filter = this.options.position === 'top' ? ':last' : ':first';
                this.$completer.children(filter).addClass(this.options.selectedClass);
                this.show();

            } else {
                this.hide();
            }

        },
        complete: function () {

            var options = this.options;
            var val = options.filter(this.$element.val()).toString();

            if (val === '') {
                this.hide();
                return;
            }

            if (options.suggest) {
                this.suggest(val);
            } else {
                this.attach(val);
            }

        },
        keydown: function (e) {
            $(this.output).val('');
            var keyCode = e.keyCode || e.which || e.charCode;

            /*if (keyCode === 13) {
             e.stopPropagation();
             e.preventDefault();
             }*/
        },
        keyup: function (e) {
            var that = this;
            var keyCode = e.keyCode || e.which || e.charCode;
            if (/*keyCode === 13 ||*/keyCode === 38 || keyCode === 40) {
                that.toggle(keyCode);
            } else {
                delay(function () {
                    that.complete();
                }, 1000);
            }
        },
        mouseover: function (e) {

            var options = this.options;
            var selectedClass = options.selectedClass,
                    $target = $(e.target);

            if ($target.is(options.itemTag)) {
                $target.addClass(selectedClass).siblings().removeClass(selectedClass);
            }

        },
        mousedown: function (e) {

            e.stopPropagation();
            e.preventDefault();

            this.setValue($(e.target));


        },

        setValue: function (clicked) {

            if(!this.disableLinks) {
                window.location.href = clicked.closest("li").find("a").attr("href");
                return;
            }

            var clicked = clicked.closest("li");

            this.$element.val(clicked.text());

            var id = clicked.attr("data-id");

            if ($(this.output).length > 0) {
                $(this.output).val(clicked.attr("data-id"));
                //this.$element.attr("readonly", true);
            }

            if ($(this.output).attr("data-success")) {

                var funn = $(this.output).attr("data-success");
                window[funn](this.data[id], this.$element, $(this.output));

            }

            this.options.complete();
            this.hide();

        },
        toggle: function (keyCode) {

            var selectedClass = this.options.selectedClass;
            var $selected = this.$completer.find('.' + selectedClass);

            switch (keyCode) {

                // Down
                case 40:
                    $selected.removeClass(selectedClass);
                    $selected = $selected.next();
                    break;

                    // Up
                case 38:
                    $selected.removeClass(selectedClass);
                    $selected = $selected.prev();
                    break;

                    // Enter
                case 13:
                    this.setValue($selected);
                    break;

                    // No default
            }

            if ($selected.length === 0) {
                $selected = this.$completer.children(keyCode === 40 ? ':first' : ':last');
            }

            $selected.addClass(selectedClass);
        },
        place: function () {

            var $element = this.$element;
            var offset = $element.offset();
            var left = offset.left;
            var top = offset.top;
            var height = $element.outerHeight();
            var width = $element.outerWidth();
            var styles = {
                minWidth: width,
                zIndex: this.options.zIndex
            };

            switch (this.options.position) {
                case 'right':
                    styles.left = left + width;
                    styles.top = top;
                    break;

                case 'left':
                    styles.right = $window.innerWidth() - left;
                    styles.top = top;
                    break;

                case 'top':
                    styles.left = left;
                    styles.bottom = $window.innerHeight() - top;
                    break;

                    // case 'bottom':
                default:
                    styles.left = left;
                    styles.top = top + height;
            }

            this.$completer.css(styles);

        },
        show: function () {

            this.$completer.show();
            $window.on(EVENT_RESIZE, $.proxy(this.place, this));
            $document.on(EVENT_MOUSE_DOWN, $.proxy(this.hide, this));

        },
        hide: function () {

            this.$completer.hide();
            $window.off(EVENT_RESIZE, this.place);
            $document.off(EVENT_MOUSE_DOWN, this.hide);

        },
        destroy: function () {

            var $this = this.$element;

            this.hide();
            this.attr('readonly', true);

            $this.off({
                focus: this.enable,
                blur: this.disable
            });

            $this.removeData(NAMESPACE);

        }

    };

    Completer.DEFAULTS = {
        itemTag: 'li',
        position: 'bottom', // or 'right'
        source: [],
        selectedClass: 'completer-selected',
        separator: '',
        suggest: false,
        template: '<ul class="completer-container"></ul>',
        zIndex: 10000,
        URL: "",
        minlen: 1,
        complete: $.noop,
        preload: false,
        filter: function (val) {
            return val;
        },
        disableLinks: true

    };

    Completer.setDefaults = function (options) {
        $.extend(Completer.DEFAULTS, options);
    };

    // Save the other completer
    Completer.other = $.fn.completer;

    // Register as jQuery plugin
    $.fn.completer = function (option) {

        var args = [].slice.call(arguments, 1);
        var result;

        this.each(function () {

            var $this = $(this);
            var data = $this.data(NAMESPACE);
            var options;
            var fn;

            if (!data) {
                if (/destroy/.test(option)) {
                    return;
                }

                options = $.extend({}, $this.data(), $.isPlainObject(option) && option);
                $this.data(NAMESPACE, (data = new Completer(this, options)));
            }

            if (typeof option === 'string' && $.isFunction(fn = data[option])) {
                result = fn.apply(data, args);
            }

        });

        return typeof result !== 'undefined' ? result : this;

    };

    $.fn.completer.Constructor = Completer;
    $.fn.completer.setDefaults = Completer.setDefaults;

    // No conflict
    $.fn.completer.noConflict = function () {
        $.fn.completer = Completer.other;
        return this;
    };

    $(function () {
        $('[data-toggle="completer"]').completer();
    });

});

$(document).on("click", ".remove-autocomplete-value", function (e) {
    var input = $(this).parent().parent().find("input");
    input.attr("readonly", false).val("");
});
