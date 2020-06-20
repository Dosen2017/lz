<?php

namespace app\admin\controller\gamemanage;

use app\common\controller\Backend;

/**
 * 游戏玩家管理
 *
 * @icon fa fa-circle-o
 */
class GameUsers extends Backend
{
    
    /**
     * GameUsers模型对象
     * @var \app\admin\model\gamemanage\GameUsers
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\gamemanage\GameUsers;
        $this->view->assign("osTypeList", $this->model->getOsTypeList());
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
//
        $caModel = new \app\admin\model\gamemanage\Channel();
        $channelList = $caModel->getKVList();

        $cpModel = new \app\admin\model\gamemanage\ChannelPkg();
        $channelPkgList = $cpModel->getKVList();
//
        foreach ($list as $k => $v) {
            $list[$k]['game_name'] = $gameList[$v['game_num']] . "[" . $v['game_num'] . "]";  //所属游戏
        }
//
        foreach ($list as $k => $v) {
            $list[$k]['channel_name'] = $channelList[$v['channel_num']] . "[" . $v['channel_num'] . "]";  //所属渠道商
        }

        foreach ($list as $k => $v) {
            $list[$k]['channel_pkg_name'] = $channelPkgList[$v['channel_pkg_num']] . "[" . $v['channel_pkg_num'] . "]";  //渠道包ID
        }

        foreach ($list as $k => $v) {
            $list[$k]['charge_amount'] = round($v['charge_amount'] / 100, 2);
            $list[$k]['charge_amount_lz'] = round($v['charge_amount_lz'] / 100, 2);
        }

    }

    /**
     * 登录明细
     */
    public function user_log($ids)
    {

        $gameUsersInfo = $this->model->where(['game_user_num' => $ids])->field('user_num, channel_pkg_num')->find();

        $userLogsModel = new \app\admin\model\gamemanage\UserLogs();
        $rows = $userLogsModel->where(['user_num' => $gameUsersInfo['user_num'], 'channel_pkg_num' => $gameUsersInfo['channel_pkg_num']])->order('opt_time desc')->select();
//        echo $ids;exit;

        $this->view->assign("row", $rows);
        return $this->view->fetch();
    }

    /**
     * 充值明细
     */
    public function pay_log($ids)
    {

        $gameUsersInfo = $this->model->where(['game_user_num' => $ids])->field('user_num, channel_pkg_num')->find();

        $gameOrdersModel = new \app\admin\model\gamemanage\GameOrders();
        $rows = $gameOrdersModel->where(['user_num' => $gameUsersInfo['user_num'], 'channel_pkg_num' => $gameUsersInfo['channel_pkg_num']])->order('pay_time desc')->select();
//        echo $ids;exit;

        foreach ($rows as $k => $v) {
            $rows[$k]['amount'] = round($v['amount'] / 100, 2);
        }

        $this->view->assign("row", $rows);
        return $this->view->fetch();
    }

}
