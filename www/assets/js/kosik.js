function findBasketItem(tr, item) {
    var id = tr.attr("id");
    return tr.closest("table").find("tr#" + id + "").find(item);
}

function recalculateSum() {
    var doprava_input = $(document).find("#cena_doprava_input");
    var doprava_value = $(document).find("#cena_doprava_value");

    var doprava = 0;
    var platba = 0;

    doprava = parseFloat($("#doprava_platba #doprava").find("input.typ_dopravy:checked").closest("td").find("input[type=hidden]").val());
    platba = parseFloat($("#doprava_platba #platba").find("input.typ_platby:checked").closest("td").find("input[type=hidden]").val());

    var celk_dopr_plat = doprava + platba;
    celk_dopr_plat = zaokrouhli(celk_dopr_plat, 2);

    var sleva_percent = parseFloat($(document).find("#cena_sleva_percent").val());
    var sleva_input = $(document).find("#cena_sleva_vyse_input");

    var dopravaSleva = ((sleva_percent / 100) * celk_dopr_plat);

    doprava_input.val(celk_dopr_plat);
    doprava_value.text(number_format(celk_dopr_plat, 2, ',', ' '));

    var cenaSDPHVal = parseFloat($(document).find("#cena_zbozi_s_dph").val());
    var vyseSlevy = parseFloat(sleva_input.val());
    var kurzMeny = 1;//parseFloat($(document).find("#kurz_meny").val());

    if (isNaN(vyseSlevy))
        vyseSlevy = 0;
    if (isNaN(dopravaSleva))
        dopravaSleva = 0;

    var celkem = (cenaSDPHVal / kurzMeny) + (vyseSlevy) + celk_dopr_plat - dopravaSleva;
    celkem = zaokrouhli(celkem, 2);
    console.log(celkem);

    var celkem_input = $(document).find("#cena_celkem_s_dph_input");
    var celkem_value = $(document).find("#cena_celkem_s_dph_value");

    celkem_input.val(celkem);
    celkem_value.text(number_format(celkem, 2, ',', ' '));
}

$(document).on("ifChanged", "#doprava_platba #doprava .icheck, #doprava_platba #platba .icheck", function (e) {
    if ($(this).is(":checked")) {
        recalculateSum();
    }
});

$(document).on("ifChanged", ".pojisteni_check", function (e) {
    manageChecksPojisteni();
});

function manageChecksPojisteni() {
    $(document).find(".pojisteni_check").each(function () {
        var check = $(this);
        var tr = check.closest('tr');
        if (check.is(':checked')) {
            tr.find('.forShow').show();
        }
        else {
            tr.find('.forShow').hide();
        }
    })
}


/* zmena poctu produktu */

$(document).on("change", ".product .details input.amount", function () {

    var tr = $(this).closest("tr");
    var amount = $(this).val();
    var countDecimal = $(document).find("#countDecimal").val();

    var cena_1_ks = findBasketItem(tr, ".cena_s_dph_input");
    var cena_all = findBasketItem(tr, ".cena_s_dph_sum_value");

    var cena_ks = parseFloat(cena_1_ks.val()) * amount;

    cena_all.text(number_format(cena_ks, countDecimal, ',', ' '));

    // pojištění
    tr = tr.next('tr');
    var pojisteni_1ks = findBasketItem(tr, ".pojisteni_cena_s_dph_input");
    var pojisteni_all = findBasketItem(tr, ".pojisteni_cena_s_dph_value");
    var pojisteni_amount = findBasketItem(tr, ".amountP");

    var pojisteni_cena = parseFloat(pojisteni_1ks.val()) * amount;
    pojisteni_all.text(number_format(pojisteni_cena, countDecimal, ',', ' '));
    pojisteni_amount.text(amount);

    // instalace
    tr = tr.next('tr');
    var instalace_1ks = findBasketItem(tr, ".instalace_cena_s_dph_input");
    var instalace_all = findBasketItem(tr, ".instalace_cena_s_dph_value");
    var instalace_amount = findBasketItem(tr, ".amountP");

    var instalace_cena = parseFloat(instalace_1ks.val()) * amount;
    instalace_all.text(number_format(instalace_cena, countDecimal, ',', ' '));
    instalace_amount.text(amount);

});


$(document).ready(function () {
    $(document).find('.selectpicker').select2();
    recalculateSum();
    manageChecksPojisteni();
});

$(document).ajaxComplete(function () {
    manageChecksPojisteni();
});

function zaokrouhli(cislo, pocet_destinych_mist) {
    var mocnina = 10;
    for (var i = 1; i < pocet_destinych_mist; i++) {
        mocnina = mocnina * 10;
    }
    cislo = cislo * mocnina;
    cislo = Math.round(cislo);
    cislo = cislo / mocnina;
    return cislo;
}