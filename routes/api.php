<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::namespace('Api')->group(function (\Illuminate\Routing\Router $router) {

    $router->group(['prefix' => 'auth'], function (\Illuminate\Routing\Router $router) {
        $router->post('login', 'AuthController@login');
        $router->post('logout', 'AuthController@logout');
        $router->post('refresh', 'AuthController@refresh');
        $router->post('me', 'AuthController@me');
    });

    $router->group(['prefix' => 'manager'], function (\Illuminate\Routing\Router $router) {
        $router->post('create', 'ManagerController@create');
        $router->put('update/{manager}', 'ManagerController@update')->where('manager', '\d+');
    });

    $router->group(['prefix' => 'permission'], function (\Illuminate\Routing\Router $router) {
        $router->get('list', 'PermissionController@list');
    });

    $router->group(['prefix' => 'role'], function (\Illuminate\Routing\Router $router) {
        $router->get('list', 'RoleController@list');
        $router->post('create', 'RoleController@create');
        $router->put('update/{role}', 'RoleController@update')->where('role', '\d+');
        $router->delete('delete/{role}', 'RoleController@delete')->where('role', '\d+');
    });
});


