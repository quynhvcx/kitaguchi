/*!
 * ScriptName: shared.js
 *
 * FoodConnection
 * http://foodconnection.jp/
 * http://foodconnection.vn/
 *
 */

var lastScrollTop = 0;

$(window).scroll(function() {
    var st = $(this).scrollTop();
    if (lastScrollTop != 0) {
        if (st < lastScrollTop) {
            $(".pagetop").addClass("visible");
            if (st < 10) {
                $(".pagetop").removeClass("visible");
            }
        } else if (st > lastScrollTop) {

            $(".pagetop").removeClass("visible");

        }
    }
    lastScrollTop = st;
});




$("body").on("click", ".pagetop", function() {
    if (!$(this).hasClass("in-scroll")) {
        $(this).addClass("in-scroll");

        var $scrollDuration = $.inArray($(this).attr("data-duration"), ["slow", "normal", "fast"]) >= 0 || parseInt($(this).attr("data-duration")) > 0 ? $(this).attr("data-duration") : "slow";

        $("html, body").stop().animate({
            scrollTop: 0
        }, $scrollDuration, function() {
            $(".pagetop").removeClass("in-scroll");
            $(".pagetop").removeClass("visible");
        });

    }
});
// END: scroll to top

$(document).ready(function() {
    $(window).scroll(function() {
        var TargetPos = $('section.block').offset().top - 90;
        var ScrollPos = $(window).scrollTop();
        if (ScrollPos > TargetPos) {
            $("body").addClass('has-nav');
        } else {
            $("body").removeClass('has-nav');
        }
    });
});

//fix scroll ios


// END: openning