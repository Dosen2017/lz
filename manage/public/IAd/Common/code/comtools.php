<?php
    class Comtools {

        protected static $conn = null;
        protected static $iadConfig = null;

        public static function getConfig()
        {
            if (self::$iadConfig == null) {
                self::$iadConfig = include 'iad_config.php';
            }
            return self::$iadConfig;
        }

        public static function getDdConn()
        {
            $iadConfigN = self::getConfig();
            if (self::$conn == null) {
                // 创建连接
                self::$conn = new mysqli($iadConfigN['server_name'], $iadConfigN['username'], $iadConfigN['password'], $iadConfigN['dbname']);
                // Check connection
                if (self::$conn->connect_error) {
                    self::exitStr("连接失败: ");
                }
            }
            return self::$conn;
        }

        public static function getUserTemplateData($data)
        {
            //落地页
            $userTemplatesField = ['template_id', 'title', 'ios_url', 'android_url', 'ios_channelid', 'android_channelid', 'redirect_type', 'ext_config'];
            $sql = "SELECT " . implode(", ", $userTemplatesField) . " FROM user_templates where id = " . $data['user_template_id'];

            $userTresult = self::getDdConn()->query($sql);

            $userTemplateData = [];
            if ($userTresult->num_rows > 0) {
                // 输出数据
                $userTemplateData = $userTresult->fetch_assoc();
            }

            return $userTemplateData;
        }

        public static function getConfAndLinkDataPageTemplates($data, &$confData, &$linkData)
        {
            //公共模板的情况下
            $conArr = json_decode($data['args_config'], true);
            foreach ($conArr as $k => $v) {
                if ($v['Default'] != '') {
                    $confData[$v['Name']] = $v["Default"];
                }
            }

            $linkData = explode('|', $confData['带链接的图片']);
        }

        public static function getConfAndLinkUserPageTemplates($data, &$confData, &$linkData)
        {
            //个人模板的情况下
            $conArr = json_decode($data['ext_config'], true);
            foreach ($conArr as $k => $v) {
                $confData[$k] = $v;
            }
            $linkData = explode('|', $confData['带链接的图片']);
        }

        public static function getConfAndLinkUserPages($userTemplateData, &$data, &$confData, &$linkData)
        {
            $conArr = json_decode($userTemplateData['ext_config'], true);
            foreach ($conArr as $k => $v) {
                $confData[$k] = $v;
            }
            $linkData = explode('|', $confData['带链接的图片']);

            //根据落地页和个人模板综合得到'title', 'ios_url', 'android_url', 'ios_channelid', 'android_channelid', 'redirect_type'的数据
            $data['title'] = $data['title'] ?: $userTemplateData['title'];
            $data['ios_url'] = $data['ios_url'] ?: $userTemplateData['ios_url'];
            $data['android_url'] = $data['android_url'] ?: $userTemplateData['android_url'];
            $data['ios_channelid'] = $data['ios_channelid'] ?: $userTemplateData['ios_channelid'];
            $data['android_channelid'] = $data['android_channelid'] ?: $userTemplateData['android_channelid'];
            $data['redirect_type'] = $data['redirect_type'] == 5 ? $userTemplateData['redirect_type'] : $data['redirect_type'] ;
        }

        public static function getData($table, $id, $adsId)
        {
            $sql = '';
            switch ($table) {
                case 'page_templates':
                    $sql = "SELECT * FROM ". $table . " where template_id = " . $id;
                    break;
                case 'user_templates':
                    $sql = "SELECT * FROM " . $table . " where id = " . $id;
                    break;
                case 'user_pages':
                    $trueAdsId = explode("-", $adsId)[0];
                    $sql = "SELECT * FROM " . $table . " where ads_id = '" . $trueAdsId . "'";
                    break;
            }

            $result = self::getDdConn()->query($sql);

            $data = [];
            if ($result->num_rows > 0) {
                // 输出数据
                $data = $result->fetch_assoc();
            }

            if (empty($data)) {
                self::exitStr(模板数据不存在！);
            }

            return $data;
        }

        public static function handlePageStats($table, $data)
        {
            //------------------------- PageStats对统计表进行修改 ------------------------------
            $pageStatTableName = "page_stats";
            $pageStatData['type'] = "'" . $table . "'";   // user_pages
            $pageStatData['source_id'] = (int)$data['id'];
            $pageStatData['opt_d'] = (int)date('Ymd');

            $whereStr = " where type = " . $pageStatData['type']
                . " and source_id = " . $pageStatData['source_id'] . " and opt_d = " . $pageStatData['opt_d'];

            $pageStatSql = "select view_count from $pageStatTableName $whereStr";

            if (!self::getDdConn()->query($pageStatSql)->num_rows) {
                //不存在记录时，添加数据到page_stats表
                $pageStatData['view_count'] = 1;
                $pageStatData['hit_count'] = 0;
                $insertFields = '';
                foreach ($pageStatData as $k => $v) {
                    $insertFields .= "`" . $k . "`, ";
                }
                $insertFields = substr($insertFields, 0 , -2); //有空格和逗号两个字符要去掉
                $insertValues = implode(", ", $pageStatData);

                $pageStatSqlInsert = "insert into $pageStatTableName($insertFields) values($insertValues)";

                self::getDdConn()->query($pageStatSqlInsert);
            } else {
                //已存在该记录时，update
                $pageStatSqlUpdate = "update $pageStatTableName set view_count = view_count + 1" . $whereStr;
                self::getDdConn()->query($pageStatSqlUpdate);
            }
            //------------------------- PageStats对统计表进行修改 -------------------------------
        }

        //得到表名，如果是落地页，拿到广告ID
        public static function getTableNameAndAdsId($id, &$table, &$adsId)
        {
            //判断是否是模板类型，得到表名
            if (strpos($_SERVER['PHP_SELF'], "Test")) {
                $table = strtolower(preg_replace('/(?<=[a-z])([A-Z])/', '_$1', str_replace("Test", "", basename(dirname(dirname($_SERVER['PHP_SELF'])))))) . "s";
            } else {
                $table = 'user_pages';
                $adsId = basename(dirname($_SERVER['PHP_SELF']));

                //判断adsId的字符是否符合要求 字母、数字、_、-
                if (!preg_match('/^[0-9a-zA-Z_-]{1,50}$/', $adsId)) {
                    self::exitStr("广告ID不符合要求");
                }

            }

            if (!in_array($table , ['page_templates', 'user_templates', 'user_pages'])) {
                self::exitStr("获取的表名不正确");
            }

            if ($adsId == '' && empty($id)) {
                self::exitStr("获取模板失败");
            }
        }

        //获取设备型号
        public static function get_device_type()
        {
            //全部变成小写字母
            $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
            $type = 'other';
            //分别进行判断
            if(strpos($agent, 'iphone') || strpos($agent, 'ipad'))
            {
                $type = 'ios';
            }

            if(strpos($agent, 'android'))
            {
                $type = 'android';
            }
            return $type;
        }

        public static function getKKK(&$kkk)
        {
            if(isset($_GET['k'])) {
                if (!empty($_GET['k']) && !preg_match('/^[0-9a-zA-Z_]{1,50}$/', $_GET['k'])) {
                    Tools::exitStr("param is error!");
                }
                $kkk = $_GET['k'];
            }
        }

        public static function getDownUrlAndOs($iosUrl, &$downUrl, &$os)
        {
            if ("ios" == Comtools::get_device_type()) {
                $downUrl = $iosUrl;
                $os = "ios";
            }
        }

        public static function exitStr($msg)
        {
            if (self::$conn != null ) {
                self::$conn->close();
            }
            echo $msg;exit;
        }

    }