<?php

namespace app\admin\controller\delivery;

use think\Request;
use think\Session;
use app\common\controller\Backend;

/**
 * 公共模板
 *
 * @icon fa fa-circle-o
 */
class PageTemplates extends Backend
{
    
    /**
     * PageTemplates模型对象
     * @var \app\admin\model\delivery\PageTemplates
     */
    protected $model = null;
    protected $userTemplatesmodel = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\delivery\PageTemplates;
        $this->userTemplatesmodel = new \app\admin\model\delivery\UserTemplates;
        $this->view->assign("redirectTypeList", $this->userTemplatesmodel->getRedirectTypeList());
    }
    
    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */

    //加入对当前表数据的渲染方法
    public function indexMiddle(&$list) {

        foreach ($list as $k => $v) {
            $list[$k]['preview'] = Request::instance()->domain() . "/IAd/TestPageTemplate/" . $v['template_id'];
        }

    }

    /**
     * 编辑
     */
    public function user_edit($ids = null)
    {
//        $this->model = new \app\admin\model\delivery\UserTemplates;  //临时切换model

        $row = $this->model->get($ids);
//        echo $row['args_config'];exit;
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            if (!in_array($row[$this->dataLimitField], $adminIds)) {
                $this->error(__('You have no permission'));
            }
        }
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");

            if ($params) {

                $userTempplates['admin_id'] = Session::get("admin")['id'];
                $userTempplates['template_id'] = $params['template_id'];
                $userTempplates['page_name'] = $params['page_name'];
                $userTempplates['title'] = $params['title'];
                $userTempplates['ios_url'] = $params['ios_url'];
                $userTempplates['android_url'] = $params['android_url'];
                $userTempplates['ios_channelid'] = $params['ios_channelid'];
                $userTempplates['android_channelid'] = $params['android_channelid'];
                $userTempplates['redirect_type'] = $params['redirect_type'];
                $userTempplates['query_device'] = $params['query_device'];
                $userTempplates['query_ip'] = $params['query_ip'];
                $userTempplates['query_callback'] = $params['query_callback'];

                $confParams = [];
                foreach ($params as $k => $v) {
                    if (strpos( $k, "conf_") !== false) {
                        $confParams[substr($k, 5)] = $v;
                    }
                }

                $userTempplates['ext_config'] = json_encode($confParams,JSON_UNESCAPED_UNICODE);

                if ($this->userTemplatesmodel->save($userTempplates)) {

                    $lastId = $this->userTemplatesmodel->getLastInsID();
                    //  在IAd/TestUserTemplate/$lastId/index.php  生成来自于模板IAd/TestPageTemplate/{$params['template_id']}/index.php
                    $sourcePath = ROOT_PATH . "public" . DIRECTORY_SEPARATOR . "IAd" . DIRECTORY_SEPARATOR . "TestPageTemplate" . DIRECTORY_SEPARATOR . $params['template_id'] . DIRECTORY_SEPARATOR . "index.php";
                    $destDir = ROOT_PATH . "public" . DIRECTORY_SEPARATOR . "IAd" . DIRECTORY_SEPARATOR . "TestUserTemplate" . DIRECTORY_SEPARATOR . $lastId;
                    if (!is_dir($destDir)) {
                        mkdir($destDir, 0755, true);
                    }
                    $destPath = $destDir . DIRECTORY_SEPARATOR . "index.php";
                    if (!file_exists($destPath)) {
                        $myfile = fopen($destPath, "w") or die("Unable to open file!");
                        fwrite($myfile, file_get_contents($sourcePath));
                        fclose($myfile);
                    }

                    $this->success("save success", url('/delivery/user_templates'), '',1);
                }
                $this->error(__('save data fail!'));
            }

        }

        $extConfig = $row['args_config'];

        $this->view->assign("ext_config", json_decode($extConfig, true));

        $this->view->assign("row", $row);



        return $this->view->fetch();
    }

    public function getKVList()
    {
        $list = $this->field("template_id, name")->order('id', 'desc')->select();
        foreach ($list as $k => $v) {
            $ret[$v['template_id']] = $v['name'];
        }

        return $ret;
    }

    public function searchlist()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        list($where, $sort, $order, $offset, $limit) = $this->buildparams();

        $list = $this->model
            ->where($where)
            ->order($sort, $order)
            ->limit($offset, $limit)
            ->select();

        $list = collection($list)->toArray();

        foreach ($list as $k => $v) {
            $ret[$v['template_id']] = $v['name'];
        }

        return json($ret);

    }

}
