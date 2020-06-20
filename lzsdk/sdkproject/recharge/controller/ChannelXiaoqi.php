<?php
/**
 * Created by phpStorm
 * User: php
 * Date: 20-06-04
 * Time: 15:42
 */
namespace app\recharge\controller;

use think\Request;
use app\recharge\model\ErrorOrders;
use app\recharge\helper;

//小七升级
class ChannelXiaoqi extends Recharge
{
    protected $public_key = '';
    protected $money;

    const F = 'fail';
    const SUCC = 'success';

    protected $IdToErrInfo = array(
        2 => 'sign_data_verify_failed',
        15 => 'encryp_data_decrypt_failed',
        3 => 'failed:game_orderid error',
        9 => 'failed:pay_price error'
    );

    protected function _exit($exitId)
    {
        header('Content-type: text/json');  //以纯json数据返回

        if($exitId != $this->success){
            $errorOrdersModel = new ErrorOrders;
            $errorOrdersModel->insertOrder($this->info['channel_pkg_num'], $exitId, $this->info['my_order_num'], $this->post, $this->order);

            echo in_array($exitId, array_keys($this->IdToErrInfo)) ? $this->IdToErrInfo[$exitId] : 'failed:order error';exit;
        }else {
            echo self::SUCC;exit;
        }
    }

    public function _getMoney()
    {
        return $this->money * 100;   //和一般的不同，这个是从这个加密字段里面获取的 encryp_data
    }

    public function _getParam()
    {
        //获取post过来的json数据
        $reqParam = Request::instance()->post();

        $this->post['encryp_data'] = $reqParam['encryp_data']; //这里是通过 RSA 加密的关键数据根据键名正序的形式加密
        $this->post['extends_info_data'] = $reqParam['extends_info_data']; //支付透传参数
        $this->post['game_area'] = $reqParam['game_area']; //游戏所在区服
        $this->post['game_level'] = $reqParam['game_level'];  //用户游戏角色等级
        $this->post['game_orderid'] = $reqParam['game_orderid']; //游戏订单号
        $this->post['game_role_id'] = $reqParam['game_role_id']; //游戏角色 ID 信息

        $this->post['game_role_name'] = $reqParam['game_role_name']; //游戏角色名称
        $this->post['sdk_version'] = $reqParam['sdk_version']; //表示当前回调中的游戏信息在哪个 sdk 版本中发起。
        $this->post['subject'] = $reqParam['subject']; //游戏商品简介
        $this->post['xiao7_goid'] = $reqParam['xiao7_goid']; //游戏订单在【小 7 服务器】中的唯一标识，由于这个是【小 7 服务器】订单中的唯一标识，建议游戏厂商在游戏服务器中保存当前字段内容用来标识当前的订单

        $this->post['sign_data'] = $reqParam['sign_data'];  //RSA 签名，是将除了当前参数 sign_data之外的所有参数，根据键名的正序形式组成的 url

file_put_contents("/tmp/ChannelXiaoqi.txt", date('Y-m-d H:i:s') . json_encode($reqParam) . "\n", FILE_APPEND);

        //为了代码统一，附加两个参数
        $customInfo  = explode('_', $this->post['extends_info_data']);

        //为了代码统一，附加两个参数
        $this->info['channel_pkg_num'] = $customInfo[0];  //在下单的时候传入
        $this->info['my_order_num'] = $customInfo[1];
//        $this->info['money'] = $this->post['total_fee'];            //支付金额,苹果支付时，需要通过支付凭证去获取，所以这里注释了。
        $this->info['pt_order_num'] = $this->post['xiao7_goid'];  //平台订单号,下面解析除receipt后会拿苹果的订单号重新赋值
        $this->info['pay_time'] = $this->post['time'];  //支付时间

        $this->info['os'] = $customInfo[2];   //可能用作切换参数

        $this->info['source'] = "sdk";//订单来源，苹果官方，各接入的渠道，都用sdk


        $this->_changeParamByAppAndNumAndOS();

        //判断是否有参数为空
        $checkParam = $this->post;
        !$this->apiHelper->checkParamIsNull($checkParam) && $this->_exit(helper\ErrorOrders::ERROR_MISSED_PARAM);
        //验证sign
        $this->checkSign($this->post); //&& $this->_exit(helper\ErrorOrders::ERROR_SIGN);
    }

    protected function checkSign($post_data)
    {
        $post_sign_data = base64_decode($post_data["sign_data"]);
        /************************************
        因为sign_data是不加入签名里面的
         ************************************/
        unset($post_data["sign_data"]);
        //按照参数名称的正序排序
        ksort($post_data);
        //对输入参数根据参数名排序，并拼接为key=value&key=value格式；
        $sourcestr=EncryptTT::http_build_query_noencode($post_data);

//file_put_contents("/tmp/ChannelXiaoqiSourceStr.txt", date('Y-m-d H:i:s') . $sourcestr . "\n", FILE_APPEND);

        //对数据进行验签，注意对公钥做格式转换
        $publicKey = EncryptTT::ConvertPublicKey($this->public_key);
        $verify = EncryptTT::Verify($sourcestr, $post_sign_data,$publicKey);
        //判断签名是否是正确
        if($verify!=1){
            $this->_exit(helper\ErrorOrders::ERROR_SIGN);
        }
        //对加密的encryp_data进行解密
        $post_encryp_data_decode = base64_decode($post_data["encryp_data"]);
        $decode_encryp_data = EncryptTT::PublickeyDecodeing($post_encryp_data_decode,$publicKey);
        parse_str($decode_encryp_data,$encryp_data_arr);
        if(!isset($encryp_data_arr["pay_price"]) || !isset($encryp_data_arr["guid"]) || !isset($encryp_data_arr["game_orderid"])){
            $this->_exit(helper\ErrorOrders::ERROR_OTHER_1);
        }

        $this->money = $encryp_data_arr["pay_price"];    //提取金额进行金额的验证
    }

    protected function _changeParam_880010403($getParam)
    {
        $this->public_key = '';

        if (self::OS_IOS == $getParam['os']) {
            $this->public_key = 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCpGHZ/o6q76+Hz47iSrWVKnWBpyshE55NAr9Yuk9LmIBQc/7JgGnczC1/+p/98lBbsTIpgXctXe2neXl7at+o2MQzpVEp5SYUhPUHTqMt44qHRoniD0qesOqhEt9hnK0hmGyZbQUlXNAtK9VNolHsQFacNILq97UO/TH4Yk0CxLwIDAQAB';
        }
    }

}

//定义一个小七解密类，将所以的方法放进去
class EncryptTT {

    public static function ConvertPublicKey($public_key){
        $public_key_string = "";
        $count=0;
        for($i=0;$i<strlen($public_key);$i++){
            if($count<64){
                $public_key_string.=$public_key[$i];
                $count++;
            }else{
                $public_key_string.=$public_key[$i]."\r\n";
                $count=0;
            }
        }
        $public_key_header = "-----BEGIN PUBLIC KEY-----\r\n";
        $public_key_footer = "\r\n-----END PUBLIC KEY-----";
        $public_key_string = $public_key_header.$public_key_string.$public_key_footer;
        return $public_key_string;
    }

    public static function Verify($sourcestr, $sign_dataature, $publickey){
        $pkeyid = openssl_get_publickey($publickey);
        $verify = openssl_verify($sourcestr, $sign_dataature, $pkeyid);
        openssl_free_key($pkeyid);
        return $verify;
    }

    public static function PublickeyDecodeing($crypttext, $publickey){
        $pubkeyid = openssl_get_publickey($publickey);
        if (openssl_public_decrypt($crypttext, $sourcestr, $pubkeyid, OPENSSL_PKCS1_PADDING)){
            return $sourcestr;
        }
        return FALSE;
    }

    public static function ReturnResult($text){
        echo $text;
        exit();
    }

    public static function PingErrorRecorder($recorderData,$is_out=0){
        $ping_error_fp=fopen("Ping_Error.txt","a+");
        $recorderData['dateTime']=date("Y-m-d H:i:s",time());
        $recorderStr="------------------------------------------\r\n";
        if(is_array($recorderData)){
            foreach($recorderData as $k => $v){
                if(!empty($v)){
                    $temp=@iconv("utf-8","gb2312//ignore",$v);
                    $recorderStr.="{$k}:{$temp}\r\n";
                }
            }
        }else{
            if(!empty($recorderData)){
                $temp=@iconv("utf-8","gb2312//ignore",$recorderData);
                $recorderStr.=$temp."\r\n";
            }
        }
        $str=<<<EOT
{$recorderStr}
EOT;
        fwrite($ping_error_fp,$str);
        fclose($ping_error_fp);
        if(!empty($is_out)){
            exit();
        }
    }

    public static function sendCurlGet($url,$get_data=array()){
        if(!empty($get_data)){
            $get_data_str=http_build_query($get_data);
            $url=preg_match("/\?/",$url) ? $url."&".$get_data_str : $url."?".$get_data_str;
        }
        $ch=curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLOPT_HEADER,0);
        $output=curl_exec($ch);
        $http_state=curl_getinfo($ch);
        curl_close($ch);
        return array($output,$http_state);
    }

    public static function http_build_query_noencode($queryArr){
        if(empty($queryArr)){
            return "";
        }
        $returnArr=array();
        foreach($queryArr as $key => $value){
            $returnArr[]=$key."=".$value;
        }
        return implode("&",$returnArr);
    }
}


