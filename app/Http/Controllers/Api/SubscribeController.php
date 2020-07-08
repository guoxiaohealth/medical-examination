<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Model\Subscribe;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class SubscribeController extends Controller
{
    public function reserveTodayList(Request $request)
    {
        return $this->respondWithData(
            Subscribe::with('member', 'member.memberKind', 'member.channel', 'doctor', 'doctor.doctorDepartment')
                ->where('date', '>=', Carbon::today())->get()
        );
    }

    public function reserveList(Request $request)
    {
        $request->validate([
            'search' => 'string|nullable|max:255',
            'date_s' => 'date_format:Y-m-d H:i',
            'date_e' => 'date_format:Y-m-d H:i',
            'status' => 'in:1,2,3',
        ]);
        return $this->respondWithData(
            Subscribe::with('member', 'member.memberKind', 'member.channel', 'doctor', 'doctor.doctorDepartment')
                ->when($request->input('date_s'), function (Builder $query, $value) {
                    $query->where('date', '>=', $value);
                })->when($request->input('date_e'), function (Builder $query, $value) {
                    $query->where('date', '<=', $value);
                })->when($request->input('status'), function (Builder $query, $value) {
                    $query->where('status', $value);
                })->when($request->input('search'), function (Builder $query, $value) {
                    $query->whereHas('member', function (Builder $query) use ($value) {
                        $query->whereRaw('CONCAT(name,mobile) LIKE ?', "%{$value}%");
                    });
                })->paginate()
        );
    }

    public function reserveCreate(Request $request)
    {
        $request->validate([
            'member_id' => 'required|integer|exists:members,id',
            'doctor_id' => 'required|integer|exists:roles_doctors,id',
            'date'      => 'required|date_format:Y-m-d H:i',
        ]);
        $subscribe = Subscribe::query()->where([
            'doctor_id' => $request->input('doctor_id'),
            'date'      => $request->input('date'),
        ])->exists();
        if ($subscribe) {
            throw new \Exception('date already reserved');
        }
        Subscribe::create([
            'member_id' => $request->input('member_id'),
            'doctor_id' => $request->input('doctor_id'),
            'date'      => $request->input('date'),
            'status'    => 1,
            'remarks'   => '',
        ]);
        return $this->respondWithSuccess();
    }

    public function reserveUpdate(Request $request, Subscribe $subscribe)
    {
        $request->validate([
            'remarks' => 'string|nullable|max:255',
            'status'  => 'in:1,2,3'
        ]);
        $data = $request->only(['remarks', 'status']);
        $subscribe->update(array_filter($data));
        return $this->respondWithSuccess();
    }

    public function reserveDelete(Request $request, Subscribe $subscribe)
    {
        $subscribe->delete();
        return $this->respondWithSuccess();
    }
}
