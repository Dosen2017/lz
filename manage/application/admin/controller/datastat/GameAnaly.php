<?php

namespace app\admin\controller\datastat;

use app\common\controller\Backend;

/**
 * 游戏管理
 *
 * @icon fa fa-circle-o
 */
class GameAnaly extends Backend
{
    
    /**
     * GameAnaly模型对象
     * @var \app\admin\model\datastat\GameAnaly
     */
    protected $model = null;
    protected $gameUsersModel = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\datastat\GameAnaly;
        $this->gameUsersModel = new \app\admin\model\gamemanage\GameUsers();
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

        $this->conditionHandle(true, 'create_time', $where, $dateList);

        $startTm = $dateList[count($dateList)-1];
        $endTm = $dateList[0];

        $totalTj = [];
        $totalTj['game_name'] = '总计';
        $totalTj['register_num'] = 0;
        $totalTj['charge_amount'] = 0;
        $totalTj['charge_amount_lz'] = 0;
        $totalTj['charge_amount_pt'] = 0;
        $totalTj['charge_count'] = 0;
        $totalTj['charge_count_lz'] = 0;
        $totalTj['charge_num'] = 0;
        $totalTj['charge_num_lz'] = 0;
        $totalTj['arrpu'] = 0;

        $gameList = $this->model->field('id, name')->select();
        foreach ($gameList as $k => $game) {
            $list[$k]['game_name'] = $game['name'];
            $sql = "select count(game_user_num) AS register_num
                    from game_users 
                    where game_num={$game['id']} and create_d >= {$startTm} and create_d <= {$endTm} {$where}";
            $selectData = $this->gameUsersModel->query($sql);
            $list[$k] = array_merge($list[$k], $selectData[0]);

            $sql = "select count(distinct user_num) as charge_num, 
sum(amount) as charge_amount, 
sum(if(source!='sdk', amount, null)) as charge_amount_lz, 
count(id) as charge_count, 
count(if(source!='sdk', 1, null)) as charge_count_lz,
count(distinct user_num, if(source!='sdk', 1, null)) as charge_num_lz
from game_orders where game_num={$game['id']} and notice_result='SUCCESS' and order_d >= {$startTm} and order_d <= {$endTm} {$where}";
            $selectData = $this->gameUsersModel->query($sql);
            $list[$k] = array_merge($list[$k], $selectData[0]);

            $list[$k]['charge_amount'] = bcdiv($list[$k]['charge_amount'], 100, 2);
            $list[$k]['charge_amount_lz'] = bcdiv($list[$k]['charge_amount_lz'], 100, 2);
            $list[$k]['charge_amount_pt'] = $list[$k]['charge_amount'] - $list[$k]['charge_amount_lz'];
            // arrpu
            $list[$k]['arrpu'] = $list[$k]['charge_num'] != 0 ? bcdiv($list[$k]['charge_amount'], $list[$k]['charge_num'], 2) : 0;

            $totalTj['register_num'] += $list[$k]['register_num'];
            $totalTj['charge_amount'] += $list[$k]['charge_amount'];
            $totalTj['charge_amount_lz'] += $list[$k]['charge_amount_lz'];
            $totalTj['charge_amount_pt'] += $list[$k]['charge_amount_pt'];
            $totalTj['charge_count'] += $list[$k]['charge_count'];
            $totalTj['charge_count_lz'] += $list[$k]['charge_count_lz'];
            $totalTj['charge_num'] += $list[$k]['charge_num'];
            $totalTj['charge_num_lz'] += $list[$k]['charge_num_lz'];

        }

        $totalTj['arrpu'] = $totalTj['charge_num'] != 0 ? bcdiv($totalTj['charge_amount'], $totalTj['charge_num'], 2) : 0;

        $list[] = $totalTj;
        $total = count($list);
        return $list;
    }

}
