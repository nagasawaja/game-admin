<?php

namespace App\Http\Controllers;

use App\Http\Responses\JSON;
use App\Models\Constants;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class SystemConfigController extends Controller
{
    //服务器维护模式菜单
    public function sysConfigMenu(Request $request)
    {
        $sysConfig = Redis::hGetAll(Constants::RK_SYSTEM_CONFIG);
        return JSON::ok($sysConfig);
    }

    //服务器维护模式保存
    public function sysConfigSave(Request $request)
    {
        $params = $request->toArray();
        foreach($params as $key => $value) {
            Redis::hset(Constants::RK_SYSTEM_CONFIG, $key, $value);
        }
        return JSON::ok();
    }

}
