<?php
/**
 * Created by PhpStorm.
 * User: sds
 * Date: 2020/4/8
 * Time: 10:49
 */

namespace app\recharge\controller;


use think\Controller;
use think\Request;
use think\Config;

use app\recharge\model\OfflinePay;

require_once dirname ( __FILE__ ).DIRECTORY_SEPARATOR. "php_sdk_v3.0.10/lib/WxPay.Api.php";
require_once dirname ( __FILE__ ).DIRECTORY_SEPARATOR. "php_sdk_v3.0.10/lib/WxPay.Data.php";
require_once dirname ( __FILE__ ).DIRECTORY_SEPARATOR. "php_sdk_v3.0.10/example/WxPay.Config.php";
require_once dirname ( __FILE__ ).DIRECTORY_SEPARATOR. "php_sdk_v3.0.10/example/log.php";
require_once dirname ( __FILE__ ).DIRECTORY_SEPARATOR. "php_sdk_v3.0.10/example/phpqrcode/phpqrcode.php";
require_once dirname ( __FILE__ ).DIRECTORY_SEPARATOR. "php_sdk_v3.0.10/example/WxPay.JsApiPay.php";

class QrCode extends Controller
{
    protected $key;

    protected function _initialize()
    {
        $this->key = Config::get('cache_sign_key');
    }

    //供后台人工提供
    public function index()
    {
//        header("Content-type: image/png");

        $getParam = Request::instance()->get();

        $data['order_num'] = $getParam['order_num'];
        $data['money'] = $getParam['money'];     //分
        $data['time'] = $getParam['time'];
        $data['sign'] = $getParam['sign'];

        $checkSign = md5($data['order_num'] . $data['money']  . $data['time'] . $this->key );

        if ($data['sign'] != $checkSign ) {
            echo "FAIL"; exit;
        }

        $input = new \WxPayUnifiedOrder();
        $input->SetBody("pay");
        $input->SetAttach("pay");

//        $input->SetOut_trade_no("sdkphp123456789".date("YmdHis"));
        $input->SetOut_trade_no($data['order_num']);
//        $input->SetTotal_fee("1");
        $input->SetTotal_fee($data['money']);

        $input->SetTime_start(date("YmdHis"));
        $input->SetTime_expire(date("YmdHis", time() + 60 * 60));  //默认有效期1小时
        $input->SetGoods_tag("pay");
        $input->SetNotify_url("http://" . Config::get('lp_domain') . "/recharge/OfflineRecharge");
        $input->SetTrade_type("NATIVE");
        $input->SetProduct_id("product_" . date('YmdHis'));

        $result = $this->GetPayUrl($input);

        if (!$result) {
            echo "FAIL2"; exit;
        }

        $url = $result["code_url"];

        echo $url;exit;

//        ob_clean();
//        //生成二维码
//        if(substr($url, 0, 6) == "weixin"){
//            echo \QRcode::png($url);exit;
//        }else{
//            header('HTTP/1.1 404 Not Found');
//        }

    }

    //供网页版支付使用
    public function wxweb()
    {
        header("Content-type: image/png");

        $postParam = Request::instance()->post();

        $data['order_num'] = date('YmdHis') . random(3, '123456789');
        $data['p_type'] = 1;
        $data['money'] = $postParam['money'];     //元
//        $data['code_url'] = $postParam['code_url'];
        $data['c_time'] = time();
        $data['status'] = 1;
        $data['money'] = $postParam['money'];


        $input = new \WxPayUnifiedOrder();
        $input->SetBody("pay");
        $input->SetAttach("pay");

//        $input->SetOut_trade_no("sdkphp123456789".date("YmdHis"));
        $input->SetOut_trade_no($data['order_num']);
//        $input->SetTotal_fee("1");
        $input->SetTotal_fee($data['money'] * 100);

        $input->SetTime_start(date("YmdHis"));
        $input->SetTime_expire(date("YmdHis", time() + 60 * 60));  //默认有效期1小时
        $input->SetGoods_tag("pay");
        $input->SetNotify_url("http://" . Config::get('lp_domain') . "/recharge/OfflineRecharge");
        $input->SetTrade_type("NATIVE");
        $input->SetProduct_id("product_" . date('YmdHis'));

        $result = $this->GetPayUrl($input);
//echo json_encode($result);exit;
        if (!$result || $result['return_code'] != "SUCCESS") {
            echo "FAIL2"; exit;
        }

        $url = $result["code_url"];

        $data['code_url'] = $url;

        $this->model = new OfflinePay();
        if (!$this->model->save($data)) {
            echo "FAIL3"; exit;
        }

//        ob_clean();
//        //生成二维码
//        if(substr($url, 0, 6) == "weixin"){
//            echo \QRcode::png($url);exit;
//        }else{
//            header('HTTP/1.1 404 Not Found');
//        }

        echo $url;exit;


    }

    //微信的jsapi支付
    public function jsweb()
    {
        //①、获取用户openid
        try{

            $tools = new \JsApiPay();
            $openId = $tools->GetOpenid();

            //②、统一下单
            $input = new \WxPayUnifiedOrder();
            $input->SetBody("test");
            $input->SetAttach("test");
            $input->SetOut_trade_no("sdkphp".date("YmdHis"));
            $input->SetTotal_fee("1");
            $input->SetTime_start(date("YmdHis"));
            $input->SetTime_expire(date("YmdHis", time() + 600));
            $input->SetGoods_tag("test");
            $input->SetNotify_url("http://paysdk.weixin.qq.com/notify.php");
            $input->SetTrade_type("JSAPI");
            $input->SetOpenid($openId);
            $config = new \WxPayConfig();
            $order = \WxPayApi::unifiedOrder($config, $input);
            echo '<font color="#f00"><b>统一下单支付单信息</b></font><br/>';
            printf_info($order);
            $jsApiParameters = $tools->GetJsApiParameters($order);

            //获取共享收货地址js函数参数
            $editAddress = $tools->GetEditAddressParameters();
        } catch(\Exception $e) {
//           Log::ERROR(json_encode($e));
        }
    }

    /**
     *
     * 生成直接支付url，支付url有效期为2小时,模式二
     * @param UnifiedOrderInput $input
     */
    public function GetPayUrl($input)
    {
//        var_dump($input);

        if($input->GetTrade_type() == "NATIVE")
        {
            try{
                $config = new \WxPayConfig();
                $result = \WxPayApi::unifiedOrder($config, $input);
                return $result;
            } catch(\Exception $e) {
//                \Log::ERROR(json_encode($e));
            }
        }
        return false;
    }


}