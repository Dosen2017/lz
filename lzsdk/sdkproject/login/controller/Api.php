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

use app\login\helper\RedisH;
use app\login\model\CommonAdlogs;
use app\login\model\CommonApis;
use app\login\model\PageStats;
use think\Controller;
use think\Request;


//通用广告记录
class Api extends Controller
{
    
    protected $model = null;

    public function __construct()
    {
        parent::__construct();
        $this->model = new CommonAdlogs();
    }

    public function general()
    {
        $adsId =explode("?", basename($_SERVER['REQUEST_URI']))[0];

        $getParam = Request::instance()->get();

        //用redis做重复请求的判断
        $unique = md5("lzsdk:login:Api:general:" . $adsId . http_build_query($getParam)); //将所有的参数形成一个唯一键，进行MD5[一般也是生成一个唯一的MD5值]
        $redisConn = new RedisH();
        $uniqData = $redisConn->get($unique);
        if ($uniqData) {
            $this->_exitJson(false, 'repeat request');
        }

        $data['ads_id'] = $adsId;  //广告ID
        !$data['ads_id'] && $this->_exitJson(false, 'ads_id is null');
        //通过广告Id去common_apis里面去取渠道ID和需要获取的参数
        $commonApisModel = new CommonApis();
        $info = $commonApisModel->getInfoData($data['ads_id']);
        !$info && $this->_exitJson(false, 'ads_id is not exist');

        $data['channel_id'] = $info['channel_id'];  //渠道ID
        unset($info['channel_id']);

        $data['device_id'] = $getParam[$info['query_deviceid']] ?: '';  //获取设备ID
        $data['mac'] = $getParam[$info['query_mac']] ?: '';             //获取mac
        $data['ip'] = $getParam[$info['query_ip']] ?: '';               //获取IP
        $data['callback_url'] = $getParam[$info['query_callback']] ?: '';  //获取回调URL
        $data['add_time'] = time();
        $this->model->save($data);

        //保存数据库成功，才设置缓存
        $redisConn->set($unique, 1, 60);  //暂时设置60秒

        //修改page_stats表
        $this->handlePageStats($info['id']);

        $this->_exitJson();
    }

    protected function handlePageStats($Id)
    {
        //------------------------- PageStats对统计表进行修改 ------------------------------
        $pageStatModel = new PageStats();

        $pageStatData['type'] = "common_api";   // common_api
        $pageStatData['source_id'] = (int)$Id;
        $pageStatData['opt_d'] = (int)date('Ymd');

        if (!$pageStatModel->where($pageStatData)->find()) {
            //不存在记录时，添加数据到page_stats表
            $pageStatData['view_count'] = 1;
            $pageStatData['hit_count'] = 0;

            $pageStatModel->save($pageStatData);

        } else {
            $pageStatModel->where($pageStatData)->setInc('view_count');
        }
        //------------------------- PageStats对统计表进行修改 -------------------------------
    }

    protected function _exitJson($ret = true, $msg = 'success')
    {
        $res['ret'] = $ret;
        $res['msg'] = $msg;
//var_dump($ret);exit;
        echo json_encode($res);exit;
    }
}