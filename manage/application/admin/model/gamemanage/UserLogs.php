<?php

namespace app\admin\model\gamemanage;

use think\Model;


class UserLogs extends Model
{

    

    protected $connection = 'db_sdk';

    // 表名
    protected $table = 'user_logs';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'os_type_text',
        'opt_time_text'
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


    public function getOptTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['opt_time']) ? $data['opt_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setOptTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


}
