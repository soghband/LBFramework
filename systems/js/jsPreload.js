var tempJsList = [];
function loadJs(jsList) {
    if (tempJsList.length == 0) {
        tempJsList = jsList.split(",");
    }
    loadJsProcess();
}
function loadJsProcess() {
    if (tempJsList.length > 0) {
        var jsFile = tempJsList.shift();
        if (jsFile.length > 0) {
            var scriptTag =  document.createElement('script');
            scriptTag.type =  'text/javascript';
            scriptTag.src = jsFile;
            if (tempJsList.length > 0) {
                scriptTag.onload = function() {
                    loadJsProcess();
                }
            }
            document.body.appendChild(scriptTag);
        }
    }
}