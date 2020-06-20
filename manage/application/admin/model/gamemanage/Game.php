<?php

namespace app\admin\model\gamemanage;

use think\Model;


class Game extends Model
{

    

    

    // 表名
    protected $name = 'game';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'os_text'
    ];
    

    
    public function getOsList()
    {
        return ['1' => __('Os 1'), '2' => __('Os 2'), '3' => __('Os 3'), '4' => __('Os 4'), '5' => __('Os 5')];
    }


    public function getOsTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['os']) ? $data['os'] : '');
        $list = $this->getOsList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function getGameParams($Id)
    {
        return $this->field('app_num, app_key, pay_key')->find(['id' => $Id]);
    }


}
