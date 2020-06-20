<?php

namespace app\admin\controller\gamemanage;

use app\common\controller\Backend;

/**
 * 订单记录
 *
 * @icon fa fa-circle-o
 */
class Orders extends Backend
{
    
    /**
     * Orders模型对象
     * @var \app\admin\model\gamemanage\Orders
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\gamemanage\Orders;
        $this->view->assign("osTypeList", $this->model->getOsTypeList());
        $this->view->assign("stateList", $this->model->getStateList());
    }
    
    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */

    //加入对当前表数据的渲染方法
    public function indexMiddle(&$list) {

        $cModel = new \app\admin\model\gamemanage\Game();
        $gameList = $cModel->getKVList();

        $cpModel = new \app\admin\model\gamemanage\ChannelPkg();
        $channelPkgList = $cpModel->getKVList();
//
        foreach ($list as $k => $v) {
            $list[$k]['game_name'] = $gameList[$v['game_num']] . "[" . $v['game_num'] . "]";  //所属游戏
        }

        foreach ($list as $k => $v) {
            $list[$k]['channel_pkg_name'] = $channelPkgList[$v['channel_pkg_num']] . "[" . $v['channel_pkg_num'] . "]";  //渠道包ID
        }

        foreach ($list as $k => $v) {
            $list[$k]['real_amount'] = round($v['real_amount'] / 100, 2);
            $list[$k]['amount'] = round($v['amount'] / 100, 2);
        }

        //js的number类型有个最大值（安全值）。即2的53次方，为9007199254740992。如果超过这个值，那么js会出现不精确的问题。这个值为16位。
        //解决方法：后端下发字符串类型。
        foreach ($list as $k => $v) {
            $list[$k]['my_order_num'] = "" . $v['my_order_num'];
        }

    }
}
