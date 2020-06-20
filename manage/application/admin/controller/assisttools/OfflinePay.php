<?php

namespace app\admin\controller\assisttools;

use app\common\controller\Backend;
use think\Config;
use think\Session;

/**
 * 线下充值记录
 *
 * @icon fa fa-circle-o
 */
class OfflinePay extends Backend
{
    
    /**
     * OfflinePay模型对象
     * @var \app\admin\model\assisttools\OfflinePay
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\assisttools\OfflinePay;
        $this->view->assign("pTypeList", $this->model->getPTypeList());
        $this->view->assign("statusList", $this->model->getStatusList());
    }
    
    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */

    public function indexMiddle(&$list) {

        //创建人列表
//        $adminModel = new \app\admin\model\Admin();
//        $adminList = $adminModel->getKVList();

        //js的number类型有个最大值（安全值）。即2的53次方，为9007199254740992。如果超过这个值，那么js会出现不精确的问题。这个值为16位。
        //解决方法：后端下发字符串类型。
        foreach ($list as $k => $v) {
            $list[$k]['order_num'] = "" . $v['order_num'];

//            $list[$k]['nickname'] = $adminList[$v['admin_id']] . "[" . $v['admin_id'] . "]";
        }

    }

    public function beforeAdd(&$params) {
//        $params['p_type'] =
//        $params['money'] =
//        $params['bkinfo'] =

        if (!is_numeric($params['money'])) {
            $this->error("金额值有误！");
        }

        $time = time();
        $params['order_num'] = date('YmdHis') . random(3, '123456789');
        $params['status'] = 1;
        $params['c_time'] = $time;

        $data['order_num'] =  $params['order_num'];
        $data['money'] =  $params['money'] * 100;
        $data['time'] = $time;
        $data['sign'] = md5($data['order_num'] . $data['money'] . $data['time'] . Config::get('clear_cache_sign_key'));

        //清除登录的缓存
        $loginUrl = "http://" . Config::get('api_domain') . "/recharge/QrCode";
        $params['code_url'] = file_get_contents($loginUrl . "?" . http_build_query($data));

        $params['admin_id'] = Session::get("admin")['id'];

    }

    public function qrcode_gen($ids) {

        $offlinePayInfo = $this->model->where(['id' => $ids])->field('code_url, status, c_time')->find();

        if ($offlinePayInfo['status'] != 1) {
            echo "当前状态不能生成二维码，请新添加记录生成！";exit;
        }

        if ($offlinePayInfo['c_time'] < time() - 60*60) {
            echo "当前支付链接已超时，请新生成支付链接后再生成";exit;
        }

        if(substr($offlinePayInfo['code_url'], 0, 6) == "weixin"){
            Vendor('phpqrcode.phpqrcode');
            echo \QRcode::png($offlinePayInfo['code_url']);exit;
        }else{
            header('HTTP/1.1 404 Not Found');
        }
    }
}
