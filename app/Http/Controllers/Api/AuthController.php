<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Gregwar\Captcha\CaptchaBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AuthController extends Controller
{

    /**
     * AuthController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:api', [
            'except' => [
                'login', 'captcha'
            ]
        ]);
    }

    /**
     * 生成验证码
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function captcha(Request $request)
    {
        $builder = new CaptchaBuilder();
        $builder->build();
        $code = $builder->getPhrase();
        Cache::put(strtolower($code), strtolower($code), 120);
        return $this->respondWithData(
            $builder->inline()
        );
    }

    /**
     * 登录
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function login(Request $request)
    {
        $request->validate([
            'account'  => 'required|max:255',
            'password' => 'required|max:255',
            'captcha'  => 'required|max:255',
        ]);
        if (!Cache::has(strtolower($request->input('captcha')))) {
            throw new \Exception('无效的验证码', 401);
        }
        if (!$token = auth()->attempt($request->only(['account', 'password']))) {
            throw new \Exception('账户或密码错误', 401);
        }
        if (!$this->user()->status) {
            throw new \Exception('账户禁止登陆', 401);
        }
        return $this->respondWithToken($token);
    }

    /**
     * 登出
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        auth()->logout();
        return $this->respondWithSuccess();
    }

    /**
     * 刷新token
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * @param $user
     * @return string
     */
    protected function name($user)
    {
        switch ($user->roleDoctor->kind) {
            case 1:
                if ($user->roleDoctor->role_is_admin) {
                    return $user->roleDoctor->role_name;
                }
                return $user->name;
            case 2:
                return $user->roleDoctor->doctor_name;
            default:
                return '';
        }
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json([
            'manager_id'     => $this->user()->id,
            'role_doctor_id' => $this->user()->role_doctor_id,
            'name'           => $this->name($this->user()),
            'admin'          => $this->user()->roleDoctor->role_is_admin,
            'permissions'    => $this->user()->roleDoctor->permissions,
        ]);
    }

    /**
     * @param $token
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => auth()->factory()->getTTL() * 60
        ]);
    }
}
