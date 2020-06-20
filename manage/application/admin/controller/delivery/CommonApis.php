<?php

namespace app\admin\controller\delivery;

use think\Session;
use think\Config;

use app\common\controller\Backend;

/**
 * 通用API
 *
 * @icon fa fa-circle-o
 */
class CommonApis extends Backend
{
    
    /**
     * CommonApis模型对象
     * @var \app\admin\model\delivery\CommonApis
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\delivery\CommonApis;

    }
    
    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */

    public function beforeAdd(&$params) {
        $params['add_time'] = time();
        $params['admin_id'] = Session::get("admin")['id'];
    }

    //加入对当前表数据的渲染方法
    public function indexMiddle(&$list) {

        //创建人列表
        $adminModel = new \app\admin\model\Admin();
        $adminList = $adminModel->getKVList();

        $url = "https://" . Config::get('api_domain') . "/login/Api/general/";
        foreach ($list as $k => $v) {
            $list[$k]['nickname'] = $adminList[$v['admin_id']] . "[" . $v['admin_id'] . "]";

//            https://iad.lezhonggame.com/API/Common/ioszb_cps_zh_16711?imei={deviceid}&Callback={callback}&IP={ip}&MAC={mac}

            $list[$k]['click_url'] = rtrim($url . $v['ads_id'] . "?"
                . ($v['query_deviceid'] ? $v['query_deviceid'] . "={deviceid}&" : '')
                . ($v['query_callback'] ? $v['query_callback'] . "={callback}&" : '')
                . ($v['query_ip'] ? $v['query_ip'] . "={ip}&" : '')
                . ($v['query_mac'] ? $v['query_mac'] . "={mac}" : ''), "&") ;
        }
    }

}
