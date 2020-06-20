<?php

    class Tools
    {
        protected static $conn = null;
        protected static $iadConfig = null;

        public static function getConfig()
        {
            if (self::$iadConfig == null) {
                self::$iadConfig = include '../Common/code/iad_config.php';
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

        public static function findUserTemplatesData($data, $user_template_id)
        {
            $userTemplateData = [];
            if ($data['ios_channelid'] == 0 || $data['android_channelid'] == 0) {
                $sql = "SELECT ios_channelid, android_channelid FROM user_templates where id = " . $user_template_id . " limit 1";
                $userTemplateResult = self::getDdConn()->query($sql);
                if ($userTemplateResult->num_rows > 0) {
                    // 输出数据
                    $userTemplateData = $userTemplateResult->fetch_assoc();
                }
            }

            return $userTemplateData;
        }

        public static function findUserPagesData($userPageId)
        {
            $sql = "SELECT id, ads_id, ios_channelid, android_channelid FROM user_pages where id = " . $userPageId . " limit 1";
            $result = self::getDdConn()->query($sql);
            $data = [];
            if ($result->num_rows > 0) {
                // 输出数据
                $data = $result->fetch_assoc();
            }

            return $data;
        }

        public static function arrangeData($data, $kkk, $getParam, $userTemplateData, $ip)
        {
            $saveData = [];
            $saveData['ads_id'] = "'" .$data['ads_id'] . $kkk . "'";
            $saveData['user_template_id'] = (int)$getParam['utpId'];
            $saveData['template_id'] = (int)$getParam['tpId'];

            $saveData['channel_id'] = 0;
            if (self::isIOS()) {
                $saveData['channel_id'] = $data['ios_channelid']  ?: $userTemplateData['ios_channelid'];
            } else {
                $saveData['channel_id'] = $data['android_channelid']  ?: $userTemplateData['android_channelid'];
            }

            $saveData['ip'] = "'" . $ip . "'";
            $saveData['user_agent'] = "'" . $_SERVER['HTTP_USER_AGENT'] . "'";
            $saveData['device_id'] = '\'\'';
            $saveData['callback_url'] = '\'\'';
            $saveData['referer'] = "'" . $_SERVER['HTTP_REFERER'] . "'";
            $saveData['reg_count'] = 0;
            $saveData['add_time'] = time();
            $saveData['add_d'] = (int)date('Ymd');

            return $saveData;
        }

        public static function recordPageLogs($saveData)
        {
            $insertFields = '';
            foreach ($saveData as $k => $v) {
                $insertFields .= "`" . $k . "`, ";
            }
            $insertFields = substr($insertFields, 0 , -2); //有空格和逗号两个字符要去掉
            $insertValues = implode(", ", $saveData);
            $insertSql = "insert into page_logs($insertFields) values ($insertValues)";
            self::getDdConn()->query($insertSql);
        }

        public static function updatePageStats($id)
        {
            $whereStr = " where type = 'user_pages' and source_id = " . $id . " and opt_d = " . (int)date('Ymd');
            $pageStatSqlUpdate = "update page_stats set hit_count = hit_count + 1" . $whereStr;
            self::getDdConn()->query($pageStatSqlUpdate);
        }

        public static function isIOS()
        {
            if(strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone')||strpos($_SERVER['HTTP_USER_AGENT'], 'iPad')){
                return true;
            }else if(strpos($_SERVER['HTTP_USER_AGENT'], 'Android')){
                return false;
            }
            return false;
        }

        public static function get_client_ip()
        {
            $unknown = 'unknown';
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] && strcasecmp($_SERVER['HTTP_X_FORWARDED_FOR'], $unknown)) {
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], $unknown)) {
                $ip = $_SERVER['REMOTE_ADDR'];
            }

            return $ip;
        }

        public static function isRepeatRequest($template_id, $user_template_id, $userPageId, $ip)
        {

            $iadConfigN = self::getConfig();
            //如果redis服务连接失败，catch异常，并返回false.
            try {
                //连接本地的 Redis 服务
                $redis = new Redis();
                $redis->connect($iadConfigN['redis_host'], $iadConfigN['redis_port'], 2);
                $redis->auth($iadConfigN['redis_password']);
                $key = "IAd_Stat:Tools:$template_id:$user_template_id:$userPageId:$ip";
                if ($redis->get($key)) {
                    return true;
                }
                $redis->set($key, "is_request", 60);
            } catch (RedisException $e) {
                return false;
            }

            return false;
        }

        public static function getKKK($getParam, &$kkk)
        {
            if(isset($getParam['k'])) {
                if (!empty($getParam['k']) && !preg_match('/^[0-9a-zA-Z_]{1,50}$/', $getParam['k'])) {
                    Tools::exitStr("param is error!");
                }
                $kkk = $getParam['k'];
            }
        }

        public static function exitStr($str)
        {
            if (self::$conn != null ) {
                self::$conn->close();
            }
            echo $str;exit;
        }
    }