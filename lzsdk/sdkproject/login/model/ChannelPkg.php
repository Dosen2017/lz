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

class ChannelPkg extends Model
{

    protected $connection;

    public function __construct($data = []) {
        $this->connection = Config::get('manage_base');  //定义使用的数据库连接地址
        parent::__construct($data);
    }

    public function getAppInfoByChannelPkgNum($channelPkgNum) {

        $unique = Config::get('cache_keys_prefix')['channel_pkg'] . $channelPkgNum;   //缓存使用uni
        $cache = null;
        $cache = Cache::get($unique);
        if ( !$cache ) {
            //有实际操作才会去连接数据库
            $field = 'payment_num, notify_url, productjson,
             pay_lockswitch, pay_smswitch, login_lockswitch, register_lockswitch, login_smswitch, quick_regitserswitch, fcmswitch,
             os_type';
            $appInfo = $this->field($field)->where(['channel_pkg_num' => $channelPkgNum])->find();

            if (empty($appInfo)) {
                return false;
            }

            Cache::set($unique, $appInfo, 3600); // 1小时文件缓存
            return $appInfo;
        }

        return $cache;
    }

//    public function getProductIdsAndMoney($channelPkgNum) {
//
//        $productJson = $this->getAppInfoByChannelPkgNum($channelPkgNum)['productjson'];
//
//        return json_decode($productJson, true);
//    }

}