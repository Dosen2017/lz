<?php

namespace app\admin\model\delivery;

use think\Model;


class UserTemplates extends Model
{

    

    

    // 表名
    protected $table = 'user_templates';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'redirect_type_text'
    ];
    

    
    public function getRedirectTypeList()
    {
        return ['1' => __('Redirect_type 1'), '2' => __('Redirect_type 2'), '3' => __('Redirect_type 3'), '4' => __('Redirect_type 4')];
    }


    public function getRedirectTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['redirect_type']) ? $data['redirect_type'] : '');
        $list = $this->getRedirectTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function getKVList()
    {
        $ret = [];
        $list = $this->field("id, page_name")->order('id', 'desc')->select();
        foreach ($list as $k => $v) {
            $ret[$v['id']] = $v['page_name'];
        }

        return $ret;
    }


}
