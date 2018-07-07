<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Admin extends Model
{

    public $timestamps = false;
    static $valiRules = [
        'username' => 'bail|required|min:2|max:20',
        'password' => 'bail|required|min:6|max:32',
    ];

    /**
     * 与模型关联的数据表
     *
     * @var string
     */
    protected $table = 'admin';

    public function adminList($page, $pagesize = 20)
    {
        $start = ($page - 1) * $pagesize;
        $query = "SELECT SQL_CALC_FOUND_ROWS a.id, a.username, a.login_ip, a.login_time
			, a.realname, r.rolename,r.id as roleid FROM admin a 
			INNER JOIN admin_role r ON (r.id = a.role_id) ORDER BY a.id DESC limit $start,$pagesize";

        $lists = DB::select($query);
        if (!$lists && $start == 0) {
            return [
                'total' => 0,
                'items' => [],
            ];
        }

        $total = DB::selectOne('SELECT FOUND_ROWS() as total')->total;

        return [
            'total' => $total,
            'items' => $lists,
        ];
    }

    /**
     * 添加或编辑管理员信息
     * @param array $param
     */
    public function addOrEdit($param)
    {
        if (isset($param['password'])) {
            list($pwd, $encrypt) = $this->genPassword($param['password']);
            $param['password'] = $pwd;
            $param['encrypt'] = $encrypt;
        }

        if (isset($param['id'])) {
            $id = $param['id'];
            unset($param['id']);

            return $this->where('id', $id)->update($param);
        } else {
            return $this->insertGetId($param);
        }
    }

    /**
     * 验证用户密码
     * @param string $username
     * @param string $md5password
     * @return boolean
     */
    public function loginByPassword($username, $md5password, $loginIP = '')
    {
        $admin = $this->selectRaw('id,password,encrypt,role_id')->where('username', $username)->limit(1)->first();
        if (!$admin->exists()) {
            return false;
        }

        $pwd = $this->password($md5password, $admin->encrypt);
        if ($admin->password === $pwd) {
            $this->where('id', $admin->id)->update([
                'login_time' => time(),
                'login_ip' => $loginIP,
            ]);
            return $admin;
        }

        return false;
    }

    /**
     * 通过账号获取管理员信息
     * @param string $username
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function infoByUsername($username)
    {
        return $this->where('username', $username)->limit(1)->first();
    }

    /**
     * 对用户的密码进行二次加密
     * @param string $md5password md5(原始密码)
     * @param string $encrypt 加密串
     * @return array|string
     */
    public function password($md5password, $encrypt)
    {
        return md5(trim($md5password) . $encrypt);
    }

    /**
     * 生成初始化密码
     * @param string $init
     * @return array [$password, $encrypt]
     */
    public function genPassword($init)
    {
        $encrypt = str_random(6);
        $psw = $this->password($init, $encrypt);

        return [$psw, $encrypt];
    }

    /**
     * 返回单例
     * @return \App\Models\Admin
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
