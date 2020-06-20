<?php

namespace app\admin\model\channelpack;

use app\admin\model\gamemanage\Channel;
use app\admin\model\gamemanage\Game;
use think\Model;


class ChannelPack extends Model
{

    

    

    // 表名
    protected $table = 'channel_pack';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'game_name',
        'channel_name',
        'keystore_name',
        'icon_name',
        'basepack_name',
    ];

    public function getGameNameAttr($value,$data)
    {
        $name = Game::where(['id' => $data['game_num']])->value('name');
        return $name;
    }

    public function getChannelNameAttr($value,$data)
    {
        $name = Channel::where(['id' => $data['channel_num']])->value('name');
        return $name;
    }

    public function getKeystoreNameAttr($value,$data)
    {
        $name = Keystore::where(['id' => $data['keystore_id']])->value('remark');
        return $name;
    }

    public function getIconNameAttr($value,$data)
    {
        $name = Icons::where(['id' => $data['icon_id']])->value('remark');
        return $name;
    }
    public function getBasepackNameAttr($value,$data)
    {
        $name = Basepack::where(['id' => $data['basepack_id']])->value('remark');
        return $name;
    }


    public function getKVList()
    {
        $ret = [];
        $list = $this->field("id, channel_pack_tag")->order('id', 'desc')->select();
        foreach ($list as $k => $v) {
            $ret[$v['id']] = $v['channel_pack_tag'];
        }

        return $ret;
    }



}
