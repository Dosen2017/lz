<?php

//    file_put_contents('/tmp/callbackData.txt', date('Y-m-d H:i:s') . json_encode($_POST) . "\n", FILE_APPEND);
//
//    echo "SUCCESS";exit;



header("Content-type: text/html; charset=utf-8");
header("Access-Control-Allow-Origin: *");

date_default_timezone_set("PRC");

phpinfo();

exit;

$res['user_name'] = '';

var_dump(isset($res['user_name']));


exit;

$str = '[{"ProductId":"com.yxjh6","ZHProductId":"com.yxjh61","Money":"600"},{"ProductId":"product200","ZHProductId":"product200","Money":"200"}]';

$strArr = json_decode($str, true);


var_dump(array_column($strArr, 'Money'));
exit;

echo json_encode($_POST);exit;

$url = "https://www.baidu.com";

$fHandler = fopen($url, 'r');

var_dump($fHandler === false);

function url_exists($url)
{
    return file_get_contents($url,0,null,0,1) ? true : false;
}


exit;


echo (int)date('Ymd');exit;


$adsId = "hshd_fef_3r32r";
echo explode("-", $adsId)[0];exit;

var_dump(preg_match('/^[0-9a-zA-Z_-]{1,}$/', "hshd_fef_3r32r-*@#"));

exit;


var_dump( strpos("conf_浮层方向", "1conf_"));exit;

echo '{"88003":"乐众游戏十二","88002":"乐众游戏十一","88001":"乐众游戏十"}';exit;


//原程序有问题，现修改为ip138数据库

/**
 * 获取IP地区
 * Enter description here ...
 * @param unknown_type $ip
 */

function GetArea($ip)
{

    $url = "http://www.ip138.com/ips8.asp?ip=" . $ip . "&action=2";

    $contents = file_get_contents($url);

    preg_match_all('|<li>本站主数据：.*</li>|', $contents, $rsR);

    $rsR[0][0] = str_replace("<li>本站主数据：", "", $rsR[0][0]);

    $pos = strpos($rsR[0][0], '</li>');

    $Area = substr_replace($rsR[0][0], '', $pos);

    return $Area;

}

//header('Content-type:text/html;Charset=gb2312');

$area = GetArea('218.242.232.194');

print_r($area);

exit;
var_dump(get_pre_week_date());

//function get_pre_week_date($format='Ymd'){
//    $date = [];
//    $time = time();
//    for ($i=0; $i<=6; $i++){
//        $date[$i] = date($format, $time - 86400 * $i);
//    }
//    return $date;
//}
//exit;

echo file_get_contents("http://sdk-test-api.chaofangames.com/login/ClearCache");exit;

echo strtotime('20091225091010');exit;


$n = (int)((time() - strtotime(date('Y-m-d', 1586876400))) / 86400) + 1;

echo $n;exit;

exit;
$userInfo = "70000000096";
var_dump($userInfo['user_num']);exit;


exit;

$infoField = ["device_id", "device_model", "mac", "bundle", "version"];
$info = '{"device_id":"fb9eec7f-6df7-2019-af5e-7ad7dfefd593", "device_model":"EVR-AL00", "mac":"74:ac:5f:9e:9f:bb", "bundle":"com.admin.tom.1.0.4", "version":"12.13"}';
$infoDataKeys = array_keys(json_decode($info, true));
foreach ($infoField as $v) {
    if (!in_array($v, $infoDataKeys)) {
        echo $v . " is null";
    }
}


exit;


var_dump(preg_match('/^[\x7f-\xff]{1,20}$/', str_replace('·', '', $_GET['name'])));exit;



echo $_SERVER['REMOTE_ADDR'];exit;

function getAgeByID($id){ //过了这年的生日才算多了1周岁

    if (empty($id)) return '';

    $date = strtotime(substr($id, 6, 8)); //获得出生年月日的时间戳

    $today = strtotime('today'); //获得今日的时间戳

    $diff = floor(($today - $date) / 86400 / 365); //得到两个日期相差的大体年数

    //strtotime加上这个年数后得到那日的时间戳后与今日的时间戳相比

    $age = strtotime(substr($id, 6,8) . ' +' . $diff . 'years') > $today ? ($diff + 1) : $diff;

    return $age;

}

echo getAgeByID(421023200205016651);exit;



//61.241.120.156

//preg_match_all("/city:\"(.*)\".*province:\"(.*)\"/", iconv("gb2312","utf-8", file_get_contents("http://ip.ws.126.net/ipquery?ip=127.0.0.1")), $match);
//
//var_dump($match[1][0]);
//var_dump($match[2][0]);

//取IP信息
//function getIpAddress($ip=""){
//    $url = "http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=js";
//    if($ip!="")$url .= "&ip=".$ip;
//    $ipContent   = file_get_contents($url);
//    $jsonData = explode("=",$ipContent);
//    $jsonAddress = substr($jsonData[1], 0, -1);
//    return $jsonAddress;
//}
////返回IP的地理地址
//function showIpAddress($ip=""){
//    if($ip){
//        $cityArray = getIpAddress($ip);
//        $city_json = json_decode($cityArray,TRUE);
//        $address = $city_json['country'].''.$city_json['province'].''.$city_json['city'];
//        return $address;
//    }else{
//        return '';
//    }
//}
//
//$ip = '119.134.90.110';
////echo getIpAddress($ip);
////echo '<br>';
//echo showIpAddress($ip);
//echo '<br>';



   // $str = "source=WeChatPay_1491397502_MWEB&real_amount=1&amount=1&user_num=3333&game_num=44444&channel_pkg_num=5555&currency=6666&server_id=1&server_name=cevew&role_id=1332323232&role_name=233dff3&role_level=1&notify_url=http://sdk-test-api.chaofangames.com/recharge/payPage/ret.php&cp_order_num=scvevervreeww";

   // $strArr = explode('&', )

//    $param = $_GET;
//    ksort($param);
//    $str = '';
//    foreach ($param as $k => $v) {
//        $str .= $k . "=" . $v . "&";
//    }
//
//    echo htmlentities($str);