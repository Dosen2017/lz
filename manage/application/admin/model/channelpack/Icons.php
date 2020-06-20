<?php

namespace app\admin\model\channelpack;

use app\admin\model\gamemanage\Game;
use think\Model;


class Icons extends Model
{

    

    

    // 表名
    protected $table = 'icons';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'game_name'
    ];


    public function getGameNameAttr($value,$data)
    {
        $name = Game::where(['id' => $data['game_num']])->value('name');
        return $name;
    }
    







}
