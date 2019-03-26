<?php

namespace App\Http\Controllers;

use App\Http\Responses\JSON;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{

    /**
     * 获取角色列表
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
        $status = (int) $request->input('status');
        $where = '';
        if ($status) {
            $where = "where status=$status";
        }

        $query = "select SQL_CALC_FOUND_ROWS * from admin_role $where order by id asc";
        if ($page) {
            $start = ($page - 1) * $pagesize;
            $query .= " limit $start,$pagesize";
        }

        $items = DB::select($query);
        if (!$items && empty($start)) {
            return JSON::ok([
                        'items' => [],
                        'total' => 0,
            ]);
        }

        $total = DB::selectOne('SELECT FOUND_ROWS() as total')->total;
        return JSON::ok([
                    'items' => $items,
                    'total' => $total,
        ]);
    }

    /**
     * 获取权限功能列表
     * @param Request $request
     */
    public function listAuth(Request $request)
    {
        $valiRules = [
            'roleid' => 'required|integer|min:1',
        ];

        if ($r = $this->validateFail($request, $valiRules)) {
            return $r;
        }

        $roleid = (int) $request->input('roleid');
        $menus = include app()->getConfigurationPath('menus');
        $auths = include app()->basePath('routes/auths.php');
        $menuPaths = [];
        foreach ($menus as $m) {
            if (!empty($m['children'])) {
                foreach ($m['children'] as $child) {
                    $menuPaths[$child['path']] = $m['title'] . '-' . $child['title'];
                }
            }
        }

        $ret = [];
        $rolePaths = false;
        if ($roleid != 1) {
            $rolePaths = DB::table('admin_role_priv')
                    ->where('role_id', $roleid)
                    ->where('status', 1)
                    ->pluck('uri')
                    ->toArray();
            $rolePaths = array_combine($rolePaths, $rolePaths); // [uri=>item]
        }

        foreach ($auths as $path => $auth) {
            if (!empty($auth['allrole'])) {
                continue;
            }

            $item = [
                'title' => $auth['title'],
                'uri' => $path,
            ];

            if ($rolePaths === false || isset($rolePaths[$path])) {
                $item['checked'] = true;
            }

            if (isset($menuPaths[$path])) {
                $item['menu'] = $menuPaths[$path];
            }

            $ret[] = $item;
        }

        return JSON::ok(['items' => $ret]);
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
        $res = DB::table('admin_role')->delete($id);
        if ($res) {
            return JSON::ok();
        }

        return JSON::E_INVALID_PARAM('删除失败');
    }

    private function ifInvali(Request $request, $mode = 'add')
    {
        $valiRules = [
            'rolename' => 'bail|required|min:1|max:50',
            'status' => 'required|integer|min:1|max:2',
        ];

        if ($mode != 'add') {
            $valiRules['id'] = 'required|integer|min:1';
        }

        if ($r = $this->validateFail($request, $valiRules)) {
            return $r;
        }

        return false;
    }

    private function save(Request $request)
    {
        $rolename = trim($request->input('rolename'));
        $description = trim($request->input('description'));
        $id = (int) $request->input('id');
        $status = (int) $request->input('status');
        $data = [
            'rolename' => $rolename,
            'description' => $description,
            'status' => $status,
        ];

        try {
            if ($id) {
                if (DB::table("admin_role")->where('id', $id)->update($data) === false) {
                    return JSON::E_INVALID_PARAM('保存失败');
                }
            } else {
                $id = DB::table("admin_role")->where('id', $id)->insertGetId($data);
                if (!$id) {
                    return JSON::E_INVALID_PARAM('保存失败');
                }
            }
        } catch (\Exception $ex) {
            return JSON::E_INTERNAL($ex->getMessage());
        }

        return JSON::ok(['id' => $id]);
    }

    /**
     * 编辑角色信息
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
     * 角色权限编辑
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function editAuth(Request $request)
    {
        $valiRules = [
            'roleid' => 'required|integer|min:1',
        ];

        if ($r = $this->validateFail($request, $valiRules)) {
            return $r;
        }

        $roleid = (int) $request->input('roleid');
        $uris = trim($request->input('uris'));

        $uris = explode(',', $uris);
        foreach($uris as $k => $v) {
            if ($v == "") {
                unset($uris[$k]);
            }
        }

        DB::table('admin_role_priv')->where('role_id', '=', $roleid)->delete();

        if(count($uris) > 1) {
            $sql = "insert into admin_role_priv(role_id,uri,status)values";
            $vals = '';
            foreach ($uris as $uri) {
                $uri = addslashes($uri);
                $vals.=",($roleid,'$uri',1)";
            }

            $vals[0] = ' ';
            $sql.=$vals;
            DB::insert($sql);
        }

        return JSON::ok();
    }

    /**
     * 添加角色
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

}
