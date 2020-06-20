<?php
/**
 * Created by PhpStorm.
 * User: sds
 * Date: 2019/3/1
 * Time: 11:18
 */

namespace app\recharge\controller;

use app\recharge\helper;
use app\recharge\model\ErrorCallback;
use app\recharge\model\GameOrders;
use app\recharge\model\GameUsers;

abstract class Recharge extends RechargeCommon
{

    const OS_IOS = 1;

    const RET_SUCCESS = "SUCCESS";

    const CHARGE_DAYS =  [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,30,60];

    //发出回调到CP服务端
    protected function _requestCallBackToCP() {
        $orderData = $this->order;

//        $callBackUrl = $orderData['notify_url'];
        $callBackUrl = $this->_getCPNotifyUrlByGameID($orderData['notify_url']);   //回调地址的优先次序：渠道包设置优先，客户端传入其次，游戏设置最后

        $callBackData['channel_pkg_num'] = $this->info['channel_pkg_num'];  //渠道包ID
        $callBackData['my_order_num'] = $this->info['my_order_num'];  //订单号

        $callBackData['cp_order_num'] = $orderData['cp_order_num'];  //CP订单号
        $callBackData['extra'] = $orderData['extra'] ?: '';  //CP的透传参数

        $callBackData['role_id'] = $orderData['role_id'];  //角色ID
        $callBackData['role_name'] = $orderData['role_name'];  //角色名

        $callBackData['product_num'] = $orderData['product_num'];  //产品ID
        $callBackData['product_name'] = $orderData['product_name'];  //产品名称

        $callBackData['server_id'] = $orderData['server_id'];  //服务器ID
        $callBackData['server_name'] = $orderData['server_name'];  //服务器名称
        $callBackData['currency'] = $orderData['currency'] ?: "RMB";  //币种  币种，国内版默认为：CNY,海外版默认USD
        $callBackData['amount'] = $orderData['amount'];  //充值总额  分
        $callBackData['pay_result'] = 1; //支付结果  1.成功，2失败  暂时默认为1，全部为成功
        $callBackData['sign'] = $this->getCallBackSign($callBackData);

//        file_put_contents('/tmp/requestCallBackToCP.txt', date('Y-m-d H:i:s') . "__" . json_encode($callBackData) . "\n", FILE_APPEND);

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
        while ($callbackResult != self::RET_SUCCESS)
        {
            sleep(2);
            $callbackResult = httpRequest($callBackUrl , http_build_query($callBackData), $isHttps, 'post');

            $i++;
            if ($i > self::CP_CALLBACK_REQUEST_TIMES)
            {
                break;
            }
        }

        //修改game_orders和game_users表的数据，方便统计
        $this->_handleGameOrdersAndGameUsers($callbackResult);

        //CP 返回 SUCCESS 代表成功
        if ($callbackResult != self::RET_SUCCESS) {

            //记录回调的数据和地址,方便手动和自动回调到CP
            $errorCallbackModel = new ErrorCallback();
            $errorCallbackModel->insertData($this->info['channel_pkg_num'], $this->info['my_order_num'], $callBackUrl, $callBackData);

            $this->_exit(helper\ErrorOrders::ERROR_CALLBACK_RETURN); // 记录CP端返回有误
        }

        $this->orderModel->upOrderStatus($this->info, helper\Orders::STATUS_COMPLETE, $callbackResult);
        $this->_exit($this->success);

    }

    protected function _handleGameOrdersAndGameUsers($callbackResult)
    {
        //1.添加数据到game_orders表
        $gameOrderInfo = [];
        $gameOrdersModel = new GameOrders();
        $result = $gameOrdersModel->hasGameOrder($this->info['channel_pkg_num'], $this->info['my_order_num'], $gameOrderInfo);

        //如果订单不存在于game_orders表中，则添加数据
        if (false === $result) {

            //2.修改game_users表的充值数据
            $userNum = $this->order['user_num'];
            $channelPkgNum = $this->info['channel_pkg_num'];
            $gameUsersModel = new GameUsers();
            $gameUserWhere = ['user_num' => $userNum, 'channel_pkg_num' => $channelPkgNum];
            $gameUserInfo = $gameUsersModel->field('`b_id`, `ads_id`, `create_time`')->where($gameUserWhere)->find();

            //1属于第一步添加到game_orders表的步骤，理论上是放在该if的第一行，因为要用到玩家表的b_id和ads_id，所以放在这里执行
            $gameOrdersModel->addData($this->order, $this->info, $callbackResult, $gameUserInfo);


            $realPayTime = $this->info['pay_time'] ?: $this->order['create_time'];
            $n = (int)(($realPayTime - strtotime(date('Y-m-d', $gameUserInfo['create_time']))) / 86400) + 1; //计算出第几天登录

            //注册当日0点0分 时间戳
            //默认为非首日
//            $isfirstDay = false;
//            $createDUnix = strtotime(date('Y-m-d', $gameUserInfo['create_time']));
//            //如果充值时间在注册当日，则设置首日为true
//            if (($realPayTime >= $createDUnix) && ($realPayTime < $createDUnix + 86400)) {
//                $isfirstDay = true;
//            }
            
            //如果计算出的日期在日期列表中
            if (in_array($n, self::CHARGE_DAYS)) {
                //新方案
                $updStr = "";
                $updData["charge" . $n] = 1;
                $updData["last_pay"] = $this->info['pay_time'];
                $updData["charge" . $n . "_count"] = "charge" . $n . "_count + 1";
                $updData["charge_amount"] = "charge_amount + " . $this->_getMoney();
                $updData["charge_count"] = "charge_count + 1";

//                if ($isfirstDay) {
                $updData["charge" . $n . "_amount"] = "charge" . $n . "_amount + " . $this->_getMoney();
//                    $updData["charge1_count"] = "charge_fisrt_count + 1";
//                }


                if ($this->info['source'] != "sdk") {   //代表是乐众支付
                    $updData["charge" . $n . "_lz"] = 1;
                    $updData["last_pay_lz"] = $this->info['pay_time'];

                    $updData["charge" . $n . "_lz_count"] = "charge" . $n . "_lz_count + 1";
                    $updData["charge_amount_lz"] = "charge_amount_lz + " . $this->_getMoney();
                    $updData["charge_count_lz"] = "charge_count_lz + 1";

//                    if ($isfirstDay) {
                    //官方支付只记录第一天的充值金额
                    if ($n == 1) {
                        $updData["charge1_amount_lz"] = "charge1_amount_lz + " . $this->_getMoney();
                    }

//                        $updData["charge1_count_lz"] = "charge_first_count_lz + 1";
//                    }

                }

                foreach ($updData as $k => $v) {
                 $updStr .= ($updStr != "" ? " , " : "") . $k . " = " . $v;
                }

                $sql = "update game_users set $updStr where user_num = " . $gameUserWhere["user_num"] . " and channel_pkg_num = " . $gameUserWhere["channel_pkg_num"];
//                file_put_contents("/tmp/rechargeUpd.sql", date("Y-m-d H:i:s") . "___" . $sql . "\n", FILE_APPEND);
                $gameUsersModel->execute($sql);
            }

        } else {
            //修改订单的通知结果
            $gameOrdersModel->where(['my_order_num' => $this->info['my_order_num']])->update(['notice_result' => $callbackResult, 'complete_time' => time()]);
        }
    }

}