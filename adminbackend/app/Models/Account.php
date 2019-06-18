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
        $oubo1 = floor(($request->input('oubo1')));
        $oubo2 = floor(($request->input('oubo2')));
        $signDay1 = floor(($request->input('sign_day_1')));
        $signDay2 = floor(($request->input('sign_day_2')));
        $errorTimes1 = floor(($request->input('error_times_1')));
        $errorTimes2 = floor(($request->input('error_times_2')));
        $accountId = floor(($request->input('accountId')));

        //帐号数据

        $query = DB::table('account as a')
            ->leftJoin('qiri_account_detail as qad', function($join) {$join->on('a.id', '=', 'qad.account_id');})
            ->when($email, function($query) use($email) {$query->where('email', 'like', $email . '%');})
            ->when($oubo1 != '', function($query) use($oubo1) {$query->where('oubo', '>=', $oubo1);})
            ->when($oubo2 != '', function($query) use($oubo2) {$query->where('oubo', '<=', $oubo2);})
            ->when($status, function($query) use($status) {$query->where('status', '=', $status);})
            ->when($serverName, function($query) use($serverName) {$query->where('server_name', '=', $serverName);})
            ->when($accountId, function($query) use($accountId) {$query->where('a.id', '=', $accountId);})
            ->when($getNumber, function($query) use($getNumber) {$query->take($getNumber);})
            ->when($signDay1 != '', function($query) use($signDay1) {$query->where('sign_day', '>=', $signDay1);})
            ->when($signDay2 != '', function($query) use($signDay2) {$query->where('sign_day', '<=', $signDay2);})
            ->when($errorTimes1 != '', function($query) use($errorTimes1) {$query->where('error_times', '>=', $errorTimes1);})
            ->when($errorTimes2 != '', function($query) use($errorTimes2) {$query->where('error_times', '<=', $errorTimes2);})
            ->where('a.game_id', '=', 6378);

        return $query;
    }

    public function getId5AccountQuery(Request $request)
    {
        //接收处理参数
        $email = trim($request->input('email'));
        $status = trim($request->input('status'));
        $serverName = trim($request->input('serverName'));
        $signDay1 = floor(($request->input('sign_day_1')));
        $signDay2 = floor(($request->input('sign_day_2')));
        $errorTimes1 = floor(($request->input('error_times_1')));
        $errorTimes2 = floor(($request->input('error_times_2')));
        $xianSuo1 = floor(($request->input('xian_suo_1')));
        $xianSuo2 = floor(($request->input('xian_suo_2')));
        $jingHua = floor(($request->input('jing_hua')));
        $accountId = floor(($request->input('accountId')));

        //帐号数据

        $query = DB::table('account as a')
            ->leftJoin('id5_account_detail as iad', function($join) {$join->on('a.id', '=', 'iad.account_id');})
            ->when($email, function($query) use($email) {$query->where('email', 'like', $email . '%');})
            ->when($signDay1 != '', function($query) use($signDay1) {$query->where('sign_day', '>=', $signDay1);})
            ->when($signDay2 != '', function($query) use($signDay2) {$query->where('sign_day', '<=', $signDay2);})
            ->when($errorTimes1 != '', function($query) use($errorTimes1) {$query->where('error_times', '>=', $errorTimes1);})
            ->when($errorTimes2 != '', function($query) use($errorTimes2) {$query->where('error_times', '<=', $errorTimes2);})
            ->when($status, function($query) use($status) {$query->where('status', '=', $status);})
            ->when($serverName, function($query) use($serverName) {$query->where('server_name', '=', $serverName);})
            ->when($accountId, function($query) use($accountId) {$query->where('a.id', '=', $accountId);})
            ->when($xianSuo1 != '', function($query) use($xianSuo1) {$query->where('xian_suo', '>=', $xianSuo1);})
            ->when($xianSuo2 != '', function($query) use($xianSuo2) {$query->where('xian_suo', '<=', $xianSuo2);})
            ->when($jingHua, function($query) use($jingHua) {$query->where('jing_hua', '=', $jingHua);})
            ->where('a.game_id', '=', 6587);

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

    public function recoverOuBo16()
    {
        //update qiri_account_detail as a left join account as b on a.account_id = b.id set sign_day = 7,oubo_update_time = 1522274400
        //where sign_day = 999  and b.`status` = 2;
        $where = [
            ['qiri_account_detail.sign_day', '=', 15],
            ['account.status', '=', 2],
            ['qiri_account_detail.oubo', '=', 16]
        ];
        $rows = DB::table('qiri_account_detail')->leftJoin('account', 'qiri_account_detail.account_id', '=', 'account.id')->selectRaw('qiri_account_detail.account_id, qiri_account_detail.sign_day, qiri_account_detail.oubo, account.email, account.passwd, account.server_name')->where($where)->get();
        foreach($rows as $row) {
            file_put_contents('/tmp/crontab.log', date('Y-m-d H:i:s', time()) . ' ' . json_encode($row) . PHP_EOL, 8);
        }
        $updateData = [
            'sign_day' => 14,
            'oubo_update_time' => strtotime(date('Y-m-d 00:00:00', time())) + 27000,
            'oubo' => 100
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
