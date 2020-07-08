<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Model\Member;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    public function memberList(Request $request)
    {
        $request->validate([
            'search'         => 'string|nullable|max:255',
            'member_kind_id' => 'integer',
            'channel_id'     => 'integer',
        ]);
        return $this->respondWithData(
            Member::with('memberKind', 'channel')->when($request->input('member_kind_id'), function (Builder $query, $value) {
                $query->where('member_kind_id', $value);
            })->when($request->input('channel_id'), function (Builder $query, $value) {
                $query->where('channel_id', $value);
            })->when($request->input('search'), function (Builder $query, $value) {
                $query->whereRaw('CONCAT(name,mobile) LIKE ?', "%{$value}%");
            })->paginate(10)
        );
    }

    public function memberCreate(Request $request)
    {
        $request->validate([
            'name'           => 'required|max:255',
            'sex'            => 'required|in:1,2',
            'birthday'       => 'required|date_format:Y-m-d H:i:s',
            'mobile'         => 'required|size:11',
            'member_kind_id' => 'required|integer|exists:member_kinds,id',
            'channel_id'     => 'integer|exists:channels,id',
            'remarks'        => 'string|nullable|max:255',
        ]);
        Member::create([
            'name'           => $request->input('name'),
            'sex'            => $request->input('sex'),
            'birthday'       => $request->input('birthday'),
            'mobile'         => $request->input('mobile'),
            'member_kind_id' => $request->input('member_kind_id'),
            'channel_id'     => $request->input('channel_id'),
            'remarks'        => strval($request->input('remarks')),
        ]);
        return $this->respondWithSuccess();
    }

    public function memberUpdate(Request $request, Member $member)
    {
        $request->validate([
            'name'           => 'string|nullable|max:255',
            'sex'            => 'in:1,2',
            'birthday'       => 'date_format:Y-m-d H:i:s',
            'mobile'         => 'size:11',
            'member_kind_id' => 'integer|exists:member_kinds,id',
            'channel_id'     => 'integer|exists:channels,id',
            'remarks'        => 'string|nullable|max:255',
        ]);
        $data = $request->only(['name', 'sex', 'birthday', 'mobile', 'member_kind_id', 'channel_id', 'remarks']);
        $member->update(array_filter($data));
        return $this->respondWithSuccess();
    }
}
