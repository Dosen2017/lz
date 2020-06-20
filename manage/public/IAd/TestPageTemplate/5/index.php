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
    <title></title>
    <script src="<?php echo $domain; ?>/Content/zepto/zepto.min.js"></script>
    <link href="<?php echo $domain; ?>/Content/swiper/dist/css/swiper.min.css" rel="stylesheet" />
    <link href="<?php echo $domain; ?>/IAd/Common/4/Styles/global_slider.css" rel="stylesheet" />
    <link href="<?php echo $domain; ?>/IAd/Common/4/Styles/index.css" rel="stylesheet" />
    <script src="<?php echo $domain; ?>/Content/swiper/dist/js/swiper.min.js"></script>
    <script src="<?php echo $domain; ?>/IAd/Common/4/Styles/screen.js"></script>
    <style type="text/css">
        .main {
        height:100%;position:relative;background:#181818 url(<?php echo $domain; ?>/IAd/Common/4/Styles/sdwf_bg.jpg);-webkit-background-size: 100% auto;
background-size: 100% auto;}
        .top {
            position: fixed;
            top: 0px;
            width: 100%;
        }
        .bottom {
            position: fixed;
            bottom: 0px;
            width: 100%;
        }
        .main img {
            height:auto;
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
    <meta charset="UTF-8">
    <title><?php echo $data['title']; ?></title>
    <meta name="keywords" content="" />
    <meta name="description" content="" />
    <meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=no">
    <meta name="format-detection" content="telephone=no">
    <meta name="format-detection" content="address=no">
    <meta name="applicable-device" content="mobile">
    <link href="<?php echo $domain; ?>/IAd/Common/4/Styles/common.css" rel="stylesheet" media="all" />
    <link href="<?php echo $domain; ?>/IAd/Common/4/Styles/index.css" rel="stylesheet" />

</head>
<body>
    <div id="mark" style="display: none">
        <img src="<?php echo $domain; ?>/IAd/Common/img/<?php echo $os ?>.png" id="markimg"  style="display: none"/>
    </div>
    <div class="main" id="Jmain">
        <div class="swiper-container" id="specile">
            <div class="swiper-wrapper">
                <div class="swiper-slide" style="height:auto;">
                    <div class="stage stage1">
                        <div class="h360"></div>
                        <div class="black-bg" style="background: #110c12 url(<?php echo $confData['背景图'] ?>) no-repeat;  background-size: 100% auto;"></div>
                    </div>

                    <div class="stage stage2">
                        <div class="gw-content">
                            <section id="activy">
                                <div class="swiper-container swiper-container-horizontal swiper-container-android">
                                    <div class="swiper-wrapper">
                                            <div class="swiper-slide">
                                                <img src="<?php echo $confData['轮播1图片1'] ?>" />
                                            </div>
                                            <div class="swiper-slide">
                                                <img src="<?php echo $confData['轮播1图片2'] ?>" />
                                            </div>
                                            <div class="swiper-slide">
                                                <img src="<?php echo $confData['轮播1图片3'] ?>" />
                                            </div>
                                    </div>
                                </div>
                                <div class="swiper-button-prev" style="background-image:url(<?php echo $confData['轮播箭头'] ?>)"></div>
                                <div class="swiper-button-next" style="background-image:url(<?php echo $confData['轮播箭头'] ?>)"></div>
                                <div class="swiper-pagination"></div>
                            </section>
                            <div class="xian"></div>
                        </div>
                    </div>
                    <div class="stage stage3">
                            <img src="<?php echo $confData['图片1'] ?>" width="100%"/>
                            <a href="<?php echo in_array('图片2', $linkData) ? $downUrl : '#'; ?>" <?php echo in_array('图片2', $linkData) ? 'data-adlink="adlink"' : "" ?> >
                                <img src="<?php echo $confData['图片2'] ?>" width="100%"/>
                            </a>
                    </div>
                    <div class="stage stage4">
                        <div class="h1"></div>
                        <div class="xian"></div>
                        <section id="gamepoint">
                            <div class="swiper-container">
                                <div class="swiper-wrapper">
                                        <div class="swiper-slide">
                                            <img src="<?php echo $confData['轮播2图片1'] ?>" />
                                        </div>
                                        <div class="swiper-slide">
                                            <img src="<?php echo $confData['轮播2图片2'] ?>" />
                                        </div>
                                        <div class="swiper-slide">
                                            <img src="<?php echo $confData['轮播2图片3'] ?>" />
                                        </div>
                                 </div>

                            </div>
                            <div class="swiper-button-prev" style="background-image:url(<?php echo $confData['轮播箭头'] ?>)"></div>
                            <div class="swiper-button-next" style="background-image:url(<?php echo $confData['轮播箭头'] ?>)"></div>
                            <div class="swiper-pagination"></div>
                        </section>
                        <div class="h1"></div>
                        <!--通用底部模块-->
                        <section class="site_bottom">
                            <div class="NIE-copyRight">
                            <p><?php echo $confData['公司名称'] ?></p>
                            <p><?php echo $confData['文网文号'] ?></p>
                            </div>
                            <div class="h1">
                            </div>
                            <a class="btn_toTop"></a>
                        </section>

                    </div>
                </div>
                <div class="swiper-scrollbar"></div>
            </div>
        </div>
        <div class="down-bar" id="Jdownbar">
            <a href="<?php echo in_array('游戏LOGO', $linkData) ? $downUrl : '#'; ?>" <?php echo in_array('游戏LOGO', $linkData) ? 'data-adlink="adlink"' : "" ?>   class="down-bar-logo">
                <img src="<?php echo $confData['游戏LOGO'] ?>" />
            </a>
            <span>
                <b><?php echo $data['title']; ?></b>
            </span>
            <a rel="nofollow" href="<?php echo in_array('下载按钮', $linkData) ? $downUrl : '#'; ?>" <?php echo in_array('下载按钮', $linkData) ? 'data-adlink="adlink"' : "" ?> class="down-bar-btn btn_download" style="url(./IAd/Common/4/Styles/down_btn.jpg)" id="download-btn" pub-name="下载链接">下载游戏</a>
        </div>
    </div>

</body>
    <script src="<?php echo $domain; ?>/IAd/Common/4/Styles/common.js?2017090716170001"></script>
    <script src="<?php echo $domain; ?>/IAd/Common/4/Styles/slide.js"></script>
    <script src="<?php echo $domain; ?>/IAd/Common/4/Styles/index.js"></script>
    <?php
        $defaultPath = "../../Common/code/footer.php";
        if (!file_exists($defaultPath)) {
            $defaultPath = "../Common/code/footer.php";
        }
        include_once $defaultPath;
    ?>