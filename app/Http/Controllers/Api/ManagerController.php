<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Model\Manager;
use App\Model\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ManagerController extends Controller
{

    public function create(Request $request)
    {
        $request->validate([
            'name'     => 'required|max:255',
            'account'  => 'required|max:255',
            'password' => 'required|max:255',
            'model'    => 'required|in:' . implode(',', [Role::class]),
            'model_id' => 'required|integer',
        ]);
        Manager::create([
            'account'  => $request->input('account'),
            'password' => Hash::make($request->input('password')),
            'model'    => $request->input('model'),
            'model_id' => $request->input('model_id'),
            'name'     => $request->input('name'),
            'status'   => true,
        ]);
        return $this->success();
    }

    public function update(Request $request, Manager $manager)
    {
        $request->validate([
            'name'        => 'string|max:255',
            'account'     => 'string|max:255',
            'password'    => 'string|max:255',
            're_password' => 'string|max:255|required_with:password',
            'status'      => 'boolean',
        ]);
        $data = $request->only(['name', 'account', 'status']);
        if ($request->has('password') && $request->has('re_password')) {
            if (Hash::check($request->has('re_password'), $manager->password)) {
                $data = array_merge($data, [
                    'password' => Hash::make($request->input('password')),
                ]);
            }
            return $this->error('旧的管理员登录密码错误');
        }
        $manager->update($data);
        return $this->success();
    }
}
