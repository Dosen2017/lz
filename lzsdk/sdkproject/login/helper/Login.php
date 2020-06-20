<?php
/**
 * Created by PhpStorm.
 * User: sds
 * Date: 2019/2/27
 * Time: 12:00
 */

namespace app\login\helper;

use app\login\model\ChannelPkg;
use think\Helper;
use app\login\model\Game;



class Login extends Helper
{
    const ClIENT_LOGIN_KEY = 'Cf~!~@~!2020~Vers@#@!Dion2017DWDWwXjJKHB.z#H)AcPLAT';  //已使用，客户端登陆时，所有参数加密验证用到的KEY,充值也是用次KEY

    const CP_LOGIN_CHECK_KEY = 'Cf~!~@~!2020~LZ2#@F@#@~PLAT~#@$@#!C@@#SDAsd)2017~SUC';  //CP请求返回省token和  CP请求验证的KEY

    const MSG_TYPE_REGISTER = 1;//注册
    const MSG_TYPE_LOGIN = 2; //登录
    const MSG_TYPE_FIND_PASSWORD = 3; //找回密码

    //消息发送模板
    const MSG_TYPE_REGISTER_TEMPLATEID = 'SMS_173176218';//注册
    const MSG_TYPE_LOGIN_TEMPLATEID = 'SMS_173176220'; //登录
    const MSG_TYPE_FIND_PASSWORD_TEMPLATEID = 'SMS_173176216'; //找回密码

    const PHONE_REGISTER_SUFFIX = "phone_";

    const ACTIVE_DAYS = [2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,60,90,120,180,360];

    const ACTIVE_LOGIN_FLAG = "active";

    const SUCCESS = 0;

    //错误码
    const PARAM_ERROR = 1;  //参数为空
    const SIGN_ERROR = 2;   //sign验证错误
    const ACCOUNT_NAME_ERROR = 3;   //账号名不存在
    const PASSWORD_ERROR = 4;   //登陆密码错误
    const REGSITER_ACCOUNT_NAME_IS_EXSIT = 5;   //新注册账号名已存在
    const REGSITER_ACCOUNT_ERROR = 6;   //注册账号失败
    const FORMAT_ACCOUNT_ERROR = 7; //账号只能是数字和字母
    const FORMAT_PASSWORD_ERROR = 8; //密码只能是数字和字母
    const FORMAT_TELNUM_ERROR = 9; //手机号码错误
    const PASSWORD_FIVE_ERROR = 10; //账号密码错误次数超过五次，10分钟内禁止登陆
    const UPDATE_PASSWORD_ERROR = 11; //修改密码失败
    const MSG_SEND_ERROR = 12; //发送短信失败
    const MSG_CODE_CHECK_ERROR = 13; //手机验证码验证失败
    const RESET_PASSWORD_PWD_CODE_ONE_EXSIT_ERROR = 14; //重置密码时，密码和验证码有且有一个存在
    const UP_IDENTIFY_ERROR = 15; //修改身份信息出错
    const PHONE_NUM_IS_BANDED_ERROR = 16; //手机号码已经被绑定
    const ERROR_GET_TRUE_UID = 17; //获取真正返回的uid失败
    const ERROR_CODE_TYPE = 18; //验证码类型错误
    const ERROR_PHONE_NUM_LOGIN = 19; //手机号码登录失败
    const ERROR_USERNAME_AND_PHONE_NUMBER_HAS_ONE_EXSIT = 20; //用户名和手机号只能存在一个

    const ERROR_FULL_NAME_ERROR = 21; //姓名不正确
    const ERROR_ID_CARD_ERROR = 22; //身份证号码不正确

    const TOKEN_IS_ERROR = 23;
    const TOKEN_IS_TIME_OUT = 24;

    const ERROR_GET_CODE_FRE = 25; //获取验证码太频繁

    const ERROR_PHONE_NUM_IS_REGISTER = 26; //手机号码已注册

    const ERROR_PASSWORD_IS_EQU_NPASSWORD = 27; //
    const ERROR_LOGIN_OR_REGISTER_INFO_FIELD_INCOMPLETE = 28; //登录和充值接口的info字段不全

    const ERROR_CHANNEL_PKG_NUM_IS_NOT_EXIST = 29; //渠道包未设置

    const ERROR_LOGIN_IS_CLOSEED = 30; //登录关闭
    const ERROR_REGISTER_IS_CLOSEED = 31; //注册关闭

    protected $infoField = ["device_id", "device_model", "mac", "bundle", "version"];  //登录充值接口info字段必传字段

    //生成账号id
    public function createAccountId()
    {
        usleep(1);
        $timeStr = (string)time();
        return strtoupper( random(1, 'ABCDEFGHIJKLMNPQRSTUVWXYZ') . $timeStr[0] . ($timeStr[1] - 1) . ($timeStr[2] + 1) . substr($timeStr, 3) );
    }

    //随机生成密码
    public function createPassword()
    {
        $randStr = str_shuffle('1234567890');
        return substr($randStr,0,6);
    }

    //检查参数是否为空
    public function checkParamIsNull($reqData)
    {
        foreach($reqData as $k => $v) {
            if (is_null($v)) {
                $this->echoJson(self::PARAM_ERROR, $k . ' is null!');
            }
        }
    }

    //检查登录和充值接口中info信息字段是否全
    public function checkLoginOrRegisterInfoFieldIsComplete($info, $flag = false)
    {
        //默认字段是全的
        $ret = true;

        //1.1、检查info数据的字段是否都存在
        $infoDataKeys = array_keys(json_decode($info, true));
//        var_dump($infoDataKeys);
        foreach ($this->infoField as $v) {
            if (!in_array($v, $infoDataKeys)) {

                $ret = false;

                $this->echoJson(self::ERROR_LOGIN_OR_REGISTER_INFO_FIELD_INCOMPLETE, 'info field is incomplete');
            }
        }

        if ($flag) {
            return $ret;
        }
    }

    //检查手机号码
//    public function checkTelNum($tel)
//    {
//        //验证，  只要是11位数字就行
//        if ( empty($tel) || !preg_match("/^1[34578]\d{9}$/", $tel) ) {
//            $this->echoJson(self::FORMAT_TELNUM_ERROR, 'Please enter correct tel num!');
//        }
//
//    }

    public function isTelNum($tel)
    {
        //验证，  只要是11位数字就行
        if ( empty($tel) || !preg_match("/^1[345789]\d{9}$/", $tel) ) {
            return false;
        }

        return true;

    }

    //检查验证码的类型
    public function checkCodeType($type)
    {
        //验证验证码类型，1:注册，2:登录 3.找回密码
        if ( !in_array($type, [self::MSG_TYPE_REGISTER,self::MSG_TYPE_LOGIN,self::MSG_TYPE_FIND_PASSWORD]) ) {
            $this->echoJson(self::ERROR_CODE_TYPE, 'Please enter correct code type!');
        }

    }

    //检查身份证号码
    public function checkFullName($name)
    {
        return preg_match('/^[\x7f-\xff]{1,20}$/', str_replace('·', '', $name));
    }

    //检查身份证号码
    public function checkIdCard($idCard)
    {
        return checkIdCard($idCard);
    }

    public function checkAccountAndPasswordIsOK($account, $password)
    {
        if( empty($account) &&  empty($password)) {
            $this->echoJson(self::PARAM_ERROR, 'Please enter correct account and password!');
        }

        empty($account) && $this->echoJson(self::PARAM_ERROR, 'Please enter account!');
        empty($password) && $this->echoJson(self::PARAM_ERROR, 'Please enter password!');

        if ( !preg_match("/^[a-z]{1}[0-9a-z]{5,19}$/i", $account) ) {
            //第一位必须是字母
            $this->echoJson(self::FORMAT_ACCOUNT_ERROR, 'Account error, Please enter 6-20 letters or numbers, and the first is a letter!');
        }

        if( !preg_match("/^[0-9a-z]{6,20}$/i", $password)) {
            $this->echoJson(self::FORMAT_PASSWORD_ERROR, 'Password error, Please enter 6-20 letters or numbers!');
        }
    }

    public function checkPasswordIsOK($password)
    {
        empty($password) && $this->echoJson(self::PARAM_ERROR, 'Please enter password!');
        if( !preg_match("/^[0-9a-z]{6,20}$/i", $password)) {
            $this->echoJson(self::FORMAT_PASSWORD_ERROR, 'Password error, Please enter 6-20 letters or numbers!');
        }
    }

    //检查玩家的密码是否匹配
    public function checkPlayerIsLoginSuccess($checkParam, $return)
    {
        $pd = $this->produceMd5Password($checkParam['password'], $return['salt']);
        if ( $return['password'] != $pd ) {
            return false;
        }

        return true;
    }

    public function produceMd5Password($password, &$salt)
    {
        $salt = $salt ?: random(8);

        return md5(md5($password) . $salt);
    }

    /**
     * @param $reqData
     * @param string $key  和客户端的加密，使用  self::ClIENT_LOGIN_KEY，和CP的加密方式是APP_KEY
     * @return bool
     */
    public function checkSign($reqData, $key = self::ClIENT_LOGIN_KEY)
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
        //$str = substr($str, 0,-1);
        $checkSign = md5($str .  $key);
//        file_put_contents('/tmp/cccccc.txt', $str .  $key);
        if ($checkSign != $reqData['sign']) {
            return false;
        }

        return true;
    }

    //检验是否可以登录
    public function checkIsLoginOk($checkParam)
    {
        $channelPkgModel = new ChannelPkg();
        $appInitInfo = $channelPkgModel->getAppInfoByChannelPkgNum($checkParam['channel_pkg_num']);
        if ( empty($appInitInfo) || $appInitInfo['login_lockswitch'] == 1) {
            return false;
        }
        return true;
    }

    //检验是否可以注册
    public function checkIsRegisterOk($checkParam)
    {
        $channelPkgModel = new ChannelPkg();
        $appInitInfo = $channelPkgModel->getAppInfoByChannelPkgNum($checkParam['channel_pkg_num']);
        if ( empty($appInitInfo) || $appInitInfo['register_lockswitch'] == 1) {
            return false;
        }
        return true;
    }

    public function handlerData($account, $channelPkgNum)
    {
        $token = $this->getLoginTokenEncode($account['user_num'], $channelPkgNum);
        if (!$token) {
            $this->echoJson(self::ERROR_GET_TRUE_UID, 'Get true Uid error!');
        }

        $channelPkgInfo = $this->_getIsForceSm($channelPkgNum);
        if (!$channelPkgInfo) {
            $this->echoJson(self::ERROR_CHANNEL_PKG_NUM_IS_NOT_EXIST, 'channel_pkg_num is not exsit!');
        }

        $stime = time();
        $this->echoJson(
            self::SUCCESS,
            array(
                'uid' => $account['user_num'],
                'nick_name' => $account['user_name'],
                'is_f_sm' => $channelPkgInfo['login_smswitch'] ? 1 : 0,  //是否强制实名
                'is_sm' => $account['id_card'] ? 1 : 0,             //是否已经实名
                'is_adult' => $this->getIsAdult($account['id_card'] ) ? 1 : 0,  //是否成年
                'is_p_sm' => $channelPkgInfo['pay_smswitch'] ? 1 : 0,  //支付是否实名
                'token' => $token,
                'time' => $stime
            )
        );
    }

    //渠道的数据
    public function handlerChannelData($account, $channelPkgNum)
    {
        $token = $this->getLoginTokenEncode($account['user_num'], $channelPkgNum);
        if (!$token) {
            $this->echoJson(self::ERROR_GET_TRUE_UID, 'Get true Uid error!');
        }

        $stime = time();
        $this->echoJson(
            self::SUCCESS,
            array(
                'uid' => $account['user_num'],
                'nick_name' => $account['user_name'],
                'sdk_uid' => $account['sdk_uid'],
                'is_sm' => $account['id_card'] ?: 0,             //是否已经实名
                'is_adult' => $account['is_adult'] ?: 0,  //是否成年
                'token' => $token,
                'time' => $stime
            )
        );
    }

    public function getIsAdult($idCard)
    {
        if (empty($idCard)) {
            return false;
        }
        $age = $this->_getAgeByID($idCard);
        if ( $age < 18 ) {
            return false;
        }

        return true;

    }

    protected function _getAgeByID($id){ //过了这年的生日才算多了1周岁

        if (empty($id)) return '';

        $date = strtotime(substr($id, 6, 8)); //获得出生年月日的时间戳

        $today = strtotime('today'); //获得今日的时间戳

        $diff = floor(($today - $date) / 86400 / 365); //得到两个日期相差的大体年数

        //strtotime加上这个年数后得到那日的时间戳后与今日的时间戳相比

        $age = strtotime(substr($id, 6,8) . ' +' . $diff . 'years') > $today ? ($diff + 1) : $diff;

        return $age;

    }

    protected function _getIsForceSm($channelPkgNum)
    {
        //获取是否强制实名
        $channelPkgModel = new ChannelPkg();
        $channelPkgInfo = $channelPkgModel->getAppInfoByChannelPkgNum($channelPkgNum);
        return $channelPkgInfo;
    }

//    public function handlerDataExt($account, $appId, $channel = 1)
//    {
//        $token = $this->getLoginTokenEncode($account['account_id'], $appId, $channel);
//        if (!$token) {
//            $this->echoJson(self::ERROR_GET_TRUE_UID, 'Get true Uid error!');
//        }
//
//        $stime = time();
//        $this->echoJson(
//            self::SUCCESS,
//            array(
//                'access_token' => $account['access_token'],
////                'uid' => $account['account_id'],
//                'nick_name' => $account['account_name'],
//                'phone_is_band' => $account['tel_num'] ? 1 : 0,  //电话号码是否绑定
//                'tel_num' => $account['tel_num'] ?: '',
//                'id_is_band' => $account['id_number'] ? 1 : 1,   //身份证号是否绑定 [由于客户端界面输入框问题，现在改成全部绑定]
//                'token' => $token,
//                'time' => $stime
//            )
//        );
//    }
//
//    //返回原始账号的方法
//    public function handlerDataNoHidden($account, $appId, $channel = 1)
//    {
//        $token = $this->getLoginTokenEncodeNoHidden($account['account_id'], $appId, $channel);
//        if (!$token) {
//            $this->echoJson(self::ERROR_GET_TRUE_UID, 'Get true Uid error!');
//        }
//
//        $stime = time();
//        $this->echoJson(
//            self::SUCCESS,
//            array(
////                'uid' => $account['account_id'],
//                'nick_name' => $account['account_name'],
//                'phone_is_band' => $account['tel_num'] ? 1 : 0,  //电话号码是否绑定
//                'tel_num' => $account['tel_num'] ?: '',
//                'id_is_band' => $account['id_number'] ? 1 : 1,   //身份证号是否绑定 [由于客户端界面输入框问题，现在改成全部绑定]
//                'token' => $token,
//                'time' => $stime
//            )
//        );
//    }

    /**
     * @param $accountId  不能带+或者汉字，原本生成的也不带，所以authcode函数还是可以用的
     * @param $appId
     * @return string
     */
    public function getLoginTokenEncode($accountId, $channelPkgNum) {

//        return authcode($accountId, 'ENCODE', $this->_getEncodeKey($appId), 600);  //有效时间十分钟
        return authcode($accountId, 'ENCODE', $this->_getEncodeKey($channelPkgNum), 600);  //有效时间十分钟
    }

    /**
     * @param $accountId  不能带+或者汉字，原本生成的也不带，所以authcode函数还是可以用的
     * @param $appId
     * @return string
     *
     * 此方法是不影响原始账号的
     *
     */
//    public function getLoginTokenEncodeNoHidden($accountId, $appId, $channel) {
//
//        $accountOrgUid = new AccountOrgUid();
//
//        $aId = $accountOrgUid->checkAccountUidIsExist($accountId, $channel);
//
//        if (!$aId) {
//            $accountOrgUid->addAccountUidData($accountId, $channel, $appId);
//        }
//
////        return authcode($accountId, 'ENCODE', $this->_getEncodeKey($appId), 600);  //有效时间十分钟
//        return authcode($accountId, 'ENCODE', $this->_getEncodeKey($appId), 600);  //有效时间十分钟
//    }

    /**
     * @param $token
     * @param $appId
     * @return string
     */
    public function getLoginTokenDecode($token,$channelPkgNum) {
        return authcode($token, 'DECODE', $this->_getEncodeKey($channelPkgNum));

    }

    private function _getEncodeKey($channelPkgNum) {

        $gameId = getGameNumByChannelPkgNum($channelPkgNum);

        $gameModel = new Game();
        $gameInfo = $gameModel->getAppInfoByGameId($gameId);
        $appKey = $gameInfo['app_key'];

//        return md5(md5($accountId . '#&#' . $appKey)  . '#&#' . self::CP_LOGIN_CHECK_KEY);

        return md5(md5($appKey)  . '#&#' . self::CP_LOGIN_CHECK_KEY);

    }

    //根据IP获取地区数据
    public function getProvinceAndCity ($regIp) {
        preg_match_all("/city:\"(.*)\".*province:\"(.*)\"/", iconv("gb2312","utf-8", file_get_contents("http://ip.ws.126.net/ipquery?ip=" . $regIp)), $match);

        $areaData['city'] = $match[1][0] ?: '';
        $areaData['province'] = $match[2][0] ?: '';

        return $areaData;
    }

    public function getCommonRegisterData($password, &$saveData) {
        $time = time();
        $salt = '';
        $saveData['password'] = $this->produceMd5Password($password, $salt);
        $saveData['salt'] = $salt;
        $saveData['create_time'] = $time;

        $saveData['reg_ip'] = get_client_ip();

        //获取省份信息
        $areaData = $this->getProvinceAndCity($saveData['reg_ip']);
        $saveData['city'] = $areaData['city'];
        $saveData['province'] = $areaData['province'];

        $saveData['last_time'] = time();
        $saveData['last_ip'] = $saveData['reg_ip'];

//        return $saveData;
    }

    public function echoJson($ret, $msg = null, $exit = true, $isJson = true)
    {
        $isJson && header('Content-type: text/json;charset=utf-8');

        $array['ret'] = (int)$ret;
        $msg && $array['msg'] = $msg;

        echo json_encode($array,JSON_UNESCAPED_UNICODE);
        $exit && exit;
    }

}
