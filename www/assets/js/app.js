import $ from 'jquery';
window.$ = window.jQuery = $;
import 'bootstrap';
require('jquery-ui');
require('jquery-ui/ui/widgets/datepicker');
require('jquery-ui/ui/widgets/slider');

import toastr from 'toastr';
window.toastr = toastr;

import 'select2';

require('./funkce.js');
require('./iframeResizer.min.js');
require('./ElementQueries.js');
import ResizeSensor from './ResizeSensor';
window.ResizeSensor = ResizeSensor;
require('./dragscroll.js');
require('./easing.js');
require('./modernizr.js');
require('./menu.js');
//require('./jquery.punch.js');
require('../packages/scroll_bar/jquery.scrollbar.min.js');
require('../packages/dialog/dialog.js');
require('../packages/swiper/js/swiper.jquery.min.js');
require('../packages/nice_select/js/jquery.nice-select.min.js');
require('../packages/icheck/js/icheck.min.js');
require('../packages/sly_slider/js/sly.js');
require('../packages/lightbox/js/lightbox.js');
require('../packages/bar_chart/js/bar_chart.js');
require('../packages/textarea_autogrow/js/textarea_grow.js');
require('../packages/showup/showup.js');
require('../packages/paginator/paginator.js');
require('../packages/spinner/spinner.js');
require('../packages/progress/progress.js');
require('../packages/toggles/toggles.min.js');

require('../packages/fancybox/jquery.fancybox.pack.js');
require('./masonry.pkgd.min');
require('../packages/jquery-confirm/jquery-confirm.min');

require('./init.js');

require('./nette.ajax.js');
require('../../core/packages/completer/js/completer.js');
require('./kosik');
require('vanilla-cookieconsent/dist/cookieconsent');

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

import LazyLoad from "vanilla-lazyload";

var lazyLoadInstance = new LazyLoad({
    elements_selector: ".lazy",
    use_native: true
});

var $window = $(window);

$window.on('scroll', check_if_in_view);

function check_if_in_view() {
    var window_height = $window.height();
    var window_top_position = $window.scrollTop();
    var window_bottom_position = (window_top_position + window_height);

    $('header').toggleClass('fixed', window_top_position > 0);
}

$(function () {
    $.nette.init();

    if (lazyLoadInstance) {
        lazyLoadInstance.update();
    }

    $(document).on('click', 'header .menu', function() {
        $('body').addClass('menu');
        $('#sidebar-overlay').show();
    });

    $(document).on('click', '#sidebar-overlay .exit-btn', function() {
        $('body').removeClass('menu');
        $('#sidebar-overlay').hide();
    });

    var progress = new Progress();

    $(document).on("ifChecked ifUnchecked change", "#product-filters input.icheck", function () {
        var form = $(this).closest('form');
        delay(function () {
            progress.insertInto($(document).find("#products_vypis"), true, true);
            form.submit();
            $(document).ajaxComplete(function () {
                progress.remove();
            });
        }, 300);
    });

    $(document).on("click", "#filter a.change-product-type", function (e) {
        e.preventDefault();
        progress.insertInto($(document).find("#products_vypis"), true, true);
        $.nette.ajax({
            url: $(this).attr('href')
        });
        $(document).ajaxComplete(function () {
            progress.remove();
        });
    });

    $(document).on("click", "#product-filters a.sort-action", function (e) {
        e.preventDefault();
        progress.insertInto($(document).find("#products_vypis"), true, true);
        $.nette.ajax({
            url: $(this).attr('href')
        });
        $(document).ajaxComplete(function () {
            progress.remove();
        });
    });

    $window.trigger('scroll');
});

toastr.options = {
    "closeButton": true,
    "debug": false,
    "newestOnTop": true,
    "progressBar": false,
    "positionClass": "toast-top-right",
    "preventDuplicates": true,
    "onclick": null,
    "showDuration": "300",
    "hideDuration": "1000",
    "timeOut": "5000",
    "extendedTimeOut": "5000",
    "showEasing": "swing",
    "hideEasing": "linear",
    "showMethod": "fadeIn",
    "hideMethod": "fadeOut"
};

window.addEventListener('load', function() {
    var cookieconsent = initCookieConsent();

    // run plugin with your configuration
    cookieconsent.run({
        autorun: true,
        current_lang: document.documentElement.getAttribute('lang'),
        autoclear_cookies: true,
        page_scripts: true,
        hide_from_bots: true,
        gui_options: {
            consent_modal: {
                layout: 'cloud',               // box/cloud/bar
                position: 'bottom center',     // bottom/middle/top + left/right/center
                transition: 'slide'            // zoom/slide
            },
            settings_modal: {
                layout: 'box',                 // box/bar
                // position: 'left',           // left/right
                transition: 'slide'            // zoom/slide
            }
        },
        languages: {
            cs: {
                consent_modal: {
                    title: 'Nastavení soukromí',
                    description: 'Na našich webových stránkách používáme soubory cookies. Některé z nich jsou nezbytné, zatímco jiné nám pomáhají vylepšit tento web a váš uživatelský zážitek. Souhlasíte s používáním všech cookies? <button type="button" data-cc="c-settings" class="cc-link">Podrobné nastavení</button>',
                    primary_btn: {
                        text: 'Rozumím a přijímám vše',
                        role: 'accept_all'              // 'accept_selected' or 'accept_all'
                    },
                    secondary_btn: {
                        text: 'Odmítnout vše',
                        role: 'accept_necessary'                // 'settings' or 'accept_necessary'
                    }
                },
                settings_modal: {
                    title: 'Předvolby souborů cookies',
                    save_settings_btn: 'Uložit nastavení',
                    accept_all_btn: 'Přijmout vše',
                    reject_all_btn: 'Odmítnout vše',       // optional, [v.2.5.0 +]
                    blocks: [
                        {
                            title: 'Technická cookies',
                            description: 'Technické cookies jsou nezbytné pro správné fungování webu a všech funkcí, které nabízí. Jsou odpovědné mj. za uchovávání produktů v košíku, zobrazování seznamu oblíbených výrobků (schránka), působení filtrů, nákupní proces a ukládání nastavení soukromí. Nepožadujeme Váš souhlas s využitím technických cookies na našem webu. Z tohoto důvodu technické cookies nemohou být individuálně deaktivovány nebo aktivovány.',
                            toggle: {
                                value: 'necessary',
                                enabled: true,
                                readonly: true
                            }
                        }, {
                            title: 'Analytické cookies',
                            description: 'Analytické cookies nám umožňují měření výkonu našeho webu a našich reklamních kampaní. Jejich pomocí určujeme počet návštěv a zdroje návštěv našich internetových stránek. Data získaná pomocí těchto cookies zpracováváme souhrnně, bez použití identifikátorů, které ukazují na konkrétní uživatelé našeho webu. Pokud vypnete používání analytických cookies ve vztahu k Vaší návštěvě, ztrácíme možnost analýzy výkonu a optimalizace našich opatření.',
                            toggle: {
                                value: 'analytics',
                                enabled: false,
                                readonly: false
                            }

                        }, {
                            title: 'Reklamní cookies',
                            description: 'Reklamní cookies používáme my nebo naši partneři, abychom Vám mohli zobrazit vhodné obsahy nebo reklamy jak na našich stránkách, tak na stránkách třetích subjektů. Díky tomu můžeme vytvářet profily založené na Vašich zájmech, tak zvané pseudonymizované profily. Na základě těchto informací není zpravidla možná bezprostřední identifikace Vaší osoby, protože jsou používány pouze pseudonymizované údaje. Pokud nevyjádříte souhlas, nebudete příjemcem obsahů a reklam přizpůsobených Vašim zájmům.',
                            toggle: {
                                value: 'ads',
                                enabled: false,
                                readonly: false
                            },
                        }
                    ]
                }
            }

        }
    });
});