<?php

namespace app\admin\controller\channelpack;

use app\admin\model\gamemanage\Channel;
use app\admin\model\gamemanage\Game;
use app\common\controller\Backend;

/**
 * 渠道包管理
 *
 * @icon fa fa-circle-o
 */
class ChannelPack extends Backend
{
    
    /**
     * ChannelPack模型对象
     * @var \app\admin\model\channelpack\ChannelPack
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\channelpack\ChannelPack;

    }


    public function renderView($gameId = 0)
    {
        if ($gameId == 0) {
            $gameId = $this->request->get('game_num');
        }

        $gameM = new Game();
        $channelM = new Channel();
        $gamelist = $gameM->getKVList();
        $channellist = $channelM->getKVList();
        $keystorelist = [];
        foreach (\app\admin\model\channelpack\Keystore::field('id, remark')->select() as $v) {
            $keystorelist[$v['id']] = $v['remark'];
        }

        $gameInfo = Game::where(['id' => $gameId])->find();
        $iconlist = [];
        if ($gameId) {
            $icons = \app\admin\model\channelpack\Icons::field('id, remark')->where(['game_num'=>$gameId])->select();
        }else{
            $icons = \app\admin\model\channelpack\Icons::field('id, remark')->select();
        }
        foreach ($icons as $v) {
            $iconlist[$v['id']] = $v['remark'];
        }

        $basepacklist = [];
        if ($gameId) {
            $basepacks = \app\admin\model\channelpack\Basepack::field('id, remark')->where(['game_num'=>$gameId])->select();
        }else{
            $basepacks = \app\admin\model\channelpack\Basepack::field('id, remark')->select();
        }
        foreach ($basepacks as $v) {
            $basepacklist[$v['id']] = $v['remark'];
        }

        $this->view->assign('game_num', $gameId);
        $this->view->assign('game_name', $gameInfo['name']);
        $this->view->assign('keystorelist', $keystorelist);
        $this->view->assign('gamelist', $gamelist);
        $this->view->assign('iconlist', $iconlist);
        $this->view->assign('channellist', $channellist);
        $this->view->assign('basepacklist', $basepacklist);
    }

    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */

    public function edit($ids = null)
    {
        $row = $this->model->get($ids);

        if ($this->request->isPost()) {
            return parent::edit($ids);
        }

        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            if (!in_array($row[$this->dataLimitField], $adminIds)) {
                $this->error(__('You have no permission'));
            }
        }
        if ($this->request->isGet()) {
            $gameId = $row['game_num'];
            $this->renderView($gameId);
        }

        $this->view->assign("row", $row);
        return $this->view->fetch();
    }

    public function add()
    {
        $gameId = $this->request->get('game_num');

        if ($this->request->isGet()) {
            $this->renderView();
        }
        if ($gameId) {
            if ($this->request->isPost()) {
                return parent::add();
            }
            return $this->view->fetch('add_detail');
        }else{
            return $this->view->fetch();
        }
    }


    public function add_detail()
    {


        return $this->view->fetch();
    }

}
