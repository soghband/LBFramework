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
    var firstSignCss = [];
    socket.emit('link', {msg: "Dev-IO onnected"});
    socket.on('my other event', function (data) {
        console.log(data);
    });
    socket.on('registerEmbedCSS',function(data) {
        var dataSplit = data.split(",");
        var filePathArray = [];
        var firstSignCss = [];
        for (var i in dataSplit) {
            firstSignCss.push(dataSplit[i]);
            filePathArray.push("../"+config.CSS_PATH+"/"+dataSplit[i]+".css");
            console.log("Register CSS:"+dataSplit[i]);
        }
        watcherFSCss = watch(filePathArray, { recursive: true });
        watcherFSCss.on("change", function(evt,name) {
            if (evt == "update") {
                setTimeout(function () {
                    var cssData = ""
                    for (var i in dataSplit) {
                        var contents = fs.readFileSync("../"+config.CSS_PATH+"/"+dataSplit[i]+".css", 'utf8');
                        cssData += contents;
                    }
                    socket.emit("fsCssChange",cssData);
                },100);
            }
        });
    });
    // fs.watch('../'+config.CSS_PATH+'/', function (event, filename) {
    //     var ext = path.extname(filename);
    //     if (ext == ".css") {
    //         var contents = fs.readFileSync("../"+config.CSS_PATH+"/"+filename, 'utf8');
    //         console.log("==",contents);
    //     }
    //     //socket.emit("cssChange","test");
    // });
    // watch('../'+config.CSS_PATH+'/', { recursive: true }, function(evt, name) {
    //     var ext = path.extname(name);
    //     if (ext == ".css") {
    //         var contents = fs.readFileSync("../"+config.CSS_PATH+"/"+name, 'utf8');
    //         console.log("==",contents);
    //     }
    // });
});