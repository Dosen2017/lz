<?php

namespace app\admin\controller\gamemanage;

use app\common\controller\Backend;
use think\Config;
/**
 * 玩家账号管理
 *
 * @icon fa fa-users
 */
class Users extends Backend
{
    
    /**
     * Users模型对象
     * @var \app\admin\model\gamemanage\Users
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\gamemanage\Users;

    }
    
    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */

    public function detail($ids)
    {
        $row = $this->model->get(['user_num' => $ids]);
        if (!$row) {
            $this->error(__('No Results were found') . $ids);
        }
        if ($this->request->isAjax()) {
            $passWd = $this->request->post('pwd');
            if (!$this->checkPasswordIsOK($passWd)) {
                $this->error("密码格式不正确！6~20位数字或字母");
            }
            $upData['password'] = $this->produceMd5Password($passWd, $salt);
            $upData['salt'] = $salt;

            //----------------清除玩家缓存--------------------------
            $time = time();
            $data['user_num'] =  $ids;
            $data['time'] = $time;
            $data['sign'] = md5($data['user_num'] . $data['time'] . Config::get('clear_cache_sign_key'));
            //清除登录的缓存
            $loginUrl = "http://" . Config::get('api_domain') . "/login/ClearCache/user";
            $retLogin = file_get_contents($loginUrl . "?" . http_build_query($data));

            if ($retLogin !== "SUCCESS") {
                $this->error(__('清除缓存失败！') );
            }
            //-----------------清除玩家缓存---------------------------

            if (!$this->model->where(['user_num' => $ids])->update($upData)) {
                $this->error('修改密码出错！');
            }

            $this->success("修改密码成功！");
        }
        $this->view->assign("row", $row->toArray());
        $this->view->assign("ids", $ids);
        return $this->view->fetch();
    }

    public function lock($ids)
    {
        $row = $this->model->get(['user_num' => $ids]);
        if (!$row) {
            $this->error(__('No Results were found') . $ids);
        }
        if ($this->request->isAjax()) {
            $lockflag = $this->request->post('lock');
            if (!$this->checkLockFlagIsOK($lockflag)) {
                $this->error("锁定标识不正确！1~10位数字");
            }
            $upData['lock_flag'] = $lockflag;

            //----------------清除玩家缓存--------------------------
            $time = time();
            $data['user_num'] =  $ids;
            $data['time'] = $time;
            $data['sign'] = md5($data['user_num'] . $data['time'] . Config::get('clear_cache_sign_key'));
            //清除登录的缓存
            $loginUrl = "http://" . Config::get('api_domain') . "/login/ClearCache/user";
            $retLogin = file_get_contents($loginUrl . "?" . http_build_query($data));

            if ($retLogin !== "SUCCESS") {
                $this->error(__('清除缓存失败！') );
            }
            //-----------------清除玩家缓存---------------------------

            if (!$this->model->where(['user_num' => $ids])->update($upData)) {
                $this->error('修改锁定标识出错！');
            }

            $this->success("修改锁定标识成功！");
        }
//        var_dump($row['lock_flag']);exit;

        $this->view->assign("row", $row->toArray());
        $this->view->assign("ids", $ids);
        return $this->view->fetch();
    }

    protected function produceMd5Password($password, &$salt)
    {
        $salt = $salt ?: random(8);

        return md5(md5($password) . $salt);
    }

    protected function checkPasswordIsOK($password)
    {
        if( empty($password) || !preg_match("/^[0-9a-z]{6,20}$/i", $password)) {
            return false;
        }
        return true;
    }

    protected function checkLockFlagIsOK($lockflag)
    {
        if(!preg_match("/^[0-9]{1,10}$/i", $lockflag)) {
            return false;
        }
        return true;
    }

}
