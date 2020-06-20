<?php
/**
 * Created by sudesheng.
 * User: 2066815257@qq.com
 * Date: 15-7-17
 * Time: 下午2:46
 */
namespace app\recharge\controller;

use think\Controller;
use think\Request;

use app\recharge\helper;

class OrderXiaoqi extends Controller
{
    protected $helper;
    public function __construct()
    {
        $this->helper = new helper\Api();
        parent::__construct();
    }

    protected $publicRSA = array(
        //  IOS越狱
//        1 => 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDX7W/Qy+4x4En7cycMuMhoKRDRzrqUQBfxHoVseH5bBBfJK4TXmKPkAqh7pUcbH7xjSK1Ajj8Sd2MA8ILGqVTBabZPMH3OEjaF0Lh3T507TQvLfl0dJFY8S6oPM1S72P9BoyoGlwFmGV7qr34Cb1jnPJu9QVY5/K1lPDES5O0/xwIDAQAB',
//        //  安卓
//        2 => 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCY5FfrRkMXzGtwBW0UwS3oS4tv2Vdfux3AIA/aqV36T5h2seUhnJQb/HixP34dxpHeMSq1azTIA0cxPd47MRqACG7hf5jc0h2QHilvRlf4zwoldH4gQO6w8Ua+A1WVGvWYEzOAA4BwBsyg9Mkcj+G93WZC0rCf0ZqYxD7zafrU8QIDAQAB'
    );

    public function index()  //默认为安卓
    {
        // 获取请求
        $reqParam = Request::instance()->post();
        foreach ($reqParam as $k => $v) {
            $reqParam[$k] = str_replace("\\", '', $v);
        }

        $data['extends_info_data'] = $reqParam['extends_info_data'];       //订单扩展数据   N   channelPkgNum_orderId_os
        $data['game_area'] = $reqParam['game_area'];   //角色所在的游戏区

        $data['game_level'] = $reqParam['game_level'];         //用户游戏中角色等级
        $data['game_orderid'] = $reqParam['game_orderid'];  //游戏订单号
        $data['game_price'] = $reqParam['game_price'];      //商品价格（使用元为单位）
        $data['game_role_id'] = $reqParam['game_role_id'];   //用户游戏中角色 ID 信息
        $data['game_role_name'] = $reqParam['game_role_name'];  //用户游戏中角色名称

        $data['game_guid'] = $reqParam['game_guid'];         //标识用户在小 7 平台中的唯一标识

        $data['notify_id'] = $reqParam['notify_id'];    //回调通知的 id
        $data['subject'] = $reqParam['subject'];   // 道具简介
        //$data['game_sign'] = $reqParam['game_sign'];

        $data['time'] = $reqParam['time'];
        $data['sign'] = $reqParam['sign'];
//        var_dump($data);exit;
//
        !$this->helper->checkParamIsNull($data) && $this->helper->echoJson(helper\ErrorOrders::ERROR_MISSED_PARAM);
        !$this->helper->checkSign($data) && $this->helper->echoJson(helper\ErrorOrders::ERROR_SIGN);

        $extendsInfoDataArr = explode('_', $data['extends_info_data']);
        $channelPkgNum = $extendsInfoDataArr[0];

        $this->_changeParamByAppIdAndOS($channelPkgNum);

        //生成game_sign
        $str = '';
        $dataSign['game_area'] = $data['game_area'];
        $dataSign['game_orderid'] = $data['game_orderid'];
        $dataSign['game_price'] = $data['game_price'] / 100;
        $dataSign['game_guid'] = $data['game_guid'];
        $dataSign['subject'] = $data['subject'];
//        unset($dataSign['os'], $dataSign['sign']);
        ksort($dataSign);
        foreach($dataSign as $k => $v)
        {
//            $str .= $k . '=' . urlencode($v) . '&';
            $str .= $k . '=' . $v . '&';
        }

//        echo $this->publicRSA[$reqParam['os']];exit;

        $str = substr($str, 0,-1) . $this->publicRSA[$extendsInfoDataArr[2]];
        $data['game_sign'] = md5($str);
//var_dump($data);exit;
        unset($data['time'], $dataSign['sign']);

        echo json_encode($data);exit;   //返回订单号
    }

    protected function _changeParamByAppIdAndOS($channelPkgNum)
    {
        if (880010403 == $channelPkgNum) {
            $this->publicRSA = array(
                //  IOS越狱
                1 => 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQC+zCNgOlIjhbsEhrGN7De2uYcfpwNmmbS6HYYI5KljuYNua4v7ZsQx5gTnJCZ+aaBqAIRxM+5glXeBHIwJTKLRvCxC6aD5Mz5cbbvIOrEghyozjNbM6G718DvyxD5+vQ5c0df6IbJHIZ+AezHPdiOJJjC+tfMF3HdX+Ng/VT80LwIDAQAB',
                //  安卓
                2 => '',
            );

        }

    }

}