String.prototype.uriToResource = function() { return this.replace(/\//, '').replace(/\//g, ':'); }
String.prototype.resourceToUri = function() { return '/' + this.replace(/\:/g, '/'); }

var elemPre = "out-",
    ioport = 8081,
    resources = {},
    EVT = {};

$(function() {
    $('button#colorset')
        .on("click", function() {
            console.log("posting to /color: " + $('input#colorspec').val());
            $.post("/api/color", { "payload": $('input#colorspec').val() });
        });

    $('button#textset')
        .on("click", function() {
            console.log("posting to /text: " + $('input#textspec').val());
            $.post("/api/text", { "payload": $('input#textspec').val() });
        });

    $('button#messageset')
        .on("click", function() {
            console.log("posting to /message: " + $('input#messagespec').val());
            $.post("/api/message", { "payload": $('input#messagespec').val() });
        });

    $('#out-text')
        .on("POST", function(e, data) {
            $(this).text(data);
            console.log(data);
        });

    $('#out-color')
        .on("POST", function(e, data) {
            $(this).css("background-color", data);
        });

    $('#out-message')
        .on("POST", function(e, data) {
            $(this).prepend($('<article>').text(data));
        });

    $.get("/api/setup", {}, function(d) {
        if (d && d.data && d.data.resources) {
            for (var i = 0; i < d.data.resources.length; ++i) {
                var tmpRes = {
                    "uri": d.data.resources[i],
                    "elem": $("#" + elemPre + d.data.resources[i].uriToResource()),
                    "sock": io.connect(location.protocol + "//" + location.hostname + ":" + ioport + d.data.resources[i])
                }
                tmpRes.sock
                    .on("message", function() { console.log("msg: ", arguments) })
                    .on("error", function() { console.log("error: ", arguments) })
                    .on("POST", function(data) {
                        resources[this.name.uriToResource()].elem.trigger("POST", data);
                    })
                    .on("PUT", function() { console.log("resource modified: ", arguments) })
                    .on("DELETE", function() { console.log("resource  deleted: ", arguments) });
                resources[d.data.resources[i].uriToResource()] = tmpRes;
            }
        }
    }, "json");

//    for (var i = 0; i < uris.length; ++i) {
//        var tmpRes = {
//            "uri": uris[i],
//            "elem": $("#" + elemPre + uris[i].uriToResource()),
//            "sock": io.connect(location.protocol + "//" + location.hostname + ":" + ioport + uris[i])
//        }
//
//        tmpRes.sock
//            .on("message", function(data) {
//                console.log("inbound push", arguments);
//                //resources[uri](uri, data);
//            })
//            .on("welcome", function(data) {
//                console.info("i am welcome!");
//            });
//
//        tmpRes.sock.emit("hallo", "daten");
//
//        resources[uris[i].uriToResource()] = tmpRes;
//    }
});