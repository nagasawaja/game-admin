<?php

namespace App\Http\Controllers;

use App\Http\Responses\JSON;
use App\Models\Constants;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServiceController extends Controller
{

    /**
     * 获取功能服务列表
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
        $serviceId = trim($request->input('service_id'));
        $type = trim($request->input('type'));

        $servicesRaw = 'services.id as service_id, ';
        $servicesProductRaw = 'services_product.id as service_product_id, services_product.coins, services_product.status as service_product_status, services_product.type as service_product_type, FLOOR((services_product.avail_time / 24 / 60)) as avail_time';
        $serviceRows = DB::table('services_product')
            ->selectRaw($servicesRaw. $servicesProductRaw)
            ->leftJoin('services', 'services_product.service_id', '=', 'services.id')
            ->take($take)
            ->skip($skip)
            ->when($serviceId, function($query) use ($serviceId) {return $query->where('service_id', '=', $serviceId);})
            ->when($type, function($query) use ($type) {return $query->where('type', '=', $type);})
            ->orderByDesc('service_product_id')
            ->get();

        $total = DB::table('services_product')
            ->leftJoin('services', 'services_product.service_id', '=', 'services.id')
            ->when($serviceId, function($query) use ($serviceId) {return $query->where('service_id', '=', $serviceId);})
            ->when($type, function($query) use ($type) {return $query->where('type', '=', $type);})
            ->count();

        return JSON::ok([
            'total' => $total,
            'items' => $serviceRows,
            'service_list' => DB::table('services')->get()->mapWithKeys(function($item) {
                return [$item->id => $item];
            })
        ]);
    }

    public function del(Request $request)
    {
        $valiRules = [
            'id' => 'required|integer|min:1',
        ];

        if ($r = $this->validateFail($request, $valiRules)) {
            return $r;
        }

        $id = (int) $request->input('id');
        $res = DB::table('services_product')->delete($id);
        if ($res) {
            return JSON::ok();
        }

        return JSON::error(JSON::E_INVALID_PARAM);
    }

    private function save(Request $request)
    {
        $name = trim($request->input('service_name'));
        $description = trim($request->input('description'));
        $status = (int) $request->input('status');
        $price = (int) $request->input('price');
        $id = (int) $request->input('id');
        $trial = (int) $request->input('trial');
        $trialTime = (int) $request->input('trial_time');
        $time = time();
        $data = [
            'service_name' => $name,
            'description' => $description,
            'status' => $status,
            'price' => $price,
        ];

        try {
            if ($id) {// update
                DB::table('services')->where('id', $id)->update($data);

                $this->updateTrialSetting($id, $trial, $trialTime);
            } else {
                $data['create_time'] = $time;
                $id = DB::table('services')->insertGetId($data);
                if (!$id) {
                    return JSON::E_INTERNAL('系统错误');
                }
            }
        } catch (\Exception $ex) {
            return JSON::E_INTERNAL($ex->getMessage());
        }

        $data['id'] = $id;
        $data['trial'] = $trial;
        $data['trial_time'] = $trialTime;

        return JSON::ok($data);
    }

    private function updateTrialSetting($serviceID, $isTrial, $trialTime)
    {
        $row = DB::table('services_trial_setting')->where('service_id', $serviceID)->first();
        if (!$row) {
            if ($isTrial != 1) {
                return 0;
            }

            return DB::table('services_trial_setting')->insertGetId([
                        'service_id' => $serviceID,
                        'status' => $isTrial,
                        'avail_time' => $trialTime,
                        'create_time' => time(),
            ]);
        }

        $data = [];
        if ($row->status != $isTrial && $row->avail_time == $trialTime) {
            $data['status'] = $isTrial;
        }

        if ($row->avail_time != $trialTime) {
            $data['avail_time'] = $trialTime;
        }

        if ($data) {
            DB::table('services_trial_setting')->where('id', $row->id)->update($data);
        }

        return $row->id;
    }

    /**
     * 编辑
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit(Request $request)
    {
        $valiRules = [
            'avail_time' => 'integer|between:1,50',
            'coins' => 'integer|min:0',
            'service_id' => 'integer|min:0',
            'service_product_id' => 'integer|min:0',
            'service_product_status' => 'integer|min:0',
            'service_product_type' => 'integer|min:0',
        ];

        if ($r = $this->validateFail($request, $valiRules)) {
            return $r;
        }

        $availTime = trim($request->input('avail_time'));
        $coins = trim($request->input('coins'));
        $serviceId = trim($request->input('service_id'));
        $serviceProductId = trim($request->input('service_product_id'));
        $serviceProductStatus = trim($request->input('service_product_status'));
        $serviceProductType = trim($request->input('service_product_type'));

        //修改数据
        $update = [
            'service_id' => $serviceId,
            'coins' => $coins,
            'status' => $serviceProductStatus,
            'type' => $serviceProductType,
            'avail_time' => $availTime * 24 * 60
        ];
        $where = [
            ['id', '=', $serviceProductId]
        ];
        DB::table('services_product')->where($where)->update($update);
        $data = [
            'service_id' => $serviceId,
            'coins' => $coins,
            'service_product_status' => $serviceProductStatus,
            'service_product_type' => $serviceProductType,
            'avail_time' => $availTime,
            'service_product_id' => $serviceProductId
        ];
        return JSON::ok($data);
    }

    /**
     * 添加
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function add(Request $request)
    {
        $valiRules = [
            'avail_time' => 'bail|required|integer|between:1,50',
            'coins' => 'bail|required|integer|min:0',
            'service_id' => 'bail|required|integer|min:1',
            'service_product_status' => 'bail|required|integer|min:1',
            'service_product_type' => 'bail|required|integer|min:1',
        ];

        if ($r = $this->validateFail($request, $valiRules)) {
            return $r;
        }

        $availTime = $request->input('avail_time');
        $coins = $request->input('coins');
        $serviceId = $request->input('service_id');
        $serviceProductStatus = $request->input('service_product_status');
        $serviceProductType = $request->input('service_product_type');

        $insertData = [
            'service_id' => $serviceId,
            'coins' => $coins,
            'status' => $serviceProductStatus,
            'type' => $serviceProductType,
            'avail_time' => $availTime * 24 * 60,
            'create_time' => time()
        ];
        $id = DB::table('services_product')->insertGetId($insertData);
        $data = [
            'service_id' => $serviceId,
            'coins' => $coins,
            'service_product_status' => $serviceProductStatus,
            'service_product_type' => $serviceProductType,
            'avail_time' => $availTime,
            'create_time' => time()
        ];
        $data['service_product_id'] = $id;
        return JSON::ok($data);
    }

    //功能套餐
    public function packageList(Request $request)
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
        $where = [
            ['activation_code_gift.status', '=', Constants::STATUS_VALID]
        ];

        $selectRaw = 'activation_code_gift.id as gift_id, activation_code_gift.title as gift_title, activation_code_gift.status as gift_status, activation_code_gift.is_company, activation_code_gift.sort_order, ';
        $serviceSelectRaw = 'group_concat(services.service_name) as service_name_rows, 0 as gift_standard';
        $dbBuilder = DB::table('activation_code_gift')
            ->leftJoin('gift_2_service', function($join) {$join->on('activation_code_gift.id', '=', 'gift_2_service.activation_code_gift_id');})
            ->leftJoin('services', function($join) {$join->on('gift_2_service.service_id', '=', 'services.id');})
            ->where($where)
            ->groupBy('activation_code_gift.id')
            ->orderBy('activation_code_gift.id', 'desc')
            ->take($take)
            ->skip($skip);

        $standardSelectRaw = 'activation_code_gift.id as gift_id, group_concat(concat(FLOOR(activation_code_gift_standard.avail_time /24 /60), "天-$", floor(activation_code_gift_standard.price / 100)) order by activation_code_gift_standard.price) as gift_standard';
        $dbBuilderGiftStandard = DB::table('activation_code_gift')
            ->leftJoin('activation_code_gift_standard', function($join) {$join->on('activation_code_gift.id', '=', 'activation_code_gift_standard.activation_code_gift_id');})
            ->selectRaw($standardSelectRaw)
            ->groupBy('activation_code_gift.id');
        $giftIdRows = [];
        $items = $dbBuilder->selectRaw($selectRaw . $serviceSelectRaw)->get();
        $tmpResult = [];
        foreach($items as $k => $item) {
            $tmpResult[$item->gift_id] = $item;
            $giftIdRows[] = $item->gift_id;
        }

        $gitStandardRows = $dbBuilderGiftStandard->where($where)->orderBy('activation_code_gift.id', 'desc')->whereIn('activation_code_gift.id', $giftIdRows)->get();
        foreach($gitStandardRows as $k => $v) {
            $tmpResult[$v->gift_id]->gift_standard = $v->gift_standard;
        }
        return JSON::ok([
            'total' => DB::table('activation_code_gift')->count(),
            'items' => array_values($tmpResult),
            'service_list' => DB::table('services')->get()->mapWithKeys(function($item) {
                return [$item->id => $item];
            })
        ]);
    }

    //功能套餐添加
    public function packageAdd(Request $request)
    {
        $validRules = [
            'gift_title' => 'bail|required|string|min:1',
            'gift_status' => 'bail|required|integer|min:1',
            'service_id_rows' => 'bail|required|array|min:1',
            'gift_standard_rows' => 'bail|required|array|min:1',
            'is_company' => 'bail|required|integer|min:1|max:2'
        ];

        if ($r = $this->validateFail($request, $validRules)) {
            return $r;
        }

        $giftTitle = $request->input('gift_title');
        $giftStatus = $request->input('gift_status');
        $giftStandardRows = $request->input('gift_standard_rows');
        $serviceIdRows = $request->input('service_id_rows');
        $isCompany = $request->input('is_company');
        $r = true;
        DB::beginTransaction();
        //activation_code_gift
        $insertData = [
            'title' => $giftTitle,
            'status' => $giftStatus,
            'is_company' => $isCompany
        ];
        $giftId = DB::table('activation_code_gift')->insertGetId($insertData);
        $r = $r && $giftId;
        //activation_code_gift_standard
        $insertData = [];
        foreach($giftStandardRows as $giftStandardRow) {
            //'activation_code_gift_id' => $giftId
            $insertData[] = [
                'activation_code_gift_id' => $giftId,
                'avail_time' => $giftStandardRow['avail_time_day'] * 24 * 60,
                'price' => $giftStandardRow['price_yuan'] * 100
            ];
        }
        $r = $r && DB::table('activation_code_gift_standard')->insert($insertData);
        //gift_2_service
        $insertData = [];
        foreach($serviceIdRows as $serviceId) {
            $insertData[] = [
                'activation_code_gift_id' => $giftId,
                'service_id' => $serviceId
            ];
        }
        $r =  $r && DB::table('gift_2_service')->insert($insertData);

        //return data
        if($r) {
            DB::commit();
            return JSON::ok();
        } else {
            DB::rollBack();
            return JSON::error(JSON::E_INTERNAL);
        }
    }

    //功能套餐修改
    public function packageEdit(Request $request)
    {
        $param = $request->toArray();

        //更新
        $dbQuery = DB::table('activation_code_gift');
        $updateData = [];
        foreach($param['param'] as $key => $value) {
            $updateData[$key] = $value;
        }
        $dbQuery->where('id', '=', $param['gift_id'])->update($updateData);

        //return data
        return JSON::ok();
    }

    //功能套餐添加
    public function packageDel(Request $request)
    {
        $giftId = trim($request->input('gift_id'));

        DB::table('activation_code_gift')->where('id', '=', $giftId)->update(['status' => Constants::STATUS_INVALID]);

        return JSON::ok();
    }

    private function ifInvali(Request $request, $mode = 'add')
    {
        $valiRules = [
            'service_name' => 'required',
            'status' => 'required|integer|min:1|max:3',
            'trial' => 'required|integer|min:1|max:2',
            'avail_time' => 'required|integer|min:1',
            'trial_time' => 'integer|min:0',
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

    //获取所有功能服务
    public function getAllService(Request $request)
    {
        $r = DB::table('services')->get();
        return ['items' => $r];
    }
}
