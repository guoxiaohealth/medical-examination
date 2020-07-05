<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Model\Channel;
use App\Model\Department;
use App\Model\Doctor;
use App\Model\Member;
use App\Model\Permission;
use App\Model\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SystemController extends Controller
{
    public function memberList(Request $request)
    {
        return $this->data(
            Member::query()->get()
        );
    }

    public function memberCreate(Request $request)
    {
        $request->validate([
            'kind' => 'required|max:255',
            'desc' => 'required|max:255',
        ]);
        Member::query()->create([
            'kind' => $request->input('kind'),
            'desc' => $request->input('desc'),
        ]);
        return $this->success();
    }

    public function memberUpdate(Request $request, Member $member)
    {
        $request->validate([
            'kind' => 'max:255',
            'desc' => 'max:255',
        ]);
        $data = $request->only(['kind', 'desc']);
        $member->update($data);
        return $this->success();

    }

    public function memberDelete(Request $request, Member $member)
    {
        $member->delete();
        return $this->success();
    }


    public function channelList(Request $request)
    {
        return $this->data(
            Channel::query()->get()
        );
    }

    public function channelCreate(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255',
        ]);
        Channel::query()->create([
            'name' => $request->input('name'),
        ]);
        return $this->success();
    }

    public function channelUpdate(Request $request, Channel $channel)
    {
        $request->validate([
            'name' => 'string|nullable|max:255',
        ]);
        $data = $request->only(['name']);
        $channel->update($data);
        return $this->success();

    }

    public function channelDelete(Request $request, Channel $channel)
    {
        $channel->delete();
        return $this->success();
    }


    public function departmentList(Request $request)
    {
        return $this->data(
            Department::query()->get()
        );
    }

    public function departmentCreate(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255',
        ]);
        Department::query()->create([
            'name' => $request->input('name'),
        ]);
        return $this->success();
    }

    public function departmentUpdate(Request $request, Department $department)
    {
        $request->validate([
            'name' => 'string|nullable|max:255',
        ]);
        $data = $request->only(['name']);
        $department->update($data);
        return $this->success();

    }

    public function departmentDelete(Request $request, Department $department)
    {
        $department->delete();
        return $this->success();
    }


    public function doctorList(Request $request)
    {
        return $this->data(
            Doctor::query()->get()
        );
    }

    public function doctorCreate(Request $request)
    {
        $request->validate([
            'name'          => 'required|max:255',
            'department_id' => 'required|integer',
            'image'         => 'string|nullable|max:255',
            'desc'          => 'string|nullable|max:255',
        ]);
        Doctor::query()->create([
            'name'          => $request->input('account'),
            'department_id' => $request->input('department_id'),
            'image'         => $request->input('image', ''),
            'desc'          => $request->input('desc', ''),
        ]);
        return $this->success();
    }

    public function doctorUpdate(Request $request, Doctor $doctor)
    {
        $request->validate([
            'name'          => 'string|nullable|max:255',
            'department_id' => 'integer',
            'image'         => 'string|nullable|max:255',
            'desc'          => 'string|nullable|max:255',
        ]);
        $data = $request->only(['name', 'department_id', 'image', 'desc']);
        $doctor->update($data);
        return $this->success();

    }

    public function doctorDelete(Request $request, Doctor $doctor)
    {
        $doctor->delete();
        return $this->success();
    }


    public function roleList(Request $request)
    {
        return $this->data(
            Role::query()->get()
        );
    }

    public function roleCreate(Request $request)
    {
        $request->validate([
            'name'          => 'required|max:255',
            'permissions'   => 'array',
            'permissions.*' => 'integer',
        ]);
        DB::transaction(function () use ($request) {
            Role::query()->create([
                'name'  => $request->input('name'),
                'desc'  => '',
                'admin' => false,
            ])->permissions()->sync($request->input('permissions', []));
        });
        return $this->success();
    }

    public function roleUpdate(Request $request, Role $role)
    {
        $request->validate([
            'name'          => 'max:255',
            'permissions'   => 'array',
            'permissions.*' => 'integer',
        ]);
        DB::transaction(function () use ($request, $role) {
            $role->update($request->only(['name']));
            $role->permissions()->sync($request->input('permissions', []));
        });
        return $this->success();

    }

    public function roleDelete(Request $request, Role $role)
    {
        $role->delete();
        return $this->success();
    }

    public function permissionList(Request $request)
    {
        return $this->data(
            Permission::with('children')->where('parent_id', 0)->get()
        );
    }
}
