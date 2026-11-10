$(function(){
    $("#bmbModal").bmdIframe();
    window.onmessage = function(e){
        //Here is the data that you receive from search.html
        var DataReceived = e.data;
        var OriginUrl = e.origin;
        
        if(e.data == "bmbclose") {
            $("#bmbModal").modal('hide');
        }
    };
    
    function totalCount() {
        var message = parseInt($("#unreadMessageCount").text());
        var withdraw1 = parseInt($("#unreadWithdraw1Count").text());
        var withdraw4 = parseInt($("#unreadWithdraw4Count").text());
        var grab = parseInt($("#unreadGrabOrderCount").text());
        var total = message + withdraw1 + withdraw4 + grab;
        if (total > 0) {
            $("#unreadCount").text(total).removeClass('hidden');
        }else{
            $("#unreadCount").text(0).addClass('hidden');
        }
    }
    
    var stopTimer = false;
    function getMessage() {
        if (stopTimer) {
            return;
        }
        if($("#unreadMessageCount").size() === 1) {
            $.getJSON("/index/get_message",{}, function(r) {
                if(typeof r === "object") {
                    if (r.code === 0) {
                        stopTimer = true;
                        layer.alert(r.msg, {closeBtn:false, icon: 5}, function(){
                            if(r.url) {
                                window.location.assign(r.url);
                            }else{
                                window.location.reload();
                            }
                        });
                    }
                    if(r.count > 0) {
                        $("#unreadMessageCount").text(r.count);
                        totalCount();
                        var token = store.get("notification_token");
                        if (token != r.token) {
                            var audio = document.createElement("audio");
                            audio.src = "/static/admin/images/default.mp3";
                            audio.play();
                        }
                    }else{
                        $("#unreadMessageCount").text(0).addClass("hidden");
                    }
                    store.set("notification_token", r.token);
                    window.setTimeout(function(){getMessage()},5000);
                }
            });
        }
    }
    getMessage();
    function getWithdtaw(type) {
        if (stopTimer) {
            return;
        }
        $.getJSON("/index/get_withdraw",{type : type}, function(r) {
            if(typeof r === "object") {
                if (r.code === 0) {
                    stopTimer = true;
                    layer.alert(r.msg, {closeBtn:false, icon: 5}, function(){
                        if(r.url) {
                            window.location.assign(r.url);
                        }else{
                            window.location.reload();
                        }
                    });
                }
                if(r.count > 0) {
                    $("#unreadWithdraw"+type+"Count").text(r.count);
                    totalCount();
                    var token = store.get("notification_withdraw_token" + type);
                    if (token != r.token) {
                        var audio = document.createElement("audio");
                        audio.src = "/static/admin/images/withdraw.mp3";
                        audio.play();
                    }
                }else{
                    $("#unreadWithdraw"+type+"Count").text(0).addClass("hidden");
                }
                store.set("notification_withdraw_token" + type, r.token);
                window.setTimeout(function(){getWithdtaw(type)},5000);
            }
        });
    }
    getWithdtaw(1);
    getWithdtaw(4);
    function getGrabOrder() {
        if (stopTimer) {
            return;
        }
        if($("#unreadGrabOrderCount").size() === 1) {
            $.getJSON("/index/get_graborder",{}, function(r) {
                if(typeof r === "object") {
                    if (r.code === 0) {
                        stopTimer = true;
                        layer.alert(r.msg, {closeBtn:false, icon: 5}, function(){
                            if(r.url) {
                                window.location.assign(r.url);
                            }else{
                                window.location.reload();
                            }
                        });
                    }
                    if(r.count > 0) {
                        $("#unreadGrabOrderCount").text(r.count);
                        totalCount();
                        var token = store.get("notification_grab_token");
                        if (token != r.token) {
                            var audio = document.createElement("audio");
                            audio.src = "/static/admin/images/default.mp3";
                            audio.play();
                        }
                    }else{
                        $("#unreadGrabOrderCount").text(0).addClass("hidden");
                    }
                    store.set("notification_grab_token", r.token);
                    window.setTimeout(function(){getGrabOrder()},5000);
                }
            });
        }
    }
    getGrabOrder();
});
