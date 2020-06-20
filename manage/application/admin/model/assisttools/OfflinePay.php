<?php

namespace app\admin\model\assisttools;

use think\Model;


class OfflinePay extends Model
{

    

    protected $connection = 'db_sdk';

    // 表名
    protected $table = 'offline_pay';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'p_type_text',
        'c_time_text',
        'status_text',
        'cb_time_text'
    ];
    

    
    public function getPTypeList()
    {
        return ['1' => __('P_type 1'), '2' => __('P_type 2'), '3' => __('P_type 3')];
    }

    public function getStatusList()
    {
        return ['1' => __('Status 1'), '2' => __('Status 2'), '3' => __('Status 3')];
    }


    public function getPTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['p_type']) ? $data['p_type'] : '');
        $list = $this->getPTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getCTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['c_time']) ? $data['c_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getCbTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['cb_time']) ? $data['cb_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setCTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setCbTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


}
