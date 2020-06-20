//rem鎹㈢畻
(function(doc, win) {
    var config = {
        docEl: doc.documentElement,
        resizeEvt: 'orientationchange' in window ? 'orientationchange' : 'resize',
        clientWidth: doc.documentElement.clientWidth,
        firstWidth: doc.documentElement.clientWidth,
        setFontSize: function() {
            config.docEl.style.fontSize = 100 * ((config.clientWidth > 640 ? 640 : config.clientWidth) / 640) + 'px';
        },
        recalc: function() {
            config.clientWidth = config.docEl.clientWidth;
            if (!config.clientWidth) return;
            config.setFontSize();
            config.docEl.style.zoom = config.isUc() && config.isAndroid() ? config.clientWidth / config.firstWidth : 1;
        },
        isUc: function() {
            return navigator.userAgent.indexOf("UCBrowser") > 0;
        },
        isAndroid: function() {
            return navigator.userAgent.indexOf("Android") > 0;
        }
    };
    config.setFontSize();
    if (!doc.addEventListener) return;
    win.addEventListener(config.resizeEvt, config.recalc, false);
    doc.addEventListener('DOMContentLoaded', config.recalc, false);
})(document, window);