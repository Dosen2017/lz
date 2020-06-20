<?php
/**
 * Created by PhpStorm.
 * User: sds
 * Date: 2019/2/27
 * Time: 11:58
 */

namespace app\login\controller;

use app\login\model\ChannelPkg;
use app\login\model\GameUsers;
use app\login\model\UserLogs;
use think\Controller;
use think\Request;

use app\login\helper;
use app\login\model\Users;
use app\login\model\ErrorLogin;

use app\login\model\MesSend;

class Login extends Controller
{
    protected $helper;
    protected $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new Users();
        $this->helper = new helper\Login();
    }

    /**
     *
     * 初始化渠道设置
     * http://localhost/login/login/initApp
     * channel_pkg_num  渠道包ID
     * time             时间戳
     * sign             Sign
     *
     */
    public function initApp()
    {
        $helper = $this->helper;

        $getParam = Request::instance()->post(); // 获取所有的get变量（经过过滤的数组）
        $reqData['channel_pkg_num'] = $getParam['channel_pkg_num'];

        $reqData['time'] = $getParam['time'];
        $reqData['sign'] = $getParam['sign'];

        $checkParam = $reqData;
        //1.检测参数是否有为空的
        $this->helper->checkParamIsNull($checkParam);
        //2.检测sign是否正确
        !$this->helper->checkSign($reqData) && $this->helper->echoJson($helper::SIGN_ERROR, 'Sign error!');

        $channelPkgModel = new ChannelPkg();
        $appInitInfo = $channelPkgModel->getAppInfoByChannelPkgNum($reqData['channel_pkg_num']);
        if (empty($appInitInfo)) {
            $this->helper->echoJson($helper::ERROR_CHANNEL_PKG_NUM_IS_NOT_EXIST);
        }

        $this->helper->echoJson($helper::SUCCESS, ['is_f_sm' => $appInitInfo['login_smswitch'], 'is_p_sm' => $appInitInfo['pay_smswitch'], 'is_open_rr' => $appInitInfo['quick_regitserswitch']]);

    }

    public function login()
    {
        $isNewAccount = false;

        $helper = $this->helper;

        $getParam = Request::instance()->post(); // 获取所有的get变量（经过过滤的数组）
        $reqData['channel_pkg_num'] = $getParam['channel_pkg_num'];
        $reqData['user_name'] = $getParam['user_name'];
        $reqData['password'] = $getParam['password'];

        //{"device_id":"fb9eec7f-6df7-2019-af5e-7ad7dfefd593", "device_model":"EVR-AL00", "mac":"74:ac:5f:9e:9f:bb", "bundle":"com.admin.tom", "version":"1.0.1", "b_id":"hss_12aa"}
        //其中b_id为可选的
        $reqData['info'] = $getParam['info'];

        $reqData['time'] = $getParam['time'];
        $reqData['sign'] = $getParam['sign'];

        $reqData['phone_number'] = $getParam['phone_number'];
        $reqData['code'] = $getParam['code'];  //账号名是手机号码时，就传这个值
//file_put_contents("/tmp/login.txt", date("Y-m-d H:i:s") . http_build_query($reqData) . "\n", FILE_APPEND);
        $checkParam = $reqData;

        //验证码和密码只能有一个存在
        if (!(is_null($checkParam['phone_number']) ^ is_null($checkParam['user_name'])))
            $this->helper->echoJson($helper::RESET_PASSWORD_PWD_CODE_ONE_EXSIT_ERROR, 'phone_number or user_name only has one exits!');

        if (is_null($checkParam['phone_number'])) {
            unset($checkParam['phone_number']);
            unset($checkParam['code']);
        } else {
            unset($checkParam['user_name']);
            unset($checkParam['password']);
        }

        //1.检测参数是否有为空的
        $this->helper->checkParamIsNull($checkParam);
        $this->helper->checkLoginOrRegisterInfoFieldIsComplete($reqData['info']);

        //2.检测sign是否正确
        !$this->helper->checkSign($checkParam) && $this->helper->echoJson($helper::SIGN_ERROR, 'Sign error!');

        //检验是否可以登录
        !$this->helper->checkIsLoginOk($checkParam) && $this->helper->echoJson($helper::ERROR_LOGIN_IS_CLOSEED, 'Login locked!');

        //3.检测账号密码是否合法
        $return = [];
        //电话号码也可以以user_name的形式传入，这里需要做判断。由于普通账号都是以一个字母开头，所以判断容易
        if ($checkParam['user_name']) {

            if ($this->helper->isTelNum($checkParam['user_name'])) {

                $this->helper->checkPasswordIsOK($checkParam['password']);  //检查密码

                //手机号+密码登录
                $return = $this->model->checkPhoneNumberIsExist($checkParam['user_name']);
            } else {
                //非手机号+密码登录
                $this->helper->checkAccountAndPasswordIsOK($checkParam['user_name'], $checkParam['password']);  //检查账号名  和 密码
                $return = $this->model->checkAccountNameIsExist($checkParam['user_name']);
            }

            //4.检查账号是否已经存在
            !$return && $this->helper->echoJson($helper::ACCOUNT_NAME_ERROR, 'Incorrect user_name or Be locked!');

            if (!$this->helper->checkPlayerIsLoginSuccess($reqData, $return)) {
                $time = time();
                $errLogin = new ErrorLogin();

                $endTime = $time;
                $startTime = $endTime - 600;
                //判断10分钟内，是否已经五次输入错误密码
                $ret = $errLogin->getTimeSlotErrorLoginTimes($startTime, $endTime, $reqData);
                count($ret) >= 5 && $this->helper->echoJson($helper::PASSWORD_FIVE_ERROR, 'Account or password error more than five times, 10 minutes to prohibit landing!');

                //密码错误，则记录数据到错误登陆表
                $errData['user_name'] = $checkParam['user_name'];
                $errData['time'] = $time;
                $errLogin->saveErrorData($errData);

                $this->helper->echoJson($helper::PASSWORD_ERROR, 'Incorrect account or password!');
            }

        } else {

            //2.判断手机验证码是否正确
            !$this->checkCode($checkParam['phone_number'], $checkParam['code'], $helper::MSG_TYPE_LOGIN) && $this->helper->echoJson($helper::MSG_CODE_CHECK_ERROR, 'Phone code error!');

            $return = $this->model->checkPhoneNumberIsExist($checkParam['phone_number']);
            //如果手机号码不存在，则创建改账号
            if (!$return) {
                $saveData['user_name'] = $helper::PHONE_REGISTER_SUFFIX . $checkParam['phone_number'];
                $saveData['phone_number'] = $checkParam['phone_number'];

                //注册的公用参数
                $this->helper->getCommonRegisterData($reqData['password'], $saveData);

//                $this->model->save($saveData);  //保存的时候返回一个自增ID
                $this->model->addUser($saveData, $userId);  //保存的时候返回一个自增ID
//                $userId = $this->model->user_num;

                if (!is_null($userId)) {

                    $isNewAccount = true;

                    //返回玩家的信息
                    $return['user_num'] = $userId;
                    $return['user_name'] = $saveData['user_name'];
                    $return['phone_number'] = $saveData['phone_number'];  //此时手机号码就是登录名

                    //添加game_users表的数据
                    $gameUsersModel = new GameUsers();
                    $gameUsersModel->addGameUser($return, $reqData, $saveData);

                } else {
                    $this->helper->echoJson($helper::ERROR_PHONE_NUM_LOGIN, 'Phone code login error!');
                }

            }
        }

        //非新账号才需要修改
        if (!$isNewAccount) {
            //修改账号最后的登录IP和时间

            $lastTime = time();
            $lastIp = get_client_ip();
            //修改Users表【Users表是以user_num为唯一键】
            $this->model->where(['user_num' => $return['user_num']])->update(['last_time' => $lastTime, 'last_ip' => $lastIp]);

            //修改GameUsers表 【GameUsers表是以user_num和channel_pkg_num为唯一键的】
            $gameUsersModel = new GameUsers();
            //如果这个账号ID在这个包下存在，则修改最后登录时间和登录IP
            $where = ['user_num' => $return['user_num'], 'channel_pkg_num' => $reqData['channel_pkg_num']];
            $gameUserInfo = $gameUsersModel->field('create_time')->where($where)->find();



            if ($gameUserInfo) {

                //需要修改的活跃天数
                $n = (int)((time() - strtotime(date('Y-m-d', $gameUserInfo['create_time']))) / 86400) + 1; //计算出第几天登录
                if (in_array($n, $helper::ACTIVE_DAYS)) {
                    $updData[$helper::ACTIVE_LOGIN_FLAG . $n] = 1;
                }

                $updData['last_time'] = $lastTime;
                $updData['last_ip'] = $lastIp;
//file_put_contents('/tmp/LoginData.txt', date("Y-m-d H:i:s") . "__n=" . $n . "__ " . time() . "-" . $gameUserInfo['create_time'] . "===="  . json_encode($updData) . "\n", FILE_APPEND);
                $gameUsersModel->where($where)->update($updData);
            } else {
                //如果这个账号在这个包下不存在，则添加数据到game_users表
                //添加game_users表的数据
                $gameUsersModel = new GameUsers();
                $gameUsersModel->addGameUserNoSaveData($return, $reqData);

            }

        }

        //登录的时候，加个日志，保存数据到user_logs中
        $userLogsModel = new UserLogs();
        $userLogsModel->addUserLogs($return, $reqData);

        $this->helper->handlerData($return, $reqData['channel_pkg_num']);
    }

    public function register()
    {
        $helper = $this->helper;

        $getParam = Request::instance()->post(); // 获取所有的get变量（经过过滤的数组）
        $reqData['channel_pkg_num'] = $getParam['channel_pkg_num'];  //channel_pkg_num 渠道包ID,后台渠道管理那里用到
        $reqData['user_name'] = $getParam['user_name'];
        $reqData['password'] = $getParam['password'];

        $reqData['info'] = $getParam['info']; //{"device_id":"fb9eec7f-6df7-2019-af5e-7ad7dfefd593", "device_model":"EVR-AL00", "mac":"74:ac:5f:9e:9f:bb", "bundle":"com.admin.tom", "version":"1.0.1", "b_id":"hss_12aa"}

        $reqData['time'] = $getParam['time'];
        $reqData['sign'] = $getParam['sign'];

//file_put_contents("/tmp/register.txt", date("Y-m-d H:i:s") . http_build_query($reqData) . "\n", FILE_APPEND);

        //手机号注册【安全注册】必传参数
        $reqData['phone_number'] = $getParam['phone_number'];  //
        $reqData['code'] = $getParam['code'];  //账号名是手机号码时，就传这个值

//        file_put_contents('/tmp/register.txt', date('Y-m-d H:i:s') . "_" . json_encode($reqData) . "\n", FILE_APPEND);

        $checkParam = $reqData;

        //验证码和密码只能有一个存在
        if (!(is_null($checkParam['phone_number']) ^ is_null($checkParam['user_name'])))
            $this->helper->echoJson($helper::ERROR_USERNAME_AND_PHONE_NUMBER_HAS_ONE_EXSIT, 'phone_number or user_name only has one exits!');

        if (is_null($checkParam['phone_number'])) {
            unset($checkParam['phone_number']);
            unset($checkParam['code']);
        } else {
            unset($checkParam['user_name']);
        }

        //1、检测参数是否有为空的
        $this->helper->checkParamIsNull($checkParam);
        $this->helper->checkLoginOrRegisterInfoFieldIsComplete($reqData['info']);

        //2、检测sign是否正确
        !$this->helper->checkSign($checkParam) && $this->helper->echoJson($helper::SIGN_ERROR, 'Sign error!');

        //检验是否可以登录
        !$this->helper->checkIsRegisterOk($checkParam) && $this->helper->echoJson($helper::ERROR_REGISTER_IS_CLOSEED, 'Register locked!');

        //3.1、检测账号密码是否合法
        $checkParam['user_name'] && $this->helper->checkAccountAndPasswordIsOK($reqData['user_name'], $reqData['password']);  //检查账号名  和 密码
        //3.2、检验手机号码的验证码是否正确
        if ($checkParam['phone_number']) {
            !$this->checkCode($checkParam['phone_number'], $checkParam['code'], $helper::MSG_TYPE_REGISTER) && $this->helper->echoJson($helper::MSG_CODE_CHECK_ERROR, 'Phone code error!');
        }

        $this->model->startTrans();//开启事务

        //4、检查账号名 或者手机号 是否已经注册
        if ( $checkParam['user_name'] )  {
            $this->model->lock(true)->where("user_name = '" . $checkParam['user_name'] . "'")->find();
            $return = $this->model->checkAccountNameIsExist($reqData['user_name']);
            $return && $this->helper->echoJson($helper::REGSITER_ACCOUNT_NAME_IS_EXSIT, 'Account already exists!');

            $saveData['user_name'] = $reqData['user_name'];

        } else{
            $this->model->lock(true)->where("phone_number = '" . $reqData['phone_number'] . "'")->find();
            $this->model->checkPhoneNumIsExist($checkParam['phone_number']) && $this->helper->echoJson($helper::PHONE_NUM_IS_BANDED_ERROR, 'phone num is registered!');

            $saveData['user_name'] = $helper::PHONE_REGISTER_SUFFIX . $checkParam['phone_number'];
            $saveData['phone_number'] = $checkParam['phone_number'];
        }

        //注册的公用参数
        $this->helper->getCommonRegisterData($reqData['password'], $saveData);

//        $this->model->save($saveData);
        $this->model->addUser($saveData, $userId);
//        $userId = $this->model->user_num;  //$this->model->getLastInsID(),不用这个方式，当前方式获取的ID是在当前进程内，注释内的方式，在非原子操作的情况下，并发情况下，获取的数据可能会混乱

        $this->model->commit();//事务提交后，解锁, 这里的事务主要是为了对数据加锁，没有多次修改操作，所以不用回滚

        if (!is_null($userId)) {

            //返回玩家的信息
            $return['user_num'] = $userId;
            $return['user_name'] = $saveData['user_name'];

            //添加game_users表的数据
            $gameUsersModel = new GameUsers();
            $gameUsersModel->addGameUser($return, $reqData, $saveData);

            //因为注册成功就代表登录成功了，所以这里也加个日志，保存数据到user_logs中
            $userLogsModel = new UserLogs();
            $userLogsModel->addUserLogs($return, $reqData);

            $this->helper->handlerData($return, $reqData['channel_pkg_num']);
        }

        $this->helper->echoJson($helper::REGSITER_ACCOUNT_ERROR, 'Account registration error!');

    }

    /**
     * 实名认证，暂时是将身份证号码和真实姓名保存在账号表中
     *
     * http://localhost/login/login/realNameAuth
     * channel_pkg_num  渠道包ID
     * token            登录拿到的token
     * id_card          身份证号码
     * full_name        真实姓名
     * time             时间戳
     * sign             Sign
     *
     */
    public function realNameAuth()
    {
        $helper = $this->helper;

        $getParam = Request::instance()->post(); // 获取所有的get变量（经过过滤的数组）
        $reqData['channel_pkg_num'] = $getParam['channel_pkg_num'];
        $reqData['token'] = $getParam['token'];

        $reqData['id_card'] = $getParam['id_card'];  //身份证号码
        $reqData['full_name'] = $getParam['full_name'];  //真实姓名

        $reqData['time'] = $getParam['time'];
        $reqData['sign'] = $getParam['sign'];

        $checkParam = $reqData;
        //1.检测参数是否有为空的
        $this->helper->checkParamIsNull($checkParam);
        //2.检测sign是否正确
        !$this->helper->checkSign($reqData) && $this->helper->echoJson($helper::SIGN_ERROR, 'Sign error!');

        //3.检验姓名是否正确
        !$this->helper->checkFullName($reqData['full_name']) && $this->helper->echoJson($helper::ERROR_FULL_NAME_ERROR, 'name error!');
        //4.检测身份证
        !$this->helper->checkIdCard($reqData['id_card']) && $this->helper->echoJson($helper::ERROR_ID_CARD_ERROR, 'id card error!');

        $token = str_replace(' ','+', $reqData['token']);
        $userNum = $this->helper->getLoginTokenDecode($token, $reqData['channel_pkg_num']);  //得到账号唯一ID

        //token获取账号ID失败的判断
        if ( '' === $userNum ) {
            $this->helper->echoJson($helper::TOKEN_IS_ERROR, 'token error!');
        }
        if ( is_null($userNum) ) {
            $this->helper->echoJson($helper::TOKEN_IS_TIME_OUT, 'token is time out!');
        }

        //更新玩家的身份信息
        if (!$this->model->upIdentityByUserNum($userNum, $reqData['id_card'], $reqData['full_name'])) {
            $this->helper->echoJson($helper::UP_IDENTIFY_ERROR, 'update identify error!');  //修改玩家身份信息出错
        }

        $this->helper->echoJson($helper::SUCCESS, ['is_adult' => $this->helper->getIsAdult($reqData['id_card'] ) ? 1 : 0]);

    }

    /**
     *
     * 找回密码和修改密码
     * 修改密码：http://localhost/login/login/resetPwd
     * user_name=Aa144e235skks
     * password=sds123456
     * npassword=999999
     * time=1568939328
     * sign=2kndedesdwefewewgewg
     *
     * 找回密码：http://localhost/login/login/resetPwd
     * phone_number=Aa144e235skks
     * code=888888
     * npassword=sds123456
     * time=1568939328
     * sign=2kndedesdwefewewgewg
     *
     */
    public function resetPwd()
    {
        $helper = $this->helper;

        $getParam = Request::instance()->post(); // 获取所有的get变量（经过过滤的数组）
        $reqData['user_name'] = $getParam['user_name'];
        $reqData['password'] = $getParam['password'];  //旧密码
        $reqData['npassword'] = $getParam['npassword']; //新密码
        $reqData['time'] = $getParam['time'];
        $reqData['sign'] = $getParam['sign'];

        $reqData['phone_number'] = $getParam['phone_number'];
        $reqData['code'] = $getParam['code'];

//        echo json_encode($getParam);exit;

        $checkParam = $reqData;

        //验证码和密码只能有一个存在
        if (!(is_null($checkParam['phone_number']) ^ is_null($checkParam['user_name'])))
            $this->helper->echoJson($helper::ERROR_USERNAME_AND_PHONE_NUMBER_HAS_ONE_EXSIT, 'phone_number or user_name only has one exits!');

        if ($checkParam['user_name']) {
            unset($checkParam['phone_number']);
            unset($checkParam['code']);
        } else {
            unset($checkParam['user_name']);
            unset($checkParam['password']);
        }

        //2.检测参数是否有为空的
        $this->helper->checkParamIsNull($checkParam);
        //3.检测sign是否正确
        !$this->helper->checkSign($checkParam) && $this->helper->echoJson($helper::SIGN_ERROR, 'Sign error!');

        $method = "upPasswordByAccountname"; //默认按照账号密码的形式去修改
        $condition = $checkParam['user_name'];

        $this->helper->checkPasswordIsOK($checkParam['npassword']);  //检查新密码

        //判断新旧密码是否一样
        if ($checkParam['npassword'] == $checkParam['password']) {
            $this->helper->echoJson($helper::ERROR_PASSWORD_IS_EQU_NPASSWORD, 'The old and new passwords are the same!');
        }

        //4.判断是账号登录，还是手机号登录
        if ($checkParam['user_name']) {

            if ($this->helper->isTelNum($checkParam['user_name'])) {
                $this->helper->checkPasswordIsOK($checkParam['password']);  //检查密码
                //手机号+密码登录
                $return = $this->model->checkPhoneNumberIsExist($checkParam['user_name']);
                $method = "upPasswordByPhoneNum";
            } else {
                //检验账号和密码格式是否正确
                $this->helper->checkAccountAndPasswordIsOK($checkParam['user_name'], $checkParam['password']);  //检查账号名  和 密码
                $return = $this->model->checkAccountNameIsExist($reqData['user_name']);
            }

            //8.检查角色名是否已经存在
            !$return && $this->helper->echoJson($helper::ACCOUNT_NAME_ERROR, 'Incorrect account');
            //9.验证玩家账号密码是否正确
            if (!$this->helper->checkPlayerIsLoginSuccess($checkParam, $return)) {
                $this->helper->echoJson($helper::PASSWORD_ERROR, 'Incorrect password!');
            }

        } else {
            //检验手机号码及其验证码是否正确
            !$this->helper->isTelNum($checkParam['phone_number']) && $this->helper->echoJson($helper::FORMAT_TELNUM_ERROR, 'Please enter correct tel num!');
            !$this->checkCode($checkParam['phone_number'], $checkParam['code'], $helper::MSG_TYPE_FIND_PASSWORD) && $this->helper->echoJson($helper::MSG_CODE_CHECK_ERROR, 'Phone code error!');
            !$this->model->checkPhoneNumIsExist($checkParam['phone_number']) && $this->helper->echoJson($helper::PHONE_NUM_IS_BANDED_ERROR, 'phone num is not exist!');

            $method = "upPasswordByPhoneNum";
            $condition = $checkParam['phone_number'];
        }

        //修改玩家的密码
        $salt = null;
        $passWord = $this->helper->produceMd5Password($checkParam['npassword'], $salt);
        if (!$this->model->$method($condition, $passWord, $salt)) {
            $this->helper->echoJson($helper::UPDATE_PASSWORD_ERROR, 'update password error!');
        }

        $this->helper->echoJson($helper::SUCCESS, 'update password success!');
    }

    /**
     *
     * 发送验证码
     *
     */
    public function sendMsg()
    {
        $getParam = Request::instance()->post(); // 获取所有的get变量（经过过滤的数组）
        $helper = $this->helper;

        $reqData['channel_pkg_num'] = $getParam['channel_pkg_num'];
        $reqData['phone_number'] = $getParam['phone_number'];
        $reqData['type'] = $getParam['type'];  //1.注册，2.登录, 3.找回密码
        $reqData['time'] = $getParam['time'];
        $reqData['sign'] = $getParam['sign'];
//var_dump($reqData);exit;
        //1.检测参数是否有为空的
        $this->helper->checkParamIsNull($reqData);
        //2.检验手机号码格式
//        var_dump($reqData['tel']);exit;
        !$this->helper->isTelNum($reqData['phone_number']) && $this->helper->echoJson($helper::FORMAT_TELNUM_ERROR, 'Please enter correct tel num!');
        //3.检验发送验证码的类型
        $this->helper->checkCodeType((int)$reqData['type']);
        //4.检测sign是否正确
        !$this->helper->checkSign($reqData) && $this->helper->echoJson($helper::SIGN_ERROR, 'Sign error!');

        //5.如果是注册类型，判断如果该手机号已经注册，则提示已注册
        if ($helper::MSG_TYPE_REGISTER == $reqData['type']) {
            $users = new Users();
            $users->checkPhoneNumberIsExist($reqData['phone_number']) && $this->helper->echoJson($helper::ERROR_PHONE_NUM_IS_REGISTER, 'phone num is registered!');
        }

        //6.判断该手机号30秒有没有获取验证码
        $msgSendModel = new MesSend();
        $msgSendModel->check30SecondsIsGetCode($getParam['phone_number'], $reqData['type']) && $this->helper->echoJson($helper::ERROR_GET_CODE_FRE, 'Do not get it frequently!');

        $code = randomCode(6);

        $typeToTemplateId = [
            $helper::MSG_TYPE_REGISTER => $helper::MSG_TYPE_REGISTER_TEMPLATEID,
            $helper::MSG_TYPE_LOGIN => $helper::MSG_TYPE_LOGIN_TEMPLATEID,
            $helper::MSG_TYPE_FIND_PASSWORD => $helper::MSG_TYPE_FIND_PASSWORD_TEMPLATEID
        ];

        //模板数据, 将所有调用发送到短信接口的字段全部转换成字符串类型
        $tpParam = array('code' => (string)$code);
        $msgRet = sendMsg($tpParam, (string)$getParam['phone_number'], $reqData['type'], $typeToTemplateId[$reqData['type']]);
        if (true === $msgRet) {
            //记录发送的信息
            $msgData['channel_pkg_num'] = $getParam['channel_pkg_num'];
            $msgData['phone_number'] = $getParam['phone_number'];
            $msgData['code'] = $code;
            $msgData['type'] = $reqData['type'];
            $msgData['msg'] = json_encode($tpParam);
            $msgData['time'] = time();
//            $msgSendModel = new MesSend();
            $msgSendModel->saveData($msgData);

            $this->helper->echoJson($helper::SUCCESS);

        }

        if (false === $msgRet) {
            $this->helper->echoJson($helper::MSG_SEND_ERROR, '暂未开通手机验证功能');
        }

        $this->helper->echoJson($helper::MSG_SEND_ERROR, $msgRet);
    }

    protected function checkCode($phoneNum, $code, $type)
    {
//        $phoneNum = 15121043586;
//        $code = '004275';

        $msgSendModel = new MesSend();
        return $msgSendModel->getCodeInfo($phoneNum, $code, $type);
    }

    /**
     * 随机生成账号和密码，主要用于游客登陆形式
     *
     * http://localhost:81/login/mkAccAndPwd?time=1568939328&sign=2kndedesdwefewewgewg
     *
     * time  时间戳
     * sign  Sign验证
     *
     */
    public function mkAccAndPwd()
    {
        $helper = $this->helper;

        $getParam = Request::instance()->post(); // 获取所有的get变量（经过过滤的数组）
        $reqData['time'] = $getParam['time'];
        $reqData['sign'] = $getParam['sign'];

        $checkParam = $reqData;
        //1.检测参数是否有为空的
        $this->helper->checkParamIsNull($checkParam);
        //2.检测sign是否正确
        !$this->helper->checkSign($reqData) && $this->helper->echoJson($helper::SIGN_ERROR, 'Sign error!');


        $accountId = $this->helper->createAccountId();
        $password = $this->helper->createPassword();

        $this->helper->echoJson($helper::SUCCESS, array('user_name' => $accountId, 'password' => $password));

    }

}