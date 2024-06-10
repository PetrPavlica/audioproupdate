$.fn.spinner = function (e) {

    var inputs = $(this);

    var spinner = {

        interval: undefined,
        interval2: undefined,

        setVal: function (element, up) {

            var btn = element;
            var inp = btn.closest(".spinner").find("input");

            var val = parseFloat(inp.val());
            var max = parseFloat(inp.attr("max"));
            var min = parseFloat(inp.attr("min"));

            if (up) {

                if ((val + 1) <= max) {

                    btn.siblings().last().removeClass("disabled");
                    inp.val(val + 1);
                    inp.trigger('change');

                    if ((val + 1) == max) {
                        btn.addClass("disabled");
                    }

                } else {
                    btn.addClass("disabled");
                }

            } else {

                if ((val - 1) >= min) {

                    btn.siblings().first().removeClass("disabled");
                    inp.val(parseFloat(inp.val()) - 1);
                    inp.trigger('change');
                    if ((val - 1) == min) {
                        btn.addClass("disabled");
                    }

                } else {
                    btn.addClass("disabled");
                }

            }

        },
        up: function (event) {

            if (event.type == "onClick") {
                this.interval = setInterval(function () {
                    spinner.setVal($(event.target), true);
                }, 100);
            } else {
                spinner.setVal($(event.target), true);
            }

        },
        down: function (event) {

            if (event.type == "onClick") {
                this.interval2 = setInterval(function () {
                    spinner.setVal($(event.target), false);
                }, 10);
            } else {
                spinner.setVal($(event.target), false);
            }

        },

        clear: function (event) {

            clearInterval(this.interval);
            clearInterval(this.interval2);

        },

        blur: function (event) {

            var input = $(event.target);

            if (input.val() == "") {
                input.val(1);
            }

        },

        build: function (input) {

            if (input.data("data-build") != true) {

                input.data("data-build", true);

                input.wrap('<div class="spinner-input flex flexElem valignCenter"></div>');
                input.parent().wrap('<div class="spinner flexElem alignElemsCenter"></div>');
                input.parent().parent().wrap('<div class="spinnerContainer"></div>');
                input.closest(".spinner").append('<div class="spinner-btns flexElem flex valignCenter alignElemsCenter flexWrap"></div>');

                var btns = input.closest(".spinner").find(".spinner-btns");

                btns.append('<div class="btn btn-up"></div><div class="btn btn-down"></div>');

                var max = parseFloat(input.attr("max"));
                var min = parseFloat(input.attr("min"));
                var val = parseFloat(input.val());

                if (val == min) {
                    btns.children().last().addClass("disabled");
                }

                if (val == max) {
                    btns.children().first().addClass("disabled");
                }

                btns.find(".btn-up").on("mousedown touch", $.proxy(spinner.up, spinner));
                btns.find(".btn-down").on("mousedown touch", $.proxy(spinner.down, spinner));
                btns.find(".btn").on("mouseup", $.proxy(spinner.clear, spinner));
                btns.find(".btn").bind("touchend", $.proxy(spinner.clear, spinner));

                input.on("blur", $.proxy(spinner.blur, spinner));
            }

        }

    };

    inputs.each(function () {
        spinner.build($(this));
    });

};
