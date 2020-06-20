<?php
$payType = $_GET['pay_type'];

echo "<script>
//    (function () {
//        TFPayCancel($payType);
//    })();
//    function TFPayCancel(int) {
//        window.webkit.messageHandlers.TFPayCancel.postMessage(int);
//    }
    
    (function () {
        TFPayCancel($payType);
    })();
    function TFPayCancel(int) {
        var ua = navigator.userAgent;
        var android = ua.match(/(Android);?[\s\/]+([\d.]+)?/);
        var ipad = ua.match(/(iPad).*OS\s([\d_]+)/);
        var ipod = ua.match(/(iPod)(.*OS\s([\d_]+))?/);
        var iphone = !ipad && ua.match(/(iPhone\sOS)\s([\d_]+)/);
        if(android){
            var androidPayCancel=window.android.TFPayCancel(int);
        }else {
            window.webkit.messageHandlers.TFPayCancel.postMessage(int);
        }
    }
    
</script>";

exit;

