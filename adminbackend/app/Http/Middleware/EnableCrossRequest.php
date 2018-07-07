<?php

namespace App\Http\Middleware;

use Closure;

class EnableCrossRequest
{

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
        $httpCfg = include base_path('config/http.php');
        if (empty($httpCfg['allow-origin'])) {
            return $next($request);
        }

        header('Access-Control-Allow-Origin: ' . $httpCfg['allow-origin']);
        header('Access-Control-Allow-Headers: ' . $httpCfg['allow-headers']);
        header('Access-Control-Allow-Methods: ' . $httpCfg['allow-methods']);
        header('Access-Control-Allow-Credentials: true');

        if ($request->method() == 'OPTIONS') {
            exit();
        }

        return $next($request);
    }

}
