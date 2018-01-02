var tempJavaScriptPreload;
var tempCssPreload;
var tempAsyncJS;
var tempCallbackFunction;
var onloadCount = 0;
var tempCssLoadStatus = [];

function loadCss(t) {
    if (typeof(tempCssPreload) != 'undefined') {
        var n = function () {
            if (++onloadCount != 2) return;
            var o = document.createElement('link');
            o.rel = 'stylesheet';
            var e = [], i = document.getElementsByTagName('head')[0], n, a;
            for (var t in tempCssPreload) {
                if (tempCssPreload[t] != '') {
                    e[t] = o.cloneNode();
                    e[t].href = tempCssPreload[t];
                    if (typeof(n) == 'undefined' || typeof(n) == 'cssRules') {
                        if ('sheet' in e[t]) {
                            n = 'sheet';
                            a = 'cssRules'
                        }
                        else {
                            n = 'styleSheet';
                            a = 'rules'
                        }
                    }
                    ;i.appendChild(e[t]);
                    tempCssLoadStatus[t] = !1
                }
            }
            ;var d = setInterval(function () {
                try {
                    for (var t in e) {
                        if (e[t][n] && e[t][n][a].length) {
                            tempCssLoadStatus[t] = !0
                        }
                    }
                    ;var o = !0;
                    for (var s in tempCssLoadStatus) {
                        if (tempCssLoadStatus[t] == !1) {
                            o = !1
                        }
                    }
                    ;
                    if (o == !0) {
                        clearInterval(d);
                        clearTimeout(timeout_preload);
                        loadJS()
                    }
                } catch (i) {
                } finally {
                }
            }, 50);
            timeout_preload = setTimeout(function () {
                clearInterval(d);
                clearTimeout(timeout_preload);
                for (var t in e) {
                    i.removeChild(e[t])
                }
                ;loadJS()
            }, 15000)
        }, e;
        if (typeof(requestAnimationFrame) != 'undefined') {
            e = requestAnimationFrame
        }
        else if (typeof(mozRequestAnimationFrame) != 'undefined') {
            e = mozRequestAnimationFrame
        }
        else if (typeof(webkitRequestAnimationFrame) != 'undefined') {
            e = webkitRequestAnimationFrame
        }
        else if (typeof(msRequestAnimationFrame) != 'undefined') {
            e = msRequestAnimationFrame
        }
        ;
        if (e) {
            e(function () {
                n()
            })
        }
        else {
            onloadCount++
        }
        ;
        if (typeof(window.onload) == 'function') {
            var a = window.onload;
            window.onload = function () {
                a();
                n()
            }
        }
        else {
            window.onload = function () {
                n()
            }
        }
    }
    else {
        loadJS()
    }
};

function initFileload(e, n, t) {
    var o = e;
    if (typeof(t) != 'undefined') {
        if (typeof(t.callback) != 'undefined') {
            tempCallbackFunction = t.callback
        }
    }
    ;tempCssPreload = e;
    tempJavaScriptPreload = n;
    var a = document.getElementsByTagName('body');
    if (a.length > 0) {
        loadCss(t)
    }
};

function loadJS(e) {
    if (typeof(tempJavaScriptPreload) != 'undefined' && tempJavaScriptPreload.length > 0) {
        if (e == null) {
            e = 0
        }
        ;
        if (typeof(tempJavaScriptPreload[e]) != 'undefined') {
            if (tempJavaScriptPreload[e] != '') {
                var t = document.createElement('script');
                t.type = 'text/javascript';
                t.src = tempJavaScriptPreload[e];
                if (typeof(tempJavaScriptPreload[e + 1]) != 'undefined') {
                    t.onload = function () {
                        loadJS(e + 1)
                    }
                }
                else {
                    t.onload = function () {
                        loadAsyncJS()
                    }
                }
                ;document.body.appendChild(t)
            }
            else {
                if (typeof(tempJavaScriptPreload[e + 1]) != 'undefined') {
                    loadJS(e + 1)
                }
                else {
                    loadAsyncJS()
                }
            }
        }
    }
    else {
        loadAsyncJS()
    }
};

function initLoadAsyncJS(e) {
    if (typeof(tempAsyncJS) == 'undefined') {
        tempAsyncJS = e
    }
    else {
        for (var t in e) {
            tempAsyncJS.push(e.shift())
        }
    }
};

function loadAsyncJS(e) {
    if (typeof(tempAsyncJS) != 'undefined') {
        if (e == null) {
            e = 0
        }
        ;
        if (tempAsyncJS[e] != '') {
            var t = document.createElement('script');
            t.setAttribute('defer', 'defer');
            t.type = 'text/javascript';
            t.src = tempAsyncJS[e];
            if (typeof(tempAsyncJS[e + 1]) != 'undefined') {
                loadAsyncJS(e + 1)
            }
            else {
                runCallBack()
            }
            ;document.body.appendChild(t)
        }
        else {
            if (typeof(tempAsyncJS[e + 1]) != 'undefined') {
                loadAsyncJS(e + 1)
            }
            else {
                runCallBack()
            }
        }
    }
    else {
        runCallBack()
    }
};

function runCallBack() {
    if (typeof(tempCallbackFunction) != 'undefined') {
        tempCallbackFunction()
    }
};