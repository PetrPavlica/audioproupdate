$(document).on("change", '.ajax-submit-row', function () {
    var formData = new FormData();

    $(this).closest('tr').find("input, select, textarea").not(":submit, :button").each(function () {
        formData.append($(this).attr("name").replace("[]", ""), $(this).val());
    });

    $.ajax({
        url: $(this).attr("data-path"),
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,
        cache: false,
        /*error: function (jqXHR, textStatus, errorThrown) {
         //alert(errorThrown);
         }*/
    });
});
