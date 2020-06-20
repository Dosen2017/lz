<?php
/**
 * Created by PhpStorm.
 * User: sds
 * Date: 2020/4/20
 * Time: 09:55
 */

namespace app\recharge\model;

use think\Model;

class GameUsers extends Model
{
    protected $createTime = '';//防止create_time在存储时，自动转换
}