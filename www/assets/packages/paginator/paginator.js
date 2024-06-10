var dataPagerLastW = 0;
function dataPagerCheck(pager) {
    function Check(elem) {
        var all = elem.find(".dataPagerBody .dataPagerItem");
        var visible = all.filter(":visible");
        var count = visible.length - 1;
        var itemWidth = visible.first().outerWidth(true) + parseInt(visible.first().css("margin-left"));
        var parentWidth = pager.width() - 20;
        var arrows = elem.find(".dataPagerArrow");
        var arrowVisible = elem.find(".dataPagerArrow:visible");
        var proprt = eval('(' + arrows.first().attr("data-properties") + ')');
        var proprt2 = eval('(' + arrows.last().attr("data-properties") + ')');
        if ((parentWidth > dataPagerLastW) && (dataPagerLastW > 0)) {
            console.log("here");
            var direction = "right";
            var pom = visible.last().index();
            while (((itemWidth * (count + 1)) + (arrowVisible.outerWidth(true) * 2)) < parentWidth) {
                if (count == proprt.visItPerSlideOrig) {
                    break;
                }
                if (pom == all.last().index()) {
                    direction = "left";
                    pom = visible.first().index();
                }
                if (direction == "right") {
                    count++;
                    pom++;
                    all.eq(pom).fadeIn();
                } else {
                    count++;
                    pom--;
                    all.eq(pom).fadeIn();
                }
            }
        } else {
            var allItemsWidth = itemWidth * (count + 1);
            while (allItemsWidth > parentWidth) {
                visible.eq(count).fadeOut();
                count--;
                allItemsWidth = (itemWidth * (count + 1)) + (arrowVisible.first().outerWidth(true) * 2);
            }
        }
        proprt.visItPerSlide = count;
        proprt2.visItPerSlide = count;
        arrows.first().attr("data-properties", JSON.stringify(proprt));
        arrows.last().attr("data-properties", JSON.stringify(proprt2));
        dataPagerLastW = parentWidth;
    }

    if ($(window).width() != dataPagerLastW) {
        if (isDefined(pager)) {
            Check(pager);
        } else {
            $(document).find(".dataPager").each(function () {
                Check($(this));
            });
        }
    }
}

function preparePaginators() {
    $(document).find(".dataPager").each(function () {

        if(isDefined($(this).data("build"))){
            if(!$(this).data("build")){
                return;
            }
        }

        if ($(this).data("handled") != true) {
            $(this).data("handled", true);
            if ($(window).width() <= 600) {
                $(this).css({width: ($(window).width())});
            }
            $(this).css({width: $(this).parent().width() * 0.8});
            dataPagerCheck($(this));
            $(this).show();
        }


    });
}

window.preparePaginators = preparePaginators;

$(window).resize(function () {
    preparePaginators();
});
$(document).on("click", ".dataPagerArrow", function (e) {
    var properties = eval('(' + $(this).attr("data-properties") + ')');
    var direction = properties.direction;
    var arrow = $(this);
    var container = arrow.closest(".dataPager");
    var all = container.find(".dataPagerBody .dataPagerItem");
    var visible = all.filter(":visible");
    var left = visible.first().index();
    var right = visible.last().index();
    var count = all.length;
    var step = properties.visItPerSlide;
    if (direction == "right") {
        var mover = right;
        var last = right + step;
        mover++;
        for (mover; mover <= last; mover++) {
            if (mover > count - 1) {
                break;
            }
            all.eq(mover).fadeIn(0);
            if (left < right) {
                all.eq(left).fadeOut(0);
            }
            left++;
            if (mover == (count - 1)) {
                arrow.parent().hide();
                break;
            }
        }
        container.find(".dataPagerLeft").show();
    } else {
        var mover = left;
        var last = left - step;
        mover--;
        for (mover; mover >= last; mover--) {
            if (mover < 0) {
                break;
            }
            all.eq(mover).fadeIn(0);
            if (right > left) {
                all.eq(right).fadeOut(0);
            }
            right--;
            if (mover == 0) {
                arrow.parent().hide();
                break;
            }
        }
        container.find(".dataPagerRight").show();
    }
    dataPagerCheck(container);
});