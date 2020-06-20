<?php
/**
 * Created by sudesheng.
 * User: 2066815257@qq.com
 * Date: 20-4-9
 * Time: 下午2:46
 *
 * 支付宝充值下单接口
 *
 */
namespace app\recharge\controller;

use think\Controller;
use think\Request;

use app\recharge\helper;

require_once dirname ( __FILE__ ).DIRECTORY_SEPARATOR.'aliwappay/wappay/service/AlipayTradeService.php';
require_once dirname ( __FILE__ ).DIRECTORY_SEPARATOR.'aliwappay/wappay/buildermodel/AlipayTradeWapPayContentBuilder.php';

class AliwapPay extends Controller
{

    protected $apiHelper;

    public function __construct()
    {
        parent::__construct();
        $this->apiHelper = new helper\Api();
    }

    /**
     * @return 返回支付页面
     */
    public function getRepaHtml()
    {
        $getPram = Request::instance()->get();

        // 1.验证参数是否为空
        $checkParam['out_trade_no'] = $getPram['out_trade_no'];
        $checkParam['subject'] = $getPram['subject'];
        $checkParam['total_amount'] = $getPram['total_amount'];  //客户端传的分，在下单时，转换成元
        $checkParam['channel_pkg_num'] = $getPram['channel_pkg_num'];
        $checkParam['sign'] = $getPram['sign'];

//        echo json_encode($checkParam);exit;

        !$this->apiHelper->checkParamIsNull($checkParam) && $this->apiHelper->echoJson(helper\ErrorOrders::ERROR_MISSED_PARAM);

        //2.验证与客户端的sign
//        !$this->apiHelper->checkSign($getPram) && $this->apiHelper->echoJson(helper\ErrorOrders::ERROR_SIGN);

        if (!empty($getPram['out_trade_no']) && trim($getPram['out_trade_no'])!=""){
            //商户订单号，商户网站订单系统中唯一订单号，必填
            $out_trade_no = $getPram['out_trade_no'];

            //订单名称，必填
            $subject = $getPram['subject'];

            //付款金额，必填
            $total_amount = $getPram['total_amount'] / 100;  //订单总金额，单位为元，精确到小数点后两位，取值范围[0.01,100000000]

            //商品描述，可空  ，【在这里为了方便回调的操作，传入游戏的应用channel_num，改为必传】
            $body = $getPram['channel_pkg_num'];

            //超时时间
            $timeout_express="1m";

            $aliwapPayconfig = include dirname ( __FILE__ ).DIRECTORY_SEPARATOR.'aliwappay/aliconfig.php';

            $payRequestBuilder = new \AlipayTradeWapPayContentBuilder();
            $payRequestBuilder->setBody($body);
            $payRequestBuilder->setSubject($subject);
            $payRequestBuilder->setOutTradeNo($out_trade_no);
            $payRequestBuilder->setTotalAmount($total_amount);
            $payRequestBuilder->setTimeExpress($timeout_express);

            $payRequestBuilder->setQuitUrl($aliwapPayconfig['quit_url']);

//            print "<pre>";
//            print_r($aliwapPayconfig);
//            print "</pre>";
//            exit;

            $payResponse = new \AlipayTradeService($aliwapPayconfig);
            $result=$payResponse->wapPay($payRequestBuilder,$aliwapPayconfig['return_url'],$aliwapPayconfig['notify_url']);
//var_dump($result);exit;

            echo $result;exit;

//            return ;
        }

    }
}