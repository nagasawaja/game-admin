<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class Account extends Model
{
    public function getAccountQuery(Request $request)
    {
        //接收处理参数
        $email = trim($request->input('email'));
        $status = trim($request->input('status'));
        $serverName = trim($request->input('serverName'));
        $getNumber = floor(($request->input('getNumber')));
        $oubo = floor(($request->input('oubo')));
        $signDay = floor(($request->input('signDay')));

        //帐号数据

        $query = DB::table('account as a')
            ->leftJoin('qiri_account_detail as qad', function($join) {$join->on('a.id', '=', 'qad.account_id');})
            ->when($email, function($query) use($email) {$query->where('email', 'like', $email . '%');})
            ->when($oubo, function($query) use($oubo) {$query->where('oubo', '=', $oubo);})
            ->when($signDay, function($query) use($signDay) {$query->where('sign_day', '=', $signDay);})
            ->when($status, function($query) use($status) {$query->where('status', '=', $status);})
            ->when($serverName, function($query) use($serverName) {$query->where('server_name', '=', $serverName);})
            ->when($getNumber, function($query) use($getNumber) {$query->take($getNumber);});

        return $query;
    }

    public function recoverAccount999()
    {
        //update qiri_account_detail as a left join account as b on a.account_id = b.id set sign_day = 7,oubo_update_time = 1522274400
        //where sign_day = 999  and b.`status` = 2;
        $where = [
            ['qiri_account_detail.sign_day', '=', 999],
            ['account.status', '=', 2]
        ];
        $updateData = [
            'sign_day' => 10,
            'oubo_update_time' => strtotime(date('Y-m-d 00:00:00', time())) + 27000
        ];
        DB::table('qiri_account_detail')->leftJoin('account', 'qiri_account_detail.account_id', '=', 'account.id')->where($where)->update($updateData);
    }

    public static function singleton()
    {
        static $inst = null;
        if (!$inst) {
            $inst = new static();
        }

        return $inst;
    }
}
