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
    <?php
        $defaultPath = "../../Common/code/footer.php";
        if (!file_exists($defaultPath)) {
            $defaultPath = "../Common/code/footer.php";
        }
        include_once $defaultPath;
    ?>