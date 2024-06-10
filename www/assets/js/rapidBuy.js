var target = $(document).find("#rapidBuyDialog");

target.on("change", "#countRapid, #frm-rapidBuy-rapidBuyForm-payMethod, #frm-rapidBuy-rapidBuyForm-deliveryMethod", function () {
    recountRapidBasket();
    handleDPDPickup($(this));
    handleUlozenkaPickup($(this));
});

target.on("ifChanged", "#isPojisteni, #isInstall", function () {
    recountRapidBasket();
    handleDPDPickup($(this));
    handleUlozenkaPickup($(this));
});

function handleDPDPickup(input) {
    // Skrývání a odkrývání dpd pickapu
    if (input.attr('id') == 'frm-rapidBuy-rapidBuyForm-deliveryMethod') {
        if (input.val() == getDPDMethodId()) {
            target.find('#dpdPickup').show();
            // $(document).find('.selectpicker').select2("destroy");

            $(document).find('.selectpicker').select2();


            $(document).find('.select2-container').css("z-index:999999");


        } else {
            target.find('#dpdPickup').hide();
        }
    }
}

function handleUlozenkaPickup(input) {
    // Skrývání a odkrývání ulozenka pickapu
    if (input.attr('id') == 'frm-rapidBuy-rapidBuyForm-deliveryMethod') {
        if (input.val() == getUlozenkaMethodId()) {
            target.find('#ulozenkaPickup').show();
            // $(document).find('.selectpicker').select2("destroy");

            $(document).find('.selectpicker').select2();


            $(document).find('.select2-container').css("z-index:999999");


        } else {
            target.find('#ulozenkaPickup').hide();
        }
    }
}

function recountRapidBasket() {
    var idPay = target.find("#frm-rapidBuy-rapidBuyForm-payMethod").val();
    var idDel = target.find("#frm-rapidBuy-rapidBuyForm-deliveryMethod").val();
    var count = target.find("#countRapid").val();
    var priceDph = target.find("#price_with_dph").val();

    var pojisteni = target.find("#isPojisteni");
    var isInstall = target.find("#isInstall");

    var result = target.find("#rapid_price_count");

    var pay = getPayPrice(idPay);
    var del = getDeliveryPrice(idDel);
    var price = count * priceDph + pay + del;

    if (pojisteni.length) {
        if (pojisteni.iCheck('update')[0].checked) {
            console.log("Is checkedd");
            price += count * pojisteni.val();
        }
    }

    if (isInstall.length) {
        if (isInstall.iCheck('update')[0].checked) {
            price += count * isInstall.val();
        }
    }
    result.text(number_format(price, 2, ',', ' '));


}

$(document).ready(function () {

    recountRapidBasket();

});