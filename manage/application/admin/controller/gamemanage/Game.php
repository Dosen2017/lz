<?php

namespace app\admin\controller\gamemanage;

use app\common\controller\Backend;
use think\Config;

/**
 * 游戏管理
 *
 * @icon fa fa-circle-o
 */
class Game extends Backend
{
    const PRODUCT_APPID_APPKEY_KEY = 'CF~$#@@#$APP~KEY~ID~2020s#@DWDA';
    /**
     * Game模型对象
     * @var \app\admin\model\gamemanage\Game
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\gamemanage\Game;
        $this->view->assign("osList", $this->model->getOsList());
    }
    
    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */

    public function selectpageMiddle(&$list)
    {
        foreach ($list as $k => $v) {
//            $list[$k]['name'] = $v['name'] . "<font color='#8b0000'>[" . $v['id'] . "]</font>";
            $list[$k]['name'] = $v['name'] . "【" . $v['id'] . "】";
        }
    }


    public function beforeAdd(&$params) {

        //生成app_id
        if ( $maxValue = $this->model->max('app_num') ) {
            $params['app_num'] = $maxValue + 1;
        } else {
            $params['app_num'] = 50000;
        }
        $randNum = random(5);
        $params['app_key'] = md5($params['app_num'] . $randNum . self::PRODUCT_APPID_APPKEY_KEY);
        $params['pay_key'] = md5($params['app_num'] . $randNum . date('Y-m-d H:i:s') . self::PRODUCT_APPID_APPKEY_KEY);


    }

    /**
     * 详情
     */
    public function detail($ids)
    {
        $row = $this->model->get(['id' => $ids]);
        if (!$row)
            $this->error(__('No Results were found'));
        $this->view->assign("row", $row->toArray());
        return $this->view->fetch();
    }

    public function afterEdit($row) {

        $time = time();
        $data['game_num'] =  $row['id'];
        $data['time'] = $time;
        $data['sign'] = md5($data['game_num'] . $data['time'] . Config::get('clear_cache_sign_key'));
        //清除登录的缓存
        $loginUrl = "http://" . Config::get('api_domain') . "/login/ClearCache/game";
        $retLogin = file_get_contents($loginUrl . "?" . http_build_query($data));

        //清除充值的缓存
        $payUrl = "http://" . Config::get('api_domain') . "/recharge/ClearCache/game";
        $retPay = file_get_contents($payUrl . "?" . http_build_query($data));

        if ($retLogin !== "SUCCESS" || $retPay !== "SUCCESS" ) {
            $this->error(__('清除缓存失败！') );
        }
    }

}
