<?php

namespace App\Http\Controllers;

use App\Http\Responses\JSON;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    protected function validateFail(Request $request, array $rules, array $messages = [], array $customAttributes = [])
    {
        empty($messages) && $messages = [
            'required' => ':attribute是必须的',
            'integer' => ':attribute必须为整数',
            'min' => ':attribute必须大(长)于:min',
            'max' => ':attribute必须小(短)于:max',
            'between' => ':attribute不在范围内',
            'in' => ':attribute不合法',
        ];

        $validator = $this->getValidationFactory()->make($request->all(), $rules, $messages, $customAttributes);
        if ($validator->fails()) {
            $msg = '';
            foreach ($validator->errors()->getMessages() as $item) {
                $msg .= implode(',', $item) . ";\n";
            }

            return JSON::error(JSON::E_INVALID_PARAM, $msg);
        }

        return false;
    }
}
