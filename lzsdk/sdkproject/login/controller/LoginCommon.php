<?php
/**
 * Created by PhpStorm.
 * User: sds
 * Date: 2019/2/27
 * Time: 14:32
 */

namespace app\login\controller;

use think\Controller;
use think\Request;

/**
 * Class LoginCommon
 * @package app\login\controller
 *
 * SDK接入流程
 *
 * 说明：1.app_id参数切换函数 【_changeParam_xxxxx ($getParam)】
 *
 * 1.确认是否需要加入新的channelId
 *      1.1 如果是完全新的SDK，则需要添加
 *      1.2 如果是已有SDK（常用方法：搜索登陆验证链接除域名的后缀部分），新的专服，再需要添加，新的渠道号对应已有渠道号的helper文件，添加app_id对应的参数切换函数
 *          例如：44 => 'ChannelYouXunSDK',   // (mx - gjxy 悠讯SDK -- 战九霄)
 *               45 => 'ChannelYouXunSDK',   // (mx - gjxy 悠讯SDK -- 夜舞风云)
 *               46 => 'ChannelYouXunSDK',   // (mx - gjxy 悠讯SDK -- 倾世剑缘)
 *               47 => 'ChannelYouXunSDK',   // (mx - gjxy 悠讯SDK -- 飘渺剑神)
 *      1.3 如果是已有SDK（常用方法：搜索登陆验证链接除域名的后缀部分），已有混服/专服，不需要添加，直接在原有的helper文件，对应的app_id切换参数函数中加入参数（改变num）
 *          例如：39 => 'ChannelSanHao',   // (mx - gjxy 三号SDK)  中对应的app_id切换函数【_changeParam_10038 ($getParam)】，根据num 改变不同包的参数
 *
 * 2.确认是否需要在发行后台加入新的app_id参数
 *      1.1 专服，则必须添加
 *
 * 以下针对公司内游戏
 * 3.需要在对应游戏的登陆充值接口中加入接口文件，对应的CP游戏参数在发行后台由运营配置
 *      说明：3.1 梦想工作室的gjxy，wldf游戏的渠道号由ChlLogin.php中的定义。
 *               例如：91 => 'ChannelCocoWanExt',   // (mx - gjxy 悠迅-2组-仙域战记（混太古武神传))
 *                    92 => 'ChannelCocoWanExt',   // (mx - gjxy 悠迅-2组-仙域幻想（混太古武神传）)
 *                    93 => 'ChannelCocoWanExt',   // (mx - gjxy 悠迅-2组-飞剑问道（混太古武神传）)
 *               其中91,92,93就完全对应ChlLogin.php文件中的渠道号
 *
 *           3.2 点聚工作室的fxwd,jnqk,qyz游戏的渠道号由作为CP方服务端的我们重新定义
 *               例如：5004 => 'ChannelCocoWanBingxue',  //武神变  冰雪 IOS
 *                    5005 => 'ChannelCocoWanBingxue',  //武神变  冰雪  安卓
 *                    500402 => 'ChannelCocoWanBingxue',  //武神变  冰雪 IOS
 *                    5006 => 'ChannelCocoWanLeTang',  //武神变  乐糖  IOS
 *                    500602 => 'ChannelCocoWanLeTang',  //武神变  乐糖  IOS
 *                    500403 => 'ChannelCocoWanBingxue',  //武神变  冰雪 IOS - 神武修真
 *               以上渠道号完全重新定义
 *
 */
class LoginCommon extends Controller
{
    const SERVER_LOGIN_KEY = 'wXjJKHB#qwxda.z#H)Acplat';

    public $helperHandle;

    public $isTest = false;


    protected function _getParam($request, $param)
    {

        foreach($param as $k => $v) {
            $data[$v] = $request->post($v);
        }

        return $data;
    }

    public function getAccount()
    {
        $request = Request::instance();
        $getParam = $request->post(); // 获取所有的get变量（经过过滤的数组）
        $channelId = getChannelNumByChannelPkgNum($getParam['channel_pkg_num']);  //渠道ID,对应helper处理类
        // 1.检查该渠道是否设置了相应的方法
        if(!isset($this->channel[$channelId]))
            echoJson(0, 'inner error1');

        // 2.检查是否有对应的渠道的验证类
        $helper = explode('\\' , __NAMESPACE__)[0] . '\\' . explode('\\' , __NAMESPACE__)[1] . '\helper\\' . $this->channel[$channelId];

        $this->helperHandle = new $helper;
        if(!class_exists($helper))
            echoJson(0, 'inner error2');
        $param = $this->helperHandle->getClientParam();

        $signData = $this->_getParam($request, $param);

        if ( $this->isTest ) {
            $signData['test'] = true;
        }

        // 3. 检查配置(出去channelId后的)
        foreach($signData as $k => $v) {
            if( empty($v) )
                echoJson(0, $k . ' is empty');
        }

        // 4.调用渠道对应的类的方法去验证登陆
        $this->helperHandle->checkToken( $signData);

    }

}
