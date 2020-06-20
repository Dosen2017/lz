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

class WebchatPay extends Controller
{
    protected $apiHelper;

    public function __construct()
    {
        parent::__construct();
        $this->apiHelper = new helper\Api();
    }

    //微信获取订单信息的请求
    public function getOrderInfoWebchatPay()
    {

        $getPram = Request::instance()->get();

        //商户订单号，商户网站订单系统中唯一订单号，必填
        $checkParam['my_order_num'] = $getPram['my_order_num'];
        $checkParam['amount'] = $getPram['amount'];
        $checkParam['channel_pkg_num'] = $getPram['channel_pkg_num'];

        $checkParam['bundle'] = $getPram['bundle'];
        $checkParam['user_name'] = $getPram['user_name'];
        $checkParam['time'] = $getPram['time'];
        $checkParam['sign'] = $getPram['sign'];

        //检查参数是否为空
        !$this->apiHelper->checkParamIsNull($checkParam) && $this->apiHelper->echoJson(helper\ErrorOrders::ERROR_MISSED_PARAM);
        //2.验证与客户端的sign
        !$this->apiHelper->checkSign($checkParam) && $this->apiHelper->echoJson(helper\ErrorOrders::ERROR_SIGN);

        $chatPayCommon = new ChatPayCommon();

        //1.获取微信下单支付的参数
        $newPara = $chatPayCommon->_getWebChatOrderParam($checkParam);
        //2.把数组转化成xml格式
        $xmlData = $chatPayCommon->_getWeChatXML($newPara);
        //3.利用PHP的CURL包，将数据传给微信统一下单接口，返回正常的prepay_id
        $getData = $chatPayCommon->_postXmlCurl($xmlData);
        !$getData && $this->apiHelper->echoJson(helper\ErrorOrders::ERROR_WX_ORDER_CURL);
        $rectData = $chatPayCommon->_xml_to_data($getData);
        /*
        array(10) {
        ["return_code"]=>
  string(7) "SUCCESS"
        ["return_msg"]=>
  string(2) "OK"
        ["appid"]=>
  string(18) "wx6770628fc5429536"
        ["mch_id"]=>
  string(10) "1491397502"
        ["nonce_str"]=>
  string(16) "grVZYnn4zOieux6z"
        ["sign"]=>
  string(32) "ABBF81A4AF757A765921ABD746C1A2E9"
        ["result_code"]=>
  string(7) "SUCCESS"
        ["prepay_id"]=>
  string(36) "wx11170341456771a8bf96c60b1516910300"
        ["trade_type"]=>
  string(4) "MWEB"
        ["mweb_url"]=>
  string(118) "https://wx.tenpay.com/cgi-bin/mmpayweb-bin/checkmweb?prepay_id=wx11170341456771a8bf96c60b1516910300&package=1312546471"
}
        */
        if($rectData['return_code'] == "SUCCESS" && $rectData['result_code'] == "SUCCESS"){
            $curlRet = $chatPayCommon->_sendPrePayCurl($rectData['mweb_url'], getLPUrl(Config::get('success_url')) . '?bundle=' . $checkParam['bundle']);
            !$curlRet && $this->apiHelper->echoJson(helper\ErrorOrders::ERROR_WX_ORDER_OPEN_MWEBURL);

            echo $curlRet;exit; //打开微信支付

        } else {
            $this->apiHelper->echoJson(helper\ErrorOrders::ERROR_WX_ORDER);
        }
    }

}