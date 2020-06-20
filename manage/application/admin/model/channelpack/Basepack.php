<?php

namespace app\admin\model\channelpack;

use app\admin\model\gamemanage\Game;
use think\Model;


class Basepack extends Model
{

    

    

    // 表名
    protected $table = 'basepack';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'type_text',
        'game_name',
    ];


    public function getGameNameAttr($value,$data)
    {
        $name = Game::where(['id' => $data['game_num']])->value('name');
        return $name;
    }



    public function getTypeList()
    {
        return ['1' => __('Type 1'), '2' => __('Type 2')];
    }


    public function getTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['type']) ? $data['type'] : '');
        $list = $this->getTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }




}
