<?php

namespace app\admin\controller\datastat;

use app\common\controller\Backend;

/**
 * 游戏玩家管理
 *
 * @icon fa fa-circle-o
 */
class AmountAnaly extends Backend
{
    
    /**
     * AmountAnaly模型对象
     * @var \app\admin\model\datastat\AmountAnaly
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\gamemanage\GameUsers();
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
            $data['stat_date'] = date("Y-m-d", strtotime($v));

            $sql = "SELECT 
                    count(game_user_num) AS register_num,
                    count(IF (charge_count > 0, 1, NULL)) AS charge_num,
                    count(IF (charge_count > 0 and charge_amount < 1000, 1, NULL)) as below10,
                    count(IF (charge_amount > 1000, 1, NULL)) as over10,
                    count(IF (charge_amount > 10000, 1, NULL)) as over100,
                    count(IF (charge_amount > 20000, 1, NULL)) as over200,
                    count(IF (charge_amount > 30000, 1, NULL)) as over300,
                    count(IF (charge_amount > 50000, 1, NULL)) as over500,
                    count(IF (charge_amount > 100000, 1, NULL)) as over1000,
                    count(IF (charge_amount > 200000, 1, NULL)) as over2000,
                    count(IF (charge_amount > 300000, 1, NULL)) as over3000,
                    count(IF (charge_amount > 500000, 1, NULL)) as over5000,
                    count(IF (charge_amount > 1000000, 1, NULL)) as over10000,
                    count(IF (charge_amount > 2000000, 1, NULL)) as over20000,
                    count(IF (charge_amount > 3000000, 1, NULL)) as over30000,
                    count(IF (charge_amount > 5000000, 1, NULL)) as over50000
                FROM
                    `game_users`
                WHERE
                    create_d = " . intval($v) . $where;

            $regData = $this->model->query($sql);
            $data = array_merge($data, $regData[0]);
            $list[$k] = $data;
        }
        $total = count($list);
        return $list;
    }

}
