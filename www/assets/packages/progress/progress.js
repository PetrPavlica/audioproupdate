var progressIdCounter = 1;

var Progress = function () {

    var ID = progressIdCounter;
    var container;

    function moveToBody(obj) {
        return obj.find("#mainProgressEventBody");
    }

    this.insert = function () {
        var content = '<div class="mainProgressEventT" id="mainProgressEvent' + ID + '">' + '<div id="mainProgressEventBody">Prosím čekejte...<br />' + '<img src="' + PATHS.icons + 'progress/mainprogress.gif" alt="progress" title="progress"/>' + '</div></div>';
        $("body").append(content);
        container = $("#mainProgressEvent" + ID + "");
        moveToBody(container).center();
    };

    this.insertInto = function (target, pointer, transparent) {

        if (isObject(target)) {
            if (!isDefined(transparent)) {
                transparent = true;
            }
            if (!isDefined(pointer)) {
                pointer = false;
            }
            if (transparent) {
                var id = "mainProgressEventT";
                var img = "mainprogress2B.gif";
            } else {
                var id = "mainProgressEvent";
                var img = "mainprogress2B.gif";
            }
            var content = '<div class="' + id + ' flexElem valignCenter alignCenter" id="mainProgressEvent' + ID + '" style="position: absolute; top: 0px; left 0px; width: 100%; height: 100%;">' + '<div id="mainProgressEventBody" class="flexElem valignCenter alignCenter">' + '<img src="' + PATHS['basePath'] + '/assets/packages/progress/icons/' + img + '" alt="progress" title="progress"/>' + '</div></div>';
            target.append(content);
            container = $(document).find("#mainProgressEvent" + ID + "");

        }
    };

    var progressBarRefresh = function (target) {

        if(!isObject(target)){
            target = $(document).find(target);
        }

        var actualPercent = parseInt(target.data("percent"));

        var width = target.outerWidth();
        var completed = (width*actualPercent)/100;

        if(!target.find(".full_bar").length) {
            target.append('<div class="full_bar"><div style="width: 0px;" class="completed_bar"></div></div>');
        }

        target.find(".completed_bar").animate({
            width: completed
        }, 3000, "easeOutQuart");

    };

    this.progressBar = function (target) {

        $(window).resize(function () {
            progressBarRefresh(target);
        });

        progressBarRefresh(target);
    };

    this.remove = function () {
        container.remove();
    };

    progressIdCounter++;

};

window.Progress = Progress;