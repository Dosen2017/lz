<?php

namespace app\recharge\controller;

use think\Controller;
use think\Request;
use think\Cache;
use think\Config;

class ClearCache extends Controller
{

    protected $key;
    // 初始化
    protected function _initialize()
    {
        $this->key = Config::get('cache_sign_key');
    }

    public function index()
    {
        $request = Request::instance();
        $channelPkgNum = $request->get('channel_pkg_num');
        $time = $request->get('time');
        $sign = $request->get('sign');

        $checkSign = md5($channelPkgNum . $time . $this->key );

//        $unique = 'lzsdk:recharge:Model:ChannelPkg:' . $channelPkgNum;   //缓存使用uni

        $unique = Config::get('cache_keys_prefix')['channel_pkg'] . $channelPkgNum;   //缓存使用uni

        if ($sign == $checkSign ) {
            Cache::rm($unique);
            echo "SUCCESS"; exit;
        }

        echo "FAIL"; exit;
    }

    public function game()
    {
        $request = Request::instance();
        $gameNum = $request->get('game_num');
        $time = $request->get('time');
        $sign = $request->get('sign');

        $checkSign = md5($gameNum . $time . $this->key );

        $unique = Config::get('cache_keys_prefix')['game'] . $gameNum;   //缓存使用uni

        if ($sign == $checkSign ) {
            Cache::rm($unique);
            echo "SUCCESS"; exit;
        }

        echo "FAIL"; exit;
    }

    public function channel_payments()
    {
        $request = Request::instance();
        $channelPkgNum = $request->get('channel_pkg_num');
        $time = $request->get('time');
        $sign = $request->get('sign');

        $checkSign = md5($channelPkgNum . $time . $this->key );

        $unique = Config::get('cache_keys_prefix')['channel_payments'] . $channelPkgNum;   //缓存使用uni

        if ($sign == $checkSign ) {
            Cache::rm($unique);
            echo "SUCCESS"; exit;
        }

        echo "FAIL"; exit;
    }
}
