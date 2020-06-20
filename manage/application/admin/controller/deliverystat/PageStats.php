<?php

namespace app\admin\controller\deliverystat;

use app\common\controller\Backend;

/**
 * 落地页/个人【公用】模板统计
 *
 * @icon fa fa-circle-o
 */
class PageStats extends Backend
{
    
    /**
     * PageStats模型对象
     * @var \app\admin\model\deliverystat\PageStats
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\deliverystat\PageStats;

    }
    
    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */

    public function getList(&$total) {

        $userPagesModel = new \app\admin\model\delivery\UserPages();
        $userPagesList = $userPagesModel->getKVList();

        $list = [];

        $where = '';

        $filterData = json_decode($this->request->request('filter'), true);

        //默认查询时间是七天内
        $startTime = date("Ymd", time() - 6 * 86400);
        $endTime = date("Ymd", time());

        if (isset($filterData['opt_d'])) {
            $timeArr = explode(' - ', $filterData['opt_d']);
            $startTime = date("Ymd", strtotime(trim($timeArr[0])));
            $endTime = date("Ymd", strtotime(trim($timeArr[1]))+1);
        }
        $where .= " and opt_d between $startTime and $endTime";
        if (isset($filterData['id'])) {
            $where .= " and source_id =" . $filterData['id'];
        }

        $sql = "select source_id, sum(view_count) as vc_sum, sum(hit_count) as hc_sum from page_stats where type = 'user_pages' " . $where . " group by source_id";

        $regData = $this->model->query($sql);
        foreach ($regData as $k => $v) {
            $list[$k]['bid'] = $k + 1;  //编号
            $list[$k]['id'] = $v['source_id'];  //广告ID  为了在【统计记录/明细】中使用，这里用id表示source_id

            $list[$k]['vc_sum'] = $v['vc_sum'];  //浏览数
            $list[$k]['hc_sum'] = $v['hc_sum'];  //点击数
            $list[$k]['hit_per'] = $list[$k]['vc_sum'] != 0 ? round($list[$k]['hc_sum'] / $list[$k]['vc_sum'], 4) * 100 . "%" : "0.00%"; //点击率
            $list[$k]['source_name'] = $userPagesList[$v['source_id']] . "[" . $v['source_id'] . "]";
            $list[$k]['pre_view'] = "http://" . $_SERVER['HTTP_HOST'] . "/IAd/" . $userPagesList[$v['source_id']];
        }

        rsort($list);

        return $list;
    }

    /**
     * 统计记录
     */
    public function record($ids)
    {
        $userPagesModel = new \app\admin\model\delivery\UserPages();
        $userPagesList = $userPagesModel->getKVList();

        $rows = $this->model->where(['source_id' => $ids])->order('opt_d desc')->select();

        foreach ($rows as $k => $v)
        {

            $rows[$k]['b_id'] = $v['id'];
            $rows[$k]['ads_id'] = $userPagesList[$v['source_id']];
            $rows[$k]['hit_per'] = $v['view_count'] != 0 ? round($v['hit_count'] / $v['view_count'], 4) * 100 . "%" : "0.00%";;
        }

        if (!$rows)
            $this->error(__('No Results were found'));
        $this->view->assign("row", $rows);
        return $this->view->fetch();
    }

    /**
     * 明细
     */
    public function record_log($ids)
    {
        $userPagesModel = new \app\admin\model\delivery\UserPages();
        $userPagesList = $userPagesModel->getKVList();

        $pageLogsModel = new \app\admin\model\deliverystat\PageLogs();

        $rows = $pageLogsModel->where(['ads_id' => $userPagesList[$ids]])->order('add_time desc')->select();

        foreach ($rows as $k => $v)
        {
            $rows[$k]['b_id'] = $v['id'];
//            $rows[$k]['ads_id'] = $userPagesList[$v['source_id']];
//            $rows[$k]['hit_per'] = $v['view_count'] != 0 ? round($v['hit_count'] / $v['view_count'], 4) * 100 . "%" : "0.00%";;
        }

        if (!$rows)
            $this->error(__('No Results were found'));
        $this->view->assign("row", $rows);
        return $this->view->fetch();
    }

    /**
     * 明细
     */
//    public function pre_view($ids)
//    {
//        $userPagesModel = new \app\admin\model\delivery\UserPages();
//        $userPagesList = $userPagesModel->getKVList();
//
//        $url = $domain = "http://" . $_SERVER['HTTP_HOST'] . "/IAd/" . $userPagesList[$ids];
////        echo $url;exit;
//        header('Location: '.$url);
////        redirect($url);
//
//    }
}
