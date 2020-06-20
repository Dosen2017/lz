<?php
/**
 * Created by PhpStorm.
 * User: sds
 * Date: 2019/3/1
 * Time: 11:30
 */

namespace app\recharge\controller;

use think\Controller;

use app\recharge\helper;
use app\recharge\model\Orders;
use app\recharge\model\ErrorOrders;
use app\recharge\model\Game;
use app\recharge\model\ErrorCallback;
use app\recharge\model;

/**
 * Class RechargeUp
 * @package app\recharge\controller
 *
 * 在原有Recharge的基础上进行升级，加入了money_type字段（必传参数），后面所有的渠道充值应该是继承自此类
 * 对于有特殊要求的，比如需要上一层次的渠道订单ID，就需要用到RechargeUpExt这个类（扩展了chl_order_num渠道订单号的参数）
 *
 */
abstract class RechargeOutHf extends RechargeCommon
{

    //发出回调到CP服务端
    protected function _requestCallBackToCP() {
        $orderData = json_decode($this->order['order'], true);

        $callBackUrl = $this->_getCPNotifyUrlByAppID() ?: $orderData['notify_url'];   //默认取后台的app_id参数那里配置

        $callBackData['app_id'] = $this->info['app_id'];  //应用ID
        $callBackData['tf_trade_no'] = $this->info['order_num'];  //TF订单号

        $callBackData['chl_order_num'] = $this->info['o_order_num'];  //渠道订单号

//        $callBackData['extra'] = $orderData['extra'] ?: '';  //CP的透传参数
//        $callBackData['cp_trade_id'] = $orderData['cp_trade_id'];  //CP订单号


        $callBackData['role_id'] = $orderData['role_id'];  //角色ID
//        $callBackData['role_name'] = $orderData['role_name'];  //角色名
        $callBackData['server_id'] = $orderData['server_id'];  //服务器ID
        $callBackData['money_type'] = $orderData['money_type'] ?: 1;  //货币类型
        $callBackData['total_fee'] = $orderData['total_fee'];  //充值总额  分
        $callBackData['pay_type'] = $this->order['pay_type'];  //支付类型
        $callBackData['pay_result'] = 1; //支付结果  1.成功，2失败  暂时默认为1，全部为成功
        $callBackData['sign'] = $this->getCallBackSign($callBackData);

//        file_put_contents('/tmp/aaaaa.txt', $callBackUrl . "\n" . json_encode($callBackData), FILE_APPEND);

        //设置状态为回调CP
        $this->orderModel->upOrderStatus($this->info, helper\Orders::STATUS_CALLBACK);

        $isHttps = false;
        //检测设置的回调地址是http还是https
        if ( 'https' === substr($callBackUrl, 0, 5) ) {
            $isHttps = true;
        }

        $callbackResult = httpRequest($callBackUrl , http_build_query($callBackData), $isHttps, 'post');

        /**
         * 如果失败，尝试多次请求,累计三次
         */
        $i = 1;
        while ($callbackResult != 'SUCCESS')
        {
            sleep(2);
            $callbackResult = httpRequest($callBackUrl , http_build_query($callBackData), $isHttps, 'post');

            $i++;
            if ($i > self::CP_CALLBACK_REQUEST_TIMES)
            {
                break;
            }
        }

        //CP 返回 SUCCESS 代表成功
        if ($callbackResult != 'SUCCESS') {

            //记录回调的数据和地址,方便手动和自动回调到CP
            $errorCallbackModel = new ErrorCallback();
            $errorCallbackModel->insertData($callBackData['app_id'], $callBackData['tf_trade_no'], $callBackUrl, $callBackData);

            $this->_exit(helper\ErrorOrders::ERROR_CALLBACK_RETURN); // 记录CP端返回有误
        }

        $this->orderModel->upOrderStatus($this->info, helper\Orders::STATUS_COMPLETE);
        $this->_exit($this->success);

    }

}