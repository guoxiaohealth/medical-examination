<?php

use Illuminate\Database\Seeder;


class InitSeeder extends Seeder
{

    public function run()
    {
        \Illuminate\Support\Facades\DB::transaction(function () {
            $role = \App\Model\Role::create([
                'name'  => '超级管理员',
                'desc'  => '',
                'admin' => true,
            ]);
            $manager = \App\Model\Manager::create([
                'account'  => 'admin',
                'password' => \Illuminate\Support\Facades\Hash::make('123456'),
                'model'    => \App\Model\Role::class,
                'model_id' => $role->id,
                'name'     => '',
                'status'   => true,
            ]);

            \App\Model\Permission::create([
                'name' => '咨询就诊', 'desc' => '', 'parent_id' => 0
            ])->children()->createMany([
                ['name' => '预约病人', 'desc' => ''],
                ['name' => '病人档案', 'desc' => ''],
                ['name' => '全部病人', 'desc' => ''],
            ]);
            \App\Model\Permission::create([
                'name' => '预约管理', 'desc' => '', 'parent_id' => 0
            ])->children()->createMany([
                ['name' => '今日预约', 'desc' => ''],
                ['name' => '预约记录', 'desc' => ''],
            ]);
            \App\Model\Permission::create([
                'name' => '体检中心', 'desc' => '', 'parent_id' => 0
            ])->children()->createMany([
                ['name' => '体检报告', 'desc' => ''],
                ['name' => '体检方案', 'desc' => ''],
                ['name' => '体检设置', 'desc' => ''],
                ['name' => '体检机构', 'desc' => ''],
                ['name' => '体检异常', 'desc' => ''],
            ]);
            \App\Model\Permission::create([
                'name' => '健康跟踪', 'desc' => '', 'parent_id' => 0
            ])->children()->createMany([
                ['name' => '我的回访', 'desc' => ''],
                ['name' => '全部回访', 'desc' => ''],
                ['name' => '回访计划', 'desc' => ''],
            ]);
            \App\Model\Permission::create([
                'name' => '会员管理', 'desc' => '', 'parent_id' => 0
            ])->children()->createMany([
                ['name' => '会员管理', 'desc' => ''],
            ]);
            \App\Model\Permission::create([
                'name' => '系统设置', 'desc' => '', 'parent_id' => 0
            ])->children()->createMany([
                ['name' => '会员类型', 'desc' => ''],
                ['name' => '渠道管理', 'desc' => ''],
                ['name' => '科室管理', 'desc' => ''],
                ['name' => '医生管理', 'desc' => ''],
                ['name' => '权限管理', 'desc' => ''],
            ]);
        });
    }
}
