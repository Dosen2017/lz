<?php

namespace app\admin\controller\datastat;

use app\common\controller\Backend;

/**
 * 游戏玩家管理
 *
 * @icon fa fa-circle-o
 */
class GatewayAnaly extends Backend
{
    
    /**
     * GatewayAnaly模型对象
     * @var \app\admin\model\datastat\GatewayAnaly
     */
    protected $model = null;
    protected $gameUsersModel = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\datastat\GatewayAnaly;
        $this->gameUsersModel = new \app\admin\model\gamemanage\GameUsers();
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

        $totalTj = [];
        $totalTj['order_d'] = '总计';
        $totalTj['game_name'] = '-';
        $totalTj['charge_count'] = 0;
        $totalTj['lz_alipay_charge_count'] = 0;
        $totalTj['lz_wechat_h5_charge_count'] = 0;
        $totalTj['lz_sdk_charge_count'] = 0;
        $totalTj['charge_amount'] = 0;
        $totalTj['lz_alipay_charge_amount'] = 0;
        $totalTj['lz_wechat_h5_charge_amount'] = 0;
        $totalTj['lz_sdk_charge_amount'] = 0;

        $gameList = $this->model->field('id, name')->select();
        $gameArr = array_column($gameList,NULL,'id');

        $wheres = json_decode($this->request->get()['filter'], true);
        $gamefilter = [];
        if (isset($wheres['game_num'])) {
            $gamefilter = $wheres['game_num'];
            $gamefilter = array_filter($gamefilter);
            if (empty($gamefilter)) {
                $gamefilter = array_column($gameList, 'id');
            }
        }else{
            $gamefilter = array_column($gameList, 'id');
        }

        foreach ($dateList as $k => $date) {

            $sql = "SELECT
    game_num,
    order_d,
	count(1) AS charge_count,
	sum(amount) AS charge_amount,
	count(IF (LOCATE('Alipay_', source),1,null)) AS lz_alipay_charge_count,
	count(IF (LOCATE('WeChatPay_', source) and LOCATE('_MWEB', source),1,null)) AS lz_wechat_h5_charge_count,
	count(IF (source='sdk',1,null)) AS lz_sdk_charge_count,
	sum(IF (LOCATE('Alipay_', source),amount,null)) AS lz_alipay_charge_amount,
	sum(IF (LOCATE('WeChatPay_', source) and LOCATE('_MWEB', source),amount,null)) AS lz_wechat_h5_charge_amount,
	sum(IF (source='sdk',amount,null)) AS lz_sdk_charge_amount
FROM
	game_orders where notice_result='SUCCESS' and order_d = {$date} {$where} group by game_num";
            $selectDataList = $this->gameUsersModel->query($sql);
            $l = [];
            if (empty($selectDataList)) {
                foreach ($gamefilter as $gameId) {
                    $selectData = [];
                    $selectData['order_d'] = $date;
                    $selectData['game_name'] = isset($gameArr[$gameId])?$gameArr[$gameId]['name']:'';
                    $selectData['charge_count'] = 0;
                    $selectData['lz_alipay_charge_count'] = 0;
                    $selectData['lz_wechat_h5_charge_count'] = 0;
                    $selectData['lz_sdk_charge_count'] = 0;
                    $selectData['charge_amount'] = 0;
                    $selectData['lz_alipay_charge_amount'] = 0;
                    $selectData['lz_wechat_h5_charge_amount'] = 0;
                    $selectData['lz_sdk_charge_amount'] = 0;
                    $l[] = $selectData;
                }
            }else{
                $selectDataList = array_column($selectDataList,null, 'game_num');

                foreach ($gamefilter as $gameId) {
                    if (isset($selectDataList[$gameId])) {
                        $selectData = $selectDataList[$gameId];
                        $selectData['game_name'] = isset($gameArr[$selectData['game_num']])?$gameArr[$selectData['game_num']]['name']:'';

                        $selectData['charge_amount'] = bcdiv($selectData['charge_amount'], 100, 2);
                        $selectData['lz_alipay_charge_amount'] = bcdiv($selectData['lz_alipay_charge_amount'], 100, 2);
                        $selectData['lz_wechat_h5_charge_amount'] = bcdiv($selectData['lz_wechat_h5_charge_amount'], 100, 2);
                        $selectData['lz_sdk_charge_amount'] = bcdiv($selectData['lz_sdk_charge_amount'], 100, 2);

                        $totalTj['charge_count'] += $selectData['charge_count'];
                        $totalTj['lz_alipay_charge_count'] += $selectData['lz_alipay_charge_count'];
                        $totalTj['lz_wechat_h5_charge_count'] += $selectData['lz_wechat_h5_charge_count'];
                        $totalTj['lz_sdk_charge_count'] += $selectData['lz_sdk_charge_count'];

                        $totalTj['charge_amount'] = bcadd($totalTj['charge_amount'], $selectData['charge_amount'], 2);
                        $totalTj['lz_alipay_charge_amount'] = bcadd($totalTj['lz_alipay_charge_amount'], $selectData['lz_alipay_charge_amount'], 2);
                        $totalTj['lz_wechat_h5_charge_amount'] = bcadd($totalTj['lz_wechat_h5_charge_amount'], $selectData['lz_wechat_h5_charge_amount'], 2);
                        $totalTj['lz_sdk_charge_amount'] = bcadd($totalTj['lz_sdk_charge_amount'], $selectData['lz_sdk_charge_amount'], 2);
                    }else{
                        $selectData = [];
                        $selectData['order_d'] = $date;
                        $selectData['game_name'] = isset($gameArr[$gameId])?$gameArr[$gameId]['name']:'';
                        $selectData['charge_count'] = 0;
                        $selectData['lz_alipay_charge_count'] = 0;
                        $selectData['lz_wechat_h5_charge_count'] = 0;
                        $selectData['lz_sdk_charge_count'] = 0;
                        $selectData['charge_amount'] = 0;
                        $selectData['lz_alipay_charge_amount'] = 0;
                        $selectData['lz_wechat_h5_charge_amount'] = 0;
                        $selectData['lz_sdk_charge_amount'] = 0;
                    }
                    $l[] = $selectData;
                }
            }

            $list = array_merge($list, $l);
        }
        array_multisort(array_column($list, 'game_name'), SORT_ASC, array_column($list, 'order_d'), SORT_ASC, $list);
        $list[] = $totalTj;
        $total = count($list);
        return $list;
    }

}
