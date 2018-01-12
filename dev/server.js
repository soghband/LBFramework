var app = require('express')();
var server = require('http').Server(app);
var io = require('socket.io')(server);
var fs = require('fs');
var config = require('../resource/config');
var path = require('path');
var watch = require('node-watch');

var watcherFSCss;

server.listen(89);

io.on('connection', function (socket) {
    var filePathArray = [];
    socket.emit('link', {msg: "Dev-IO onnected"});
    socket.on('my other event', function (data) {
        console.log(data);
    });
    socket.on('registerCSS',function(data) {
        filePathArray.push("../"+config.CSS_PATH+"/"+data+".css");
        console.log("Register CSS:"+data);
        var initData =  fs.readFileSync("../"+config.CSS_PATH+"/"+data+".css", 'utf8');
        socket.emit("CssChange",{fileName:data ,data:initData});
        watcherFSCss = watch(filePathArray, { recursive: true });
        watcherFSCss.on("change", function(evt,name) {
            if (evt == "update") {
                setTimeout(function () {
                    var cssData =  fs.readFileSync(name, 'utf8');
                    socket.emit("CssChange",{fileName: path.basename(name, '.css'),data:cssData});
                },200);
            }
        });
    });
});