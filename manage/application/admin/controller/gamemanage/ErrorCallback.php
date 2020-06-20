<?php

namespace app\admin\controller\gamemanage;

use app\common\controller\Backend;
use think\Exception;

//use app\recharge\model\Orders;

/**
 * 回调CP错误日志
 *
 * @icon fa fa-circle-o
 */
class ErrorCallback extends Backend
{
    
    /**
     * ErrorCallback模型对象
     * @var \app\admin\model\gamemanage\ErrorCallback
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\gamemanage\ErrorCallback;
        $this->view->assign("stateList", $this->model->getStateList());
    }
    
    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */

    //加入对当前表数据的渲染方法
    public function indexMiddle(&$list) {

        //js的number类型有个最大值（安全值）。即2的53次方，为9007199254740992。如果超过这个值，那么js会出现不精确的问题。这个值为16位。
        //解决方法：后端下发字符串类型。
        foreach ($list as $k => $v) {
            $list[$k]['my_order_num'] = "" . $v['my_order_num'];
        }

    }

    public function callback_cp($ids) {

        if ($this->request->isAjax()) {

            $errorCallbackWhere = ['state' => 1, 'id' => $ids];
            $data = $this->model->where($errorCallbackWhere)->field('my_order_num, callback_url, callback_data')->find();

            //查找orders这个订单号的状态字段state是否为4
            $ordersModel = new \app\admin\model\gamemanage\Orders();
            $ordersWhere = ['state' => 4, 'my_order_num' => $data['my_order_num']];
            $ordersInfo = $ordersModel->where($ordersWhere)->field('my_order_num')->find();
            if(empty($ordersInfo)) {
                $this->success("该订单不能回调", null, ['id' => $ids, 'my_order_num' => $data['my_order_num'] ? "" . $data['my_order_num'] : "无"]);
            }
            $dataArr = json_decode($data, true);
            $result = \fast\Http::post($dataArr['callback_url'], $dataArr['callback_data']);
            $resultRet = $result == "SUCCESS" ? "回调成功" : "回调失败!";

            $time = time();

            $this->model->startTrans();
            $sql = '';
            try {
                //修改error_callback表的state状态为3【手动回调成功】
                $this->model->where($errorCallbackWhere)->update(['state' => 3, 'last_time' => $time]);
                //修改orders表的state状态为7【已手动回调CP】
                $sql = sprintf("update orders set last_notice = %d, notice_times = notice_times + 1, notice_result = '%s', state = %d where my_order_num=%d",
                    $time, $result, 7, $ordersInfo['my_order_num']);
                $ordersModel->execute($sql);
            } catch(Exception $e) {
                $this->model->rollback();
                $this->success($e->getMessage() . $sql, null, ['id' => $ids]);
            }

            $this->model->commit();

            $this->success($resultRet, null, ['id' => $ids, 'my_order_num' => $data['my_order_num'] ? "" . $data['my_order_num'] : "无"]);
        }
    }
}
