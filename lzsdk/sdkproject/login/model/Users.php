<?php
/**
 * Created by PhpStorm.
 * User: sds
 * Date: 2020/4/3
 * Time: 12:00
 */

namespace app\login\model;

use app\login\helper\RedisH;
use think\Model;

class Users extends Model
{
    //普通账号首字符是字母，所以和手机号码是不会重复的，这里可以用同一个前缀
    const USER_NAME_KEY = "login:Model:Users:checkAccountNameIsExist:";

    public function checkAccountNameIsExist($userName) {

        $return = [];

        $uniKey = self::USER_NAME_KEY . $userName;
        $redisConn = new RedisH();
        if ($userInfoJson = $redisConn->get($uniKey)) {
            $return = json_decode($userInfoJson, true);
            if (!$return['user_num']) {
                return $return;
            }
        }
        $ret = $this->field('user_num, user_name, password, salt,phone_number, id_card, lock_flag')
            ->where(["user_name" => $userName])->find();
//            ->where("user_name = '" . $userName . "'")->find();
        if ( empty($ret['user_num']) || !empty($ret['lock_flag'])) {
            return false;
        }

        //如果玩家存在就给玩家返回账号id和账号名称
        $return['user_num'] = $ret['user_num'];
        $return['user_name'] = $ret['user_name'];
        $return['password'] = $ret['password'];
        $return['salt'] = $ret['salt'];
        $return['phone_number'] = $ret['phone_number'];
        $return['id_card'] = $ret['id_card'];

        $redisConn->set($uniKey, json_encode($return), 86400);
        return $return;
    }

    public function checkPhoneNumberIsExist($phoneNumber) {

        $return = [];

        $uniKey = self::USER_NAME_KEY . $phoneNumber;
        $redisConn = new RedisH();
        if ($userInfoJson = $redisConn->get($uniKey)) {
            $return = json_decode($userInfoJson, true);
            if (!$return['user_num']) {
                return $return;
            }
        }

        $ret = $this->field('user_num, user_name, password, salt,phone_number, id_card, lock_flag')
            ->where(["phone_number" => $phoneNumber])->find();
        if ( empty($ret['user_num']) || !empty($ret['lock_flag'])) {
            return false;
        }

        //如果玩家存在就给玩家返回账号id和账号名称
        $return['user_num'] = $ret['user_num'];
        $return['user_name'] = $ret['user_name'];
        $return['password'] = $ret['password'];
        $return['salt'] = $ret['salt'];
        $return['phone_number'] = $ret['phone_number'];
        $return['id_card'] = $ret['id_card'];

        $redisConn->set($uniKey, json_encode($return), 86400);

        return $return;
    }

    //根据账号名修改密码
    public function upPasswordByAccountname($userName, $newPassword, $salt) {
        $redisConn = new RedisH();
        $redisConn->del(self::USER_NAME_KEY . $userName);
        return $this->where(["user_name" => $userName ])->update(['password' => $newPassword, 'salt' => $salt]);
    }

    //根据手机号修改密码
    public function upPasswordByPhoneNum($phoneNum, $newPassword, $salt) {
        $redisConn = new RedisH();
        $redisConn->del(self::USER_NAME_KEY . $phoneNum);
        return $this->where(["phone_number" => $phoneNum ])->update(['password' => $newPassword, 'salt' => $salt]);
    }

    //更新实名信息
    public function upIdentityByUserNum($userNum, $idCard, $fullName) {
        //清除缓存
        $this->clearCacheByUserNum($userNum);

        return $this->where(["user_num" => $userNum ])->update(['id_card' => $idCard, 'full_name' => $fullName]);
    }

    public function clearCacheByUserNum ($userNum) {
        $userData = $this->field('user_name, phone_number')->where(["user_num" => $userNum ])->find();
        //更新实名信息时，把根据账号和手机号码的缓存都清掉
        $redisConn = new RedisH();
        $redisConn->del(self::USER_NAME_KEY . $userData['user_name']);
        $redisConn->del(self::USER_NAME_KEY . $userData['phone_number']);
    }

    //查看电话号码是否存在
    public function checkPhoneNumIsExist($tel) {
        if ( !$this->field('phone_number')->where(["phone_number" => $tel ])->find() ) {
            return false;
        }
        return true;
    }

    public function addUser($saveData, &$userId) {
        //可以做其它处理，比如加入缓存
        $redisConn = new RedisH();

        $uniKey = self::USER_NAME_KEY . $saveData['user_name'];
        if ( $saveData['phone_number'] ) {
            $uniKey = self::USER_NAME_KEY . $saveData['phone_number'];
        }

        $this->save($saveData);
        $userId = $this->user_num;

        $return['user_num'] = $userId;
        $return['user_name'] = $saveData['user_name'];
        $return['password'] = $saveData['password'];
        $return['salt'] = $saveData['salt'];
        $return['phone_number'] = $saveData['phone_number'] ?: '';
        $return['id_card'] = $saveData['id_card'] ?: '';

        if (!empty($userId)) {
            $redisConn->set($uniKey, json_encode($return), 86400);
        }


    }
}
