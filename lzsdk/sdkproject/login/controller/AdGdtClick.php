<?php
/**
 * Created by sudesheng.
 * User: 2066815257@qq.com
 * Date: 2020/03/202 0010
 * Time: 上午 11“28
 *
 * 广点通监测链接接收
 */
namespace app\login\controller;

use think\Controller;
use think\Request;

use app\login\model\AdGdtClick as agcModel;
use app\login\model\AdGdtClickError as agcErrorModel;


//广点通点击事件日志记录接口
class AdGdtClick extends Controller
{

//    IOS示例：
//    安卓示例：
    const PARAM_IS_NULL = 1;
    const REQUEST_IS_EXSITS = 2;

    const SUCCESS = 100;

    protected $model = null;
    protected $errModel = null;

    public function __construct()
    {
        parent::__construct();
        $this->model = new agcModel();
        $this->errModel = new agcErrorModel();
    }

    /**
     *
     *http://localhost:8085/adGdtClick/log?muid=232cewcwec311&click_time=1585662442&click_id=ed33r23&appid=34332&advertiser_id=3223232
     *      &app_type=android&android_id=32f324ffewfef&mac=c43fewfwef3443&ip=127.0.0.1&user_agent=ec4r23ff&adgroup_id=152664&device_os_type=ios&request_id=3e32r322&oaid=d3d23dwd23
     *
     */
    public function log()
    {
        $getParam = Request::instance()->get();

        $data['muid'] = $getParam['muid'];  //设备id，由IMEI（Android应用）md5生成，或是由IDFA（iOS应用）md5生成
        $data['click_time'] = $getParam['click_time'];  //点击发生的时间，由腾讯广告系统生成，取值为标准时间戳，秒级别

        $data['click_id'] = $getParam['click_id'];  //腾讯广告后台生成的点击id，腾讯广告系统中标识用户每次点击生成的唯一标识
        $data['appid'] = $getParam['appid'];  //Android应用ID为应用宝appid，iOS应用ID为Apple App Store的id

        $data['advertiser_id'] = $getParam['advertiser_id'];    //广告主在腾讯广告（e.qq.com）的账户id

        $data['app_type'] = $getParam['app_type'];    //app类型；取值为 android或ios（联盟Android为unionandroid）；注意是小写；根据广告主在腾讯广告（e.qq.com）创建转化时提交的基本信息关联
        $data['android_id'] = $getParam['android_id'];  //由android_idmd5生成，当设备id获取不到或匹配不上时，可以作为归因补充
        $data['mac'] = $getParam['mac'];  //由mac地址去掉”:” 大写后MD5生成，当设备id获取不到或匹配不上时，可以作为归因补充

        $data['ip'] = $getParam['ip'];  //IP地址，不加密，当设备号获取不到或匹配不上时，可以作为归因补充

        $data['user_agent'] = $getParam['user_agent'];  //urlencode，当设备号获取不到或匹配不上时，可以作为归因补充

//        $data['campaign_id'] = $getParam['campaign_id'];  //推广计划id
        $data['adgroup_id'] = $getParam['adgroup_id'];  //广告id
//        $data['creative_id'] = $getParam['creative_id'];  //创意id
//        $data['agent_id'] = $getParam['agent_id'];  //代理商id
//        $data['deeplink_url'] = $getParam['deeplink_url'];  //	应用直达链接
//        $data['dest_url'] = $getParam['dest_url'];  //落地页链接
        $data['device_os_type'] = $getParam['device_os_type'];  //设备系统，ios、android
//        $data['click_sku_id'] = $getParam['click_sku_id'];  //DPA商品id
//        $data['process_time'] = $getParam['process_time'];  //请求时间
//        $data['product_type'] = $getParam['product_type'];  //商品类型
        $data['request_id'] = $getParam['request_id'];  //请求id

//        $data['site_set'] = $getParam['site_set'];  //站点集id
//
//        $data['adgroup_name'] = $getParam['adgroup_name'];  //广告名称
        $data['oaid'] = $getParam['oaid'] ?: "";  //安卓系统移动终端补充设备标识

        $data['callback_params'] = json_encode($getParam);

        $data['add_time'] = time();

file_put_contents('/tmp/AdGdtClick.txt', date('Y-m-d H:i:s') . '--' . json_encode($getParam) . "\n", FILE_APPEND);

        if ( empty($data['adgroup_id']) ) {
            $this->_exitJson(self::PARAM_IS_NULL, 'param is null');
        }

        //判断是否已经请求过了
        if ( $this->model->where(['click_id' => $data['click_id']])->find() ) {
            $errData['muid'] = $data['muid'];
            $errData['click_id'] = $data['click_id'];
            $errData['appid'] = $data['appid'];
            $errData['adgroup_id'] = $data['adgroup_id'];
            $errData['request_id'] = $data['request_id'];
            $errData['oaid'] = $data['oaid'];
            $errData['callback_params'] = $data['callback_params'];

            $this->errModel->save($errData);

            $this->_exitJson(self::REQUEST_IS_EXSITS, 'click_id is exits');
        }



        $this->model->save($data);

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