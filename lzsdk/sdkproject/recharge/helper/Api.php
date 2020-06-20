<?php
/**
 * Created by PhpStorm.
 * User: sds
 * Date: 2019/3/1
 * Time: 10:50
 */

namespace app\recharge\helper;

use app\recharge\model\ChannelPayments;
use think\Helper;

class Api extends Helper
{

    const ClIENT_RECHARGE_KEY = 'Cf~!~@~!2020~Vers@#@!Dion2017DWDWwXjJKHB.z#H)AcPLAT';//和登录一样的KEY

    const PARAM_ERROR = 1;  //参数为空
    const SIGN_ERROR = 2;

    const WEB_CAHT_ERROR = 3001;
//    const WEB_CHAT_PAY_SECRET_KEY = '';

    const SUCCESS = 0;

    //检查参数是否为空
    public function checkParamIsNull($reqData)
    {
        foreach($reqData as $k => $v)
        {

            if (is_null($v))
            {
//                echo $k;exit;  //需要注释
                return false;
            }
        }
        return true;
    }

    //sign验证 , 获取订单号专用
    public function checkSignOrder($reqData)
    {

        $str = '';
        $param = $reqData;
        unset($param['sign']);
        ksort($param);
        foreach($param as $k => $v)
        {
            if("order" == $k) {
                $v = json_encode(json_decode($v, true), JSON_UNESCAPED_UNICODE);
            }
            $str .= $k . '=' . $v . '&';
        }
//echo $str;exit;
        $checkSign = md5($str .  self::ClIENT_RECHARGE_KEY);

        if ($checkSign != $reqData['sign'])
        {
            return false;
        }

        return true;
    }

    //sign验证
    public function checkSign($reqData)
    {

        $str = '';
        $param = $reqData;
        unset($param['sign']);
        ksort($param);
        foreach($param as $k => $v)
        {
            if(is_null($v)) {
                continue;
            }
            $str .= $k . '=' . urlencode($v) . '&';
        }
//echo $str .  self::ClIENT_RECHARGE_KEY;exit;
        $checkSign = md5($str .  self::ClIENT_RECHARGE_KEY);

        if ($checkSign != $reqData['sign'])
        {
            return false;
        }

        return true;
    }


    public function createSign($param, $key = self::ClIENT_RECHARGE_KEY )
    {
        $str = '';
        ksort($param);
        foreach($param as $k => $v)
        {
            if (is_null($v))
                continue;
            $str .= $k . '=' . urlencode($v) . '&';
        }
//file_put_contents('/tmp/apiTTTT2.txt', date('Y-m-d H:i:s') . $str .  $key . "\n", FILE_APPEND);
        return md5($str .  $key);
    }

    public function echoJson($ret, $msg = null, $exit = true, $isJson = true)
    {
        $isJson && header('Content-type: text/json');

        $array['ret'] = (int)$ret;
        $msg && $array['msg'] = $msg;

        echo json_encode($array,JSON_UNESCAPED_UNICODE);
        $exit && exit;
    }

    //预防重复性订单生成订单数据的攻击
    public function defendRepeatRequestProduceOrder($data)
    {
        //-----------------预防重复数据重复性攻击请求----------------------
        if (time() - $data['time'] > 300) {
            $this->echoJson(ErrorOrders::REQURST_IS_TIMEOUT_ERROR);
        }

        try {
            //使用redis做预防被攻击的缓存工具
            Cache::connect(Config::get('redis_database'));

            //将获取订单号需要用到的部分参数组成一个唯一的键值
            $uniKey = $data['time'] . md5(APP_NAME . __CLASS__ . $data['channel_pkg_num'] . $data['cp_order_num'] . $data['time'] . $data['sign']);
//echo APP_NAME . __CLASS__ . $data['app_id'] . $data['platform'] . $data['time'] . $data['sign'] . $needParam['cp_trade_id'] . $needParam['role_id'];exit;

            if (Cache::get($uniKey)) {
                $this->echoJson(ErrorOrders::REPEAT_PRODUCE_ORDER_ERROR);
            } else {
                Cache::set($uniKey, 1, 305);
            }
        } catch (\RedisException $e) {

            //不做处理，继续往后面执行
//            echo $e->getMessage();exit;
        }



        //-------------------预防重复数据重复性攻击请求----------------------
    }

    public function isChangePay($orderInfo) {

        $isFlag = false;

        $paymentsModel = new ChannelPayments();
        $paymentInfo = $paymentsModel->getPaymentsByChannelPkgNum($orderInfo['channel_pkg_num']);

        foreach ($paymentInfo as $k => $v) {
            if ("ChargeAmountStrategy" == $v['strategy_class']) {
                $strategyArr = json_decode($v['strategy_json'], true);
                $payAmountMin = $strategyArr['payAmountMin'] ?: 0;
                $payAmountMax = $strategyArr['payAmountMax'] ?: 0;

                $money = round($orderInfo['amount'] / 100, 2);

                if ( $money >= $payAmountMin && $money <= $payAmountMax ) {
                    $isFlag = true;
                }

            }

            if ( "PackIdStrategy" == $v['strategy_class'] ) {
                $strategyArr = json_decode($v['strategy_json'], true);
                $packIdList = $strategyArr['packIdList'] ?: '';
                if (in_array($orderInfo['bundle'], explode("\r\n", $packIdList))) {
                    $isFlag = true;
                }

            }

            if ( "UserStrategy" == $v['strategy_class'] ) {
                $strategyArr = json_decode($v['strategy_json'], true);
                $userList = $strategyArr['userList'] ?: '';

                if (in_array($orderInfo['user_num'], explode("\r\n", $userList))) {
                    $isFlag = true;
                }
            }
        }

        return $isFlag;

    }

}
