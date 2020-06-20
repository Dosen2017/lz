<?php
/**
 * Created by PhpStorm.
 * User: sds
 * Date: 2020/4/3
 * Time: 12:00
 */

namespace app\login\model;

use think\Model;

use app\login\helper\Login;

class UserLogs extends Model
{
    public function addUserLogs($return, $reqData)
    {
        $time = time();

        $infoData = json_decode($reqData['info'], true);

        $gameId = getGameNumByChannelPkgNum($reqData['channel_pkg_num']);
        $channelId = getChannelNumByChannelPkgNum($reqData['channel_pkg_num']);

        $saveData['user_num'] = $return['user_num'];
        $saveData['user_name'] = $return['user_name'];
        $saveData['game_num'] = $gameId;
        $saveData['channel_pkg_num'] = $reqData['channel_pkg_num'];
        $saveData['channel_num'] = $channelId;

        $saveData['pack_num'] = $infoData['bundle'];

        //添加b_id和ads_id字段,这里直接查，比较清晰。。从前面传，虽然少了一个查询数据的步骤，但是太乱了。
        $gameUserModel = new GameUsers();
        $gameUserInfo = $gameUserModel->field('b_id, ads_id')->where(['user_num' => $saveData['user_num']])->find();
        $saveData['b_id'] = $gameUserInfo['b_id'];
        $saveData['ads_id'] = $gameUserInfo['ads_id'];

        $saveData['ip'] = get_client_ip();

        $saveData['device_num'] = $infoData['device_id'];
        $saveData['device_num_md5'] = md5($infoData['device_id']);
        $saveData['device_model'] = $infoData['device_model'];
        $saveData['mac'] = $infoData['mac'];

        $channelPkgModel = new ChannelPkg();
        $osType = $channelPkgModel->getAppInfoByChannelPkgNum($reqData['channel_pkg_num'])['os_type'];
        $saveData['os_type'] = $osType;

        $saveData['os_version'] =  $infoData['version'];
        $saveData['net_type'] =  '';

        //获取省份信息
        $helper = new Login();
        $areaData = $helper->getProvinceAndCity($saveData['ip']);
        $saveData['country'] = '';
        $saveData['province'] = $areaData['province'];
        $saveData['city'] = $areaData['city'];

        $saveData['opt_time'] = $time;
        $saveData['opt_d'] = date('Ymd', $time);

        $this->save($saveData);
    }
}
