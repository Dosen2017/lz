<?php

namespace app\admin\controller\gamemanage;

use app\common\controller\Backend;
//use think\Cookie;
use think\Session;
use think\Db;
use think\Config;

/**
 * 支付策略
 *
 * @icon fa fa-circle-o
 */
class ChannelPayments extends Backend
{

    protected $multiFields = ['use_switch'];

    /**
     * ChannelPayments模型对象
     * @var \app\admin\model\gamemanage\ChannelPayments
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\gamemanage\ChannelPayments;

    }
    
    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */

    /**
     * 查看
     */
    public function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }

            $total = 1;
            $list = $this->getList($total);

            $channelPkgModel = new \app\admin\model\gamemanage\ChannelPkg();
            $channelPkgList = $channelPkgModel->getKVList();
            $channelPkgNum = json_decode($_GET['filter'], true)['channel_pkg_num'];

            Session::set('s_channel_pkg_num', $channelPkgNum);

            $result = array("total" => $total, "rows" => $list, "extend" => ['channel_pkg_num' => $channelPkgList[$channelPkgNum] . "(" . $channelPkgNum . ")"]);

            return json($result);
        }

        $this->renderMultiSelect();
        return $this->view->fetch();
    }

    //加入对当前表数据的渲染方法
    public function indexMiddle(&$list) {

        $cpModel = new \app\admin\model\gamemanage\ChannelPkg();
        $channelPkgList = $cpModel->getKVList();

        foreach ($list as $k => $v) {
            $list[$k]['channel_pkg_name'] = $channelPkgList[$v['channel_pkg_num']] . "[" . $v['channel_pkg_num'] . "]";  //所属游戏
        }


    }

    public function getStategyClass() {
//        Manling.GameSDK.Payment.Strategy.LanguageStrategy => 语言策略(Manling.GameSDK.Payment.Strategy.LanguageStrategy),
//        Manling.GameSDK.Payment.Strategy.ProductStrategy => ProductId策略(Manling.GameSDK.Payment.Strategy.ProductStrategy),
//        Manling.GameSDK.Payment.Strategy.LoginTimeStrategy => 登录时长策略(Manling.GameSDK.Payment.Strategy.LoginTimeStrategy),
//        Manling.GameSDK.Payment.Strategy.RegTimeStrategy => 注册时长策略(Manling.GameSDK.Payment.Strategy.RegTimeStrategy),
//        Manling.GameSDK.Payment.Strategy.ChargeAmountStrategy => 本次金额策略(Manling.GameSDK.Payment.Strategy.ChargeAmountStrategy),
//        Manling.GameSDK.Payment.Strategy.PayAmountStrategy => 累计金额策略(Manling.GameSDK.Payment.Strategy.PayAmountStrategy),
//        Manling.GameSDK.Payment.Strategy.ChargeCountStrategy => 累计充值笔数策略(Manling.GameSDK.Payment.Strategy.ChargeCountStrategy),
//        Manling.GameSDK.Payment.Strategy.CityStrategy => 城市策略(Manling.GameSDK.Payment.Strategy.CityStrategy),
//        Manling.GameSDK.Payment.Strategy.PackIdStrategy => 包策略(Manling.GameSDK.Payment.Strategy.PackIdStrategy),
//        Manling.GameSDK.Payment.Strategy.CountryStrategy => 国家策略(Manling.GameSDK.Payment.Strategy.CountryStrategy),
//        Manling.GameSDK.Payment.Strategy.General => 默认综合策略(Manling.GameSDK.Payment.Strategy.General),
//        Manling.GameSDK.Payment.Strategy.IPStrategy => IP策略(Manling.GameSDK.Payment.Strategy.IPStrategy),
//        Manling.GameSDK.Payment.Strategy.UserStrategy => 用户策略(Manling.GameSDK.Payment.Strategy.UserStrategy)


        $strategys =  [
////                'LanguageStrategy'          => '语言策略(LanguageStrategy)',
//                'ProductStrategy'           => 'ProductId策略(ProductStrategy)',       //{"productFlag":"1","productList":"fb.hx.4\r\nfb.hx.7"}
////                'LoginTimeStrategy'         => '登录时长策略(LoginTimeStrategy)',
////                'RegTimeStrategy'           => '注册时长策略(RegTimeStrategy)',
                'ChargeAmountStrategy'      => '本次金额策略(ChargeAmountStrategy)',    //{"payAmountMin":"0","payAmountMax":"1000000"}
//                'PayAmountStrategy'         => '累计金额策略(PayAmountStrategy)',       //{"chargeAmountMin":"7200","chargeAmountMax":"100000000"}
//                'ChargeCountStrategy'       => '累计充值笔数策略(ChargeCountStrategy)',  //{"chargeCountMin":"-1","chargeCountMax":"1"}
//                'CityStrategy'              => '城市策略(CityStrategy)',                //{"cityFlag":"1","cityList":"深圳"}
                'PackIdStrategy'            => '包策略(PackIdStrategy)',               //{"packIdList":"com.xmzj.lzyx.1.1.2.2.7.4"}
//                'CountryStrategy'           => '国家策略(CountryStrategy)',            //{"countryList":"美国\r\nUS\r\nUSA\r\nUnited States\r\nAmerica"}
////                'General'                   => '默认综合策略(General)',
////                'IPStrategy'                => 'IP策略(IPStrategy)',
                'UserStrategy'              => '用户策略(UserStrategy)'                //{"userIds":"53000226846"}
            ];
        $list = [];
        foreach ($strategys as $k => $v) {
            $list[]['id'] = $k;
            $list[]['name'] = $v;
        }

        $total = count($strategys);

        return json(['list' => $list, 'total' => $total]);

    }
    public function beforeEdit($row, &$params) {
        if ( "ChargeAmountStrategy" == $params['strategy_class'] ) {
            $strategyJson['payAmountMin'] = $params['conf_payAmountMin'];
            $strategyJson['payAmountMax'] = $params['conf_payAmountMax'];

            $params['strategy_json'] = json_encode($strategyJson);
        }

        if ( "PackIdStrategy" == $params['strategy_class'] ) {
            $strategyJson['packIdList'] = $params['conf_packIdList'];
            $params['strategy_json'] = json_encode($strategyJson);
        }

        if ( "UserStrategy" == $params['strategy_class'] ) {
            $strategyJson['userList'] = $params['conf_userList'];
            $params['strategy_json'] = json_encode($strategyJson);
        }

        //清除conf开头的参数
        foreach ($params as $k => $v) {
            if (strpos($k, "conf_") !== false) {
                unset($params[$k]);
            }
        }
    }
    public function beforeAdd(&$params) {

//        array(7) {
//            ["channel_pkg_num"]=>
//  string(9) "880010501"
//            ["strategy_class"]=>
//  string(20) "ChargeAmountStrategy"
//            ["desc"]=>
//  string(33) "根据金额限制是否切支付"
//            ["conf_payAmountMin"]=>
//  string(1) "0"
//            ["conf_payAmountMax"]=>
//  string(3) "100"
//            ["conf_packIdList"]=>
//  string(0) ""
//            ["conf_userList"]=>
//  string(0) ""
//}

        if ( "ChargeAmountStrategy" == $params['strategy_class'] ) {
            $strategyJson['payAmountMin'] = $params['conf_payAmountMin'];
            $strategyJson['payAmountMax'] = $params['conf_payAmountMax'];

            $params['strategy_json'] = json_encode($strategyJson);
        }

        if ( "PackIdStrategy" == $params['strategy_class'] ) {
            $strategyJson['packIdList'] = $params['conf_packIdList'];
            $params['strategy_json'] = json_encode($strategyJson);
        }

        if ( "UserStrategy" == $params['strategy_class'] ) {
            $strategyJson['userList'] = $params['conf_userList'];
            $params['strategy_json'] = json_encode($strategyJson);
        }

        //清除conf开头的参数
        foreach ($params as $k => $v) {
            if (strpos($k, "conf_") !== false) {
                unset($params[$k]);
            }
        }

        //判断该渠道包下的对应策略是否已经设置了
        if ( $this->model->where(['channel_pkg_num' => $params['channel_pkg_num'], 'strategy_class' => $params['strategy_class']])->find() ) {
            $this->error("该策略已经设置，请在列表中修改即可！");
        }

        $params['order_id'] = $this->model->where(['channel_pkg_num' => $params['channel_pkg_num']])->max('order_id') + 1;  //将新设置的策略设置为最优先级

    }

    protected function renderEdit(&$row) {

        if ("ChargeAmountStrategy" == $row['strategy_class']) {
            $strategyArr = json_decode($row['strategy_json'], true);
            $row['conf_payAmountMin'] = $strategyArr['payAmountMin'] ?: 0;
            $row['conf_payAmountMax'] = $strategyArr['payAmountMax'] ?: 0;
        }

        if ( "PackIdStrategy" == $row['strategy_class'] ) {
            $strategyArr = json_decode($row['strategy_json'], true);
            $row['conf_packIdList'] = $strategyArr['packIdList'] ?: '';
        }

        if ( "UserStrategy" == $row['strategy_class'] ) {
            $strategyArr = json_decode($row['strategy_json'], true);
            $row['conf_userList'] = $strategyArr['userList'] ?: '';
        }

    }

    public function sort(){
//排序的数组
        $ids = $this->request->post("ids");
        //拖动的记录ID
        $changeid = $this->request->post("changeid");
        //操作字段
        $field = $this->request->post("field");
        //操作的数据表
        $table = $this->request->post("table");
        //排序的方式
        $orderway = $this->request->post("orderway", 'strtolower');
        $orderway = $orderway == 'asc' ? 'ASC' : 'DESC';
        $sour = $weighdata = [];
        $ids = explode(',', $ids);
        $prikey = 'id';
        $pid = $this->request->post("pid");
        //限制更新的字段
        $field = in_array($field, ['order_id']) ? $field : 'order_id';   //你要修改的就是这里，把原来的weigh，修改成你要排序的字段

        // 如果设定了pid的值,此时只匹配满足条件的ID,其它忽略
        if ($pid !== '') {
            $hasids = [];
            $list = Db::name($table)->where($prikey, 'in', $ids)->where('pid', 'in', $pid)->field('id,pid')->select();
            foreach ($list as $k => $v) {
                $hasids[] = $v['id'];
            }
            $ids = array_values(array_intersect($ids, $hasids));
        }

        $list = Db::name($table)->field("$prikey,$field")->where($prikey, 'in', $ids)->order($field, $orderway)->select();
        foreach ($list as $k => $v) {
            $sour[] = $v[$prikey];
            $weighdata[$v[$prikey]] = $v[$field];
        }
        $position = array_search($changeid, $ids);
        $desc_id = $sour[$position];    //移动到目标的ID值,取出所处改变前位置的值
        $sour_id = $changeid;
        $weighids = array();
        $temp = array_values(array_diff_assoc($ids, $sour));
        foreach ($temp as $m => $n) {
            if ($n == $sour_id) {
                $offset = $desc_id;
            } else {
                if ($sour_id == $temp[0]) {
                    $offset = isset($temp[$m + 1]) ? $temp[$m + 1] : $sour_id;
                } else {
                    $offset = isset($temp[$m - 1]) ? $temp[$m - 1] : $sour_id;
                }
            }
            $weighids[$n] = $weighdata[$offset];
            Db::name($table)->where($prikey, $n)->update([$field => $weighdata[$offset]]);
        }
        $this->success();
    }

    public function afterDel($val) {
        $this->clearCache($val['channel_pkg_num']);
    }

    public function afterEdit($row)
    {
        $this->clearCache($row['channel_pkg_num']);
    }

    public function afterAdd($params)
    {
        $this->clearCache($params['channel_pkg_num']);
    }

    protected function clearCache($channelPkgNum) {
        $time = time();
        $data['channel_pkg_num'] =  $channelPkgNum;
        $data['time'] = $time;
        $data['sign'] = md5($data['channel_pkg_num'] . $data['time'] . Config::get('clear_cache_sign_key'));

        //清除充值的缓存
        $payUrl = "http://" . Config::get('api_domain') . "/recharge/ClearCache/channel_payments";
        $retPay = file_get_contents($payUrl . "?" . http_build_query($data));

        if ($retPay !== "SUCCESS" ) {
            $this->error(__('清除缓存失败！') );
        }
    }
}
