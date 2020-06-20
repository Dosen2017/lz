<?php
/**
 * Created by PhpStorm.
 * User: sds
 * Date: 2019/3/1
 * Time: 11:25
 */

namespace app\login\model;

use think\Model;
use think\Config;
use think\Cache;

class Channel extends Model
{

    protected $connection;

    public function __construct($data = []) {
        $this->connection = Config::get('manage_base');  //定义使用的数据库连接地址
        parent::__construct($data);
    }

    public function getChannelInfoByChannelId($channelId) {

        $unique = Config::get('cache_keys_prefix')['channel'] . $channelId;   //缓存使用uni
        $cache = null;
//        $cache = Cache::get($unique);
        if ( !$cache ) {
            //有实际操作才会去连接数据库
            $field = 'channel_suffix, channel_configjson';
            $channelInfo = $this->field($field)->where(['id' => $channelId])->find();

            if (empty($channelInfo)) {
                return false;
            }

            Cache::set($unique, $channelInfo, 3600); // 1小时文件缓存
            return $channelInfo;
        }

        return $cache;
    }


}