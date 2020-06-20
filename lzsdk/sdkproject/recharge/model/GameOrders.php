<?php
/**
 * Created by PhpStorm.
 * User: sds
 * Date: 2020/4/20
 * Time: 09:55
 */

namespace app\recharge\model;

use think\Model;

class GameOrders extends Model
{
    protected $createTime = '';//防止create_time在存储时，自动转换
    /**
     * 是否存在订单，返回订单状态
     */
    public function hasGameOrder($channelPkgNum, $myOrderNum, &$order)
    {
//        echo $channelPkgNum . "__" . $myOrderNum . "\n";
        $list = $this
            ->field('`my_order_num`, `notice_result`')
            ->where(['channel_pkg_num' => $channelPkgNum, 'my_order_num' => $myOrderNum])
            ->find();
//        var_dump($list);exit;
        if(isset($list['my_order_num'])){
            return $order['notice_result'];
        }else
            return false;
    }

    public function addData($orderInfo, $extInfo, $callbackResult, $gameUserInfo)
    {
        $order['my_order_num'] = $orderInfo['my_order_num'];
        $order['pt_order_num'] = $extInfo['pt_order_num'];
        $order['real_amount'] = $orderInfo['real_amount'];
        $order['amount'] = $orderInfo['amount'];
        $order['source'] = $extInfo['source'];
        $order['remark'] = $orderInfo['remark'];
        $order['user_num'] = $orderInfo['user_num'];
        $order['user_name'] = $orderInfo['user_name'];

        $gameNum = getGameNumByChannelPkgNum($extInfo['channel_pkg_num']);
        $channelNum = getChannelNumByChannelPkgNum($extInfo['channel_pkg_num']);

        $order['game_num'] = $gameNum;
        $order['channel_num'] = $channelNum;
        $order['channel_pkg_num'] = $extInfo['channel_pkg_num'];

        $order['currency'] = $orderInfo['currency'];
        $order['product_num'] = $orderInfo['product_num'];
        $order['product_name'] = $orderInfo['product_name'];

        $order['server_id'] = $orderInfo['server_id'];
        $order['server_name'] = $orderInfo['server_name'];
        $order['role_id'] = $orderInfo['role_id'];
        $order['role_name'] = $orderInfo['role_name'];
        $order['role_level'] = $orderInfo['role_level'];
        $order['cp_order_num'] = $orderInfo['cp_order_num'];

        $order['create_time'] = $orderInfo['create_time'];
        $order['pay_time'] = $extInfo['pay_time'];

        $time = time();
        $order['complete_time'] = $time;

        //用于统计的字段，用实际支付时间
        $order['order_d'] = date('Ymd', $order['pay_time']);

        $order['gateway_num'] = $orderInfo['gateway_num'];

        $order['os_type'] = $orderInfo['os_type'];
        $order['bundle'] = $orderInfo['bundle'];

        $order['b_id'] = $gameUserInfo['b_id'];
        $order['ads_id'] = $gameUserInfo['ads_id'];

        $order['notice_result'] = $callbackResult;
//        $order['settle_amount'] = $orderInfo['settle_amount'];
//        $order['settle_currency'] = $orderInfo['settle_currency'];

        $this->save($order);
    }

}