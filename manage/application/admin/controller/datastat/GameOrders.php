<?php

namespace app\admin\controller\datastat;

use app\admin\model\gamemanage\Channel;
use app\admin\model\gamemanage\ChannelPkg;
use app\admin\model\gamemanage\Game;
use app\common\controller\Backend;

/**
 * 游戏订单
 *
 * @icon fa fa-circle-o
 */
class GameOrders extends Backend
{
    
    /**
     * GameOrders模型对象
     * @var \app\admin\model\datastat\GameOrders
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\datastat\GameOrders;
        $this->view->assign("osTypeList", $this->model->getOsTypeList());
    }
    
    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */

    public function getList(&$total) {

        $list = [];

        $where = '';
        $dateList = [];

        $this->conditionHandle(true, 'complete_time', $where, $dateList);
        foreach ($dateList as $k => $v) {
            $list[$k]['stat_date'] = date("Y-m-d", strtotime($v));

            $sql = "select count(1) as register_num from game_users where create_d = " . intval($v) . $where;
            $regData = $this->model->query($sql);
            $list[$k]['register_num'] = $regData[0]['register_num'];  //注册数量

            $sqlAll = "select  sum(amount) as pay_money, 
                                count(user_num) as pay_num , 
                                count(distinct user_num) as pay_person_num 
                                from game_orders where order_d = " . intval($v) . $where;
            $payDataAll = $this->model->query($sqlAll);

            $sqlLz = "select  sum(amount) as pay_lz_money, 
                                count(user_num) as pay_lz_num , 
                                count(distinct user_num) as pay_lz_person_num 
                                from game_orders where order_d = " . intval($v) . " and source != 'sdk'" . $where;
            $payDataLz = $this->model->query($sqlLz);

            $list[$k]['pay_money'] = round($payDataAll[0]['pay_money'] / 100, 2);     //充值金额
            $list[$k]['pay_lz_money'] = round($payDataLz[0]['pay_lz_money'] / 100, 2);  //官方金额
            $list[$k]['pay_pt_money'] = $list[$k]['pay_money'] - $list[$k]['pay_lz_money'];  //平台金额
            $list[$k]['pay_num'] = $payDataAll[0]['pay_num'];       //充值笔数
            $list[$k]['pay_lz_num'] = $payDataLz[0]['pay_lz_num'] ?: 0;    //官方笔数
            $list[$k]['pay_person_num'] = $payDataAll[0]['pay_person_num'];//充值人数
            $list[$k]['pay_lz_person_num'] = $payDataLz[0]['pay_lz_person_num'] ?: 0 ;  //官方人数
            $list[$k]['arrpu'] = $list[$k]['pay_person_num'] != 0 ? round($list[$k]['pay_money'] / $list[$k]['pay_person_num'], 2) : "0.00";       //ARRPU

            $sqlDau = "select count(distinct user_num) as dau from user_logs where opt_d = " . intval($v) . $where;
            $dauData = $this->model->query($sqlDau);
            $list[$k]['dau'] = $dauData[0]['dau'];          //DAU
        }

        return $list;
    }

}
