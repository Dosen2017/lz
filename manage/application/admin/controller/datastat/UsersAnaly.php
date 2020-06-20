<?php

namespace app\admin\controller\datastat;

use app\common\controller\Backend;

/**
 * 游戏玩家管理
 *
 * @icon fa fa-circle-o
 */
class UsersAnaly extends Backend
{
    
    /**
     * UsersAnaly模型对象
     * @var \app\admin\model\datastat\UsersAnaly
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\datastat\UsersAnaly;
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

            $sql = "select count(1) as register_num,
                        sum(charge1_amount) as new_amount, 
                        sum(charge1_amount_lz) as new_lz_amount,
                        sum(charge1_count) as new_charge_count,
                        sum(charge1_lz_count) as new_lz_charge_count,
                        count(charge1=1 or null) as new_charge_person_count,
                        count(charge1_lz=1 or null) as new_lz_charge_person_count,
                        sum(charge_amount) as all_amount, 
                        sum(charge_amount_lz) as all_lz_amount,
                        count(charge_amount!=0 or null) as charge_person_count
                         from game_users where create_d = " . intval($v) . $where;
            $regData = $this->model->query($sql);
//            var_dump($regData);exit;

            $list[$k]['register_num'] = $regData[0]['register_num'];  //注册数量
            $list[$k]['new_amount'] = get_yuan_amount($regData[0]['new_amount']); //新增充值
            $list[$k]['new_lz_amount'] = get_yuan_amount($regData[0]['new_lz_amount']);  //新增官方充值
            $list[$k]['new_pt_amount'] = get_yuan_amount($regData[0]['new_amount']- $regData[0]['new_lz_amount']);  //新增平台充值
            $list[$k]['new_charge_count'] = $regData[0]['new_charge_count'] ?: 0;  //新增笔数
            $list[$k]['new_lz_charge_count'] = $regData[0]['new_lz_charge_count'] ?: 0;  //新增官方笔数
            $list[$k]['new_charge_person_count'] = $regData[0]['new_charge_person_count'];  //新增人数
            $list[$k]['new_lz_charge_person_count'] = $regData[0]['new_lz_charge_person_count'];  //新增官方人数
            $list[$k]['all_amount'] = get_yuan_amount($regData[0]['all_amount']);  //累计充值
            $list[$k]['all_pt_amount'] = get_yuan_amount($regData[0]['all_amount']- $regData[0]['all_lz_amount']);  //平台累计

            $list[$k]['new_pay_rate'] = $regData[0]['register_num'] != 0 ? round($regData[0]['new_charge_person_count'] / $regData[0]['register_num'], 4) * 100 . "%" : "0%";//新付费率
            $list[$k]['all_pay_rate'] = $regData[0]['register_num'] != 0 ? round($regData[0]['charge_person_count'] / $regData[0]['register_num'], 4) * 100 . "%" : " 0%";  //平台累计
            $list[$k]['ltv'] = $regData[0]['register_num'] != 0 ? round($regData[0]['all_amount'] / $regData[0]['register_num'] / 100, 2) : 0;  //LTV

        }

        return $list;
    }

}
