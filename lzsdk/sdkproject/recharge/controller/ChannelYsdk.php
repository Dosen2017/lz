<?php
/**
 * Created by PhpStorm.
 * User: sds
 * Date: 2019/3/1
 * Time: 15:09
 */

namespace app\recharge\controller;

use think\Request;
use app\recharge\model\ErrorOrders;
use app\recharge\model;
use app\recharge\helper;

require_once __DIR__ . '/Ysdks/Api.php';
require_once __DIR__ . '/Ysdks/Ysdk.php';
require_once __DIR__ . '/Ysdks/Payments.php';

//腾讯SDK升级
class ChannelYsdk extends Recharge
{
    //沙箱 AppKey：uP7NBR3M9baSGR0e48Jwdxtg252jmQUs
    //现网 AppKey:d6UlRi1uZ3aFlh1qm7pOQgWGsZJcFbvV


    public $channelId = 1;

    const TYPE_QQ = 1;
    const TYPE_WECHAT = 2;

    const GET_BALANCE_ERROR = 3;  //获取游戏币余额失败
    const BALANCE_IS_ZERO_ERROR = 4;  // 获取的余额为0
    const PAY_BALANCE_ERROR = 5;   // 游戏币扣除失败

    const PAY_FAIL = 7;  //游戏币充足，扣除失败

//    protected $success =  100; //成功

    public $QQAppId = '1106240475';
    public $QQAppKey = '7l9zAvFL3Rx6TPtt';
//    public $QQAppKey = 'd6UlRi1uZ3aFlh1qm7pOQgWGsZJcFbvV';
//
    //----QQ和微信充值，都没用到微信的appid和appkey
    public $WeChatAppId = 'wxa3264242227b8abb';
    public $WeChatAppKey = '3d327f1e04dfe9da41d4b031aba354b6';

    public $payAppId = '1106240475';
    public $payAppKey = 'd6UlRi1uZ3aFlh1qm7pOQgWGsZJcFbvV'; //现网
    public $testPayAppKey = 'uP7NBR3M9baSGR0e48Jwdxtg252jmQUs'; //沙箱

    public $payUrl = 'ysdk.qq.com';  //现网
    public $testPayUrl = 'ysdktest.qq.com';  //沙箱

    private $sdk;

    public function __construct()
    {
        parent::__construct();
        $this->apiHelper = new helper\Api();
        $this->orderModel = new model\Orders();
    }

    protected function _changeYsdkParamByAppId($appId)
    {
        if (10008 == $appId) {
            $this->QQAppId = '1106457191';
            $this->QQAppKey = 'hKOGPQMdOnWeewe0';

            $this->payAppId = '1106457191';
            $this->payAppKey = 'O23K1tWB6VSpNDfwqdxdtFM97DbzWvvV'; //现网
        }
    }

    protected function _exit($exitId)
    {
        header('Content-type: text/json');  //以纯json数据返回

        if($exitId != $this->success){
            $errorOrdersModel = new ErrorOrders;
            $errorOrdersModel->insertOrder($this->info['app_id'], $exitId, $this->info['order_num'], $this->post, $this->order);
            $return = array('ret' => $exitId);
        }else {
            $return = array('ret' => 0);
        }
        echo json_encode($return); exit;
    }

    protected function _exitNotExit($exitId)
    {
        $errorOrdersModel = new ErrorOrders;
        $errorOrdersModel->insertOrder($this->info['app_id'], $exitId, $this->info['order_num'], $this->post, $this->order);
    }

    public function _getMoney()
    {
        return $this->post['total_fee'];
    }

    public function test() {
        $gameModel = new model\Game();
        $payKey = $gameModel->getAppInfoByAppId(10000)['pay_key'];

        var_dump($payKey);

    }


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

    //测试接口1 -- 充值
    public function indexTest()
    {
        $this->payAppKey = $this->testPayAppKey;
        $this->payUrl = $this->testPayUrl;
        $this->index();
    }

    //测试接口2 -- 清除游戏币
    public function clearUpAmtTest()
    {
        $this->payAppKey = $this->testPayAppKey;
        $this->payUrl = $this->testPayUrl;
        $this->clearUpAmt();
    }

    //在登陆时，请求清除腾讯账号的游戏币
    public function clearUpAmt()
    {
        $request = Request::instance();

//file_put_contents('/tmp/ChannelYsdk_clearUAmt.txt', date('Y-m-d H:i:s') . '_' . json_encode(Request::instance()->get()) . "\n", FILE_APPEND);

        $type = $request->get('type');
        $time = time();
        $commonData['openid'] = $request->get('openid');
        $commonData['openkey'] = $request->get('openkey');

        if ( self::TYPE_QQ == $type ) {
            $commonData['pay_token'] = $request->get('pay_token');  //微信的公用参数不需要这个
        }

        $commonData['ts'] = $time;
        $commonData['pf'] = $request->get('pf');
        $commonData['pfkey'] = $request->get('pfkey');
        $commonData['zoneid'] = $request->get('zoneid');

        $extData['app_id'] = $request->get('app_id');  //订单号可以不传，这个主要是
        $extData['pay_token'] = $request->get('pay_token');  //微信的公用参数不需要这个 ，但是sign加密用到了，所以加进去。

        $reqData = array_merge($commonData, $extData, array('sign' => $request->get('sign'), 'type' => $type));

        //1.检查是否有参数为空
        !$this->apiHelper->checkParamIsNull($reqData) && $this->_exitReturn(helper\ErrorOrders::ERROR_MISSED_PARAM, 'param is null');
        //2.验证sign是否正确
        $signParam = $reqData;
        unset($signParam['ts']);
        !$this->apiHelper->checkSign($signParam) && $this->_exitReturn(helper\ErrorOrders::ERROR_SIGN, 'sign error');

        //获得余额, 有多少扣除多少
        $balanceRet = $this->_getBalance($commonData, $type);

//file_put_contents('/tmp/ChannelYsdk_clearUAmt2.txt', date('Y-m-d H:i:s') . '_' . json_encode($balanceRet) . "\n", FILE_APPEND);
//        var_dump($balanceRet);exit;

        if ($balanceRet['ret'] != 0) {
            $this->_exitReturn(self::GET_BALANCE_ERROR, $balanceRet['msg'] . "(" . $balanceRet['ret'] . ")");
        }

        //当余额为0的时候，也返回正确。
        if ($balanceRet['balance'] <= 0) {
//            $this->_exitReturn(self::BALANCE_IS_ZERO_ERROR);
            $this->_exitReturn(0);
        }

        $retPay = $this->_payBalance($commonData,$type, $balanceRet['balance']);

//        var_dump($retPay);exit;

//file_put_contents('/tmp/ChannelYsdk_clearUAmt3.txt', date('Y-m-d H:i:s') . '_' . json_encode($retPay) . "\n", FILE_APPEND);
        if ($retPay['ret'] != 0) {
            $this->_exitReturn(self::PAY_BALANCE_ERROR, $balanceRet['msg'] . "(" . $balanceRet['ret'] . ")");
        }

        $this->_exitReturn(0);
    }


    public function _getParam()
    {
//file_put_contents('/tmp/ChannelYsdk_index.txt', date('Y-m-d H:i:s') . '_' . json_encode(Request::instance()->get()) . "\n", FILE_APPEND);

        $request = Request::instance();

        $type = $request->get('type');
        $time = time();
        $commonData['openid'] = $request->get('openid');
        $commonData['openkey'] = $request->get('openkey');

        if ( self::TYPE_QQ == $type ) {
            $commonData['pay_token'] = $request->get('pay_token');  //微信的公用参数不需要这个
        }

        $commonData['ts'] = $time;
        $commonData['pf'] = $request->get('pf');
        $commonData['pfkey'] = $request->get('pfkey');
        $commonData['zoneid'] = $request->get('zoneid');

        $extData['total_fee'] = $request->get('total_fee');  //充值金额  分
        $extData['billno'] = $request->get('billno');  //订单号可以不传，这个主要是
        $extData['app_id'] = $request->get('app_id');  //订单号可以不传，这个主要是

        $extData['pay_token'] = $request->get('pay_token');  //微信的公用参数不需要这个 ，但是sign加密用到了，所以加进去。

        $this->post = array_merge($commonData, $extData, array('sign' => $request->get('sign'), 'type' => $type));

        //为了代码统一，附加两个参数
        $this->info['order_num'] = $extData['billno'];
        $this->info['app_id'] = $extData['app_id'];  //在下单的时候传入

        //根据app_id替换不同游戏的参数
        $this->_changeYsdkParamByAppId($this->info['app_id']);


        //1.检查是否有参数为空
        !$this->apiHelper->checkParamIsNull($this->post) && $this->_exit(helper\ErrorOrders::ERROR_MISSED_PARAM);
        //2.验证sign是否正确
        $signParam = $this->post;
        unset($signParam['ts']);
        !$this->apiHelper->checkSign($signParam) && $this->_exit(helper\ErrorOrders::ERROR_SIGN);

        //获得余额, 判断是否大于所扣游戏币余额
        $balanceRet = $this->_getBalance($commonData, $type);

//file_put_contents('/tmp/ChannelYsdk_index2.txt', date('Y-m-d H:i:s') . '_' . json_encode($balanceRet) . "\n", FILE_APPEND);
//        var_dump($balanceRet);exit;

//        if ($balanceRet['ret'] != 0)
//        {
//            $this->_exitNotExit(self::GET_BALANCE_ERROR);
//        }
//        if ($balanceRet['balance'] <= 0) {
//            $this->_exitNotExit(self::BALANCE_IS_ZERO_ERROR);
//        }

        if ($balanceRet['balance'] > 0) {
            $retPay = $this->_payBalance($commonData, $type, $balanceRet['balance']);
        }

//        var_dump($retPay);exit;

//file_put_contents('/tmp/ChannelYsdk_index3.txt', date('Y-m-d H:i:s') . '_' . json_encode($retPay) . "\n", FILE_APPEND);
//        if ($retPay['ret'] != 0)
//        {
//            $this->_exitNotExit(self::PAY_BALANCE_ERROR);
//        }
    }

    protected function _checkPayMoney()
    {
        //下单时传入的CP订单信息
        $orderData = json_decode($this->order['order'], true);
        $checkTotalFee = $orderData['total_fee'];  //客户端下单时传过来的是分

        //2.验证金额
        if ( $this->_getMoney() != $checkTotalFee ) {
            //充值金额对不上
            $this->_exit(helper\ErrorOrders::ERROR_MONEY_NOT_SAME);
        }
    }

    //扣除余额
    private function _payBalance($commonData, $type = 1, $balance)
    {
        $this->initApi();
        $params = array(
            'openid' => $commonData['openid'],
            'openkey' => $commonData['openkey'],
            'pay_token' => $commonData['pay_token'],
            'ts' => $commonData['ts'],
            'pf' => $commonData['pf'],
            'pfkey' => $commonData['pfkey'],
            'zoneid' => $commonData['zoneid'] ?: 1,
            'amt' => $balance,
        );

        $accout_type = 'qq';
        if ($type != 1)
            $accout_type = 'wx';

        $ret = pay_m($this->sdk, $params, $accout_type);

        return $ret;
    }


    //得到玩家剩余游戏币
    private function _getBalance($commonData, $type = 1)
    {
        $this->initApi();
        $params = array(
            'openid' => $commonData['openid'],
            'openkey' => $commonData['openkey'],
            'pay_token' => $commonData['pay_token'],
            'ts' => $commonData['ts'],
            'pf' => $commonData['pf'],
            'pfkey' => $commonData['pfkey'],
            'zoneid' => $commonData['zoneid'],
        );
        $accout_type = 'qq';
        if ( self::TYPE_QQ != $type )
            $accout_type = 'wx';

//        var_dump($params);
//        echo $accout_type;

        return get_balance_m($this->sdk, $params,$accout_type);
    }

    private function initApi()
    {
        $this->sdk = new \Api($this->QQAppId, $this->QQAppKey);

        // 设置支付信息
        $this->sdk->setPay($this->payAppId, $this->payAppKey);
        // 设置YSDK调用环境
        $this->sdk->setServerName($this->payUrl);
    }

    //仅扣除余额使用的返回，不用处理错误订单或者订单是否失败
    private function _exitReturn($ret, $msg = '')
    {
        header('Content-type: text/json');  //以纯json数据返回
        ob_end_clean();
        //ret 为1表示成功，为0表示失败
        echo json_encode(array('ret' => $ret, 'msg' => $msg));exit;
    }

}