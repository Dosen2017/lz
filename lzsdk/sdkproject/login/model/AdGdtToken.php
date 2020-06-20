<?php
/**
 * Created by PhpStorm.
 * User: sds
 * Date: 2019/2/28
 * Time: 19:10
 */

namespace app\login\model;

use think\Model;
use think\Config;
use think\Cache;

/**
 * Class Game
 * @package app\login\model
 *
 * 需要注意的是，ThinkPHP的数据库连接是惰性的，所以并不是在实例化的时候就连接数据库，而是在有实际的数据操作的时候才会去连接数据库。
 *
 */
class AdGdtToken extends Model
{

    protected $url = 'https://api.e.qq.com/oauth/token';

    protected $authCodeUrl = 'https://developers.e.qq.com/oauth/authorize';
    protected $authCodeRedirctUrl = 'http://localhost:8085/adGdtAuth/getAuthToken';  //待修改

    public function getAccessToken($clientId) {

        $nowTime = time();

        $res = $this->field('client_secret, access_token, refresh_token, access_token_expires_time, refresh_token_expires_time')->where(['client_id' => $clientId])->find();

        if (!$res['client_secret']) {
            return '';
        }
        //如果accessToken在有效期内，则直接返回access_token
        if ($res['access_token_expires_time'] > $nowTime) {
            return $res['access_token'];
        }

        //如果access_token不在有效期内，并且refresh_token在有效期内，则通过refresh_token获取access_token
        if ($res['refresh_token_expires_time'] > $nowTime) {
            $reqData['client_id'] = $clientId;
            $reqData['client_secret'] = $res['client_secret'];
            $reqData['grant_type'] = 'refresh_token';
            $reqData['refresh_token'] = $res['refresh_token'];

            $reqRes = httpRequest($this->url . '?' . http_build_query($reqData), null, true);
            $reqResArr = json_decode($reqRes, true);
            if ($reqResArr['code'] != 0) {
                return '';
            }

            $tokenData['access_token'] = $reqResArr['data']['access_token'];
//            $tokenData['refresh_token'] = $res['refresh_token'];  //当grant_type == refresh_token时，这个值不返回，继续使用旧的
            $tokenData['access_token_expires_in'] = $reqResArr['data']['access_token_expires_in'];
            $tokenData['access_token_expires_time'] = $nowTime + $tokenData['access_token_expires_in'];  //实际有效时间
            $tokenData['refresh_token_expires_in'] = $reqResArr['data']['refresh_token_expires_in'];
            $tokenData['refresh_token_expires_time'] = $nowTime + $tokenData['refresh_token_expires_in'];  //实际有效时间

            $tokenData['update_time'] = $reqData['update_time'];  //刷新token时间

            $this->where(['client_id' => $clientId])
                ->update($tokenData);

            return $tokenData['access_token'];

        }

        return '';

//        //如果refresh_token也不在有效期内
//        $authCodeReqData['client_id'] = $clientId;
//        $authCodeReqData['redirect_uri'] = $this->authCodeRedirctUrl;
//        $authCodeReqData['state'] = $clientId;
//        $authCodeReqData['scope'] = 'ads_management';
    }

}