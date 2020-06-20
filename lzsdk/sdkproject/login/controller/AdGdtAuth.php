<?php
/**
 * Created by sudesheng.
 * User: 2066815257@qq.com
 * Date: 2020/02/29 0010
 * Time: 下午 11:41
 *
 * 广点通授权api
 */
namespace app\login\controller;

use think\Controller;
use think\Request;

use app\login\model\AdGdtToken as gdtTokenModel;


//
class AdGdtAuth extends Controller
{
    const PARAM_IS_NULL = 1;
    const GET_TOKEN_EROOR = 2;
    const CLIENT_SECRET_IS_NULL = 3;

    const SUCCESS = 100;

    const GRANT_TYPE_1 = 'authorization_code';
    const GRANT_TYPE_2 = 'refresh_token';

    protected $redirectUrl = '';

    protected $url = 'https://api.e.qq.com/oauth/token';

    protected $clientIdToclientSecret = [
        10000 => 'aaaaaaa',
    ];

    protected $model = null;

    public function __construct()
    {
        parent::__construct();
        $this->model = new gdtTokenModel();
    }

    /**
     *
     *step1:https://developers.e.qq.com/oauth/authorize?client_id=123456&redirect_uri=https%3a%2f%2fwww.example.com%3fpara1%3da%26para2%3db&state=&scope=ADS_MANAGEMENT&account_type=ACCOUNT_TYPE_QQ
     *  client_id：开发者创建的应用程序的唯一标识id，必填，可通过【应用程序管理页面】查看；
     *  redirect_uri：回调地址，由开发者自行提供和定义（地址主域需与开发者创建应用程序时登记的回调主域一致），用于跳转并接收回调信息，必填，该字段需要做UrlEncode，确保后续跳转正常；
     *  state：开发者自定义参数，可用于验证请求有效性或者传递其他自定义信息，回调的时候会原样带回，可选；
     *  scope：授权的能力范围，可选，不传时表示授权范围为当前应用程序所拥有的所有全部权限；
     *  account_type：授权用的账号类型，可选，包括QQ号和微信号，不传时默认为QQ号
     *
     *
     *  这里接收是根据配置了redirect_url回调过来的
     *
     *  //访问地址
     *  https://developers.e.qq.com/oauth/authorize?client_id=123456&redirect_uri=https%3a%2f%2fwww.example.com%3fpara1%3da%26para2%3db&state=123456
     *
     * redirect_uri : http://localhost:8085/adGdtAuth/getAuthToken【?authorization_code=232311&state=2e32d2】
     *
     */
    public function getAuthToken()
    {
        $getParam = Request::instance()->get();
        $data['authorization_code'] = $getParam['authorization_code'];
        $data['state'] = $getParam['state'];  //这里定义成client_id
        $data['add_time'] = time();

file_put_contents('/tmp/AdGdtAuth.txt', date('Y-m-d H:i:s') . '--' . json_encode($getParam) . "\n", FILE_APPEND);

        if (empty($data['authorization_code']) || empty($data['state'])) {
            $this->_exitJson(self::PARAM_IS_NULL, 'param is null');
        }

        //请求获取access_token和refresh_token
        $reqData['client_id'] = $data['state'];

        $clientSecret = $this->clientIdToclientSecret[$reqData['client_id']];
        if (empty($clientSecret)) {
            $this->_exitJson(self::CLIENT_SECRET_IS_NULL, 'client secret is null');
        }

        $reqData['client_secret'] = $clientSecret;
        $reqData['grant_type'] = self::GRANT_TYPE_1;
        $reqData['authorization_code'] = $data['authorization_code'];
//        $reqData['refresh_token'] = '';
        $reqData['redirect_uri'] = $this->redirectUrl;  //是否urlencode, 有待测试

        $res = httpRequest($this->url . '?' . http_build_query($reqData), null, true);

file_put_contents('/tmp/AdGdtAuthResult.txt', date('Y-m-d H:i:s') . '--' . $res . "\n", FILE_APPEND);

        $resArr = json_decode($res, true);

        if ($resArr['code'] != 0) {
            $this->_exitJson(self::GET_TOKEN_EROOR, 'get token error');
        }

        //先判断client_id相关的参数是否存在, 如果存在就先删除，后插入
        if ( $this->model->where(['client_id' => $reqData['client_id']])->find() ) {
            $this->model->where(['client_id' => $reqData['client_id']])->delete();
        }

        $nowTime = time();

        $tokenData['access_token'] = $resArr['data']['access_token'];
        $tokenData['refresh_token'] = $resArr['data']['refresh_token'];
        $tokenData['access_token_expires_in'] = $resArr['data']['access_token_expires_in'];
        $tokenData['access_token_expires_time'] = $nowTime + $tokenData['access_token_expires_in'];  //实际有效时间
        $tokenData['refresh_token_expires_in'] = $resArr['data']['refresh_token_expires_in'];
        $tokenData['refresh_token_expires_time'] = $nowTime + $tokenData['refresh_token_expires_in'];  //实际有效时间

        $tokenData['client_id'] = $reqData['client_id'];  //应用ID
        $tokenData['client_secret'] = $this->clientIdToclientSecret[$reqData['client_id']];  //应用 secret

        $tokenData['add_time'] = $reqData['add_time'];  //添加时间

        $this->model->save($tokenData);

        $this->_exitJson(self::SUCCESS);
    }

    protected function _exitJson($ret, $mes = '')
    {
        $res['ret'] = $ret;
        $res['mes'] = $mes;
//var_dump($ret);exit;
        echo json_encode($res);exit;
    }
}