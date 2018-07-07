<?php

namespace App\Http\Controllers;

use App\Http\Responses\JSON;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{

    /**
     * 充值订单
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function rechargeOrder(Request $request)
    {
        $valiRules = [
            'limit' => 'integer|between:1,50',
            'page' => 'integer|min:0',
        ];

        if ($r = $this->validateFail($request, $valiRules)) {
            return $r;
        }

        $take = trim($request->input('limit'));
        $skip = (trim($request->input('page')) - 1) * $take;

        $rechargeOrderRows = DB::table('recharge_orders')->take($take)->skip($skip)->get();

        $total = DB::table('recharge_orders')->count();

        return JSON::ok([
            'total' => $total,
            'items' => $rechargeOrderRows,
        ]);
    }

    /**
     * 开通服务订单
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function serviceOrder(Request $request)
    {
        $valiRules = [
            'limit' => 'integer|between:1,50',
            'page' => 'integer|min:0',
        ];

        if ($r = $this->validateFail($request, $valiRules)) {
            return $r;
        }

        $take = trim($request->input('limit'));
        $skip = (trim($request->input('page')) - 1) * $take;

        $wxServicesBuyLogRows = DB::table('wx_services_buylog')->take($take)->skip($skip)->get();

        $total = DB::table('wx_services_buylog')->count();

        return JSON::ok([
            'total' => $total,
            'items' => $wxServicesBuyLogRows,
        ]);
    }
}
