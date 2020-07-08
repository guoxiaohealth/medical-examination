<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Model\ConfigKind;
use App\Model\ConfigMerit;
use App\Model\ConfigProject;
use App\Model\ConfigSubject;
use App\Model\Diagnosis;
use App\Model\MedicalPlan;
use App\Model\Subscribe;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ReserveController extends Controller
{
    public function subscribeTodayList(Request $request)
    {
        return $this->respondWithData(
            Subscribe::with('member', 'member.memberKind', 'member.channel', 'doctor', 'doctor.doctorDepartment')
                ->where('date', '>=', Carbon::today())->get()
        );
    }

    public function subscribeWeekList(Request $request)
    {
        return $this->respondWithData(
            Subscribe::with('member', 'member.memberKind', 'member.channel', 'doctor', 'doctor.doctorDepartment')
                ->where('date', '>=', Carbon::now()->startOfWeek())->get()
        );
    }

    public function subscribeMonthsList(Request $request)
    {
        return $this->respondWithData(
            Subscribe::with('member', 'member.memberKind', 'member.channel', 'doctor', 'doctor.doctorDepartment')
                ->where('date', '>=', Carbon::now()->startOfMonth())->get()
        );
    }

    public function subscribeTotalList(Request $request)
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

    public function diagnosisList(Request $request)
    {
        return $this->respondWithData(
            Diagnosis::with('member', 'member.memberKind', 'member.channel', 'doctor')
                ->orderByDesc('id')->get()
        );
    }

    public function diagnosisCreate(Request $request)
    {
        $request->validate([
            'subscribe_id' => 'required|integer|exists:subscribes,id',
            'member_id'    => 'required|integer|exists:members,id',
            'doctor_id'    => 'required|integer|exists:roles_doctors,id',
            'conclusion'   => 'string|nullable|max:255',
            'suggest'      => 'string|nullable|max:255',
            'remarks'      => 'string|nullable|max:255',
        ]);
        DB::transaction(function () use ($request) {
            Subscribe::where('id', $request->input('subscribe_id'))->update([
                'status' => 2,
            ]);
            Diagnosis::query()->updateOrCreate([
                'member_id'    => $request->input('member_id'),
                'subscribe_id' => $request->input('subscribe_id'),
                'doctor_id'    => $request->input('doctor_id'),
            ], [
                'times'      => Diagnosis::where('member_id', $request->input('member_id'))->count() + 1,
                'no'         => base_convert(uniqid(), 16, 10),
                'conclusion' => $request->input('conclusion'),
                'suggest'    => $request->input('suggest'),
                'remarks'    => $request->input('remarks'),
            ]);
        });
        return $this->respondWithSuccess();
    }

    public function diagnosisFinish(Request $request)
    {
        $request->validate([
            'subscribe_id' => 'required|integer|exists:subscribes,id',
        ]);
        Subscribe::where('id', $request->input('subscribe_id'))->update([
            'status' => 3,
        ]);
        return $this->respondWithSuccess();
    }


    public function medicalPlanList(Request $request)
    {
        $request->validate([
            'member_id' => 'required|integer',
        ]);
        $plan        = MedicalPlan::where('member_id', $request->input('member_id'))->firstOrFail();
        $plan->kinds = $this->medicalPlanKinds($plan->kinds);
        return $this->respondWithData($plan);
    }

    public function medicalPlanCreate(Request $request)
    {
        $request->validate([
            'member_id'                                    => 'required|integer|exists:members,id',
            'doctor_id'                                    => 'required|integer|exists:roles_doctors,id',
            'kinds'                                        => 'array',
            'kinds.*.id'                                   => 'required_with:kinds|integer|exists:config_kinds,id',
            'kinds.*.projects'                             => 'required_with:kinds|array',
            'kinds.*.projects.*.id'                        => 'required_with:kinds|integer|exists:config_projects,id',
            'kinds.*.projects.*.subjects'                  => 'required_with:kinds|array',
            'kinds.*.projects.*.subjects.*.id'             => 'required_with:kinds|integer|exists:config_subjects,id',
            'kinds.*.projects.*.subjects.*.original'       => 'string|nullable|max:255',
            'kinds.*.projects.*.subjects.*.date'           => 'required_with:kinds|date_format:Y-m-d',
            'kinds.*.projects.*.subjects.*.merits'         => 'required_with:kinds|array',
            'kinds.*.projects.*.subjects.*.merits.*.id'    => 'required_with:kinds|integer|exists:config_merits,id',
            'kinds.*.projects.*.subjects.*.merits.*.value' => 'string|nullable|max:255',
        ]);
        MedicalPlan::create([
            'member_id' => $request->input('member_id'),
            'doctor_id' => $request->input('doctor_id'),
            'kinds'     => $this->makeMedicalPlanKinds($request->input('kinds')),
            'times'     => MedicalPlan::where('member_id', $request->input('member_id'))->count() + 1,
        ]);
        return $this->respondWithSuccess();
    }
}
