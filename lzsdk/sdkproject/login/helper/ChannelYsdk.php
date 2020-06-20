<?php
/**
 * Created by PhpStorm.
 * User: sds
 * Date: 2019/2/27
 * Time: 14:33
 */

namespace app\login\helper;

use think\Helper;
use think\Request;

use app\login\helper as loginHelper;


class ChannelYsdk extends Helper
{
    const TYPE_QQ = 1;
    const TYPE_WECHAT = 2;

    //微信的相关参数
    public $WeChatAppId = 'wxa3264242227b8abb';
    public $WeChatAppKey = '3d327f1e04dfe9da41d4b031aba354b6';

    //默认为QQ的
    public $appid = '1106240475';
    public $appkey = '7l9zAvFL3Rx6TPtt';

    private $url = 'ysdk.qq.com';

    protected function _changeYsdkParamByAppId($appId)
    {
        if (10008 == $appId) {
            $this->WeChatAppId = 'wxa3264242227b8abb';
            $this->WeChatAppKey = '3d327f1e04dfe9da41d4b031aba354b6';

            //默认为QQ的
            $this->appid = '1106240475';
            $this->appkey = '7l9zAvFL3Rx6TPtt';
        }
    }

    public function getClientParam()
    {
        return array('app_id', 'type', 'openid', 'openkey', 'accessToken', 'nick_name'); //type区别是QQ登陆还是WeChat登陆 1.QQ 2.微信    为了兼容QQ和微信，openkey和accessToken传入一样的
    }

    public function checkToken($signData, $channelId)
    {
        //根据app_id替换不同游戏的参数
        $this->_changeYsdkParamByAppId($signData['app_id']);

        $this->_checkLoginType($signData['type']);  //登陆类型，切换是使用QQ参数，还是微信参数
        $this->_changeUrl($signData['test']);  // 切换使用正式链接，还是测试链接

        include __DIR__ . '/Ysdks/Api.php';
        include __DIR__ . '/Ysdks/Ysdk.php';
        include __DIR__ . '/Ysdks/Payments.php';

        ob_end_clean(); //由于引入的文件可能存在输出信息的缓冲区中，所以在引入文件之后，清空缓冲区，以免影响后面数据的正常输出
        // 创建YSDK实例
        $sdk = new \Api($this->appid, $this->appkey);
        // 设置支付信息，登陆时，不需要
        //$sdk->setPay($this->pay_appid, $this->pay_appkey);
        // 设置YSDK调用环境
        $sdk->setServerName($this->url);

        $ts = time();
        $userIp = Request::instance()->ip();
        if( self::TYPE_QQ == $signData['type'] ){
            $params = array(
                'appid' => $this->appid,
                'openid' => $signData['openid'],
                'userip' => $userIp,
                'sig' =>   md5($this->appkey.$ts),
                'openkey' => $signData['openkey'],
                'timestamp' => $ts,
            );
            $ret = qq_check_token($sdk, $params);
        }
        elseif(  self::TYPE_WECHAT == $signData['type'] ){
            $params = array(
                'appid' => $this->appid,
                'openid' => $signData['openid'],
                'userip' => $userIp,
                'sig' => md5($this->appkey.$ts),
                'access_token' => $signData['accessToken'],
                'timestamp' => $ts,
            );

            $ret = wx_check_token($sdk, $params);
        }

//        var_dump($ret);exit;

        if(!$ret)
            echoJson(1, 'return data error');
        if($ret['ret'] != 0 )
            echoJson(2, 'channel return error: ' . $ret['msg'] . '(' . $ret['ret'] . ')');

        $res['account_id'] = $signData['openid'];
        $res['account_name'] = $signData['nick_name'] ?: '';

        $helper = new loginHelper\Login();
        $helper->handlerData($res, $signData['app_id'], $channelId);
    }

    private function _changeUrl ($isTest)
    {
        //如果为测试环境，就切换请求的url
        if ($isTest) {
            $this->url = 'ysdktest.qq.com';
        }

    }

    private function _checkLoginType ($type)
    {
        //如果为微信，就切换相应的参数
        if (self::TYPE_WECHAT == $type)
        {
            $this->appid = $this->WeChatAppId;
            $this->appkey = $this->WeChatAppKey;
        }

    }
}