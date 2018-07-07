<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Factory as Auth;
use App\Http\Responses\JSON;

class Authenticate
{

    /**
     * The authentication guard factory instance.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;

    /**
     * Create a new middleware instance.
     *
     * @param  \Illuminate\Contracts\Auth\Factory  $auth
     * @return void
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if ($this->auth->guard($guard)->guest()) {
            return JSON::E_UNLOGIN();
        }

        if ($_SESSION['role_id'] != 1) {
            $path = substr($request->getPathInfo(), 1);
            $auths = include app()->basePath('routes/auths.php');
            if (empty($auths[$path]['allrole'])) {
                $canAccess=  \App\Models\Role::singleton()->canAccessPath($_SESSION['role_id'], $path);
                
                if (!$canAccess) {
                    return JSON::error(JSON::E_FORBIDDEN);
                }
            }
        }

        return $next($request);
    }

}
