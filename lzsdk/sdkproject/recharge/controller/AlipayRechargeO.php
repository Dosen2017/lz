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

use think\Config;
use think\Request;

use app\recharge\helper;
use app\recharge\model\ErrorOrders;

require_once(dirname ( __FILE__ ).DIRECTORY_SEPARATOR."aliwappay_o" . DIRECTORY_SEPARATOR . "lib" . DIRECTORY_SEPARATOR . "alipay_notify.class.php");

class AlipayRechargeO extends Recharge
{

    /**
     * 程序执行完后必须打印输出“success”（不包含引号）。如果商户反馈给支付宝的字符不是success这7个字符，支付宝服务器会不断重发通知，直到超过24小时22分钟。一般情况下，25小时以内完成8次通知（通知的间隔频率一般是：4m,10m,10m,1h,2h,6h,15h）；
     */
    protected $success =  'success'; //成功  支付宝回调返回的字符串

    public function __construct()
    {
        parent::__construct();
    }

    public function index() {
        // 1.获取参数 , 判断参数是否为空
        $this->_getParam();
        // 2.判断订单状态是否正确
        $this->_checkOrderPayIsSuccess();
        // 3.验证SIGN
        $this->_checkSign();
        // 4.验证订单是否存在
        $this->_checkOrderNumIsExist();

        //4.1 设置回调后的数据
        $this->_setCallBackData();

        // 5.根据支付宝的验证说明验证订单
        $this->_checkOrderWithAlipayCondition();
        // 6.发起到CP的回调状态
        $this->_requestCallBackToCP();
    }

    public function _getMoney()
    {
        return $this->info['money'];
    }

    /**
     * {"payment_type":"1","trade_no":"2020041022001417881442984517","subject":"pay_2004101022267043773","buyer_email":"206***@qq.com","gmt_create":"2020-04-10 10:23:01","notify_type":"trade_status_sync","quantity":"1","out_trade_no":"2004101022267043773","seller_id":"2088821608414125","notify_time":"2020-04-10 10:23:22","body":"5555","trade_status":"TRADE_SUCCESS","is_total_fee_adjust":"N","total_fee":"0.10","gmt_payment":"2020-04-10 10:23:22","seller_email":"yuq@lezhongwan.com","price":"0.10","buyer_id":"2088012698017880","notify_id":"2020041000222102322017881411132317","use_coupon":"N","sign_type":"RSA","sign":"IrWkkwCsVHRqbEk8NUOw5xPsqJlY\/h5q5GCWFa0\/HTGx\/D57lrQq5zZQZcLLLWKx2vZbQiYbtim2l\/hE9bj3xiEhHkCyIOjX91Z9z2JP20Id2AyvWfzfMshVOfbnL2a0wcwARxTQkonZjtMNuYm63Pzds7IPY+pMvCJrYF6qn08="}
    @{
    "payment_type": "1",
    "trade_no": "2020041022001417881442984517",
    "subject": "pay_2004101022267043773",
    "buyer_email": "206***@qq.com",
    "gmt_create": "2020-04-10 10:23:01",
    "notify_type": "trade_status_sync",
    "quantity": "1",
    "out_trade_no": "2004101022267043773",
    "seller_id": "2088821608414125",
    "notify_time": "2020-04-10 10:23:22",
    "body": "5555",
    "trade_status": "TRADE_SUCCESS",
    "is_total_fee_adjust": "N",
    "total_fee": "0.10",
    "gmt_payment": "2020-04-10 10:23:22",
    "seller_email": "yuq@lezhongwan.com",
    "price": "0.10",
    "buyer_id": "2088012698017880",
    "notify_id": "2020041000222102322017881411132317",
    "use_coupon": "N",
    "sign_type": "RSA",
    "sign": "IrWkkwCsVHRqbEk8NUOw5xPsqJlY\/h5q5GCWFa0\/HTGx\/D57lrQq5zZQZcLLLWKx2vZbQiYbtim2l\/hE9bj3xiEhHkCyIOjX91Z9z2JP20Id2AyvWfzfMshVOfbnL2a0wcwARxTQkonZjtMNuYm63Pzds7IPY+pMvCJrYF6qn08="
    }
    @{"payment_type":"1","trade_no":"2020041022001417881442967223","subject":"pay_2004101205454351418","buyer_email":"206***@qq.com","gmt_create":"2020-04-10 12:05:58","notify_type":"trade_status_sync","quantity":"1","out_trade_no":"2004101205454351418","seller_id":"2088821608414125","notify_time":"2020-04-10 12:05:59","body":"5555","trade_status":"TRADE_SUCCESS","is_total_fee_adjust":"N","total_fee":"0.01","gmt_payment":"2020-04-10 12:05:59","seller_email":"yuq@lezhongwan.com","price":"0.01","buyer_id":"2088012698017880","notify_id":"2020041000222120559017881411444423","use_coupon":"N","sign_type":"RSA","sign":"iKh5orZbpvF9K6Wpb+OK+wvssTSDzodYGI4Xs0pvUIwpMnIu5rUlCzKUyGrY1TfxowHCRBvAjzk5VfliNNrFSvwoeVL2Q\/W8PdPummW8H19NDS8vRCfqx2Yos92M0M5Ln\/qeTrXZn17\/sjF8xdj\/e1CK5PTWIkBPgSm3J9sj48Q="}
    {
    "payment_type": "1",
    "trade_no": "2020041022001417881442967223",
    "subject": "pay_2004101205454351418",
    "buyer_email": "206***@qq.com",
    "gmt_create": "2020-04-10 12:05:58",
    "notify_type": "trade_status_sync",
    "quantity": "1",
    "out_trade_no": "2004101205454351418",
    "seller_id": "2088821608414125",
    "notify_time": "2020-04-10 12:05:59",
    "body": "5555",
    "trade_status": "TRADE_SUCCESS",
    "is_total_fee_adjust": "N",
    "total_fee": "0.01",
    "gmt_payment": "2020-04-10 12:05:59",
    "seller_email": "yuq@lezhongwan.com",
    "price": "0.01",
    "buyer_id": "2088012698017880",
    "notify_id": "2020041000222120559017881411444423",
    "use_coupon": "N",
    "sign_type": "RSA",
    "sign": "iKh5orZbpvF9K6Wpb+OK+wvssTSDzodYGI4Xs0pvUIwpMnIu5rUlCzKUyGrY1TfxowHCRBvAjzk5VfliNNrFSvwoeVL2Q\/W8PdPummW8H19NDS8vRCfqx2Yos92M0M5Ln\/qeTrXZn17\/sjF8xdj\/e1CK5PTWIkBPgSm3J9sj48Q="
    }
     */
    public function _getParam()
    {
        $params = Request::instance()->post();
//file_put_contents('/tmp/AlipayRechargeO.txt', date('Y-m-d H:i:s', time()) . '__' . json_encode($params) . '\r\n', FILE_APPEND);
        //service   和   trade_type 是一个意思，所以此时service是可以为空

        //基本参数
        $this->post['notify_time'] = $params['notify_time']; //通知时间
        $this->post['notify_type'] = $params['notify_type']; //通知类型
        $this->post['notify_id'] = $params['notify_id']; //通知校验ID
        $this->post['sign_type'] = $params['sign_type']; //商户生成签名字符串所使用的签名算法类型，目前支持RSA2和RSA，推荐使用RSA2
        $this->post['sign'] = $params['sign']; //请参考异步返回结果的验签

        //业务参数
        $this->post['out_trade_no'] = $params['out_trade_no']; //商户网站唯一订单号
        $this->post['subject'] = $params['subject']; //商品名称
        $this->post['payment_type'] = $params['payment_type']; //支付类型
        $this->post['trade_no'] = $params['trade_no']; //支付宝交易号
        $this->post['trade_status'] = $params['trade_status']; //交易状态
        $this->post['gmt_create'] = $params['gmt_create']; //交易创建时间
        $this->post['gmt_payment'] = $params['gmt_payment']; //交易付款时间

        $this->post['gmt_close'] = $params['gmt_close']; //交易关闭时间
        $this->post['seller_email'] = $params['seller_email']; //卖家支付宝账号

        $this->post['buyer_email'] = $params['buyer_email']; //买家支付宝账号
        $this->post['seller_email'] = $params['seller_email']; //卖家支付宝账号

        $this->post['seller_id'] = $params['seller_id']; //卖家支付宝账户号
        $this->post['buyer_id'] = $params['buyer_id']; //买家支付宝账户号
        $this->post['price'] = $params['price']; //商品单价
        $this->post['total_fee'] = $params['total_fee']; //交易金额

        $this->post['quantity'] = $params['quantity']; //购买数量
        $this->post['body'] = $params['body']; //商品描述

        $this->post['discount'] = $params['discount']; //折扣
        $this->post['is_total_fee_adjust'] = $params['is_total_fee_adjust']; //是否调整总价

        $this->post['use_coupon'] = $params['use_coupon']; //是否使用红包买家

        $this->post['refund_status'] = $params['refund_status']; //退款状态
        $this->post['gmt_refund'] = $params['gmt_refund']; //退款时间

        //关键参数
        $this->info['channel_pkg_num'] = $this->post['body'];  //渠道包ID
        $this->info['my_order_num'] = $this->post['out_trade_no'];  //订单号
        $this->info['money'] = $this->post['total_fee'] * 100;  //转换成分 ，和获取订单号时，传过来的一致
        $this->info['pt_order_num'] = $this->post['trade_no'];
        $this->info['pay_time'] = strtotime($this->post['gmt_payment']);
        $this->info['source'] = "Alipay_" . Config::get('zfb_partner');  //订单来源

        //判断是否有参数为空，这里不做判空，在第二步判断sign就可以了
//        !$this->apiHelper->checkParamIsNull($checkParam) && $this->_exit(helper\ErrorOrders::ERROR_MISSED_PARAM);

    }

    protected function _checkOrderPayIsSuccess()
    {
        /**
         * 状态TRADE_SUCCESS的通知触发条件是商户签约的产品支持退款功能的前提下，买家付款成功；
         * 交易状态TRADE_FINISHED的通知触发条件是商户签约的产品不支持退款功能的前提下，买家付款成功；或者，商户签约的产品支持退款功能的前提下，交易已经成功并且已经超过可退款期限。
        */

        //符合成功的支付的状态
        $successStatus = array(
            'TRADE_SUCCESS',
            'TRADE_FINISHED'
        );

        if ( !in_array($this->post['trade_status'], $successStatus) ) {
            //记录支付宝支付状态错误后，返回的错误
            $errorOrdersModel = new ErrorOrders;
            $errorOrdersModel->insertOrder($this->info['channel_pkg_num'], helper\ErrorOrders::ERROR_ALIPAY_STATUS, $this->info['my_order_num'], $this->post, $this->order);

            $this->_exit($this->success);  //还是返回成功，表示收到了支付请求，SDK服务器就不会在周期内进行重复通知
        }
    }

    protected function _checkSign()
    {
//        $params = Request::instance()->post();  //为防止漏掉参数，再重新获取一遍

        $alipay_config = include dirname ( __FILE__ ).DIRECTORY_SEPARATOR."aliwappay_o" . DIRECTORY_SEPARATOR ."alipay.config.php";
        $alipayNotify = new \AlipayNotify($alipay_config);
        $verify_result = $alipayNotify->verifyNotify();

        if (!$verify_result) {
            $this->_exit(helper\ErrorOrders::ERROR_SIGN);
        }

    }

    protected function _checkOrderWithAlipayCondition()
    {

        /**
         *  1.商户需要验证该通知数据中的out_trade_no是否为商户系统中创建的订单号，  【 在第四部中查询订单是否存在中已经验证 】
         *  2、判断total_amount是否确实为该订单的实际金额（即商户订单创建时的金额），3、校验通知中的seller_id（或者seller_email) 是否为out_trade_no这笔单据的对应的操作方（有的时候，一个商户可能有多个seller_id/seller_email），
         *  4、验证app_id是否为该商户本身。上述1、2、3、4有任何一个验证不通过，则表明本次通知是异常通知，务必忽略。在上述验证通过后商户必须根据支付宝不同类型的业务通知，正确的进行不同的业务处理，并且过滤重复的通知结果数据。在支付宝的业务通知中，只有交易通知状态为TRADE_SUCCESS或TRADE_FINISHED时，支付宝才会认定为买家付款成功。
         *
         *  注意：
         *  状态TRADE_SUCCESS的通知触发条件是商户签约的产品支持退款功能的前提下，买家付款成功；
         *  交易状态TRADE_FINISHED的通知触发条件是商户签约的产品不支持退款功能的前提下，买家付款成功；或者，商户签约的产品支持退款功能的前提下，交易已经成功并且已经超过可退款期限。
         */
        //2.验证金额
        if ( $this->_getMoney()!= $this->order['amount'] ) {
            //充值金额对不上
            $this->_exit(helper\ErrorOrders::ERROR_MONEY_NOT_SAME);
        }

        //3.验证卖家是否正确
        $alipay_config = include dirname ( __FILE__ ).DIRECTORY_SEPARATOR."aliwappay_o" . DIRECTORY_SEPARATOR ."alipay.config.php";
        if (!in_array($this->post['seller_id'], explode(',', $alipay_config['seller_id']))) {
            $this->_exit(helper\ErrorOrders::ERROR_ALIPAY_SELLER_ID);
        }

        //4.验证应用ID是否正确，此处是验证支付宝的应用ID 和游戏的应用ID是有区别的，旧版没有app_id参数，所以这里不做判断
//        if ($this->post['app_id'] != $alipay_config['app_id'] ) {
////            $this->_exit(helper\ErrorOrders::ERROR_ALIPAY_APP_ID);
////        }

    }

    //因为是支付宝回调，是直接返回给支付宝的，按照支付宝的方式返回字符串
    protected function _exit($exitId, $fail = 'fail')
    {

        $return = $this->success;

        if($exitId != $this->success){
            $errorOrdersModel = new ErrorOrders;
            $errorOrdersModel->insertOrder($this->info['channel_pkg_num'], $exitId, $this->info['my_order_num'], $this->post, $this->order);
            $return = $fail;
        }
        echo $return; exit;
    }
}