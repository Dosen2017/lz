<?php

namespace app\admin\model\delivery;

use think\Model;


class UserPages extends Model
{

    

    

    // 表名
    protected $table = 'user_pages';
    
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
        return ['5' => __('Redirect_type 5'), '1' => __('Redirect_type 1'), '2' => __('Redirect_type 2'), '3' => __('Redirect_type 3'), '4' => __('Redirect_type 4')];
    }


    public function getRedirectTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['redirect_type']) ? $data['redirect_type'] : '');
        $list = $this->getRedirectTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function getVVList()
    {
        $ret = [];
        $list = $this->field("id, ads_id")->order('id', 'desc')->select();
        foreach ($list as $k => $v) {
            $ret[$v['ads_id']] = $v['ads_id'];
        }

        return $ret;
    }

    public function getKVList()
    {
        $ret = [];
        $list = $this->field("id, ads_id")->order('id', 'desc')->select();
        foreach ($list as $k => $v) {
            $ret[$v['id']] = $v['ads_id'];
        }

        return $ret;
    }

}
