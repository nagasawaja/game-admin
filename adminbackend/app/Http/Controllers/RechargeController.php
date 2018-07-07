<?php

namespace App\Http\Controllers;

use App\Http\Responses\JSON;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RechargeController extends Controller
{

    /**
     * 获取充值套餐列表
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function lists(Request $request)
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

        $rechargeOrderRows = DB::table('coin_products')->take($take)->skip($skip)->get();

        $total = DB::table('coin_products')->count();

        return JSON::ok([
            'total' => $total,
            'items' => $rechargeOrderRows,
        ]);
    }

    public function del(Request $request)
    {
        $valiRules = [
            'id' => 'required|integer|min:2',
        ];

        if ($r = $this->validateFail($request, $valiRules)) {
            return $r;
        }

        $id = (int) $request->input('id');
        $res = DB::table('coin_products')->delete($id);
        if ($res) {
            return JSON::ok();
        }

        return JSON::E_INVALID_PARAM('删除失败');
    }

    private function save(Request $request)
    {
        $name = trim($request->input('name'));
        $description = trim($request->input('description'));
        $coins = (int) $request->input('coins');
        $extraCoins = (int) $request->input('extra_coins');
        $price = (int) bcmul($request->input('price'), 100);
        $id = (int) $request->input('id');
        $time = time();
        $data = [
            'name' => $name,
            'description' => $description,
            'coins' => $coins,
            'extra_coins' => $extraCoins,
            'price' => $price,
            'update_time' => $time,
        ];

        try {
            if ($id) {// update
                DB::table('coin_products')->where('id', $id)->update($data);
            } else {
                $data['create_time'] = $time;
                $id = DB::table('coin_products')->insertGetId($data);
                if (!$id) {
                    return JSON::error(JSON::E_INTERNAL);
                }
            }
        } catch (\Exception $ex) {
            return JSON::E_INTERNAL($ex->getMessage());
        }

        $data['id'] = $id;

        return JSON::ok($data);
    }

    /**
     * 编辑
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit(Request $request)
    {
        if ($r = $this->ifInvali($request, 'edit')) {
            return $r;
        }

        return $this->save($request);
    }

    /**
     * 添加
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function add(Request $request)
    {
        if ($r = $this->ifInvali($request)) {
            return $r;
        }

        return $this->save($request);
    }

    private function ifInvali(Request $request, $mode = 'add')
    {
        $valiRules = [
            'name' => 'required',
            'coins' => 'required|integer|min:0',
            'extra_coins' => 'required|integer|min:0',
            'price' => 'required|numeric|min:0',
        ];

        if ($mode != 'add') {
            $valiRules['id'] = 'required|integer|min:1';
        }

        if ($r = $this->validateFail($request, $valiRules)) {
            return $r;
        }

        return false;
    }

}
