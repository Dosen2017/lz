<?php
/**
 * Created by phpStorm.
 * User: php
 * Date: 2019/03/04
 * Time: 下午 17:35
 */
namespace app\login\helper;

use think\Helper;

use app\login\helper as loginHelper;

//小七升级
class ChannelXiaoqi extends ChannelCommon
{

    protected $appKey = '';   //默认是安卓的
    protected $url = 'https://api.x7sy.com/user/check_v4_login';//小七验证url

    //这里只用填入渠道登录验证的参数即可
    public function getChannelParam()
    {
        return ['tokenkey'];
    }

    public function checkToken($signData)
    {
        $this->_checkSign();
        $this->_changeParamByAppAndNumAndOS();//改成根据app_id来获取参数

        /*渠道登录处理主体*/
        $baseData = array(
            'tokenkey' => $signData['tokenkey'],
        );

        $data = array_merge($baseData, array('sign' => $this->_getSign($baseData)));

        $ret= httpRequest($this->url . '?' . http_build_query($data) , null, true);
        $ret = json_decode($ret, true);
        if(!$ret)
            echoJson(1, 'return data error');
        if($ret['errorno'] != 0 )
            echoJson(3, 'channel return error: ' . $ret['errormsg'] . '(' . $ret['errorno'] . ')');

        $res['user_num'] = $ret['data']['guid'];
        $res['user_name'] = $ret['data']['username'] ?: '';

//        $res['user_num'] = "fefsa" . randomCode(6);
//        $res['user_name'] = "fefsa" . randomCode(6);

        $res['id_card'] = $ret['data']['is_real_user'];   //1代表实名，-1代表未实名
        $res['is_adult'] = $ret['data']['is_eighteen'];   //1代表成年，-1代表未成年
        /*渠道登录处理主体*/

        //$signData主要用到channel_pkg_num, info字段， $res主要用到user_num, user_name
        $this->handleUser($signData, $res);
    }

    protected function _getSign($param)
    {
        return md5($this->appKey . $param['tokenkey']);
    }

    protected function _changeParam_880010403($getParam)
    {
        $this->appKey = '';

        if (self::OS_IOS == $getParam['os']) {
            $this->appKey = '853f668e8fe212f3fdc6f694cc489738';
        }

    }

}