<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Model\ConfigKind;
use App\Model\ConfigMerit;
use App\Model\ConfigProject;
use App\Model\ConfigSubject;
use App\Model\Mechanism;
use App\Model\MedicalPlan;
use App\Model\Member;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MedicalController extends Controller
{
    public function mechanismList(Request $request)
    {
        return $this->respondWithData(
            Mechanism::get()
        );
    }

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

    public function mechanismDelete(Request $request, Mechanism $mechanism)
    {
        $mechanism->delete();
        return $this->respondWithSuccess();
    }


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
                $configKind = ConfigKind::create([
                    'mechanism_id' => $mechanismB,
                    'name'         => $v->name,
                ]);
                $v->projects->map(function ($v) use ($configKind, $mechanismB) {
                    $configProject = ConfigProject::create([
                        'mechanism_id'   => $mechanismB,
                        'config_kind_id' => $configKind->id,
                        'name'           => $v->name,
                    ]);
                    $v->subjects->map(function ($v) use ($configProject, $mechanismB) {
                        $configSubject = ConfigSubject::create([
                            'mechanism_id'      => $mechanismB,
                            'config_project_id' => $configProject->id,
                            'name'              => $v->name,
                        ]);
                        $v->merits->map(function ($v) use ($configSubject, $mechanismB) {
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


    public function configKindList(Request $request)
    {
        return $this->respondWithData(
            ConfigKind::get()
        );
    }

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

    public function configKindUpdate(Request $request, ConfigKind $configKind)
    {
        $request->validate([
            'name' => 'string|nullable|max:255',
        ]);
        $data = $request->only(['name',]);
        $configKind->update(array_filter($data));
        return $this->respondWithSuccess();
    }

    public function configKindDelete(Request $request, ConfigKind $configKind)
    {
        $configKind->delete();
        return $this->respondWithSuccess();
    }


    public function configProjectList(Request $request)
    {
        return $this->respondWithData(
            ConfigProject::get()
        );
    }

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

    public function configProjectUpdate(Request $request, ConfigProject $configProject)
    {
        $request->validate([
            'name' => 'string|nullable|max:255',
        ]);
        $data = $request->only(['name',]);
        $configProject->update(array_filter($data));
        return $this->respondWithSuccess();
    }

    public function configProjectDelete(Request $request, ConfigProject $configProject)
    {
        $configProject->delete();
        return $this->respondWithSuccess();
    }


    public function configSubjectList(Request $request)
    {
        return $this->respondWithData(
            ConfigSubject::get()
        );
    }

    public function configSubjectCreate(Request $request)
    {
        $request->validate([
            'mechanism_id'      => 'required|integer|exists:mechanisms,id',
            'config_project_id' => 'required|integer|exists:config_projects,id',
            'name'              => 'required|max:255',
        ]);
        ConfigProject::create([
            'mechanism_id'      => $request->input('mechanism_id'),
            'config_project_id' => $request->input('config_project_id'),
            'name'              => $request->input('name'),
        ]);
        return $this->respondWithSuccess();
    }

    public function configSubjectUpdate(Request $request, ConfigSubject $configSubject)
    {
        $request->validate([
            'name' => 'string|nullable|max:255',
        ]);
        $data = $request->only(['name',]);
        $configSubject->update(array_filter($data));
        return $this->respondWithSuccess();
    }

    public function configSubjectDelete(Request $request, ConfigSubject $configSubject)
    {
        $configSubject->delete();
        return $this->respondWithSuccess();
    }


    public function configMeritList(Request $request)
    {
        return $this->respondWithData(
            ConfigMerit::get()
        );
    }

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
            'expression.ex.*.symbol'   => 'required_with:expression|in:eq,gt,gte,lt,lte,range,match',
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
            'expression.ex.*.symbol'   => 'required_with:expression|in:eq,gt,gte,lt,lte,range,match',
            'expression.ex.*.values'   => 'required_with:expression|string',
            'expression.ex.*.alert'    => 'required_with:expression|string',
            'expression.ex.*.solution' => 'required_with:expression|string',
        ]);
        $data = $request->only(['config_subject_id', 'name', 'unit', 'type', 'expression']);
        $configMerit->update(array_filter($data));
        return $this->respondWithSuccess();
    }

    public function configMeritDelete(Request $request, ConfigMerit $configMerit)
    {
        $configMerit->delete();
        return $this->respondWithSuccess();
    }


    public function medicalPlanList(Request $request)
    {
        $request->validate([
            'search' => 'string|nullable|max:255',
        ]);
        return $this->respondWithData(
            Member::with('memberKind', 'channel', 'medicalPlans')
                ->when($request->input('search'), function (Builder $query, $value) {
                    $query->whereRaw('CONCAT(name,mobile) LIKE ?', "%{$value}%");
                })->get()->map(function ($v) {
                    $merits                           = collect($v->medicalPlans)
                        ->pluck('kinds.*.projects.*.subjects.*.merits')->flatten(2);
                    $v->medical_plans_count           = count($v->medicalPlans);
                    $v->medical_plans_merits_count    = $merits->count();
                    $v->medical_plans_merits_abnormal = $this->medicalPlanMerits($merits->toArray())
                        ->pluck('ex.*.status')->flatten(1)->filter(function ($v) {
                            return $v === false;
                        })->count();
                    unset($v->medicalPlans);
                    return $v;
                })
        );
    }

    public function medicalPlanCheck(Request $request)
    {
        $request->validate([
            'member_id' => 'required|integer|exists:members,id',
        ]);

        $plan = MedicalPlan::where('member_id', $request->input('member_id'))
            ->orderByDesc('id')->firstOrFail();

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
            'kinds.*.projects.*.subjects.*.date'           => 'required|date_format:Y-m-d',
            'kinds.*.projects.*.subjects.*.merits'         => 'required|array',
            'kinds.*.projects.*.subjects.*.merits.*.id'    => 'required|integer|exists:config_merits,id',
            'kinds.*.projects.*.subjects.*.merits.*.value' => 'required|string',
        ]);
        $medicalPlan->update([
            'kinds' => $this->makeMedicalPlanKinds($request->input('kinds')),
        ]);
        return $this->respondWithSuccess();
    }

    public function medicalPlanDelete(Request $request, MedicalPlan $medicalPlan)
    {
        $medicalPlan->delete();
        return $this->respondWithSuccess();
    }
}
