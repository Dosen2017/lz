<?php
/**
 * Created by PhpStorm.
 * User: sds
 * Date: 2019/2/28
 * Time: 19:10
 */

namespace app\login\model;

use think\Model;
use think\Config;

/**
 * Class Game
 * @package app\login\model
 *
 * 需要注意的是，ThinkPHP的数据库连接是惰性的，所以并不是在实例化的时候就连接数据库，而是在有实际的数据操作的时候才会去连接数据库。
 *
 */
class CommonApis extends Model
{

    protected $connection;

    public function __construct($data = []) {
        $this->connection = Config::get('manage_base');  //定义使用的数据库连接地址
        parent::__construct($data);
    }

    public function getInfoData($adsId) {
        $return = [];

        $ret = $this->field('id, channel_id, query_deviceid, query_ip, query_mac, query_callback')->where(['ads_id' => $adsId])->find();
        if (empty($ret['channel_id'])) {
            return false;
        }

        $return['id'] = $ret['id'];
        $return['channel_id'] = $ret['channel_id'];
        $return['query_deviceid'] = $ret['query_deviceid'];
        $return['query_ip'] = $ret['query_ip'];
        $return['query_mac'] = $ret['query_mac'];
        $return['query_callback'] = $ret['query_callback'];

        //待加缓存
        return $return;
    }

}