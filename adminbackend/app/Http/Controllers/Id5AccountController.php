<?php

namespace App\Http\Controllers;

use App\Http\Responses\JSON;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Constants;
use App\Models\Account;
use function foo\func;

class Id5AccountController extends Controller
{
    //帐号列表
    public function lists(Request $request)
    {
        $valiRules = [
            'limit' => 'required|integer|between:1,50',
            'page' => 'required|integer|min:1',
        ];

        if ($r = $this->validateFail($request, $valiRules)) {
            return $r;
        }

        $query = Account::singleton()->getId5AccountQuery($request);

        $accountSelectRaw = 'a.id, a.server_name, a.status, a.email, a.passwd, a.is_clean, ';
        $id5AccountDetailSelectRaw = 'iad.sign_times, iad.error_times, iad.jing_hua, iad.xian_suo, iad.ling_gan, iad.jing_hua_update_time, iad.email_time, iad.game_update_time, iad.create_time';
        $take = trim($request->input('limit'));
        $skip = (trim($request->input('page')) - 1) * $take;

        $total = $query->count();
        $rows = $query->selectRaw($accountSelectRaw . $id5AccountDetailSelectRaw)->take($take)->skip($skip)->get();

        //返回数据
        return JSON::ok([
            'total' => $total,
            'rows' => $rows,
        ]);
    }

    //帐号统计
    public function statistical(Request $request)
    {
        $serverName = trim($request->input('serverName'));
        $lastUpdateTime = trim($request->input('last_update_time'));
        $extraField = trim($request->input('extra_field'));
        $accountSelectRaw = 'a.server_name, count(*) as count, ';
        $qiriAccountDetailRaw = 'id5A.extra_field, id5A.xian_suo, id5A.sign_times, id5A.ling_gan,id5A.jing_hua,id5A.error_times';
        $rows = DB::table('account as a')
            ->leftJoin('game_id5_account_detail as id5A', 'a.id', '=', 'id5A.account_id')
            ->whereIn('a.status', [1,2])
            ->where('remark', '!=', '777')
            ->when($serverName, function($query) use($serverName) {$query->where('a.server_name', '=', $serverName);})
            ->when($lastUpdateTime, function($query) use($lastUpdateTime) {$query->where('id5A.game_update_time', '>=', strtotime($lastUpdateTime));})
            ->when($extraField, function($query) use($extraField) {$query->where('id5A.extra_field', 'like', $extraField . '%');})
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
        $rows = DB::table('game_id5_account_detail as id5A')
            ->leftJoin('account', 'id5A.account_id', '=', 'account.id')
            ->selectRaw('id5A.xian_suo, id5A.sign_day, id5A.ling_gan,id5A.jing_hua,id5A.error_times,account.status,count(account.id) as count')
            ->where('id5A.game_update_time', '>=', $todayTimeStamp)
            ->where('id5A.game_update_time', '<', $todayTimeStamp + 86400)
            ->groupBy(['jing_hua', 'xian_suo', 'ling_gan', 'sign_day'])
            ->orderBy('id5A.jing_hua', 'desc')
            ->orderBy('id5A.xian_suo', 'desc')
            ->orderBy('id5A.ling_gan', 'desc')
            ->get();

        return JSON::ok([
            'rows' => $rows
        ]);
    }

    //提取帐号
    public function markAccountSoldOut(Request $request)
    {
        //接收处理参数
        $status = trim($request->input('status'));
        $serverName = trim($request->input('serverName'));
        $getNumber = floor(($request->input('getNumber')));
        $jingHua1 = floor(($request->input('jing_hua1')));
        $jingHua2 = floor(($request->input('jing_hua2')));
        $xianSuo1 = floor(($request->input('xian_suo_1')));
        $xianSuo2 = floor(($request->input('xian_suo_2')));
        $extraField = floor(($request->input('extra_field')));
        $lastUpdateTime = trim($request->input('last_update_time'));

        if($getNumber > 500 || $getNumber <=0 || $jingHua1 <=0 || $xianSuo1 <=0 || $serverName == '' || $status != 2) {
            return JSON::error(JSON::E_INTERNAL, '参数不符合标准');
        }

        //接收处理参数
        $status = trim($request->input('status'));
        $serverName = trim($request->input('serverName'));
        $getNumber = floor(($request->input('getNumber')));

        //帐号数据
        $tmpWhere = [
            ['a.status', '=', $status],
            ['a.server_name', '=', $serverName],
            ['iad.jing_hua', '>=', $jingHua1],
            ['iad.xian_suo', '>=', $xianSuo1],
            ['iad.extra_field', 'like', $extraField . '%'],
            ['a.remark', '!=', '777']
        ];
        $query = DB::table('account as a')
            ->leftJoin('game_id5_account_detail as iad', function($join) {$join->on('a.id', '=', 'iad.account_id');})
            ->where($tmpWhere)
            ->when($xianSuo2, function($query) use($xianSuo2) {
                $query->where('iad.xian_suo', '<=', $xianSuo2);
            })
            ->when($jingHua2, function($query) use($jingHua2) {
                $query->where('iad.jing_hua', '<=', $jingHua2);
            })
            ->when($lastUpdateTime, function($query) use($lastUpdateTime) {
                $query->where('iad.game_update_time', '>=', strtotime($lastUpdateTime));
            })
            ->take($getNumber);
        $rows = $query->selectRaw('a.id, a.email, a.passwd')->get();
        $accountStr = '';
        $idRows = [];
        foreach($rows as $row) {
            $idRows[] = $row->id;
            $accountStr .= $row->email . ',' . $row->passwd . "\r\n";
        }
        if(count($idRows) == 0) {
            return JSON::error(JSON::E_INTERNAL, '没有数据');
        }

        $insertData = [
            'title' => date('Y-m-d H:i:s', time()) . ',服务器:' . $serverName . ',精华1:' . $jingHua1 . '-' . $jingHua2 . ',线索:' . $xianSuo1 .'-'. $xianSuo2,
            'content' => $accountStr,
            'create_time' => time(),
            'account_number' => count($rows)
        ];
        $id = DB::table('game_id5_sold_out_account')->insertGetId($insertData);

        DB::table('account')->whereIn('id', $idRows)->update(['status' => 3]);

        return JSON::ok([
            'id' => $id
        ]);
    }

    //已卖出帐号详细
    public function soldOutAccountDetail(Request $request)
    {
        $id = $request->input('id');
        $rows = DB::table('game_id5_sold_out_account')->where('id', '=', $id)->first();
        return JSON::ok([
            'rows' => $rows
        ]);
    }

    //已卖出帐号列表
    public function soldOutAccountList(Request $request)
    {
        $valiRules = [
            'limit' => 'integer|between:1,50',
            'page' => 'integer|min:1',
        ];

        if ($r = $this->validateFail($request, $valiRules)) {
            return $r;
        }


        $take = trim($request->input('limit'));
        $skip = (trim($request->input('page')) - 1) * $take;

        $query = DB::table('game_id5_sold_out_account')
            ->selectRaw('id, title, create_time, account_number');
        $total = $query->count();
        $rows = $query->take($take)->skip($skip)->orderBy('id', 'desc')->get();
        return JSON::ok([
            'rows' => $rows,
            'total' => $total
        ]);
    }
}
