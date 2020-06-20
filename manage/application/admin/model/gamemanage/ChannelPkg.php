<?php

namespace app\admin\model\gamemanage;

use think\Model;


class ChannelPkg extends Model
{

    

    

    // 表名
    protected $name = 'channel_pkg';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'os_type_text'
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

    public function getKVList()
    {
        $list = $this->field("channel_pkg_num, channel_pkg_name")->order('id', 'desc')->select();
        foreach ($list as $k => $v) {
            $ret[$v['channel_pkg_num']] = $v['channel_pkg_name'];
        }

        return $ret;
    }


}
