<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Model\ConfigKind;
use App\Model\ConfigMerit;
use App\Model\ConfigProject;
use App\Model\ConfigSubject;
use App\Model\Diagnosis;
use App\Model\DiagnosisOpearate;
use App\Model\MedicalPlan;
use App\Model\Member;
use App\Model\Subscribe;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReserveController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function subscribeTodayList(Request $request)
    {
        $subscribes = Subscribe::with('member', 'member.memberKind', 'member.channel', 'doctor',
            'doctor.doctorDepartment')
            ->where('doctor_id', $this->user()->role_doctor_id)
            ->whereBetween('date', [Carbon::now()->startOfDay(), Carbon::now()->endOfDay()])->get();

        return $this->respondWithData([
            'am' => $subscribes->filter(function ($v) {
                $hours = Carbon::parse($v->date)->format('H');
                return $hours <= 12;
            })->values(),
            'pm' => $subscribes->filter(function ($v) {
                $hours = Carbon::parse($v->date)->format('H');
                return $hours > 12 && $hours <= 18;
            })->values(),
            'ni' => $subscribes->filter(function ($v) {
                $hours = Carbon::parse($v->date)->format('H');
                return $hours > 18 && $hours <= 24;
            })->values(),
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function subscribeWeekList(Request $request)
    {
        $subscribes = Subscribe::with('member', 'member.memberKind', 'member.channel', 'doctor',
            'doctor.doctorDepartment')
            ->where('doctor_id', $this->user()->role_doctor_id)
            ->where('date', '>=', Carbon::now()->startOfWeek())
            ->where('date', '<=', Carbon::now()->endOfWeek())->get();

        $date = Carbon::now();

        $first = $date->clone()->subDays($date->clone()->dayOfWeek);

        $end = $date->clone()->addDays(7 - $date->clone()->dayOfWeek - 1);

        $period = $first->daysUntil($end);
        $data   = [];

        foreach ($period as $date) {
            $days              = $date->format('Y-m-d');
            $currentDay        = $subscribes->filter(function ($v) use ($days) {
                return Str::contains($v->date, $days);
            });
            $data[$days]['am'] = $currentDay->filter(function ($v) {
                $hours = Carbon::parse($v->date)->format('H');
                return $hours <= 12;
            })->values();
            $data[$days]['pm'] = $currentDay->filter(function ($v) {
                $hours = Carbon::parse($v->date)->format('H');
                return $hours > 12 && $hours <= 18;
            })->values();
            $data[$days]['ni'] = $currentDay->filter(function ($v) {
                $hours = Carbon::parse($v->date)->format('H');
                return $hours > 18 && $hours <= 24;
            })->values();
        }
        return $this->respondWithData($data);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function subscribeMonthList(Request $request)
    {
        $subscribes = Subscribe::with('member', 'member.memberKind', 'member.channel', 'doctor',
            'doctor.doctorDepartment')
            ->where('doctor_id', $this->user()->role_doctor_id)
            ->whereBetween('date', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])->get();

        $date   = Carbon::now();
        $period = $date->clone()->startOfMonth()->daysUntil($date->clone()->endOfMonth());
        $data   = [];
        foreach ($period as $date) {
            $days              = $date->format('Y-m-d');
            $currentDay        = $subscribes->filter(function ($v) use ($days) {
                return Str::contains($v->date, $days);
            });
            $data[$days]['am'] = $currentDay->filter(function ($v) {
                $hours = Carbon::parse($v->date)->format('H');
                return $hours <= 12;
            })->values();
            $data[$days]['pm'] = $currentDay->filter(function ($v) {
                $hours = Carbon::parse($v->date)->format('H');
                return $hours > 12 && $hours <= 18;
            })->values();
            $data[$days]['ni'] = $currentDay->filter(function ($v) {
                $hours = Carbon::parse($v->date)->format('H');
                return $hours > 18 && $hours <= 24;
            })->values();
        }
        return $this->respondWithData($data);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
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
                ->where('doctor_id', optional(auth()->user())->role_doctor_id)
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
     */
    public function diagnosisCreate(Request $request)
    {
        $request->validate([
            'subscribe_id' => 'required|integer|exists:subscribes,id',
            'member_id'    => 'required|integer|exists:members,id',
            'conclusion'   => 'string|nullable|max:255',
            'suggest'      => 'string|nullable|max:255',
            'remarks'      => 'string|nullable|max:255',
        ]);
        DB::transaction(function () use ($request) {
            Subscribe::where('id', $request->input('subscribe_id'))->update([
                'status' => 2,
            ]);
            $diagnosis = Diagnosis::query()->where('subscribe_id', $request->input('subscribe_id'))->first();
            if ($diagnosis) {
                $diagnosis->update([
                    'doctor_id'  => $this->user()->role_doctor_id,
                    'conclusion' => $request->input('conclusion'),
                    'suggest'    => $request->input('suggest'),
                    'remarks'    => $request->input('remarks'),
                ]);
                DiagnosisOpearate::create([
                    'role_doctor_id' => $this->user()->role_doctor_id,
                    'diagnosis_id'   => $diagnosis->id,
                    'operate'        => 1
                ]);
            } else {
                $diagnosis = Diagnosis::query()->create([
                    'subscribe_id' => $request->input('subscribe_id'),
                    'member_id'    => $request->input('member_id'),
                    'doctor_id'    => $this->user()->role_doctor_id,
                    'times'        => Diagnosis::where('member_id', $request->input('member_id'))->count() + 1,
                    'no'           => base_convert(uniqid(), 16, 10),
                    'conclusion'   => $request->input('conclusion'),
                    'suggest'      => $request->input('suggest'),
                    'remarks'      => $request->input('remarks'),
                ]);
                DiagnosisOpearate::create([
                    'role_doctor_id' => $this->user()->role_doctor_id,
                    'diagnosis_id'   => $diagnosis->id,
                    'operate'        => 2
                ]);
            }
        });
        return $this->respondWithSuccess();
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function diagnosisCheck(Request $request)
    {
        $request->validate([
            'subscribe_id' => 'required|integer|exists:subscribes,id',
        ]);
        $subscribe = Subscribe::with('diagnose', 'member', 'member.memberKind', 'member.medicalPlans', 'doctor')
            ->where('id', $request->input('subscribe_id'))
            ->first();
        if (!$subscribe) {
            $medicalPlans = collect(optional($subscribe->member)->medicalPlans);
            //
            $subscribe->medical_plan_date = optional($medicalPlans->last())->updated_at;
            //
            $subscribe->medical_plan_merits_abnormal = $this->medicalPlanMerits(
                $medicalPlans->pluck('kinds.*.projects.*.subjects.*.merits')->flatten(2)->toArray()
            )->pluck('ex.*.status')->flatten(1)->filter(function ($v) {
                return $v === false;
            })->count();
        }
        return $this->respondWithData($subscribe);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
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

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function diagnosisHistory(Request $request)
    {
        $request->validate([
            'diagnosis_id' => 'required|integer',
        ]);
        return $this->respondWithData(
            DiagnosisOpearate::with('roleDoctor', 'diagnosis')
                ->where('diagnosis_id', $request->input('diagnosis_id'))->get()
        );
    }

    public function diagnosisMineList(Request $request)
    {
        $request->validate([
            'search' => 'string|nullable|max:255',
        ]);
        $member = Member::with('memberKind', 'channel', 'diagnosis', 'diagnosis.doctor', 'medicalPlans')
            ->when($request->input('search'), function (Builder $query, $value) {
                $query->whereRaw('CONCAT(name,mobile) LIKE ?', "%{$value}%");
            })->whereHas('diagnosis', function (Builder $query) {
                $query->where('doctor_id', $this->user()->role_doctor_id);
            })->paginate();
        $member->map(function ($v) {
            $v->last_diagnosis     = $v->diagnosis->last();
            $v->last_medical_plans = $v->medicalPlans->last();
            unset($v->diagnosis, $v->medicalPlans);
            return $v;
        });
        return $this->respondWithData($member);
    }

    public function diagnosisTotalList(Request $request)
    {
        $request->validate([
            'search' => 'string|nullable|max:255',
        ]);
        $member = Member::with('memberKind', 'channel', 'diagnosis', 'diagnosis.doctor', 'medicalPlans')
            ->when($request->input('search'), function (Builder $query, $value) {
                $query->whereRaw('CONCAT(name,mobile) LIKE ?', "%{$value}%");
            })->paginate();
        $member->map(function ($v) {
            $v->last_diagnosis     = $v->diagnosis->last();
            $v->last_medical_plans = $v->medicalPlans->last();
            unset($v->diagnosis, $v->medicalPlans);
            return $v;
        });
        return $this->respondWithData($member);
    }

    public function diagnosisRecord(Request $request)
    {
        $request->validate([
            'member_id' => 'required|integer|exists:members,id',
        ]);
        $diagnosis = Diagnosis::with('member', 'member.memberKind', 'member.medicalPlans', 'doctor')
            ->where('member_id', $request->input('member_id'))
            ->orderByDesc('id')->get();
        return $this->respondWithData($diagnosis);
    }
}
