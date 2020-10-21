<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Model\Channel;
use App\Model\Department;
use App\Model\Manager;
use App\Model\Member;
use App\Model\MemberKind;
use App\Model\Permission;
use App\Model\RoleDoctor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class SystemController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function memberKindList(Request $request)
    {
        return $this->respondWithData(
            MemberKind::get()
        );
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
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

    /**
     * @param Request $request
     * @param MemberKind $memberKind
     * @return \Illuminate\Http\JsonResponse
     */
    public function memberKindUpdate(Request $request, MemberKind $memberKind)
    {
        $request->validate([
            'kind' => 'max:255',
            'desc' => 'max:255',
        ]);
        $data = $request->only(['kind', 'desc']);
        $memberKind->update(array_filter($data));
        return $this->respondWithSuccess();

    }

    /**
     * @param Request $request
     * @param MemberKind $memberKind
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function memberKindDelete(Request $request, MemberKind $memberKind)
    {
        if (Member::query()->where('member_kind_id', $memberKind->id)->exists()) {
            return $this->respondWithError('存在会员,删除失败');
        }
        $memberKind->delete();
        return $this->respondWithSuccess();
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function channelList(Request $request)
    {
        return $this->respondWithData(
            Channel::get()
        );
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
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

    /**
     * @param Request $request
     * @param Channel $channel
     * @return \Illuminate\Http\JsonResponse
     */
    public function channelUpdate(Request $request, Channel $channel)
    {
        $request->validate([
            'name' => 'string|nullable|max:255',
        ]);
        $data = $request->only(['name']);
        $channel->update(array_filter($data));
        return $this->respondWithSuccess();
    }

    /**
     * @param Request $request
     * @param Channel $channel
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function channelDelete(Request $request, Channel $channel)
    {
        if (Member::query()->where('channel_id', $channel->id)->exists()) {
            return $this->respondWithError('存在会员,删除失败');
        }
        $channel->delete();
        return $this->respondWithSuccess();
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function departmentList(Request $request)
    {
        return $this->respondWithData(
            Department::get()
        );
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
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

    /**
     * @param Request $request
     * @param Department $department
     * @return \Illuminate\Http\JsonResponse
     */
    public function departmentUpdate(Request $request, Department $department)
    {
        $request->validate([
            'name' => 'string|nullable|max:255',
        ]);
        $data = $request->only(['name']);
        $department->update(array_filter($data));
        return $this->respondWithSuccess();

    }

    /**
     * @param Request $request
     * @param Department $department
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function departmentDelete(Request $request, Department $department)
    {
        if (RoleDoctor::query()->where('doctor_department_id', $department->id)->exists()) {
            return $this->respondWithError('存在医生,删除失败');
        }
        $department->delete();
        return $this->respondWithSuccess();
    }

    const roleKind   = 1;
    const doctorKind = 2;

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function doctorList(Request $request)
    {
        $request->validate([
            'department_id' => 'integer',
        ]);
        return $this->respondWithData(
            RoleDoctor::with('managers', 'permissions', 'doctorDepartment')
                ->when($request->input('department_id'), function (Builder $query, $value) {
                    $query->where('doctor_department_id', $value);
                })->where('kind', self::doctorKind)->get()
        );
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function doctorCreate(Request $request)
    {
        $request->validate([
            'name'          => 'required|max:255',
            'department_id' => 'required|integer|exists:departments,id',
            'image'         => 'string|nullable|max:255',
            'desc'          => 'string|nullable|max:255',
        ]);
        RoleDoctor::create([
            'kind'                 => self::doctorKind,
            'role_is_admin'        => false,
            'role_name'            => '',
            'doctor_name'          => $request->input('name'),
            'doctor_desc'          => strval($request->input('desc')),
            'doctor_image'         => strval($request->input('image')),
            'doctor_department_id' => $request->input('department_id'),
            'doctor_can_meet'      => true,
        ]);
        return $this->respondWithSuccess();
    }

    /**
     * @param Request $request
     * @param RoleDoctor $doctor
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function doctorUpdate(Request $request, RoleDoctor $doctor)
    {
        if ($doctor->kind != self::doctorKind) {
            throw new \Exception('not found doctor');
        }
        $request->validate([
            'name'          => 'string|nullable|max:255',
            'department_id' => 'integer|exists:departments,id',
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

    /**
     * @param Request $request
     * @param RoleDoctor $doctor
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function doctorDelete(Request $request, RoleDoctor $doctor)
    {
        if ($doctor->kind != self::doctorKind) {
            throw new \Exception('not found doctor');
        }
        $doctor->delete();
        return $this->respondWithSuccess();
    }


    /**
     * @param Request $request
     * @param RoleDoctor $doctor
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function doctorManager(Request $request, RoleDoctor $doctor)
    {
        if ($doctor->kind != self::doctorKind) {
            throw new \Exception('not found doctor');
        }
        $request->validate([
            'account'       => 'string|nullable|max:255',
            'password'      => 'string|nullable|max:255',
            'status'        => 'boolean',
            'permissions'   => 'array',
            'permissions.*' => 'integer|exists:permissions,id',
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
            Manager::updateOrCreate([
                'role_doctor_id' => $doctor->id,
            ], $data);
            $doctor->permissions()->sync($request->input('permissions', []));
        });
        return $this->respondWithSuccess();
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function roleList(Request $request)
    {
        return $this->respondWithData(
            RoleDoctor::with('managers', 'permissions')->where('kind', self::roleKind)->get()
        );
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function roleCreate(Request $request)
    {
        $request->validate([
            'name'          => 'required|max:255',
            'permissions'   => 'array',
            'permissions.*' => 'integer|exists:permissions,id',
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

    /**
     * @param Request $request
     * @param RoleDoctor $role
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function roleUpdate(Request $request, RoleDoctor $role)
    {
        if ($role->kind != self::roleKind) {
            throw new \Exception('not found role');
        }
        $request->validate([
            'name'          => 'string|nullable|max:255',
            'permissions'   => 'array',
            'permissions.*' => 'integer|exists:permissions,id',
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

    /**
     * @param Request $request
     * @param RoleDoctor $role
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function roleDelete(Request $request, RoleDoctor $role)
    {
        if ($role->kind != self::roleKind) {
            throw new \Exception('not found role');
        }
        $role->delete();
        return $this->respondWithSuccess();
    }

    /**
     * @param Request $request
     * @param RoleDoctor $role
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function roleManagerCreate(Request $request, RoleDoctor $role)
    {
        if ($role->kind != self::roleKind) {
            throw new \Exception('not found role');
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

    /**
     * @param Request $request
     * @param Manager $manager
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function roleManagerUpdate(Request $request, Manager $manager)
    {
        $request->validate([
            'account'     => 'required|max:255',
            'password'    => 'required|max:255',
            're_password' => 'required|max:255',
        ]);
        if (!Hash::check($request->input('re_password'), $manager->password)) {
            throw new \Exception('password invalid');
        }
        $manager->update([
            'account'  => $request->input('account'),
            'password' => Hash::make($request->input('password'))
        ]);
        return $this->respondWithSuccess();
    }

    /**
     * @param Request $request
     * @param Manager $manager
     * @return \Illuminate\Http\JsonResponse
     */
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

    public function allRole(Request $request)
    {
        return $this->respondWithData(
            RoleDoctor::with('managers')->where('kind', self::roleKind)->get()
        );
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function permissionList(Request $request)
    {
        return $this->respondWithData(
            Permission::with('children')->where('parent_id', 0)->get()
        );
    }


    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required',
        ]);
        $file = $request->file('file');
        if (!$file->isValid()) {
            throw new \Exception('file is invalid');
        }
        $file     = $request->file('file');
        $filename = sprintf("%s.%s", uniqid('file-'), $file->getClientOriginalExtension());
        Storage::disk('public')->put($filename, file_get_contents($file->getRealPath()));

        return $this->respondWithData(
            Storage::disk('public')->url($filename)
        );
    }
}
