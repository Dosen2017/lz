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
use think\Config;

use app\recharge\helper;

require_once(dirname ( __FILE__ ).DIRECTORY_SEPARATOR."aliwappay_o/lib/alipay_submit.class.php");


class AliwapPayO extends Controller
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

        //获取的字段:my_order_num, user_name, channel_pkg_num, amount, bundle, time, sign

        $getPram = Request::instance()->get();

        //商户订单号，商户网站订单系统中唯一订单号，必填
        $checkParam['my_order_num'] = $getPram['my_order_num'];
        //订单名称，必填
//        $checkParam['subject'] = $getPram['WIDsubject'];
        //付款金额，必填
        $checkParam['amount'] = $getPram['amount'];
        //收银台页面上，商品展示的超链接，必填
//        $checkParam['show_url'] = $getPram['WIDshow_url'];
        //商品描述，可空
        $checkParam['channel_pkg_num'] = $getPram['channel_pkg_num'];

        $checkParam['bundle'] = $getPram['bundle'];
        $checkParam['user_name'] = $getPram['user_name'];
        $checkParam['time'] = $getPram['time'];
        $checkParam['sign'] = $getPram['sign'];

        !$this->apiHelper->checkParamIsNull($checkParam) && $this->apiHelper->echoJson(helper\ErrorOrders::ERROR_MISSED_PARAM);

        //2.验证与客户端的sign
        !$this->apiHelper->checkSign($checkParam) && $this->apiHelper->echoJson(helper\ErrorOrders::ERROR_SIGN);

        if (!empty($checkParam['my_order_num']) && trim($checkParam['my_order_num'])!=""){

            $alipay_config = include dirname ( __FILE__ ).DIRECTORY_SEPARATOR."aliwappay_o" . DIRECTORY_SEPARATOR ."alipay.config.php";

            $tzUrl = 'http://' . Config::get('lp_domain') . '/recharge/payPage/'. trim($checkParam['bundle']) .'.php';

            //构造要请求的参数数组，无需改动
            $parameter = array(
                "service"       => $alipay_config['service'],
                "partner"       => $alipay_config['partner'],
                "seller_id"  => $alipay_config['seller_id'],
                "payment_type"	=> $alipay_config['payment_type'],
                "notify_url"	=> $alipay_config['notify_url'],
                "return_url"	=> $tzUrl,
                "_input_charset"	=> trim(strtolower($alipay_config['input_charset'])),
                "out_trade_no"	=> $checkParam['my_order_num'],
                "subject"	=> "pay_" . $checkParam['my_order_num'],
                "total_fee"	=> $checkParam['amount'] / 100,   //获取订单号时，用分，支付宝传入时用元，所以这里 / 100
                "show_url"	=> $tzUrl,
                "app_pay"	=> "Y",//启用此参数能唤起钱包APP支付宝
                "body"	=> $checkParam['channel_pkg_num'],
                //其他业务参数根据在线开发文档，添加参数.文档地址:https://doc.open.alipay.com/doc2/detail.htm?spm=a219a.7629140.0.0.2Z6TSk&treeId=60&articleId=103693&docType=1
                //如"参数名"	=> "参数值"   注：上一个参数末尾需要“,”逗号。

            );
            $alipaySubmit = new \AlipaySubmit($alipay_config);
            $html_text = $alipaySubmit->buildRequestForm($parameter,"get", "确认");
            echo $html_text;exit;
        }

    }
}