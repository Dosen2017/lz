<?php
/**
 * Created by PhpStorm.
 * User: sds
 * Date: 2019/2/27
 * Time: 11:53
 */

//配置文件
return [
    /*
    'database' => [
        // 数据库类型88
        'type'        => 'mysql',
        // 服务器地址
        'hostname'    => 'rm-wz9367w216k67j0km9o.mysql.rds.aliyuncs.com',
        // 数据库名
        'database'    => 'lzsdk',
        // 数据库用户名
        'username'    => 'suds',
        // 数据库密码
        'password'    => '',
        // 数据库编码默认采用utf8
        'charset'     => 'utf8',
        // 数据库表前缀
        'prefix'      => '',
    ],
    */
    //登录充值的域名
    'lp_domain' => 'localhost',
    'success_url' => '/recharge/payPage/success.php',

    //支付选择地址，第三方支付公用地址
    'pay_option_url' => "/recharge/payPage/repa.php",
    //支付宝下单地址
    'zfb_url' => "/recharge/AliwapPayO/getRepaHtml",
    //异步通知地址
    'ali_notify_url' => "/recharge/alipayRechargeO",
    //支付宝商户ID
    'zfb_partner' => ,
    //同步跳转
//    'ali_return_url' => "https://sdk-test-api.chaofangames.com/recharge/payPage/success.php?pay_type=2",
//    'ali_quit_url' => 'https://sdk-test-api.chaofangames.com/recharge/payPage/cancel.php?pay_type=2',


    //微信参数
    'app_id' => '',
    'mch_id' => '',
    'app_key' => '',
    'notify_url' => '/recharge/WebchatRecharge',
    //微信下单地址
    'wx_url' => "/recharge/WebchatPay/getOrderInfoWebchatPay",
    //微信支付参数申请的域名，设置为请求下单的refer
//    'refer_url' => "sdk.lezhonggame.com://",


];