<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>游戏支付平台</title>
    <link  rel="stylesheet" type="text/css" href="style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=0"/>
</head>
<body>

    <div class="header" id="header">
        <div class="headerTitle">
            <p>游戏支付平台</p>
        </div>
    </div>

    <input type="hidden" id="param" name="param" value="<?php
        $param = $_GET;
        unset($param['zfb_url']);
        unset($param['wx_url']);
        echo http_build_query($param);
        ?>" />

    <div class="container">
        <div class="infoContainer">
            <div class="infoLabel">
                <p id="lzRePaNumber" style="float: left">充值账号:<?php echo $param['user_name'];?><a id="do-back" style="float: right" onclick="doBack()">返回游戏</a></p>
            </div>
            <div class="infoLabel">
                <p id="lzRepaMoney">充值金额:<?php echo $param['amount'] / 100;?></p>
            </div>

        </div>

        <div class="repaContainer">
            <div class="repaBtn">
                <a onclick="LZRepaType(2)">
                    <img src="images/alipayLogo.png">
                    <p>支付宝</p>
                </a>
            </div>
            <div class="repaBtn">
                <a onclick="LZRepaType(3)">
                    <img src="images/weixinlogo.png">
                    <p>微信支付</p>
                </a>
            </div>
            <!--<div class="repaBtn">-->
                <!--<a onclick="LZRepaType(4)">-->
                    <!--<img src="images/unionpay.png">-->
                    <!--<p>银联支付</p>-->
                <!--</a>-->
            <!--</div>-->
            <div class="infoLabel">
                <p id="lzRepaPro">充值出现问题？请联系QQ：228630674</p>
            </div>
        </div>

    </div>


    <script>
        let url;
        url = "<?php echo $_GET['zfb_url']; ?>";   //默认为支付宝
        url2 = "<?php echo $_GET['wx_url']; ?>";   //微信支付

        let bundle;
        bundle = "<?php echo $_GET['bundle'];?>";

        function LZRepaType(i) {
            if ( i == 3 ) {
                url = url2
            }
            window.location.href = url + "?" + document.getElementById("param").value;
        }

        var doBack = function(){
            try{
                window.closeMyself();
            }catch (e) {
                window.parent.postMessage({call:'showNormal'},'*');
                if(bundle){
                    location.href = bundle+"://";
                }
            }
        };

    </script>


</body>
</html>














































