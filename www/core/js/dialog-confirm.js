$(document).ready(function () {
    $("#dialog-confirm").dialog({
        autoOpen: false,
        modal: false
    });
});

$(".confirmLink").click(function (e) {
    e.preventDefault();
    var targetUrl = $(this).attr("href");

    $("button[name=ok]").click(function (e) {
        window.location.href = targetUrl;
    });
    $("button[name=close]").click(function (e) {
        $("#dialog-confirm").dialog('close');
    });
    $("#dialog-confirm").dialog("open");
});

(function ($, undefined) {
    $.nette.ext({
        load: function () {
            $('[data-confirm]').click(function (event) {
                var obj = this;
                event.preventDefault();
                event.stopImmediatePropagation();
                var oldText = $("#modal-text-p").html();
                var oldTitle = $("#modal-text-p").html();

                if ($(obj).data('text')) {
                    $("#modal-text-p").html($(obj).data('text'));
                }

                if ($(obj).data('title')) {
                    $("#modal-title-h4").html($(obj).data('title'));
                }

                $("button[name=close]").click(function (e) {
                    obj = null;
                    $("#modal-text-p").html(oldText);
                    $("#modal-text-p").html(oldTitle);
                    $("#dialog-confirm").dialog('close');
                });

                $('button[name=ok]').on('click', function () {
                    if (obj !== null) {
                        var tagName = $(obj).prop("tagName");
                        if (tagName == 'INPUT') {
                            var form = $(obj).closest('form');
                            form.submit();
                        } else {
                            if ($(obj).data('ajax') == 'on') {
                                $.nette.ajax({
                                    url: obj.href
                                });
                            } else {
                                document.location = obj.href;
                            }
                        }
                    }
                    $("#modal-text-p").html(oldText);
                    $("#modal-text-p").html(oldTitle);
                    $("#dialog-confirm").dialog('close');
                });
                $('#dialog-confirm').on('hidden', function () {
                    obj = null;
                    $("#modal-text-p").html(oldText);
                    $("#modal-text-p").html(oldTitle);
                    $('#dialog-confirm').remove();
                });
                $("#dialog-confirm").dialog("open");
                return false;
            });
        }
    });

})(jQuery);