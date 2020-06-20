<?php
/**
 * Created by sudesheng.
 * User: 2066815257@qq.com
 * Date: 20-4-13
 * Time: 下午2:46
 *
 * 微信充值下单接口
 *
 */
namespace app\recharge\controller;

use think\Config;
use think\Controller;
use think\Request;

use app\recharge\helper;

class ChatPayCommon extends Controller
{
    const WEB_CHAT_REQUEST_ORDER_URL = 'https://api.mch.weixin.qq.com/pay/unifiedorder';

    public function _getWebChatOrderParam($checkParam)
    {
        //生成预支付交易单的必选参数:
        $newPara = [];
        //应用ID
        $newPara["appid"] = Config::get('app_id');
        //商户号
        $newPara["mch_id"] = Config::get('mch_id');
        //设备号
//        $newPara["device_info"] = "WEB";
        //随机字符串,这里推荐使用函数生成
        $newPara["nonce_str"] = strtoupper(md5(random(32)));

        $newPara["sign_type"] = 'MD5';

        //商品描述
        $newPara["body"] = "-游戏充值";

        //商品详情
//        $newPara["detail"] = "";

        //附加数据，在查询API和支付通知中原样返回，可作为自定义参数使用。
        //现在传渠道包ID
        $newPara['attach'] = $checkParam['channel_pkg_num'];

        //商户订单号,这里是商户自己的内部的订单号
        $newPara["out_trade_no"] = $checkParam['my_order_num'];

        //货币类型
//        $newPara["fee_type"] = $requestParam['fee_type'];

        //总金额 单位为分
        $newPara["total_fee"] = $checkParam['amount'];
        //终端IP
        $newPara["spbill_create_ip"] = Request::instance()->ip();

        //交易开始时间
//        $newPara["time_start"] = $_SERVER["time_start"];
//        //交易结束时间
//        $newPara["time_expire"] = $_SERVER["time_expire"];
//        //商品标记
//        $newPara["goods_tag"] = $_SERVER["goods_tag"];

        //通知地址，注意，这里的url里面不要加参数
        $newPara["notify_url"] = getLPUrl(Config::get('notify_url'));
        //交易类型
        $newPara["trade_type"] = "MWEB";   //H5支付的交易类型为MWEB  APP支付为APP

        //product_id
//        $newPara["product_id"] = "";
//        //limit_pay
//        $newPara["limit_pay"] = "";
//        //openid
//        $newPara["openid"] = "";
//        //scene_info
//        $newPara["scene_info"] = "";

        //第一次签名
        $newPara["sign"] = $this->_produceWeChatSign($newPara);

        return $newPara;
    }

    //第一次签名的函数produceWeChatSign
    public function _produceWeChatSign($newPara, $key = ''){
        empty($key) && $key = Config::get('app_key');
        $stringA = $this->_getSignContent($newPara);
        $stringSignTemp=$stringA."&key=" . $key;
        return strtoupper(MD5($stringSignTemp));
    }

    //生成xml格式的函数
    public function _getWeChatXML($params){
        if(!is_array($params)|| count($params) <= 0) {
            return false;
        }
        $xml = "<xml>";
        foreach ($params as $key=>$val) {
            if (is_numeric($val)) {
                $xml.="<".$key.">".$val."</".$key.">";
            } else {
                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
            }
        }
        $xml.="</xml>";
        return $xml;
    }

    /**
     * 以post方式提交xml到对应的接口url
     *
     * @param string $xml  需要post的xml数据
     * @param string $url  url
     * @param bool $useCert 是否需要证书，默认不需要
     * @param int $second   url执行超时时间，默认30s
     * @throws WxPayException
     */
    public function _postXmlCurl($xml, $url = self::WEB_CHAT_REQUEST_ORDER_URL, $useCert = false, $second = 30){
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,2);
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        if($useCert == true){
            //设置证书
            //使用证书：cert 与 key 分别属于两个.pem文件
            curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
            //curl_setopt($ch,CURLOPT_SSLCERT, WxPayConfig::SSLCERT_PATH);
            curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
            //curl_setopt($ch,CURLOPT_SSLKEY, WxPayConfig::SSLKEY_PATH);
        }
        //post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        //运行curl
        $data = curl_exec($ch);
        //返回结果
        if($data){
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            curl_close($ch);
            return false;
        }
    }

    /**
     * 将xml转为array
     * @param string $xml
     * return array
     */
    public function _xml_to_data($xml){
        if(!$xml){
            return false;
        }
        //将XML转为array
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $data;
    }

    public function _getSignContent($data)
    {
        $str = '';
        ksort($data);
        foreach($data as $k => $v)
        {
            //参数为空，不参与签名
            if (is_null($v)) {
                continue;
            }
            $str .= $k . '=' . $v . '&';
        }

        return substr($str, 0, -1);
    }

     public function _sendPrePayCurl($url, $referUrl) {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);    // https请求 不验证证书和hosts
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
        curl_setopt ($ch, CURLOPT_REFERER, $referUrl);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type:application/x-www-form-urlencoded;charset=utf-8'));
        $file_contents = curl_exec($ch);

         if($errNo = curl_errno($ch)) {
             curl_close($ch);    //任何时候都要先关闭链接
             return false;
         }
         curl_close($ch);

        return $file_contents;
    }

}