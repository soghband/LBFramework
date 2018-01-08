var socket = io.connect('http://localhost:89');
socket.on('link', function (data) {
    console.log(data);
    socket.emit('my other event', { my: 'data' });
    socket.emit("registerEmbedCSS",$("#cssEmbed").attr("fileList"));
});
socket.on("fsCssChange",function(data) {
    console.log(data);
    $("#cssEmbed").html(data);
    // var fileList = $("#cssEmbed").attr("fileList");
    // $("#cssEmbed").remove();
    // var linkTag = document.createElement('link');
    // linkTag.rel = 'stylesheet';
    // linkTag.id = 'cssEmbed';
    // linkTag.href = "/css/"+fileList+".cssfs";
    // linkTag.setAttribute("fileList",fileList);
    // var headerTagDev = document.getElementsByTagName('head')[0];
    // headerTagDev.appendChild(linkTag);
});
