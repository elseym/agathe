String.prototype.uriToKey = function() { return this.replace(/\//, '').replace(/\//g, ':'); };
String.prototype.keyToUri = function() { return '/' + this.replace(/\:/g, '/'); };

var elemPre = "out-",
    ioport = 8081,
    resources = {},
    EVT = {};

$.extend({
    "put": function(u, d, c, t, f) {
        f = $.isFunction(d);
        return $.ajax({ type: 'PUT', url: u, data: f ? {} : d, success: f ? d : c, dataType: f ? c : t });
    },
    "putJson": function(u, d, c) { return $.put(u, d, c, "json") },
    "delete": function(u, d, c, t, f) {
        f = $.isFunction(d);
        return $.ajax({ type: 'DELETE', url: u, data: f ? {} : d, success: f ? d : c, dataType: f ? c : t });
    },
    "deleteJson": function(u, d, c) { return $.delete(u, d, c, "json") }
});


$(function() {
    $('button#colorset')
        .on("click", function() {
            $.put("/api/color", { payload: $('input#colorspec').val() });
        });

    $('button#messagesset')
        .on("click", function() {
            $.post("/api/messages", { payload: $('input#messagesspec').val() });
        });

    $('button#textset')
        .on("click", function() {
            $.put("/api/text", { payload: $('input#textspec').val() });
        });

    $('#out-color')
        .on("PUT", function(e, data) {
            if (typeof data.payload == "string") {
                $(this).css("background", "-webkit-linear-gradient(bottom, " + data.payload + ", #FEFEFE)");
            }
        });

    $('#out-messages')
        .on("POST", function(e, data) {
            $(this).prepend($('<article>').text(data.payload));
        });

    $('#out-text')
        .on("PUT", function(e, data) {
            $(this).text(data.payload);
        });

    $.get("/api/setup", {}, function(d) {
        if (d && d.payload && d.payload.resources) {
            for (var i = 0; i < d.payload.resources.length; ++i) {
                var tmpRes = {
                    "uri": d.payload.resources[i],
                    "elem": $("#" + elemPre + d.payload.resources[i].uriToKey()),
                    "sock": io.connect(location.protocol + "//" + location.hostname + ":" + ioport + d.payload.resources[i])
                };
                tmpRes.sock
                    .on("error",  function() { console.log("error: ", arguments) })
                    .on("connect_failed", function() { console.log("connect failed:", arguments) })
                    .on("POST",   function(data) { resources[this.name.uriToKey()].elem.trigger("POST",   data); })
                    .on("PUT",    function(data) { resources[this.name.uriToKey()].elem.trigger("PUT",    data); })
                    .on("DELETE", function(data) { resources[this.name.uriToKey()].elem.trigger("DELETE", data); });
                resources[d.payload.resources[i].uriToKey()] = tmpRes;
            }
        }
    }, "json");
});