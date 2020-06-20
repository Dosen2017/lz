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

use app\login\model\AdToutClick as atcModel;


//头条点击事件日志记录接口
class AdToutClick extends Controller
{

//    IOS示例：https://xxxx.xxx.com?aid=__AID__&csite=__CSITE__&request_id=__REQUEST_ID__&idfa=__IDFA__&ip=__IP__&mac=__MAC__&os=__OS__&ts=__TS__&callback=__CALLBACK_PARAM__
//    安卓示例：https://xxxx.xxx.com?aid=__AID__&csite=__CSITE__&request_id=__REQUEST_ID__&imei=__IMEI__&ip=__IP__&mac=__MAC__&oaid=__OAID__&androidid=__ANDROIDID__&os=__OS__&ts=__TS__&callback=__CALLBACK_PARAM__

    const PARAM_IS_NULL = 1;
    const REQUEST_ID_IS_EXIST = 2;

    const SUCCESS = 100;

    protected $model = null;

    public function __construct()
    {
        parent::__construct();
        $this->model = new atcModel();
    }

    /**
     * IOS:http://localhost:8085/adToutClick/log?aid=tf_sfew22_112&csite=1&request_id=sdff3f23f23f&idfa=2efcwe-3f32f&ip=127.0.0.1&mac=d3dwedj322f2&os=1&ts=1589823233&callback=32d23fd2f23
     * 安卓:http://localhost:8085/adToutClick/log?aid=tf_sfew22_112&csite=1&request_id=sdff3f23f23f&imei=3d23d3&oaid=d32fd3f3&androidid=f2f32f2&ip=127.0.0.1&mac=d3dwedj322f2&os=0&ts=1589823233&callback=32d23fd2f23
     */
    public function log()
    {
        $getParam = Request::instance()->get();

        $data['ads_n'] = $getParam['aid'];  //广告ID

        $data['csite'] = $getParam['csite'];  //广告投放位置
        $data['request_id'] = $getParam['request_id'];  //请求下发的 id

        $data['device_n'] = $getParam['idfa'];  //设备ID
        //如果是安卓的上报,设备ID改成下面的方式
        if ($getParam['os'] == 0) {
            $data['device_n'] = ($getParam['imei'] ?: $getParam['oaid']) ?: '';  //设备ID
        }

        $data['ip'] = $getParam['ip'];  //IP
        $data['mac'] = $getParam['mac'];  //MAC

        $data['android_n'] = $getParam['androidid'] ?: '';    //安卓ID

        $data['os'] = $getParam['os'];    //系统 安卓：0  IOS：1  其他：3
        $data['click_time'] = $getParam['ts'];  //点击时间
        $data['callback'] = $getParam['callback'];  //回调参数

        $data['callback_url'] = $getParam['callback_url'] ?: '';  //回调地址，和回调参数二选一

        $data['add_time'] = time();

file_put_contents('/tmp/AdToutClick.txt', date('Y-m-d H:i:s') . '--' . json_encode($getParam) . "\n", FILE_APPEND);

        if ( empty($data['ads_n']) ) {
            $this->_exitJson(self::PARAM_IS_NULL, 'param is null');
        }

        if ( $this->model->where(['request_id' => $data['request_id']])->find() ) {
            $this->_exitJson(self::REQUEST_ID_IS_EXIST, 'request_id is exist');
        }

        $this->model->save($data);

        $this->_exitJson(self::SUCCESS);
    }

    /**
     * http://localhost:8085/adToutClick/logios?aid=tf_sfew22_112&idfa=2efcwe-3f32f&ip=127.0.0.1&mac=d3dwedj322f2&os=ios&ts=1589823233&callback=32d23fd2f23
     */
//    public function logios()
//    {
//        $getParam = Request::instance()->get();
//
//        $data['ads_n'] = $getParam['aid'];  //广告ID
//        $data['device_n'] = $getParam['idfa'];  //设备ID
//        $data['ip'] = $getParam['ip'];  //IP
//        $data['mac'] = $getParam['mac'];  //MAC
////        $data['android_n'] = '';  //安卓ID
//        $data['os'] = $getParam['os'];    //系统
//        $data['click_time'] = $getParam['ts'];  //点击时间
//        $data['callback'] = $getParam['callback'];  //回调参数
//
//        $data['callback_url'] = $getParam['callback_url'] ?: '';  //回调地址，和回调参数二选一
//
//        $data['add_time'] = time();
//
//        if ( empty($data['ads_n']) ) {
//            $this->_exitJson(self::PARAM_IS_NULL, 'param is null');
//        }
//
//        $this->model->save($data);
//
//        $this->_exitJson(self::SUCCESS);
//    }

    /**
     * http://localhost:8085/adToutClick/logaz?aid=tf_sfew22_112&imei=3d23d3&oaid=d32fd3f3&ip=127.0.0.1&mac=d3dwedj322f2&androidid=f2f32f2&os=android&ts=1589823233&callback=32d23fd2f23
     */
//    public function logaz()
//    {
//        $getParam = Request::instance()->get();
//
//        $data['ads_n'] = $getParam['aid'];  //广告ID
//        $data['device_n'] = ($getParam['imei'] ?: $getParam['oaid']) ?: '';  //设备ID
//        $data['ip'] = $getParam['ip'];  //IP
//        $data['mac'] = $getParam['mac'];  //MAC
////        $data['oaid'] = $getParam['oaid'];  // 安卓Q以上的设备ID
//
//        $data['android_n'] = $getParam['androidid'] ?: '';    //安卓ID
//
//        $data['os'] = $getParam['os'];    //系统
//        $data['click_time'] = $getParam['ts'];  //点击时间
//        $data['callback'] = $getParam['callback'];  //回调参数
//
//        $data['callback_url'] = $getParam['callback_url'] ?: '';  //回调地址，和回调参数二选一
//
//        $data['add_time'] = time();
//
//        $this->model->save($data);
//
//        $this->_exitJson(self::SUCCESS);
//    }

    protected function _exitJson($ret, $mes = '')
    {
        $res['ret'] = $ret;
        $res['mes'] = $mes;
//var_dump($ret);exit;
        echo json_encode($res);exit;
    }
}