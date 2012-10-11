#!/usr/bin/env node

String.prototype.uriToResource = function() { return this.replace(/\//, '').replace(/\//g, ':'); }
String.prototype.resourceToUri = function() { return '/' + this.replace(/\:/g, '/'); }

var io = require("socket.io").listen(8081),
    redis = require("redis"),
    rd = redis.createClient(),  // redis data connection
    rc = redis.createClient();  // redis ctrl connection

io.configure(function() {
    io.set('authorization', function(hd, cb) {
        var sid = hd.headers.cookie.match(/PHPSESSID=([^\;]+);?/i)[1];
        console.log("authorizing client with sid " + sid);
        cb(null, true);
    });
});

rc
    .on("pmessage", function(pattern, evt, data) {
        console.info("redis msg", arguments)
        var evtParams = evt.split(/\:/);
        switch (evtParams[1]) {
            case "data":
                rd.get(data.uriToResource(), function(err, res) {
                    io.of(data).emit(evtParams[2], res);
                });
                break;
            case "ctrl":
                switch (evtParams[2]) {
                    case "new":
                        registerNamespaces(data);
                        break;
                    default:
                }
                break;
            default:
        }
        //io.of(uri).emit(method.replace(/^e\:/, ''), "hallo");
    })
    .psubscribe("e:*");

io
    .on("connection", function(sock) {
        sock
            .on("message", function(msg, cb) {
                //console.log("client: [s] ", arguments);
            })
            .on("hallo", function(data) {
                //console.log("HALLO!! client: [s] ", arguments);
            });
    });

function registerNamespaces(data) {
    rd.smembers(data, function(err, res) {
        console.log("registering namespaces:", res);
        if (err) return false;
        rd.del(data);
        for (var i = 0; i < res.length; ++i) {
            if (res[i] in io.namespaces) continue;
            io
                .of(res[i])
                .on("connection", function(sock) {
                    sock.send("welcome to " + sock.namespace.name);
                });
        }
    });
}