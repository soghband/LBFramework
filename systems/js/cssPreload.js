var tempCSSList = [];
var preloadTimeout;
var tempCallback;
var onloadProcessed = false;
var cssLoadStatus = [];
function loadCss(cssList,callBack) {
    tempCallback = callBack;
    var splitCssList = cssList.split(",");
    tempCSSList = splitCssList;
    var loadProcess = function() {
        if (onloadProcessed == true) {
            return;
        }
        var linkTag = document.createElement('link');
        linkTag.rel = 'stylesheet';
        var linkTagArray = [];
        var headerTag = document.getElementsByTagName('head')[0];
        var sheet;
        var rules;
        for (var i in tempCSSList) {
            if (tempCSSList[i] != "") {
                linkTagArray[i] = linkTag.cloneNode();
                linkTagArray[i].href = tempCSSList[i];
                if (typeof(sheet) == 'undefined' || typeof(sheet) == 'cssRules') {
                    if ('sheet' in linkTagArray[i]) {
                        sheet = 'sheet';
                        rules = 'cssRules'
                    }
                    else {
                        sheet = 'styleSheet';
                        rules = 'rules'
                    }
                }
                headerTag.appendChild(linkTagArray[i]);
                cssLoadStatus[i] = false;
            }
            var cssLoadCheck = setInterval(function() {
                try {
                    for (var i in linkTagArray) {
                        if (linkTagArray[i][sheet] && linkTagArray[i][sheet][rules].length) {
                            cssLoadStatus[i] = true;
                        }
                    }
                } catch (error) {
                    console.log(error);
                }
                var allCssLoaded = true;
                for (var i in cssLoadStatus) {
                    if (cssLoadStatus[i] == false) {
                        allCssLoaded = false;
                    }
                }
                if (allCssLoaded == true) {
                    clearInterval(cssLoadCheck);
                    clearTimeout(preloadTimeout);
                    if (typeof(tempCallbackl) == "function") {
                        tempCallbackl();
                    }
                }
            },50);
            preloadTimeout = setTimeout(function() {
                var allCssLoaded = true;
                for (var i in cssLoadStatus) {
                    if (cssLoadStatus[i] == false) {
                        allCssLoaded = false;
                    }
                }
                if (allCssLoaded == false) {
                    console.log("CSS Preload Timeout");
                }
                clearInterval(cssLoadCheck);
                clearTimeout(preloadTimeout);
                for (var i in linkTagArray) {
                    headerTag.removeChild(linkTagArray[i]);
                }
                if (typeof(tempCallbackl) == "function") {
                    tempCallbackl();
                }
            },15000);
        }
    }
    var eventCapter ;
    if (typeof(requestAnimationFrame) != 'undefined') {
        eventCapter = requestAnimationFrame
    }
    else if (typeof(mozRequestAnimationFrame) != 'undefined') {
        eventCapter = mozRequestAnimationFrame
    }
    else if (typeof(webkitRequestAnimationFrame) != 'undefined') {
        eventCapter = webkitRequestAnimationFrame
    }
    else if (typeof(msRequestAnimationFrame) != 'undefined') {
        eventCapter = msRequestAnimationFrame
    }
    if (eventCapter) {
        eventCapter(function () {
            loadProcess();
            onloadProcessed = true;
        });
    }else {
        if (typeof(window.onload) == 'function') {
            var windowsOnLoadOld = window.onload;
            window.onload = function () {
                windowsOnLoadOld();
                loadProcess();
                onloadProcessed = true;
            }
        }else {
            window.onload = function () {
                loadProcess()
                onloadProcessed = true;
            }
        }
    }
}