<?php

namespace app\admin\controller\datastat;

use app\common\controller\Backend;

/**
 * 游戏玩家管理
 *
 * @icon fa fa-circle-o
 */
class RetentionAnaly extends Backend
{
    
    /**
     * RetentionAnaly模型对象
     * @var \app\admin\model\datastat\RetentionAnaly
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\datastat\RetentionAnaly;
        $this->view->assign("osTypeList", $this->model->getOsTypeList());
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

        foreach ($dateList as $k => $v) {
            $list[$k]['stat_date'] = date("Y-m-d", strtotime($v));

            $sql = "select count(1) as register_num, 
                                count(active2=1 or null) as s_active2, 
                                count(active3=1 or null) as s_active3, 
                                count(active4=1 or null) as s_active4,
                                count(active5=1 or null) as s_active5,
                                count(active6=1 or null) as s_active6,
                                count(active7=1 or null) as s_active7,
                                count(active8=1 or null) as s_active8,
                                count(active9=1 or null) as s_active9,
                                count(active10=1 or null) as s_active10,
                                count(active11=1 or null) as s_active11,
                                count(active12=1 or null) as s_active12,
                                count(active13=1 or null) as s_active13,
                                count(active14=1 or null) as s_active14,
                                count(active30=1 or null) as s_active30,
                                count(active60=1 or null) as s_active60,
                                count(active90=1 or null) as s_active90,
                                count(active120=1 or null) as s_active120,
                                count(active180=1 or null) as s_active180,
                                count(active360=1 or null) as s_active360
                                  from game_users where create_d = " . intval($v) . $where;
            $statKeepData = $this->model->query($sql);
            $list[$k]['register_num'] = $statKeepData[0]['register_num'];
            $list[$k]['s_active2'] = $this->statKeepShow($statKeepData[0]['s_active2'],  $list[$k]['register_num']);
            $list[$k]['s_active3'] = $this->statKeepShow($statKeepData[0]['s_active3'],  $list[$k]['register_num']);
            $list[$k]['s_active4'] = $this->statKeepShow($statKeepData[0]['s_active4'],  $list[$k]['register_num']);
            $list[$k]['s_active5'] = $this->statKeepShow($statKeepData[0]['s_active5'],  $list[$k]['register_num']);
            $list[$k]['s_active6'] = $this->statKeepShow($statKeepData[0]['s_active6'],  $list[$k]['register_num']);
            $list[$k]['s_active7'] = $this->statKeepShow($statKeepData[0]['s_active7'],  $list[$k]['register_num']);
            $list[$k]['s_active8'] = $this->statKeepShow($statKeepData[0]['s_active8'],  $list[$k]['register_num']);
            $list[$k]['s_active9'] = $this->statKeepShow($statKeepData[0]['s_active9'],  $list[$k]['register_num']);
            $list[$k]['s_active10'] = $this->statKeepShow($statKeepData[0]['s_active10'],  $list[$k]['register_num']);
            $list[$k]['s_active11'] = $this->statKeepShow($statKeepData[0]['s_active11'],  $list[$k]['register_num']);
            $list[$k]['s_active12'] = $this->statKeepShow($statKeepData[0]['s_active12'],  $list[$k]['register_num']);
            $list[$k]['s_active13'] = $this->statKeepShow($statKeepData[0]['s_active13'],  $list[$k]['register_num']);
            $list[$k]['s_active14'] = $this->statKeepShow($statKeepData[0]['s_active14'],  $list[$k]['register_num']);
            $list[$k]['s_active30'] = $this->statKeepShow($statKeepData[0]['s_active30'],  $list[$k]['register_num']);
            $list[$k]['s_active60'] = $this->statKeepShow($statKeepData[0]['s_active60'],  $list[$k]['register_num']);
            $list[$k]['s_active90'] = $this->statKeepShow($statKeepData[0]['s_active90'],  $list[$k]['register_num']);
            $list[$k]['s_active120'] = $this->statKeepShow($statKeepData[0]['s_active120'],  $list[$k]['register_num']);
            $list[$k]['s_active180'] = $this->statKeepShow($statKeepData[0]['s_active180'],  $list[$k]['register_num']);
            $list[$k]['s_active360'] = $this->statKeepShow($statKeepData[0]['s_active360'],  $list[$k]['register_num']);
        }

        return $list;
    }

    protected function statKeepShow($active, $registerNum) {
        return $active . ($active ? "(<font color='red'>" .  round($active / $registerNum, 4) * 100 . "%</font>)" : "(<font color='red'>-</font>)");
    }

}
