<?php
/**
 * Created by PhpStorm.
 * User: sds
 * Date: 2019/3/1
 * Time: 11:08
 */

namespace app\recharge\model;

use think\Model;

class ErrorOrders extends Model
{
    public function insertOrder($channelPkgNum, $errorId, $myOrderNum, $requestData, $orderData)
    {
        $this->save(
            array(
                'channel_pkg_num' => is_null($channelPkgNum) ? '' : $channelPkgNum,
                'error_id' => is_null($errorId) ? '' : $errorId,
                'my_order_num' => is_null($myOrderNum) ? '' : $myOrderNum,
                'request_data' => is_null($requestData) ? '' : json_encode($requestData),
                'order_data' => is_null($orderData['order']) ? '' : $orderData['order'],
                'time' => time(),
            )
        );
    }
}
