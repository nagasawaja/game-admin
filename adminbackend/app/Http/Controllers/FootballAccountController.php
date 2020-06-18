<?php

namespace App\Http\Controllers;

use App\Http\Responses\JSON;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use App\Models\Account;

class FootballAccountController extends Controller
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

        $query = Account::singleton()->getFootballAccountQuery($request);
        $accountSelectRaw = 'a.id, a.server_name, a.status, a.email, a.passwd, a.is_clean, ';
        $footballAccountDetailSelectRaw = 'fad.sign_day, fad.error_times, fad.money, fad.gold, fad.black_player, 
        fad.gold_player, fad.silver_player, fad.email_time, fad.update_time, fad.create_time';
        $take = trim($request->input('limit'));
        $skip = (trim($request->input('page')) - 1) * $take;

        $total = $query->count();
        $rows = $query->selectRaw($accountSelectRaw . $footballAccountDetailSelectRaw)->take($take)->skip($skip)->get();

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
        $qiriAccountDetailRaw = 'fad.gold, fad.money, fad.black_player,fad.gold_player,fad.silver_player,fad.sign_day';
        $rows = DB::table('account as a')
            ->leftJoin('football_account_detail as fad', 'a.id', '=', 'fad.account_id')
            ->whereIn('a.status', [1,2])
            ->where('server_name', '=', 'football_master')
            ->when($serverNameRows, function($query) use($serverNameRows) {$query->whereIn('a.server_name', $serverNameRows);})
            ->groupBy(['black_player', 'gold_player', 'gold', 'money', "sign_day"])
            ->orderBy('fad.black_player', 'desc')
            ->orderBy('fad.gold_player', 'desc')
            ->orderBy('fad.gold', 'desc')
            ->orderBy('sign_day', 'desc')
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
        $gold1 = floor(($request->input('gold_1')));
        $gold2 = floor(($request->input('gold_2')));
        $blackPlayer1 = floor(($request->input('black_player_1')));
        $blackPlayer2 = floor(($request->input('black_player_2')));
        $money1 = floor(($request->input('money_1')));
        $money2 = floor(($request->input('money_2')));

        if($getNumber > 50 || $getNumber <=0 || $gold1 <=0 || $money1 <= 0 || $blackPlayer1 <=0 || $serverName == '' || $status != 2) {
            return JSON::error(JSON::E_INTERNAL, '参数不符合标准');
        }

        //帐号数据
        $tmpWhere = [
            ['a.status', '=', $status],
            ['a.server_name', '=', $serverName],
            ['football.black_player', '>=', $blackPlayer1],
            ['football.gold', '>=', $gold1],
            ['football.money', '>=', $money1]
        ];
        $query = DB::table('account as a')
            ->leftJoin('football_account_detail as football', function($join) {$join->on('a.id', '=', 'football.account_id');})
            ->where($tmpWhere)
            ->when($gold2, function($query) use($gold2) {
                $query->where('football.gold', '<=', $gold2);
            })
            ->when($blackPlayer2, function($query) use($blackPlayer2) {
                $query->where('football.black_player', '<=', $blackPlayer2);
            })
            ->when($money2, function($query) use($money2) {
                $query->where('football.money', '<=', $money2);
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
                . ',黑球:' . $blackPlayer1 . '-' . $blackPlayer2
                . ',金币:' . $gold1 .'-'. $gold2,
            'content' => $accountStr,
            'create_time' => time(),
            'account_number' => count($rows)
        ];
        $id = DB::table('football_sold_out_account')->insertGetId($insertData);

        DB::table('account')->whereIn('id', $idRows)->update(['status' => 3]);

        return JSON::ok([
            'id' => $id
        ]);
    }

    //已卖出帐号详细
    public function soldOutAccountDetail(Request $request)
    {
        $id = $request->input('id');
        $rows = DB::table('football_sold_out_account')->where('id', '=', $id)->first();
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

        $query = DB::table('football_sold_out_account')
            ->selectRaw('id, title, create_time, account_number');
        $total = $query->count();
        $rows = $query->take($take)->skip($skip)->orderBy('id', 'desc')->get();
        return JSON::ok([
            'rows' => $rows,
            'total' => $total
        ]);
    }

    // 重置邮件
    public function resetEmail() {
        $keys = Redis::keys("football*");
        foreach($keys as $k=>$v) {
            Redis::del($v);
        }
        $updateData = [
            'email_time' => 1
        ];
        DB::table('football_account_detail')->update($updateData);
        return JSON::ok([]);
    }
}
