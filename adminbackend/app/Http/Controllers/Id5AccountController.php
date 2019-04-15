<?php

namespace App\Http\Controllers;

use App\Http\Responses\JSON;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Constants;
use App\Models\Account;

class Id5AccountController extends Controller
{
    //帐号统计
    public function statistical(Request $request)
    {
        $serverNameRows = trim($request->input('serverNameRows'));
        $accountSelectRaw = 'a.server_name, count(*) as count, ';
        $qiriAccountDetailRaw = 'id5A.xian_suo, id5A.sign_day, id5A.ling_gan,id5A.jing_hua,id5A.error_times';
        $rows = DB::table('account as a')
            ->leftJoin('id5_account_detail as id5A', 'a.id', '=', 'id5A.account_id')
            ->whereNotIn('a.status', [3,4,5])
            ->where('server_name', '=', '163master')
            ->when($serverNameRows, function($query) use($serverNameRows) {$query->whereIn('a.server_name', $serverNameRows);})
            ->groupBy(['jing_hua', 'xian_suo', 'ling_gan', 'sign_day'])
            ->orderBy('id5A.jing_hua', 'desc')
            ->orderBy('id5A.xian_suo', 'desc')
            ->orderBy('id5A.ling_gan', 'desc')
            ->selectRaw($accountSelectRaw . $qiriAccountDetailRaw)
            ->get();

        //返回数据
        return JSON::ok([
            'rows' => $rows,
        ]);
    }

    //今日帐号统计
    public function todayStatistics(Request $request)
    {
        $updateTime = $request->input('updateTime');
        $todayTimeStamp = strtotime($updateTime);
        $rows = DB::table('id5_account_detail as id5A')
            ->leftJoin('account', 'id5A.account_id', '=', 'account.id')
            ->selectRaw('id5A.xian_suo, id5A.sign_day, id5A.ling_gan,id5A.jing_hua,id5A.error_times,account.status,count(account.id) as count')
            ->where('id5A.login_time', '>=', $todayTimeStamp)
            ->where('id5A.login_time', '<', $todayTimeStamp + 86400)
            ->groupBy(['jing_hua', 'xian_suo', 'ling_gan', 'sign_day'])
            ->orderBy('id5A.jing_hua', 'desc')
            ->orderBy('id5A.xian_suo', 'desc')
            ->orderBy('id5A.ling_gan', 'desc')
            ->get();

        return JSON::ok([
            'rows' => $rows
        ]);
    }
}
