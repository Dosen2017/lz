<?php

namespace app\admin\controller\delivery;

use think\Request;
use think\Session;
use app\common\controller\Backend;

/**
 * 个人模板
 *
 * @icon fa fa-circle-o
 */
class UserTemplates extends Backend
{
    
    /**
     * UserTemplates模型对象
     * @var \app\admin\model\delivery\UserTemplates
     */
    protected $model = null;
    protected $userPagemodel;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\delivery\UserTemplates;
        $this->userPagemodel = new \app\admin\model\delivery\UserPages();
        $this->view->assign("redirectTypeList", $this->model->getRedirectTypeList());
        $this->view->assign("userTemplateList", $this->IdToName());
    }
    
    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */

    //加入对当前表数据的渲染方法
    public function indexMiddle(&$list) {

        //创建人列表
        $adminModel = new \app\admin\model\Admin();
        $adminList = $adminModel->getKVList();

        //模板编号
        $pageTempModel = new \app\admin\model\delivery\PageTemplates();
        $pageTempList = $pageTempModel->getKVList();

        foreach ($list as $k => $v) {
//            $list[$k]['nickname'] = $adminList[$v['admin_id']] . "[" . $v['admin_id'] . "]";
//            $list[$k]['page_template_name'] = $pageTempList[$v['template_id']] . "[" . $v['template_id'] . "]";

            $list[$k]['nickname'] = $adminList[$v['admin_id']];
            $list[$k]['page_template_name'] = $pageTempList[$v['template_id']];

            $list[$k]['preview'] = Request::instance()->domain() . "/IAd/TestUserTemplate/" . $v['id'];
        }

    }

    /**
     * 编辑
     */
    public function edit($ids = null)
    {
        $row = $this->model->get($ids);
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

                if ($this->model->where(['id' => $ids])->update($userTempplates)) {
                    $this->success("update success！");
                }
                $this->error(__('update fail!'));
            }

        }

        $pageTemplatesModel = new \app\admin\model\delivery\PageTemplates();
        $pageTData = $pageTemplatesModel->field('name, args_config')->where(['template_id' => $row['template_id']])->find();

        $argsConfig = json_decode($pageTData['args_config'], true);
        $trueExtConfig = json_decode($row['ext_config'], true);
        foreach ($argsConfig as $k => $v) {
            $argsConfig[$k]['Default'] = $trueExtConfig[$v['Name']];
        }
        $this->view->assign("ext_config", $argsConfig);
        $row['page_temp_name'] = $pageTData['name'];
        $this->view->assign("row", $row);
        return $this->view->fetch();
    }

    /**
     * 编辑
     */
    public function user_pages_edit($ids = null)
    {

        $row = $this->model->get($ids);
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

            if ($this->userPagemodel->where(['ads_id' => $params['ads_id']])->find() ) {
                $this->error('ads_id is exists');
            }

            if ($params) {

                $userPages['ads_id'] = $params['ads_id'];
                $userPages['admin_id'] = Session::get("admin")['id'];
                $userPages['user_template_id'] = $params['user_template_id'];
                $userPages['title'] = $params['title'];
                $userPages['ios_url'] = $params['ios_url'];
                $userPages['android_url'] = $params['android_url'];
                $userPages['ios_channelid'] = $params['ios_channelid'];
                $userPages['android_channelid'] = $params['android_channelid'];
                $userPages['redirect_type'] = $params['redirect_type'];

                if ($this->userPagemodel->save($userPages)) {

                    //  在IAd/TestUserTemplate/$lastId/index.php  生成来自于模板IAd/TestPageTemplate/{$params['template_id']}/index.php
                    $sourcePath = ROOT_PATH . "public" . DIRECTORY_SEPARATOR . "IAd" . DIRECTORY_SEPARATOR . "TestUserTemplate" . DIRECTORY_SEPARATOR . $ids . DIRECTORY_SEPARATOR . "index.php";
                    $destDir = ROOT_PATH . "public" . DIRECTORY_SEPARATOR . "IAd" . DIRECTORY_SEPARATOR . $userPages['ads_id'];
                    if (!is_dir($destDir)) {
                        mkdir($destDir, 0755);
                    }
                    $destPath = $destDir . DIRECTORY_SEPARATOR . "index.php";
                    if (!file_exists($destPath)) {
                        $myfile = fopen($destPath, "w") or die("Unable to open file!");
                        fwrite($myfile, file_get_contents($sourcePath));
                        fclose($myfile);
                    }

                    $this->success("save success", url('/delivery/user_pages'), '',1);
                }
                $this->error(__('save data fail!'));
            }

        }

        $this->view->assign("row", $row);

        return $this->view->fetch();
    }

    /**
     * 查看
     */
    public function IdToName()
    {
        $ret = [];
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
            $ret[$v['id']] = $v['page_name'];
        }

        return $ret;

    }

    /**
     * 查看
     */
    public function searchlist()
    {
        $ret = $this->IdToName();
        return json($ret);
    }
}
