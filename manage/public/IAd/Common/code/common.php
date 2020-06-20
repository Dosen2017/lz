<?php

    require_once 'comtools.php';

    $domain = "http://" . $_SERVER['HTTP_HOST'];
    $id = (int)basename(dirname($_SERVER['PHP_SELF']));
    $adsId = '';
    $table = '';
    $data = [];
    $kkk = '';

    //这个值是落地页在广告ID后额外加的字符串，这个字符串是用来一个落地页，多处使用，并且还能做统计，因为可以被打标识到玩家表中
    Comtools::getKKK($kkk);

    //得到表明和广告ID
    Comtools::getTableNameAndAdsId($id, $table, $adsId);
    //根据表名，获取page_templates/user_templates/user_pages的数据
    $data = Comtools::getData($table, $id, $adsId);

    $confData = [];
    $linkData = [];
    if ( $table == 'page_templates' ) {
        Comtools::getConfAndLinkDataPageTemplates($data, $confData, $linkData);
    } elseif($table == 'user_templates') {
        Comtools::getConfAndLinkUserPageTemplates($data, $confData, $linkData);
    } else {

        //落地页需要个人模板的数据做渲染
        $userTemplateData = Comtools::getUserTemplateData($data);
        Comtools::getConfAndLinkUserPages($userTemplateData, $data, $confData, $linkData);

        //对统计表(page_stats)进行修改
        Comtools::handlePageStats($table, $data);
    }

    //默认下载地址是安卓
    $downUrl= $data['android_url'];
    $os = "android";

    Comtools::getDownUrlAndOs($data['ios_url'], $downUrl, $os);


?>