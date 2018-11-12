<?php

namespace App\Http\Controllers;

use App\Http\Responses\JSON;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Constants;
use App\Models\Account;

class AccountController extends Controller
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

        $query = Account::singleton()->getAccountQuery($request);

        $accountSelectRaw = 'a.id, a.server_name, a.status, a.email, a.passwd, ';
        $qiriAccountDetailSelectRaw = 'qad.oubo, qad.sign_day';
        $take = trim($request->input('limit'));
        $skip = (trim($request->input('page')) - 1) * $take;

        $total = $query->count();
        $rows = $query->selectRaw($accountSelectRaw . $qiriAccountDetailSelectRaw)->take($take)->skip($skip)->get();

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
        //select server_name,oubo, sign_day,count(*) from account as a left join qiri_account_detail as qad on a.id = qad.account_id where a.status not in (3,4,5) and a.server_name  in ('jiyi','chunri', 'dahe') group by server_name,sign_day,oubo;
        $accountSelectRaw = 'a.server_name, count(*) as count, ';
        $qiriAccountDetailRaw = 'qad.oubo, qad.sign_day';
        $rows = DB::table('account as a')
            ->leftJoin('qiri_account_detail as qad', 'a.id', '=', 'qad.account_id')
            ->whereNotIn('a.status', [3,4,5])
            ->when($serverNameRows, function($query) use($serverNameRows) {$query->whereIn('a.server_name', $serverNameRows);})
            ->groupBy(['server_name', 'sign_day', 'oubo'])
            ->orderBy('a.server_name', 'desc')
            ->orderBy('qad.sign_day', 'asc')
            ->selectRaw($accountSelectRaw . $qiriAccountDetailRaw)
            ->get();

        //返回数据
        return JSON::ok([
            'rows' => $rows,
        ]);
    }

    //将未卖出的帐号置成已卖出
    public function markAccountSoldOut(Request $request)
    {
        //接收处理参数
        $email = trim($request->input('email'));
        $status = trim($request->input('status'));
        $serverName = trim($request->input('serverName'));
        $getNumber = floor(($request->input('getNumber')));
        $oubo = floor(($request->input('oubo')));
        $signDay = floor(($request->input('signDay')));

        if($getNumber > 50 || $getNumber <=0 || $oubo <=0 || $serverName == '' || $status != 2 || $signDay != 15) {
            return JSON::error(JSON::E_INTERNAL, '参数不符合标准');
        }

        $query = Account::singleton()->getAccountQuery($request);
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
            'title' => date('Y-m-d H:i:s', time()) . ',服务器:' . $serverName . ',欧泊:' . $oubo,
            'content' => $accountStr,
            'create_time' => time(),
            'account_number' => count($rows)
        ];
        $id = DB::table('sold_out_account')->insertGetId($insertData);

        DB::table('account')->whereIn('id', $idRows)->update(['status' => 3]);

        return JSON::ok([
            'id' => $id
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

        $query = DB::table('sold_out_account')
            ->selectRaw('id, title, create_time, account_number');
        $total = $query->count();
        $rows = $query->take($take)->skip($skip)->orderBy('id', 'desc')->get();
        return JSON::ok([
            'rows' => $rows,
            'total' => $total
        ]);
    }

    //已卖出帐号详细
    public function soldOutAccountDetail(Request $request)
    {
        $id = $request->input('id');
        $rows = DB::table('sold_out_account')->where('id', '=', $id)->first();
        return JSON::ok([
            'rows' => $rows
        ]);
    }

    //今日帐号统计
    public function todayStatistics(Request $request)
    {
        $updateTime = $request->input('updateTime');
        $todayTimeStamp = strtotime($updateTime);
        $rows = DB::table('qiri_account_detail')
            ->leftJoin('account', 'qiri_account_detail.account_id', '=', 'account.id')
            ->selectRaw('account.server_name,qiri_account_detail.sign_day,qiri_account_detail.oubo,account.status,count(account.id) as count')
            ->where('qiri_account_detail.update_time', '>=', $todayTimeStamp)
            ->where('qiri_account_detail.update_time', '<', $todayTimeStamp + 86400)
            ->groupBy(['server_name', 'sign_day', 'account.status'])
            ->get();

        return JSON::ok([
            'rows' => $rows
        ]);
    }

    //回收交易猫帐号
    public function recovery(Request $request)
    {
        $accountStr = $request->input('info');
        $accountArrRows = explode("\n", $accountStr);
        $updateWhere = [];
        foreach($accountArrRows as $accountArrRow) {
            $b = explode('	', $accountArrRow);
            $accountStr = $b[1];
            if(strpos($accountStr, '@163.com,') === false) {
                return JSON::error(JSON::E_INTERNAL);
            }
            $updateWhere[] = explode(',', $accountStr)[0];
        }
        $r = DB::table('account')->whereIn('email', $updateWhere)->update(['status' => 2]);
        $r2 = DB::table('qiri_account_detail')->leftJoin('account', 'qiri_account_detail.account_id', '=', 'account.id')->whereIn('email', $updateWhere)->update(['account.status' => 2, 'qiri_account_detail.sign_day' => 14]);

        return JSON::ok();
    }

    //回收15天完成的账号到14天签到，用于服务器更新奖励物品的时候
    public function backTo14(Request $request)
    {
        $where = [
            ['qiri_account_detail.sign_day', '=', 15],
            ['account.status', '=', 2]
        ];
        $updateData = [
            'sign_day' => 14
        ];
        $effectRowCount = DB::table('qiri_account_detail')
            ->leftJoin('account', 'qiri_account_detail.account_id', '=', 'account.id')
            ->where($where)
            ->update($updateData);

        return JSON::ok([
            'effectRowCount' => $effectRowCount
        ]);
    }

    //执行sql语句
    public function querySqlSave(Request $request)
    {
        $sql = $request->input('sql');
        $passwd = $request->input('passwd');
        $originSql = $request->input('origin_sql');
        $type = $request->input('type');

        if($passwd != 'shuadhLiang.123') {
            file_put_contents('/tmp/qiriAdmin.log', date('Y-m-d H:i:s', time()) . ' ' . $passwd . PHP_EOL, 8);
            return json::error('123', 'fuck you');
        }

        $selectRes = '';
        $updateRes = '';

        if($type == 'origin_sql') {
            //更新原生sql
            $updateRes = DB::table('origin_sql')->where('id', '=', 1)->update(['content' => $originSql]);
        } else {
            //执行sql
            $dbQueryType = substr($sql , 0 , 1);

            switch($dbQueryType) {
                case 's':
                    $selectRes = DB::select($sql);
                    break;
                case 'u':
                    $updateRes = DB::update($sql);
                    break;
                default:
                    return json::erro(json::E_INTERNAL);
            }
        }

        return json::ok(['select' => $selectRes, 'update' => $updateRes, 'type' => $type]);

    }

    //执行sql语句
    public function querySqlMenu(Request $request)
    {
        $row = DB::table('origin_sql')->first();

        return json::ok(['origin_sql_content' => $row->content]);
    }
}
