(function (doc, win) {
    var docEl = doc.documentElement,
    resizeEvt = 'orientationchange' in window ? 'orientationchange' : 'resize',
    recalc = function () {
        var clientWidth = docEl.clientWidth;
        if (!clientWidth) return;
        if(clientWidth>=640){
            docEl.style.fontSize = '100px';
        }else{
            docEl.style.fontSize = 100 * (clientWidth / 640) + 'px';
        }
    };
     // Abort if browser does not support addEventListener
    if (!doc.addEventListener) return;
    win.addEventListener(resizeEvt, recalc, false);
    doc.addEventListener('DOMContentLoaded', recalc, false);
    //以640像素设计图为准 ，1rem代表100像素
})(document, window);