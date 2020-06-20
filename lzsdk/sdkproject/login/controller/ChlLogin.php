<?php
/**
 * Created by PhpStorm.
 * User: sds
 * Date: 2019/2/27
 * Time: 14:30
 */

namespace app\login\controller;

class ChlLogin extends LoginCommon
{
    public $helperHandle;

    // 渠道与helper对应关系
    public $channel = array(
        //编号1分配给了自主账号系统
        2 => 'ChannelYsdk',  //腾讯
        3 => 'ChannelXiaoqi',  //小七
    );

    public function getAccountTest()
    {
        $this->isTest = true;
        $this->getAccount();
    }
}
