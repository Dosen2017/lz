<?php

namespace app\admin\controller\gamemanage;

use app\common\controller\Backend;
use think\Config;

/**
 * 渠道管理
 *
 * @icon fa fa-circle-o
 */
class ChannelPkg extends Backend
{
    protected $multiFields = ['login_lockswitch', 'register_lockswitch', 'pay_lockswitch', 'login_smswitch', 'pay_smswitch', 'quick_regitserswitch', 'fcmswitch'];//开关权限开启
    /**
     * ChannelPkg模型对象
     * @var \app\admin\model\gamemanage\ChannelPkg
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\gamemanage\ChannelPkg;
        $this->view->assign("osTypeList", $this->model->getOsTypeList());
    }
    
    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */


    //加入对当前表数据的渲染方法
    public function indexMiddle(&$list) {

        $cModel = new \app\admin\model\gamemanage\Game();
        $gameList = $cModel->getKVList();

        $caModel = new \app\admin\model\gamemanage\Channel();
        $channelList = $caModel->getKVList();

        foreach ($list as $k => $v) {
            $list[$k]['game_name'] = $gameList[$v['game_num']] . "[" . $v['game_num'] . "]";  //所属游戏
        }

        foreach ($list as $k => $v) {
            $list[$k]['channel_name'] = $channelList[$v['channel_num']] . "[" . str_pad($v['channel_num'], 2, "0", STR_PAD_LEFT) . "]";  //所属渠道商
        }

    }

    public function getNums() {

//        {"list":[{"id":88001,"name":"乐众游戏十<font color='#8b0000'>[88001]<\/font>","pid":0},
//                  {"id":88002,"name":"乐众游戏十一<font color='#8b0000'>[88002]<\/font>","pid":0},
//                 {"id":88003,"name":"乐众游戏十二<font color='#8b0000'>[88003]<\/font>","pid":0}],"total":3}
//        echo '{"list":[{"id":1,"name":"01"},{"id":2,"name":"02"},{"id":3,"name":"03"}],"total":3}';

        $list = [];
        $i = 1;
        for (; $i <= 50; $i++) {
            $tmp = [];
            $tmp['id'] = (int)$i;
            $tmp['name'] = str_pad($i, 2, "0", STR_PAD_LEFT);
            $list[] = $tmp;
        }


        return json(['list' => $list, 'total' => $i - 1]);
    }

    //在添加数据之前，对数据进行处理
    public function beforeAdd(&$params) {
//echo json_encode($params);exit;
//        if (!is_numeric($params['number']) || $params['number'] > 50) {
//            $this->error(__('包编号错误') );
//        }

        //number只会提交选择的数字，不论选择后，改成多少

        //渠道ID = 游戏ID + 两位number编号 + 两位渠道ID
        $params['channel_pkg_num'] = $params['game_num'] . ($params['number'] < 10 ? '0' . $params['number']  :  $params['number']). ($params['channel_num'] < 10 ? '0' . $params['channel_num'] : $params['channel_num']);
        if ( $this->model->where(['channel_pkg_num' => $params['channel_pkg_num']])->find() ) {
            //如果此渠道号已经存在，则提示错误
            $this->error(__('渠道ID已存在！') );
        }

    }

    public function afterAdd($row) {
        // 写入bundle 文件
        if ($row['bundle']) {
            $isSucc = $this->genBundleFile($row['bundle']);
            if (!$isSucc) {
                $this->error(__('生成BundleId文件失败！') );
            }
        }
    }

    /**
     * 详情
     */
    public function detail($ids)
    {
        $row = $this->model->get(['id' => $ids]);
        if (!$row)
            $this->error(__('No Results were found'));

        $rowSingle = $row->toArray();

        $gameModel = new \app\admin\model\gamemanage\Game();

        $gameParamData = $gameModel->getGameParams($rowSingle['game_num']);
        $rowSingle['app_id'] = $gameParamData['app_num'];
        $rowSingle['app_key'] = $gameParamData['app_key'];
        $rowSingle['pay_key'] = $gameParamData['pay_key'];

        $this->view->assign("row", $rowSingle);
        return $this->view->fetch();
    }

    public function searchlist2() {

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
            $ret[$k]['id'] = $v['channel_pkg_num'];
            $ret[$k]['name'] = $v['channel_pkg_name'];
        }


        return json(['list' => $ret, 'total' =>  1]);
    }

    public function searchlist3() {

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

            $ret[$k]['channel_pkg_num'] = $v['channel_pkg_num'];
            $ret[$k]['channel_pkg_name'] = $v['channel_pkg_name'];

//            $ret[$v['channel_pkg_num']] = $v['channel_pkg_name'];
        }


        return json(['list' => $ret, 'total' =>  1]);
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
            $ret[$v['channel_pkg_num']] = $v['channel_pkg_name'];
        }

        return json($ret);

    }

    public function beforeEdit($row, &$params) {
//        echo json_encode($row) . "\n";
//        echo json_encode($params);
//        exit;

        if ((int)($row['channel_pkg_num'] / 10000)  == $params['game_num'] && (int)($row['channel_pkg_num'] % 100) == $params['channel_num'] ) {

        } else{
            $this->error(__('不能修改渠道ID相关值！') );
        }

    }

    public function afterEdit($row) {

//        $key = 'lzCfWDEWF#@$@Aa~2020si@!@##@#~odjqo2!';
        $time = time();
        $data['channel_pkg_num'] =  $row['channel_pkg_num'];
        $data['time'] = $time;
        $data['sign'] = md5($data['channel_pkg_num'] . $data['time'] . Config::get('clear_cache_sign_key'));
        //清除登录的缓存
        $loginUrl = "http://" . Config::get('api_domain') . "/login/ClearCache";
        $retLogin = file_get_contents($loginUrl . "?" . http_build_query($data));

        //清除充值的缓存
        $payUrl = "http://" . Config::get('api_domain') . "/recharge/ClearCache";
        $retPay = file_get_contents($payUrl . "?" . http_build_query($data));

        if ($retLogin !== "SUCCESS" || $retPay !== "SUCCESS" ) {
            $this->error(__('清除缓存失败！') );
        }

        // 写入bundle 文件
        if ($row['bundle']) {
            $isSucc = $this->genBundleFile($row['bundle']);
            if (!$isSucc) {
                $this->error(__('生成BundleId文件失败！') );
            }
        }

    }

    public function genBundleFile($bundleId = '')
    {
        try{
            if (empty($bundleId)) return false;
            $time = time();
            $data['bundle'] =  $bundleId;
            $data['time'] = $time;
            $data['sign'] = md5($data['bundle'] . $data['time'] . Config::get('clear_cache_sign_key'));

            $genUrl = "http://" . Config::get('api_domain') . "/recharge/GenBundleFile";
            $isSucc = file_get_contents($genUrl . "?" . http_build_query($data));
            if ($isSucc !== "SUCCESS") {
                return false;
            }
            return true;
        }catch (\Exception $exception) {
            return false;
        }

    }

}
