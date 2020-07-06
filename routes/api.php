<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Routing\Router;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::namespace('Api')->group(function (Router $router) {

//    $router->group(['prefix' => 'auth'], function (\Illuminate\Routing\Router $router) {
//        $router->post('login', 'AuthController@login');
//        $router->post('logout', 'AuthController@logout');
//        $router->post('refresh', 'AuthController@refresh');
//        $router->post('me', 'AuthController@me');
//    });

    $router->group(['prefix' => 'system'], function (Router $router) {
        $router->group(['prefix' => 'member_kind'], function (Router $router) {
            $router->get('list', 'SystemController@memberKindList');
            $router->post('create', 'SystemController@memberKindCreate');
            $router->put('update/{member_kind}', 'SystemController@memberKindUpdate');
            $router->delete('delete/{member_kind}', 'SystemController@memberKindDelete');
        });
        $router->group(['prefix' => 'channel'], function (Router $router) {
            $router->get('list', 'SystemController@channelList');
            $router->post('create', 'SystemController@channelCreate');
            $router->put('update/{channel}', 'SystemController@channelUpdate');
            $router->delete('delete/{channel}', 'SystemController@channelDelete');
        });
        $router->group(['prefix' => 'department'], function (Router $router) {
            $router->get('list', 'SystemController@departmentList');
            $router->post('create', 'SystemController@departmentCreate');
            $router->put('update/{department}', 'SystemController@departmentUpdate');
            $router->delete('delete/{department}', 'SystemController@departmentDelete');
        });
        $router->group(['prefix' => 'doctor'], function (Router $router) {
            $router->get('list', 'SystemController@doctorList');
            $router->post('create', 'SystemController@doctorCreate');
            $router->put('update/{doctor}', 'SystemController@doctorUpdate');
            $router->delete('delete/{doctor}', 'SystemController@doctorDelete');
            $router->put('manager/{doctor}', 'SystemController@doctorManager');
        });
        $router->group(['prefix' => 'role'], function (Router $router) {
            $router->get('list', 'SystemController@roleList');
            $router->post('create', 'SystemController@roleCreate');
            $router->put('update/{role}', 'SystemController@roleUpdate');
            $router->delete('delete/{role}', 'SystemController@roleDelete');
            $router->put('manager/create/{role}', 'SystemController@roleManagerCreate');
            $router->put('manager/update/{manager}', 'SystemController@roleManagerUpdate');
            $router->put('manager/status/{manager}', 'SystemController@roleManagerStatus');
        });
        $router->group(['prefix' => 'permission'], function (Router $router) {
            $router->get('list', 'SystemController@permissionList');
        });
    });

});


