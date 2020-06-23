<?php

namespace App\Http\Controllers;

use App\Http\Responses\JSON;
use App\Models\Constants;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use App\Models\Mao;

class taobaoController extends Controller
{
    public function orderLists(Request $request) {
        $orderId = trim($request->input('orderId'));
        $email = trim($request->input('email'));
        $a = DB::table('taobao_order as o')
            ->when($email, function($query) use($email) {
                $query->join('taobao_order_detail as od', 'o.order_id', '=', 'od.order_id');
                if($email != '') {
                    $query->where('od.email', '=', $email);
                }
            })
            ->when($orderId, function ($query) use($orderId) {
                $query->where('o.order_id', '=', $orderId);
            })
            ->get();


        return json::ok(['rows' => $a]);
    }

    public function createOrder(Request $request) {
        $orderId = trim($request->input('orderId'));
        $easyContent = trim($request->input('easyContent'));
        $originContent = trim($request->input('originContent'));
        $description = trim($request->input('description'));

        if($orderId == "" || $easyContent == "" || $originContent == "" || $description == "") {
            return JSON::error(JSON::E_INVALID_PARAM, '1P');
        }

        $b = explode("\n", $easyContent);
        $c = explode("@", $easyContent);
        if(count($b) + 1 != count($c)) {
            return JSON::error(JSON::E_INVALID_PARAM, '2P');
        }

        $insertData = [
            'origin_content' => $originContent,
            'create_time' => time(),
            'order_id' => $orderId,
            'easy_content' => $easyContent,
            'description' => $description
        ];
        $d = DB::table('taobao_order')->insert($insertData);
        if($d == false) {
            return JSON::error(JSON::E_INVALID_PARAM, '3P');
        }

        foreach($b as $v) {
            $account = explode(',', $v);
            $insertData = [
                'email' => $account[0],
                'password' => $account[1],
                'status' => 3,
                'order_id' => $orderId
            ];
            DB::table('taobao_order_detail')->insert($insertData);
        }
    }

    public function forbidStatistical(Request $request) {
        $a = DB::table('taobao_order as o')
            ->join('taobao_order_detail as od', 'o.order_id', '=', 'od.order_id')
            ->where('od.status', '=', 4)
            ->groupBy('o.order_id')
            ->selectRaw('count(*) as forbidCount, o.order_id as orderId, description')
            ->get();
        //返回数据
        return JSON::ok([
            'rows' => $a,
        ]);
    }


    public function markAccountSoldOut(Request $request) {
        $orderId = trim($request->input('orderId'));

        $rows = DB::table('taobao_order as o')
            ->join('taobao_order_detail as od', 'o.order_id', '=', 'od.order_id')
            ->where('o.order_id', '=', $orderId)
            ->where('od.status', '=', 4)
            ->selectRaw('od.id, email, password, status')
            ->get();

        $accountStr = '';
        $idRows = [];
        foreach($rows as $row) {
            $idRows[] = $row->id;
            $accountStr .= $row->email . ',' . $row->password . "\r\n";
        }
        if(count($idRows) == 0) {
            return JSON::error(JSON::E_INTERNAL, '没有数据');
        }

        $insertData = [
            'title' => date('Y-m-d H:i:s', time()) . '----orderId:' . $orderId,
            'content' => $accountStr,
            'create_time' => time(),
            'account_number' => count($rows),
            'order_id' => $orderId,
        ];
        $id = DB::table('taobao_sold_out_account')->insertGetId($insertData);

        DB::table('taobao_order_detail')->whereIn('id', $idRows)->update(['status' => 10]);

        //返回数据
        return JSON::ok([
            'id' => $id,
        ]);
    }

    public function soldOutAccountDetail(Request $request) {
        $id = $request->input('id');
        $rows = DB::table('taobao_sold_out_account')->where('id', '=', $id)->first();
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

    public function createForbidEmail(Request $request) {
        $email = trim($request->input('email'));
        if($email== '') {
            return JSON::error(JSON::E_INVALID_PARAM);
        }

        //
        $a = DB::table('taobao_order_detail')
            ->where('email', '=', $email)
            ->first();
        if($a==null) {
            return JSON::error(JSON::E_INVALID_PARAM, 'email不存在，或许是订单还没收集！建议联系帅哥');
        }
        if($a->status == 4) {
            return JSON::error(JSON::E_INVALID_PARAM, 'email已经是待换号名单中，不用再添加');
        }
        if($a->status == 10) {
            return JSON::error(JSON::E_INVALID_PARAM, 'email已经更换成功，不用再添加');
        }
        if($a->status != 3) {
            return JSON::error(JSON::E_INVALID_PARAM, 'errrrr!!!!');
        }
        //
        DB::table('taobao_order_detail')
            ->where('email', '=', $email)
            ->update(['status' => 4]);

        return JSON::ok(['ojbk'=>'ojbk']);
    }

    public function soldOut(Request $request) {
        $valiRules = [
            'limit' => 'integer|between:1,50',
            'page' => 'integer|min:1',
        ];

        if ($r = $this->validateFail($request, $valiRules)) {
            return $r;
        }

        $take = trim($request->input('limit'));
        $skip = (trim($request->input('page')) - 1) * $take;

        $query = DB::table('taobao_sold_out_account')
            ->selectRaw('id, title, create_time, account_number,order_id');
        $total = $query->count();
        $rows = $query->take($take)->skip($skip)->orderBy('id', 'desc')->get();
        return JSON::ok([
            'rows' => $rows,
            'total' => $total
        ]);
    }
}