
// JavaScript Document
CKEDITOR.dtd.$editable.span = 1
CKEDITOR.dtd.$editable.a = 1
CKEDITOR.disableAutoInline = true;
$(document).ready(function () {
    $("[contenteditable='true']").each(function () {
        $(this).hover(function (e) {
            if ($(this).attr("back") === "black") {
                $(this).css("background-color", e.type === "mouseenter" ? "black" : "transparent");
                $(this).css("color", e.type === "mouseenter" ? "white" : "");
            } else {
                $(this).css("background-color", e.type === "mouseenter" ? "lightblue" : "transparent");
            }
            ;
        });

        var content_id = $(this).attr('id');
        var page_id = $(this).attr('page');
        CKEDITOR.inline(content_id, {
            on: {
                blur: function (event) {
                    var request = $.nette.ajax({
                        url: "?do=saveInline",
                        type: "POST",
                        data: {
                            content: event.editor.getData(),
                            content_id: content_id,
                            page_id: page_id

                        }
                    });
                }}
        });
    });
});
