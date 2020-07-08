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
    $router->group(['prefix' => 'auth'], function (Router $router) {
        $router->post('login', 'AuthController@login');
        $router->post('logout', 'AuthController@logout');
        $router->post('refresh', 'AuthController@refresh');
        $router->post('me', 'AuthController@me');
    });
    $router->group(['prefix' => 'reserve'], function (Router $router) {
        $router->group(['prefix' => 'subscribe'], function (Router $router) {
            $router->get('today', 'ReserveController@subscribeTodayList');
            $router->get('week', 'ReserveController@subscribeWeekList');
            $router->get('months', 'ReserveController@subscribeMonthsList');
            $router->get('total', 'ReserveController@subscribeTotalList');
        });
        $router->group(['prefix' => 'diagnosis'], function (Router $router) {
            $router->get('check', 'ReserveController@diagnosisCheck');
            $router->get('list', 'ReserveController@diagnosisList');
            $router->post('create', 'ReserveController@diagnosisCreate');
            $router->post('finish', 'ReserveController@diagnosisFinish');
        });
        $router->group(['prefix' => 'plan'], function (Router $router) {
            $router->get('check', 'MedicalController@medicalPlanCheck');
            $router->post('create', 'MedicalController@medicalPlanCreate');
        });
        $router->group(['prefix' => 'medical'], function (Router $router) {
            $router->get('list', 'ReserveController@medicalPlanList');
            $router->post('create', 'ReserveController@medicalPlanCreate');
        });
        $router->group(['prefix' => 'archives'], function (Router $router) {
            $router->get('list', 'ReserveController@archivesList');
            $router->get('options', 'MedicalController@memberOptionList');
            $router->get('check', 'ReserveController@diagnosisCheck');
        });
        $router->get('patient', 'ReserveController@patientList');
    });
    $router->group(['prefix' => 'subscribe'], function (Router $router) {
        $router->get('today', 'SubscribeController@reserveTodayList');
        $router->get('list', 'SubscribeController@reserveList');
        $router->post('create', 'SubscribeController@reserveCreate');
        $router->put('update/{subscribe}', 'SubscribeController@reserveUpdate');
        $router->delete('delete/{subscribe}', 'SubscribeController@reserveDelete');
    });
    $router->group(['prefix' => 'medical'], function (Router $router) {
        $router->group(['prefix' => 'plan'], function (Router $router) {
            $router->get('list', 'MedicalController@medicalPlanList');
            $router->get('check', 'MedicalController@medicalPlanCheck');
            $router->post('create', 'MedicalController@medicalPlanCreate');
            $router->put('update/{medical_plan}', 'MedicalController@medicalPlanUpdate');
            $router->delete('delete/{medical_plan}', 'MedicalController@medicalPlanDelete');
        });
        $router->group(['prefix' => 'abnormal'], function (Router $router) {
            $router->get('list', 'MedicalController@medicalPlanList');
            $router->get('check', 'MedicalController@medicalPlanCheck');
        });
        $router->group(['prefix' => 'option'], function (Router $router) {
            $router->get('list', 'MedicalController@optionList');
            $router->get('member', 'MedicalController@memberOptionList');
        });
        $router->group(['prefix' => 'config'], function (Router $router) {
            $router->get('list', 'MedicalController@configList');
            $router->get('copy', 'MedicalController@configCopy');
            $router->group(['prefix' => 'kind'], function (Router $router) {
                $router->get('list', 'MedicalController@configKindList');
                $router->post('create', 'MedicalController@configKindCreate');
                $router->put('update/{config_kind}', 'MedicalController@configKindUpdate');
                $router->delete('delete/{config_kind}', 'MedicalController@configKindDelete');
            });
            $router->group(['prefix' => 'project'], function (Router $router) {
                $router->get('list', 'MedicalController@configProjectList');
                $router->post('create', 'MedicalController@configProjectCreate');
                $router->put('update/{config_project}', 'MedicalController@configProjectUpdate');
                $router->delete('delete/{config_project}', 'MedicalController@configProjectDelete');
            });
            $router->group(['prefix' => 'subject'], function (Router $router) {
                $router->get('list', 'MedicalController@configSubjectList');
                $router->post('create', 'MedicalController@configSubjectCreate');
                $router->put('update/{config_subject}', 'MedicalController@configSubjectUpdate');
                $router->delete('delete/{config_subject}', 'MedicalController@configSubjectDelete');
            });
            $router->group(['prefix' => 'merit'], function (Router $router) {
                $router->get('list', 'MedicalController@configMeritList');
                $router->post('create', 'MedicalController@configMeritCreate');
                $router->put('update/{config_merit}', 'MedicalController@configMeritUpdate');
                $router->delete('delete/{config_merit}', 'MedicalController@configMeritDelete');
            });
        });
        $router->group(['prefix' => 'mechanism'], function (Router $router) {
            $router->get('list', 'MedicalController@mechanismList');
            $router->post('create', 'MedicalController@mechanismCreate');
            $router->put('update/{mechanism}', 'MedicalController@mechanismUpdate');
            $router->delete('delete/{mechanism}', 'MedicalController@mechanismDelete');
        });
    });
    $router->group(['prefix' => 'visit'], function (Router $router) {
        $router->get('mine', 'VisitController@planMine');
        $router->get('list', 'VisitController@planList');
        $router->get('total', 'VisitController@planTotal');
        $router->post('check/{visit_details}', 'VisitController@planCheck');
        $router->post('create', 'VisitController@planCreate');
        $router->get('records', 'VisitController@planRecords');
    });

    $router->group(['prefix' => 'member'], function (Router $router) {
        $router->get('list', 'MemberController@memberList');
        $router->post('create', 'MemberController@memberCreate');
        $router->put('update/{member}', 'MemberController@memberUpdate');
    });
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


