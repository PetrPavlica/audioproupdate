var dialogIDCounter = 1;
var buttonsList = {};

var AbstractDialog = function (new_options) {

    var options = {};
    var ID = dialogIDCounter;

    /**
     *
     * @param button - button jQuery object, which triggers event to show dialog
     * @returns {boolean}
     */
    function inArray(button) {

        var founded = false;

        if (isDefined(button)) {

            $.each(buttonsList, function (i,v) {

                if(isDefined(v)) {
                    if (v.is(button)) {
                        founded = true;
                    }
                }

            });

        }
        return founded;
    }

    /**
     *
     */
    function setOptions(){

        options = {

            buttonPointer: undefined,
            event: {},
            close: true,

            bar: {
                title: " ",
                options: undefined,
                help: false
            },

            defaults: {
                centered: true,
                dialog: true,
                fixed: false,
                draggable: true
            },

            normal: {
                centered: true,
                dialog: true,
                fixed: false,
                draggable: true
            },

            mobile:{
                centered: true,
                dialog: true,
                fixed: true,
                draggable: false
            },

            insertIntoParent: true,
            content: " ",

            width: undefined,
            height: undefined,

            lastTop: 0,

            beforeClose: undefined,
            afterClose: undefined,

            changesDetect: false,
            changeFound: false,

            initialLeft: undefined,

            classes: {
                container: "",
                bar: "",
                title: "",
                close: "",
                body: "",
                content: ""
            }
            
        };
    }

    /**
     *
     * @param container - dialog jQuery object
     * @param object - event object
     * @param callback - callback as soon as all images loaded and dialog positioned
     * @private
     */
    function _positionDialog(container, object, callback) {

        var viewportWidth = $(window).width();
        var viewportHeight = $(window).height();

        var documentHeight = getDocHeight();

        var ContainerHeight = getContainerHeight(container);
        var ContainerWidth = container.width();

        var viewPortTop = $(document).scrollTop();
        var viewPortLeft = $(document).scrollLeft();

        var newPosX;
        var newPosY;
        var position = "absolute";

        if ((container.attr("data-parent-window") != '' && options.insertIntoParent) && !options.defaults.dialog) {

            var parent = getParentDialog(container);

            var parentTop = parent.offset().top;
            var parentLeft = parent.offset().left;

            var parentH = getContainerHeight(moveToBody(parent).parent());
            var parentW = moveToBody(parent).width();
            var parentScroll = moveToBody(parent).parent().scrollTop();

            newPosX = object.pageX - parentLeft;
            newPosY = object.pageY - parentTop;

            if(options.defaults.centered) {

                if (viewportHeight < parentH) {

                    if (parentTop < viewPortTop) {

                        if ((parentTop + parentH) > (viewPortTop + viewportHeight)) {
                            newPosY = parentScroll + (viewPortTop - parentTop) + (viewportHeight / 2) - (ContainerHeight / 2);
                        } else {
                            var visible = viewportHeight - ((viewPortTop + viewportHeight) - (parentTop + parentH));
                            newPosY = parentScroll + viewPortTop - parentTop + visible / 2 - (ContainerHeight / 2);
                        }

                    } else {

                        newPosY = parentScroll + (viewPortTop - parentTop) + ((viewPortTop + viewportHeight - parentTop) / 2) - (ContainerHeight / 2);

                        if(newPosY < parentTop){
                            newPosY = parentTop;
                        }

                        if((newPosY + ContainerHeight) > (parentTop + parentH)){
                            newPosY = parentTop + parentH - 10;
                        }

                    }

                } else {
                    newPosY = (parentH / 2) - (ContainerHeight / 2);
                }

                newPosX = (parentW / 2) - (ContainerWidth / 2);

            } else {

                if ((newPosY + ContainerHeight) >= (parentH)) {

                    newPosY = parentH - (ContainerHeight + 40);

                    if (newPosY < 0) {
                        newPosY = 30;
                    }
                }

                if ((newPosX + ContainerWidth) > parentW) {
                    newPosX = parentW - (ContainerWidth + 50);
                }

            }

        } else {

            if (!options.defaults.centered) {

                if(container.css("left") != "auto"){
                    newPosX = parseInt(container.css("left"));
                } else {
                    newPosX = object.pageX;
                }

                if(!isDefined(options.initialLeft)){
                    options.initialLeft = newPosX;
                }

                if(container.css("top") != "auto"){
                    newPosY = parseInt(container.css("top"));
                } else {
                    newPosY = object.pageY;
                }

                if(options.defaults.fixed) {

                    if (screen.width >= 700) {

                        if ((newPosY + ContainerHeight) > (viewPortTop + viewportHeight)) {
                            newPosY = (viewPortTop + viewportHeight) - ContainerHeight - 50;
                        }

                        if ((newPosY + ContainerHeight) > documentHeight) {
                            newPosY = (documentHeight - 50) - ContainerHeight;
                        }

                        newPosY = viewPortTop + (newPosY - options.lastTop);

                    }
                }

                if ((newPosX + ContainerWidth) > (viewportWidth)) {
                    newPosX = viewportWidth - ContainerWidth - 70;
                } else {

                    if(newPosX < options.initialLeft){

                        newPosX = viewportWidth - ContainerWidth - 70;

                    }

                }

                if(newPosX <= 0){
                    newPosX = 10;
                }

            }

            if (options.defaults.centered) {

                if (ContainerHeight > viewportHeight) {

                    if (container.css("left") != 'auto' && container.css("top") != 'auto') {

                        var top = parseInt(container.css("top"));
                        var left = parseInt(container.css("left"));

                        if ((viewPortTop + viewportHeight) < (top + ContainerHeight)) {

                            if (viewPortTop + 50 < top) {
                                newPosY = viewPortTop + 50;
                            } else {
                                newPosY = top;
                            }

                        } else {

                            if(viewPortTop < options.lastTop){
                                newPosY = viewPortTop + 50;
                            } else {
                                newPosY = (viewPortTop + viewportHeight - 30) - ContainerHeight;
                            }

                        }

                        if ((newPosY + ContainerHeight) > documentHeight) {
                            newPosY = (documentHeight - 30) - ContainerHeight;
                        }


                    } else {
                        newPosY = viewPortTop + 50;
                    }

                } else {
                    newPosY = viewPortTop + (viewportHeight / 2) - (ContainerHeight / 2);
                }

                newPosX = viewPortLeft + (viewportWidth / 2) - (ContainerWidth / 2);

            }
        }

        options.lastTop = viewPortTop;
        container.css({position: position, left: newPosX, top: newPosY});

        callback();
    }

    /**
     *
     * @param container - dialog jQuery object
     * @param object - event object
     * @param callback - callback as soon as all images loaded and dialog positioned
     * @private
     */
    function positionDialog(container, object, callback) {

        if (screen.width < 700) {
            options.defaults.centered = true;
        }

        if (moveToBody(container).find("img").length > 0) {

            var loaded = 0;
            var IMG = moveToBody(container).find("img");
            var numImages = IMG.length;

            IMG.batchImageLoad({
                loadingCompleteCallback: function () {

                    _positionDialog(container, object, function(){
                        callback();
                    });

                }
            });

        } else {
            _positionDialog(container, object, function(){
                callback();
            });
        }
    }

    /**
     *
     * @param obj - dialog jQuery object
     * @param width - user defined width
     * @param orientation - mobile orientation
     */
    function setWidth(obj, width, orientation) {

        var objW = obj.outerWidth(true);
        var viewPortW;

        if ((obj.attr("data-parent-window") && options.insertIntoParent) && !options.defaults.dialog) {
            viewPortW = getParentDialog(moveToContainer(obj)).width();
        } else {
            if (isDefined(orientation)) {
                if (orientation == 90 || orientation == -90) {
                    viewPortW = $(window).width();
                } else {
                    viewPortW = $(window).height();
                }
            } else {
                viewPortW = $(window).width();
            }
        }

        var maxWidth;
        var viewPortLeft = $(document).scrollLeft();

        var percent = 80;

        if (!isDefined(orientation) && viewPortW > 700) {

            if (options.defaults.centered) {

                maxWidth = ($(window).width() * percent) / 100 + "px";

            } else {

                if (((viewPortLeft + viewPortW) - objW) > 0) {
                    maxWidth = (viewPortLeft + viewPortW) - objW;
                } else {
                    maxWidth = ($(window).width() * percent) / 100 + "px";
                }

            }

            if (isDefined(width)) {

                if (width == "full") {

                    width = (viewPortW * percent) / 100 + "px";

                } else if (width == "auto") {

                    width = "auto";

                } else {

                    var patt = /[0-9]*%/;

                    if (!patt.test(width)) {

                        width = parseInt(width);

                        if (width > maxWidth) {
                            width = maxWidth + "px";
                        } else {
                            width = width + "px";
                        }

                    }
                }

            } else {
                width = "auto";
            }

            if (maxWidth > 1900) {
                maxWidth = 1900 + "px";
            }

        } else {
            maxWidth = (viewPortW * 90) / 100 + "px";
        }

        obj.css({"width": width, "max-width": maxWidth});
    }

    /**
     *
     * @param obj - dialog jQuery object
     * @param height - user defined height
     * @param event - jQuery event
     */
    function setHeight(obj, height, event) {

        var objH = obj.height();

        var maxHeight;
        var viewPortTop = $(document).scrollTop();
        var viewPortH;

        if ((obj.attr("data-parent-window") && options.insertIntoParent) && !options.defaults.dialog) {
            viewPortH = getContainerHeight(moveToBody(getParentDialog(moveToContainer(obj))).parent());
        } else {
            viewPortH = $(window).height();
        }

        var percent = 90;

        if (options.defaults.centered) {
            maxHeight = ((getDocHeight() - 30) - (viewPortTop + 30)) + "px";
        } else {

            if (((viewPortTop + viewPortH) - objH) > 0) {
                maxHeight = (viewPortTop + viewPortH) - objH;
            } else {
                maxHeight = (viewPortH * percent) / 100 + "px";
            }
        }

        if (isDefined(height)) {

            if (height == "full") {

                height = (viewPortH * 90) / 100 + "px";

            } else if (height == "auto") {

                height = "auto";

            } else {

                var patt = /[0-9]*%/;

                if (!patt.test(height)) {

                    height = parseInt(height);

                    if (height > maxHeight) {
                        height = maxHeight + "px";
                    } else {
                        height = height + "px";
                    }

                }
            }

        } else {
            height = "auto";
        }

        moveToBody(obj).parent().css({"height": height, "max-height": maxHeight});
    }

    /**
     *
     * @param parent - parent dialog object
     * @param id - dialog id
     * @param orientation - mobile orientation
     */
    function setSublayerDim(parent, id, orientation) {

        if(options.defaults.dialog) {

            var docW;
            var docH;

            if ((isDefined(parent) && options.insertIntoParent) && !options.defaults.dialog) {

                if (isDefined(orientation) && (orientation == 90 || orientation == -90)) {
                    docW = getContainerHeight(moveToBody($("#" + parent.id + "")).parent());
                    docH = moveToBody($("#" + parent.id + "")).outerWidth();
                } else {
                    docW = moveToBody($("#" + parent.id + "")).outerWidth();
                    docH = getContainerHeight(moveToBody($("#" + parent.id + "")).parent());
                }

            } else {

                if (isDefined(orientation) && (orientation == 90 || orientation == -90)) {
                    docW = screen.width;
                    docH = screen.height;
                } else {
                    docW = $(window).width();
                    docH = getDocHeight();
                }
            }

            $("#mainWindowTransparentBackground" + id + "").css({
                "width": "" + docW + "px",
                "height": "" + docH + "px"
            });

        }

    }

    /**
     *
     * @param target - parent dialog object
     * @returns {*}
     */
    function getParentDialog(target) {

        target = moveToContainer(target);

        if(target.closest(".mainWindowContainer").length > 0){
            return target.parent().closest(".mainWin dowContainer");
        } else {
            return false;
        }
    }

    /**
     *
     * @param target - parent dialog object
     */
    this.getParentDialog = function(target){
        getParentDialog(target);
    };

    /**
     *
     * @param target - object into dialog
     * @returns {*}
     */
    function moveToContainer(target) {

        if(isObject(target)) {
            if (target.hasClass("mainWindowContainer")) {
                return target;
            } else {

                if (target.closest(".mainWindowContainer").length > 0) {
                    return target.closest(".mainWindowContainer");
                } else {
                    return false;
                }

            }
        }

        return false;
    }

    this.moveToContainer = function(target) {
        return moveToContainer(target);
    };

    function getDialogElemId(button) {
        var target = moveToContainer(button);
        return target.attr("id");
    }

    this.getDialogElemId = function(button) {
        return getDialogElemId(button);
    };

    function moveToBody(from) {
        var target = moveToContainer(from);
        target = target.find(".mainWindowBody").find(".mainWindowEditorBody").first();
        return target;
    }

    this.moveToBodyy = function(from) {
        return moveToBody(from);
    };

    function refresh(dialog, orientation, callback) {

        if(!isWindow(dialog)){
            dialog = moveToContainer(dialog);
        }

        var id = getDialogElemId(dialog);
        var event;

        if (options.defaults.centered) {
            event = {};
        } else {
            event = options.event;
        }

        var width = undefined, height = undefined;

        if(isDefined(dialog.attr("data-width-property"))){
            width = dialog.attr("data-width-property");
        }
        if(isDefined(dialog.attr("data-height-property"))){
            height = dialog.attr("data-height-property");
        }

        var body = dialog.find(".mainWindowBody");
        var editor_body = dialog.find(".mainWindowEditorBody");

        setWidth(dialog,width, orientation);
        setHeight(dialog,height, event);
        positionDialog(dialog, event, function () {

            if(dialog.attr("data-parent-window")){
                setSublayerDim({id: dialog.attr("data-parent-window")},id,orientation);
            } else {
                setSublayerDim(undefined,id,orientation);
            }

            if(isFunction(callback)) {
                callback();
            }

            if($(window).width() <= 768) {

                editor_body.find(">div").each(function () {

                    if ($(this).css("min-width") != "none" && $(this).css("min-width") != "undefined") {
                        $(this).data("default-min-width",$(this).css("min-width"));
                        $(this).css("min-width", dialog.css("max-width"));
                    }

                });

            } else {

                editor_body.find(">div").each(function () {
                    if (isDefined($(this).data("default-min-width"))) {
                        $(this).css("min-width", $(this).data("default-min-width"));
                    }
                });

            }

        });

        // todo: Dodělat, že když dialog má body menší než container, nebude lišta, jinak se zobrazí scroll lišta
        /*
        if(getContainerHeight(body) <= getContainerHeight(editor_body)){
            body.css({"overflow-y": "hidden"});
        } else {
            body.css({"overflow-y": "scroll"});
        }
        */

    }

    this.refresh = function(window, orientation, callback){
        refresh(window, orientation, callback);
    };

    this.changeDetect = function(){
        options.changeFound = true;
    };

    this.changesDetectEnabled = function(){
        return options.changesDetect;
    };

    this.changesDetected = function(){
        return options.changeFound;
    };

    function customizeMobile() {

        if($(window).width() < 700) {

            options.defaults.draggable = options.mobile.draggable;
            options.defaults.dialog = options.mobile.dialog;
            options.defaults.centered = options.mobile.centered;
            options.defaults.fixed = options.mobile.fixed;

        } else {

            options.defaults.draggable = options.normal.draggable;
            options.defaults.dialog = options.normal.dialog;
            options.defaults.centered = options.normal.centered;
            options.defaults.fixed = options.normal.fixed;

        }
    }

    this.customizeMobile = function(){
        customizeMobile();
    };

    this.getHelp = function(){
        return options.help;
    };

    this.update = function(dialog, content){
        var body = moveToBody(dialog);
        body.html(content);
    };

    this.getButtonPointer = function(window){
        return(buttonsList[ID]);
    };
    
    this.remove = function(button, effect) {

        var container = moveToContainer(button);

        if(container != false) {

            if (isFunction(options.beforeClose)) {
                options.beforeClose();
            }

            var containerID = getDialogElemId(container);

            if(isFunction(options.close)){
                options.close(container);
            } else {

                if (container.find("resizeSenzor" + containerID + "").length > 0) {
                    ResizeSensor.detach(container.find("resizeSenzor" + containerID + ""));
                }

                container.find(".mainWindowContainer").each(function () {
                    var instance = $(this).data("instance");
                    instance.remove($(this));
                });

                var dialog = options.defaults.dialog;

                if (dialog) {
                    container.closest(".mainWindowTransparentBackground").remove();
                } else {

                    if (isFunction(effect)) {
                        options.effect(container);
                    } else {
                        container.remove();
                    }

                }

                delete buttonsList[ID];

                if (isFunction(options.afterClose)) {
                    options.afterClose();
                }
            }

        }
    };

    function isWindow(el){

        if(isObject(el)) {
            if (el.attr("class") == "mainWindowContainer") {
                return true;
            }
        }
        return false;
    }

    this.isWindow = function(el){
        return isWindow(el);
    };

    this.build = function (callback) {

        setOptions(); // set defaults
        parseOptions(options, new_options);

        var ID_full = "Dialog-" + ID;
        dialogIDCounter++;

        var parentWindowID = "";

        if (isDefined(options.buttonPointer)) {

            if(!isObject(options.buttonPointer)){
                options.buttonPointer = $(document).find(options.buttonPointer);
            }

            if(inArray(options.buttonPointer)){
                callback(false); console.log("Dialog is still opened by this button!"); return;
            }

            buttonsList[ID] = options.buttonPointer;

            var parentWindow = moveToContainer(options.buttonPointer);

            if (parentWindow != false) {
                parentWindowID = parentWindow.attr("id");
            }

        }

        var closeV = {
            0: '',
            1: ''
        };

        if(options.close) {

            closeV = {
                0: '<div class="mainWindowBarClose ' + options.classes.close + '"><img src="' + PATHS.packages + 'dialog/close/close.png" title="Close" alt="Close"/></div>',
                1: '<div class="mainWindowBarClose mainWindowBarCloseDynamic ' + options.classes.close + '"><img src="' + PATHS.icons + 'dialog/close/close.png" title="Close" alt="Close"/></div>'
            };
        }

        var bar = "";
        var close = "";

        if(isObject(options.bar)){

            if(options.close){
                close = closeV[0];
            }

            bar += '<div class="mainWindowBar '+options.classes.bar+'"><div class="mainWindowBarTitle '+options.classes.title+'" style="text-align: center;">'+((isDefined(options.bar.title) && options.bar.title != "") ? options.bar.title : options.bar.title) + '</div>';
            bar += close;
            bar += '</div>';

            if (isDefined(options.bar.options) && options.bar.options != '' && options.bar.options != false) {
                bar += '<div class="mainWindowEditorMenu">' + options.bar.options + '</div>';
            }

        } else {

            if(options.close){
                close = closeV[1];
            }

            bar = close;
        }

        customizeMobile();

        var data = (options.defaults.dialog ? '<div id="mainWindowTransparentBackground' + ID_full + '" class="mainWindowTransparentBackground">' : "") +
            '<div class="mainWindowContainer '+options.classes.container+'" id="' + ID_full + '" data-parent-window="' + parentWindowID + '">' +
            bar +
            '<div id="resizeSenzor' + ID_full + '"><div class="mainWindowBody '+options.classes.body+'"><div class="mainWindowEditorBody '+options.classes.content+'">' + options.content + '</div></div></div>' +
            '</div>' +
            (options.defaults.dialog ? '</div>' : '');

        if (parentWindowID != "" && options.insertIntoParent && !options.defaults.dialog) {

            moveToBody($("#" + parentWindowID + "")).append(data);
            setSublayerDim({id: parentWindowID}, ID_full);

        } else {

            $("body").append(data);
            setSublayerDim(undefined, ID_full);
        }

        var container = $(document).find('#' + ID_full + '');

        if (isDefined(options.width) && options.width != "") {
            container.attr("data-width-property", options.width);
        }
        if (isDefined(options.height) && options.height != "") {
            container.attr("data-height-property", options.width);
        }

        container.find(".mainWindowBody").addClass("scrollbar-inner");

        refresh(container, undefined, function () {

            if (isFunction(options.effect)) {
                options.effect(container);
            } else {
                container.css({display: "inline-block"});
            }

            moveToBody(container).parent().on("scroll", function () {
                $(this).find(".mainWindowContainer").each(function () {
                    refresh($(this));
                });
            });

            if (isFunction(callback)) {
                callback(container, options);
            }

        });
    };
};

var BasicDialog = function (options, callback) {

    var dialogInstance = new AbstractDialog(options);

    dialogInstance.build(function (dialog, options) {

        if(dialog != false) {

            if(!options.defaults.dialog && options.defaults.draggable){

                var disable = [];

                if(options.close) {
                    disable.push(dialog.find("#mainWindowBarClose"));
                }

                disable.push(dialog.find("#mainWindowBody"));

                if(isDefined(options.bar.options)) {
                    disable.push(dialog.find("#mainWindowEditorMenu"));
                }

                if(options.bar.help != false) {
                    disable.push(dialog.find("#mainWindowBarHelp"));
                }

                disable.push(dialog.find(".mainWindowEditorBody"));

                dialog.drags("", disable);

            }

            dialog.data("instance", dialogInstance);

            new ResizeSensor($("#resizeSenzor" + dialog.attr("id") + ""), function (el) {
                if(isDefined(el)) {

                    var inst = new AbstractDialog();
                    el = inst.moveToContainer(el);

                    var instance = el.data("instance");
                    instance.refresh(el,undefined);

                }
            });

            if(isFunction(callback)) {
                callback(dialog);
            }

        } else {
            if(isFunction(callback)) {
                callback(false);
            }
        }

    });

};

window.BasicDialog = BasicDialog;

var AlertDialog = function (alert_options, callback) {

    if(isDefined(alert_options.type) && alert_options.type == "message"){

        alert_options.normal = {
            centered: true
        };
        alert_options.close = true;

        if($(document).find("#mainMessagesContainer").length == 0){
            var data = '<div id="mainMessagesContainer"></div>';
            $("body").append(data);
        }

        var parent = "";
        var $icon = '<img src="';

        if(alert_options.content.icon == "success"){
            $icon += PATHS.icons + 'validation/right.png';
        } else {
            $icon += PATHS.icons + 'validation/wrong.png';
        }

        $icon += '" width="25px"/>';

        var messages = $(document).find("#mainMessagesContainer");
        var $message = $('<div data-parent-window="'+parent+'"><div class="message flexElem valignCenter"><div style="padding-right: 10px;">'+$icon+'</div><div class="flex">'+alert_options.content.data+'</div></div></div>');

        messages.append($message.fadeIn(500));

        setTimeout(function(){

            $message.fadeOut(3000, function(){
                $message.remove();
            });

        },5000);

        if (isFunction(callback)) {
            callback(true);
        }

    } else {

        alert_options.normal = {
            centered: true
        };
        alert_options.close = true;
        alert_options.content = '<br /><div style="text-align: center;">'+alert_options.content+'</div><br />';

        var dialogInstance = new AbstractDialog(alert_options);

        dialogInstance.build(function (dialog, options) {

            dialog.data("instance", dialogInstance);

            if (container.attr("data-parent-window")) {
                var parent = $(document).find("#" + container.attr("data-parent-window") + "");
            }

            $(document).on("click", "#" + container.attr("id") + " .mainWindowBarObjSvg", function (e) {

                if (isBoolean(obj.closeParent)) {
                    if (options.closeParent && isObject(parent)) {
                        self.remove(parent);
                    }
                }

                if (isFunction(callback)) {
                    callback(true);
                }

            });

            var ContWidth = container.width();
            container.find("#mainWindowBarTitle").css({"width": (ContWidth - 55)});

        });

    }

};

var ConfirmDialog = function (buttonPointer, title, event, callback) {

    var properties = {
        bar: {
            title: 'Hlášení programu'
        },
        buttonPointer: buttonPointer,
        normal: {
            centered: true,
            dialog: true
        },
        event: event,
        close: false,
        content: '<div style="text-align: center;">' + title + '<br /><br /><a id="mainWindowYes">Ano</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a id="mainWindowNo">Ne</a></div>'
    };

    var dialogInstance = new AbstractDialog(properties);

    dialogInstance.build(function (dialog, options) {

        if(isObject(options)) {

            var formData;

            if(isDefined(buttonPointer)) {

                if (buttonPointer.prop("tagName") == "INPUT" || buttonPointer.prop("tagName") == "BUTTON") {

                    var form = $(buttonPointer).closest("form");

                    form.submit(function (e) {
                        formData = new FormData(this);
                        e.preventDefault();
                        event.preventDefault();
                    });

                } else {
                    event.preventDefault();
                }

            }

            if (dialog != false) {

                if(isDefined(event.target)){

                    if (isDefined(event) && (event.target.nodeName == "INPUT" || event.target.nodeName == "BUTTON")) {

                        $("#" + dialog.attr("id") + " #mainWindowYes").click(function (e) {
                            e.preventDefault();

                            if (e.handled !== true) {
                                dialogInstance.remove($(this));
                                e.handled = true;
                                callback(formData);
                            }

                        });

                        $("#" + dialog.attr("id") + " #mainWindowNo").click(function (e) {
                            e.preventDefault();

                            if (e.handled !== true) {
                                dialogInstance.remove($(this));
                                e.handled = true;
                                callback(undefined);
                            }

                        });

                    } else {

                        $("#" + dialog.attr("id") + " #mainWindowYes").click(function (e) {
                            e.preventDefault();

                            if (e.handled !== true) {
                                dialogInstance.remove($(this));
                                e.handled = true;
                                callback(true);
                            }

                        });

                        $("#" + dialog.attr("id") + " #mainWindowNo").click(function (e) {
                            e.preventDefault();

                            if (e.handled !== true) {
                                dialogInstance.remove($(this));
                                e.handled = true;
                                callback(false);
                            }

                        });

                    }

                } else {

                    $("#" + dialog.attr("id") + " #mainWindowYes").click(function (e) {
                        e.preventDefault();

                        if (e.handled !== true) {
                            dialogInstance.remove($(this));
                            e.handled = true;
                            callback(true);
                        }

                    });

                    $("#" + dialog.attr("id") + " #mainWindowNo").click(function (e) {
                        e.preventDefault();

                        if (e.handled !== true) {
                            dialogInstance.remove($(this));
                            e.handled = true;
                            callback(false);
                        }

                    });

                }
            }
        }
    });
};


$(document).ready(function() {

    $(document).on("click", ".dialog-handler", function (e) {

        var btn = $(this);

        var handler_options = {

            content: "",
            dialogType: "basic",
            ajax: {
                process: false,
                method: "GET"
            },
            applyAfterClicks: 1,
            isSnippet: true,
            dropPasteData: false,
            snippetName: ""

        };

        var set_options = btn.data("dialog-options");
        parseOptions(handler_options, set_options);
        var clicks = 1;

        if(!isDefined(btn.data("really-clicks"))){
            btn.data("really-clicks", 1);
        } else {
            clicks = btn.data("really-clicks") + 1;
            btn.data("really-clicks", clicks);
        }

        if(clicks == handler_options.applyAfterClicks) {

            if (btn.prop("tagName") == "A") {
                e.preventDefault();
            }

            set_options = Object.assign({}, set_options);

            set_options.content = "";
            var expr = /[#.]/;

            if (handler_options.isSnippet) {
                if (expr.test(handler_options.content)) {

                    if(handler_options.dropPasteData){
                        set_options.content = $(document).find(handler_options.content).contents();
                    } else {
                        set_options.content = $(document).find(handler_options.content).html();
                    }

                }
            } else {

                console.log(handler_options.content);

                if(handler_options.dropPasteData){
                    set_options.content = $(document).find(handler_options.content).contents();
                } else {
                    set_options.content = $(document).find(handler_options.content).html();
                }

            }

            set_options.buttonPointer = btn;
            set_options.event = e;

            if(handler_options.dropPasteData){
                set_options.beforeClose = function () {
                    $(handler_options.content).append(set_options.content);
                };
            }

            switch (handler_options.dialogType) {

                case "basic":

                    if (handler_options.ajax.process) {

                        var progress = new Progress();

                        new BasicDialog(set_options, function (obj) {

                            var instance = obj.data("instance");
                            progress.insertInto(instance.moveToBodyy(obj));

                            $.nette.ajax({
                                url: btn.attr("href"),
                                type: handler_options.ajax.method,
                                off: {
                                    snippets: true
                                }
                            }).done(function (payload) {

                                if (payload["completed"] == 1) {

                                    if (handler_options.isSnippet) {

                                        if(handler_options.snippetName != ""){

                                            $.each(payload.snippets, function (i,v) {

                                                if(i.indexOf(handler_options.snippetName) !== -1){
                                                    instance.update(obj, v);
                                                }

                                            });

                                        } else {

                                            if (expr.test(handler_options.content)) {
                                                set_options.content = $(document).find(handler_options.content).contents();
                                            }

                                            instance.moveToBodyy(obj).html("");
                                            set_options.content.appendTo(instance.moveToBodyy(obj));
                                        }

                                    } else {
                                        instance.update(obj, set_options.content);
                                    }

                                    if(isObject(obj)) {
                                        progress.progressBar(obj.find(".freeDeliveryBar"));
                                    }

                                }

                            });

                        });

                    } else {

                        new BasicDialog(set_options, function (obj) {

                            var instance = obj.data("instance");

                            if (handler_options.isSnippet) {

                                instance.moveToBodyy(obj).html("");

                                if (expr.test(handler_options.content)) {
                                    set_options.content = $(document).find(handler_options.content).contents();
                                }

                                set_options.content.appendTo(instance.moveToBodyy(obj));

                            } else if(handler_options.dropPasteData){

                                instance.moveToBodyy(obj).html("");
                                set_options.content.appendTo(instance.moveToBodyy(obj));

                            } else {
                                instance.update(obj, set_options.content);
                            }

                        });
                    }

                    break;
                case "alert":

                    if (handler_options.ajax.process) {

                        $.nette.ajax({
                            url: btn.attr("href"),
                            type: handler_options.method,
                            off: {
                                snippets: true
                            }
                        }).done(function (payload) {

                            if (payload["completed"] == 1) {

                                if (expr.test(handler_options.content)) {
                                    set_options.content = $(document).find(handler_options.content).contents();
                                }

                                new AlertDialog(set_options);

                            }
                        });

                    } else {
                        new AlertDialog(set_options);
                    }

                    break;
                case "confirm":

                    new ConfirmDialog(btn, set_options.content, e, function (result) {

                        if (result) {
                            /* todo: male ajax call */
                        }

                    });

                    break;
                default:
                    break;

            }

            btn.data("really-clicks", 0);
        }

    });


    $(document).on("click", ".mainWindowContainer .mainWindowBarClose, .mainWindowContainer .dialog-close-handler", function (e) {

        var btn = $(this);

        var dialog = btn.closest(".mainWindowContainer");
        var instance = dialog.data("instance");

        if (instance.changesDetectEnabled()) {

            if (instance.changesDetected()) {

                new ConfirmDialog(btn, "Byly detekovány změny polí, opravdu chcete zavřít okno?", e, function (result) {
                    if (result) {
                        instance.remove(btn);
                    }
                });

            } else {
                instance.remove(btn);
            }
        } else {
            instance.remove(btn);
        }

    });

    $(window).on("orientationchange", function (event) {

        var orientation = this.orientation;

        $(document).find(".mainWindowContainer").each(function () {

            var instance = $(this).data("instance");
            instance.refresh($(this), orientation);

        });

    });

    /* todo: Click help button show pdf with info or new dialog with info
    /*
    $(document).on("click", ".mainWindowBarHelp", function (e) {

        mainWindow.normal({

            bar: {
                title: "Nápověda"
            },
            title: "Nápověda",
            event: e,
            content: mainWindow.getHelp($(this)),
            buttonPointer: $(this),
            insertIntoParent: false,
            dialog: false
        });

    });
    */


    $(window).on('resize', function () {

        var orientation = this.orientation;

        $(document).find(".mainWindowContainer").each(function () {

            var instance = $(this).data("instance");

            instance.refresh($(this), orientation);
            instance.customizeMobile();
        });

    });

    $(window).on("scroll", function () {

        var orientation = this.orientation;

        $(document).find(".mainWindowContainer").each(function () {

            var instance = $(this).data("instance");
            instance.refresh($(this), orientation);

        });

    });

    /* Input fields changes detector */

     $(document).on("change","input,select", function(e){

         var el = $(this);

         if(el.closest(".mainWindowContainer").length){
             var instance = el.closest(".mainWindowContainer").data("instance");

             if(instance.changesDetectEnabled()) {
                 instance.changeDetect();
             }
         }

     });

    /*
    $(document).on("keyup", "textarea", function (e) {

        var el = $(this);

        if (el.closest(".mainWindowContainer").length) {
            var instance = el.closest(".mainWindowContainer").data("instance");

            if(instance.changesDetectEnabled()) {
                instance.changeDetect();
            }
        }

    });

    */

    /*
    $(document).on("keyup,click", "input[type='number']", function (e) {

        var el = $(this);

        if (mainWindow.isWindow(mainWindow.moveToContainer(el))) {
            mainWindow.changeDetect(mainWindow.getDialogElemId(mainWindow.moveToContainer(el)));
        }

    });
    */

});
