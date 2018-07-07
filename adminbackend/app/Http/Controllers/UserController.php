<?php

namespace App\Http\Controllers;

use App\Http\Responses\JSON;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Constants;

class UserController extends Controller
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
        $registerTime = $request->input('register_time');
        $loginTime = $request->input('login_time');
        $take = trim($request->input('limit'));
        $skip = (trim($request->input('page')) - 1) * $take;
        //用户基本数据
        $usersRaw = 'users.id, users.phone, users.coins, users.register_time, users.user_name, users.login_time, ';
        $usersCoinBillRaw = 'sum(ifnull(users_coin_bill.coins, 0)) as total_recharge_coins, ';
        $usersWxAccountRaw = 'ifnull(user_wx_account, 0) as user_wx_account';

        $userRows = DB::table('users')
            ->when($userId, function($query) use($userId) {return $query->where('users.id', '=', $userId);})
            ->when($number, function($query) use($number) {return $query->where('users.phone', 'like', '%' . $number. '%');})
            ->when($userType, function($query) use($userType) {return $query->where('users.type', '=', $userType);})
            ->when($registerTime, function($query) use($registerTime) {
                $startTime = strtotime($registerTime[0]);
                $endTime = strtotime($registerTime[1]);
                $query->where('register_time', '>=', $startTime)->where('register_time', '<', $endTime+ 86400);
            })
            ->when($loginTime, function($query) use($loginTime) {
                $startTime = strtotime($loginTime[0]);
                $endTime = strtotime($loginTime[1]);
                $query->where('users.login_time', '>=', $startTime)->where('users.login_time', '<', $endTime+ 86400);
            })
            ->skip($skip)
            ->take($take)
            ->orderByDesc('users.id')
            ->leftJoin(DB::raw('(select count(*) as user_wx_account, user_id from users_wx_account group by user_id) as uwa_temp'), 'uwa_temp.user_id', '=', 'users.id')
            ->selectRaw($usersRaw . $usersCoinBillRaw . $usersWxAccountRaw)
            ->leftJoin('users_coin_bill', function($join) {$join->on('users_coin_bill.user_id', '=', 'users.id')->where('users_coin_bill.type', '=', Constants::TYPE_COIN_BILL_RECHARGE)->where('users_coin_bill.symbol', '=', Constants::OTHER_SYMBOL_ADD);})
            ->groupBy('users.id')
            ->get();

        $totalCount = DB::table('users')
            ->when($userId, function($query) use($userId) {return $query->where('users.id', '=', $userId);})
            ->when($number, function($query) use($number) {return $query->where('users.phone', 'like', '%' . $number. '%');})
            ->when($userType, function($query) use($userType) {return $query->where('users.type', '=', $userType);})
            ->when($registerTime, function($query) use($registerTime) {
                $startTime = strtotime($registerTime[0]);
                $endTime = strtotime($registerTime[1]);
                $query->where('register_time', '>=', $startTime)->where('register_time', '<', $endTime+ 86400);
            })
            ->count();

        //返回数据
        return JSON::ok([
            'total' => $totalCount,
            'items' => $userRows,
        ]);
    }

    //修改用户信息
    public function editUser(Request $request)
    {
        $validRules = [
            'user_name' => 'bail|required|string|min:1',
            'reset_password' => 'bail|required|string|min:1,max:100',
        ];

        if ($r = $this->validateFail($request, $validRules)) {
            return $r;
        }

        //接收处理参数
        $userName = trim($request->input('user_name'));
        $resetPassword = trim($request->input('reset_password'));

        $encrypt = rand(10000, 99999);
        $encryptPassword = md5($resetPassword . $encrypt);

        $updateData = [
            'password' => $encryptPassword,
            'encrypt' => $encrypt
        ];
        DB::table('users')->where('user_name', '=', $userName)->update($updateData);

        return JSON::ok();

    }

    /**
     * 功能续费充值
     * @param $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function serviceRecharge(Request $request)
    {
        $validRules = [
            'phone' => 'bail|required|string|min:11,max:11',
            'day' => 'bail|required|string|min:1,max:100',
        ];

        if ($r = $this->validateFail($request, $validRules)) {
            return $r;
        }

        //接收处理参数
        $phone = trim($request->input('phone'));
        $day = trim($request->input('day'));

        //update wx_services as ws left join users_wx_account as uwa on ws.wxid = uwa.wxid left join users as u on u.id = uwa.user_id set ws.end_time = '1538323200' where u.phone = '15222783078'
        $where = [
            ['users.phone', '=', $phone]
        ];
        $r = DB::table('wx_services')
            ->leftJoin('users_wx_account', 'wx_services.wxid', '=', 'users_wx_account.wxid')
            ->leftJoin('users', 'users.id', '=', 'users_wx_account.user_id')
            ->where($where)
            ->increment('wx_services.end_time', $day * 3600 * 24);
        if($r) {
            return JSON::ok([]);
        } else {
            return JSON::error(JSON::E_FORBIDDEN);
        }
    }
}
