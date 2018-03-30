var fs = require("fs");
var server = require("http").createServer(function(req, res) {
     res.writeHead(200, {"Content-Type":"text/html"});
     var output = fs.readFileSync("./timeline.php", "utf-8");
     res.end(output);
}).listen(3000);
var io = require("socket.io").listen(server);
// var io = require("socket.io").listen(8080);
io.sockets.on("connection",function(socket) {
    console.log('hoge');
    // 3. チャット開始通知を受けたらtokenをhtmlにかえします
    socket.on("onServerRecvStart",function(data) {
        console.log('3');
        socket.emit("onClientRecvToken",socket.id);
    });
});