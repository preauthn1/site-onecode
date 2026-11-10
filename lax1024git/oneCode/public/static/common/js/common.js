
$(function(){
	$('.Cash_num_password').find('span').on('click', function(e){
        var type = $(this).data('id');
        $('#type').val(type);
        $('.Cash_num_password').find('span').removeClass('active');
        $(this).addClass('active');
    })

    $('.paypwd').keyup(function(e){
        if(e.keyCode == 8) { // 删除
            if ($(this).index() > 0) {
                $(this).prev().focus()
            }
        }else{
            var val =  $(this).val().replace(/[^\d]/g,'');
            $(this).val(val);
            if (val != '' && $(this).index() < 4) {
                $(this).next().focus()
            }
        }
    })
    $('.paypwd').on("click", function(e){
        var i = $(this).siblings().length;
        for(; i > 0; i--) {
            if ($(this).siblings().eq(i).val() == '') {
                $(this).siblings().eq(i).focus()
            }
        }
//        console.log($(this).siblings().length)
    })

    $('#checkBox').find('li').unbind();
    $('#checkBox').find('li').on('click', function(e){
        var type  = $(this).data('id');
        if($(this).hasClass('on')){
            $(this).removeClass('on');
        } else {
            $(this).addClass('on');
        } 
        e.stopPropagation();  // 表示阻止向父元素冒泡
        e.preventDefault();   // 阻止 方法阻止元素发生默认的行为（例如，当点击提交按钮时阻止对表单的提交或者a标签）。
    })

    /*$('.return').on('click', function(){
    	history.go(-1);
    })*/
})

function closeBox(id){
	$(id).hide();
}

function screen(){
    $('#screen').show();
}

// 手机验证码
var phoneReg = /(^1\d{10}$)|(^09\d{8}$)/; //手机号正则 
var count    = 60; // 间隔函数，1秒执行
var InterValObj;   // timer变量，控制时间
var curCount;      // 当前剩余秒数
/*第一*/
function sendMessage(send) {
    if (!send) {
        showSliderCaptcha();
        return;
    }
    curCount  = count;               
    var phone = $.trim($('#mobile').val());
    var tcode = $.trim($('#tcode').val());
    var gRecaptchaResponse = $.trim($('#gRecaptchaResponse').val());
    var smsverify = $.trim($('#smsverify').val());
    if (!phoneReg) {
//        layer.open({content: '请输入有效的手机号码', skin: 'msg', time: 2 });
//        return false;
    }
    if(tcode == ''){
//        layer.open({content: '请输入图形验证码', skin: 'msg', time: 2 });
//        return false;
    }

    // 向后台发送处理数据
    $.ajax({
        type: "POST",
        url: "/api/sendSms",
        data: {
            mobile: phone.replace(/\s+/g,""),
            tcode : tcode,
            smsverify : smsverify,
            gRecaptchaResponse:gRecaptchaResponse
        },
        dataType: 'json',
        success: function(res){
            console.log(res);
            if(res.code == 200){
                // 设置button效果，开始计时
                $("#btnGetCode").attr("disabled", "true");
                $("#btnGetCode").text( + curCount + " S");
                InterValObj = window.setInterval(SetRemainTime, 1000); // 启动计时器，1秒执行一次
                layer.open({
                    content: res.msg, 
                    skin: 'msg', 
                    time: 2
                });
            } else {
//                igrecaptcha();
                layer.open({content: res.msg, skin: 'msg', time: 2 });
            }
        }
    })  
}

function showSliderCaptcha() {
    verify_layer_index = layer.open({
        className:'slider-captcha-layer-content',
        content: '<div id="sliderCaptchaDiv"></div>',
        shadeClose:false
      ,success:function(){
        $('#sliderCaptchaDiv').sliderCaptcha(sliderCaptchaOptions);
      }
    });
}

function igrecaptcha() {
    grecaptcha.ready(function() {
        grecaptcha.execute(website_key, {action: 'sendsms'}).then(function(token) {
          var recaptchaResponse = document.getElementById('gRecaptchaResponse');
          recaptchaResponse.value = token;
        });
    });
}

function SetRemainTime() {
    if (curCount == 0) {                
        window.clearInterval(InterValObj);         // 停止计时器
        $("#btnGetCode").removeAttr("disabled"); // 启用按钮
        $("#btnGetCode").text($("#btnGetCode").data('re'));
    } else {
        curCount --;
        $("#btnGetCode").text( + curCount + " S");
    }
}

$.fn.textScroll = function(){ 
    var p = $(this),  c = p.children(), speed = 2000; //值越大速度越小
    var cw = c.width(),pw=p.width(); 
    var t = (cw / 100) * speed; 
    var f = null, t1 = 0; 
    function ani(tm) { 
        counttime(); 
        c.animate({ left: -cw }, tm, "linear", function () {  c.css({ left: pw });  clearInterval(f);  t1 = 0;  t=((cw+pw)/100)*speed;  ani(t);
        }); 
    } 
    function counttime() {
        f = setInterval(function () { 
            t1 += 10;  
        }, 10); 
    }
    p.on({  mouseenter: function () { 
        c.stop(false, false); 
        clearInterval(f);
        console.log(t1); 
    },  mouseleave: function () {
        ani(t - t1); 
        console.log(t1);
    } }); 
    ani(t); 
};
function setCookie(c_name, value, expiredays) {
    var exdate = new Date()
    exdate.setDate(exdate.getDate() + expiredays)
    document.cookie=c_name+ "=" + escape(value) + ((expiredays==null) ? "" : ";expires="+exdate.toGMTString())
}
function getCookie(c_name) {
    if (document.cookie.length > 0) {
        c_start = document.cookie.indexOf(c_name + "=")
        if (c_start!=-1) { 
            c_start = c_start + c_name.length+1 
            c_end = document.cookie.indexOf(";",c_start)
            if (c_end == -1) c_end = document.cookie.length
            return unescape(document.cookie.substring(c_start, c_end))
        } 
    }
    return "";
}
function langSwitch(lang_string){
    var langs=[];
    langs['vn']=[];
    langs['vn'][0]='data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAbCAYAAAAdx42aAAADSElEQVRIie2Wy4tcRRjFf19V3dvTj+me6U7GiCb4ykLIxicoqGAiBBkEkYGAGxf+AQpC8je4dScK7t0oKC7dudSVLuIYQ2eE6DixZ6b79nTfekjdbpMBZ/olMpsU3Lu4tzh16tT5Tn1y/fzDgRMc6iQXj+M+gcUJGJCuIPtAmgOyKMwiQwh9Dw8k4AP+L4VacuP9zOfpxRTQFntTU72SUX9/n+EtUCoQ5lx8AQJCEA9WEdCsXMxYfXsPvSb4nhkfwv+ogATwyuNuJ5Sfy0mf7ZMmnuorDr8lUKig5yJwtAdkvJEMfC6I3NuXkDLsQn1jgMEW35avdOh93kJ2IJhACFLMjITRAamFe5izEBAHXhT6QsD0PMPbCtERRKALnA80NvLxbE31kkdeHOB+TTH1sRccmFYgnPb4nxXkUpCZSiCgUDhkL8euppz9rEPlzADbUfjMxy1iTUJpLRufoCOtD3j0yw4qM3jtMKWAOTUky0rsfNCk/71BSvZosY+8CyJZBwe/Q/kZR/PDjMbze2MN1egJjqi03NX1nxIczdn7pcb2tSr2K4N6EEIaiP6dgcAIIIiglSNvG/IsYWV9SOujLkuPdFEhHznyMONgiiPqdxJ+u9qg/3ENY4Ykj+cQhTsmqKbfhiqgHOSbKdF2Z7+5Q/Pyzr85i2H3hwq3Xj0NHUftnMNWQhRqMvzk30Q3FmqoxzxaeVKf313zsACjjx5yT+mcxUfJrZ4aTjPlgBfBtx3Vyxnl17PxmprejRW6PzaKSnB4Gk8PqG/0yNsJ1sTMsLEY/zuBqICzCSuv9dBF7Ws6Xze5eXGV9gun2P50DV9A5TTftIiKpeyRMB1+JgJmW1N6KqfyXpdht8rmWy221pdRHUdSyfjj3TKbLz1E93qN2ht3qK4P8O2kUG5aNM90Gw4HlvIlz+4XTf58Zxl2A0tP9Ak6KdRYavQZfFfmxoUWZz5J0S+D/1Zh/HAUXhPGTD1h0B6fJvBTijQPUK0ANgL7sQMFMR6/D26rhDwZ0NoRDmKM24m9wmxNaUyQoRC0BhMDxR17eiGi2Rg8sYLD1EZltoYkmimJfvYFlzDBOiKCJCMmx4XP/ARGLA69J8+bpyO43xWfLAHgbykjU+7fU2FKAAAAAElFTkSuQmCC';
    langs['vn'][1]='Việt nam';
    langs['zh-cn']=[];
    langs['zh-cn'][0]='data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACYAAAAZCAYAAABdEVzWAAACPElEQVRIie2Xy2oUQRSGv1NVPeNoNBcvMUZ9gLhwIYIgXhARXAquFX0aX0I3bvIKLgVFEUREV64UjQkS1JlJdCZdXUeqejK5gcnIdHCRH5rpHrqrvv7/cw4z8vTmzAOMv4hKYDsJaO8uMYBu+8TgEjUE99wVVq9nnnPBbA9FgKKl2IYg9Wq4TAG504YRZUFl6w3FL9CiBxTNWSnPp+9nHDhj8K0KqOI+Ek1jYatPMS4PjdOC2Ve6tPp9PIolRQsl5Ipvah982OqDxZqJDnU+K76lnLjjcAeF5Q+BsAKmXtbU/Kyn/SZw8Kxh/JKFYq3uhim3upZfVhqnDMdvu+RUPJ+8ZZm4Ymm/L2i/Ddj9QjYmKcbaUWFkxtB8HRBZ/4rDUX8544TutzKesQsWqcHoeYttQOeTIlb6kbox+Pky8OWhx2QgbviOrUVZA81h7rHn98foDgkyXne+agJcH3uMMHR3ocZi/YSOcviaZWVReXe3y49ngYmrFrEb6yj/roxfNpy8l5XdW2WNRdkRobugKaLYAPOzObVJQzYupTMKvq1o/GyRnEwxVuDaBjDJIF/UVMz1Y0LIS3dSR/ZmWuzGognNV0V6JkJX4djGXgolXDziZjFCU+vF7EuwIzcc9enSIjdaDRSbHfubYvdFzT3K07xzhyqq+p52Pn16HH6JNHCHPbc2a+AJlKKlol8W6/epdvl/1x7YoNoDG1R7YIPqfwbTqaqH5UBKLDrlRO0TMUULdvC/chckRo2offEHMdXW9QpMK+4AAAAASUVORK5CYII=';
    langs['zh-cn'][1]='中文';
    langs['en']=[];
    langs['en'][0]='data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACgAAAAbCAYAAAAOEM1uAAAErklEQVRYhc2WOWwcZRiGn5n559rd2cvHOna89tqObYVchEADEQV0CClIFAiklAhoEU0o6FKkQVAgUdEjhISUOoEyDhDAuWyvnfW53iTra8+50Q6IKC2ZSH6lKf5/Rr8e/e/3fe9I0xe+uLdmh0MhIUEYYuoqzUaHRNIAQtptm2TCoG27CEXG0ASNRhvNSqH4Hl8uznHabrIjNOKU12yTmiyuC8fzS/mBvP6ovk8YhhT6Mxxs7dI30o8fBDQfHTA4NsTyxiNUoTDYn2F/a4dccQjJcThY38Z2GnSEHiuga7cQyYQqzV68Urk/vz6WKxXQhEJt8zFDxQG26w1URWEwn2KzUqPvSB7P99l/fMDI2CCbWzvRQTc+foPprImDFCtg6HkoycRt4eu6ZyT16PZs1yOVTdFpdrFMHUWW/rE7ncBx/WhtZZK09tuYkgSmRundN8nGivaU2sLb2dezxQIP/7V4cnSA8q1lisdHcb2AarnKxOkSK5UaqqYyPpxn6fdlBmaOoslw7eIlXjIlDmQRK1nQtTGGBnJC81zxYHOfwlh/ZHH57hqlk+NUtnej9dTJsWhvZLyA4/ksLWww9eIEK/UmwUGL6vUfWKVGDSNWQJcGmeRMQgRpy1Z3utiOFz1mJsnuXgtTEyiyTH23iW6ZNDo2sixhWCb1Xn3aLqGh8urV75jtS+JKcqyAoR+gmPqu8DXNS/dZ1FtdCEMmRgdYubXMyOworh/wcLnK+OkSlQc1ZE1QPJKn8ucK2eE8RjrByFuvYcWK9pTaQu10tPp6neHp4cjSlfvrUc2t9ixWBdNnJli8u8bRUiGqyUp5i5mzUyxt7bBX2+P251+DKbMfxtvFgeOi5bMZ4R80VSNvRR3suB6mlaDR6KBrajSYd/eaGCmDlu0iSxKmZUZ7eiqBT8gfl68g84gqMc9BGmTlWUsEqaSTSwuqj/cji6fHCyzeXKR4YhzX86kubDJ1bopyuYqiq0yO9rM4t8DgsaOoeYuz337FuUKaVhArH6HvI5LmntBzlrvyy20GT4xFFi/OVyieKrFW3UVVlcju8nyFoV4Xuz6Ld9aYPHuM5cUN0ASvfPgZaXhes7AhTb13ebVWbxeNXCLa8TsOkiwRCoFQJELXx3U8ZENFUWR820ORoNtLjiDgx9dLnMzodGLu4sD1UDPWPVG+88Agk6Wx8TCymCN5mK/AzNEIgOVteKEICxugqzCYhfkHcLzUC0yuffoJHk22iXdQO3hkGc9K33z/81L1wJ5SdRGlaW+0aLqKa7vRh6omcBwvsj8Igui9bmg4jo9MyDudOv1ul46sxAoYdXEuc18Kw/A34Gysp8enG6J2/YbpN5rIarwWPatCz0dJmCnpp/zLq493bha15/lP8j8UZbE6syVmLn3UHd+8gGzGG/bPqsB20ftzzV4N3gLOHCq6J5oTe3eWtLBjI4l4u/CZ5QfIhmZKV0fOr9Y3ezWYO1R8Lk0ymWObYvSDt52+8imUVPIQYD2R3+lijhQ6h70Gb4p29aGQepEWc5Y+s8IQSZE16fr591t7v/6VUHOZQ8XnNlpY06WuUDPWnD40MKWmU84h4PpPIpnQ1Gx6+W980wugw4AfUwAAAABJRU5ErkJggg==';
    langs['en'][1]='English';
    return langs.hasOwnProperty(lang_string)?langs[lang_string]:false;
}

/**
 * 一些通用方法
 */
(function(exports) {
    /**
     * 将string字符串转为html对象,默认创一个div填充
     * 因为很常用，所以单独提取出来了
     * @param {String} strHtml 目标字符串
     * @return {HTMLElement} 返回处理好后的html对象,如果字符串非法,返回null
     */
    exports.parseHtml = function(strHtml) {
        if (typeof strHtml !== 'string') {
            return strHtml;
        }
        // 创一个灵活的div
        var i,
            a = document.createElement('div');
        var b = document.createDocumentFragment();
        a.innerHTML = strHtml;
        while ((i = a.firstChild)) {
            b.appendChild(i);
        }
        return b;
    };
    /**
     * 将对象渲染到模板
     * @param {String} template 对应的目标
     * @param {Object} obj 目标对象
     * @return {String} 渲染后的模板
     */
    exports.renderTemplate = function(template, obj) {
        return template.replace(/[{]{2}([^}]+)[}]{2}/g, function($0, $1) {
            return obj[$1] || '';
        });
    };

    /**
     * 添加测试数据
     * @param {String} dom 目标dom
     * @param {Number} count 需要添加的数量
     * @param {Boolean} isReset 是否需要重置，下拉刷新的时候需要
     * @param {Number} index 属于哪一个刷新
     */
    exports.appendTestData = function(dom, data, template, isReset) {
        if (typeof dom === 'string') {
            dom = document.querySelector(dom);
        }
        if (isReset) {
            dom.innerHTML = '';
        }
        var html = '';
        for (var i = 0; i < data.length; i++) {
            html += exports.renderTemplate(template, data[i]);
        }
        var child = exports.parseHtml(html);
        dom.appendChild(child);
    };
    
    /**
     * 请求数据
     * @param {string} url 
     * @param {object} params
     * @param {function} callback
     * @param {string} method
     * @returns {undefined}
     */
    exports.request = function(url, params, callback, method) {
        $.ajax({
            type: method === "POST" ? "POST" : "GET",
            url: url,
            data: params,
            dataType: 'JSON',
            success: function(res){
                callback(res);
            }
        })
    };

    /**
     * 绑定监听事件 暂时先用click
     * @param {String} dom 单个dom,或者selector
     * @param {Function} callback 回调函数
     * @param {String} eventName 事件名
     */
    exports.bindEvent = function(dom, callback, eventName) {
        eventName = eventName || 'click';
        if (typeof dom === 'string') {
            // 选择
            dom = document.querySelectorAll(dom);
        }
        if (!dom) {
            return;
        }
        if (dom.length > 0) {
            for (var i = 0, len = dom.length; i < len; i++) {
                dom[i].addEventListener(eventName, callback);
            }
        } else {
            dom.addEventListener(eventName, callback);
        }
    };
})(window.Common = {});

function gonative_device_info(info) {
    console.log(info);
}