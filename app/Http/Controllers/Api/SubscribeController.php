<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Model\Subscribe;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class SubscribeController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function reserveTodayList(Request $request)
    {
        return $this->respondWithData(
            Subscribe::with('member', 'member.memberKind', 'member.channel', 'doctor', 'doctor.doctorDepartment')
                ->whereDay('date', Carbon::today())->get()
        );
    }

    public function reserveNewList(Request $request)
    {
        $request->validate([
            'doctor_id' => 'required|integer',
            'date'      => 'required|date_format:Y-m',
        ]);
        $date      = Carbon::parse($request->input('date'));
        $subscribe = Subscribe::query()->whereBetween('date',
            [$date->clone()->startOfMonth(), $date->clone()->endOfMonth()])
            ->where('doctor_id', $request->input('doctor_id'))
            ->get();
        $period    = $date->daysUntil($date->clone()->endOfMonth());
        $data      = [];
        foreach ($period as $date) {
            $days              = $date->format('Y-m-d');
            $xx                = $subscribe->filter(function ($v) use ($days) {
                return Str::contains($v->date, $days);
            });
            $data[$days]['am'] = $xx->filter(function ($v) {
                return Carbon::parse($v->date)->format('A') == 'AM';
            });
            $data[$days]['pm'] = $xx->filter(function ($v) {
                return Carbon::parse($v->date)->format('A') == 'PM';
            });
        }
        return $this->respondWithData($data);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
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

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
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

    /**
     * @param Request $request
     * @param Subscribe $subscribe
     * @return \Illuminate\Http\JsonResponse
     */
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

    /**
     * @param Request $request
     * @param Subscribe $subscribe
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function reserveDelete(Request $request, Subscribe $subscribe)
    {
        $subscribe->delete();
        return $this->respondWithSuccess();
    }
}
