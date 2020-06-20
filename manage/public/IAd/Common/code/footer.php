<script type="text/javascript">
    <?php
    $clkUrl = "";
    switch ($table) {
        case 'user_templates':
            $clkUrl = "/IAd/Stat/?ptpId=0&utpId=" . ($id ?: 0) . "&tpId=" . $data['template_id'];
            break;
        case 'user_pages':
            $extParam = "";
            if (!empty($kkk)) {
                $extParam = "&k=" . $kkk;
            }
            $clkUrl = "/IAd/Stat/?ptpId=" . ($data['id'] ?: 0)  . "&utpId=" . ($data['user_template_id'] ?: 0) . "&tpId=" . ($userTemplateData['template_id'] ?: 0) . $extParam;
            break;
        default :
            $clkUrl = "/IAd/Stat/?ptpId=0&utpId=0&tpId=" . ($id ?: 0);
    }
    ?>

    // 判断安卓
    // function isAndroid() {
    //     var u = navigator.userAgent;
    //     if (u.indexOf("Android") > -1 || u.indexOf("Linux") > -1) {
    //         if (window.ShowFitness !== undefined) return true;
    //     }
    //     return false;
    // }
    // 判断设备为 ios
    function isIos() {
        var u = navigator.userAgent;
        if (u.indexOf("iPhone") > -1 || u.indexOf("iOS") > -1) {
            return true;
        }
        return false;
    }

    function isWx() {
        var ua = navigator.userAgent.toLowerCase();
        return ua.match(/MicroMessenger/i) == 'micromessenger';
    }
    //是否QQ
    function isQQ() {
        var ua = navigator.userAgent.toLowerCase();
        return !!ua.match(/QQ/i);
    }

    $("a[data-adlink]").on("click", function () {

        if (!isIos() &&  ( isWx() || isQQ() )){
            $(this).attr("href", "#");
            //将Jcontainer命名为Jmain，是为了各模板之间复制出错。
            $('#Jmain').css("display", "none");
            $('#mark').css("display", "block");
            $('#markimg').css("display", "block");

        } else {

            var clkurl = "<?php echo $clkUrl; ?>";
            var $img = $("<img style=\"width:0px;height:0px;\">");
            $img.attr("src", clkurl);
            $("body").append($img);

        }
    });

    //判断跳转方式
    var redirectType = "<?php echo isset($data['redirect_type']) ? $data['redirect_type'] : 1; ?>";
    if (redirectType !== "1") {
        //自动跳转IOS
        if( redirectType === "2" && isIos() ) {
            $("a[data-adlink]").click();
        }
        //自动跳转android
        if( redirectType === "3" && !isIos() ) {
            $("a[data-adlink]").click();
        }
        //根据设备自动跳转
        if( redirectType === "4" && isIos() ) {
            $("a[data-adlink]").click();
        }
    }
</script>

</body>
</html>

