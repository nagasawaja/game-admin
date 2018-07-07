<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class Role
{

    const TABLE = 'admin_role_priv';

    /**
     * 获取角色可访问的菜单
     * @param int $role
     * @return array
     */
    public function accessableMenus($role)
    {
        $menus = include app()->getConfigurationPath('menus');
        if ($role == 1) {
            return $menus;
        }

        $auths = include app()->basePath('routes/auths.php');
        $ctrledMenu = [];
        foreach ($menus as $m) {
            if (empty($m['children'])) {
                continue;
            }

            foreach ($m['children'] as $child) {
                $path = $child['path'];
                if (isset($auths[$path]) && empty($auths[$path]['allrole'])) {
                    $ctrledMenu[$path] = $path;
                }
            }
        }

        $uris = self::table()->where('role_id', $role)
                ->where('status', 1)
                ->whereIn('uri', $ctrledMenu)
                ->pluck('uri')
                ->toArray();
        $avails = [];
        foreach ($menus as $m) {
            if (empty($m['children'])) {
                $avails[] = $m;
                continue;
            }

            $children = [];
            foreach ($m['children'] as $child) {
                $path = $child['path'];
                if (isset($ctrledMenu[$path]) && !in_array($path, $uris)) {
                    continue;
                }

                $children[] = $child;
            }

            if (!$children) {
                continue;
            }

            $m['children'] = $children;
            $avails[] = $m;
        }

        return $avails;
    }

    /**
     * 是否有权限访问 $path
     * @param int $roleid
     * @param string $path
     * @return bool
     */
    public function canAccessPath($roleid, $path)
    {
        return self::table()->where('uri', $path)
                        ->where('status', 1)
                        ->get()
                        ->isNotEmpty();
    }

    /**
     * 
     * @return \Illuminate\Database\Query\Builder
     */
    public static function table()
    {
        return DB::table(self::TABLE);
    }

    /**
     * 返回单例
     * @return \App\Models\Role
     */
    public static function singleton()
    {
        static $inst = null;
        if (!$inst) {
            $inst = new static();
        }

        return $inst;
    }

}
