$.datepicker.regional['cs'] = {
    closeText: 'Cerrar',
    prevText: 'Předchozí',
    nextText: 'Další',
    currentText: 'Hoy',
    monthNames: ['Leden', 'Únor', 'Březen', 'Duben', 'Květen', 'Červen', 'Červenec', 'Srpen', 'Září', 'Říjen', 'Listopad', 'Prosinec'],
    monthNamesShort: ['Le', 'Ún', 'Bř', 'Du', 'Kv', 'Čn', 'Čc', 'Sr', 'Zá', 'Ří', 'Li', 'Pr'],
    dayNames: ['Neděle', 'Pondělí', 'Úterý', 'Středa', 'Čtvrtek', 'Pátek', 'Sobota'],
    dayNamesShort: ['Ne', 'Po', 'Út', 'St', 'Čt', 'Pá', 'So'],
    dayNamesMin: ['Ne', 'Po', 'Út', 'St', 'Čt', 'Pá', 'So'],
    dateFormat: 'dd.mm.yy',
    firstDay: 1
};

$.datepicker.setDefaults($.datepicker.regional['cs']);

function preparePlugins(container) {

    container.find(".nice_select").each(function () {
        if (!$(this).hasClass("nice-select")) {
            $(this).niceSelect();
            //$(this).addClass("scrollbar-inner").scrollbar();
        }
    });

    container.find("input.icheck").each(function () {

        if (!$(this).parent().hasClass("icheckbox_flat") && !$(this).parent().hasClass("iradio_flat")) { // ošetří, že se init ichecku provede pouze jednou - pokud je již inicializovaný, tak nepokračuje dál
            if ($(this).closest("div.icheck").length) {
                $(this).closest("div.icheck").find(".iCheck-helper").remove();
                $(this).unwrap("div.icheck");
            }
            $(this).iCheck({
                checkboxClass: "icheckbox_flat",
                radioClass: "iradio_flat"
            });
        }

    });

    container.find("textarea").autogrow({
        vertical: true, horizontal: false
    });

    container.find(".rangeSlider").rangeSlider();
    container.find("input.number, input[type='number']").spinner();
    container.find(".responziveTable").responsiveTable();

    container.find(".toggles_switch").each(function () {

        var check = $(this).data("checkbox");

        $(this).toggles({
            drag: true,
            click: true,
            text: {
                on: "Jsem firma",
                off: "Jsem fyzická osoba"
            },
            animate: 250,
            easing: "swing",
            checkbox: $(check),
            type: "select"
        });

        $(document).find(".toggle").css({"width": "auto"}).find("div").css({"height": "auto", "width": "auto"});

    });

    container.find("input.date_picker").each(function () {

        if (!isDefined($(this).data("builded"))) {

            var default_settings = {

                disabledDates: [],
                noWeekends: false,
                minDate: false,
                currentDate: true,
                disableHolidays: false

            };

            var Allholidays = CONSTANTS.holidays;

            if (isDefined($(this).data("date-settings"))) {
                var settings = $(this).data("date-settings");
                parseOptions(default_settings, settings);
            }

            var id = uniqid();
            $(this).attr("id", id);
            var date = new Date();

            var input = $(document).find("input#" + id + "");

            input.datepicker({

                beforeShowDay: function (date) {

                    var noWeekend = [];

                    if (default_settings.noWeekends) {
                        noWeekend = $.datepicker.noWeekends(date);
                    } else {
                        noWeekend[0] = true;
                    }

                    var holidays = [];

                    if (default_settings.disableHolidays) {
                        holidays = Allholidays;
                    }

                    var disable = [];

                    if (default_settings.disabledDates.length) {
                        disable = default_settings.disabledDates;
                    }

                    var string = jQuery.datepicker.formatDate('dd.mm', date);
                    return [holidays.indexOf(string) == -1 && disable.indexOf(string) == -1 && noWeekend[0]];

                },
                onSelect: function (selectedDate, inst) {

                    $(inst.input).val(selectedDate);
                    $(inst.input).focus().blur();

                }

            });

            if (default_settings.currentDate) {
                input.datepicker("setDate", date);
            }

            if (default_settings.minDate != false) {
                input.datepicker("minDate", default_settings.minDate);
            }

            $(this).data("builded", true);
        }

    });

    container.find(".toggle.toggle-modern").each(function () {
        $(this).css({"width": "auto"});
        $(this).find(".toggle-inner").css({"width": "auto"}).children().css({"width": "auto"});
    });

    preparePaginators();

    container.find(".dragscroll").each(function () {
        $(this).addClass("scrollbar-inner").scrollbar();
    });

}

window.preparePlugins = preparePlugins;

$(document).ready(function () {
    var headerHeight = 0;
    var reklama = $('#reklamni_sdeleni');
    if (reklama.length) {
        headerHeight += reklama.outerHeight();
    }
    var header = $('body > header');
    if (header.length) {
        headerHeight += header.outerHeight();
    }
    var nav = $('body > nav');
    if (nav.length) {
        headerHeight += nav.outerHeight();
    }
    /*var slider = $('#main #slider');
    if (slider.length) {
        headerHeight += slider.outerHeight();
    }
    var hodnoty = $('#main #hodnoty_container');
    if (hodnoty.length) {
        headerHeight += hodnoty.outerHeight();
    }*/
    var body = $('body');
    if (body && body.hasClass('christmas')) {
        body.css('background-position-y', (headerHeight - 27) + 'px');
    }

    $(document).find(".icheck .iCheck-helper").each(function () {
        if ($(this).parent().find("input").is(":checked")) {
            $(this).click();
        }
    });

    $(document).on('click', '#search_toggle', function () {
        $('#head #contact').slideToggle();
    });

    $(document).on('click', '.collapse-block', function() {
        if ($(this).hasClass('show')) {
            $(this).removeClass('show');
        } else {
            $(this).addClass('show');
        }
    });

    /*var fixedBarTop = new fixedBar({
        resRange: '0-768',
        showY: 200,
        classes: {
            container: "spread",
            body: "alignCenter"
        }
    });

    fixedBarTop.createGroup();
    fixedBarTop.addItem("#head #logo", 0);
    fixedBarTop.addItem("#hamburger", 0);
    fixedBarTop.addItem("#head .basket_container", 0);*/

    new countDown($("#odpocet_casu"));

    preparePlugins($(document));

    if (flashesRendered == false) {
        flashesRendered = true;
        handleFlashess();
    }

    if ($(window).width() < 480) {
        setTimeout(function () {
            $(document).find(".dop__stripe").slideUp(300);
        }, 8000);
    }

    $(document).find(".btgrid .row .col iframe").each(function () {

        var width = $(this).outerWidth() - 50;
        var height = $(this).outerHeight();

        var ratio = width / height;
        var parentW = $(this).parent().outerWidth();

        $(this).css({"width": parentW, "height": height * ratio});

    });

    $(document).on("click", ".product a.buy_btn, button.buy_btn", function (e) {

        e.preventDefault();

        var data = {};
        var url;

        if($(this).is("button")){

            var form = $(this).closest("form");

            url = new URL($(this).attr("data-href"));
            data = $.extend(data, url.getParameters());

            form.find('input[type="hidden"], input[type="text"], input[type="number"], input[type="checkbox"], input[type="radio"]:checked, select').each(function () {
                data[$(this).attr("name")] = $(this).val();
            });

        } else {
            url = new URL($(this).attr("href"));
            data = $.extend(data, url.getParameters());
        }

        if (isObject($(this).data("also-post"))) {

            $.each($(this).data("also-post"), function (i, v) {

                if ($(document).find(v).length) {

                    var el = $(document).find(v);
                    var val = el.val();

                    if (el.is(":checkbox") || el.is(":radio")) {

                        if (el.is(":checked")) {
                            data[i] = val;
                        }

                    } else {

                        if (isDefined(val)) {
                            data[i] = val;
                        }

                    }

                }

            });

        }

        $.each(data, function (i, v) {

            if (!isNaN(v)) {

                if (v % 1 !== 0) {
                    data[i] = parseFloat(v);
                } else {

                    if (v % 1 === 0) {
                        data[i] = parseInt(v);
                    }
                }
            }

        });

        $.nette.ajax({
            url: url.getBaseUrl(),
            type: "GET",
            off: {
                snippets: true
            },
            data: data
        }).done(function (payload) {

            if (payload["completed"] == 1) {

                new BasicDialog({
                    content: payload["data"]
                }, function (dialog) {

                    if (isObject(dialog)) {
                        var progress = new Progress();
                        progress.progressBar(dialog.find(".freeDeliveryBar"));
                        var swiper3 = new Swiper('#swiper3', {
                            nextButton: '#swiper3_left',
                            prevButton: '#swiper3_right',
                            loop: false,
                            effect: "slide",
                            autoplay: false,
                            speed: 600,
                            scrollbarHide: true

                        });
                    }

                });

            }

        });

    });

    /*var previousMenu = null;

    $(document).on('click', 'nav #menu > .drop_down > a', function(e) {
        if ($(this).data('id')) {
            if (previousMenu != $(this).data('id')) {
                e.preventDefault();
            }
            previousMenu = $(this).data('id');
        }

        $(this).parent().find('.sub_menu').addClass('visible');
    });*/

});


$(window).on('load', function () {
    if ($("#loga_slider").length) {
        var sly_slider = $("#loga_slider");
        var sly_slider_instance;

        function resizeSlySlider() {

            if ($(window).width() > 768) {
                sly_slider.css("max-width", sly_slider.parent().width() - 40);
            } else {
                sly_slider.css("max-width", sly_slider.parent().width() - 20);
            }

            sly_slider_instance.reload();
        }

        sly_slider_instance = sly_slider.applySly("", {
            cycleBy: "pages"
        });

        resizeSlySlider();

        $(window).resize(function () {
            resizeSlySlider();
        });
    }
});