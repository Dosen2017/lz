<?php
namespace app\login\model;

use think\Model;

class ErrorMesSend extends Model
{
    //查询时间段获取错误登陆的次数
//    public function getTimeSlotErrorLoginTimes($startTime, $endTime, $reqData) {
//        return $this->where("time between {$startTime} and {$endTime} and account_name = '" . $reqData['account_name'] . "' and device_id = '" . $reqData['device_id'] . "'")->select();
//    }

    //登陆密码错误记录
    public function saveErrorData($errData) {
        return $this->save($errData);
    }
}
