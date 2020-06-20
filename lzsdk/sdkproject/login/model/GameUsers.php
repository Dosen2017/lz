<?php
/**
 * Created by PhpStorm.
 * User: sds
 * Date: 2020/4/3
 * Time: 12:00
 */

namespace app\login\model;

use app\login\helper\Login;
use think\Model;

class GameUsers extends Model
{
    protected $createTime = '';//防止create_time在存储时，自动转换
    public function addGameUser($userInfo, $reqData, $saveData)
    {
        $infoData = json_decode($reqData['info'], true);
        $gameId = getGameNumByChannelPkgNum($reqData['channel_pkg_num']);
        $channelId = getChannelNumByChannelPkgNum($reqData['channel_pkg_num']);

        $gameUsers['user_num'] = $userInfo['user_num'];
        $gameUsers['user_name'] = $userInfo['user_name'];
        $gameUsers['game_num'] = $gameId;
        $gameUsers['channel_pkg_num'] = $reqData['channel_pkg_num'];
        $gameUsers['channel_num'] = $channelId;

        $channelPkgModel = new ChannelPkg();
        $osType = $channelPkgModel->getAppInfoByChannelPkgNum($reqData['channel_pkg_num'])['os_type'];
        $gameUsers['os_type'] = $osType;

        $gameUsers['device_num'] = $infoData['device_id'];
        $gameUsers['device_num_md5'] = md5($infoData['device_id']);
        $gameUsers['device_model'] = $infoData['device_model'];
        $gameUsers['mac'] = $infoData['mac'];

        //saveData字段
        $gameUsers['create_ip'] = $saveData['reg_ip'];
        $gameUsers['create_time'] = $saveData['create_time'];
        $gameUsers['create_d'] = (int)date('Ymd', $saveData['create_time']);//这样存储可以方便数据统计

        $gameUsers['last_ip'] = $saveData['last_ip'];
        $gameUsers['last_time'] = $saveData['last_time'];

        $gameUsers['province'] = $saveData['province'];
        $gameUsers['city'] = $saveData['city'];

        if (!isset($infoData['b_id'])) {
            $gameUsers['pack_num'] = $infoData['bundle'] . '_' . $infoData['version'];
            $gameUsers['b_id'] = $gameUsers['pack_num'];
        } else {
            $gameUsers['pack_num'] = $infoData['bundle'] . '_' . $infoData['version'] . '_' . $infoData['b_id'];
            $gameUsers['b_id'] = $infoData['b_id'];
        }

        $gameUsers['ads_id'] = '';
        $this->save($gameUsers);  //先保存，不能因为给玩家打标签，导致玩家注册不成功
        $gameUserNum = $this->game_user_num;

        //打标签，1.落地页广告ID,2.通用API广告ID,3.腾讯API广告ID
        $this->addTagToGameUser($gameUsers, $gameUserNum);

    }

    //给游戏账号玩家打标签
    protected function addTagToGameUser($gameUsers, $gameUserNum)
    {
        $rangeTime = time() - 86400;
        //1.查找落地页日志
        $ads_id = $this->getAdsIdByUserPages($gameUsers, $rangeTime);
        //2.落地页日志中找不到数据时，再去通用API日志里去查找
        if ( empty($ads_id) ) {
            $ads_id = $this->getAdsIdByCommonAdlogs($gameUsers, $rangeTime);
        }
        //3.腾讯API日志查找


        if (!empty($ads_id)) {
            //获取到广告不为空的情况下，再修改当前账号的广告ID
            $updGameUsers['ads_id'] = $ads_id;
            $this->where(['game_user_num' =>$gameUserNum ])->update($updGameUsers);
        }
    }

    public function getAdsIdByUserPages($gameUsers, $rangeTime )
    {
        //1.落地页模块   在这里打广告标签【可能需要区分是否需要打标签】
        $pageLogsModel = new PageLogs();
        $where['ip'] = $gameUsers['create_ip'];
        $where['add_time'] = ['>=', $rangeTime];
        $where['channel_id'] = $gameUsers['channel_pkg_num'];
        $plInfo = $pageLogsModel->field('ads_id')->where($where)->order('add_time desc')->find();
//file_put_contents("/tmp/gameUsers.sql", date("Y-m-d H:i:s") . "---" . $pageLogsModel->getLastSql() . "\n", FILE_APPEND);

        return $plInfo['ads_id'] ?: '';
    }

    public function getAdsIdByCommonAdlogs($gameUsers, $rangeTime)
    {
        //2.通用API模块
        $commonAdlogsModel = new CommonAdlogs();

        //这里加入条件callback_result，是因为，对于已经打过玩家标签的日志记录，不再重复打
        $whereStr = "channel_id = " . $gameUsers['channel_pkg_num'] . " and add_time >= " . $rangeTime . " and callback_result = ''"
//            .  " and (ip = '" . $gameUsers['create_ip'] . "' or device_id = '" . $gameUsers['device_num'] . "' or mac = '" . $gameUsers['mac'] . "')";
            .  " and (ip = '" . $gameUsers['create_ip'] . "' or device_id = '" . $gameUsers['device_num'] . "')";  //暂时去掉mac，mac可能重复，比如全部是02:00:00:00:00:00
        $calInfo = $commonAdlogsModel->field('id, ads_id, callback_url, callback_times')->where($whereStr)->order('add_time desc')->find();
// file_put_contents("/tmp/gameUsers2.sql", date("Y-m-d H:i:s") . "---" . $commonAdlogsModel->getLastSql() . "\n", FILE_APPEND);

        //这里还需要上报广告平台
        //判断callbackurl是否存在
        if (empty($calInfo['callback_url'])) {
            return $calInfo['ads_id'] ?: '';
        }
        //判断链接是否可用
        $fHandler = fopen($calInfo['callback_url'], 'r');
        if ( $fHandler === false) {
            return $calInfo['ads_id'] ?: '';
        }
        fclose($fHandler);

        //1.上报
        $isHttps = false;
        //检测设置的回调地址是http还是https
        if ( 'https' === substr($calInfo['callback_url'], 0, 5) ) {
            $isHttps = true;
        }

        //设置请求的timeout,减少等待时间
        $callbackResult = httpRequest($calInfo['callback_url'] , null, $isHttps, 'get', 1, 3);
        //2.修改callback*数据
        $updateData['callback_time'] = time();
        $updateData['callback_times'] = $calInfo['callback_times'] + 1;
        $updateData['callback_result'] = $callbackResult;

        $commonAdlogsModel->where(['id' => $calInfo['id']])->update($updateData);

        return $calInfo['ads_id'] ?: '';
    }

    public function addGameUserNoSaveData($userInfo, $reqData)
    {
        $time = time();
        $ipTimeData['create_time'] = time();
        $ipTimeData['reg_ip'] = get_client_ip();

        $ipTimeData['last_time'] = $time;
        $ipTimeData['last_ip'] = $ipTimeData['reg_ip'];

        //获取省份信息
        $helper = new Login();
        $areaData = $helper->getProvinceAndCity($ipTimeData['reg_ip']);
        $ipTimeData['city'] = $areaData['city'];
        $ipTimeData['province'] = $areaData['province'];

        $this->addGameUser($userInfo, $reqData, $ipTimeData);

    }
}
