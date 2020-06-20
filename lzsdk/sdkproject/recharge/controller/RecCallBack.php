<?php
/**
 * Created by PhpStorm.
 * User: sds
 * Date: 2020/4/8
 * Time: 10:49
 */

namespace app\recharge\controller;


use think\Controller;

/**
 * Class RecCallBack
 * @package app\recharge\controller
 *
 * 所有渠道充值回调的入口
 *
 */
class RecCallBack extends Controller
{
    protected $channelPkgNumToClass = [
        880010403 => 'ChannelXiaoqi',

    ];

    public function cb() {
        //在字符串形式实例化，需要加上完整的命名空间
        $className = "app\\recharge\\controller\\" . $this->channelPkgNumToClass[basename($_REQUEST['s'])];

        $control = new $className;   //ChannelXiaoqi
        $control->index();
    }


}