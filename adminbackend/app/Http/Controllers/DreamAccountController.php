<?php

namespace App\Http\Controllers;

use App\Http\Responses\JSON;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Account;

class DreamAccountController extends Controller
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

        $query = Account::singleton()->getDreamAccountQuery($request);
        $accountSelectRaw = 'a.id, a.server_name, a.status, a.email, a.passwd, a.is_clean, ';
        $dreamAccountDetailSelectRaw = 'd.sign_day, d.error_times, d.mo_jing, d.sheng_mo_quan, a.device_name as idcard_name, a.idcard as idcard_num';
        $take = trim($request->input('limit'));
        $skip = (trim($request->input('page')) - 1) * $take;

        $total = $query->count();
        $rows = $query->selectRaw($accountSelectRaw . $dreamAccountDetailSelectRaw)->take($take)->skip($skip)->get();

        //返回数据
        return JSON::ok([
            'total' => $total,
            'rows' => $rows,
        ]);
    }

    //帐号统计
    public function statistical(Request $request)
    {
        $serverNameRows = trim($request->input('serverNameRows'));
        $accountSelectRaw = 'a.server_name, count(*) as count, ';
        $qiriAccountDetailRaw = 'd.sign_day, d.sheng_mo_quan, d.mo_jing';
        $rows = DB::table('account as a')
            ->leftJoin('dream_account_detail as d', 'a.id', '=', 'd.account_id')
            ->whereIn('a.status', [1,2])
            ->where('server_name', '=', 'dream_master')
            ->when($serverNameRows, function($query) use($serverNameRows) {$query->whereIn('a.server_name', $serverNameRows);})
            ->groupBy(['sheng_mo_quan', 'mo_jing', 'sign_day'])
            ->orderBy('d.sheng_mo_quan', 'desc')
            ->orderBy('d.mo_jing', 'desc')
            ->orderBy('d.sign_day', 'desc')
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
        $shengMoQuan1 = floor(($request->input('sheng_mo_quan_1')));
        $shengMoQuan2 = floor(($request->input('sheng_mo_quan_2')));
        $moJing1 = floor(($request->input('mo_jing_1')));
        $moJing2 = floor(($request->input('mo_jing_2')));

        if($getNumber > 50 || $getNumber <=0 || $shengMoQuan1 <=0 || $moJing1 <=0 || $serverName == '' || $status != 2) {
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
            ['d.sheng_mo_quan', '>=', $shengMoQuan1],
            ['d.mo_jing', '>=', $moJing1]
        ];
        $query = DB::table('account as a')
            ->leftJoin('dream_account_detail as d', function($join) {$join->on('a.id', '=', 'd.account_id');})
            ->where($tmpWhere)
            ->when($shengMoQuan2, function($query) use($shengMoQuan2) {
                $query->where('d.sheng_mo_quan', '<=', $shengMoQuan2);
            })
            ->when($moJing2, function($query) use($moJing2) {
                $query->where('d.mo_jing', '<=', $moJing2);
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
            'title' => date('Y-m-d H:i:s', time()) . ',服务器:' . $serverName
                . ',圣魔券:' . $shengMoQuan1. '-' . $shengMoQuan2
                . ',魔晶:' . $moJing1 .'-'. $moJing2,
            'content' => $accountStr,
            'create_time' => time(),
            'account_number' => count($rows)
        ];
        $id = DB::table('dream_sold_out_account')->insertGetId($insertData);

        DB::table('account')->whereIn('id', $idRows)->update(['status' => 3]);

        return JSON::ok([
            'id' => $id
        ]);
    }

    //已卖出帐号详细
    public function soldOutAccountDetail(Request $request)
    {
        $id = $request->input('id');
        $rows = DB::table('dream_sold_out_account')->where('id', '=', $id)->first();
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

        $query = DB::table('dream_sold_out_account')
            ->selectRaw('id, title, create_time, account_number');
        $total = $query->count();
        $rows = $query->take($take)->skip($skip)->orderBy('id', 'desc')->get();
        return JSON::ok([
            'rows' => $rows,
            'total' => $total
        ]);
    }
}
