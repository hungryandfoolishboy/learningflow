var app = require('http').createServer(handler);
var io = require('socket.io')(app);

var Redis = require('ioredis');
var redis = new Redis({
        port:6380,
        host:'127.0.0.1',
        family:4,
        password:'123456',
        db:0
});

//在线用户

var onlineUsers = {};

//当前在线人数
var onlineCount = 0;

app.listen(6005, function() {
    console.log('Server is running!');
});

function handler(req, res) {
    res.writeHead(200);
    res.end('');
}

io.on('connection', function(socket) {

    socket.on('login', function(uid){
        socket.uid = uid;
        if(!onlineUsers.hasOwnProperty(uid)){
                onlineUsers[uid] = uid;
                onlineCount++;
        }
        io.emit('online',{onlineCount:onlineCount});
    });

    //监听用户退出
    socket.on('disconnect', function(){
        if(onlineUsers.hasOwnProperty(socket.uid)) {

                delete onlineUsers[socket.uid];

                onlineCount--;

                io.emit('logout', { onlineCount:onlineCount});
        }
     });
});

redis.psubscribe('*', function(err, count) {

});

redis.on('pmessage', function(subscribed, channel, message) {
    message = JSON.parse(message);
    io.emit(channel + ':' + message.event, message.data);
});
