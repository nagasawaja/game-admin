<?php

use App\Http\Responses\JSON;

/*
  |--------------------------------------------------------------------------
  | Application Routes
  |--------------------------------------------------------------------------
  |
  | Here is where you can register all of the routes for an application.
  | It is a breeze. Simply tell Lumen the URIs it should respond to
  | and give it the Closure to call when that URI is requested.
  |
 */

$app->get('/', function () {
    return JSON::error(JSON::E_UNLOGIN);
});


$app->group(['middleware' => 'auth'], function () use ($app) {
    $auths = include 'auths.php';
    foreach ($auths as $uri => $cfg) {
        $method = $cfg['method'];
        $app->$method($uri, $cfg['ctrl']);
    }
});

$app->post('login/bypassword', 'LoginController@bypassword');
$app->post('login/logout', 'LoginController@logout');