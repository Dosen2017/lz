<?php

namespace app\admin\controller\datastat;

use app\common\controller\Backend;

/**
 * 游戏玩家管理
 *
 * @icon fa fa-circle-o
 */
class PayTrend extends Backend
{
    
    /**
     * PayTrend模型对象
     * @var \app\admin\model\datastat\PayTrend
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\datastat\PayTrend;
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
        $chargeDays =  [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,30,60];

        $this->conditionHandle(true, 'create_time', $where, $dateList);
        foreach ($dateList as $k => $v) {
            $list[$k]['stat_date'] = date("Y-m-d", strtotime($v));

            $sql = "select count(1) as register_num,
                        count(charge1=1 or null) as new_charge_person_count,
                        sum(charge1_amount) as a_charge1_amount, 
                        sum(charge2_amount) as a_charge2_amount,
                        sum(charge3_amount) as a_charge3_amount,
                        sum(charge4_amount) as a_charge4_amount,
                        sum(charge5_amount) as a_charge5_amount,
                        sum(charge6_amount) as a_charge6_amount,
                        sum(charge7_amount) as a_charge7_amount,
                        sum(charge8_amount) as a_charge8_amount,
                        sum(charge9_amount) as a_charge9_amount,
                        sum(charge10_amount) as a_charge10_amount,
                        sum(charge11_amount) as a_charge11_amount,
                        sum(charge12_amount) as a_charge12_amount,
                        sum(charge13_amount) as a_charge13_amount,
                        sum(charge14_amount) as a_charge14_amount,
                        sum(charge15_amount) as a_charge15_amount,
                        sum(charge30_amount) as a_charge30_amount,
                        sum(charge60_amount) as a_charge60_amount,
                        sum(charge_amount)   as a_charge_amount,
                        sum(charge_amount_lz) as a_charge_amount_lz
                         from game_users where create_d = " . intval($v) . $where;
            $regData = $this->model->query($sql);
//            var_dump($regData);exit;

            $list[$k]['register_num'] = $regData[0]['register_num'];  //注册数量
            $sumAmout = 0;
            foreach ($chargeDays as $val) {

                $n = (int)((time() - strtotime($v)) / 86400) + 1; //计算出第几天登录
                if ( $val > $n ) {
                    $sumAmout = "-";
                    $list[$k]['a_charge' . $val . '_amount'] = "-<br/><font color='#dc143c'>-</font>";
                    continue;
                }

                $sumAmout += $regData[0]['a_charge' . $val . '_amount'];
                $list[$k]['a_charge' . $val . '_amount'] = get_yuan_amount($sumAmout) . "<br/><font color='#dc143c'>" . ($regData[0]['register_num'] != 0 ? (round(get_yuan_amount($sumAmout) / $regData[0]['register_num'], 2) ?: "0.00") : "0.00") . "</font>"; //首日充值
            }


            $list[$k]['a_charge_amount'] = get_yuan_amount($regData[0]['a_charge_amount']);  //累计充值
            $list[$k]['a_charge_amount_lz'] = get_yuan_amount($regData[0]['a_charge_amount_lz']);  //乐众累计

//            $list[$k]['new_pay_rate'] = $regData[0]['register_num'] != 0 ? round($regData[0]['new_charge_person_count'] / $regData[0]['register_num'], 4) * 100 . "%" : "0%";//新付费率

        }

        return $list;
    }
}
