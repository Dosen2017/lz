<?php

namespace app\admin\controller\gamemanage;

use app\common\controller\Backend;

/**
 * 
 *
 * @icon fa fa-circle-o
 */
class ErrorOrders extends Backend
{
    
    /**
     * ErrorOrders模型对象
     * @var \app\admin\model\gamemanage\ErrorOrders
     */
    protected $model = null;


    protected $errorArr = [1 => '参数缺失',
                           2 => '身份验证失败',
                           3 => '订单未找到',
                           4 => '重复请求',
                           5 => 'APP_ID对应的产品ID没有配置',
                           6 => '产品ID不在该游戏产品ID中',
                           7 => '下单时产品ID和请求验证receipt中的产品ID不一致',
                           8 => '产品ID对应的金额不在后台配置中',
                           9 => '下单时的充值金额和验证后返回的金额不一致',
                           10 => 'CP端回调失败',
                           11 => '微信预下单CURL请求失败',
                           12 => '微信预下单后，打开MWEB_URL失败',
                           13 => '微信预下单失败',
                           14 => '同一个订单请求重复生成订单',
                           15 => '获取订单号的请求已经超时',
                           16 => '获取订单号时，游戏ID和渠道包ID不匹配',
                           20001 => '支付宝回调的订单状态不对',
                           20002 => '卖家用户号错误',
                           20003 => '应用ID错误',
                           401 => '支付验证返回为空',
                           402 => 'receipt重复请求'
    ];


    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\gamemanage\ErrorOrders;

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
            $list[$k]['error_id'] = $this->errorArr[$v['error_id']] . "(" . $v['error_id'] . ")";
        }

    }

}
