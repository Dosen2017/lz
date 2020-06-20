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
class PageStats extends Model
{

    protected $connection;

    public function __construct($data = []) {
        $this->connection = Config::get('manage_base');  //定义使用的数据库连接地址
        parent::__construct($data);
    }


}