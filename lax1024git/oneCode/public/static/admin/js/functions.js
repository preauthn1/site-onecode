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
// Retina Mode
function retina(){
  retinaMode = (window.devicePixelRatio > 1);
  return retinaMode;
}


/***************************************************************/
/*	以下是打开或新建浏览器窗口函数							   */
/***************************************************************/
function openWindowSelf(url){
    var openthiswin = window.open(url, "_self");
    if (openthiswin != null) openthiswin.focus();
}
function openWindow(url, width, height){
    var left = (window.screen.availWidth - width) / 2;
    var top = (window.screen.availHeight - height) / 2;

    var openthiswin = window.open(url, "welcomes", 'toolbar=no,location=no,directories=no,status=yes,menubar=no,scrollbars=yes,resizable=no,left=' + left + ',top=' + top + ',width=' + width + ',height=' + height);
    if (openthiswin != null)openthiswin.focus();
}
function openWindowOwn(url, name, width, height){
    var left = (window.screen.availWidth - width) / 2;
    var top = (window.screen.availHeight - height) / 2;
    var openthiswin = window.open(url, name, 'toolbar=no,location=no,directories=no,status=yes,menubar=no,scrollbars=yes,resizable=yes,left=' + left + ',top=' + top + ',width=' + width + ',height=' + height);
    if (openthiswin != null) openthiswin.focus();
}
/** 显示信息提示网页对话框                                        */
function showDialog(url, width, height){
    var top = (window.screen.availHeight - height) / 2;
    var feature = "dialogHeight:" + height + "px;dialogWidth:" + width + "px;dialogTop:" + top + "px; center:yes;scroll:yes;status:no;resizable:no;edge:raised;help:no;unadorned:no";
    var returnValue = window.showModalDialog(url, "游戏家园提醒您", feature);
    if (returnValue == "Success") {
        window.location.href = window.location.href;
    }
}
/**弹出一个不可见窗体(注释语句用来调试用)*/
function openHideWin(sPath){
    window.open(sPath, 'welcome', 'width=10px,height=10px,top=2000px,left=2000px');
    //window.open(sPath,'welcome','width=300px,height=500px,top=20px,left=20px');
}
/**弹出一个窗口*/
function windowOpen(sPath, sWidth, sHeight){
    window.open(sPath, 'welcomes', 'width=' + sWidth + ',height=' + sHeight + ',center=yes');
}
/**弹出一个窗口*/
function newWindow(sPath, sWidth, sHeight){
    window.open(sPath, 'welcomes', 'width=' + sWidth + ',height=' + sHeight +
        ',center=yes,toolbar=yes,menubar=yes,scrollbars=yes, resizable=yes,location=yes,status=yes');
}
/**打开链接地址上的图片*/
function openPic(url){
    window.open(url);
}

function dateValidator(dateString){
    alert(dateString);
}

/***************************************************************/
/*	以上是打开或新建浏览器窗口函数完毕						   */
/***************************************************************/