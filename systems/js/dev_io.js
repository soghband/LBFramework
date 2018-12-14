var socket = io.connect('http://localhost:89');
socket.on('link', function (data) {
    var cssElement =  $(".devCss");
    for (var i =0;i<cssElement.length;i++) {
        var cssList = $(cssElement[i]).attr("fileList");
        if (cssList != "undefined") {
            var fileList = cssList.split(",");
            for (var j in fileList) {
                var filename = fileList[j].replace(/\./,"_dot_");
                if ($('#cssDev_'+filename).length == 0) {
                    var styleTag = document.createElement('style');
                    styleTag.id = 'cssDev_'+filename;
                    styleTag.setAttribute("fileList",filename);
                    var headerTagDev = document.getElementsByTagName('head')[0];
                    headerTagDev.appendChild(styleTag);
                }
                socket.emit('registerCSS', fileList[j]);
            }
        }
    }
    cssElement.html("");
});
socket.on("CssChange",function(data) {
    var filename = data.fileName.replace(/\./,"_dot_");
    $("#cssDev_"+filename).html(data.data);
});
