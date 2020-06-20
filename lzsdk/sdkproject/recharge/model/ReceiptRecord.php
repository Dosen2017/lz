<?php
/**
 * Created by PhpStorm.
 * User: sds
 * Date: 2020/04/13
 * Time: 11:25
 */

namespace app\recharge\model;

use think\Model;

class ReceiptRecord extends Model
{
    const RECEIPT_STATUS_CREATE = 1;
    const RECEIPT_STATUS_PAY_ERROR = 2;
    const RECEIPT_STATUS_PAY_SUCCESS = 3;

    public function addReceipt($post, $money)
    {
        //判断receipt是否已存在
        if ( $this->hasReceipt($post['receipt']) )
        {
            return 'exist';
        }

        $data['my_order_num'] = $post['my_order_num'];
        $data['money'] = $money ?: 0;
        $data['receipt'] = $post['receipt'];
        $data['md5_receipt'] = md5($post['receipt']);
        $data['state'] = self::RECEIPT_STATUS_CREATE;
        $data['create_time'] = time();
//        $data['date'] = date('Y-m-d', $data['create_time']);

        return $this->save($data);
    }

    /**
     * 是否存在购买凭证
     */
    public function hasReceipt($receipt)
    {
        $list = $this
            ->where(["md5_receipt" => md5($receipt), "receipt" => $receipt])
            ->find();

        if ( empty($list) )
            return false;

        return true;
    }

    public function updateReceiptStatus($status, $receipt, $receiptCheckReturn)
    {
        $now = time();
        $order['complete_time'] = $now;

        $updata = array(
            'state' => $status,
            'complete_time' => $now,
            'last_time' => $now,
            'rtn_info' => $receiptCheckReturn ?: ''
        );

        if ($status == self::RECEIPT_STATUS_PAY_ERROR)
        {
            unset($updata['last_time']);
        }
        return $this
            ->where(["md5_receipt" => md5($receipt), "receipt" => $receipt])
            ->update(
                $updata
            );
    }
}
