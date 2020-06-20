<?php

header('content-type:text/html;charset=utf-8');
require("TopSdk.php");

$appkey = '24466860';
$secret = 'bb89a35449a170ca3390bee7379176e8';


$c = new TopClient;
$c->appkey = $appkey;
$c->secretKey = $secret;
$req = new AlibabaAliqinFcSmsNumSendRequest;
$req->setExtend("123456");
$req->setSmsType("normal");
$req->setSmsFreeSignName("泰逢TFww11");
$req->setSmsParam("{\"name\":\"sds\",\"code\":\"111222\",\"n\":\"1\"}");
$req->setRecNum("18588815049");
$req->setSmsTemplateCode("SMS_71291372");
$resp = $c->execute($req);

var_dump($resp);exit;