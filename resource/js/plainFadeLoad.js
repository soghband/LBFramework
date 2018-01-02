function plainFade() {
    var t = '.hidebody {display: block;opacity: 0; }.showbody {opacity: 1;display: block;transition: opacity 0.5s ease-out;}',
        a = document.getElementsByTagName('head')[0], e = document.createElement('style');
    e.setAttribute('id', 'preloadCss');
    e.setAttribute('type', 'text/css');
    var n = navigator.userAgent;
    if (n.indexOf('MSIE 8') > -1) {
        e.styleSheet.cssText = t
    }
    else {
        e.innerHTML = t
    }
    ;a.appendChild(e);
    addClass(document.body, 'hidebody')
};

function fadeBodyIn() {
    addClass(document.body, 'loaded');
    setTimeout(function () {
        addClass(document.body, 'showbody');
        setTimeout(function () {
            removeClass(document.body, 'hidebody');
            removeClass(document.body, 'showbody')
        }, 500)
    }, 500)
};

function addClass(e, t) {
    var n = e.className;
    if (n.indexOf(t) != -1) {
        return
    }
    ;
    if (n != '') {
        t = ' ' + t
    }
    ;e.className = n + t
};
function removeClass(e, n) {
    var t = e.className, a = new RegExp('\\s?\\b' + n + '\\b', 'g');
    t = t.replace(a, '');
    e.className = t
};