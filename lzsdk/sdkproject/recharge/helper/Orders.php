<?php
/**
 * Created by PhpStorm.
 * User: sds
 * Date: 2019/3/1
 * Time: 10:52
 */

namespace app\recharge\helper;

use think\Helper;

class Orders extends Helper
{
    //订单状态1.下单，【2.开始验证receipt,3.验证receipt成功 => 苹果支付的专属状态 】,4.回调CP,5.完成
    const STATUS_CREATE = 1;
    const STATUS_CHECK_RECEIPT = 2;
    const STATUS_CHECK_RECEIPT_SUCCESS = 3;
    const STATUS_CALLBACK = 4;
    const STATUS_COMPLETE = 5;
}