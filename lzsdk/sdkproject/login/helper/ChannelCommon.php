<?php
/**
 * Created by sudesheng.
 * User: 2066815257@qq.com
 * Date: 2019/04/19 0010
 * Time: 下午 18:35
 */
namespace app\login\helper;

use think\Helper;

use app\login\helper as loginHelper;
use app\login\model;
use think\Request;

class ChannelCommon extends Helper
{

    const OS_IOS = 1;
    const OS_AZ = 2;

    //渠道登录时必须传入的参数
    protected $commonParam = ['channel_pkg_num', 'os', 'info', 'time', 'sign'];

    protected $loginHelper;
    protected $usersModel;
    protected $gameUserModel;
    protected $userLogModel;
    protected $channelModel;

    public function __construct()
    {
        $this->loginHelper = new loginHelper\Login();
        $this->usersModel = new model\Users();
        $this->gameUserModel = new model\GameUsers();
        $this->userLogModel = new model\UserLogs();
        $this->channelModel = new model\Channel();
    }

    protected function _checkSign() {
        $request = Request::instance();
        $getParam = $request->post();

        if (!$this->loginHelper->checkSign($getParam) ) {
            echoJson(2, 'sign error');
        }
    }

    protected function _changeUrl ($isTest, $testUrl)
    {
        //如果为测试环境，就切换请求的url
        if ($isTest) {
            $this->url = $testUrl;
        }

    }

    //这里是默认的获取渠道参数的函数
    public function getChannelParam()
    {
        return [];
    }

    public function getClientParam()
    {
        return array_merge($this->commonParam, $this->getChannelParam());
    }

    protected function _changeParamByAppAndNumAndOS()
    {
        $getParam = Request::instance()->post();

        $defultFun = '_changeParam';
        //匹配不同的channel_pkg_num对应的方法   (应对多套channel_pkg_num参数的模板)
        $channelPkgNum = $getParam['channel_pkg_num'];
        $changeParamFun = '_changeParam_' . $channelPkgNum;
        if (method_exists($this, $changeParamFun)) {
            $this->$changeParamFun($getParam);
        } else {
            $this->$defultFun($getParam);
        }
    }

    protected function _changeParam ($getParam)
    {

    }

    protected function handleUser($signData, $res)
    {
        if (empty($res['user_num']) || !isset($res['user_name'])) {
            echoJson(4, "user_num is null or user_name is not set");
        }

        $helper = new loginHelper\Login();
        //检查info字段，里面有些需要存储到game_users表的数据，比较关键
        $isOK = $helper->checkLoginOrRegisterInfoFieldIsComplete($signData['info'], true);
        if (!$isOK) {
            echoJson(5, "info is incomplete!");
        }

        //加入到账号系统中，和自主账号统一
        $userId = $this->handleAccountSystem($signData, $res);
        $res['sdk_uid'] = $res['user_num'];  //将渠道的SDK也返回
        $res['user_num'] = $userId;  //覆盖上面的user_num的值************重点******************

        $helper->handlerChannelData($res, $signData['channel_pkg_num']);
    }

    protected function handleAccountSystem($signData, $res)
    {
        $userId = null;
        $isNewAccount = false;

        //保存到
        $channelId = getChannelNumByChannelPkgNum($signData['channel_pkg_num']);
        $channelSuffix = $this->channelModel->getChannelInfoByChannelId($channelId)['channel_suffix'];
        $saveData['user_name'] = $channelId . "_" . $res['user_num'] . "." . $channelSuffix;

        //判断账号是否存在,不存在的情况下，保存
        $return = $this->usersModel->field('user_num, user_name')->where(['user_name' => $saveData['user_name']])->find();
        if ( !$return ) {

            $isNewAccount = true;

            //这里是渠道的账号，随机给个密码就行
            $this->loginHelper->getCommonRegisterData(randomCode(6), $saveData);
            $this->usersModel->addUser($saveData, $userId);  //保存的时候返回一个自增ID

            //渠道用户数据的保存------------
            $channelUsersData['channel_num'] = $channelId;
            $channelUsersData['channel_suffix'] = $channelSuffix;
            $channelUsersData['channel_user_num'] = $res['user_num'];
            $channelUsersData['channel_user_name'] = $res['user_name'];
            $channelUsersData['user_num'] = $userId;

            $channelUsersModel = new model\ChannelUsers();
            $channelUsersModel->saveChannelUser($channelUsersData);
            //渠道用户数据的保存------------

            //返回玩家的信息
            $return['user_num'] = $userId;
            $return['user_name'] = $saveData['user_name'];
            $return['phone_number'] = $saveData['phone_number'];  //此时手机号码就是登录名

            //添加game_users表的数据
            $this->gameUserModel->addGameUser($return, $signData, $saveData);

            //登录的时候，加个日志，保存数据到user_logs中
//            $this->userLogModel->addUserLogs($return, $signData);

        }

        //非新账号才需要修改
        if (!$isNewAccount) {
            //修改账号最后的登录IP和时间
            $userId = $return['user_num'];
            $lastTime = time();
            $lastIp = get_client_ip();
            //修改Users表【Users表是以user_num为唯一键】
            $this->usersModel->where(['user_num' => $return['user_num']])->update(['last_time' => $lastTime, 'last_ip' => $lastIp]);

            //修改GameUsers表 【GameUsers表是以user_num和channel_pkg_num为唯一键的】
            //如果这个账号ID在这个包下存在，则修改最后登录时间和登录IP
            $where = ['user_num' => $return['user_num'], 'channel_pkg_num' => $signData['channel_pkg_num']];
            $gameUserInfo = $this->gameUserModel->field('create_time')->where($where)->find();

            if ($gameUserInfo) {

                $helper = $this->loginHelper;

                //需要修改的活跃天数
                $n = (int)((time() - strtotime(date('Y-m-d', $gameUserInfo['create_time']))) / 86400) + 1; //计算出第几天登录
                if (in_array($n, $helper::ACTIVE_DAYS)) {
                    $updData[$helper::ACTIVE_LOGIN_FLAG . $n] = 1;
                }

                $updData['last_time'] = $lastTime;
                $updData['last_ip'] = $lastIp;
//file_put_contents('/tmp/LoginData.txt', date("Y-m-d H:i:s") . "__n=" . $n . "__ " . time() . "-" . $gameUserInfo['create_time'] . "===="  . json_encode($updData) . "\n", FILE_APPEND);
                $this->gameUserModel->where($where)->update($updData);
            } else {
                //如果这个账号在这个包下不存在，则添加数据到game_users表
                //添加game_users表的数据
                $this->gameUserModel->addGameUserNoSaveData($return, $signData);

            }

        }

        //无论第几次登录，都需要加入日志
        //登录的时候，加个日志，保存数据到user_logs中
        $this->userLogModel->addUserLogs($return, $signData);

        return $userId;
    }
}