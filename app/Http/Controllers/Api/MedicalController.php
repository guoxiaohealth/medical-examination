<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Model\ConfigKind;
use App\Model\ConfigMerit;
use App\Model\ConfigProject;
use App\Model\ConfigSubject;
use App\Model\Mechanism;
use App\Model\MedicalPlan;
use App\Model\MedicalPlanOperate;
use App\Model\Member;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MedicalController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function mechanismList(Request $request)
    {
        return $this->respondWithData(
            Mechanism::get()
        );
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function mechanismCreate(Request $request)
    {
        $request->validate([
            'name'      => 'required|max:255',
            'remarks'   => 'string|nullable|max:255',
            'print_msg' => 'string|nullable|max:255',
        ]);
        Mechanism::create([
            'name'      => $request->input('name'),
            'remarks'   => strval($request->input('remarks')),
            'print_msg' => strval($request->input('print_msg')),
        ]);
        return $this->respondWithSuccess();
    }

    /**
     * @param Request $request
     * @param Mechanism $mechanism
     * @return \Illuminate\Http\JsonResponse
     */
    public function mechanismUpdate(Request $request, Mechanism $mechanism)
    {
        $request->validate([
            'name'      => 'string|nullable|max:255',
            'remarks'   => 'string|nullable|max:255',
            'print_msg' => 'string|nullable|max:255',
        ]);
        $data = $request->only(['name', 'remarks', 'print_msg',]);
        $mechanism->update(array_filter($data));
        return $this->respondWithSuccess();
    }

    /**
     * @param Request $request
     * @param Mechanism $mechanism
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function mechanismDelete(Request $request, Mechanism $mechanism)
    {
        $mechanism->delete();
        return $this->respondWithSuccess();
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function configList(Request $request)
    {
        $request->validate([
            'mechanism_id' => 'required|integer',
        ]);
        return $this->respondWithData(
            ConfigKind::with('projects', 'projects.subjects', 'projects.subjects.merits')
                ->where('mechanism_id', $request->input('mechanism_id'))->get()
        );
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function configCopy(Request $request)
    {
        $request->validate([
            'from_mechanism_id' => 'required|integer|exists:mechanisms,id',
            'to_mechanism_id'   => 'required|integer|exists:mechanisms,id',
        ]);
        $mechanismA = $request->input('from_mechanism_id');
        $mechanismB = $request->input('to_mechanism_id');
        if ($mechanismA == $mechanismB) {
            throw new \Exception('mechanism_id invalid');
        }
        DB::transaction(function () use ($request, $mechanismA, $mechanismB) {
            ConfigKind::where('mechanism_id', $mechanismA)->get()->map(function ($v) use ($request, $mechanismB) {
                ConfigKind::where('mechanism_id', $mechanismB)->delete();
                $configKind = ConfigKind::create([
                    'mechanism_id' => $mechanismB,
                    'name'         => $v->name,
                ]);
                $v->projects->map(function ($v) use ($configKind, $mechanismB) {
                    ConfigProject::where('mechanism_id', $mechanismB)->delete();
                    $configProject = ConfigProject::create([
                        'mechanism_id'   => $mechanismB,
                        'config_kind_id' => $configKind->id,
                        'name'           => $v->name,
                    ]);
                    $v->subjects->map(function ($v) use ($configProject, $mechanismB) {
                        ConfigSubject::where('mechanism_id', $mechanismB)->delete();
                        $configSubject = ConfigSubject::create([
                            'mechanism_id'      => $mechanismB,
                            'config_project_id' => $configProject->id,
                            'name'              => $v->name,
                        ]);
                        $v->merits->map(function ($v) use ($configSubject, $mechanismB) {
                            ConfigMerit::where('mechanism_id', $mechanismB)->delete();
                            ConfigMerit::create([
                                'mechanism_id'      => $mechanismB,
                                'config_subject_id' => $configSubject->id,
                                'name'              => $v->name,
                                'unit'              => $v->unit,
                                'range'             => $v->range,
                                'type'              => $v->type,
                                'expression'        => $v->expression,
                            ]);
                        });
                    });
                });
            });
        });
        return $this->respondWithSuccess();
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function configKindList(Request $request)
    {
        $request->validate([
            'mechanism_id' => 'required|integer',
        ]);
        return $this->respondWithData(
            ConfigKind::where('mechanism_id', $request->input('mechanism_id'))->get()
        );
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function configKindCreate(Request $request)
    {
        $request->validate([
            'mechanism_id' => 'required|integer|exists:mechanisms,id',
            'name'         => 'required|max:255',
        ]);
        ConfigKind::create([
            'mechanism_id' => $request->input('mechanism_id'),
            'name'         => $request->input('name'),
        ]);
        return $this->respondWithSuccess();
    }

    /**
     * @param Request $request
     * @param ConfigKind $configKind
     * @return \Illuminate\Http\JsonResponse
     */
    public function configKindUpdate(Request $request, ConfigKind $configKind)
    {
        $request->validate([
            'name' => 'string|nullable|max:255',
        ]);
        $data = $request->only(['name',]);
        $configKind->update(array_filter($data));
        return $this->respondWithSuccess();
    }

    /**
     * @param Request $request
     * @param ConfigKind $configKind
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function configKindDelete(Request $request, ConfigKind $configKind)
    {
        $configKind->delete();
        return $this->respondWithSuccess();
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function configProjectList(Request $request)
    {
        $request->validate([
            'mechanism_id' => 'required|integer',
        ]);
        return $this->respondWithData(
            ConfigProject::where('mechanism_id', $request->input('mechanism_id'))->get()
        );
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function configProjectCreate(Request $request)
    {
        $request->validate([
            'mechanism_id'   => 'required|integer|exists:mechanisms,id',
            'config_kind_id' => 'required|integer|exists:config_kinds,id',
            'name'           => 'required|max:255',
        ]);
        ConfigProject::create([
            'mechanism_id'   => $request->input('mechanism_id'),
            'config_kind_id' => $request->input('config_kind_id'),
            'name'           => $request->input('name'),
        ]);
        return $this->respondWithSuccess();
    }

    /**
     * @param Request $request
     * @param ConfigProject $configProject
     * @return \Illuminate\Http\JsonResponse
     */
    public function configProjectUpdate(Request $request, ConfigProject $configProject)
    {
        $request->validate([
            'name' => 'string|nullable|max:255',
        ]);
        $data = $request->only(['name',]);
        $configProject->update(array_filter($data));
        return $this->respondWithSuccess();
    }

    /**
     * @param Request $request
     * @param ConfigProject $configProject
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function configProjectDelete(Request $request, ConfigProject $configProject)
    {
        $configProject->delete();
        return $this->respondWithSuccess();
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function configSubjectList(Request $request)
    {
        $request->validate([
            'mechanism_id' => 'required|integer',
        ]);
        return $this->respondWithData(
            ConfigSubject::where('mechanism_id', $request->input('mechanism_id'))->get()
        );
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function configSubjectCreate(Request $request)
    {
        $request->validate([
            'mechanism_id'      => 'required|integer|exists:mechanisms,id',
            'config_project_id' => 'required|integer|exists:config_projects,id',
            'name'              => 'required|max:255',
        ]);
        ConfigSubject::create([
            'mechanism_id'      => $request->input('mechanism_id'),
            'config_project_id' => $request->input('config_project_id'),
            'name'              => $request->input('name'),
        ]);
        return $this->respondWithSuccess();
    }

    /**
     * @param Request $request
     * @param ConfigSubject $configSubject
     * @return \Illuminate\Http\JsonResponse
     */
    public function configSubjectUpdate(Request $request, ConfigSubject $configSubject)
    {
        $request->validate([
            'name' => 'string|nullable|max:255',
        ]);
        $data = $request->only(['name',]);
        $configSubject->update(array_filter($data));
        return $this->respondWithSuccess();
    }

    /**
     * @param Request $request
     * @param ConfigSubject $configSubject
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function configSubjectDelete(Request $request, ConfigSubject $configSubject)
    {
        $configSubject->delete();
        return $this->respondWithSuccess();
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function configMeritList(Request $request)
    {
        $request->validate([
            'mechanism_id' => 'required|integer',
        ]);
        return $this->respondWithData(
            ConfigMerit::where('mechanism_id', $request->input('mechanism_id'))->get()
        );
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function configMeritCreate(Request $request)
    {
        $request->validate([
            'mechanism_id'             => 'required|integer|exists:mechanisms,id',
            'config_subject_id'        => 'required|integer|exists:config_subjects,id',
            'name'                     => 'required|max:255',
            'unit'                     => 'required|max:255',
            'range'                    => 'required|max:255',
            'type'                     => 'required|in:1,2,3,4',
            'expression'               => 'array',
            'expression.mean'          => 'required_with:expression|string',
            'expression.ex'            => 'required_with:expression|array',
            'expression.ex.*.symbol'   => 'required_with:expression|in:eq,gt,gte,lt,lte,range,rangeout,match',
            'expression.ex.*.values'   => 'required_with:expression|string',
            'expression.ex.*.alert'    => 'required_with:expression|string',
            'expression.ex.*.solution' => 'required_with:expression|string',
        ]);
        ConfigMerit::create([
            'mechanism_id'      => $request->input('mechanism_id'),
            'config_subject_id' => $request->input('config_subject_id'),
            'name'              => $request->input('name'),
            'unit'              => $request->input('unit'),
            'range'             => $request->input('range'),
            'type'              => $request->input('type'),
            'expression'        => $request->input('expression'),
        ]);
        return $this->respondWithSuccess();
    }

    /**
     * @param Request $request
     * @param ConfigMerit $configMerit
     * @return \Illuminate\Http\JsonResponse
     */
    public function configMeritUpdate(Request $request, ConfigMerit $configMerit)
    {
        $request->validate([
            'config_subject_id'        => 'integer',
            'name'                     => 'string|nullable|max:255',
            'unit'                     => 'string|nullable|max:255',
            'range'                    => 'string|nullable|max:255',
            'type'                     => 'in:1,2,3,4',
            'expression'               => 'array',
            'expression.mean'          => 'required_with:expression|string',
            'expression.ex'            => 'required_with:expression|array',
            'expression.ex.*.symbol'   => 'required_with:expression|in:eq,gt,gte,lt,lte,range,rangeout,match',
            'expression.ex.*.values'   => 'required_with:expression|string',
            'expression.ex.*.alert'    => 'required_with:expression|string',
            'expression.ex.*.solution' => 'required_with:expression|string',
        ]);
        $data = $request->only(['config_subject_id', 'name', 'unit', 'range', 'type', 'expression']);
        $configMerit->update(array_filter($data));
        return $this->respondWithSuccess();
    }

    /**
     * @param Request $request
     * @param ConfigMerit $configMerit
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function configMeritDelete(Request $request, ConfigMerit $configMerit)
    {
        $configMerit->delete();
        return $this->respondWithSuccess();
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function medicalPlanList(Request $request)
    {
        $request->validate([
            'search' => 'string|nullable|max:255',
        ]);
        $plans = Member::with('memberKind', 'channel', 'medicalPlans')
            ->when($request->input('search'), function (Builder $query, $value) {
                $query->whereRaw('CONCAT(name,mobile) LIKE ?', "%{$value}%");
            })->paginate();
        $plans->map(function ($v) {
            $medicalPlans = collect($v->medicalPlans);
            // 体检次数
            $v->medical_plans_count = $medicalPlans->count();
            // 报告份数
            $v->medical_plans_merits_count = $medicalPlans->pluck('kinds.*.projects.*.subjects.*.merits')
                ->flatten(1)->count();
            // 异常数
            $v->medical_plans_merits_abnormal = $this->medicalPlanMerits(
                $medicalPlans->pluck('kinds.*.projects.*.subjects.*.merits')->flatten(2)->toArray()
            )->pluck('ex.*.status')->flatten(1)->filter(function ($v) {
                return $v === false;
            })->count();
            unset($v->medicalPlans);
            return $v;
        });
        return $this->respondWithData($plans);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function medicalPlanCheck(Request $request)
    {
        $request->validate([
            'member_id' => 'required|integer|exists:members,id',
            'times'     => 'integer',
        ]);
        // 最新体检
        $plan = MedicalPlan::with('doctor')
            ->where('member_id', $request->input('member_id'))
            ->when($request->input('times'), function (Builder $query, $value) {
                $query->where('times', $value);
            })->orderByDesc('id')->firstOrFail();
        //
        $plan->kinds = $this->medicalPlanKinds($plan->kinds);
        return $this->respondWithData($plan);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function medicalPlanCreate(Request $request)
    {
        $request->validate([
            'member_id'                                    => 'required|integer|exists:members,id',
            'kinds'                                        => 'array',
            'kinds.*.id'                                   => 'required_with:kinds|integer|exists:config_kinds,id',
            'kinds.*.projects'                             => 'required_with:kinds|array',
            'kinds.*.projects.*.id'                        => 'required_with:kinds|integer|exists:config_projects,id',
            'kinds.*.projects.*.subjects'                  => 'required_with:kinds|array',
            'kinds.*.projects.*.subjects.*.id'             => 'required_with:kinds|integer|exists:config_subjects,id',
            'kinds.*.projects.*.subjects.*.original'       => 'string|nullable|max:255',
            'kinds.*.projects.*.subjects.*.date'           => 'date_format:Y-m-d',
            'kinds.*.projects.*.subjects.*.merits'         => 'array',
            'kinds.*.projects.*.subjects.*.merits.*.id'    => 'integer|exists:config_merits,id',
            'kinds.*.projects.*.subjects.*.merits.*.value' => 'string|nullable|max:255',
        ]);
        DB::transaction(function () use ($request) {
            $medicalPlan = MedicalPlan::create([
                'member_id' => $request->input('member_id'),
                'doctor_id' => $this->user()->role_doctor_id,
                'kinds'     => $this->makeMedicalPlanKinds($request->input('kinds')),
                'times'     => MedicalPlan::where('member_id', $request->input('member_id'))->count() + 1,
            ]);
            MedicalPlanOperate::create([
                'role_doctor_id'  => $this->user()->role_doctor_id,
                'medical_plan_id' => $medicalPlan->id,
                'operate'         => 1
            ]);
        });
        return $this->respondWithSuccess();
    }

    /**
     * @param Request $request
     * @param MedicalPlan $medicalPlan
     * @return \Illuminate\Http\JsonResponse
     */
    public function medicalPlanUpdate(Request $request, MedicalPlan $medicalPlan)
    {
        $request->validate([
            'kinds'                                        => 'required|array',
            'kinds.*.id'                                   => 'required|integer|exists:config_kinds,id',
            'kinds.*.projects'                             => 'required|array',
            'kinds.*.projects.*.id'                        => 'required|integer|exists:config_projects,id',
            'kinds.*.projects.*.subjects'                  => 'required|array',
            'kinds.*.projects.*.subjects.*.id'             => 'required|integer|exists:config_subjects,id',
            'kinds.*.projects.*.subjects.*.original'       => 'string|nullable|max:255',
            'kinds.*.projects.*.subjects.*.date'           => 'date_format:Y-m-d',
            'kinds.*.projects.*.subjects.*.merits'         => 'array',
            'kinds.*.projects.*.subjects.*.merits.*.id'    => 'integer|exists:config_merits,id',
            'kinds.*.projects.*.subjects.*.merits.*.value' => 'string',
        ]);
        DB::transaction(function () use ($request, $medicalPlan) {
            $medicalPlan->update([
                'kinds' => $this->makeMedicalPlanKinds($request->input('kinds')),
            ]);
            MedicalPlanOperate::create([
                'role_doctor_id'  => $this->user()->role_doctor_id,
                'medical_plan_id' => $medicalPlan->id,
                'operate'         => 2
            ]);
        });
        return $this->respondWithSuccess();
    }

    /**
     * @param Request $request
     * @param MedicalPlan $medicalPlan
     * @return \Illuminate\Http\JsonResponse
     */
    public function medicalPlanDelete(Request $request, MedicalPlan $medicalPlan)
    {
        DB::transaction(function () use ($request, $medicalPlan) {
            $medicalPlan->delete();
            MedicalPlanOperate::create([
                'role_doctor_id'  => $this->user()->role_doctor_id,
                'medical_plan_id' => $medicalPlan->id,
                'operate'         => 3
            ]);
        });
        return $this->respondWithSuccess();
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function medicalPlanHistory(Request $request)
    {
        $request->validate([
            'medical_plan_id' => 'integer',
        ]);
        return $this->respondWithData(
            MedicalPlanOperate::with('roleDoctor', 'medicalPlan', 'medicalPlan.member')
                ->when($request->input('medical_plan_id'), function (Builder $query, $value) {
                    $query->where('medical_plan_id', $value);
                })->get()
        );
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function optionList(Request $request)
    {
        $request->validate([
            'search' => 'string|nullable|max:255',
        ]);
        $plans = Member::with('memberKind', 'channel', 'medicalPlans', 'medicalPlans.doctor')
            ->when($request->input('search'), function (Builder $query, $value) {
                $query->whereRaw('CONCAT(name,mobile) LIKE ?', "%{$value}%");
            })->paginate();
        $plans->map(function ($v) {
            $medicalPlans = collect($v->medicalPlans);
            // 体检次数
            $v->medical_plans_count = $medicalPlans->count();
            // 最后体检备注
            $v->medical_plans_latest_doctor = optional($medicalPlans->last())->doctor;
            $v->medical_plans_latest_date   = optional($medicalPlans->last())->updated_at;
            unset($v->medicalPlans);
            return $v;
        });
        return $this->respondWithData($plans);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function memberOptionList(Request $request)
    {
        $request->validate([
            'member_id' => 'required|integer|exists:members,id',
        ]);
        return $this->respondWithData(
            MedicalPlan::with('doctor')->where('member_id', $request->input('member_id'))
                ->get()->map(function ($v) {
                    $kinds                     = collect($v->kinds);
                    $v->medical_plans_kinds    = $kinds->count();
                    $v->medical_plans_projects = $kinds->pluck('projects')->flatten(1)->count();
                    $v->medical_plans_subjects = $kinds->pluck('projects.*.subjects')->flatten(2)->count();
                    $v->medical_plans          = $this->medicalPlanKinds($v->kinds);
                    $m                         = $v->medical_plans->random();
                    if (empty($m)) {
                        $v->mechanism = null;
                    } else {
                        $v->mechanism = Mechanism::query()->find($m->mechanism_id);
                    }
                    unset($v->kinds);
                    return $v;
                })
        );
    }
}
