<?php

namespace app\recharge\controller;

use think\Config;
use think\Controller;
use think\Request;
use think\Cache;

class GenBundleFile extends Controller
{
    protected $key;
    protected function _initialize()
    {
        $this->key = Config::get('cache_sign_key');
    }

    public function index()
    {
        $request = Request::instance();
        $bundleId = $request->get('bundle');
        $time = $request->get('time');
        $sign = $request->get('sign');

        $checkSign = md5($bundleId . $time . $this->key );

        if ($sign != $checkSign ) {
            echo "FAIL"; exit;
        }

        $is_succ = $this->genFile($bundleId);
        if (!$is_succ) {
            echo "FAIL"; exit;
        }
        echo "SUCCESS"; exit;

    }

    public function genFile($bundleId)
    {
        try{
            if (empty($bundleId)) return false;
            $genBundleFilePath = str_replace('\\', '/', ROOT_PATH)."sdk/recharge/payPage/";
            $tempFile = $genBundleFilePath."temp.php";
            $fileContent = file_get_contents($tempFile);
            return file_put_contents($genBundleFilePath.$bundleId.".php", $fileContent);
        }catch (\Exception $exception) {
            return false;
        }
    }

}
