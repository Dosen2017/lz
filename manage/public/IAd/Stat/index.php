<?php
    date_default_timezone_set("PRC");
    require_once './tools.php';

    $getParam = $_GET;
    $userPageId = (int)$getParam['ptpId'];  //属于自增ID
    $user_template_id = (int)$getParam['utpId'];
    $template_id = (int)$getParam['tpId'];

    $ip = Tools::get_client_ip();

    if ($userPageId == 0 || $user_template_id == 0 || $template_id ==0){
        Tools::exitStr("param is null!");
    }

    //判断ip是否重复请求，使用redis
    if (Tools::isRepeatRequest($template_id, $user_template_id, $userPageId, $ip)) {
        Tools::exitStr("repeat request!");
    }

    //在后台设置的广告ID后面额外传入的值
    $kkk = '';
    Tools::getKKK($getParam, $kkk);

    //查找落地页的数据
    $data = Tools::findUserPagesData($userPageId);
    //如果落地页未设置IOS或者安卓的渠道号，则去个人模板里面去取值
    $userTemplateData = Tools::findUserTemplatesData($data, $user_template_id);

    //---------------------1.进行点击日志的记录-----------------------
    //整理数据
    $saveData = Tools::arrangeData($data, $kkk, $getParam, $userTemplateData, $ip);
    Tools::recordPageLogs($saveData);

    //---------------------2.修改page_stats表的点击事件数量-------------------
    Tools::updatePageStats($data['id']);

    Tools::exitStr("success");

