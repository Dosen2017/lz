<?php

namespace app\login\controller;

use app\login\model\Users;
use think\Config;
use think\Controller;
use think\Request;
use think\Cache;

class ClearCache extends Controller
{
    protected $key;
    // 初始化
    protected function _initialize()
    {
        $this->key = Config::get('cache_sign_key');
    }

    //默认为清理channelPkgNum的缓存
    public function index()
    {
        $request = Request::instance();
        $channelPkgNum = $request->get('channel_pkg_num');
        $time = $request->get('time');
        $sign = $request->get('sign');

        $checkSign = md5($channelPkgNum . $time . $this->key );

        $unique = Config::get('cache_keys_prefix')['channel_pkg'] . $channelPkgNum;   //缓存使用uni

        if ($sign == $checkSign ) {
            Cache::rm($unique) ;
            echo "SUCCESS"; exit;
        }

        echo "FAIL"; exit;
    }

    //清理game表的缓存
    public function game()
    {
        $request = Request::instance();
        $gameNum = $request->get('game_num');
        $time = $request->get('time');
        $sign = $request->get('sign');

        $checkSign = md5($gameNum . $time . $this->key );

        $unique = Config::get('cache_keys_prefix')['game'] . $gameNum;   //缓存使用uni

        if ($sign == $checkSign ) {
            Cache::rm($unique) ;
            echo "SUCCESS"; exit;
        }

        echo "FAIL"; exit;
    }

    //清理Users表的缓存
    public function user()
    {
        $request = Request::instance();
        $userNum = $request->get('user_num');
        $time = $request->get('time');
        $sign = $request->get('sign');

        $checkSign = md5($userNum . $time . $this->key );

        if ($sign == $checkSign ) {
            $userModel = new Users();
            $userModel->clearCacheByUserNum($userNum);
            echo "SUCCESS"; exit;
        }

        echo "FAIL"; exit;
    }

}
