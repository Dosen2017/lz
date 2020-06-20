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

use think\Request;

use app\recharge\helper;
use app\recharge\model\ErrorOrders;

require_once dirname ( __FILE__ ).DIRECTORY_SEPARATOR.'aliwappay/wappay/service/AlipayTradeService.php';

class AlipayRecharge extends Recharge
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

    public function _getParam()
    {
        $params = Request::instance()->post();
file_put_contents('/tmp/ChannelAplipayParam.txt', date('Y-m-d H:i:s', time()) . '__' . json_encode($params) . '\r\n', FILE_APPEND);
        //service   和   trade_type 是一个意思，所以此时service是可以为空
        $this->post['notify_time'] = $params['notify_time']; //通知时间
        $this->post['notify_type'] = $params['notify_type']; //通知类型
        $this->post['notify_id'] = $params['notify_id']; //通知校验ID
        $this->post['app_id'] = $params['app_id']; //支付宝分配给开发者的应用Id
        $this->post['charset'] = $params['charset']; //编码格式，如utf-8、gbk、gb2312等
        $this->post['version'] = $params['version']; //调用的接口版本，固定为：1.0
        $this->post['sign_type'] = $params['sign_type']; //商户生成签名字符串所使用的签名算法类型，目前支持RSA2和RSA，推荐使用RSA2
        $this->post['sign'] = $params['sign']; //请参考异步返回结果的验签
        $this->post['trade_no'] = $params['trade_no']; //支付宝交易号
        $this->post['out_trade_no'] = $params['out_trade_no']; //商户订单号

        $this->post['out_biz_no'] = $params['out_biz_no']; //商户业务号  【否】
        $this->post['buyer_id'] = $params['buyer_id']; //买家支付宝用户号
        $this->post['buyer_logon_id'] = $params['buyer_logon_id']; //买家支付宝账号
        $this->post['seller_id'] = $params['seller_id']; //卖家支付宝用户号
        $this->post['seller_email'] = $params['seller_email']; //卖家支付宝账号

        $this->post['trade_status'] = $params['trade_status']; //交易状态
        $this->post['total_amount'] = $params['total_amount']; //订单金额  本次交易支付的订单金额，单位为人民币（元）
        $this->post['receipt_amount'] = $params['receipt_amount']; //实收金额
        $this->post['invoice_amount'] = $params['invoice_amount']; //开票金额

        $this->post['buyer_pay_amount'] = $params['buyer_pay_amount']; //付款金额
        $this->post['point_amount'] = $params['point_amount']; //集分宝金额
        $this->post['refund_fee'] = $params['refund_fee']; //总退款金额
        $this->post['subject'] = $params['subject']; //订单标题

        $this->post['body'] = $params['body']; //该订单的备注、描述、明细等。对应请求时的body参数，原样通知回来

        $this->post['gmt_create'] = $params['gmt_create']; //交易创建时间
        $this->post['gmt_payment'] = $params['gmt_payment']; //交易付款时间
        $this->post['gmt_refund'] = $params['gmt_refund']; // 交易退款时间
        $this->post['gmt_close'] = $params['gmt_close'];  //交易结束时间
        $this->post['fund_bill_list'] = $params['fund_bill_list']; //支付金额信息

        $this->post['passback_params'] = $params['passback_params']; //回传参数
        $this->post['voucher_detail_list'] = $params['voucher_detail_list']; //优惠券信息


//file_put_contents('/tmp/ChannelAplipay.txt', date('Y-m-d H:i:s', time()) . '__' . json_encode($this->post), FILE_APPEND);
        //为了代码统一，附加两个参数
        $this->info['channel_pkg_num'] = $this->post['body'];  //在下单的时候传入
        $this->info['my_order_num'] = $this->post['out_trade_no'];
        $this->info['money'] = $this->post['total_amount'] * 100;  //转换成分 ，和获取订单号时，传过来的一致

        $this->info['o_order_num'] = $this->post['trade_no'];

        //去掉那些不是必传的字段
        $nullParam = array('out_biz_no', 'buyer_id', 'buyer_logon_id',  'seller_email',
        'trade_status', 'receipt_amount', 'invoice_amount','buyer_pay_amount','point_amount',
        'refund_fee', 'subject',
//            'seller_id',
//            'total_amount',
//            'body',
        'gmt_create', 'gmt_payment', 'gmt_refund',
        'gmt_close', 'fund_bill_list','passback_params','voucher_detail_list' );
        $checkParam = $this->post;
        foreach ($nullParam as $k => $v) {
            unset($checkParam[$v]);
        }
        //判断是否有参数为空
        !$this->apiHelper->checkParamIsNull($checkParam) && $this->_exit(helper\ErrorOrders::ERROR_MISSED_PARAM);

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
//            echo $this->post['trade_status']; exit;
            //记录支付宝支付状态错误后，返回的错误
            $errorOrdersModel = new ErrorOrders;
            $errorOrdersModel->insertOrder($this->info['channel_pkg_num'], helper\ErrorOrders::ERROR_ALIPAY_STATUS, $this->info['my_order_num'], $this->post, $this->order);

            $this->_exit($this->success);  //还是返回成功，表示收到了支付请求，SDK服务器就不会在周期内进行重复通知
        }
    }

    protected function _checkSign()
    {
        $params = Request::instance()->post();  //为防止漏掉参数，再重新获取一遍

        $aliwapPayconfig = include dirname ( __FILE__ ).DIRECTORY_SEPARATOR.'aliwappay/aliconfig.php';
        $alipaySevice = new \AlipayTradeService($aliwapPayconfig);
//        $alipaySevice->writeLog(var_export($_POST,true));
        $result = $alipaySevice->check($params);

        if (!$result) {
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

        //下单时传入的CP订单信息
        $orderData = json_decode($this->order['order'], true);
        $checkTotalFee = $orderData['total_fee'];  //客户端下单时传过来的是分

        //2.验证金额
        if ( $this->_getMoney()!= $checkTotalFee ) {
            //充值金额对不上
            $this->_exit(helper\ErrorOrders::ERROR_MONEY_NOT_SAME);
        }

        //3.验证卖家是否正确
        $aliwapPayconfig = include dirname ( __FILE__ ).DIRECTORY_SEPARATOR.'aliwappay/aliconfig.php';
        if (!in_array($this->post['seller_id'], explode(',', $aliwapPayconfig['user_id']))) {
            $this->_exit(helper\ErrorOrders::ERROR_ALIPAY_SELLER_ID);
        }

        //4.验证应用ID是否正确，此处是验证支付宝的应用ID 和游戏的应用ID是有区别的
        if ($this->post['app_id'] != $aliwapPayconfig['app_id'] ) {
            $this->_exit(helper\ErrorOrders::ERROR_ALIPAY_APP_ID);
        }


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