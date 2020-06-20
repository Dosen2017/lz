<?php
    $defaultPath = "../../Common/code/common.php";
    if (!file_exists($defaultPath)) {
        $defaultPath = "../Common/code/common.php";
    }
    require_once $defaultPath;
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta name="author" content="tkylin" />
    <meta content="width=device-width,user-scalable=no" name="viewport" />
    <meta name="HandheldFriendly" content="true" />
    <meta http-equiv="x-rim-auto-match" content="none" />
    <meta name="format-detection" content="telephone=no" />
    <title><?php echo $data['title']; ?></title>
    <link href="<?php echo $domain; ?>/IAd/Common/5/Styles/reset.css" rel="stylesheet" />
    <script src="<?php echo $domain; ?>/Content/zepto/zepto.min.js"></script>
    <link href="<?php echo $domain; ?>/Content/swiper/dist/css/swiper.min.css" rel="stylesheet" />
    <script src="<?php echo $domain; ?>/Content/swiper/dist/js/swiper.min.js"></script>
    <script src="<?php echo $domain; ?>/IAd/Common/5/Styles/conversion.js"></script>
    <style>
        .container img {
            width:100%;
            display:block;
        }
        .top {
            width:100%;
            max-width:640px;
            min-width:320px;
            margin: 0 auto;
            position: fixed;
            top: 0px;
            width: 100%;
            z-index:3;
        }
        .bottom {
            width:100%;
            max-width:640px;
            min-width:320px;
            margin: 0 auto;
            position: fixed;
            bottom: 0px;
            width: 100%;
            z-index:3;
        }
        .swiper-container {
            width: 95.4%;
            height: 100%;
        }

        .swiper-pagination-bullet {
            background: #1d1d63;
            opacity: 1;
        }

        .swiper-pagination-bullet-active {
            background: #5754c5;
        }

        .swiper-pagination-bullet {
            width: 12px;
            height: 12px;
        }

        #markimg{
            width:100%;
            position:fixed;
        }

        #mark{
            position:fixed;
            left:0;
            top:0;
            opacity:.7;
            width:100%;
            height:100%;
            background:#000;
            /*z-index:998;*/
            pointer-events: none; //不能操作
        }

    </style>
</head>
<body style="margin-bottom:<?php echo $confData['距离顶部或底部距离']; ?>">
    <div id="mark" style="display: none">
        <img src="<?php echo $domain; ?>/IAd/Common/img/android.png" id="markimg"  style="display: none"/>
    </div>
    <div class="container" id="Jmain">
        <div>
            <a href="<?php echo in_array('浮层图片', $linkData) ? $downUrl : '#'; ?>" <?php echo in_array('浮层图片', $linkData) ? 'data-adlink="adlink"' : "" ?>  >
                <img class="<?php echo $confData['浮层方向']; ?>" src="<?php echo $confData['浮层图片']; ?>" />
            </a>
            <div class="stage1">
                <a href="<?php echo in_array('图片上1', $linkData) ? $downUrl : '#'; ?>" <?php echo in_array('浮层图片', $linkData) ? 'data-adlink="adlink"' : "" ?> >
                    <img src="<?php echo $confData['图片上1']; ?>" ) />
                </a>
            </div>

            <!-- Swiper -->
            <div class="swiper-cont" style="background:<?php echo $confData['轮播背景']; ?>">
                <div class="swiper-container">
                    <div class="swiper-wrapper">
                        <div class="swiper-slide">
                            <img src="<?php echo $confData['轮播1图片1']; ?>" />
                        </div>
                        <div class="swiper-slide">
                            <img src="<?php echo $confData['轮播1图片2']; ?>" />
                        </div>
                        <div class="swiper-slide">
                            <img src="<?php echo $confData['轮播1图片3']; ?>" />
                        </div>
                        <div class="swiper-slide">
                            <img src="<?php echo $confData['轮播1图片4']; ?>" />
                        </div>
                        <div class="swiper-slide">
                            <img src="<?php echo $confData['轮播1图片5']; ?>" />
                        </div>
                    </div>
                    <!-- Add Pagination -->
                    <div class="swiper-pagination"></div>
                </div>
            </div>
            <!-- Swiper -->
            <div class="stage2">
                <img src="<?php echo $confData['图片下1']; ?>" />
                <img src="<?php echo $confData['图片下2']; ?>" />
            </div>
        </div>
        <p style="text-align:center;background-color:#fff;color:#000;font-size:14px;padding:20px 0;"><?php echo $confData['公司名称'] . " " . $confData['文网文'] ?></p>

    </div>
    <script type="text/javascript">
        var swiper = new Swiper('.swiper-container', {
            pagination: '.swiper-pagination',
            paginationClickable: true,
            slidesPerView: 2,
            spaceBetween: 10,
            autoplay: 2500,
            autoplayDisableOnInteraction: false
        });
    </script>
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
