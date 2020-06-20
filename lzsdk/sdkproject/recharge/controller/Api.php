<?php
/**
 * Created by PhpStorm.
 * User: sds
 * Date: 2020/4/8
 * Time: 10:49
 */

namespace app\recharge\controller;

use app\recharge\model\ChannelPkg;
use think\Controller;
use think\Request;
use think\Config;

use app\recharge\helper;
use app\recharge\model\Orders;
use app\recharge\helper\Orders as helperOrders;

class Api extends Controller
{

    const ERROR_SAVEORDER = 3;  //存储订单号失败
    const ERROR_CHANNEL_PKG_NUM_NOT_EXSIT = 4; //渠道包不存在

    protected $helper;
    protected $orderModel;

    public function __construct()
    {
        parent::__construct();
        $this->helper = new helper\Api();
        $this->orderModel = new Orders();
    }

    protected function _getOrderCommon()  //默认为安卓
    {
        // 获取请求
        $reqParam = Request::instance()->post();
        foreach ($reqParam as $k => $v) {
            $reqParam[$k] = str_replace("\\", '', $v);
        }

        //账号信息
        $data['channel_pkg_num'] = $reqParam['channel_pkg_num'];  //渠道包ID
        $data['user_num'] = $reqParam['user_num'];  //账号ID
        $data['user_name'] = $reqParam['user_name'];  //账号名
        $data['game_num'] = $reqParam['game_num'];  //游戏ID


        //订单信息
        $data['real_amount'] = $reqParam['real_amount'];       //真实金额（分）
        $data['amount'] = $reqParam['amount'];   //金额（分）
//        $data['source'] = $reqParam['source'];         //订单来源  否
//                                                       //如：支付宝：Alipay_2088821608414125（user_id）,
//                                                       // 微信：WeChatPay_1491397502_MWEB（MCH_ID）,
//                                                       // sdk,
//                                                       // WeChatPay_1380827502_WAP
//                                                       //applestore

        $data['remark'] = $reqParam['remark'];         //订单备注   否
        $data['currency'] = $reqParam['currency'];  //币种   国内默认RMB，海外版默认USD
        $data['bundle'] = $reqParam['bundle'];  //给第三方支付时返回游戏用到

        //产品参数 可以没有
        $data['product_num'] = $reqParam['product_num'];  //产品ID     否
        $data['product_name'] = $reqParam['product_name'];  //产品名称  否
        $data['product_desc'] = $reqParam['product_desc'];  //产品描述  否

        //CP参数
        $data['server_id'] = $reqParam['server_id'];  //服务器ID
        $data['server_name'] = $reqParam['server_name'];  //服务器名称   否
        $data['role_id'] = $reqParam['role_id'];  //角色ID
        $data['role_name'] = $reqParam['role_name'];  //角色名称
        $data['role_level'] = $reqParam['role_level'];  //角色等级
        $data['extra'] = $reqParam['extra'];  //透传参数                 否
        $data['notify_url'] = $reqParam['notify_url'];  //回调地址
        $data['cp_order_num'] = $reqParam['cp_order_num'];  //CP订单号

        //切支付参数

        //验签参数
        $data['time'] = $reqParam['time'];       //时间戳
        $data['sign'] = $reqParam['sign'];

        //预防重复性获取订单号请求的拦截方法
//        $this->helper->defendRepeatRequestProduceOrder($data);

        $checkParam = $data;
        unset($checkParam['source'], $checkParam['remark'], $checkParam['product_num'], $checkParam['product_name'],
            $checkParam['product_desc'], $checkParam['server_name'], $checkParam['extra'] );

//var_dump($checkParam);exit;
        !$this->helper->checkParamIsNull($checkParam) && $this->helper->echoJson(helper\ErrorOrders::ERROR_MISSED_PARAM);
        !$this->helper->checkSign($data) && $this->helper->echoJson(helper\ErrorOrders::ERROR_SIGN);

        //如果游戏ID和包ID对应不上。提示错误
        if ( getGameNumByChannelPkgNum($reqParam['channel_pkg_num']) != $data['game_num']) {
            $this->helper->echoJson(helper\ErrorOrders::ERROR_ORDER_CHANNEL_PKG_NUM_IS_NOT_MATCH_GAME_NUM);
        }
        //排除需要存储在数据表中的字段
        unset($data['sign']);

        // 生成订单号
        $data['state'] = helperOrders::STATUS_CREATE;
        $data['my_order_num'] = $this->orderModel->getUniqueOrderNum();

        $data['create_time'] = time();

        return $data;
    }

    public function getOrderNum()
    {
        $retData = $this->_getOrderCommon();
        $bundle = $retData['bundle'];
//        unset($retData['bundle']);  //不存在数据表,用作第三方支付时，【返回游戏】跳转

        //根据channel_pkg_num设置的不同的支付策略来返回对应不同的数据
        $channelPkgModel = new ChannelPkg();
        $channelPkgInfo = $channelPkgModel->getAppInfoByChannelPkgNum($retData['channel_pkg_num']);
        $paymentNum = $channelPkgInfo['payment_num'];
        //如果渠道包不存在，则获取不到订单号
        if (is_null($paymentNum)) {
            $this->helper->echoJson(self::ERROR_CHANNEL_PKG_NUM_NOT_EXSIT);
        }
        $retData['gateway_num'] = $paymentNum;
        $retData['os_type'] = $channelPkgInfo['os_type'];

        if($this->orderModel->save($retData)) {

            //判断是否切支付，现有逻辑是判断当前渠道包是否设置了策略（channel_payments表），如果表中有对应策略（切开启use_switch字段），则按策略来，表中无数据，则不切支付
            $changePayParam['channel_pkg_num'] = $retData['channel_pkg_num'];
            $changePayParam['user_num'] = $retData['user_num'];
            $changePayParam['amount'] = $retData['amount'];
            $changePayParam['bundle'] = $retData['bundle'];

            $isChangePay = $this->helper->isChangePay($changePayParam);

            //正常支付
//            if ($paymentNum === 0) {
            if (!$isChangePay) {
                $productmap = [];
                $productArr = json_decode($channelPkgInfo['productjson'], true);
                foreach ($productArr as $k => $v) {
                    $productmap[$v['ZHProductId']] = $v['ProductId'];
                }

                $this->helper->echoJson(0, ['order_num' => $retData['my_order_num'], 'is_p_sm' => $channelPkgInfo['pay_smswitch'], 'productmap' => $productmap]);
            }

            //第三方支付
            //原本需要返回的订单号，现在改为返回链接,需要渲染页面信息
            $pageData['my_order_num'] = $retData['my_order_num'];
            $pageData['user_name'] = $retData['user_name'];
            $pageData['channel_pkg_num'] = $retData['channel_pkg_num'];  //渠道包ID

            $pageData['amount'] = $retData['amount'];   //金额（分）
            $pageData['bundle'] = $bundle;   //第三方支付跳转标识
            $pageData['time'] = time();    //时间戳
            $pageData['sign'] = $this->helper->createSign($pageData);

//            $pageData['is_p_sm'] = $channelPkgInfo['pay_smswitch'];  //额外加的字段。纯粹返回给客户端判断，是否需要支付实名的，不参与sign签名

            $pageData['zfb_url'] = getLPUrl(Config::get('zfb_url'));
            $pageData['wx_url'] = getLPUrl(Config::get('wx_url'));

            $this->helper->echoJson(0, ['data' => getLPUrl(Config::get('pay_option_url')) . '?' . http_build_query($pageData), 'is_p_sm' => $channelPkgInfo['pay_smswitch']]);
        }
        else
            $this->helper->echoJson(self::ERROR_SAVEORDER);
    }

}