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
//
//    $router->group(['prefix' => 'doctor'], function (\Illuminate\Routing\Router $router) {
//        $router->post('create', 'DoctorController@create');
//        $router->put('update/{manager}', 'DoctorController@update')->where('manager', '\d+');
//    });
//
//    $router->group(['prefix' => 'manager'], function (\Illuminate\Routing\Router $router) {
//        $router->post('create', 'ManagerController@create');
//        $router->put('update/{manager}', 'ManagerController@update')->where('manager', '\d+');
//    });
//
//    $router->group(['prefix' => 'permission'], function (\Illuminate\Routing\Router $router) {
//        $router->get('list', 'PermissionController@list');
//    });
//
//    $router->group(['prefix' => 'role'], function (\Illuminate\Routing\Router $router) {
//        $router->get('list', 'RoleController@list');
//        $router->post('create', 'RoleController@create');
//        $router->put('update/{role}', 'RoleController@update')->where('role', '\d+');
//        $router->delete('delete/{role}', 'RoleController@delete')->where('role', '\d+');
//    });


    $router->group(['prefix' => 'system'], function (Router $router) {
        $router->group(['prefix' => 'member'], function (Router $router) {
            $router->get('list', 'SystemController@memberList');
            $router->get('create', 'SystemController@memberCreate');
            $router->get('update/{member}', 'SystemController@memberUpdate');
            $router->get('delete/{member}', 'SystemController@memberDelete');
        });
        $router->group(['prefix' => 'channel'], function (Router $router) {
            $router->get('list', 'SystemController@channelList');
            $router->get('create', 'SystemController@channelCreate');
            $router->get('update/{channel}', 'SystemController@channelUpdate');
            $router->get('delete/{channel}', 'SystemController@channelDelete');
        });
        $router->group(['prefix' => 'department'], function (Router $router) {
            $router->get('list', 'SystemController@departmentList');
            $router->get('create', 'SystemController@departmentCreate');
            $router->get('update/{channel}', 'SystemController@departmentUpdate');
            $router->get('delete/{channel}', 'SystemController@departmentDelete');
        });
        $router->group(['prefix' => 'doctor'], function (Router $router) {
            $router->get('list', 'SystemController@doctorList');
            $router->get('create', 'SystemController@doctorCreate');
            $router->get('update/{channel}', 'SystemController@doctorUpdate');
            $router->get('delete/{channel}', 'SystemController@doctorDelete');
        });
        $router->group(['prefix' => 'role'], function (Router $router) {
            $router->get('list', 'SystemController@roleList');
            $router->get('create', 'SystemController@roleCreate');
            $router->get('update/{channel}', 'SystemController@roleUpdate');
            $router->get('delete/{channel}', 'SystemController@roleDelete');
        });
        $router->group(['prefix' => 'permission'], function (Router $router) {
            $router->get('list', 'SystemController@permissionList');
        });
    });

});


