<?php
/**
 * Created by PhpStorm.
 * User: sds
 * Date: 2019/2/27
 * Time: 11:49
 */

// 应用公共文件
if (!function_exists('test')) {

    /**
     * 函数创建的模板
     * @return string
     */
    function test()
    {
        return 'test';
    }
}

// 应用公共文件
if (!function_exists('echoJson')) {

    /**
     * 返回json数据
     * @param $ret
     * @param null $msg
     * @param bool $exit
     */
    function echoJson($ret, $msg = null, $exit = true)
    {
        $array['ret'] = (int)$ret;
        $msg && $array['msg'] = $msg;
        echo json_encode($array);
        $exit && exit;
    }
}

if (!function_exists('httpRequest')) {
    /**
     * @param $url  url链接
     * @param null $data   post时，需要传入的参数
     * @param bool|false $https  http or https   默认是http请求
     * @param string $reqType get or post
     * @param int $dataType  1 指普通数据  http_build_query($data), 2指json json_encode($data)
     * @return mixed
     */
    function httpRequest($url,$data = null, $https = false, $reqType = 'get', $dataType = 1, $timeout = 10) {
        $ch = curl_init();

        //是否是https请求
        if ($https)
        {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);    // https请求 不验证证书和hosts
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        //请求类型
        if ($reqType == 'post')
        {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }

        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);

        if ($dataType == 1) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type:application/x-www-form-urlencoded;charset=utf-8'));
        } elseif($dataType == 2)
        {
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type:application/json;charset=utf-8'));
        }
        $rtn = curl_exec($ch);
        if($errNo = curl_errno($ch))
            return 'curl error:' . $errNo;
        curl_close($ch);
        return $rtn;
    }
}


// 应用公共文件
if (!function_exists('myHash')) {

    /**
     * 函数创建的模板
     * @return string
     */
    function myHash($key)
    {
        $md5 = substr(md5($key), 0, 8);
        $seed = 31;
        $hash = 0;
        for ($i=0; $i<8;$i++) {
            $hash = $hash * $seed + ord($md5{$i});
            $i++;
        }

        return $hash & 0x7FFFFFFF;
    }
}

//获取随机数
if (!function_exists('random')) {
    /**
     * 获取随机数
     */
    function random($length, $chars = '123456789abcdefghijklmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ')
    {
        $hash = '';
        $max = strlen($chars) - 1;
        for ($i = 0; $i < $length; $i++) {
            $hash .= $chars[mt_rand(0, $max)];
        }
        return $hash;
    }
}

//获取随机验证码
if (!function_exists('randomCode')) {
    /**
     * 获取验证码
     */
    function randomCode($length, $chars = '1234567890')
    {
        $hash = '';
        $max = strlen($chars) - 1;
        for ($i = 0; $i < $length; $i++) {
            $hash .= $chars[mt_rand(0, $max)];
        }
        return $hash;
    }
}

//判断是否是json
if (!function_exists('isJson')) {
    /**
     * 判断是否是json数据
     * @param $string
     * @return bool
     */
    function isJson($string)
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }
}

//获取登录充值中的链接地址
if (!function_exists('getLPUrl')) {
    /**
     * 获取相关URL
     * @param $string
     * @return bool
     */
    function getLPUrl($addr, $domain = '', $isHttps = false)
    {
        if (empty($addr)) {
            return false;
        }
        empty($domain) && $domain = \think\Config::get('lp_domain');
        return $isHttps ? 'https' : 'http' . '://' . $domain . $addr;
    }
}
if (!function_exists('get_client_ip')) {
    /**
     * Get client ip.
     *
     * @return string
     */
    function get_client_ip()
    {
        $unknown = 'unknown';
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] && strcasecmp($_SERVER['HTTP_X_FORWARDED_FOR'], $unknown)) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], $unknown)) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        return $ip;
    }
}

if (!function_exists('getGameNumByChannelPkgNum')) {
    /**
     * 通过渠道包ID获取游戏ID
     */
    function getGameNumByChannelPkgNum($channelPkgNum)
    {
        return (int)($channelPkgNum / 10000);
    }
}

if (!function_exists('getChannelNumByChannelPkgNum')) {
    /**
     * 通过渠道包ID获取渠道商ID
     */
    function getChannelNumByChannelPkgNum($channelPkgNum)
    {
        return (int)($channelPkgNum % 100);
    }
}