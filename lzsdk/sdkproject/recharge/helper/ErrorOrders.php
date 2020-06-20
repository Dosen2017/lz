<?php
/**
 * Created by PhpStorm.
 * User: sds
 * Date: 2019/3/1
 * Time: 10:55
 */

namespace app\recharge\helper;

use think\Helper;

class ErrorOrders extends Helper
{
    //公共的错误码
    const ERROR_MISSED_PARAM = 1; // 参数缺失
    const ERROR_SIGN = 2; // 身份验证失败
    const ERROR_ORDER_NUM = 3; // 订单未找到
    const ERROR_REPEAT = 4; // 重复请求
    const ERROR_APP_ID_PRODUCT_ID_NOT_CONFIGURE = 5; //APP_ID对应的产品ID没有配置
    const ERROR_PRODUCT_ID_NOT_EXIST = 6; //产品ID不在该游戏产品ID中
    const ERROR_PRODUCT_ID_NOT_SAME = 7; //下单时产品ID和请求验证receipt中的产品ID不一致
    const ERROR_MONEY_NOT_EXIST = 8;
    const ERROR_MONEY_NOT_SAME = 9; //下单时的充值金额和验证后返回的金额不一致
    const ERROR_CALLBACK_RETURN = 10;  //CP端回调失败
    const ERROR_WX_ORDER_CURL = 11;  //微信预下单CURL请求失败
    const ERROR_WX_ORDER_OPEN_MWEBURL = 12; //微信预下单后，打开MWEB_URL失败
    const ERROR_WX_ORDER = 13;   //微信预下单失败
    const REPEAT_PRODUCE_ORDER_ERROR = 14;   //同一个订单请求重复生成订单
    const REQURST_IS_TIMEOUT_ERROR = 15;   //获取订单号的请求已经超时

    const ERROR_ORDER_CHANNEL_PKG_NUM_IS_NOT_MATCH_GAME_NUM = 16;   //获取订单号时，游戏ID和渠道包ID不匹配

    const ERROR_ALIPAY_STATUS = 20001;  //支付宝回调的订单状态不对
    const ERROR_ALIPAY_SELLER_ID = 20002;  //卖家用户号错误
    const ERROR_ALIPAY_APP_ID = 20003;  //应用ID错误


    const REQUEST_APPLE_RETURN_EMPTY = 401;  //支付验证返回为空
    const RECEIPT_IS_EXIST = 402;  //receipt重复请求
}