<?php

namespace App\Http\Controllers;

use App\Http\Responses\JSON;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Constants;

class AccountController extends Controller
{

    //帐号列表
    public function lists(Request $request)
    {
        $valiRules = [
            'limit' => 'integer|between:1,50',
            'page' => 'integer|min:1',
        ];

        if ($r = $this->validateFail($request, $valiRules)) {
            return $r;
        }

        //接收处理参数
        $email = trim($request->input('email'));
        $status = trim($request->input('status'));
        $serverName = trim($request->input('serverName'));
        $take = trim($request->input('limit'));
        $skip = (trim($request->input('page')) - 1) * $take;

        //帐号数据
        $accountSelectRaw = 'a.id, a.server_name, a.status, a.email, a.passwd, ';
        $qiriAccountDetailSelectRaw = 'qad.oubo, qad.sign_day';
        $totalQuery = $rowsQuery = DB::table('account as a')
            ->leftJoin('qiri_account_detail as qad', function($join) {$join->on('a.id', '=', 'qad.account_id');})
            ->when($email, function($query) use($email) {$query->where('email', 'like', '%' . $email . '%');})
            ->when($status, function($query) use($status) {$query->where('status', '=', $status);})
            ->when($serverName, function($query) use($serverName) {$query->where('server_name', '=', $serverName);});

        $rows = $rowsQuery->selectRaw($accountSelectRaw . $qiriAccountDetailSelectRaw)->take($take)->skip($skip)->get();
        $total = $totalQuery->count();
        //返回数据
        return JSON::ok([
            'total' => $total,
            'rows' => $rows,
        ]);
    }
}
