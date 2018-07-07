<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Responses\JSON;
use App\Models\Admin;

class LoginController extends Controller
{

    /**
     * 通过密码登录
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function bypassword(Request $request)
    {
        $valiRules = Admin::$valiRules;

        if ($r = $this->validateFail($request, $valiRules)) {
            return $r;
        }

        $username = trim($request->input('username'));
        $password = trim($request->input('password'));
        $admin = Admin::singleton()->loginByPassword($username, $password, $request->getClientIp());
        if (!$admin) {
            return JSON::E_WRONG_PASSWORD();
        }

        session_start();
        $_SESSION['username'] = $username;
        $_SESSION['role_id'] = $admin->role_id;

        return JSON::ok([
                    'token' => session_id(),
        ]);
    }

    public function logout(Request $request)
    {
        session_start();
        session_destroy();
        
        return JSON::ok();
    }

}
