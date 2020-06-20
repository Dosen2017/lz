<?php

/**
 * Created by sudesheng.
 * User: 2066815257@qq.com
 * Date: 20-04-13
 * Time: 上午 12:01
 *
 * IOS充值验证苹果充值参数receipt 和回调CP
 *
 * 在收到凭证receipt之后，如果后面出现错误，比如CP回调返回的不是SUCCESS，
 * 第一次返回给客户端的ret 为错误码，不=0。第二次请求时，由于receipt已经被验证过了，【判断receipt_record这个表中是否存在】
 * 所以，返回ret=0,成功的给客户端。
 *
 * 如果要重新回调【只客户端的receipt验证的回调】，需要修改orders表中的status为1，并且删掉receipt_record表中对应订单的数据
 *
 */

namespace app\recharge\controller;

use app\recharge\model\ErrorOrders;
use think\Request;

use app\recharge\helper;
use app\recharge\model\ProductidData;
use app\recharge\model\ReceiptRecord;
use app\recharge\model\ChannelPkg;

class IosRecharge extends Recharge
{
    const APPLE_TEST_RECHAGRE_RETURN_ERROR_NUMBER = 21007;
    const RECEIPT_REQUEST_TIMES = 6;

    //测试
    private $testUrl = 'https://sandbox.itunes.apple.com/verifyReceipt';
    //正式环境[默认为正式环境]
    private $url = 'https://buy.itunes.apple.com/verifyReceipt';

    public function index()
    {
        // 1.获取参数 , 判断参数是否为空， 判断sign是否正确
        $this->_getParam();
        // 2.验证订单是否存在
        $this->_checkOrderNumIsExist();

        //2.1 设置回调后的数据
        $this->_setCallBackData();

        // 3.发送请求到Apple 服务器验证
        $this->_postRequestToAppServer();
        // 4.发起到CP的回调状态
        $this->_requestCallBackToCP();
    }

    protected function _getParam()
    {
        // 获取请求
        $reqParam = Request::instance()->post();
        $this->post['channel_pkg_num'] = $reqParam['channel_pkg_num']; //渠道包ID
        $this->post['my_order_num'] = $reqParam['my_order_num']; //订单号
        $this->post['receipt'] = $reqParam['receipt']; //apple的支付凭证
        $this->post['product_id'] = $reqParam['product_id']; //产品ID
//        $this->post['receipt'] = str_replace(' ', '+',$reqParam['receipt']); //apple的支付凭证

//        $this->post['version_id'] = $reqParam['version_id'];  //苹果版本号
        $this->post['time'] = $reqParam['time']; //支付时间
        $this->post['sign'] = $reqParam['sign']; //数据加密验证字符串

        //为了代码统一，附加两个参数
        $this->info['channel_pkg_num'] = $this->post['channel_pkg_num'];  //在下单的时候传入
        $this->info['my_order_num'] = $this->post['my_order_num'];
//        $this->info['money'] = $this->post['total_fee'];            //支付金额,苹果支付时，需要通过支付凭证去获取，所以这里注释了。
        $this->info['pt_order_num'] = 'apple_' . $this->post['my_order_num'];  //平台订单号,下面解析除receipt后会拿苹果的订单号重新赋值
        $this->info['pay_time'] = $this->post['time'];  //支付时间

        $this->info['source'] = "sdk";//订单来源，苹果官方，各接入的渠道，都用sdk

//var_dump($this->post);;exit;
        //判断是否有参数为空
        !$this->apiHelper->checkParamIsNull($this->post) && $this->_exit(helper\ErrorOrders::ERROR_MISSED_PARAM);
        //验证sign
        !$this->apiHelper->checkSign($this->post) && $this->_exit(helper\ErrorOrders::ERROR_SIGN);

    }

    protected function _postRequestToAppServer()
    {
//echo 9999;exit;
        //加入一个是否切换成测试连接的判断
        //$this->judgeIsTestEvn();
        //下单时传入的CP订单信息
//        $orderData = json_decode($this->order['order'], true);
        $checkTotalFee = $this->order['amount'];  //客户端下单时传过来的是分

        //1.将购买凭证的信息存入数据表  ,会先判断是否已经存在该凭证，已经存在，就不再添加
        $receiptRecord = new ReceiptRecord;
        $return = $receiptRecord->addReceipt($this->post, $checkTotalFee);
        if ($return == 'exist') {
            $this->_exit(helper\ErrorOrders::RECEIPT_IS_EXIST);
        }

        //开始验证，修改order表的status 为2
        $this->orderModel->upOrderStatus($this->post, helper\Orders::STATUS_CHECK_RECEIPT);

        $data = array('receipt-data' => $this->post['receipt']);
        $return = $this->_httpsPost($this->url, json_encode($data));
//        $return  = httpRequest($this->url, json_encode($data), true,'post', 2);
        $returnArr = json_decode($return, true);

        /**
         * 如果失败，尝试多次请求
         */
        $i = 1;
        while (!$return || $returnArr['status'] != 0) {
            //21007说明是沙盒测试，切换请求链接
            if (self::APPLE_TEST_RECHAGRE_RETURN_ERROR_NUMBER == $returnArr['status']) {
                $this->url = $this->testUrl;
            }

            sleep(1);
            $return = $this->_httpsPost($this->url, json_encode($data));
//            $return  = httpRequest($this->url, json_encode($data), true,'post', 2);
            $returnArr = json_decode($return, true);

            $i++;
            if ($i >= self::RECEIPT_REQUEST_TIMES) {
                break;
            }
        }

        if (!$return) {
            //2修改后买凭证的状态为请求付款失败
            $receiptRecord->updateReceiptStatus($receiptRecord::RECEIPT_STATUS_PAY_ERROR, $this->post['receipt'], $return);
            $this->_exit(helper\ErrorOrders::REQUEST_APPLE_RETURN_EMPTY);
        }
        if ($returnArr['status'] != 0) {
            //2修改后买凭证的状态为请求付款失败
            $receiptRecord->updateReceiptStatus($receiptRecord::RECEIPT_STATUS_PAY_ERROR, $this->post['receipt'], $return);
            $this->_exit($returnArr['status']);
        }

        $this->info['pt_order_num'] = ($returnArr['environment'] ?: '') . $returnArr['receipt']['in_app'][0]['transaction_id'];

            //2修改购买凭证的状态为请求付款成功, 并且把苹果返回的数据更新到receipt里面
        $receiptRecord->updateReceiptStatus($receiptRecord::RECEIPT_STATUS_PAY_SUCCESS, $this->post['receipt'], $return);

        //更新平台订单号
        $this->orderModel->upOrderPtOrderNum($this->post, $this->info['pt_order_num']);

        $this->orderModel->upOrderStatus($this->post, helper\Orders::STATUS_CHECK_RECEIPT_SUCCESS);

        //---------------------------------1-----------------------------
        //判断返回的产品ID是否在该APP_ID配置的产品列表ID内
        $channelPkgModel = new ChannelPkg();
        $productIdsAndMoney = $channelPkgModel->getProductIdsAndMoney($this->post['channel_pkg_num']);

        if (is_null($productIdsAndMoney)) {
            //APP_ID对应的产品ID没有配置
            $this->_exit(helper\ErrorOrders::ERROR_APP_ID_PRODUCT_ID_NOT_CONFIGURE);
        }

        $appleRequestReturnProductId = $returnArr['receipt']['in_app'][0]['product_id'];

        if (!in_array($appleRequestReturnProductId, array_keys($productIdsAndMoney))) {
            //产品ID不在后台配置中
            $this->_exit(helper\ErrorOrders::ERROR_PRODUCT_ID_NOT_EXIST);
        }
        //--------------------------------2-------------------------------------
        if ($this->post['product_id'] != $appleRequestReturnProductId) {
            $this->_exit(helper\ErrorOrders::ERROR_PRODUCT_ID_NOT_SAME);
        }

        //修改money的值为返回的值
        $this->info['money'] = $productIdsAndMoney[$appleRequestReturnProductId];
//file_put_contents('/tmp/IosRecharge.txt', date('Y-m-d H:i:s') . "__" . $appleRequestReturnProductId . "__" . json_encode($productIdsAndMoney) . "__" . json_encode($productIdsAndMoney) . "\n");
        if (!in_array($productIdsAndMoney[$appleRequestReturnProductId], array_values($productIdsAndMoney))) {
            //金钱不在后台配置中
            $this->_exit(helper\ErrorOrders::ERROR_MONEY_NOT_EXIST);
        }
        //--------------------------------3-------------------------------------
        //下单时的金额
        if ($this->_getMoney() != $checkTotalFee) {
            $this->_exit(helper\ErrorOrders::ERROR_MONEY_NOT_SAME);
        }

    }

    private function _httpsPost($url, $data = null)
    {
        $ch = curl_init();

        //https
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);    // https请求 不验证证书和hosts
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        //curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);


        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        //curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type:application/x-www-form-urlencoded;charset=utf-8'));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type:application/json;charset=utf-8'));
        $rtn = curl_exec($ch);
        if ($errNo = curl_errno($ch)) {
            return false;
        }
        curl_close($ch);
        return $rtn;
    }

    //因为是支付宝回调，是直接返回给支付宝的，按照支付宝的方式返回字符串
    protected function _exit($exitId, $fail = 'fail')
    {
        if($exitId != $this->success){
            $errorOrdersModel = new ErrorOrders;
            $errorOrdersModel->insertOrder($this->info['channel_pkg_num'], $exitId, $this->info['my_order_num'], $this->post, $this->order);
            $this->apiHelper->echoJson($exitId);//失败
        }
        $this->apiHelper->echoJson(0); //成功
    }

    protected function _getMoney()
    {
        // TODO: Implement _getMoney() method.
        return $this->info['money'];
    }


}
