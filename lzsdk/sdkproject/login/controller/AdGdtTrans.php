<?php
/**
 * Created by sudesheng.
 * User: 2066815257@qq.com
 * Date: 2020/02/27 0010
 * Time: 下午 17:19
 *
 * 头条监测链接接收
 */
namespace app\login\controller;

use think\Controller;
use think\Request;

use app\login\model\AdGdtClick as agcModel;
use app\login\model\AdGdtToken as gdtTokenModel;
use app\login\model\AdGdtTrans as gdtTModel;

//广点通注册，充值等转化上报接口
class AdGdtTrans extends Controller
{

    const PARAM_IS_NULL = 1;
    const ACTION_TYPE_ERROR = 2;
    const USER_ACTION_SET_ID_GET_ERROR = 3;
    const ACCESS_TOKEN_ERROR = 4;
    const REQUEST_REPEAT = 5;

    const SUCCESS = 100;

    const OS_TYPE_ANDROID = 'android';
    const OS_TYPE_IOS = 'ios';

    protected $agcModel = null;
    protected $gdtTokenModel = null;
    protected $model = null;

    protected $actionTypeList = ['REGISTER', 'COMPLETE_ORDER'];
    protected $osList = ['web','android','ios','offline'];

    protected $userActionSetIdUrl = 'https://api.e.qq.com/v1.1/user_action_sets/add';

    //接口上报地址
    protected $url = 'https://api.e.qq.com/v1.1/user_actions/add';

    public function __construct()
    {
        parent::__construct();
        $this->agcModel = new agcModel();
        $this->gdtTokenModel = new gdtTokenModel();
        $this->model = new gdtTModel();
    }

    /**
     * IOS:http://localhost:8085/adGdtTrans/trans?client_id=232231231&appid=1212121&account_id=fccw3r23&idfa=3223232&mac=32f324ffewfef&os=ios&action_type=REGISTER&action_time=1588451236
     * 安卓:http://localhost:8085/adGdtTrans/trans?client_id=232cewcwec311&appid=1212121&account_id=fccw3r23&imei=ed33r23&android_id=34332&oaid=3223232&mac=32f324ffewfef&os=android&action_type=REGISTER&action_time=1588451236
     *
     * client_id:      应用ID
     * os:             传ios或android
     * action_type :   REGISTER或COMPLETE_ORDER 等
     *
     *  上报数据格式
        {
            “account_id”: “<your_account_id>“,
            “user_action_set_id”: “<your_user_action_set_id>“,
            “actions”: [
                {
                    “action_time”: <action_timestamp>,
                    “user_id”: {
                        “hash_imei”: “<MD5_hash_imei>“,
                        “hash_phone”: “<MD5_hash_phone_number>“, //非必填, 电话号码直接MD5编码
                        “hash_android_id”: “<MD5_hash_android_id>“, //非必填, 对 android_id 进行MD5编码
                        “hash_mac”:”<MD5_hash_mac>” //非必填, mac 地址去掉‘:’ 后保持大写进行MD5编码
                        “oaid”: “<oaid>”   //Android选填，推荐使用
                    },
                    “action_type”: “ACTIVATE_APP”,
                    “trace”: {
                        “click_id”: “<CLICK_ID>” //非必填，推荐使用方案一的时候填入
                    }
                }
            ]
        }

     */
    public function trans()
    {
        $getParam = Request::instance()->get();

        $data['client_id'] = $getParam['client_id'];//应用ID  主要用于获取access_token

        $data['appid'] = $getParam['appid'];//Android应用ID为应用宝appid，iOS应用ID为Apple App Store的id  用于区别游戏

        $data['account_id'] = $getParam['account_id'] ;  //账号ID

        if ($getParam['os'] == self::OS_TYPE_ANDROID) {
            $data['imei'] = $getParam['imei'] ;  //设备ID
            $data['android_id'] = $getParam['android_id'];  //安卓ID
            $data['oaid'] = $getParam['oaid'] ;  //安卓系统移动终端补充设备标识

            $muid = $data['imei'];
        }

        if ($getParam['os'] == self::OS_TYPE_IOS) {
            $data['idfa'] = $getParam['idfa'] ;

            $muid = $data['idfa'];
        }


        $data['mac'] = $getParam['mac'];  //MAC
//        $data['phone'] = $getParam['phone'] ;  //电话号码
//        $data['ip'] = $getParam['ip'];  //IP

        $data['os'] = $getParam['os'];

        $data['action_type'] = $getParam['action_type'];    //注册 REGISTER  下单 COMPLETE_ORDER
        $data['action_time'] = $getParam['action_time'];

//var_dump($data);exit;
        //转化上报的结果,默认为失败
        $data['status'] = 0;
        $data['ret_content'] = '';

        $data['add_time'] = time();

file_put_contents('/tmp/AdGdtTrans.txt', date('Y-m-d H:i:s') . '--' . json_encode($getParam) . "\n", FILE_APPEND);

        if (empty($data['client_id']) || empty($data['account_id'])) {
            $this->_exitJson(self::PARAM_IS_NULL, 'param is null');
        }

        if ( !in_array($data['action_type'], $this->actionTypeList) || !in_array($data['os'], $this->osList) ) {
            $this->_exitJson(self::ACTION_TYPE_ERROR, 'type is error');
        }

        //判断是否重复请求-------------------------start
        $unqIdStr = '';
        foreach ($getParam as $k => $v) {
            if (!is_null($v)) {
                $unqIdStr .= $v;
            }
        }

        $data['uni_id'] = md5($unqIdStr);//生成一个大致唯一的md5值，因为可能出现hash碰撞，所以会建立一个普通索引，并加入时间的条件限制
        $uniLimitTime = time() - 5 * 60;  //五分钟内
        if ( $this->model->where(['uni_id' => $data['uni_id'], 'action_time' => ['gt', $uniLimitTime]])->find() ){
            $this->_exitJson(self::REQUEST_REPEAT, 'request repeat');  //请求重复
        }
        //判断是否重复请求-------------------------end

        if( !empty($muid) ) {
            $limitTime = time() - 7 * 86400;
            $sqlData = $this->agcModel->field('click_id')
                ->where("(android_id = '". $data['android_id'] . "' or oaid = '" . $data['oaid'] ."' or muid = '". $muid . "') and  add_time > " . $limitTime . " and appid = " . $data['appid'] . " and device_os_type = '" . $data['os'] . "'")
                ->find();
//            echo $this->agcModel->getLastSql();exit;
//var_dump($sqlData);exit;
            //上报的数据
            $reportData['account_id'] = $data['account_id'];
            $reportData['user_action_set_id'] = $this->_getUserActionSetId($data['client_id'], $data['os'], $data['account_id'], $data['appid']);
//var_dump($reportData['user_action_set_id']);exit;
            if (!$reportData['user_action_set_id']) {
                $this->_exitJson(self::USER_ACTION_SET_ID_GET_ERROR, 'user_action_set_id get error');
            }

            $reportData['actions'][0]['action_time'] = time();

            //如果是安卓，传入下面三个值
            if ($getParam['os'] == self::OS_TYPE_ANDROID) {
                $reportData['actions'][0]['user_id']['hash_imei'] = md5(strtolower($data['imei']));

                if ($data['android_id']) {
                    $reportData['actions'][0]['user_id']['hash_android_id'] = md5($data['android_id']);
                }
                $reportData['actions'][0]['user_id']['oaid'] = $data['oaid'];
            }

            //如果是IOS，下面一个值
            if ($getParam['os'] == self::OS_TYPE_IOS) {
                $reportData['actions'][0]['user_id']['hash_idfa'] = md5(strtoupper($data['idfa']));
            }

//            $reportData['actions'][0]['user_id']['hash_phone'] = '';
            $reportData['actions'][0]['user_id']['hash_mac'] = md5(strtoupper( str_replace(':', '',$data['mac']) ));


            $reportData['actions'][0]['action_type'] = $data['action_type'];

            $reportData['actions'][0]['trace']['click_id']= $sqlData['click_id'] ?: '';

            $reportGetData['access_token'] = $this->gdtTokenModel->getAccessToken($data['client_id']);
            if (!$reportGetData['access_token']) {
                $this->_exitJson(self::ACCESS_TOKEN_ERROR, 'access_token get error');
            }
            $reportGetData['timestamp'] = time();
            $reportGetData['nonce'] = uniqid() . $reportGetData['timestamp'] . random(5);

            $ret = httpRequest($this->url . "?" . http_build_query($reportGetData), json_encode($reportData), true, 'post', 2);
            $retArr = json_decode($ret, true);

            if ($retArr['code'] == 0) {
                $data['status'] = 1;  //上报成功就把状态改成1
            } else {
                $data['ret_content'] = $ret;
            }

        }

        $this->model->save($data);

        $this->_exitJson(self::SUCCESS);
    }

    protected function _getUserActionSetId($clientId, $os, $accountId, $appid) {
        $getData['access_token'] = $this->gdtTokenModel->getAccessToken($clientId);

        if ('' == $getData['access_token'] ) {
            return false;
        }
        //获取user_action_set_id值
        $reqData['account_id'] = $accountId;
        $reqData['type'] = strtoupper($os);  //用户行为源类型，[枚举详情]枚举列表：{ WEB, ANDROID, IOS, OFFLINE }
        $reqData['mobile_app_id'] = $appid;  //应用 id，IOS：App Store id ； ANDROID：应用宝 id，type=ANDROID 或 IOS 时必填
//        $reqData['name'] = ''; //用户行为源名称，当 type=WEB 时必填，当 type=ANDROID 或 IOS 时，若未填写该字段，则默认通过 mobile_app_id 获取名称字段长度最小 1 字节，长度最大 32 字节
        $reqData['description'] = '';

        $nowTime = time();
        $reqGetData['access_token'] = $getData['access_token'];
        $reqGetData['timestamp'] = $nowTime;
        $reqGetData['nonce'] = uniqid() . $nowTime . random(5);

        $ret = httpRequest($this->userActionSetIdUrl . "?" . http_build_query($reqGetData), json_encode($reqData), true, 'post', 2);
        $retArr = json_decode($ret, true);
        if ($retArr['code'] != 0) {
            return '';
        }

        return $retArr['data']['user_action_set_id'];
    }

    protected function _exitJson($ret, $mes = '')
    {
        $res['ret'] = $ret;
        $res['mes'] = $mes;
//var_dump($ret);exit;
        echo json_encode($res);exit;
    }
}