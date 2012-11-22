#!/usr/bin/env node

String.prototype.uriToKey = function() { return this.replace(/\//, '').replace(/\//g, ':'); }
String.prototype.keyToUri = function() { return '/' + this.replace(/\:/g, '/'); }

var io = require("socket.io").listen(8081),
    redis = require("redis"),
    rd = redis.createClient(),  // redis data
    rc = redis.createClient();  // redis pubsub

rc
    .on("pmessage", function(pattern, evt, data) {
        var evtParams = evt.match(/e:(ctrl|data):(.+)/);
        if (evtParams == null) {
            console.error("stupid inbound message via redis pubsub.");
        } else {
            if (evtParams[1] == "data") {
                handleDataEvent(evtParams[2], data);
            } else if (evtParams[1] == "ctrl") {
                handleControlEvent(evtParams[2], data);
            }
        }
    })
    .psubscribe("e:*");

function handleControlEvent(evt, data) {
    var evtParams = evt.match(/([^:]+)+/g);
    switch (evtParams[0]) {
        case "client":
            switch (evtParams[1]) {
                case "new": registerNamespaces(data); break;
                case "modified": checkNamespaces(data); break;
                case "removed": registerNamespaces(data); break;
                default:
            }
            break;
        case "general":
        default:
    }
}

function handleDataEvent(evt, data) {
    var key = "data:" + data.uriToKey();
    console.log(arguments);
    rd.hgetall(key, function(err, res) {
        console.log(arguments);
        // res can be null if no payload should be pushed.
        io.of(data).emit(evt, res);
    });
}

function registerNamespaces(data) {
    rd.smembers(data, function(err, res) {
        for (var i = 0; i < res.length; ++i) {
            if (res[i] in io.namespaces) continue;
            setupNamespace(res[i]);
        }
        rd.del(data); // let php know that the namespaces are set up.
    });
}

function setupNamespace(ns) {
    io
        .of(ns)
        .authorization(function(hd, cb) {
            var sid = (hd.headers.cookie.match(/PHPSESSID=([^\;]+);?/i) || [,""])[1];
            rd.sismember("ns:" + ns.uriToKey(), sid, function(err, res) {
                return cb(null, res === 1);
            });
        });
}