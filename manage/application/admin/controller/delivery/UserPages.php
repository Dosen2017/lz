<?php

namespace app\admin\controller\delivery;

use think\Request;
use app\common\controller\Backend;

/**
 * 落地页管理
 *
 * @icon fa fa-circle-o
 */
class UserPages extends Backend
{
    
    /**
     * UserPages模型对象
     * @var \app\admin\model\delivery\UserPages
     */
    protected $model = null;

    protected $userTemplatesCon = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\delivery\UserPages();
        $this->view->assign("redirectTypeList", $this->model->getRedirectTypeList());
        $this->userTemplatesCon = new \app\admin\controller\delivery\UserTemplates();
        $this->view->assign("userTemplateList", $this->userTemplatesCon->IdToName());

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
        $userTempModel = new \app\admin\model\delivery\UserTemplates();
        $userTempList = $userTempModel->getKVList();

        foreach ($list as $k => $v) {
            $list[$k]['nickname'] = $adminList[$v['admin_id']] . "[" . $v['admin_id'] . "]";
            $list[$k]['user_template_name'] = $userTempList[$v['user_template_id']] . "[" . $v['user_template_id'] . "]";
        }

        foreach ($list as $k => $v) {
            $list[$k]['preview'] = Request::instance()->domain() . "/IAd/" . $v['ads_id'];
        }

    }

    /**
     * 查看
     */
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
            $ret[$v['id']] = $v['ads_id'];
        }

        return json($ret);

    }

}
