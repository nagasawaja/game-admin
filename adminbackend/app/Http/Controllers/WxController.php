<?php

namespace App\Http\Controllers;

use App\Http\Responses\JSON;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Constants;

class WxController extends Controller
{
    /**
     * 管理用户
     * @param $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
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
        $userId = trim($request->input('user_id'));
        $number = trim($request->input('phone'));
        $userType = trim($request->input('user_type'));
        $registerTime = trim($request->input('register_time'));
        $loginTime = $request->input('login_time');
        $stateId = $request->input('state_id');
        $userName = $request->input('user_name');
        $stateIdOperator = '=';
        if($stateId !== '') {
            if($stateId < 0) {
                $stateIdOperator = '!=';
                $stateId = Constants::IPAD_STATE_CYCLE;
            }
        }

        $take = trim($request->input('limit'));
        $skip = (trim($request->input('page')) - 1) * $take;

        //用户基本数据
        $usersRaw = 'users.id as user_id, users.phone, users.user_name, ';
        $wxServicesRaw = 'IFNULL(GROUP_CONCAT(wx_services.end_time), "") as service_end_time, ';
        $servicesRaw = 'IFNULL(GROUP_CONCAT(services.service_name), "") as service_name, ';
        $wxAccountRaw = 'wx_account.nickname as nickname, ';
        $usersWxAccountRaw = 'users_wx_account.wxid as wx_id, users_wx_account.remark, users_wx_account.create_time as wx_id_create_time, users_wx_account.state as wx_state, users_wx_account.login_time';
        $userWxAccountRows = DB::table('users_wx_account')
            ->when($userId, function($query) use($userId) {return $query->where('users.id', '=', $userId);})
            ->when($number, function($query) use($number) {return $query->where('users.phone', 'like', '%' . $number. '%');})
            ->when($userName, function($query) use($userName) {return $query->where('users.user_name', 'like', '%' . $userName. '%');})
            ->when($userType, function($query) use($userType) {return $query->where('users.type', '=', $userType);})
            ->when($registerTime, function($query) use($registerTime) {return $query->whereDate('create_time', $registerTime);})
            ->when($loginTime, function($query) use($loginTime) {
                $startTime = strtotime($loginTime[0]);
                $endTime = strtotime($loginTime[1]);
                $query->where('users_wx_account.login_time', '>=', $startTime)->where('users_wx_account.login_time', '<', $endTime+ 86400);
            })            ->when($stateId, function($query) use($stateId, $stateIdOperator) {return $query->where('users_wx_account.state', $stateIdOperator, $stateId);})
            ->selectRaw($usersRaw . $wxServicesRaw . $servicesRaw . $wxAccountRaw . $usersWxAccountRaw)
            ->leftJoin('users', 'users.id', 'users_wx_account.user_id')
            ->leftJoin('wx_services', 'wx_services.wxid', '=', 'users_wx_account.wxid')
            ->leftJoin('services', 'services.id', '=', 'wx_services.service_id')
            ->leftJoin('wx_account', 'wx_account.wxid', '=', 'users_wx_account.wxid')
            ->groupBy('users_wx_account.wxid','users.id')
            ->take($take)
            ->skip($skip)
            ->orderByDesc('users_wx_account.create_time')
            ->get();

        $raw = 'count(*) as total, sum(case when users_wx_account.state=6 then 1 else 0 end) as online_total, sum(case when users_wx_account.state!=6 then 1 else 0 end) as offline_total';
        $totalCount = DB::table('users_wx_account')
            ->when($userId, function($query) use($userId) {return $query->where('users.id', '=', $userId);})
            ->when($number, function($query) use($number) {return $query->where('users.phone', 'like', '%' . $number. '%');})
            ->when($userType, function($query) use($userType) {return $query->where('users.type', '=', $userType);})
            ->when($userName, function($query) use($userName) {return $query->where('users.user_name', 'like', '%' . $userName. '%');})
            ->when($loginTime, function($query) use($loginTime) {
                $startTime = strtotime($loginTime[0]);
                $endTime = strtotime($loginTime[1]);
                $query->where('users_wx_account.login_time', '>=', $startTime)->where('users_wx_account.login_time', '<', $endTime+ 86400);
            })
            ->when($registerTime, function($query) use($registerTime) {return $query->whereDate('create_time', $registerTime);})
            ->when($stateId, function($query) use($stateId, $stateIdOperator) {return $query->where('users_wx_account.state', $stateIdOperator, $stateId);})
            ->leftJoin('users', 'users.id', 'users_wx_account.user_id')
            ->selectRaw(DB::raw($raw))
            ->first();

        //返回数据
        return JSON::ok([
            'total' => $totalCount,
            'items' => $userWxAccountRows,
        ]);
    }
}
