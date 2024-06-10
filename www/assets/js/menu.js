$(document).ready(function() {
    var mainMenu = $("#menu_container");
    var ovladaciLista = $("#ovladani-menu-mobil");
    var vysunuteSubMenu = null;
    var previousMenu = null;

    //$( "#top .wrap .content" ).clone().insertAfter( "#menu_zasun_ul_0" );   
    $( "#top .wrap .content .right .currencies" ).clone().insertAfter( "#menu_zasun_ul_0" );
    $( "#log_reg" ).clone().insertAfter( "#menu_zasun_ul_0" );  

    $( "#summary .odkazy-ikony .top" ).clone().insertAfter( "#menu .rest" ); 
    $( "#summary .odkazy" ).clone().insertAfter( "#menu .rest" );

    $(document).on("click touch", "#hamburger", function() {    //---------- HAMBURGER BUTTON
        
        if (vysunuteSubMenu) {
            vysunuteSubMenu.removeClass('visible-on-smallscreens visible'); 
        }

        previousMenu = null
        
        mainMenu.removeClass('vysun-uroven-0 vysun-uroven-1 zasun-menu-i-submneu');
        ovladaciLista.removeClass('vysun-uroven-1');

        mainMenu.addClass('vysun-uroven-0');
        

        $('body').addClass('visible-menu');

    });

    $(document).on("click touch", "#menu_zasun_ul_0", function() { // ---- zasunout uroven 0 (prvni ul) = zasunout menu
        if (mainMenu.hasClass( 'vysun-uroven-1' )) {    
            mainMenu.addClass('zasun-menu-i-submneu');

        } else {
            if (vysunuteSubMenu) {
                vysunuteSubMenu.removeClass('visible-on-smallscreens'); 
            }
            mainMenu.removeClass('vysun-uroven-0'); 

        }
        
        $('body').removeClass('visible-menu');

        
        $('.drop_down').add("#menu > .clone-to-mob-menu").each(function() {
            $(this).removeClass('hover');
        });


    });

    $(document).on("click touch", ".menu_zasun_ul_1", function() { // ----  zasunout uroven 1 - close button (submenu i cele menu)  
        vysunuteSubMenu.removeClass('visible');       // css definovano jen na velkych obrazovkach
        mainMenu.addClass('zasun-menu-i-submneu');    // css definovano jen na mobilu
        $('body').removeClass('visible-menu');

        $('.drop_down').add("#menu > .clone-to-mob-menu").each(function() {
            $(this).removeClass('hover');
        });

    });


    $(document).on("click touch", ".menu_zpet_na_ul_0", function() {  // --- zpet na uroven 0
        mainMenu.removeClass('vysun-uroven-1');
        
        //vysunuteSubMenu.removeClass('vysun-uroven-1');
        previousMenu = null;
    });



    mainMenu.find("a.no_link").on("click touch", function(e) {
        e.preventDefault();
    });

    $("#menu > li.drop_down").on("hover", function(e) {
        var elem = $(this);
        var clear = false;
        if (isDefined(elem) && elem.data("clicked")) {
            clear = true;
            elem.data({
                "clicked": false
            });
        } else {
            elem.data({
                "clicked": true
            });
        }
        elem.siblings().each(function() {
            $(this).removeClass("hover");
            $(this).find("> a").removeClass("hover");
        });
        var all = elem.add(elem.find("a.no_link").first());
        if (clear) {
            all.removeClass("hover");
        } else {
            all.addClass("hover");
        }
    });

    

    $("#menu > li.drop_down .sub_menu .exit-btn").on('click touch', function (e) {  
        $(this).parent().parent().removeClass('visible').removeAttr('style');
        $(this).parent().parent().parent().removeClass('hover');
        previousMenu = null;
    });

    $("#menu > li.drop_down > a").add("#menu > .clone-to-mob-menu > .nadpis").on("click touch", function(e) {  // --------------------------- SUBMENU
        var a = $(this);
        var elem = a.parent();

        mainMenu.addClass('vysun-uroven-1'); // --- v css bude prazdna pro > 768
        ovladaciLista.addClass('vysun-uroven-1');
        

        if (a.data('id')) {   // --- pokud ma submenu
            if (previousMenu != a.data('id')) { // --- link funguje na druhy klik
                e.preventDefault();
            }
            previousMenu = a.data('id');
        }

        if (vysunuteSubMenu) {
            vysunuteSubMenu.removeClass('visible'); 
            vysunuteSubMenu.removeClass('visible-on-smallscreens'); 
        } 

        vysunuteSubMenu = elem.children(".sub_menu").first();

        vysunuteSubMenu.addClass('visible');
        vysunuteSubMenu.addClass('visible-on-smallscreens'); 

        var siblings = elem.siblings("li.drop_down").add("#menu > .clone-to-mob-menu"); // ---- hide all submenus

        siblings.each(function() {
            $(this).removeClass('hover');
        });
        elem.addClass('hover');
    });

});