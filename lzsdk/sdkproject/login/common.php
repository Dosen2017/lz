<?php
/**
 * Created by PhpStorm.
 * User: sds
 * Date: 2019/2/27
 * Time: 11:53
 */

//发送短信
if (!function_exists('sendMessage')) {
    /**
     * 发送短信
     * 所有的参数都必须是字符串类型，json里面的key和value也都必须是字符串类型，就算只是个数字
     */
    function sendMessage($param, $phoneNum = '18588815049', $type , $templateId = 'SMS_71291372')
    {
        require(TOOLS_PATH . "taobao_sms". DS . "TopSdk.php");

        $confObj = new think\Config();

        $c = new TopClient;
        $c->appkey = $confObj->get('sms_appkey');
        $c->secretKey = $confObj->get('sms_secret');

        $req = new AlibabaAliqinFcSmsNumSendRequest;
        $req->setExtend($confObj->get('sms_extend'));
        $req->setSmsType($confObj->get('sms_type'));
        $req->setSmsFreeSignName($confObj->get('sms_signname'));
//        $req->setSmsParam("{\"name\":\"sds\",\"code\":\"111222\",\"n\":\"1\"}");
        $req->setSmsParam($param);
        $req->setRecNum($phoneNum);
        $req->setSmsTemplateCode($templateId);
        $resp = $c->execute($req);

        $respArr = (array)iterator_to_array($resp)['result'];

        if ($respArr['err_code'] == 0 && $respArr['success'] == 'true' ){
            return true;
        }

        //将发送短息失败的信息存入数据库
        $errData['phone_num'] = $phoneNum;
        $errData['type'] = $type;
        $errData['resp_data'] = json_encode((array)$resp);
        $errData['msg'] = $param;
        $errData['time'] = time();

        $errorMsgSend = new app\login\model\ErrorMesSend();
        $errorMsgSend->saveErrorData($errData);

        return false;

    }
}

//发送短信
if (!function_exists('sendMsg')) {
    /**
     * 发送短信
     * 所有的参数都必须是字符串类型，json里面的key和value也都必须是字符串类型，就算只是个数字
     */
    function sendMsg($param, $phoneNum = '18588815049', $type, $templateId = 'SMS_173176218')
    {
        require(TOOLS_PATH . "dysms". DS . "SmsDemo.php");

        $confObj = new think\Config();
        $demo = new SmsDemo(
            $confObj->get('dy_sms_accessKey'),
            $confObj->get('dy_sms_accessKeySecret')
        );

        $response = $demo->sendSms(
            $confObj->get('dy_sms_signname'), // 短信签名
            $templateId, // 短信模板编号
            $phoneNum, // 短信接收者
            $param
        );

        $resArr = (array)$response;
//var_dump($resArr);
        if ('OK' == $resArr['Message'] && 'OK' == $resArr['Code'] ){
            return true;
        }

        //将发送短息失败的信息存入数据库
        $errData['phone_num'] = $phoneNum;
        $errData['type'] = $type;
        $errData['resp_data'] = json_encode($resArr);
        $errData['msg'] = json_encode($param);
        $errData['time'] = time();

        $errorMsgSend = new app\login\model\ErrorMesSend();
        $errorMsgSend->saveErrorData($errData);

        //业务限流--将短信发送频率限制在正常的业务流控范围内，默认流控：短信验证码 ：使用同一个签名，对同一个手机号码发送短信验证码，支持1条/分钟，5条/小时 ，累计10条/天
        if ('isv.BUSINESS_LIMIT_CONTROL' == $resArr['Code'] ) {
            return '验证码发送频繁，请稍后再试!';
        }
        return false;
    }
}

if (!function_exists('checkIdCard')) {

    /**
     * 验证身份证号
     * @param  [type] $idcard [身份证号码]
     */
    function checkIdCard($idcard)
    {
        if (empty($idcard)) {
            return false;
        }
        $City = array(11 => "北京", 12 => "天津", 13 => "河北", 14 => "山西", 15 => "内蒙古", 21 => "辽宁", 22 => "吉林", 23 => "黑龙江", 31 => "上海", 32 => "江苏", 33 => "浙江", 34 => "安徽", 35 => "福建", 36 => "江西", 37 => "山东", 41 => "河南", 42 => "湖北", 43 => "湖南", 44 => "广东", 45 => "广西", 46 => "海南", 50 => "重庆", 51 => "四川", 52 => "贵州", 53 => "云南", 54 => "西藏", 61 => "陕西", 62 => "甘肃", 63 => "青海", 64 => "宁夏", 65 => "新疆", 71 => "台湾", 81 => "香港", 82 => "澳门", 91 => "国外");
        $iSum = 0;
        $idCardLength = strlen($idcard);
        //长度验证
        if (!preg_match('/^\d{17}(\d|x)$/i', $idcard) and !preg_match('/^\d{15}$/i', $idcard)) {
            return false;
        }
        //地区验证
        if (!array_key_exists(intval(substr($idcard, 0, 2)), $City)) {
            return false;
        }
        // 15位身份证验证生日，转换为18位
        if ($idCardLength == 15) {
            $sBirthday = '19' . substr($idcard, 6, 2) . '-' . substr($idcard, 8, 2) . '-' . substr($idcard, 10, 2);
            echo $sBirthday;
            die;
            $d = new \DateTime($sBirthday);
            $dd = $d->format('Y-m-d');
            if ($sBirthday != $dd) {
                return false;
            }
            $idcard = substr($idcard, 0, 6) . "19" . substr($idcard, 6, 9);//15to18
            $Bit18 = getVerifyBit($idcard);//算出第18位校验码
            $idcard = $idcard . $Bit18;
        }
        // 判断是否大于2078年，小于1900年
        $year = substr($idcard, 6, 4);
        if ($year < 1900 || $year > 2078) {
            return false;
        }

        //18位身份证处理
        $sBirthday = substr($idcard, 6, 4) . '-' . substr($idcard, 10, 2) . '-' . substr($idcard, 12, 2);
        $d = new \DateTime($sBirthday);
        $dd = $d->format('Y-m-d');
        if ($sBirthday != $dd) {
            return false;
        }
        //身份证编码规范验证
        $idcard_base = substr($idcard, 0, 17);
        if (strtoupper(substr($idcard, 17, 1)) != getVerifyBit($idcard_base)) {
            return false;
        }
        return true;
    }
}

if (!function_exists('getVerifyBit')) {
    // 计算身份证校验码，根据国家标准GB 11643-1999
    function getVerifyBit($idcard_base)
    {
        if (strlen($idcard_base) != 17) {
            return false;
        }
        //加权因子
        $factor = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
        //校验码对应值
        $verify_number_list = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');
        $checksum = 0;
        for ($i = 0; $i < strlen($idcard_base); $i++) {
            $checksum += substr($idcard_base, $i, 1) * $factor[$i];
        }
        $mod = $checksum % 11;
        $verify_number = $verify_number_list[$mod];
        return $verify_number;
    }
}

if (!function_exists('authcode')) {

    // $string： 明文 或 密文
    // $operation：DECODE表示解密,其它表示加密
    // $key： 密匙
    // $expiry：密文有效期
    function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0)
    {
        // 动态密匙长度，相同的明文会生成不同密文就是依靠动态密匙
        $ckey_length = 4;

        $confObj = new think\Config();

        // 密匙
        $key = md5($key ? $key : $confObj->get('auth_key'));

        // 密匙a会参与加解密
        $keya = md5(substr($key, 0, 16));
        // 密匙b会用来做数据完整性验证
        $keyb = md5(substr($key, 16, 16));
        // 密匙c用于变化生成的密文
        $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) : substr(md5(microtime()), -$ckey_length)) : '';
        // 参与运算的密匙
        $cryptkey = $keya . md5($keya . $keyc);
        $key_length = strlen($cryptkey);
        // 明文，前10位用来保存时间戳，解密时验证数据有效性，10到26位用来保存$keyb(密匙b)，解密时会通过这个密匙验证数据完整性
        // 如果是解码的话，会从第$ckey_length位开始，因为密文前$ckey_length位保存 动态密匙，以保证解密正确
        $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string;
        $string_length = strlen($string);
        $result = '';
        $box = range(0, 255);
        $rndkey = array();
        // 产生密匙簿
        for ($i = 0; $i <= 255; $i++) {
            $rndkey[$i] = ord($cryptkey[$i % $key_length]);
        }
        // 用固定的算法，打乱密匙簿，增加随机性，好像很复杂，实际上对并不会增加密文的强度
        for ($j = $i = 0; $i < 256; $i++) {
            $j = ($j + $box[$i] + $rndkey[$i]) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }
        // 核心加解密部分
        for ($a = $j = $i = 0; $i < $string_length; $i++) {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;
            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            // 从密匙簿得出密匙进行异或，再转成字符
            $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
        }
        if ($operation == 'DECODE') {
            // substr($result, 0, 10) == 0 验证数据有效性
            // substr($result, 0, 10) - time() > 0 验证数据有效性
            // substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16) 验证数据完整性
            // 验证数据有效性，请看未加密明文的格式
//            if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)) {
//                return substr($result, 26);
//            } else {
//                return '';
//            }

            if ( substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16) ) {
                if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0)) {
                    return substr($result, 26);
                } else {
                    return null;
                }
            } else {
                return '';
            }


        } else {
            // 把动态密匙保存在密文里，这也是为什么同样的明文，生产不同密文后能解密的原因
            // 因为加密后的密文可能是一些特殊字符，复制过程可能会丢失，所以用base64编码
            return $keyc . str_replace('=', '', base64_encode($result));
        }
    }
}

//发送短信
if (!function_exists('httpPostByJsonAndHeadAdd')) {

    function httpPostByJsonAndHeadAdd($url, $data, $isHttps = true, $headerAdd)
    {
        $ch = curl_init();

        //下面三行是https请求需要的参数设置
        if ($isHttps) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);    // https请求 不验证证书和hosts
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge(array('Content-type:application/json;charset=utf-8'), $headerAdd) );
        $rtn = curl_exec($ch);
//        $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if($errNo = curl_errno($ch))
            echoJson(0, 'curl error:' . $errNo);
        curl_close($ch);
        return $rtn;
    }

}