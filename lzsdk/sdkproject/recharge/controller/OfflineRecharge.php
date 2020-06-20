<?php
/**
 * Created by PhpStorm.
 * User: sds
 * Date: 2020/4/8
 * Time: 10:49
 */

namespace app\recharge\controller;
use app\recharge\model\OfflinePay;
use think\Controller;

require_once dirname ( __FILE__ ).DIRECTORY_SEPARATOR. "php_sdk_v3.0.10/lib/WxPay.Api.php";
require_once dirname ( __FILE__ ).DIRECTORY_SEPARATOR. "php_sdk_v3.0.10/lib/WxPay.Notify.php";
require_once dirname ( __FILE__ ).DIRECTORY_SEPARATOR. "php_sdk_v3.0.10/example/WxPay.Config.php";
require_once dirname ( __FILE__ ).DIRECTORY_SEPARATOR. "php_sdk_v3.0.10/example/native_notify.php";


class OfflineRecharge extends Controller
{

    protected $model;

    public function index()
    {
        $this->model = new OfflinePay();

        $data = $this->_xml_to_data(file_get_contents("php://input"));

//        $outTradeNo = 20200609113526324;
//        $this->model->where(['order_num' => $outTradeNo])->update(['status' => 2, 'cb_time' => time()]);

        $config = new \WxPayConfig();
        $notify = new WxPayHandler;
        $resxml = $notify->Handle($config, true);

        $resData = $this->_xml_to_data($resxml);
        if ("SUCCESS" == $resData['return_code']) {
            $this->model->where(['order_num' => $data['out_trade_no']])->update(['status' => 3, 'cb_time' => time()]);
        }

        echo $resxml;
//        file_put_contents("/tmp/offlineRecharge3.txt", date('Y-m-d H:i:s') . "_" . $resxml . "\n", FILE_APPEND);

        exit;
    }

    public function _xml_to_data($xml){
        if(!$xml){
            return false;
        }
        //将XML转为array
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $data;
    }

}

class WxPayHandler extends \NativeNotifyCallBack
{
    public function NotifyProcess($objData, $config, &$msg)
    {
        //TODO 用户基础该类之后需要重写该方法，成功的时候返回true，失败返回false
        //不做处理
        return true;
    }
}