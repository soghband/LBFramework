$(document).ready(function () {
        $(".functionLink").click(function() {
            var link = $(this).attr("href");
            var linkSplit = link.split("#");
            var targetId = linkSplit.pop();
            var linkUrl = linkSplit.shift();
            var targetElement = $("#"+targetId);
            if (targetElement.length > 0 && window.location.href.indexOf(linkUrl) > 0) {
                gotoElement(targetId);
                return false;
            } else {
                return true;
            }
        });
        $(".classLink").click(function() {
            var className = $(this).attr("linkClass");
            var url = "document/"+className;
            if ( window.location.href.indexOf(url) > 0) {
                if ($(this).next().attr("class") === "lap2" && $(this).next().css("display") === "none") {
                    $(".lap2").slideUp();
                    $(this).next().slideDown();
                } else {
                    $(this).next().slideUp();
                }
                return false;
            } else {
                if ($(this).next().attr("class") === "lap2" && $(this).next().css("display") === "none") {
                    $(".lap2").slideUp();
                    $(this).next().slideDown();
                    return false;
                } else {
                    return true;
                }
            }
        });
        setTimeout(function() {
            var currentUrl = window.location.href;
            if (currentUrl.indexOf("#") > 0) {
                var urlSlpitCharp = currentUrl.split("#");
                var targetId = urlSlpitCharp.pop();
                gotoElement(targetId);
            }
            $(".classLink").each(function() {
                var checkUrl = "document/"+$(this).attr("linkclass");
                if (currentUrl.indexOf(checkUrl) > 0) {
                    $(this).next().slideDown();
                }
            });
        },500);
    }
);
function gotoElement(elementId) {
    var targetElement = $("#"+elementId);
    $('html, body').animate({
        scrollTop: targetElement.offset().top
    }, 500);
}