<?php
/**
 * Created by PhpStorm.
 * User: sds
 * Date: 2019/3/1
 * Time: 10:51
 */

namespace app\recharge\model;

use think\Model;
use app\recharge\helper\Orders as helperOrders;

class Orders extends Model
{

    //后面orders单表数据量大的情况下，可以使用这种方案切换表（分表）
//    public function __construct() {
//        if (time() >= strtotime('2018-09-04')) {
//            $this->table = "orders_bran20180904";
//        }
//        parent::__construct();
//    }
    protected $createTime = '';//防止create_time在存储时，自动转换
    /**
     * 生成唯一订单号
     */
    public function getUniqueOrderNum()
    {
        usleep(1);
//        return date('YmdHis') . (int)( explode(' ',microtime())[0] * 1000000)  . strtoupper('--' . random(3) . '--' . substr(uniqid(), 8));
//        return date('YmdHis') . (int)( explode(' ',microtime())[0] * 1000000)  . strtoupper(random(3) . substr(uniqid(), 8));

        //固定生成一个19位数的数字
        $orderID = substr(date('YmdHis'),2) .
            (int)substr(( explode(' ',microtime())[0] * 1000000), 2)  .
            randomCode(3, '123456789');

        $padNum = 19 - strlen($orderID);

        if ($padNum > 0) {
            $orderID = str_pad($orderID, 19, randomCode($padNum, '123456789'));
        }

//        echo strlen($orderID);
        return $orderID;
    }

    /**
     * 生成唯一订单号(缩短版)
     */
    public function getUniqueOrderNumShort()
    {
        usleep(1);
//        return date('YmdHis') . (int)( explode(' ',microtime())[0] * 1000000)  . strtoupper('--' . random(3) . '--' . substr(uniqid(), 8));
        return substr(date('Ymd'),2) .  strtoupper(random(2) . uniqid());
    }

    /**
     * 是否存在订单，返回订单状态
     */
    public function hasOrder($channelPkgNum, $myOrderNum, &$order)
    {

        $field = '`real_amount`,`amount`,`state`, `currency`, `server_id`, `server_name`, `role_id`, `role_name`, 
        `role_level`, `extra`, `notify_url`, `cp_order_num`, `product_num`, `product_name`,
        `user_num`, `user_name`, `create_time`, `gateway_num`, `os_type`, `bundle`';

        $list = $this
            ->field($field)
            ->where(['channel_pkg_num' => $channelPkgNum, 'my_order_num' => $myOrderNum])
            ->find();
//        file_put_contents("/tmp/Orders.sql", date("Y-m-d H:i:s") ."__" . $myOrderNum . "__" . $this->getLastSql() . "__" . json_encode($list) . "\n", FILE_APPEND);
        if(isset($list['state'])){

            $order['my_order_num'] = $myOrderNum;
            $order['server_id'] = $list['server_id'];
            $order['server_name'] = $list['server_name'];
            $order['role_id'] = $list['role_id'];
            $order['role_name'] = $list['role_name'];
            $order['role_level'] = $list['role_level'];
            $order['extra'] = $list['extra'];
            $order['notify_url'] = $list['notify_url'];
            $order['cp_order_num'] = $list['cp_order_num'];

            $order['real_amount'] = $list['real_amount'];
            $order['amount'] = $list['amount'];
            $order['currency'] = $list['currency'];

            $order['product_num'] = $list['product_num'];
            $order['product_name'] = $list['product_name'];

            $order['user_num'] = $list['user_num'];
            $order['user_name'] = $list['user_name'];

            $order['create_time'] = $list['create_time'];

            $order['gateway_num'] = $list['gateway_num'];

            $order['os_type'] = $list['os_type'];
            $order['bundle'] = $list['bundle'];

//            var_dump($order);exit;

//            $order['channel_pkg_num'] = $list['channel_pkg_num'];

//            `user_num` bigint(20) unsigned NOT NULL COMMENT '账号ID',
//  `user_name` varchar(64) DEFAULT '' COMMENT '账号名',
//  `game_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '游戏ID',
//  `channel_pkg_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '渠道包ID',


            return $list['state'];
        }else
            return null;
    }

    public function upOrderStatus($info, $state, $callbackResult = '')
    {
        $time = time();

        $where['channel_pkg_num'] = $info['channel_pkg_num'];
        $where['my_order_num'] = $info['my_order_num'];

        $updData['state'] = $state;
        $updData['pay_time'] = $info['pay_time'] ?: $time;
        $updData['complete_time'] = $time;
        $updData['notice_result'] = $callbackResult;
        $updData['notice_times'] = 1;

        return $this
            ->where($where)
            ->update($updData);
    }

    public function upOrderPtOrderNum($info, $pt_order_num)
    {
        $where['channel_pkg_num'] = $info['channel_pkg_num'];
        $where['my_order_num'] = $info['my_order_num'];

        $updData['pt_order_num'] = $pt_order_num;

        return $this
            ->where($where)
            ->update($updData);
    }

    public function setCallBack($info, $postData)
    {
        $requestData = is_null($postData) ? '' : json_encode($postData);
        $ptOrderNum = $info['pt_order_num'] ?: '';

        $where['channel_pkg_num'] = $info['channel_pkg_num'];
        $where['my_order_num'] = $info['my_order_num'];

        $updData['pt_order_num'] = $ptOrderNum;
        $updData['request_data'] = $requestData;
        $updData['source'] = $info['source'] ?: 'sdk';  //默认为SDK，比如苹果官方充值，各渠道，如华为，oppo的渠道充值

//        file_put_contents('/tmp/setCallback', json_encode($info) . "\n", FILE_APPEND);

        return $this
            ->where($where)
            ->update($updData);
    }

}