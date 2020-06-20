<?php
/**
 * Created by PhpStorm.
 * User: sds
 * Date: 2020/4/3
 * Time: 19:15
 */

namespace app\login\model;

use think\Model;

class ChannelUsers extends Model
{
    public function saveChannelUser($userData)
    {
        $this->save($userData);
    }

    public function getUserInfo($userNum)
    {
        return $this->field('channel_user_num')->where(['user_num' => $userNum])->find();
    }
}
