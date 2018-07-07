<?php

namespace App\Http\Responses;

class JSON
{

    const E_OK = 0;
    const E_INVALID_PARAM = 2;
    const E_INTERNAL = 3;
    const E_FORBIDDEN = 4;
    const E_UNLOGIN = 201;
    const E_WRONG_PASSWORD = 202;

    public static $codeMsgs = [
        self::E_OK => '操作成功',
        self::E_FORBIDDEN => '无权限访问',
    ];

    public static function __callStatic($func, $arguments)
    {
        if ($func == 'E_OK') {
            return call_user_func_array(self::ok, $arguments);
        }

        // 利用反射获取 $func 对应的常量
        $fl = new \ReflectionClass(__CLASS__);
        $code = $fl->getConstant($func);
        if ($code) {
            $msg = $func;
            if (!empty($arguments[0])) {
                $msg = $arguments[0];
            } elseif (isset(self::$codeMsgs[$code])) {
                $msg = self::$codeMsgs[$code];
            }

            return self::error($code, $msg);
        } else {
            return self::error($func, $func, $arguments);
        }
    }

    /**
     * 操作成功
     * @param array $data
     * @param string $msg
     * @return \Illuminate\Http\JsonResponse
     */
    public static function ok($data = null, $msg = null)
    {
        $r = [
            'code' => self::E_OK,
        ];

        if ($msg) {
            $r['msg'] = $msg;
        }

        if ($data) {
            $r['data'] = $data;
        } elseif ($data !== null) {
            if (is_array($data)) {
                $r['data'] = (object) $data;
            }
        }

        return response()->json($r);
    }

    /**
     * 操作失败
     * @param int $code
     * @param string $msg
     * @param array $data
     * @return \Illuminate\Http\JsonResponse
     */
    public static function error($code, $msg = null, $data = null)
    {
        $r = [
            'code' => $code,
        ];

        if ($msg === null) {
            if (isset(self::$codeMsgs[$code])) {
                $r['msg'] = self::$codeMsgs[$code];
            } else {
                // 利用反射获取 $code 对应的常量名
                $fl = new \ReflectionClass(__CLASS__);
                $consts = $fl->getConstants();
                $name = array_search($code, $consts);
                if ($name) {
                    $r['msg'] = $name;
                }
            }
        } else {
            $r['msg'] = $msg;
        }

        if ($data) {
            $r['data'] = $data;
        }

        return response()->json($r);
    }

}
