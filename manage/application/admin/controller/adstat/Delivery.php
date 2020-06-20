<?php

namespace app\admin\controller\adstat;

use app\admin\model\delivery\UserPages;
use app\common\controller\Backend;

/**
 * 游戏玩家管理
 *
 * @icon fa fa-circle-o
 */
class Delivery extends \app\admin\controller\datastat\Delivery
{
    
    /**
     * Delivery模型对象
     * @var \app\admin\model\adstat\Delivery
     */
    protected $model = null;

    protected $noNeedRight = ['table1', 'table2'];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\adstat\Delivery;
        $this->view->assign("osTypeList", $this->model->getOsTypeList());
    }
    
    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */

    public function table1()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }

            $total = 1;
            $list = $this->getList1($total);

            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch('index');
    }

    public function table2()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            $total = 1;
            $list = $this->getList2($total);

            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch('index');
    }

    public function getList1(&$total) {

        $list = [];

        $where = '';
        $dateList = [];

        $this->conditionHandle(true, 'complete_time', $where, $dateList);
        foreach ($dateList as $k => $v) {
            $list[$k]['stat_date'] = date("Y-m-d", strtotime($v));

            //注册数量
            $sql = "select count(1) as register_num from game_users where create_d = " . intval($v) . $where;
            $regData = $this->model->query($sql);
            $list[$k]['register_num'] = $regData[0]['register_num'];  //注册数量
//var_dump($list);exit;
            //充值金额，充值人数
            //                                count(user_num) as pay_num ,
            $sqlAll = "select  sum(amount) as pay_money, 
                                count(distinct user_num) as pay_person_num 
                                from game_orders where order_d = " . intval($v) . $where;
            $payDataAll = $this->model->query($sqlAll);
            $list[$k]['pay_money'] = round($payDataAll[0]['pay_money'] / 100, 2);     //充值金额
            $list[$k]['pay_person_num'] = $payDataAll[0]['pay_person_num'];//充值人数

            //次留数
            $sql = "select count(active2=1 or null) as s_active2
                                  from game_users where create_d = " . intval($v) . $where;
            $statKeepData = $this->model->query($sql);
            $list[$k]['s_active2'] = $statKeepData[0]['s_active2'];
            $list[$k]['s_active2_per'] = $this->statKeepShow($statKeepData[0]['s_active2'],  $list[$k]['register_num']);

            //新充和累充
            $sql = "select
                        sum(charge1_amount) as new_amount, 
                        sum(charge2_amount) as new2_amount,
                        sum(charge3_amount) as new3_amount,
                        sum(charge4_amount) as new4_amount,
                        sum(charge5_amount) as new5_amount,
                        sum(charge6_amount) as new6_amount,
                        sum(charge7_amount) as new7_amount,
                        sum(charge8_amount) as new8_amount,
                        count(charge1=1 or null) as new_charge_person_count,
                        sum(charge_amount) as all_amount, 
                        count(charge_amount!=0 or null) as charge_person_count
                         from game_users where create_d = " . intval($v) . $where;
            $regData = $this->model->query($sql);
            $list[$k]['new_amount'] = get_yuan_amount($regData[0]['new_amount']); //新增充值
            $list[$k]['new_charge_arrpu'] = $regData[0]['new_charge_person_count'] != 0 ? round($list[$k]['new_amount'] / $regData[0]['new_charge_person_count'], 2) : "0.00";  //新充ARRPU
            $list[$k]['new_charge_person_count'] = $regData[0]['new_charge_person_count'];  //新增充值人数
            $list[$k]['new_pay_rate'] = $list[$k]['register_num'] != 0 ? round($regData[0]['new_charge_person_count'] / $list[$k]['register_num'], 4) * 100 . "%" : "0%";//新付费率

            $list[$k]['all_amount'] = get_yuan_amount($regData[0]['all_amount']);  //累计充值
            $list[$k]['charge_person_count'] = $regData[0]['charge_person_count'];  //累计人数
            $list[$k]['charge_arrpu'] = $regData[0]['charge_person_count'] != 0 ? round($list[$k]['all_amount'] / $regData[0]['charge_person_count'], 2) : "0.00";  //累充ARRPU

            $sqlDau = "select count(distinct user_num) as dau from user_logs where opt_d = " . intval($v) . $where;
            $dauData = $this->model->query($sqlDau);

            $list[$k]['old_player_num'] = $dauData[0]['dau'] - $list[$k]['register_num'];

            $allAmount8 = $regData[0]['new_amount'];
            for ($i = 2; $i <= 8; $i++) {
                $allAmount8 += $regData[0]['new' . $i . '_amount'];
            }
            $list[$k]['all_amount8'] = get_yuan_amount($allAmount8);  //8天累计充值

            $list[$k]['ltv'] = $list[$k]['register_num'] != 0 ? (round($list[$k]['all_amount'] / $list[$k]['register_num'], 2) ?: "0.00") : "0.00";  //累计充值 / 当日注册数

        }

        return $list;
    }

    public function getList2(&$total) {

        $list = [];

        $where = '';
        $adsIdList = [];

        $this->conditionHandle2(true, 'complete_time', $where, $adsIdList);

        foreach ($adsIdList as $k => $v) {
            $list[$k]['ads_id'] = $v;

            //注册数量
            $sql = "select count(1) as register_num from game_users where ads_id = '" . $v  . "'" . str_replace("complete_time", "create_time", $where);
//            echo $sql . "\n";
            $regData = $this->model->query($sql);
            $list[$k]['register_num'] = $regData[0]['register_num'];  //注册数量
//var_dump($list);exit;
            //充值金额，充值人数
            //                                count(user_num) as pay_num ,
            $sqlAll = "select  sum(amount) as pay_money, 
                                count(distinct user_num) as pay_person_num 
                                from game_orders where ads_id = '" . $v . "'" . $where;
//            echo $sqlAll . "\n";
            $payDataAll = $this->model->query($sqlAll);
            $list[$k]['pay_money'] = round($payDataAll[0]['pay_money'] / 100, 2);     //充值金额
            $list[$k]['pay_person_num'] = $payDataAll[0]['pay_person_num'];//充值人数

            //次留数
            $sql = "select count(active2=1 or null) as s_active2
                                  from game_users where ads_id = '" . $v . "'" . str_replace("complete_time", "create_time", $where);
//            echo $sql . "\n";
            $statKeepData = $this->model->query($sql);
            $list[$k]['s_active2'] = $statKeepData[0]['s_active2'];
            $list[$k]['s_active2_per'] = $this->statKeepShow($statKeepData[0]['s_active2'],  $list[$k]['register_num']);

            //新充和累充
            $sql = "select
                        sum(charge1_amount) as new_amount, 
                        sum(charge2_amount) as new2_amount,
                        sum(charge3_amount) as new3_amount,
                        sum(charge4_amount) as new4_amount,
                        sum(charge5_amount) as new5_amount,
                        sum(charge6_amount) as new6_amount,
                        sum(charge7_amount) as new7_amount,
                        sum(charge8_amount) as new8_amount,
                        count(charge1=1 or null) as new_charge_person_count,
                        sum(charge_amount) as all_amount, 
                        count(charge_amount!=0 or null) as charge_person_count
                         from game_users where ads_id = '" . $v . "'" . str_replace("complete_time", "create_time", $where);
//            echo $sql . "\n";
            $regData = $this->model->query($sql);
            $list[$k]['new_amount'] = get_yuan_amount($regData[0]['new_amount']); //新增充值
            $list[$k]['new_charge_arrpu'] = $regData[0]['new_charge_person_count'] != 0 ? round($list[$k]['new_amount'] / $regData[0]['new_charge_person_count'], 2) : "0.00";  //新充ARRPU
            $list[$k]['new_charge_person_count'] = $regData[0]['new_charge_person_count'];  //新增充值人数
            $list[$k]['new_pay_rate'] = $list[$k]['register_num'] != 0 ? round($regData[0]['new_charge_person_count'] / $list[$k]['register_num'], 4) * 100 . "%" : "0%";//新付费率

            $list[$k]['all_amount'] = get_yuan_amount($regData[0]['all_amount']);  //累计充值
            $list[$k]['charge_person_count'] = $regData[0]['charge_person_count'];  //累计人数
            $list[$k]['charge_arrpu'] = $regData[0]['charge_person_count'] != 0 ? round($list[$k]['all_amount'] / $regData[0]['charge_person_count'], 2) : "0.00";  //累充ARRPU

            $sqlDau = "select count(distinct user_num) as dau from user_logs where ads_id = '" . $v . "'" . str_replace("complete_time", "opt_time", $where);
            $dauData = $this->model->query($sqlDau);

            $list[$k]['old_player_num'] = $dauData[0]['dau'] - $list[$k]['register_num'];

            $allAmount8 = $regData[0]['new_amount'];
            for ($i = 2; $i <= 8; $i++) {
                $allAmount8 += $regData[0]['new' . $i . '_amount'];
            }
            $list[$k]['all_amount8'] = get_yuan_amount($allAmount8);  //8天累计充值

            $list[$k]['ltv'] = $list[$k]['register_num'] != 0 ? (round($list[$k]['all_amount'] / $list[$k]['register_num'], 2) ?: "0.00") : "0.00";  //累计充值 / 当日注册数

        }

        return $list;
    }

    public function conditionHandle2($isOpt = false, $timeField = 'time', &$where = '', &$bIdList = []) {
        if ($isOpt) {
            $endTime = strtotime(date('Y-m-d')) + 86399;
            $startTime = $endTime - 7 * 86400 + 1;
            $where = "";
            $filterData = json_decode($this->request->request('filter'), true);
            if (!empty($filterData)) {

                //统一整理
                $commonConditions = ['game_num', 'os_type', 'complete_time', 'channel_num', 'channel_pkg_num'];
                foreach ($commonConditions as $k => $v) {
                    if (isset($filterData[$v])) {
                        $param[$v] = $filterData[$v];
                        if (is_array($filterData[$v])) {
                            $inStr = "";
                            foreach ($filterData[$v] as $kk => $vv) {
                                if (empty($vv))
                                    continue;

                                if(is_string($vv)) {
                                    $inStr .= "'" . $vv . "', ";
                                } else {
                                    $inStr .= $vv . ", ";
                                }
                            }

                            $inStr = substr($inStr, 0, -2);

                            $where .= " and $v in (" . $inStr . ")";
                        } else {
                            $filterData[$v] = trim($filterData[$v]);
                            if (is_string($filterData[$v])) {

                                if ('complete_time' == $v) {
                                    $timeArr = explode(' - ', $filterData[$timeField]);
                                    $startTime = strtotime(trim($timeArr[0]));
                                    $endTime = strtotime(trim($timeArr[1]));
                                    $where .= " and complete_time between $startTime and $endTime";
                                    continue;
                                }

                                $where .= " and $v = '" . $filterData[$v] . "'";
                            } else {
                                $where .= " and $v = " . $filterData[$v];
                            }

                        }

                    }
                }
            }
            $bIdList = $this->getAdsList($filterData);
        }

    }

    public function getAdsList($filterData) {
        $userPagesM = new UserPages();
        $upData = $userPagesM->getKVList();
        if (!isset($filterData['ads_id'])) {
            return array_values($upData);
        } else {
            return array_intersect($filterData['ads_id'], array_values($upData));  //得到的分包的数字ID
        }

    }


    protected function statKeepShow($active, $registerNum) {
        return $active . ($active ? "(<font color='red'>" .  round($active / $registerNum, 4) * 100 . "%</font>)" : "(<font color='red'>-</font>)");
    }

}
