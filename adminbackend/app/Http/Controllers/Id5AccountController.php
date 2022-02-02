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

        $accountSelectRaw = 'a.id, a.server_name, a.status, a.email, a.passwd, a.is_clean, a.remark, a.device_name, a.idcard, ';
        $id5AccountDetailSelectRaw = 'iad.extra_field, iad.sign_times, iad.error_times, iad.jing_hua, iad.xian_suo, iad.ling_gan, iad.game_update_time, iad.create_time';
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
        $qiriAccountDetailRaw = 'id5A.extra_field, id5A.xian_suo, id5A.sign_times,0 as ling_gan,id5A.jing_hua,id5A.error_times';
        $rows = DB::table('account as a')
            ->where('a.status', '=', 2)
            ->where('id5A.jing_hua', '>=', 10)
            ->when($serverName, function($query) use($serverName) {
                if($serverName == 'id5_ios') {
                    $query->join('game_id5_account_detail as id5A', 'a.id', '=', 'id5A.account_id', 'inner');
                } else if($serverName == 'id5_android') {
                    $query->join('game_id5_android_account_detail as id5A', 'a.id', '=', 'id5A.account_id', 'inner');
                }
                $query->where('a.server_name', '=', $serverName);
            })
            ->when($lastUpdateTime, function($query) use($lastUpdateTime) {$query->where('id5A.game_update_time', '>=', strtotime($lastUpdateTime));})
            ->when($extraField != '', function($query) use($extraField) {
                if($extraField == '123') {
                    $query->whereIn('id5A.extra_field', ['nil', '']);
                } else {
                    $query->where('id5A.extra_field', 'like', $extraField . '%');
                }

            })            ->groupBy(['jing_hua', 'xian_suo'])
            ->orderBy('id5A.jing_hua', 'desc')
            ->orderBy('id5A.xian_suo', 'desc')
            ->selectRaw($accountSelectRaw . $qiriAccountDetailRaw)
            ->get();

        //返回数据
        return JSON::ok([
            'rows' => $rows,
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
        $extraField = trim(($request->input('extra_field')));
        $lastUpdateTime = trim($request->input('last_update_time'));

        if($getNumber > 500 || $getNumber <=0 || $jingHua1 <=0 || $xianSuo1 <=0 || $serverName == '' || $status != 2 || $extraField == '') {
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
            ['iad.xian_suo', '>=', $xianSuo1]
        ];
        $query = DB::table('account as a')
            ->where($tmpWhere)
            ->when($serverName, function($query) use($serverName) {
                if($serverName == 'id5_ios') {
                    $query->join('game_id5_account_detail as iad', 'a.id', '=', 'iad.account_id', 'inner');
                } else if($serverName == 'id5_android') {
                    $query->join('game_id5_android_account_detail as iad', 'a.id', '=', 'iad.account_id', 'inner');
                }
            })
            ->when($xianSuo2, function($query) use($xianSuo2) {
                $query->where('iad.xian_suo', '<=', $xianSuo2);
            })
            ->when($jingHua2, function($query) use($jingHua2) {
                $query->where('iad.jing_hua', '<=', $jingHua2);
            })
            ->when($lastUpdateTime, function($query) use($lastUpdateTime) {
                $query->where('iad.game_update_time', '>=', strtotime($lastUpdateTime));
            })
            ->when($extraField != '', function($query) use($extraField) {
                if($extraField == '123') {
                    $query->whereIn('iad.extra_field', ['nil', '']);
                } else {
                    $query->where('iad.extra_field', 'like', $extraField . '%');
                }

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
            'title' => date('Y-m-d H:i:s', time()) . ',服务器:' . $serverName . ',精华:' . $jingHua1 . '-' . $jingHua2 . ',线索:' . $xianSuo1 .'-'. $xianSuo2 . ',extraField:' . $extraField,
            'content' => $accountStr,
            'create_time' => time(),
            'account_number' => count($rows)
        ];
        $id = DB::table('game_id5_sold_out_account')->insertGetId($insertData);

        DB::table('account')->whereIn('id', $idRows)->update(['status' => 3]);

        if($serverName == 'id5_ios') {
            DB::table('game_id5_account_detail as iad')->whereIn('account_id', $idRows)->update(['game_status' => 3]);
        } else if($serverName == 'id5_android') {
            DB::table('game_id5_android_account_detail')->whereIn('account_id', $idRows)->update(['game_status' => 3]);
        }

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
