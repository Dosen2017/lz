<?php

namespace app\admin\controller\datastat;

use app\common\controller\Backend;

/**
 * 游戏玩家管理
 *
 * @icon fa fa-circle-o
 */
class DailyKeydata extends Backend
{
    
    /**
     * DailyKeydata模型对象
     * @var \app\admin\model\datastat\DailyKeydata
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\datastat\DailyKeydata;
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

        $this->conditionHandle(true, 'create_time', $where, $dateList);
        foreach ($dateList as $k => $v) {
            $list[$k]['stat_date'] = date("Y-m-d", strtotime($v));

            //COALESCE 加这个函数，是因为没有满足条件的数据，sum得到的是null, 而不是0
            $newSql = "select count(1) as new_player_num,
                        COALESCE(sum(charge_amount), 0) as new_all_amount,
                        COALESCE(sum(charge1_amount), 0) as new_amount, 
                        COALESCE(sum(charge1_count), 0) as new_charge_count,
                        count(charge1=1 or null) as new_pay_person_count
                         from game_users where create_d = " . intval($v) . $where;
            $newData = $this->model->query($newSql);

//            var_dump($newData);exit;

            $allSql = "select  sum(amount) as pay_money, 
                                count(user_num) as all_pay_num , 
                                count(distinct user_num) as pay_person_num 
                                from game_orders where order_d = " . intval($v) . $where;
            $allData = $this->model->query($allSql);

            $sqlDau = "select count(distinct user_num) as dau from user_logs where opt_d = " . intval($v) . $where;
            $dauData = $this->model->query($sqlDau);


            $list[$k]['new_player_num'] = $newData[0]['new_player_num'];  //新增人数
            $list[$k]['dau'] = $dauData[0]['dau'];          //DAU
            $list[$k]['old_player_num'] = $dauData[0]['dau'] - $newData[0]['new_player_num'];          //老玩家数
            $list[$k]['all_amount'] = get_yuan_amount($allData[0]['pay_money']);  //总充值金额
            $list[$k]['new_amount'] = get_yuan_amount($newData[0]['new_amount']); //新充金额
            $list[$k]['old_amount'] = get_yuan_amount($allData[0]['pay_money'] - $newData[0]['new_amount']); //老玩家充值金额

            //累计充值金额
            $list[$k]['new_all_amount'] = get_yuan_amount($newData[0]['new_all_amount']); //累计充值金额

            $list[$k]['pay_person_num'] = $allData[0]['pay_person_num']; //总充值人数
            $list[$k]['new_pay_person_count'] = $newData[0]['new_pay_person_count']; //新增充值人数
            $list[$k]['old_pay_person_count'] = $list[$k]['pay_person_num'] -  $newData[0]['new_pay_person_count']; //老玩家充值人数
            $list[$k]['all_pay_rate'] = $dauData[0]['dau'] != 0 ? round($list[$k]['pay_person_num'] / $dauData[0]['dau'], 4) * 100 . "%" : " 0%";  //总付费率
            $list[$k]['new_pay_rate'] = $newData[0]['new_player_num'] != 0 ? round($newData[0]['new_pay_person_count'] / $newData[0]['new_player_num'], 4) * 100 . "%" : " 0%";  //新增付费率
            $list[$k]['old_pay_rate'] = $list[$k]['old_player_num'] != 0 ? round($list[$k]['old_pay_person_count'] / $list[$k]['old_player_num'], 4) * 100 . "%" : " 0%";  //老玩家付费率
            $list[$k]['all_arpu'] = $allData[0]['all_pay_num'] != 0 ? round($list[$k]['all_amount'] / $allData[0]['pay_person_num'], 2) : 0;  //总付费ARPU
            $list[$k]['new_arpu'] = $newData[0]['new_pay_person_count'] != 0 ? round($list[$k]['new_amount'] / $newData[0]['new_pay_person_count'], 2) : 0;  //新增付费ARPU
            $list[$k]['old_arpu'] = $list[$k]['old_pay_person_count'] != 0 ? round($list[$k]['old_amount'] / $list[$k]['old_pay_person_count'], 2) : 0;  //老玩家ARPU
            $list[$k]['dau_arpu'] = $dauData[0]['dau'] != 0 ? round($list[$k]['all_amount'] / $dauData[0]['dau'], 2) : 0;  //DAUARPU

            $list[$k]['new_ltv'] = $list[$k]['new_player_num'] != 0 ? round($list[$k]['new_amount'] / $list[$k]['new_player_num'], 2) : 0;  //新增LTV
            $list[$k]['time_ltv'] = $list[$k]['new_player_num'] != 0 ? round($list[$k]['new_all_amount'] / $list[$k]['new_player_num'], 2) : 0;  //实时LTV

            $list[$k]['all_pay_num'] = $allData[0]['all_pay_num']; //总充值笔数
            $list[$k]['new_charge_count'] = $newData[0]['new_charge_count']; //新增充值笔数
            $list[$k]['old_charge_count'] = $list[$k]['all_pay_num'] - $newData[0]['new_charge_count']; //老玩家充值笔数
        }

        return $list;
    }

}
