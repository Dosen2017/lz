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

class ChannelPayments extends Model
{

    protected $connection;

    public function __construct($data = []) {
        $this->connection = Config::get('manage_base');  //定义使用的数据库连接地址
        parent::__construct($data);
    }

    public function getPaymentsByChannelPkgNum($channelPkgNum) {

        $unique = Config::get('cache_keys_prefix')['channel_payments'] . $channelPkgNum;   //缓存使用uni
        $cache = null;
        $cache = Cache::get($unique);
        if ( !$cache ) {

            $field = 'strategy_class, strategy_json';
            $paymentInfo = $this->field($field)->where(['channel_pkg_num' => $channelPkgNum, 'use_switch' => 1])->order('order_id')->select();

//file_put_contents('/tmp/getPaymentsByChannelPkgNum.txt', date("Y-m-d H:i:s") . "___" . $channelPkgNum . "__" . $this->getLastSql() . "__" . json_encode($paymentInfo) . "\n", FILE_APPEND);

            Cache::set($unique, $paymentInfo, 3600); // 1小时文件缓存
            return $paymentInfo;
        }

        return $cache;
    }



}