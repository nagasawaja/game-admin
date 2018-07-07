<?php

namespace App\Http\Controllers;

use App\Models\Constants;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Responses\JSON;
use App\Models\Admin;

class AgentController extends Controller
{
    /**
     * 代理用户列表
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function userList(Request $request)
    {
        $validRules = [
            'limit' => 'integer|between:1,50',
            'page' => 'integer|min:1',
        ];

        if ($r = $this->validateFail($request, $validRules)) {
            return $r;
        }

        //接收处理参数
        $take = trim($request->input('limit'));
        $skip = (trim($request->input('page')) - 1) * $take;

        $selectRaw = 'users.id as user_id, users.phone, floor(agent.balance / 100) as balance, agent.create_time as agent_create_time, users.user_name';
        $r = DB::table('users')
            ->leftJoin('agent', 'users.id', '=', 'agent.user_id')
            ->selectRaw($selectRaw)
            ->whereNotNull('agent.id');

        $item = $r->take($take)->skip($skip)->get();
        $total = $r->count();

        return JSON::ok(['items' => $item, 'total' => $total]);
    }

    //成为代理
    public function beAAgent(Request $request)
    {
        $validRules = [
            'user_name' => 'bail|required|string|min:1'
        ];

        if ($r = $this->validateFail($request, $validRules)) {
            return $r;
        }

        $userName = trim($request->input('user_name'));

        $where = [
            ['users.user_name', '=', $userName]
        ];
        $usersRow = DB::table('users')->leftJoin('agent', 'users.id', '=', 'agent.user_id')->where($where)->selectRaw('users.id as user_id, agent.id as agent_id')->first();
        if($usersRow == null) {
            return JSON::error(JSON::E_INTERNAL);
        }
        if($usersRow->agent_id == null) {
            //insert
            $insertData = [
                'user_id' => $usersRow->user_id,
                'balance' => 0,
                'create_time' => time(),
                'status' => Constants::STATUS_VALID
            ];
            DB::table('agent')->insertGetId($insertData);
        } else {
            //update
            DB::table('users')->leftJoin('agent', 'users.id', '=', 'agent.user_id')->where('users.user_name', '=', $userName)->update(['agent.status' => Constants::STATUS_VALID]);
        }

        return JSON::ok();
    }

    //激活码列表
    public function activationCodeList(Request $request)
    {
        $validRules = [
            'limit' => 'integer|between:1,50',
            'page' => 'integer|min:0',
        ];

        if ($r = $this->validateFail($request, $validRules)) {
            return $r;
        }

        $take = trim($request->input('limit'));
        $skip = (trim($request->input('page')) - 1) * $take;

        $selectRaw = 'activation_code.code, activation_code.gift_name, floor(activation_code.avail_time / 24 / 60) as avail_time_day, ';
        $selectRaw .= 'activation_code.origin, activation_code.buyer_user_id, 0 as phone, activation_code.status as code_status, ';
        $selectRaw .= 'activation_code.create_time as code_create_time, activation_code.wxid, activation_code.activation_time';
        $r = DB::table('activation_code')
            ->leftJoin('activation_code_standard', function($join) {$join->on('activation_code.id', '=', 'activation_code_standard.activation_code_id');})
            ->groupBy('activation_code.id');
        return JSON::ok([
            'total' => count($r->selectRaw('activation_code.code')->get()),
            'items' => $r->selectRaw($selectRaw)->orderBy('activation_code.id', 'desc')->take($take)->skip($skip)->get()
        ]);
    }

    //保存数据
    public function saveAgentData(Request $request)
    {
        $balance = trim($request->input('balance_up'));
        $userId = trim($request->input('user_id'));


        DB::table('agent')->where('user_id', '=', $userId)->increment('balance', $balance * 100);

        return JSON::ok();
    }
}
