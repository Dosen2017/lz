<?php
/**
 * Created by PhpStorm.
 * User: sds
 * Date: 2020/5/18
 * Time: 12:00
 */

namespace app\login\helper;

use think\Config;
use think\Helper;

/**
 * Class RedisH
 * @package app\login\helper
 * 极简版redis操作类
 */
class RedisH extends Helper
{

    protected $redisHandler;

    public function __construct()
    {
        //得到redis的配置参数
        $redisConfig = Config::get('cache')['redis'];
        try {
            //连接本地的 Redis 服务
            $this->redisHandler = new \Redis();
            $this->redisHandler->connect($redisConfig['host'], $redisConfig['port'], 2);
            $this->redisHandler->auth($redisConfig['password']);

        } catch (\RedisException $e) {
            //这里设置，主要是针对redis挂掉后。不要再执行实际操作，避免导致整个服务瘫痪
            $this->redisHandler = false;
        }
    }

    public function set($name, $value, $expire = 60)
    {
        if (empty($name) || $this->redisHandler == false) {
            return false;
        }
        return $this->redisHandler->set($name, $value, $expire);
    }

    public function get($name)
    {
        if (empty($name) || $this->redisHandler == false) {
            return false;
        }
        return $this->redisHandler->get($name);
    }

    public function del($name)
    {
        if (empty($name) || $this->redisHandler == false) {
            return false;
        }
        return $this->redisHandler->del($name);
    }
}
