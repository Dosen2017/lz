<?php
/**
 * Created by sudesheng.
 * User: 2066815257@qq.com
 * Date: 2017/06/13 0010
 * Time: 下午 17:43
 *
 * CP登陆数据的验证
 */
namespace app\login\controller;

use think\Controller;
use think\Request;

use app\login\helper;
use app\login\model;

class AuthToken extends Controller
{
    const SUCCESS = 0;
    const PARAM_IS_NULL_ERROR = 1;
    const SIGN_IS_ERROR = 2;
//    const TOKEN_IS_ERROR = 3;
//    const TOKEN_IS_TIME_OUT = 4;

    protected $gfChannels = [1];

    protected $helper;
    public function __construct()
    {
        parent::__construct();
        $this->helper = new helper\Login();
    }

    /**
     * http://localhost:81/authToken
     * channel_pkg_num  渠道包ID
     * time             时间戳
     * token
     * sign
     */
    public function index()
    {
        $getParam = Request::instance()->post();
        $reqData['channel_pkg_num'] = $getParam['channel_pkg_num'];

        $reqData['token'] = $getParam['token'];
        $reqData['time'] = $getParam['time'];
        $reqData['sign'] = $getParam['sign'];

        $helper = $this->helper;

        //1.检测参数是否有为空的
        $this->helper->checkParamIsNull($reqData);

        $gameId = getGameNumByChannelPkgNum($reqData['channel_pkg_num']);
        $gameModel = new model\Game();
        $gameInfo = $gameModel->getAppInfoByGameId($gameId);
        //2.判断app_id是否存在
        empty($gameInfo) && $this->_exitJson($helper::PARAM_ERROR, 'app_id error!');
        $appKey = $gameInfo['app_key'];
        //3.判断sign是否正确
        !$this->helper->checkSign($reqData, $appKey) && $this->_exitJson(self::SIGN_IS_ERROR, 'sign error!');

        //4.判断token是否能解析出账号id，空字符''说明解析失败，返回返回账号id  uid。
        $token = str_replace(' ','+', $reqData['token']);
        $uid = $this->helper->getLoginTokenDecode($token, $reqData['channel_pkg_num']);
        if ( '' === $uid ) {
            $this->_exitJson($helper::TOKEN_IS_ERROR, 'token error!');
        }

        if ( is_null($uid) ) {
            $this->_exitJson($helper::TOKEN_IS_TIME_OUT, 'token is time out!');
        }

        $channelId = getChannelNumByChannelPkgNum($reqData['channel_pkg_num']);
        //官方渠道（乐众账号系统），不返回账号名
        if (in_array($channelId, $this->gfChannels)) {
            $this->_exitJson( $helper::SUCCESS, array('uid' => $uid, 'channel_id' => $channelId, 'sdk_uid' => $uid) );
        }

        //渠道账号，返回渠道的用户ID
        $channelUsersModel = new model\ChannelUsers();
        $channelUserInfo = $channelUsersModel->getUserInfo($uid);
        $this->_exitJson( $helper::SUCCESS, array('uid' => $uid, 'channel_id' => $channelId, 'sdk_uid' => $channelUserInfo['channel_user_num']) );
    }

    protected function _exitJson($code, $msg = '', $exit = true, $isJson = true) {
        $isJson && header('Content-type: text/json');

        $array['code'] = (int)$code;
        $array['msg'] = $msg;

        echo json_encode($array,JSON_UNESCAPED_UNICODE);
        $exit && exit;
    }
}