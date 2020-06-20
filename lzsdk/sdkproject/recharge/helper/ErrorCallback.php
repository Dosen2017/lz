<?php
/**
 * Created by PhpStorm.
 * User: sds
 * Date: 2019/3/1
 * Time: 11:04
 */

namespace app\recharge\helper;

use think\Helper;

class ErrorCallback extends Helper
{
    //1.回调出错插入，2.自动回调成功，3.手动回调成功

    //状态码
    const STATUS_CREATE = 1;
    const STATUS_AUTO_CALLBACK = 2;
    const STATUS_HAND_CALLBACK = 3;
}
