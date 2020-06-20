<?php
use think\Config as tConfig;
$aliwapPayconfig = array (

        //'user_id' => "2088421787198094,2088421787198094,2088421787198094",  //配置多个时

        //以下是乐众的支付宝账号
        'user_id' => "",
		//应用ID,您的APPID。
		'app_id' =>  "",

		//商户私钥，您的原始格式RSA私钥
		'merchant_private_key' => "",

        //异步通知地址
        'notify_url' => tConfig::get('ali_notify_url'),
        //同步跳转
        'return_url' => tConfig::get('ali_return_url'),
        'quit_url' => tConfig::get('ali_quit_url'),

		//编码格式
		'charset' => "UTF-8",

		//签名方式
		'sign_type'=>"RSA2",

		//支付宝网关
		'gatewayUrl' => "https://openapi.alipay.com/gateway.do",

		//支付宝公钥,查看地址：https://openhome.alipay.com/platform/keyManage.htm 对应APPID下的支付宝公钥。
		'alipay_public_key' => "",
);

return $aliwapPayconfig;