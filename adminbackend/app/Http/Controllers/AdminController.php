<?php

namespace App\Http\Controllers;

use App\Http\Responses\JSON;
use Illuminate\Http\Request;
use App\Models\Admin;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{

    /**
     * 获取管理员列表
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

        $page = $request->input('page');
        $pagesize = $request->input('limit');
        $data = Admin::singleton()->adminList($page, $pagesize);

        return JSON::ok($data);
    }

    /**
     * 获取管理员信息
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function info()
    {
        $admin = Admin::singleton()->infoByUsername($_SESSION['username']);
        if (!$admin) {
            return JSON::E_UNLOGIN();
        }

        $menus = \App\Models\Role::singleton()->accessableMenus($_SESSION['role_id']);
        $info = [
            'roles' => ['admin'],//!!应该返回该角色可以访问的uri;客户端暂未使用
            'name' => $admin->realname,
            'avail_menus' => $menus,
        ];

        return JSON::ok($info);
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
        $res = DB::table('admin')->delete($id);
        if ($res) {
            return JSON::ok();
        }

        return JSON::E_INVALID_PARAM('删除失败');
    }

    /**
     * 编辑管理员信息
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit(Request $request)
    {
        if ($r = $this->ifInvali($request, 'edit')) {
            return $r;
        }

        $realname = trim($request->input('realname'));
        $roleid = trim($request->input('roleid'));
        $password = trim($request->input('password'));
        $id = (int) $request->input('id');
        $data = [
            'realname' => $realname,
            'role_id' => $roleid,
            'id' => $id,
        ];

        if ($password) {
            $data['password'] = $password;
        }

        if (Admin::singleton()->addOrEdit($data) === false) {
            return JSON::E_INVALID_PARAM('保存失败');
        }

        $_SESSION['role_id'] = $roleid;
        
        return JSON::ok();
    }

    /**
     * 添加管理员
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function add(Request $request)
    {
        if ($r = $this->ifInvali($request)) {
            return $r;
        }

        $realname = trim($request->input('realname'));
        $username = trim($request->input('username'));
        $roleid = trim($request->input('roleid'));
        $password = trim($request->input('password'));
        $data = [
            'realname' => $realname,
            'role_id' => $roleid,
            'password' => $password,
            'username' => $username,
        ];

        $id = Admin::singleton()->addOrEdit($data);
        if ($id) {
            return JSON::ok(['id' => $id]);
        }

        return JSON::E_INVALID_PARAM('保存失败');
    }

    private function ifInvali(Request $request, $mode = 'add')
    {
        if ($mode == 'add') {
            $valiRules = Admin::$valiRules;
        } else {
            $valiRules = [
                'password' => 'bail|min:6|max:32',
                'id' => 'required|integer|min:1',
            ];
        }

        $valiRules['roleid'] = 'required|integer|min:1';
        $valiRules['confirm_password'] = $valiRules['password'];

        if ($r = $this->validateFail($request, $valiRules)) {
            return $r;
        }

        $roleid = $request->input('roleid');
        $password = $request->input('password');
        $confirmPassword = $request->input('confirm_password');
        if ($password != $confirmPassword) {
            return JSON::E_INVALID_PARAM("密码不一致");
        }

        if (!DB::table('admin_role')->where([['status', 1], ['id', $roleid]])->exists()) {
            return JSON::E_INVALID_PARAM('权限不存在');
        }

        return false;
    }

}
