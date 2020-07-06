<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Model\Channel;
use App\Model\Department;
use App\Model\Manager;
use App\Model\MemberKind;
use App\Model\Permission;
use App\Model\RoleDoctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SystemController extends Controller
{
    public function memberKindList(Request $request)
    {
        return $this->respondWithData(
            MemberKind::query()->get()
        );
    }

    public function memberKindCreate(Request $request)
    {
        $request->validate([
            'kind' => 'required|max:255',
            'desc' => 'required|max:255',
        ]);
        MemberKind::create([
            'kind' => $request->input('kind'),
            'desc' => $request->input('desc'),
        ]);
        return $this->respondWithSuccess();
    }

    public function memberKindUpdate(Request $request, MemberKind $memberKind)
    {
        $request->validate([
            'kind' => 'max:255',
            'desc' => 'max:255',
        ]);
        $data = $request->only(['kind', 'desc']);
        $memberKind->update($data);
        return $this->respondWithSuccess();

    }

    public function memberKindDelete(Request $request, MemberKind $memberKind)
    {
        $memberKind->delete();
        return $this->respondWithSuccess();
    }


    public function channelList(Request $request)
    {
        return $this->respondWithData(
            Channel::get()
        );
    }

    public function channelCreate(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255',
        ]);
        Channel::create([
            'name' => $request->input('name'),
        ]);
        return $this->respondWithSuccess();
    }

    public function channelUpdate(Request $request, Channel $channel)
    {
        $request->validate([
            'name' => 'string|nullable|max:255',
        ]);
        $data = $request->only(['name']);
        $channel->update($data);
        return $this->respondWithSuccess();
    }

    public function channelDelete(Request $request, Channel $channel)
    {
        $channel->delete();
        return $this->respondWithSuccess();
    }


    public function departmentList(Request $request)
    {
        return $this->respondWithData(
            Department::get()
        );
    }

    public function departmentCreate(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255',
        ]);
        Department::create([
            'name' => $request->input('name'),
        ]);
        return $this->respondWithSuccess();
    }

    public function departmentUpdate(Request $request, Department $department)
    {
        $request->validate([
            'name' => 'string|nullable|max:255',
        ]);
        $data = $request->only(['name']);
        $department->update($data);
        return $this->respondWithSuccess();

    }

    public function departmentDelete(Request $request, Department $department)
    {
        $department->delete();
        return $this->respondWithSuccess();
    }

    const roleKind = 1;
    const doctorKind = 2;

    public function doctorList(Request $request)
    {
        return $this->respondWithData(
            RoleDoctor::where('kind', self::doctorKind)->get()->map(function ($v) {
                return [
                    'id'          => $v->id,
                    'name'        => $v->doctor_name,
                    'desc'        => $v->doctor_desc,
                    'image'       => $v->doctor_image,
                    'department'  => $v->doctor_department,
                    'can_meet'    => $v->doctor_can_meet,
                    'manager'     => Manager::where('role_doctor_id', $v->id)->first(),
                    'permissions' => $v->permissions,
                    'created_at'  => $v->created_at,
                    'updated_at'  => $v->updated_at,
                ];
            })
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
        RoleDoctor::create([
            'kind'                 => self::doctorKind,
            'role_is_admin'        => false,
            'role_name'            => '',
            'doctor_name'          => $request->input('name'),
            'doctor_desc'          => $request->input('desc', ''),
            'doctor_image'         => $request->input('image', ''),
            'doctor_department_id' => $request->input('department_id'),
            'doctor_can_meet'      => true,
        ]);
        return $this->respondWithSuccess();
    }

    public function doctorUpdate(Request $request, RoleDoctor $doctor)
    {
        if ($doctor->kind != self::doctorKind) {
            return $this->respondWithError('not found doctor');
        }
        $request->validate([
            'name'          => 'string|nullable|max:255',
            'department_id' => 'integer',
            'image'         => 'string|nullable|max:255',
            'desc'          => 'string|nullable|max:255',
            'status'        => 'boolean',
        ]);
        $data = [];
        if ($request->has('name')) {
            $data['doctor_name'] = $request->input('name');
        }
        if ($request->has('department_id')) {
            $data['doctor_department_id'] = $request->input('department_id');
        }
        if ($request->has('image')) {
            $data['doctor_image'] = $request->input('image');
        }
        if ($request->has('desc')) {
            $data['doctor_desc'] = $request->input('desc');
        }
        if ($request->has('status')) {
            $data['doctor_can_meet'] = $request->input('status');
        }
        $doctor->update($data);
        return $this->respondWithSuccess();

    }

    public function doctorDelete(Request $request, RoleDoctor $doctor)
    {
        if ($doctor->kind != self::doctorKind) {
            return $this->respondWithError('not found doctor');
        }
        $doctor->delete();
        return $this->respondWithSuccess();
    }

    public function doctorManager(Request $request, RoleDoctor $doctor)
    {
        if ($doctor->kind != self::doctorKind) {
            return $this->respondWithError('not found doctor');
        }
        $request->validate([
            'account'       => 'string|nullable|max:255',
            'password'      => 'string|nullable|integer',
            'status'        => 'boolean',
            'permissions'   => 'array',
            'permissions.*' => 'integer',
        ]);
        DB::transaction(function () use ($request, $doctor) {
            $data = [];
            if ($request->has('account')) {
                $data['account'] = $request->input('account');
            }
            if ($request->has('password')) {
                $data['password'] = Hash::make($request->input('password'));
            }
            if ($request->has('status')) {
                $data['status'] = $request->input('status');
            }
            Manager::query()->updateOrCreate([
                'role_doctor_id' => $doctor->id,
            ], $data);
            $doctor->permissions()->sync($request->input('permissions', []));
        });
        return $this->respondWithSuccess();
    }


    public function roleList(Request $request)
    {
        return $this->respondWithData(
            RoleDoctor::where('kind', self::roleKind)->get()->map(function ($v) {
                return [
                    'id'          => $v->id,
                    'name'        => $v->role_name,
                    'is_admin'    => $v->role_is_admin,
                    'manager'     => Manager::where('role_doctor_id', $v->id)->get(),
                    'permissions' => $v->permissions,
                    'created_at'  => $v->created_at,
                    'updated_at'  => $v->updated_at,
                ];
            })
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
            RoleDoctor::create([
                'kind'                 => self::roleKind,
                'role_name'            => $request->input('name'),
                'role_is_admin'        => false,
                'doctor_name'          => '',
                'doctor_desc'          => '',
                'doctor_image'         => '',
                'doctor_department_id' => 0,
                'doctor_can_meet'      => false,
            ])->permissions()->sync($request->input('permissions', []));
        });
        return $this->respondWithSuccess();
    }

    public function roleUpdate(Request $request, RoleDoctor $role)
    {
        if ($role->kind != self::roleKind) {
            return $this->respondWithError('not found role');
        }
        $request->validate([
            'name'          => 'string|nullable|max:255',
            'permissions'   => 'array',
            'permissions.*' => 'integer',
        ]);
        DB::transaction(function () use ($request, $role) {
            $data = [];
            if ($request->has('name')) {
                $data['role_name'] = $request->input('name', '');
            }
            $role->update($data);
            $permissions = [];
            if ($request->has('permissions')) {
                $permissions = $request->input('permissions', []);
            }
            $role->permissions()->sync($permissions);
        });
        return $this->respondWithSuccess();

    }

    public function roleDelete(Request $request, RoleDoctor $role)
    {
        if ($role->kind != self::roleKind) {
            return $this->respondWithError('not found role');
        }
        $role->delete();
        return $this->respondWithSuccess();
    }

    public function roleManagerCreate(Request $request, RoleDoctor $role)
    {
        if ($role->kind != self::roleKind) {
            return $this->respondWithError('not found role');
        }
        $request->validate([
            'account'  => 'required|max:255',
            'password' => 'required|max:255',
            'name'     => 'required|max:255',
        ]);
        Manager::create([
            'account'        => $request->input('account'),
            'password'       => Hash::make($request->input('password')),
            'status'         => true,
            'name'           => $request->input('name'),
            'role_doctor_id' => $role->id,
        ]);
        return $this->respondWithSuccess();
    }

    public function roleManagerUpdate(Request $request, Manager $manager)
    {
        $request->validate([
            'account'     => 'required|max:255',
            'password'    => 'required|max:255',
            're_password' => 'required|max:255',
        ]);
        if (!Hash::check($request->input('re_password'), $manager->password)) {
            return $this->respondWithError('password invalid');
        }
        $manager->update([
            'account'  => $request->input('account'),
            'password' => Hash::make($request->input('password'))
        ]);
        return $this->respondWithSuccess();
    }

    public function roleManagerStatus(Request $request, Manager $manager)
    {
        $request->validate([
            'status' => 'required|boolean',
        ]);
        $manager->update([
            'status' => $request->input('status'),
        ]);
        return $this->respondWithSuccess();
    }

    public function permissionList(Request $request)
    {
        return $this->respondWithData(
            Permission::with('children')->where('parent_id', 0)->get()
        );
    }
}
