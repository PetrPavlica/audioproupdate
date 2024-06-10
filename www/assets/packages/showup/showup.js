/*
 * Showup.js jQuery Plugin
 * http://github.com/jonschlinkert/showup
 *
 * Copyright (c) 2013 Jon Schlinkert, contributors
 * Licensed under the MIT License (MIT).
 */

// TODO: make customizable
$(document).ready(function () {
    var duration      = 420;
    var showOffset    = 220;
    var btnFixed      = '.btn-fixed-bottom';
    var btnToTopClass = '.back-to-top';

    $(window).scroll(function () {
        if ($(this).scrollTop() > showOffset) {
            $(btnFixed).fadeIn(duration);
        } else {
            $(btnFixed).fadeOut(duration);
        }
    });

    $(btnToTopClass).click(function (event) {
        event.preventDefault();
        $('html, body').animate({
            scrollTop: 0
        }, duration);
        return false;
    });
});

$(document).ready(function () {

    var duration      = 800;
    var showOffset    = 220;
    var btnToTopClass = '#back-to-top';

    $(window).scroll(function () {
        if ($(this).scrollTop() > showOffset) {
            $(btnToTopClass).fadeIn(duration);
        } else {
            $(btnToTopClass).fadeOut(duration);
        }
    });

    $(btnToTopClass).click(function (event) {

        event.preventDefault();

        $('html, body').animate({
            scrollTop: 0
        }, duration);
        return false;

    });

});
