<?php
/**
 * Created by PhpStorm.
 * User: sds
 * Date: 2019/2/27
 * Time: 12:01
 */

namespace app\login\model;

use think\Model;
use think\Config;

class ErrorLogin extends Model
{
    //查询时间段获取错误登陆的次数
    public function getTimeSlotErrorLoginTimes($startTime, $endTime, $reqData) {
        return $this->field('user_name')->where("time between {$startTime} and {$endTime} and user_name = '" . $reqData['user_name'] . "'")->select();
    }

    //登陆密码错误记录
    public function saveErrorData($errData) {
        return $this->save($errData);
    }
}
