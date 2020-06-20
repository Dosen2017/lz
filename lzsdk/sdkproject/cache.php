<?php
/**
 * Created by PhpStorm.
 * User: sds
 * Date: 2020/04/21
 * Time: 11:49  ooo
 */

return [
    'type' => 'complex',
    // 默认
    'default' => [
        // 驱动方式
        'type'   => 'File',
        // 缓存保存目录
        'path'   => CACHE_PATH,
        // 缓存前缀
        'prefix' => '',
        // 缓存有效期 0表示永久缓存
        'expire' => 0,
    ],

    // 文件缓存
//    'file'   =>  [
//        // 驱动方式
//        'type'   => 'file',
//        // 设置不同的缓存保存目录
//        'path'   => RUNTIME_PATH . 'file/',
//    ],

    // redis
    'redis' =>  [
        'type'       => 'redis',
        'host'       => '127.0.0.1',
        'port'       =>  '6379',
        'password'   => 'foo123456',
        // 全局缓存有效期（0为永久有效）
        'expire'     =>  0,
        // 缓存前缀
        'prefix'     =>  '',
    ],
];