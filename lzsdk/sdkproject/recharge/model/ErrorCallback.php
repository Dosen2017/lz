<?php
/**
 * Created by PhpStorm.
 * User: sds
 * Date: 2019/3/1
 * Time: 11:09
 */

namespace app\recharge\model;

use think\Model;

use app\recharge\helper;

class ErrorCallback extends Model
{
    public function insertData($channelPkgNum, $myOrderNum, $callbackUrl, $callbackData)
    {
        $this->save(
            array(
                'channel_pkg_num' => $channelPkgNum,
                'my_order_num' => $myOrderNum,
                'callback_url' => $callbackUrl,
                'callback_data' => json_encode($callbackData),
                'state' => helper\ErrorCallback::STATUS_CREATE,
                'create_time' => time(),
            )
        );
    }
}