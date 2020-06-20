<?php

namespace app\admin\model\adstat;

use think\Model;


class GameOrders extends Model
{

    

    protected $connection = 'db_sdk';

    // 表名
    protected $table = 'game_orders';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'os_type_text',
        'create_time_text',
        'pay_time_text',
        'complete_time_text'
    ];
    

    
    public function getOsTypeList()
    {
        return ['1' => __('Os_type 1'), '2' => __('Os_type 2'), '3' => __('Os_type 3'), '4' => __('Os_type 4'), '5' => __('Os_type 5')];
    }


    public function getOsTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['os_type']) ? $data['os_type'] : '');
        $list = $this->getOsTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getCreateTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['create_time']) ? $data['create_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getPayTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['pay_time']) ? $data['pay_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getCompleteTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['complete_time']) ? $data['complete_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setCreateTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setPayTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setCompleteTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


}
