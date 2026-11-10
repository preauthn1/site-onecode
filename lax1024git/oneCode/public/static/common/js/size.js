sizeWidth();
window.onresize = function() {
    sizeWidth();
}

function sizeWidth() {
    var screenWidth = document.querySelector('html').offsetWidth;
    var baseSz = document.querySelector('html').getAttribute('bsize');
    if (!baseSz) {
        baseSz = 10;
    }
    if (screenWidth > 750) {
        screenWidth = 750;
    }
    var fZ =  screenWidth / baseSz;
    document.querySelector("html").style.fontSize = fZ + "px";
};