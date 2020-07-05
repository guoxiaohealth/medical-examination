<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Model\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{

    public function list()
    {
        $roles = Role::with('permissions')->get()->map(function ($v) {
            $v->managers = Role::managers($v->id);
            $v->managers_count = Role::managersCount($v->id);
            return $v;
        });
        return $this->data($roles);
    }

    public function create(Request $request)
    {
        $request->validate([
            'name'          => 'required|max:255',
            'permissions'   => 'array',
            'permissions.*' => 'integer',
        ]);
        DB::transaction(function () use ($request) {
            Role::create([
                'name'  => $request->input('name'),
                'desc'  => '',
                'admin' => false,
            ])->permissions()->sync($request->input('permissions', []));
        });
        return $this->success();
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name'          => 'max:255',
            'permissions'   => 'array',
            'permissions.*' => 'integer',
        ]);
        DB::transaction(function () use ($request, $role) {
            $role->update([
                'name' => $request->input('name'),
            ]);
            $role->permissions()->sync($request->input('permissions', []));
        });
        return $this->success();
    }

    public function delete(Request $request, Role $role)
    {
        if (Role::managersCount($role->id)) {
            return $this->error('该角色下已有人员，不能删除');
        }
        $role->delete();
        return $this->success();
    }
}
