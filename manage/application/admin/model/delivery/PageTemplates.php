<?php

namespace app\admin\model\delivery;

use think\Model;


class PageTemplates extends Model
{

    

    

    // 表名
    protected $table = 'page_templates';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [

    ];




    public function getKVList()
    {
        $ret = [];
        $list = $this->field("template_id, name")->order('template_id', 'desc')->select();
        foreach ($list as $k => $v) {
            $ret[$v['template_id']] = $v['name'];
        }

        return $ret;
    }





}
