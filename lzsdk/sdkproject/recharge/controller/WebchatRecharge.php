<?php
/**
 * Created by sudesheng.
 * User: 2066815257@qq.com
 * Date: 17-06-27
 * Time: 下午 14:57
 *
 * IOS充值验证苹果充值参数receipt 和回调CP
 */
namespace app\recharge\controller;

use app\recharge\helper;
use app\recharge\model\ErrorOrders;
use think\Config;

class WebchatRecharge extends Recharge
{

    const RETURN_CODE_SUCCESS = 'SUCCESS';
    const RETURN_CODE_FAIL = 'FAIL';

    protected $info;
    protected $chatPayCommon;

    protected $allParams;

    public function __construct()
    {
        parent::__construct();
        $this->apiHelper = new helper\Api();
        $this->chatPayCommon = new ChatPayCommon();
    }

    public function _getMoney()
    {
        return $this->info['money'];
    }

    public function index() {
        // 1.获取参数 , 判断参数是否为空
        $this->_getParam();
        // 2.验证SIGN
        $this->_checkSign();
        // 3.验证订单是否存在
        $this->_checkOrderNumIsExist();
        // 4.验证金钱是否正确
        $this->_checkMoney();

        //4.1 设置回调后的数据
        $this->_setCallBackData();

        // 5.发起到CP的回调状态
        $this->_requestCallBackToCP();
    }

    /**
     *
    <xml>
        <appid><![CDATA[wx6770628fc5429536]]></appid>
        <attach><![CDATA[880010402]]></attach>
        <bank_type><![CDATA[PAB_CREDIT]]></bank_type>
        <cash_fee><![CDATA[1]]></cash_fee>
        <fee_type><![CDATA[CNY]]></fee_type>
        <is_subscribe><![CDATA[N]]></is_subscribe>
        <mch_id><![CDATA[1491397502]]></mch_id>
        <nonce_str><![CDATA[017C9E78FF0FFD881210BB9FEE8E04F3]]></nonce_str>
        <openid><![CDATA[oOEaSxP2gmdzpKYT00pfp7pK4puc]]></openid>
        <out_trade_no><![CDATA[2004140913416734678]]></out_trade_no>
        <result_code><![CDATA[SUCCESS]]></result_code>
        <return_code><![CDATA[SUCCESS]]></return_code>
        <sign><![CDATA[6F6201B946A96369EFD54448C5CEB5EA]]></sign>
        <time_end><![CDATA[20200414091350]]></time_end>
        <total_fee>1</total_fee>
        <trade_type><![CDATA[MWEB]]></trade_type>
        <>transaction_id<![CDATA[4200000522202004140990926670]]></transaction_id>
    </xml>
     */

    public function _getParam()
    {

        $params = $this->allParams = $this->chatPayCommon->_xml_to_data(file_get_contents("php://input"));

//file_put_contents('/tmp/WebchatRecharge2.txt', date('Y-m-d H:i:s', time()) . '__' . file_get_contents("php://input") . '\r\n', FILE_APPEND);

//        $params = $this->allParams =  $_GET;

        $this->post['return_code'] = $params['return_code']; //SUCCESS/FAIL 此字段是通信标识，非交易标识，交易是否成功需要查看result_code来判断

        if ($this->post['return_code'] != self::RETURN_CODE_SUCCESS) {
//            echo 9999;exit;
            $this->_exit($this->success);  //还是返回成功，表示收到了支付请求，SDK服务器就不会在周期内进行重复通知
        }

//        $this->post['return_msg'] = $params['return_msg'];  //返回信息，如非空，为错误原因签名失败 参数格式校验错误

        //以下字段在return_code为SUCCESS的时候有返回
        $this->post['appid'] = $params['appid']; //微信分配的公众账号ID（企业号corpid即为此appId）
        $this->post['mch_id'] = $params['mch_id']; //微信支付分配的商户号
//        $this->post['device_info'] = $params['device_info']; //微信支付分配的终端设备号，
        $this->post['nonce_str'] = $params['nonce_str']; //随机字符串，不长于32位
        $this->post['sign'] = $params['sign']; //签名
//        $this->post['sign_type'] = $params['sign_type']; //签名类型，目前支持HMAC-SHA256和MD5，默认为MD5
        $this->post['result_code'] = $params['result_code']; //SUCCESS/FAIL
//        $this->post['err_code'] = $params['err_code']; //错误代码
//        $this->post['err_code_des'] = $params['err_code_des']; //错误返回的信息描述
        $this->post['openid'] = $params['openid']; //用户在商户appid下的唯一标识

        $this->post['is_subscribe'] = $params['is_subscribe']; //用户是否关注公众账号，Y-关注，N-未关注，仅在公众账号类型支付有效
        $this->post['trade_type'] = $params['trade_type']; //JSAPI、NATIVE、APP
        $this->post['bank_type'] = $params['bank_type']; //银行类型，采用字符串类型的银行标识
        $this->post['total_fee'] = $params['total_fee']; //订单总金额，单位为分
//        $this->post['settlement_total_fee'] = $params['settlement_total_fee']; //应结订单金额=订单金额-非充值代金券金额，应结订单金额<=订单金额

        $this->post['fee_type'] = $params['fee_type']; //货币类型，符合ISO4217标准的三位字母代码，默认人民币：CNY
        $this->post['cash_fee'] = $params['cash_fee']; //现金支付金额订单现金支付金额
//        $this->post['cash_fee_type'] = $params['cash_fee_type']; //货币类型，符合ISO4217标准的三位字母代码，默认人民币：CNY
//        $this->post['coupon_fee'] = $params['coupon_fee']; //代金券金额<=订单金额，订单金额-代金券金额=现金支付金额
//        $this->post['coupon_count'] = $params['coupon_count']; //代金券使用数量

//        $this->post['coupon_type_$n'] = $params['coupon_type_$n']; //CASH--充值代金券NO_CASH---非充值代金券订单使用代金券时有返回（取值：CASH、NO_CASH）。$n为下标,从0开始编号，举例：coupon_type_0
//        $this->post['coupon_id_$n'] = $params['coupon_id_$n']; //代金券ID,$n为下标，从0开始编号
//        $this->post['coupon_fee_$n'] = $params['coupon_fee_$n']; //单个代金券支付金额,$n为下标，从0开始编号

        $this->post['transaction_id'] = $params['transaction_id']; //微信支付订单号
        $this->post['out_trade_no'] = $params['out_trade_no']; //商户订单号
        $this->post['attach'] = $params['attach']; //商家数据包 【传APP_ID,游戏的应用ID】

        $this->post['time_end'] = $params['time_end']; //支付完成时间，格式为yyyyMMddHHmmss，如2009年12月25日9点10分10秒表示为20091225091010

        //为了代码统一，附加两个参数
        $this->info['channel_pkg_num'] = $this->post['attach'];  //渠道包ID
        $this->info['my_order_num'] = $this->post['out_trade_no'];  //订单号
        $this->info['money'] = $this->post['total_fee'];            //支付金额
        $this->info['pt_order_num'] = $this->post['transaction_id'];  //微信支付订单号
        $this->info['pay_time'] = strtotime($this->post['time_end']);  //支付时间
        $this->info['source'] = "WeChatPay_" . Config::get('mch_id') . "_" . $this->post['trade_type'];  //订单来源


        //去掉那些不是必传的字段
        $nullParam = array('return_msg', 'device_info', 'sign_type', 'err_code', 'err_code_des',
        'settlement_total_fee','fee_type', 'cash_fee_type','coupon_fee','coupon_count',
        'coupon_type_$n', 'coupon_id_$n',
            'attach',
        'coupon_fee_$n' );
        $checkParam = $this->post;
        foreach ($nullParam as $k => $v) {
            unset($checkParam[$v]);
        }
        //判断是否有参数为空
        !$this->apiHelper->checkParamIsNull($checkParam) && $this->_exit(helper\ErrorOrders::ERROR_MISSED_PARAM);
    }

    protected function _checkMoney()
    {
        //客户端下单时传过来的是分, 微信收到的金额也是分
        //验证金额
        if ( $this->_getMoney() != $this->order['amount'] ) {
            //充值金额对不上
            $this->_exit(helper\ErrorOrders::ERROR_MONEY_NOT_SAME);
        }
    }

    protected function _checkSign()
    {
        $signParam = $this->allParams;
        unset($signParam['sign']);
//        $chatPayCommon = new ChatPayCommon();
        $checkSign = $this->chatPayCommon->_produceWeChatSign($signParam);

        if ( $this->post['sign'] != $checkSign ) {
            $this->_exit(helper\ErrorOrders::ERROR_SIGN);
        }

    }

    //因为是支付宝回调，是直接返回给支付宝的，按照支付宝的方式返回字符串
    protected function _exit($exitId, $fail = 'fail')
    {
        $return = $this->success;

        $retunData['return_code'] = self::RETURN_CODE_SUCCESS;
        $retunData['return_msg'] = 'OK';
        if($exitId != $this->success){
            $errorOrdersModel = new ErrorOrders;
            $errorOrdersModel->insertOrder($this->info['channel_pkg_num'], $exitId, $this->info['my_order_num'], $this->post, $this->order);

            $retunData['return_code'] = self::RETURN_CODE_FAIL;
            $retunData['return_msg'] = 'pay_fail';
        }
        echo $this->chatPayCommon->_getWeChatXML($retunData);exit;
    }

}