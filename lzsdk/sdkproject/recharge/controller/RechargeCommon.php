<?php
/**
 * Created by PhpStorm.
 * User: sds
 * Date: 2019/3/1
 * Time: 11:18
 */

namespace app\recharge\controller;

use app\recharge\model\ChannelPkg;
use think\Controller;

use app\recharge\helper;
use app\recharge\model\Orders;
use app\recharge\model\ErrorOrders;
use app\recharge\model\Game;

abstract class RechargeCommon extends Controller
{
    protected $success =  'success'; //成功

    protected $apiHelper;
    protected $orderModel;

    protected $post; // 渠道方数据数组
    protected $order;

    protected $info; //统一APP_ID 和 我方订单号

    const CP_CALLBACK_REQUEST_TIMES = 2;

    public function __construct()
    {
        parent::__construct();
        $this->apiHelper = new helper\Api();
        $this->orderModel = new Orders();
    }

    //充值流程方法
    public function index()
    {
        // 1.获取参数 , 判断参数是否为空， 判断sign是否正确
        $this->_getParam();
        // 2.验证订单是否存在
        $this->_checkOrderNumIsExist();

        //2.1 设置回调后的数据
        $this->_setCallBackData();

        // 3.验证充值金额
        $this->_checkPayMoney();
        // 4.发起到CP的回调状态
        $this->_requestCallBackToCP();
    }

    //获取参数方法
    abstract protected function _getParam();
    //判断订单是否存在
    protected function _checkOrderNumIsExist() {
        $orderStatus = $this->orderModel->hasOrder($this->info['channel_pkg_num'], $this->info['my_order_num'], $this->order);
        // 未找到订单
        is_null($orderStatus) && $this->_exit(helper\ErrorOrders::ERROR_ORDER_NUM);

        // 只处理创建状态的订单
        if($orderStatus != helper\Orders::STATUS_CREATE) {
            //在订单已经不处于创建状态时，将重复请求的订单保存到补单的订单表【错误订单表】中，方便运营进行补单操作【这个补单是指自身客户端的请求，非回调CP】
            $errorOrdersModel = new ErrorOrders;
            $errorOrdersModel->insertOrder($this->info['channel_pkg_num'], helper\ErrorOrders::ERROR_REPEAT, $this->info['my_order_num'], $this->post, $this->order);

            $this->_exit($this->success);
        }
    }

    //发出回调到CP服务端
    abstract protected function _requestCallBackToCP();

    //获取回调之前生成的sign参数
    protected function getCallBackSign($callBackData) {

        $gameId = getGameNumByChannelPkgNum($this->info['channel_pkg_num']);
        $gameModel = new Game();
        $payKey = $gameModel->getAppInfoByGameId($gameId)['pay_key'];

        return $this->apiHelper->createSign($callBackData, $payKey);
    }

    //设置渠道的回调数据
    protected function _setCallBackData() {
        $this->orderModel->setCallBack($this->info, $this->post);
    }

    abstract protected function _exit($exitId);

    abstract protected function _getMoney();

    protected function _checkPayMoney()
    {
        //下单时传入的CP订单信息
        $orderData = $this->order;
        $checkTotalFee = $orderData['amount'];  //客户端下单时传过来的是分

        //2.验证金额
        if ( $this->_getMoney() != $checkTotalFee ) {
            //充值金额对不上
            $this->_exit(helper\ErrorOrders::ERROR_MONEY_NOT_SAME);
        }
    }

    //获取后台配置的CP回调地址
    protected function _getCPNotifyUrlByGameID($notifyUrlBySdk)
    {
        $gameId = getGameNumByChannelPkgNum($this->info['channel_pkg_num']);

        //channel_pkg设置了，优先channel_pkg设置的
        $channelPkgModel = new ChannelPkg();
        $notifyUrl = $channelPkgModel->getAppInfoByChannelPkgNum($this->info['channel_pkg_num'])['notify_url'];
        if ($notifyUrl)
            return $notifyUrl;

        //其次SDK接口传入
        if($notifyUrlBySdk)
            return $notifyUrlBySdk;

        //最后去game设置
        $gameModel = new Game();
        $notifyUrl = $gameModel->getAppInfoByGameId($gameId)['notify_url'];
//var_dump($notifyUrl);exit;
        return $notifyUrl;
    }

    /**
     * 以下两个函数都是方便切换参数用的
     */
    protected function _changeParamByAppAndNumAndOS()
    {
        $getParam = $this->info;   //channel_pkg_num 渠道包ID

        $defultFun = '_changeParam';
        //匹配不同的app_id对应的方法   (应对多套app_id参数的模板)
        $channelNum = $getParam['channel_pkg_num'];
        $changeParamFun = '_changeParam_' . $channelNum;
        if (method_exists($this, $changeParamFun)) {
            $this->$changeParamFun($getParam);
        } else {
            $this->$defultFun($getParam);
        }
    }

    protected function _changeParam ($getParam)
    {

    }

}
