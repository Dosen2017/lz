<?php

namespace app\admin\controller\adstat;

use app\common\controller\Backend;

/**
 * 游戏玩家管理
 *
 * @icon fa fa-circle-o
 */
class PeriodAnaly extends Backend
{
    /**
     * PeriodAnaly模型对象
     * @var \app\admin\model\datastat\PeriodAnaly
     */
    protected $model = null;
    protected $gameUsersModel = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\datastat\PeriodAnaly;
        $this->gameUsersModel = new \app\admin\model\gamemanage\GameUsers();
        $this->view->assign("osTypeList", $this->model->getOsTypeList());
    }

    public function index()
    {
        $where = '';
        $dateList = [];

        $this->conditionHandle(true, 'create_time', $where, $dateList);
        asort($dateList);
        $dateList = array_values($dateList);
        if (count($dateList) > 3) {
            $dateList = array_slice($dateList, 0, 3);
        }
        $this->assignconfig('cols', count($dateList));
        $this->assignconfig('dateList', $dateList);

        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }

            $total = 1;
            $list = $this->getList($total);

            $result = array("total" => $total, "rows" => $list, 'datelist'=>$dateList);

            return json($result);
        }

        $this->renderMultiSelect();
        return $this->view->fetch();
//        return parent::index();
    }

    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */
    public function getList(&$total)
    {

        $list = [];

        $where = '';
        $dateList = [];

        $this->conditionHandle(true, 'create_time', $where, $dateList);

        asort($dateList);
        $dateList = array_values($dateList);
        if (count($dateList) > 3) {
            $dateList = array_slice($dateList, 0, 3);
        }

        $this->assignconfig('cols', count($dateList));
        $this->assignconfig('dateList', $dateList);

        $periodsList = ['00', '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23'];

        $list = [];
        foreach ($periodsList as $period) {
            $data['period'] = $period;

            $prevdate = 0;
            foreach ($dateList as $k=>$date) {
                $startTm = strtotime($date." {$period}0000");
                $endTm = strtotime($date." {$period}5959");

                // 注册
                $sql = "select count(game_user_num) AS register_num
                    from game_users 
                    where create_time >= {$startTm} and create_time <= {$endTm} {$where}";
                $selectData = $this->gameUsersModel->query($sql);
                $data['reg_'.$date] = isset($selectData[0]['register_num'])?$selectData[0]['register_num']:0;

                // 充值金额 笔数
                $sql = "select 
                    count(id) as charge_count, 
                    sum(amount) as charge_amount
                    from game_orders where notice_result='SUCCESS' and pay_time >= {$startTm} and pay_time <= {$endTm} {$where}";
                $selectData = $this->gameUsersModel->query($sql);
                $data['charge_count_'.$date] = isset($selectData[0]['charge_count'])?$selectData[0]['charge_count']:0;
                $data['charge_amount_'.$date] = isset($selectData[0]['charge_amount'])?get_yuan_amount($selectData[0]['charge_amount']):0;

                // todo 激活设备数


                if ($k > 0) {
                    $data['reg_change_'.$date] = $data['reg_'.$date] - $data['reg_'.$prevdate];
                    $data['charge_count_change_'.$date] = $data['charge_count_'.$date] - $data['charge_count_'.$prevdate];
                    $data['charge_amount_change_'.$date] = $data['charge_amount_'.$date] - $data['charge_amount_'.$prevdate];
                }

                $prevdate = $date;
            }
            // 总计

            $list[] = $data;
        }
        $totaldata = [];
        $temp = $list[0];
        unset($temp['period']);
        $totaldata['period'] = '总计';
        foreach ($temp as $k=>$v) {
            $totaldata[$k] = array_sum(array_column($list, $k));
        }
        $list[] = $totaldata;
        return $list;
    }

}
