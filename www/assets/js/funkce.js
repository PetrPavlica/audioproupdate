function isDefined(prom) {
    if ((prom !== undefined) && (typeof prom !== typeof undefined)) {
        return true;
    }
    return false;
}

window.isDefined = isDefined;

function isFunction(prom) {

    if (isDefined(prom)) {
        if (typeof prom === 'function') {
            return true;
        }
    }
    return false;
}

window.isFunction = isFunction;

function isObject(prom) {
    if (isDefined(prom)) {
        if (typeof prom === 'object') {
            return true;
        }
    }
    return false;
}

window.isObject = isObject;

function isBoolean(prom) {
    if (isDefined(prom)) {
        if (typeof prom === "boolean") {
            return true;
        }
    }
    return false;
}

window.isBoolean = isBoolean;

var delay = function () {
    var timer = 0;
    return function (callback, ms) {
        clearTimeout(timer);
        timer = setTimeout(callback, ms);
    };
}();

window.delay = delay;

function replaceIndex(string, pattern, repl, at) {

    var nth = 0;
    var reg = new RegExp(pattern, "g");

    string = string.replace(reg, function (match, i, original) {
        nth++;
        return (nth === at) ? repl : match;
    });

    return string;
}

window.replaceIndex = replaceIndex;

function thousandSeparator(x) {
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, " ");
}

window.thousandSeparator = thousandSeparator;

function number_format(number, decimals, decPoint, thousandsSep) {

    number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
    var n = !isFinite(+number) ? 0 : +number;
    var prec = !isFinite(+decimals) ? 0 : Math.abs(decimals);
    var sep = (typeof thousandsSep === 'undefined') ? ',' : thousandsSep;
    var dec = (typeof decPoint === 'undefined') ? '.' : decPoint;
    var s = '';
    var toFixedFix = function (n, prec) {
        var k = Math.pow(10, prec);
        return '' + (Math.round(n * k) / k)
                .toFixed(prec)
    };

    s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
    if (s[0].length > 3) {
        s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep)
    }
    if ((s[1] || '').length < prec) {
        s[1] = s[1] || '';
        s[1] += new Array(prec - s[1].length + 1).join('0')
    }

    return s.join(dec)
}

window.number_format = number_format;

function uniqid() {

    function s4() {
        return Math.floor((1 + Math.random()) * 0x10000)
            .toString(16)
            .substring(1);
    }

    return s4() + s4() + '-' + s4() + '-' + s4() + '-' + s4() + '-' + s4() + s4() + s4();
}

window.uniqid = uniqid;

var elementFeatures = {
    isFlexBox: function (element) {

        var display = element.css("display");

        if (display == "flex" || display == "-webkit-flex" || display == "-ms-flexbox" || display == "-moz-box" || display == "-webkit-box") {
            return true;
        }

        return false;
    }

};

/* change default options to user defined options */

function parseOptions(options_def, options_new) {

    $.each(options_new, function (property, value) {

        $.each(options_def, function (prop, val) {

            if (prop == property) {

                if (isObject(val) && isObject(value)) {
                    parseOptions(val, value);
                } else {

                    if (isObject(val) && !isObject(value)) {
                        console.log("Wrong option value, it must be object: " + prop + "");
                    } else {

                        if(value == "true" || value == "false"){
                            options_def[prop] = (value == 'true');
                        } else {

                            if (value != "" && isDefined(value)) {
                                options_def[prop] = value;
                            }

                        }

                    }

                }
            }
        });
    });

}

window.parseOptions = parseOptions;


(function ($) {

    $.fn.hasAttr = function (name) {

        var attr = this.attr(name);

        if (typeof attr !== typeof undefined && attr !== false) {
            return true;
        }

        return false;
    };

    $.fn.inlineStyle = function (prop) {
        var styles = this.attr("style"),
            value;
        styles && styles.split(";").forEach(function (e) {
            var style = e.split(":");
            if ($.trim(style[0]) === prop) {
                value = style[1];
            }
        });
        return value;
    };

    $.fn.center = function (parent) {

        if(isObject(parent)){

            $(this).css("position", "absolute");
            $(this).css("top", (getContainerHeight(parent)/2 - getContainerHeight($(this))/2)  + "px");
            $(this).css("left", (parent.outerWidth()/2 - $(this).outerWidth()/2) + "px");

        } else {

            var top = $(window).height()/2 - getContainerHeight($(this))/2;
            var left = $(window).width()/2 - $(this).outerWidth()/2;

            $(this).css("position", "fixed");
            $(this).css("top", top  + "px");
            $(this).css("left", left + "px");

            if((top + getContainerHeight($(this))) > getContainerHeight()){
                $(this).css("position", "absolute");
            }

        }

        return this;
    };

    $.fn.drags = function(opt,disabled) {

        opt = $.extend({handle:"",cursor:"move"}, opt);

        if(opt.handle === "") {
            var $el = this;
        } else {
            var $el = this.find(opt.handle);
        }

        $el.css('cursor', opt.cursor);

        $.each(disabled, function( key, value ) {
            value.css({'cursor':"auto"});
        });

        return $el.on("mousedown", function(e) {

            var disable = false;

            var target = $(e.target);

            $.each(disabled, function( key, value ) {

                if(target.closest(value).length){
                    disable = true;
                }

            });

            if(!disable) {

                if (opt.handle === "") {
                    var $drag = $(this).addClass('draggable');
                } else {
                    var $drag = $(this).addClass('active-handle').parent().addClass('draggable');
                }

                var drg_h = $drag.outerHeight(),
                    drg_w = $drag.outerWidth(),
                    pos_y = $drag.offset().top + drg_h - e.pageY,
                    pos_x = $drag.offset().left + drg_w - e.pageX;
                $drag.parents().on("mousemove", function (e) {
                    $('.draggable').offset({
                        top: e.pageY + pos_y - drg_h,
                        left: e.pageX + pos_x - drg_w
                    }).on("mouseup", function () {
                        $(this).removeClass('draggable');
                    });
                });
            }

        }).on("mouseup", function() {
            if(opt.handle === "") {
                $(this).removeClass('draggable');
            } else {
                $(this).removeClass('active-handle').parent().removeClass('draggable');
            }
        });

    };

    $.fn.applySly = function (img, options) {

        var $container = $(this);
        var $frame = $container.find('.frame');
        window.frr = $frame;

        var $preview = img;

        var $controls = $container.find(".controls");
        var $controlsP = $controls.find(".prev");
        var $controlsN = $controls.find(".next");

        var sly = new Sly($frame, {
            horizontal: 1,
            itemNav: 'centered',
            activateMiddle: 1,
            smart: 1,
            activateOn: 'click',
            mouseDragging: 1,
            touchDragging: 1,
            releaseSwing: 1,
            startAt: 0,
            scrollBar: $container.find('.scrollbar'),
            scrollBy: 1,
            pagesBar: $container.find('.pages'),
            activatePageOn: 'click',
            speed: 200,
            moveBy: 0,
            elasticBounds: 1,
            dragHandle: 1,
            dynamicHandle: 1,
            clickBar: 1,
            keyboardNavBy: 'items',
            cycleBy: (isDefined(options) ? (options.cycleBy != "" ? options.cycleBy : null) : null), // Enable automatic cycling by 'items' or 'pages'.
            cycleInterval: 5000, // Delay between cycles in milliseconds.
            startPaused: false, // Whether to start in paused sate.

            // Buttons
            prevPage: $controlsP,
            nextPage: $controlsN

        }).init();

        $container.find(".frame").find("img").batchImageLoad({
            loadingCompleteCallback:  function() {
                sly.reload();
            }
        });

        $(window).resize(function () {
            sly.reload();
        });

        return sly;
    };

    $.fn.responsiveTable = function (e) {

        var table = $(this);

        function processTd(controlTd, td, options, action) {

            var windowWidth = $(window).width();

            switch (action) {

                case "hide-column":

                    $.each(options, function (index, value) {

                        options = parseInt(index);

                        if (options >= windowWidth) {
                            td.hide();
                        } else {
                            td.show();
                        }

                    });

                    break;
                case "new-row":

                    var resolution;
                    var target;

                    var tr = td.closest("tr");

                    $.each(options, function (index, value) {

                        resolution = parseInt(index);

                        if (!isDefined(value.insert)) {
                            value.insert = "after";
                        }

                        if (!isDefined(value.class)) {
                            value.class = "";
                        }

                        if (!isDefined(value.target)) {
                            target = tr;
                        } else {

                            var parts = value.target.split(":");

                            if (!isDefined(parts[1])) {
                                parts[1] = "next";
                            }

                            if (parts[1] == "prev") {
                                target = tr.prev(parts[0]);
                            } else {
                                target = tr.next(parts[0]);
                            }

                        }

                        var new_tr = tr.closest("table").find('tr#' + tr.data("unique") + '[data-name="' + value.rowName + '"]');
                        var new_td = new_tr.find('td[data-name="' + value.colName + '"]');

                        var content = [];

                        if (resolution >= windowWidth) {

                            if (isDefined(value.copyOnly)) {

                                td.find("" + value.copyOnly + "").each(function () {
                                    content.push($(this).clone());
                                });

                            } else {
                                content.push(td.contents());
                            }

                            if (!tr.hasClass("control")) {

                                if (!new_tr.length) {
                                    new_tr = $('<tr id="' + tr.data("unique") + '" data-name="' + value.rowName + '" class="' + ((tr.attr("class") != "") ? tr.attr("class") + " " : "") + ' new_row"></tr>');
                                    new_tr.append(new_td);

                                    var ignore = controlTd.closest("tr").data("ignore-rows");
                                    ignore += ',tr#' + tr.data("unique") + '[data-name="' + value.rowName + '"]';

                                    controlTd.closest("tr").data("ignore-rows", ignore.trim(","));

                                    if (value.insert == "before") {
                                        new_tr.insertBefore(target);
                                    } else {
                                        new_tr.insertAfter(target);
                                    }
                                }

                                var classes = value.class.split(" ");
                                var classes2 = [];

                                $.each(classes, function (i, v) {
                                    classes[i] = v + "_td";
                                    classes2[i] = v + "_div";
                                });

                                if (!isDefined(value.destination)) {

                                    if (!isDefined(value.rowspan)) {
                                        value.rowspan = 1;
                                    }

                                    if (!isDefined(value.colspan)) {
                                        value.colspan = 1;
                                    }

                                    var new_td_insert = $('<td class="' + classes.join(" ") + '" data-name="' + value.colName + '" rowspan="' + value.rowspan + '" colspan="' + value.colspan + '"></td>');
                                } else {
                                    new_td = new_tr.find('td[data-name="' + value.destination + '"]');
                                }

                                if (!isDefined(value.destination) && !new_td.length) {
                                    new_tr.append(new_td_insert);
                                    new_td = new_td_insert;
                                }

                                if (!new_td.find('div[data-name="' + value.colName + '"]').length) {

                                    new_td.append('<div class="' + classes2.join(" ") + '" style="padding: 10px 0px;" data-name="' + value.colName + '"></div>');

                                    $.each(content, function (x, v) {
                                        $(this).appendTo(new_td.find('div[data-name="' + value.colName + '"]'));
                                    });

                                }

                            }

                            if (isDefined(value.copyOnly)) {
                                td.find("" + value.copyOnly + "").hide();
                            } else {
                                td.hide();
                            }

                        } else {

                            if (!tr.hasClass("control")) {

                                if (new_tr.length) {

                                    if (isDefined(value.destination)) {
                                        new_td = new_tr.find('td[data-name="' + value.destination + '"]');
                                    }

                                    if (new_td.length) {

                                        var div = new_td.find('div[data-name="' + value.colName + '"]');

                                        if (isDefined(value.copyOnly)) {

                                            var parts = value.copyOnly.split(",");

                                            $.each(parts, function (i, v) {
                                                td.find(v).replaceWith(div.find(v)).show();
                                            });

                                            div.remove();

                                        } else {
                                            div.contents().appendTo(td);
                                            div.remove();
                                        }

                                        if (!new_td.find("div").length) {
                                            new_td.remove();
                                        }
                                    }

                                    if (!new_tr.find("td").length) {
                                        new_tr.remove();
                                    }
                                }
                            }
                            td.show();
                        }
                    });

                    break;
                case "join-columns":

                    tr = td.closest("tr");

                    $.each(options, function (index, value) {

                        var resolution = index;

                        if (!isDefined(value.target)) {
                            value.target = "";
                        }

                        var resolutionRange = index.split("-");
                        var pointers = value.target.split(",");

                        if (resolutionRange.length == 1) {
                            resolutionRange[1] = 0;
                        }

                        if ((windowWidth <= parseInt(resolutionRange[0])) && (windowWidth >= parseInt(resolutionRange[1]))) {

                            $.each(pointers, function (index, value) {

                                var toJoin = value.trim();

                                if (!td.find('div[data-name="' + toJoin + '"]').length) {

                                    var tdHide = tr.find('td[data-name="' + toJoin + '"]');
                                    var content = tdHide.contents();

                                    if (isDefined(tdHide.data("destination"))) {
                                        td.find("" + tdHide.data("destination") + "").append('<div style="padding: 10px 0px;" data-name="' + toJoin + '" data-resolution="' + resolution + '"></div>');
                                    } else {
                                        td.append('<div style="padding: 10px 0px;" data-name="' + toJoin + '" data-resolution="' + resolution + '"></div>');
                                    }

                                    content.appendTo(td.find('div[data-name="' + toJoin + '"]'));
                                    tdHide.hide();
                                }

                            });

                        } else {

                            $.each(pointers, function (index, value) {

                                var toSplit = value.trim();

                                if (td.find('div[data-name="' + toSplit + '"][data-resolution="' + resolution + '"]').length) {

                                    var tdShow = td.closest("tr").find('td[data-name="' + toSplit + '"]');
                                    var div = td.closest("tr").find('div[data-name="' + toSplit + '"]');

                                    var content = div.contents();
                                    content.appendTo(tdShow);

                                    div.remove();
                                    tdShow.show();

                                }

                            });
                        }

                    });

                    break;

                case "change":

                    $.each(options,function(index, value){

                        var resolution = parseInt(index);

                        console.log(resolution);

                        if (resolution >= windowWidth) {

                            $.each(value, function(attr, val){

                                if(!isDefined(td.data("old-"+attr+""))) {
                                    if (td.hasAttr(attr)) {
                                        td.data("old-" + attr + "", td.attr(attr));
                                    } else {
                                        td.data("old-" + attr + "", "");
                                    }
                                }

                                td.attr(attr,val);

                            });

                        } else {

                            $.each(value, function(attr, val){
                                if(isDefined(td.data("old-"+attr+""))) {
                                    td.attr(attr, td.data("old-" + attr + ""));
                                    td.data("old-" + attr + "", undefined);
                                }
                            });

                        }

                    });

                    break;

                default:
                    break;

            }

        }

        function processRow(controlColumn, action, options) {


            var controlRow = controlColumn.closest("tr");

            var ignore;

            if (isDefined(controlRow.data("ignore-rows"))) {
                ignore = controlRow.data("ignore-rows");
            } else {
                ignore = "";
            }

            controlColumn.closest("table").find("tr").not("" + ignore + "").each(function () {

                if (!isDefined($(this).data("unique"))) {
                    var unique = uniqid();
                    $(this).data("unique", unique);
                    $(this).attr("id", unique);
                }

                processTd(controlColumn, $(this).find("td").eq(controlColumn.index()), options, action);

            });

        }

        function customizeTable(table) {

            var action, options = {}, controlColumn;

            table.find("tr.control td[data-hide]").each(function () {

                controlColumn = $(this);

                if (isDefined(controlColumn.data("hide"))) {
                    action = "hide-column";
                    options[controlColumn.data("hide")] = {};
                } else {
                    return;
                }

                processRow(controlColumn, action, options);
            });

            table.find("tr.control td[data-join]").each(function () {

                controlColumn = $(this);

                if (isObject(controlColumn.data("join"))) {
                    action = "join-columns";
                    options = controlColumn.data("join");
                } else {
                    return;
                }

                processRow(controlColumn, action, options);
            });

            table.find("tr.control td[data-new-row]").each(function () {

                controlColumn = $(this);

                if (isObject(controlColumn.data("new-row"))) {
                    action = "new-row";
                    options = controlColumn.data("new-row");
                } else {
                    return;
                }

                processRow(controlColumn, action, options);
            });

            table.find("tr.control td[data-change]").each(function () {

                controlColumn = $(this);

                if(isObject(controlColumn.data("change"))){
                    action = "change";
                    options = controlColumn.data("change");
                }  else {
                    return;
                }

                processRow(controlColumn,action,options);

            });

        }

        $(window).resize(function () {
            setTimeout(customizeTable(table), 500);
        });

        customizeTable(table);

    };

    var rangeSliderOptions = [];
    var rangeSliderLastIndex = 1;

    $.fn.rangeSlider = function (def_options) {

        if (!isDefined(def_options)) {
            def_options = {};
        }

        if (!isDefined(def_options.mena)) {
            def_options.mena = "Kč";
        }

        if (!isDefined(def_options.range)) {
            def_options.range = [0, 30000];
        }

        if (!isDefined(def_options.values)) {
            def_options.values = [0, 30000];
        }

        if (!isDefined(def_options.step)) {
            def_options.step = 500;
        }

        $(this).each(function () {

            var container = $(this);

            if (!isDefined(container.data("build"))) {

                var uniq = uniqid();

                rangeSliderOptions[rangeSliderLastIndex] = {};

                if (isDefined(container.data("mena"))) {
                    rangeSliderOptions[rangeSliderLastIndex].mena = container.data("mena");
                } else {
                    rangeSliderOptions[rangeSliderLastIndex] = def_options.mena;
                }

                if (isDefined(container.data("range"))) {
                    rangeSliderOptions[rangeSliderLastIndex].range = container.data("range");
                } else {
                    rangeSliderOptions[rangeSliderLastIndex] = def_options.range;
                }

                if (isDefined(container.data("values"))) {
                    rangeSliderOptions[rangeSliderLastIndex].values = container.data("values");
                } else {
                    rangeSliderOptions[rangeSliderLastIndex] = def_options.values;
                }

                if (isDefined(container.data("step"))) {
                    rangeSliderOptions[rangeSliderLastIndex].step = parseInt(container.data("step"));
                } else {
                    rangeSliderOptions[rangeSliderLastIndex] = def_options.step;
                }

                rangeSliderOptions[rangeSliderLastIndex].uniq = uniq;

                var id = container.hasAttr("id") ? container.attr("id") : uniq;

                container.find("input[type=hidden]").first().attr("id", "" + id + "_min");
                container.find("input[type=hidden]").last().attr("id", "" + id + "_max");

                container.append(
                    '<div class="wrap flexElem valignCenter"><label id="' + id + '_min">' + rangeSliderOptions[rangeSliderLastIndex].values[0] + '&nbsp;' + rangeSliderOptions[rangeSliderLastIndex].mena + '</label> ' +
                    '<div style="width: 91%;margin-left: 8px;" id="' + id + '_cena_range"></div> ' +
                    '<label id="' + id + '_max">' + rangeSliderOptions[rangeSliderLastIndex].values[1] + '&nbsp;' + rangeSliderOptions[rangeSliderLastIndex].mena + '</label></div>');

                if (container.find(".wrap").outerWidth(true) < 300) {
                    container.find(".wrap").addClass("flexWrap");
                    container.find(".wrap").addClass("narrow")
                }

                container.find("#" + id + "_cena_range").slider({
                    range: true,
                    min: rangeSliderOptions[rangeSliderLastIndex].range[0],
                    step: rangeSliderOptions[rangeSliderLastIndex].step,
                    max: rangeSliderOptions[rangeSliderLastIndex].range[1],
                    values: [rangeSliderOptions[rangeSliderLastIndex].values[0], rangeSliderOptions[rangeSliderLastIndex].values[1]],
                    slide: function (event, ui) {

                        var index = $(event.target).closest(".ui-slider").data("range-slider-index");

                        container.find("label#" + id + "_min").html(thousandSeparator(ui.values[0]) + "&nbsp;" + rangeSliderOptions[index].mena);
                        container.find("label#" + id + "_max").html(thousandSeparator(ui.values[1]) + "&nbsp;" + rangeSliderOptions[index].mena);
                        var hiddenMin = container.find("input#" + id + "_min");
                        var hiddenMax = container.find("input#" + id + "_max");
                        hiddenMin.val(ui.values[0]);
                        hiddenMax.val(ui.values[1]);
                        hiddenMin.change();
                        hiddenMax.change();

                    },
                    change: function (event, ui) {

                        var index = $(event.target).closest(".ui-slider").data("range-slider-index");

                        if (isFunction(rangeSliderOptions[index].change)) {
                            rangeSliderOptions[index].change(event, ui);
                        }

                    }
                }).data("range-slider-index", rangeSliderLastIndex);

                container.data("build", true);

                container.find("label#" + id + "_min").html(thousandSeparator(rangeSliderOptions[rangeSliderLastIndex].values[0]) + "&nbsp;" + rangeSliderOptions[rangeSliderLastIndex].mena);
                container.find("label#" + id + "_max").html(thousandSeparator(rangeSliderOptions[rangeSliderLastIndex].values[1]) + "&nbsp;" + rangeSliderOptions[rangeSliderLastIndex].mena);

                rangeSliderLastIndex++;
            }

        });

    };

    $.fn.batchImageLoad = function(options) {
        var images = $(this);
        var originalTotalImagesCount = images.length;
        var totalImagesCount = originalTotalImagesCount;
        var elementsLoaded = 0;

        // Init
        $.fn.batchImageLoad.defaults = {
            loadingCompleteCallback: null,
            imageLoadedCallback: null
        };

        var opts = $.extend({}, $.fn.batchImageLoad.defaults, options);

        // Start
        images.each(function() {
            // The image has already been loaded (cached)
            if ($(this)[0].complete) {
                totalImagesCount--;
                if (opts.imageLoadedCallback) opts.imageLoadedCallback(elementsLoaded, originalTotalImagesCount);
                // The image is loading, so attach the listener
            } else {

                $(this).on('load', function() {
                    elementsLoaded++;

                    if (opts.imageLoadedCallback) opts.imageLoadedCallback(elementsLoaded, originalTotalImagesCount);

                    // An image has been loaded
                    if (elementsLoaded >= totalImagesCount)
                        if (opts.loadingCompleteCallback) opts.loadingCompleteCallback();
                });

                $(this).on('error', function() {
                    elementsLoaded++;

                    if (opts.imageLoadedCallback) opts.imageLoadedCallback(elementsLoaded, originalTotalImagesCount);

                    // The image has errored
                    if (elementsLoaded >= totalImagesCount)
                        if (opts.loadingCompleteCallback) opts.loadingCompleteCallback();
                });
            }
        });

        // There are no unloaded images
        if (totalImagesCount <= 0)
            if (opts.loadingCompleteCallback) opts.loadingCompleteCallback();
    };

}(jQuery));

function getDocHeight() {
    var D = document;
    return Math.max(
        D.body.scrollHeight, D.documentElement.scrollHeight,
        D.body.offsetHeight, D.documentElement.offsetHeight,
        D.body.clientHeight, D.documentElement.clientHeight
    );
}

window.getDocHeight = getDocHeight;

function getContainerHeight(selector) {

    var total = 0;

    $(selector).children().each(function () {
        total += $(this).outerHeight(true);
    });

    total += (parseInt(selector.css("padding-top")) + parseInt(selector.css("padding-bottom")));

    return total + 80;
}

window.getContainerHeight = getContainerHeight;

// odpočet času akce

var countDown = function (target) {

    var seconds = target.find(".seconds");
    var minutes = target.find(".minutes");
    var hours = target.find(".hours");
    var days = target.find(".days");

    this.refresh = function () {

        var sec = parseInt(seconds.text());
        var min = parseInt(minutes.text());
        var hour = parseInt(hours.text());
        var day = parseInt(days.text());

        if ((sec - 1) < 0) {

            if ((min - 1) < 0) {

                if ((hour - 1) < 0) {

                    if ((day - 1) < 0) {
                        return;
                        //location.reload();
                    } else {
                        day--;
                    }

                    hour = 23;

                } else {
                    hour--;
                }

                min = 59;
            } else {
                min--;
            }

            sec = 59;
        } else {
            sec--;
        }

        seconds.text(((sec < 10) ? "0" : "") + sec);
        minutes.text(((min < 10) ? "0" : "") + min);
        hours.text(((hour < 10) ? "0" : "") + hour);
        days.text(((day < 10) ? "0" : "") + day);

    };

    setInterval(this.refresh, 1000);

};

window.countDown = countDown;

var fixedBarInstances = [];

var fixedBar = function (options) {

    var id;
    var defaultOptions;
    var object;

    var items = [];
    var groups = [];

    var setDefaults = function () {

        id = uniqid();

        defaultOptions = {
            resRange: '0-' + $(window).width() + '',
            showY: 0,
            minTop: 0,
            maxTop: getDocHeight(),
            position: "top",
            width: "100%",
            height: "auto",
            classes: {
                container: "",
                body: "",
                group: ""
            }
        };

    };

    var setOptions = function (options) {

        $.each(options, function (property, value) {
            $.each(defaultOptions, function (prop, val) {

                if (prop == property) {
                    if (value != "") {
                        defaultOptions[prop] = value;
                    }
                }

            });
        });

        if (isObject(defaultOptions["showY"])) {

            console.log(defaultOptions["showY"]);

            if ($(defaultOptions["showY"]).length) {
                defaultOptions["showY"] = $(defaultOptions["showY"]).offset().top;
            }
        }

    };

    var insert = function () {

        /* Todo: distribution by position: top,left,right,bottom, Add custom class, apply minTop and maxTop, width, height */

        var content = '<div id="' + id + '" style="display: none;" class="fixed_container flexElem ' + defaultOptions.classes.container + '"><div class="fixed_container_body flex100 flexElem wrap ' + defaultOptions.classes.body + '"></div></div>';
        $("body").append(content);

        object = $(document).find("#" + id + "");

    };

    this.createGroup = function () {

        var unique = uniqid();

        object.find(".fixed_container_body").append('<div id="' + unique + '" class="group flexElem alignJustify valignCenter"></div>');

        var group = {
            id: unique,
            elem: object.find("#" + unique + "")
        };

        groups.push(group);

    };

    this.addItem = function (elem, group, options) {

        if (!isObject(options)) {
            options = {};
        }

        if (!isDefined(group) && (group < 0 || group > (groups.length - 1))) {
            this.createGroup();
            group = groups.length - 1;
        }

        var defaultO = {
            resRange: '0-' + $(window).width() + '',
            showY: 0,
            selector: elem,
            wrapBy: "",
            group: group
        };

        $.each(options, function (property, value) {
            $.each(defaultO, function (prop, val) {

                if (prop == property) {
                    if (value != "") {
                        defaultO[prop] = value;
                    }
                }

            });
        });

        if (isObject(defaultO["showY"])) {
            defaultO["showY"] = $(defaultO["showY"]).offset().top();
        }

        items.push(defaultO);

    };

    this.refresh = function () {
        process();
    };

    var toInt = function (arr) {

        $.each(arr, function (index, value) {
            arr[index] = parseInt(value);
        });

        return arr;
    };

    var process = function () {

        var range = toInt(defaultOptions.resRange.split("-"));

        if (($(window).width() >= range[0] && $(window).width() <= range[1]) && $(window).scrollTop() >= defaultOptions.showY) {

            $.each(items, function (index, value) {

                var elem = $(document).find(value.selector);
                var eRange = toInt(value.resRange.split("-"));
                var clone = elem.clone();

                if (($(window).width() >= eRange[0] && $(window).width() <= eRange[1]) && $(window).scrollTop() >= value.showY) {

                    var group = groups[value.group].elem;

                    if (!object.find("#" + elem.attr("id") + "").length && !object.find("." + elem.attr("class") + "").length) {
                        group.append(clone);
                        clone.wrap('<div></div>');

                        if (value.wrapBy != '') {
                            clone.wrap(value.wrapBy);
                        }

                    } else {
                        object.find("#" + elem.attr("id")).replaceWith(clone);
                        object.find("#" + elem.attr("id")).show();
                    }

                } else {

                    if (object.find("#" + elem.attr("id") + "").length) {
                        object.find("#" + elem.attr("id")).replaceWith(clone);
                        object.find("#" + elem.attr("id")).hide();
                    }

                }

            });

            object.show();
            $("body").css({"padding-top": object.outerHeight()});

        } else {
            object.hide();
            $("body").css({"padding-top": 0});
        }

    };

    setDefaults();
    setOptions(options);
    insert();

    $(window).scroll(function () {
        process();
    });

    $(window).resize(function () {
        process();
    });

    process();

    fixedBarInstances.push(this);

};

window.fixedBarInstances = fixedBarInstances;

window.fixedBar = fixedBar;

var URL = function(url){

    var URL = ((isDefined(url))?url:window.location.href);

    var URLbefore;
    var params = {};

    var self = this;
    getParams();

    function getParams(){

        var sPageURL = URL.split("?");

        URLbefore = sPageURL[0];
        sPageURL = sPageURL[1];

        if(isDefined(sPageURL)){

            var sURLVariables = sPageURL.split('&');

            for (var i = 0; i < sURLVariables.length; i++) {

                var sParameterName = sURLVariables[i].split('=');
                params[sParameterName[0]] = sParameterName[1];

            }

        }
    }

    var clear = function(){
        params = {};
    };

    this.getParameters = function(){
        return params;
    };

    this.getBaseUrl = function(){
        return URLbefore;
    };

    this.changeUrl = function(url){

        if(isDefined(url)){
            URL = url;
            clear();
            getParams();
        }

    };

    this.parameterExists = function(sParam) {

        if(isDefined(params[sParam])){
            return true;
        }
        return false;
    };

    this.getUrlParameter = function(sParam) {

        if(self.parameterExists(sParam)) {
            return params[sParam];
        } else {
            return false;
        }

    };

    this.useThisUrl = function(title,noreload){

        if(!isBoolean(noreload)){
            noreload = false;
        }

        var full = URLbefore+"?";
        var after = "";

        for (var key in params) {
            // skip loop if the property is from prototype
            if (!params.hasOwnProperty(key)) continue;

            after += ((after != '')?"&":"")+key+"="+params[key];

        }

        full += after;


        if (noreload) {
            if (typeof (history.pushState) != "undefined") {
                var obj = {Title: title, Url: full};
                history.pushState(obj, obj.Title, full);
            } else {
                location.replace(full);
            }
        } else {
            location.replace(full);
        }

    };

    this.removeParameter = function (sParam) {

        if(self.parameterExists(sParam)){
            delete params[sParam]
        }

    };

    this.changeParameter = function(sParam, sValue){

        if(self.parameterExists(sParam)){
            params[sParam] = sValue;
        }

    };

    this.addParameter = function(sParam, sValue){

        if(!self.parameterExists(sParam)){
            params[sParam] = sValue;
        } else {
            self.changeParameter(sParam, sValue);
        }

    }


};

window.URL = URL;


/* event for article */

$(document).on("click", ".article.no_full .more", function (e) {

    e.preventDefault();

    var content = $(this).closest(".article").find(".content");

    if (!$(this).data("open")) {

        content.addClass("full");
        content.find(".gradient").hide();
        $(this).text("Méně informací");
        $(this).data("open", true);

    } else {

        content.removeClass("full");
        content.find(".gradient").show();
        $(this).text("Více informací");
        $(this).data("open", false);

    }

});


function show_defined_elements(elem) {

    var show_options = {
        target: "",
        effect: "show",
        duration: 500,
        secondClick: false,
        nthClick: elem.data("nth-click"),
        scrollTo: "",
        newText: ""
    };

    if(!isDefined(elem.data("oldText"))){
        elem.data("oldText", "");
    }

    if (isObject(elem.data("show"))) {

        $.each(elem.data("show"), function (property, value) {

            $.each(show_options, function (prop, val) {

                if (prop == property) {

                    if (value != "") {

                        if(value == "true" || value == "false"){
                            show_options[prop] = value == "true";
                        } else {
                            show_options[prop] = value;
                        }

                    }
                }

            });

        });

        show_options.duration = parseInt(show_options.duration);

        if (show_options.newText != '' && elem.data("oldText") == "") {

            elem.data("oldText", elem.text());
            elem.text(show_options.newText);

        }

        var target = show_options.target.split(",");
        var count = target.length;

        if (show_options.secondClick) {

            if (show_options.nthClick == 2) {
                if (isDefined(show_options.secondClick)) {
                    show_options.effect = show_options.secondClick;
                    show_options.nthClick = 0;
                }

                if (isDefined(elem.data("oldText")) && elem.data("oldText") != '') {
                    elem.text(elem.data("oldText"));
                    elem.data("oldText", "");
                }

            }
        }

        if (elem.is(":checkbox")) {
            if (elem.is(":checked")) {
                show_options.effect = "show";
            } else {
                show_options.effect = "hide";
            }
        }

        if (elem.hasClass("toggle")) {

            if (elem.parent().find('input[type="checkbox"]').is(":checked")) {
                show_options.effect = "show";
            } else {
                show_options.effect = "hide";
            }

        }

        $.each(target, function (index, value) {

            $(document).find(value)[show_options.effect](show_options.duration, function () {

                if (index == (count - 1)) {

                    if (isDefined(show_options.scrollTo)) {

                        target = $(show_options.scrollTo);

                        if (target.length && target.is(":visible")) {
                            $('html, body').animate({
                                scrollTop: target.offset().top
                            }, 500);
                        }

                    }

                }

            });

        });

    }

    elem.data("nth-click", show_options.nthClick);

}

function hide_defined_elements(elem) {

    var hide_options = {
        target: "",
        effect: "hide",
        duration: 500
    };

    if (isObject(elem.data("hide"))) {

        $.each(elem.data("hide"), function (property, value) {

            $.each(hide_options, function (prop, val) {

                if (prop == property) {
                    if (value != "") {
                        hide_options[prop] = value;
                    }
                }

            });

        });

        hide_options.duration = parseInt(hide_options.duration);

        var target = hide_options.target.split(",");

        if (elem.is(":checkbox")) {
            if (elem.is(":checked")) {
                hide_options.effect = "show";
            } else {
                hide_options.effect = "hide";
            }
        }

        $.each(target, function (index, value) {
            $(document).find(value)[hide_options.effect](hide_options.duration);
        });

    }

}


/* scroll to somewhere, show elements, hide elements by link or buttons */

$(document).on("click ifChanged ifClicked", "a.no-link, input[type=submit].no-submit, button, a.button, .icheck, .toggle", function (e) {

    if ($(this).hasClass("no-link") || $(this).hasClass("no-submit")) {
        e.preventDefault();
    }

    var elem = $(this);

    var nth_click = elem.data("nth-click");

    if (!isDefined(nth_click)) {
        nth_click = 1;
        elem.data("nth-click", nth_click);
    } else {
        elem.data("nth-click", nth_click + 1);
    }

    show_defined_elements(elem);
    hide_defined_elements(elem);

});

$(document).on("click", ".checkbox_container label, .radio_container label", function () {

    if($(this).closest(".checkbox_container").length){

        var input = $(this).closest(".checkbox_container").find(".icheck");

        if(!input.iCheck('update')[0].checked){
            input.iCheck("check");
        } else {
            input.iCheck("uncheck");
        }

    } else {

        var input = $(this).closest(".radio_container").find(".icheck");
        input.iCheck("check");

    }

});

/* scroll somewhere */

$(document).on("click ifChanged", ".scroll-to", function (e) {

    e.preventDefault();

    if (isDefined($(this).data("scroll-to"))) {

        var elem = $(this);
        var target = $(document).find(elem.data("scroll-to"));

        setTimeout(function () {

            if (target.is(":visible")) {
                $('html, body').animate({
                    scrollTop: target.offset().top - 60
                }, 500);
            }

        }, 200);

    }

});

/* drop-down */

function processDropDown(btn, hover) {

    var options = {
        direction: "down",
        controlWidth: $(window),
        bottom: btn,
        inSlider: false,
        renderSocials: false
    };

    if (isObject(btn.data("options"))) {

        $.each(btn.data("options"), function (property, value) {

            $.each(options, function (prop, val) {

                if (prop == property) {
                    if (value != "") {
                        options[prop] = value;
                    }
                }

            });

        });
    }

    if (!isObject(options.controlWidth)) {
        options.controlWidth = $(document).find(options.controlWidth);
    }

    if (!isObject(options.bottom)) {
        options.bottom = btn.closest(options.bottom);
    }

    var content = btn.closest(".drop_down_container").find(".drop_down");
    var container = btn.closest(".drop_down_container");

    if (container.data("opened") == true) {
        return;
    }

    if (!isObject(options.controlWidth)) {
        options.controlWidth = $(document).find(options.controlWidth);
    }

    var windowWidth = $(window).width();

    if (!hover) {
        $(document).find(".drop_down_container").not(btn.closest(".drop_down_container")).find(".drop_down").slideUp(500);
    } else {
        if (windowWidth >= 640) {
            $(document).find(".drop_down.info").not(content).slideUp(500);
        }
    }

    var contentWidth = content.outerWidth(true);
    var contentLeft = container.offset().left;

    var parentWidth = options.controlWidth.outerWidth(true);
    var parentLeft = 0;

    if (!options.controlWidth.is($(window))) {
        parentLeft = options.controlWidth.offset().left;
    }

    var clone = undefined;

    function recalculatePosition(){

        if (content.css("position") != "relative") {

            if (options.direction == "up") {

                var bottom = options.bottom.outerHeight(true);
                if (hover) {
                    bottom += 5;
                }

                content.css({"bottom": bottom});
            }

            if (windowWidth <= 640) {

                var winW = $(window).width();
                var offset = content.closest(".drop_down_container").offset().left;
                var pos;

                if (winW <= 480) {
                    contentWidth = winW - 50;
                    content.css({"margin-left": 0, "width": contentWidth, "min-width": 100});
                }

                if(!hover) {
                    if ((contentLeft + contentWidth) > (parentWidth + parentLeft)) {
                        content.css({"right": 0});
                    } else {
                        content.css({"right": "auto", "left": 0});
                    }
                }

            } else {

                var width;

                if (hover) {

                    content.css("left", btn.offset().left - contentLeft);

                    width = (parentLeft + parentWidth) - btn.offset().left - 10;

                    if (width < 250) {

                        content.css({"max-width": "none"});

                        if ((contentLeft + contentWidth) > (parentWidth + parentLeft)) {
                            content.css({"left": parentWidth - contentWidth - 10 - parentLeft});
                        } else {
                            content.css({"right": "auto", "left": 0});
                        }

                    } else {
                        content.css("max-width", width);
                    }

                } else {

                    contentWidth = content.outerWidth(true);

                    width = (parentLeft + parentWidth) - btn.offset().left - 50;
                    content.css("width", width);

                    if ((contentLeft + contentWidth) > (parentWidth + parentLeft)) {

                        if(options.inSlider){
                            content.css({"left": (btn.offset().left - options.bottom.offset().left + btn.outerWidth(true)) - contentWidth});
                        } else {
                            content.css({"left": (btn.offset().left - options.bottom.offset().left + btn.outerWidth(true)) + (btn.offset().left - container.offset().left) - contentWidth});
                        }

                    } else {
                        content.css({"right": "auto", "left": 0});
                    }


                }

            }

        } else {

            if(!isDefined(clone)) {

                content.css({"max-width": "none", "width": "100%"});

                if (elementFeatures.isFlexBox(container.parent())) {

                    var arr = container.parent().attr("class").match(/flexWrap[0-9]*/);

                    if (arr != null) {
                        container.parent().data("flex-wrap", arr[0]);
                    } else {
                        container.parent().addClass("flexWrap");
                    }

                }

                clone = content.clone();
                clone.insertAfter(container);
                clone.css({"width": "100%"});
                clone.wrap('<div class="drop_down_container"></div>');

                content = clone;

                container.data("opened", true);
            }

        }

    }

    function showContent(down, elem, callback) {

        if(!isDefined(down)){
            down = true;
        }

        if(down){
            elem.slideDown(500, function () {
                if(isFunction(callback)){
                    callback();
                }
            });
        } else {
            elem.slideUp(500, function () {

                if(isFunction(callback)){
                    callback();
                }
            });
        }

    }

    recalculatePosition();

    if (!content.is(":visible")) {
        showContent(true,content);
        btn.addClass("hover");
    } else {
        if (!hover) {
            showContent(false,content);
            btn.removeClass("hover");
        }
    }

    if (!hover) {

        $(document).click(function (e) {
            if (!content.is(e.target) && content.has(e.target).length === 0 && !$(e.target).closest(".arrow").length) {
                showContent(false,content);
                btn.removeClass("hover");
            }
        });

    }

    if (content.css("position") != "relative") {

        if (hover) {
            if (windowWidth >= 640) {
                content.find(".close").hide();
            }
        } else {
            content.find(".close").show();
        }

        if(hover) {
            btn.on("mouseleave", function (e) {
                showContent(false,content);
            });
        }

        container.find(".close").on("click touch", function (e) {
            showContent(false,content);
            btn.removeClass("hover");
        });


    } else {

        clone.find(".close").on("click touch", function (e) {

            showContent(false,content,function () {

                btn.removeClass("hover");

                if (!isDefined(container.parent().data("flex-wrap"))) {
                    container.parent().removeClass("flexWrap");
                }

                container.data("opened", false);
                clone.closest(".drop_down_container").remove();

            });

        });

    }

    recalculatePosition();
}

$(document).on("click touch", ".drop_down_container .arrow", function (e) {
    e.preventDefault();
    processDropDown($(this), false);
});

$(document).on("click mouseenter", ".drop_down_container .arrow_hover", function (e) {

    var el = $(this);
    var stop = false;

    if(isDefined(el.data("no-prevent"))){
        if(el.data("no-prevent")) {
            stop = true;
        }

    }

    if (!e.hadnled) {

        if(!stop) {
            e.preventDefault();
            e.stopPropagation();
        }

        processDropDown($(this), true);
        e.handled = true;
    }

});

/* show edit block */

$(document).on("click", ".button.show_edit, a.no-link.show_edit", function (e) {

    var btn = $(this);
    var target = $(document).find(btn.data("target"));
    var container = target.closest(".edit_block_container");

    var targetHeight, cont;

    if (isDefined(target)) {

        if (target.hasClass("form_in_form")) {

            var unique = uniqid();

            $("body").append('<div id="' + unique + '_clone" class="form_in_form edit_block_container"></div>');

            var clone = $(document).find("#" + unique + "_clone");
            var cloneContents = container.contents().clone();
            container.contents().appendTo(clone);

            container.attr("id", unique + "_orig");
            var cloneTarget = $("body").find("#" + unique + "_clone").find(".edit_block");

            clone.css({
                "position": "absolute",
                "top": container.offset().top,
                "left": container.offset().left
            }).width(container.parent().width());
            cloneTarget.css({"position": "relative"});
            cloneTarget.fadeIn(500);

            $(window).resize(function () {
                clone.css({
                    "position": "absolute",
                    "top": container.offset().top,
                    "left": container.offset().left
                }).width(container.parent().width());
            });

            targetHeight = getContainerHeight(cloneTarget);
            cont = clone.offset().top + targetHeight;

            new ResizeSensor(clone, function (el) {
                if (isDefined(el)) {
                    container.height(getContainerHeight(target));
                }
            });

        } else {
            clone = target.parent();
            cloneTarget = target;
            target.fadeIn(500);
            targetHeight = getContainerHeight(target);
            cont = target.offset().top + targetHeight;
        }

        var win = $(window).scrollTop() + $(window).height();
        $(document).find("#edit_full_background").fadeIn(500);
        container.height(targetHeight);

        if (win < cont) {

            $('html, body').animate({
                scrollTop: container.offset().top - 80
            }, 500);

        }

        $("#edit_full_background").height(getDocHeight());

        clone.find(".edit_block .bar .close").on("click", function (e) {

            cloneTarget.fadeOut(500);

            $(document).find("#edit_full_background").fadeOut(500, function () {

                if (clone.hasClass("form_in_form")) {

                    var id = target.parent().attr("id").split("_");
                    var origid = "#" + id[0] + "_orig";

                    var container_orig = $(document).find(origid);
                    cloneContents.appendTo(container_orig);

                    preparePlugins(cloneContents);

                    container_orig.height(0);
                    container_orig.find(".edit_block").hide();

                    clone.remove();

                } else {
                    btn.closest(".edit_block_container").height(0);
                }

            });

        });


    }

});

/* events and functions to tabs */

function updateTabsHandlers() {

    $(document).find("a.tabs-switcher,input[type=submit].tabs-switcher,button.tabs-switcher").each(function () {

        var object = $(this).data("switch-tab");

        var tabs = $(document).find(object.target).find(".tab");
        var active = tabs.index($(document).find(object.target).find(".tab.active"));

        if (object.direction == "left") {

            if (active > 0) {
                object.index = active - 1;
            }

        } else {

            if (active < (tabs.length - 1)) {
                object.index = active + 1;
            }

        }

        $(this).data("switch-tab", object);

    });

}

$(document).on("click", "a.tabs-switcher,input[type=submit].tabs-switcher,button.tabs-switcher", function (e) {

    if (isDefined($(this).data("switch-tab"))) {

        var object = $(this).data("switch-tab");
        var tabs = $(document).find(object.target).find(".tab");

        tabs.eq(object.index).click();

    }

});


$(document).on("click", ".tabs_container .tabs .tab", function (e) {

    var tab = $(this);

    if (isDefined(tab.data("show-content"))) {
        var index = parseInt(tab.data("show-content"));
    } else {
        var index = $(".tabs_container .tabs .tab").index(this);
    }

    var siblings = tab.siblings(".tab");
    siblings.removeClass("active");

    siblings.each(function () {

        if ($(this).index() < tab.index()) {
            $(this).addClass("completed");
        } else {
            $(this).removeClass("completed");
        }

    });

    tab.removeClass("completed");
    tab.addClass("active");

    var contents = tab.closest(".tabs_container").find(".tabs_content .tab_content");
    contents.eq(index).siblings(".tab_content").fadeOut();

    show_defined_elements(tab);
    hide_defined_elements(tab);

    contents.eq(index).fadeIn(400, function () {

        if (isDefined(tab.data("scroll-to"))) {

            $('html, body').animate({
                scrollTop: $(tab.data("scroll-to")).offset().top - 10
            }, 500);


        } else {

            var win = $(window).scrollTop() + $(window).height();
            var cont = contents.offset().top + getContainerHeight(contents);

            if ((win < cont)) {

                $('html, body').animate({
                    scrollTop: contents.position().top - 10
                }, 500);

            }
        }

    });

    updateTabsHandlers();

});


/*
 elements resize-event-change
 */

$(document).ready(function () {

    function adjustElements() {

        var windowWidth = $(window).width();

        $(document).find("[data-res-change]").each(function () {

            var elem = $(this);
            var options = undefined;

            if (isObject(elem.data("res-change"))) {
                options = elem.data("res-change");
            } else {
                return;
            }

            $.each(options, function (resolution, option) {

                resolution = resolution.split(",");
                var condition;

                if (resolution.length == 2) {

                    resolution[0] = parseInt(resolution[0]);
                    resolution[1] = parseInt(resolution[1]);

                    condition = (windowWidth <= resolution[0]) && (windowWidth >= resolution[1]);

                } else {

                    var vetsi = />/;
                    var vetsiRovno = />=/;
                    var mensi = /</;
                    var mensiRovno = /<=/;

                    if (vetsiRovno.test(resolution[0])) {

                        resolution[0] = parseInt(resolution[0].replace(">=", ""));
                        condition = (windowWidth >= resolution[0]);

                    } else if (mensiRovno.test(resolution[0])) {

                        resolution[0] = parseInt(resolution[0].replace("<=", ""));
                        condition = (windowWidth <= resolution[0]);

                    } else if (vetsi.test(resolution[0])) {

                        resolution[0] = parseInt(resolution[0].replace(">", ""));
                        condition = (windowWidth > resolution[0]);

                    } else if (mensi.test(resolution[0])) {

                        resolution[0] = parseInt(resolution[0].replace("<", ""));
                        condition = (windowWidth < resolution[0]);

                    } else {

                        resolution[0] = parseInt(resolution[0]);
                        condition = (resolution[0] >= windowWidth);

                    }
                }

                if (condition) {

                    $.each(option, function (attr, val) {

                        /* what to do if true */

                        if (attr == "moveTo") {

                            if (!elem.data("ignore-move-to") && !isDefined(elem.data("self-clone-id"))) {

                                console.log("ok");

                                var target = $(val);
                                var clone = elem.clone(true);
                                var unique = uniqid();

                                clone.data("ignore-move-to", true);
                                clone.data("clone-id", unique);

                                elem.hide();
                                elem.data("self-clone-id", unique);

                                if (!target.find('[data-clone-id="' + elem.data("self-clone-id") + '"]').length) {
                                    clone.appendTo(target);
                                }

                                elem.data("moved-clone", clone);
                            } else {
                                console.log("no ok");
                            }

                        } else if (attr == "hide") {

                            var hide_options = {
                                effect: "hide",
                                duration: 0
                            };

                            $.each(val, function (property, value) {

                                $.each(hide_options, function (prop, val) {

                                    if (prop == property) {
                                        if (value != "") {
                                            hide_options[prop] = value;
                                        }
                                    }

                                });

                            });

                            elem[hide_options.effect](hide_options.duration);

                        } else {

                            if (!isDefined(elem.data("old-" + attr + ""))) {
                                if (elem.hasAttr(attr)) {
                                    elem.data("old-" + attr + "", elem.attr(attr));
                                } else {
                                    elem.data("old-" + attr + "", "");
                                }
                            }

                            elem.attr(attr, val);

                        }

                    });

                } else {

                    /* what to do if false */

                    $.each(option, function (attr, val) {

                        if (attr == "moveTo") {

                            var target = $(val);

                            if (isDefined(elem.data("moved-clone"))) {

                                var clone = elem.data("moved-clone");
                                clone.data("ignore-move-to", false);

                                elem.replaceWith(clone);
                            }

                            elem.data("moved-clone", undefined);

                        } else if (attr == "hide") {

                            var show_options = {
                                effect: "show",
                                duration: 0
                            };

                            elem[show_options.effect](show_options.duration);

                        } else {

                            if (isDefined(elem.data("old-" + attr + ""))) {
                                elem.attr(attr, elem.data("old-" + attr + ""));
                                elem.data("old-" + attr + "", undefined);
                            }

                        }

                    });

                }

            });

        });

    }

    $(window).resize(function () {
        adjustElements();
    });

    adjustElements();

});

/* možnost vytočení čísla pomocí odkazu */

$(document).ready(function () {

    if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {

        var countrycodes = "1";
        var delimiters = "-|\\.|—|–|&nbsp;";
        var phonedef = "\\+?(?:(?:(?:" + countrycodes + ")(?:\\s|" + delimiters + ")?)?\\(?[2-9]\\d{2}\\)?(?:\\s|" + delimiters + ")?[2-9]\\d{2}(?:" + delimiters + ")?[0-9a-z]{4})";
        var spechars = new RegExp("([- \(\)\.:]|\\s|" + delimiters + ")", "gi"); //Special characters to be removed from the link
        var phonereg = new RegExp("((^|[^0-9])(href=[\"']tel:)?((?:" + phonedef + ")[\"'][^>]*?>)?(" + phonedef + ")($|[^0-9]))", "gi");

        function ReplacePhoneNumbers(oldhtml) {
            //Created by Jon Meck at LunaMetrics.com - Version 1.0
            var newhtml = oldhtml.replace(/href=['"]callto:/gi, 'href="tel:');
            newhtml = newhtml.replace(phonereg, function ($0, $1, $2, $3, $4, $5, $6) {
                if ($3)
                    return $1;
                else if ($4)
                    return $2 + $4 + $5 + $6;
                else
                    return $2 + "<a href='tel:" + $5.replace(spechars, "") + "'>" + $5 + "</a>" + $6;
            });
            return newhtml;
        }

        $("a[href^='tel:']").click(function (event) {
            event.preventDefault();

            link = $(this).attr('href');
            tracklink = link.replace("tel:", "");
            tracklink = tracklink.replace(spechars, "");
            if (tracklink.length == 10) {
                tracklink = "1" + tracklink;
            }

            ga('send', 'event', 'Contact', 'Phone', tracklink);
            //_gaq.push(['_trackEvent', 'Contact', 'Phone', tracklink]);

            setTimeout(function () {
                window.location = link;
            }, 300);
        });
    }

});