<?php
/**
 * Created by PhpStorm.
 * User: sds
 * Date: 2019/3/1
 * Time: 11:25
 */

namespace app\recharge\model;

use think\Model;
use think\Config;
use think\Cache;

class Game extends Model
{

    protected $connection;

    public function __construct($data = []) {
        $this->connection = Config::get('manage_base');  //定义使用的数据库连接地址
        parent::__construct($data);
    }

    public function getAppInfoByGameId($gameId) {

        $unique = Config::get('cache_keys_prefix')['game'] . $gameId;   //缓存使用uni
        $cache = null;
        $cache = Cache::get($unique);
        if ( !$cache ) {
            //有实际操作才会去连接数据库
            $appInfo = $this->field('app_num, app_key, pay_key, notify_url, web_notify_url')->where(['id' => $gameId])->find();
            Cache::set($unique, $appInfo, 3600); // 1小时文件缓存
            return $appInfo;
        }

        return $cache;

        //需要加缓存
//        return $this->field('app_key, pay_key')->where(['app_id' => $appId])->find();
    }

}