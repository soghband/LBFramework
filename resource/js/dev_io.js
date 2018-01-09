var socket = io.connect('http://localhost:89');
socket.on('link', function (data) {
    var cssElement =  $(".devCss");
    for (var i =0;i<cssElement.length;i++) {
        var cssList = $(cssElement[i]).attr("fileList");
        if (cssList != "undefined") {
            var fileList = cssList.split(",");
            for (var j in fileList) {
                if ($('#cssDev_'+fileList[j]).length == 0) {
                    var styleTag = document.createElement('style');
                    styleTag.id = 'cssDev_'+fileList[j];
                    styleTag.setAttribute("fileList",fileList[j]);
                    var headerTagDev = document.getElementsByTagName('head')[0];
                    headerTagDev.appendChild(styleTag);
                }
                socket.emit('registerCSS', fileList[j]);
            }
        }
    }
});
socket.on("CssChange",function(data) {
    $("#cssDev_"+data.fileName).html(data.data);
});
