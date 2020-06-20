<?php
/**
 * Created by PhpStorm.
 * User: sds
 * Date: 2019/2/27
 * Time: 11:41
 */

// [ 应用入口文件 ]

declare(strict_types = 1);
//echo 9999;exit;
// 定义应用目录
define('APP_NAME', 'sdk');
define('APP_PATH', __DIR__ . '/../../sdkproject/');

//echo 99;exit;
// 绑定当前访问到index模块
define('BIND_MODULE','recharge');

// 加载框架引导文件
require __DIR__ . '/../../core/start.php';