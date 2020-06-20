<?php

namespace app\admin\model\gamemanage;

use think\Model;


class Channel extends Model
{

    

    

    // 表名
    protected $name = 'channel';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [

    ];

    //2020.02.17 add by sudesheng
//    public function getKVList()
//    {
//        $list = $this->field("id, channel_name")->order('id', 'desc')->select();
//        foreach ($list as $k => $v) {
//            $ret[$v['id']] = $v['channel_name'];
//        }
//
//        return $ret;
//    }
    







}
