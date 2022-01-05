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
        $oubo = floor(($request->input('oubo')));
        $signTimes1 = floor(($request->input('sign_times_1')));
        $signTimes2 = floor(($request->input('sign_times_2')));
        $signDay1 = floor(($request->input('sign_day_1')));
        $signDay2 = floor(($request->input('sign_day_2')));
        $signDay = floor(($request->input('signDay')));
        $errorTimes1 = floor(($request->input('error_times_1')));
        $errorTimes2 = floor(($request->input('error_times_2')));
        $accountId = floor(($request->input('accountId')));
        $goodsDetailUpdateDate1 = ($request->input('goods_detail_update_date1'));
        $goodsDetailUpdateDate2 = ($request->input('goods_detail_update_date2'));
        $stcCreateDatetimeStart = ($request->input('stc_create_datetime_start'));
        $stcCreateDatetimeEnd = ($request->input('stc_create_datetime_end'));
        $statusList = $request->input('statusList', []);
        $lastUpdateTime = trim($request->input('last_update_time'));


        //帐号数据

        $query = DB::table('account as a')
            ->leftJoin('game_f7_account_detail as qad', function($join) {$join->on('a.id', '=', 'qad.account_id');})
            ->when($email, function($query) use($email) {$query->where('email', 'like', $email . '%');})
            ->when($oubo1 != '', function($query) use($oubo1) {$query->where('oubo', '>=', $oubo1);})
            ->when($oubo2 != '', function($query) use($oubo2) {$query->where('oubo', '<=', $oubo2);})
            ->when($status, function($query) use($status) {$query->where('status', '=', $status);})
            ->when($oubo, function($query) use($oubo) {$query->where('oubo', '=', $oubo);})
            ->when($serverName, function($query) use($serverName) {$query->where('server_name', '=', $serverName);})
            ->when($accountId, function($query) use($accountId) {$query->where('a.id', '=', $accountId);})
            ->when($getNumber, function($query) use($getNumber) {$query->take($getNumber);})
            ->when($signTimes1 != '', function($query) use($signTimes1) {$query->where('sign_times', '>=', $signTimes1);})
            ->when($signTimes2 != '', function($query) use($signTimes2) {$query->where('sign_times', '<=', $signTimes2);})
            ->when($signDay1 != '', function($query) use($signDay1) {$query->where('sign_day', '>=', $signDay1);})
            ->when($signDay2 != '', function($query) use($signDay2) {$query->where('sign_day', '<=', $signDay2);})
            ->when($lastUpdateTime, function($query) use($lastUpdateTime) {
                $query->where('qad.game_update_time', '>=', strtotime($lastUpdateTime));
            })
            ->when($signDay, function($query) use($signDay) {$query->where('sign_day', '=', $signDay);})
            ->when($errorTimes1 != '', function($query) use($errorTimes1) {$query->where('error_times', '>=', $errorTimes1);})
            ->when($errorTimes2 != '', function($query) use($errorTimes2) {$query->where('error_times', '<=', $errorTimes2);})
            ->when($goodsDetailUpdateDate1 != '', function($query) use($goodsDetailUpdateDate1) {$query->where('qad.game_update_time', '>=', strtotime($goodsDetailUpdateDate1));})
            ->when($goodsDetailUpdateDate2 != '', function($query) use($goodsDetailUpdateDate2) {$query->where('qad.game_update_time', '<', strtotime($goodsDetailUpdateDate2)+86400);})
            ->when($stcCreateDatetimeStart != '', function($query) use($stcCreateDatetimeStart) {$query->where('qad.create_time', '>=', strtotime($stcCreateDatetimeStart));})
            ->when($stcCreateDatetimeEnd != '', function($query) use($stcCreateDatetimeEnd) {$query->where('qad.create_time', '<=', strtotime($stcCreateDatetimeEnd));})
            ->when(count($statusList) != 0, function($query) use($statusList) {$query->whereIn('a.status', $statusList);})
            ->where('a.game_id', '=', 6378);

        return $query;
    }

    public function getId5AccountQuery(Request $request)
    {
        //接收处理参数
        $email = trim($request->input('email'));
        $status = trim($request->input('status'));
        $serverName = trim($request->input('serverName'));
        $signTimes1 = floor(($request->input('sign_times_1')));
        $signTimes2 = floor(($request->input('sign_times_2')));
        $errorTimes1 = floor(($request->input('error_times_1')));
        $errorTimes2 = floor(($request->input('error_times_2')));
        $xianSuo1 = floor(($request->input('xian_suo_1')));
        $xianSuo2 = floor(($request->input('xian_suo_2')));
        $jingHua1 = floor(($request->input('jing_hua_1')));
        $jingHua2 = floor(($request->input('jing_hua_2')));
        $accountId = floor(($request->input('accountId')));
        $goodsDetailUpdateDate1 = ($request->input('goods_detail_update_date1'));
        $goodsDetailUpdateDate2 = ($request->input('goods_detail_update_date2'));
        $stcCreateDatetimeStart = ($request->input('stc_create_datetime_start'));
        $stcCreateDatetimeEnd = ($request->input('stc_create_datetime_end'));
        $orderBy = ($request->input('order_by_option'));
        $statusList = $request->input('statusList', []);
        $extraField = $request->input('extra_field');

        //帐号数据

        $query = DB::table('account as a')
            ->when($email, function($query) use($email) {$query->where('email', 'like', $email . '%');})
            ->when($signTimes1 != '', function($query) use($signTimes1) {$query->where('iad.sign_times', '>=', $signTimes1);})
            ->when($signTimes2 != '', function($query) use($signTimes2) {$query->where('sign_times', '<=', $signTimes2);})
            ->when($errorTimes1 != '', function($query) use($errorTimes1) {$query->where('error_times', '>=', $errorTimes1);})
            ->when($errorTimes2 != '', function($query) use($errorTimes2) {$query->where('error_times', '<=', $errorTimes2);})
            ->when($status, function($query) use($status) {$query->where('status', '=', $status);})
            ->when($serverName, function($query) use($serverName) {
                if($serverName=='id5_ios') {
                    $query->leftJoin('game_id5_account_detail as iad', function($join) {$join->on('a.id', '=', 'iad.account_id');});
                } else if ($serverName == 'id5_android') {
                    $query->leftJoin('game_id5_android_account_detail as iad', function($join) {$join->on('a.id', '=', 'iad.account_id');});
                }
                $query->where('server_name', '=', $serverName);
            })
            ->when($accountId, function($query) use($accountId) {$query->where('a.id', '=', $accountId);})
            ->when($xianSuo1 != '', function($query) use($xianSuo1) {$query->where('xian_suo', '>=', $xianSuo1);})
            ->when($xianSuo2 != '', function($query) use($xianSuo2) {$query->where('xian_suo', '<=', $xianSuo2);})
            ->when($jingHua1 != '', function($query) use($jingHua1) {$query->where('jing_hua', '>=', $jingHua1);})
            ->when($jingHua2 != '', function($query) use($jingHua2) {$query->where('jing_hua', '<=', $jingHua2);})
            ->when($goodsDetailUpdateDate1 != '', function($query) use($goodsDetailUpdateDate1) {$query->where('iad.game_update_time', '>=', strtotime($goodsDetailUpdateDate1));})
            ->when($goodsDetailUpdateDate2 != '', function($query) use($goodsDetailUpdateDate2) {$query->where('iad.game_update_time', '<=', strtotime($goodsDetailUpdateDate2));})
            ->when($stcCreateDatetimeStart != '', function($query) use($stcCreateDatetimeStart) {$query->where('iad.create_time', '>=', strtotime($stcCreateDatetimeStart));})
            ->when($stcCreateDatetimeEnd != '', function($query) use($stcCreateDatetimeEnd) {$query->where('iad.create_time', '<=', strtotime($stcCreateDatetimeEnd));})
            ->when($extraField != '', function($query) use($extraField) {
                if($extraField == '123') {
                    $query->whereIn('iad.extra_field', ['nil', '']);
                } else {
                    $query->where('iad.extra_field', 'like', $extraField . '%');
                }

            })
            ->when(count($statusList) != 0, function($query) use($statusList) {$query->whereIn('a.status', $statusList);})
            ->when($serverName == '',function($query) {$query->whereIn('a.server_name', ['163master', '163masterAndroid']);})
            ->when($orderBy != '', function($query) use($orderBy) {$query->orderByRaw("iad.".$orderBy);});


        return $query;
    }

    public function getPesAccountQuery(Request $request)
    {
        //接收处理参数
        $email = trim($request->input('email'));
        $status = trim($request->input('status'));
        $serverName = trim($request->input('serverName'));
        $signTimes1 = floor(($request->input('sign_times_1')));
        $signTimes2 = floor(($request->input('sign_times_2')));
        $errorTimes1 = floor(($request->input('error_times_1')));
        $errorTimes2 = floor(($request->input('error_times_2')));
        $gold1 = floor(($request->input('gold_1')));
        $gold2 = floor(($request->input('gold_2')));
        $money1 = floor(($request->input('money_1')));
        $money2 = floor(($request->input('money_2')));
        $blackPlayer1 = floor(($request->input('black_player_1')));
        $blackPlayer2 = floor(($request->input('black_player_2')));
        $goldPlayer1 = floor(($request->input('gold_player_1')));
        $goldPlayer2 = floor(($request->input('gold_player_2')));
        $silverPlayer1 = floor(($request->input('silver_player_1')));
        $silverPlayer2 = floor(($request->input('silver_player_2')));
        $accountId = floor(($request->input('accountId')));
        $goodsDetailUpdateDate1 = ($request->input('goods_detail_update_date1'));
        $goodsDetailUpdateDate2 = ($request->input('goods_detail_update_date2'));
        $stcCreateDatetimeStart = ($request->input('stc_create_datetime_start'));
        $stcCreateDatetimeEnd = ($request->input('stc_create_datetime_end'));
        $statusList = $request->input('statusList', []);
        //帐号数据

        $query = DB::table('account as a')
            ->when($email, function($query) use($email) {$query->where('email', 'like', $email . '%');})
            ->when($signTimes1 != '', function($query) use($signTimes1) {$query->where('sign_times', '>=', $signTimes1);})
            ->when($signTimes2 != '', function($query) use($signTimes2) {$query->where('sign_times', '<=', $signTimes2);})
            ->when($errorTimes1 != '', function($query) use($errorTimes1) {$query->where('error_times', '>=', $errorTimes1);})
            ->when($errorTimes2 != '', function($query) use($errorTimes2) {$query->where('error_times', '<=', $errorTimes2);})
            ->when($status, function($query) use($status) {$query->where('status', '=', $status);})
            ->when($serverName, function($query) use($serverName) {
                if($serverName=='pes_ios') {
                    $query->leftJoin('game_pes_account_detail as fad', function($join) {$join->on('a.id', '=', 'fad.account_id');});
                } else if ($serverName == 'pes_android') {
                    $query->leftJoin('game_pes_android_account_detail as fad', function($join) {$join->on('a.id', '=', 'fad.account_id');});
                }
                $query->where('server_name', '=', $serverName);
            })
            ->when($accountId, function($query) use($accountId) {$query->where('a.id', '=', $accountId);})
            ->when($gold1 != '', function($query) use($gold1) {$query->where('gold', '>=', $gold1);})
            ->when($gold2 != '', function($query) use($gold2) {$query->where('gold', '<=', $gold2);})
            ->when($money1 != '', function($query) use($money1) {$query->where('money', '>=', $money1);})
            ->when($money2 != '', function($query) use($money2) {$query->where('money', '<=', $money2);})
            ->when($blackPlayer1 != '', function($query) use($blackPlayer1) {$query->where('black_player', '>=', $blackPlayer1);})
            ->when($blackPlayer2 != '', function($query) use($blackPlayer2) {$query->where('black_player', '<=', $blackPlayer2);})
            ->when($goldPlayer1 != '', function($query) use($goldPlayer1) {$query->where('gold_player', '>=', $goldPlayer1);})
            ->when($goldPlayer2 != '', function($query) use($goldPlayer2) {$query->where('gold_player', '<=', $goldPlayer2);})
            ->when($silverPlayer1 != '', function($query) use($silverPlayer1) {$query->where('silver_player', '>=', $silverPlayer1);})
            ->when($silverPlayer2 != '', function($query) use($silverPlayer2) {$query->where('silver_player', '<=', $silverPlayer2);})
            ->when($goodsDetailUpdateDate1 != '', function($query) use($goodsDetailUpdateDate1) {$query->where('fad.game_update_time', '>=', strtotime($goodsDetailUpdateDate1));})
            ->when($goodsDetailUpdateDate2 != '', function($query) use($goodsDetailUpdateDate2) {$query->where('fad.game_update_time', '<', strtotime($goodsDetailUpdateDate2)+86400);})
            ->when($stcCreateDatetimeStart != '', function($query) use($stcCreateDatetimeStart) {$query->where('fad.create_time', '>=', strtotime($stcCreateDatetimeStart));})
            ->when($stcCreateDatetimeEnd != '', function($query) use($stcCreateDatetimeEnd) {$query->where('fad.create_time', '<=', strtotime($stcCreateDatetimeEnd));})
            ->when(count($statusList) != 0, function($query) use($statusList) {$query->whereIn('a.status', $statusList);})
            ->where('a.game_id', '=', 7744);

        return $query;
    }

    // dream account list
    public function getDreamAccountQuery(Request $request)
    {
        //接收处理参数
        $email = trim($request->input('email'));
        $status = trim($request->input('status'));
        $serverName = trim($request->input('serverName'));
        $signDay1 = floor(($request->input('sign_day_1')));
        $signDay2 = floor(($request->input('sign_day_2')));
        $errorTimes1 = floor(($request->input('error_times_1')));
        $errorTimes2 = floor(($request->input('error_times_2')));
        $moJing1 = floor(($request->input('mo_jing_1')));
        $moJing2 = floor(($request->input('mo_jing_2')));
        $shengMoQuan1 = floor(($request->input('sheng_mo_quan_1')));
        $shengMoQuan2 = floor(($request->input('sheng_mo_quan_2')));
        $accountId = floor(($request->input('accountId')));
        $goodsDetailUpdateDate1 = ($request->input('goods_detail_update_date1'));
        $goodsDetailUpdateDate2 = ($request->input('goods_detail_update_date2'));
        $stcCreateDatetimeStart = ($request->input('stc_create_datetime_start'));
        $stcCreateDatetimeEnd = ($request->input('stc_create_datetime_end'));
        $statusList = $request->input('statusList');

        //帐号数据

        $query = DB::table('account as a')
            ->leftJoin('game_mz_account_detail as d', function($join) {$join->on('a.id', '=', 'd.account_id');})
            ->when($email, function($query) use($email) {$query->where('email', 'like', $email . '%');})
            ->when($signDay1 != '', function($query) use($signDay1) {$query->where('sign_day', '>=', $signDay1);})
            ->when($signDay2 != '', function($query) use($signDay2) {$query->where('sign_day', '<=', $signDay2);})
            ->when($errorTimes1 != '', function($query) use($errorTimes1) {$query->where('error_times', '>=', $errorTimes1);})
            ->when($errorTimes2 != '', function($query) use($errorTimes2) {$query->where('error_times', '<=', $errorTimes2);})
            ->when($status, function($query) use($status) {$query->where('status', '=', $status);})
            ->when($serverName, function($query) use($serverName) {$query->where('server_name', '=', $serverName);})
            ->when($accountId, function($query) use($accountId) {$query->where('a.id', '=', $accountId);})
            ->when($moJing1 != '', function($query) use($moJing1) {$query->where('mo_jing', '>=', $moJing1);})
            ->when($moJing2 != '', function($query) use($moJing2) {$query->where('mo_jing', '<=', $moJing2);})
            ->when($shengMoQuan1 != '', function($query) use($shengMoQuan1) {$query->where('sheng_mo_quan', '>=', $shengMoQuan1);})
            ->when($shengMoQuan2 != '', function($query) use($shengMoQuan2) {$query->where('sheng_mo_quan', '<=', $shengMoQuan2);})
            ->when($goodsDetailUpdateDate1 != '', function($query) use($goodsDetailUpdateDate1) {$query->where('d.game_update_time', '>=', strtotime($goodsDetailUpdateDate1));})
            ->when($goodsDetailUpdateDate2 != '', function($query) use($goodsDetailUpdateDate2) {$query->where('d.game_update_time', '<', strtotime($goodsDetailUpdateDate2)+86400);})
            ->when($stcCreateDatetimeStart != '', function($query) use($stcCreateDatetimeStart) {$query->where('d.create_time', '>=', strtotime($stcCreateDatetimeStart));})
            ->when($stcCreateDatetimeEnd != '', function($query) use($stcCreateDatetimeEnd) {$query->where('d.create_time', '<=', strtotime($stcCreateDatetimeEnd));})
            ->when(count($statusList) != 0, function($query) use($statusList) {$query->whereIn('a.status', $statusList);})
            ->where('a.game_id', '=', 7266);

        return $query;
    }
    public function recoverAccount999()
    {
        //update game_f7_account_detail as a left join account as b on a.account_id = b.id set sign_day = 7,oubo_update_time = 1522274400
        //where sign_day = 999  and b.`status` = 2;
        $where = [
            ['game_f7_account_detail.sign_day', '=', 999],
            ['account.status', '=', 2]
        ];
        $updateData = [
            'sign_day' => 10,
            'oubo_update_time' => strtotime(date('Y-m-d 00:00:00', time())) + 27000
        ];
        DB::table('game_f7_account_detail')->leftJoin('account', 'game_f7_account_detail.account_id', '=', 'account.id')->where($where)->update($updateData);
    }

    public function recoverOuBo16()
    {
        //update game_f7_account_detail as a left join account as b on a.account_id = b.id set sign_day = 7,oubo_update_time = 1522274400
        //where sign_day = 999  and b.`status` = 2;
        $where = [
            ['game_f7_account_detail.sign_day', '=', 15],
            ['account.status', '=', 2],
            ['game_f7_account_detail.oubo', '=', 16]
        ];
        $rows = DB::table('game_f7_account_detail')->leftJoin('account', 'game_f7_account_detail.account_id', '=', 'account.id')->selectRaw('game_f7_account_detail.account_id, game_f7_account_detail.sign_day, game_f7_account_detail.oubo, account.email, account.passwd, account.server_name')->where($where)->get();
        foreach($rows as $row) {
            file_put_contents('/tmp/crontab.log', date('Y-m-d H:i:s', time()) . ' ' . json_encode($row) . PHP_EOL, 8);
        }
        $updateData = [
            'sign_day' => 14,
            'oubo_update_time' => strtotime(date('Y-m-d 00:00:00', time())) + 27000,
            'oubo' => 100
        ];
        DB::table('game_f7_account_detail')->leftJoin('account', 'game_f7_account_detail.account_id', '=', 'account.id')->where($where)->update($updateData);
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
