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
use app\login\model\AdToutTrans as attModel;

//头条注册，充值等转化上报接口
class AdToutTrans extends Controller
{

    const PARAM_IS_NULL = 1;
    const EVENT_TYPE_ERROR = 2;
    const REQUEST_REPEAT = 5;

    const SUCCESS = 100;

    protected $actModel = null;
    protected $model = null;

    //接口上报地址
    protected $url = 'https://ad.oceanengine.com/track/activate/';

    public function __construct()
    {
        parent::__construct();
        $this->actModel = new atcModel();
        $this->model = new attModel();
    }

    /**
     * IOS:http://localhost:8085/adToutTrans/trans?idfa=2efcwe-3f32f&ip=127.0.0.1&mac=d3dwedj322f2&os=1&conv_time=1589823233&type=1
     * 安卓:http://localhost:8085/adToutTrans/trans?imei=3d23d3&oaid=2efcwe-3f32f1&ip=127.0.0.1&mac=d3dwedj322f2&androidid=32r232&os=0&conv_time=1589823233&type=1
     */
    public function trans()
    {
        $getParam = Request::instance()->get();

        $data['device_n'] = $getParam['idfa'];  //设备ID

        //如果是安卓的上报,设备ID改成下面的方式
        if ($getParam['os'] == 0) {
            $data['device_n'] = ($getParam['imei'] ?: $getParam['oaid']) ?: '';  //设备ID
        }

        $data['ip'] = $getParam['ip'];  //IP
        $data['mac'] = $getParam['mac'];  //MAC
        $data['android_n'] = $getParam['androidid'] ?: '';    //安卓ID
        $data['os'] = $getParam['os'];    //系统 0安卓, 1 IOS
        $data['conv_time'] = $getParam['conv_time'];    //转化发生时间
        $data['event_type'] = $getParam['type'];  //事件类型 0.激活 1.注册 2.付费 6.次留,暂时我们只用到注册（1） 和付费（2）

        //转化上报的结果,默认为失败
        $data['status'] = 0;
        $data['ret_content'] = '';

        $data['add_time'] = time();

file_put_contents('/tmp/AdToutTrans.txt', date('Y-m-d H:i:s') . '--' . json_encode($getParam) . "\n", FILE_APPEND);

        if ( is_null($data['os']) || is_null($data['event_type']) ) {
            $this->_exitJson(self::PARAM_IS_NULL, 'param is null');
        }

        if ( !in_array($data['event_type'], [1,2]) ) {
            $this->_exitJson(self::EVENT_TYPE_ERROR, 'event_type is error');
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
        if ( $this->model->where(['uni_id' => $data['uni_id'], 'conv_time' => ['gt', $uniLimitTime]])->find() ){
            $this->_exitJson(self::REQUEST_REPEAT, 'request repeat');  //请求重复
        }
        //判断是否重复请求-------------------------end

        if( !empty($data['device_n']) ) {
            $limitTime = time() - 7 * 86400;
            $sqlData = $this->actModel->field('callback')
                    ->where("(device_n = '" . $data['device_n'] . "' or android_n = '" . $data['android_n'] . "' or mac = '" . $data['mac'] . "') and os = '" . $data['os'] . "' and click_time > " . $limitTime)
                    ->find();

//            echo $this->actModel->getLastSql();exit;

            if (!$sqlData['callback']) {
                //没匹配到
                $data['status'] = 2;
            } else {
                //匹配到，则上报数据
                $reportData['callback'] = $sqlData['callback'];    //回调数据
                $reportData['imei'] = $sqlData['imei'] ?: '';    //安卓手机 imei 的 md5 摘要
                $reportData['idfa'] = $sqlData['idfa'] ?: '';    //ios 手机的 idfa 原值
                $reportData['muid'] = $data['device_n'];
                $reportData['oaid'] = $data['oaid'] ?: '';              //	Android Q 版本的 oaid 原值
                $reportData['os'] = $data['os'];
                $reportData['event_type'] = $data['event_type'];  //事件类型
                $reportData['conv_time'] = $data['conv_time'];

                $ret = httpRequest($this->url . '?' . http_build_query($reportData), null, true);

                $retArr = json_decode($ret, true);

                if ($retArr['code'] == 0 && $retArr['msg'] == 'success') {
                    $data['status'] = 1;  //上报成功就把状态改成1

                } else {
                    $data['ret_content'] = $ret;
                }
            }
        }

        $this->model->save($data);

        $this->_exitJson(self::SUCCESS);
    }


//    public function transaz()
//    {
//        $getParam = Request::instance()->get();
////        var_dump($getParam);exit;
//        $data['device_n'] = ($getParam['imei'] ?: $getParam['oaid']) ?: '';  //设备ID
//        $data['ip'] = $getParam['ip'];  //IP
//        $data['mac'] = $getParam['mac'];  //MAC
////        $data['oaid'] = $getParam['oaid'];  // 安卓Q以上的设备ID
//
////        $data['android_n'] = $getParam['androidid'] ?: '';    //安卓ID
//
//        $data['os'] = $getParam['os'];    //系统0安卓, 1 IOS
//        $data['conv_time'] = $getParam['conv_time'];    //转化发生时间
//        $data['event_type'] = $getParam['type'];  //事件类型 0.激活 1.注册 2.付费 6.次留,暂时我们只用到注册（1） 和付费（2）
//
//        //转化上报的结果,默认为失败
//        $data['status'] = 0;
//        $data['ret_content'] = '';
//
//        $data['add_time'] = time();
//
//        if ( is_null($data['os']) || is_null($data['event_type']) ) {
//            $this->_exitJson(self::PARAM_IS_NULL, 'param is null');
//        }
//
//        if ( !in_array($data['event_type'], [1,2]) ) {
//            $this->_exitJson(self::EVENT_TYPE_ERROR, 'event_type is error');
//        }
//
//        if( !empty($data['device_n']) ) {
//            $limitTime = time() - 7 * 86400;
//            $sqlData = $this->actModel->field('callback')->where(['device_n' => $data['device_n'], 'os' => $data['os'], 'click_time' => ['gt', $limitTime]])->find();
//            if (!$sqlData['callback']) {
//
////                $sqlData = $this->actModel->field('callback')->where(['android_n' => $data['android_n'], 'os' => $data['os'], 'click_time' => ['gt', $limitTime]])->find();
////                if (!$sqlData['callback']) {
//                    //没匹配到
//                    $data['status'] = 2;
//                }else {
//                    //匹配到，则上报数据
//                    $reportData['callback'] = $sqlData['callback'];    //回调数据
//                    $reportData['event_type'] = $data['event_type'];  //事件类型
//
//                    //区别于IOS的参数【idfa】
//                    $reportData['muid'] = $data['device_n'];
////                    $reportData['oaid'] = $data['android_n'];
//
//                    $reportData['os'] = $data['os'];
//                    $reportData['conv_time'] = $data['conv_time'];
//
//                    $ret = httpRequest($this->url . '?' . http_build_query($reportData), null, true);
////var_dump($ret);exit;
//                    $retArr = json_decode($ret, true);
//
//                    if ($retArr['code'] == 0 && $retArr['msg'] == 'success') {
//                        $data['status'] = 1;  //上报成功就把状态改成1
//
//                    } else {
//                        $data['ret_content'] = $ret;
//                    }
//                }
//
////            }
//        }
//
//
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