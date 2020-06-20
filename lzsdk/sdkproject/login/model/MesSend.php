<?php
/**
 * Created by PhpStorm.
 * User: sds
 * Date: 2019/2/27
 * Time: 12:01
 */

namespace app\login\model;

use think\Model;

class MesSend extends Model
{
    //查询时间段获取错误登陆的次数
//    public function getTimeSlotErrorLoginTimes($startTime, $endTime, $reqData) {
//        return $this->where("time between {$startTime} and {$endTime} and account_name = '" . $reqData['account_name'] . "' and device_id = '" . $reqData['device_id'] . "'")->select();
//    }

    //登陆密码错误记录
    public function saveData($errData) {
        return $this->save($errData);
    }

    public function getCodeInfo($phoneNum, $code, $type) {

        $where['phone_number'] = $phoneNum;
//        $where['code'] = $code;
        $where['type'] = $type;
        $where['time'] = ['egt', time() - 600];  //10分钟之内的最新数据
//echo 999;exit;
        //获取十分钟之内的最新的一条数据
        $ret = $this->field('code, time')->where($where)->order('time desc')->find();
//echo $this->getLastSql();exit;
        if ( !$ret) {
            return false;
        }

        if ($code != $ret['code']) {
            return false;
        }

        return true;
    }

    public function check30SecondsIsGetCode($phoneNum, $type) {

        $where['phone_number'] = $phoneNum;
//        $where['code'] = $code;
        $where['type'] = $type;
        $where['time'] = ['egt', time() - 30];

        //获取十分钟之内的最新的一条数据
        $ret = $this->field('code')->where($where)->order('time desc')->find();
//        echo $this->getLastSql();exit;
        if ( !$ret) {
            return false;
        }

        return true;
    }
}