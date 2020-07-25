<?php

namespace App\Http\Controllers;

use App\Model\ConfigKind;
use App\Model\ConfigMerit;
use App\Model\ConfigProject;
use App\Model\ConfigSubject;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Str;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var mixed
     */
    protected $user;

    /**
     * @return \Illuminate\Support\Optional|mixed
     */
    protected function user()
    {
        if (!$this->user) {
            $this->user = optional(auth()->user());
        }
        return $this->user;
    }

    /**
     * @param $v
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithData($v)
    {
        return response()->json($v);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithSuccess()
    {
        return response()->json('success');
    }

    /**
     * @param $error
     * @param int $status
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithError($error, $status = 400)
    {
        return response()->json(['error' => $error], $status);
    }

    protected function meritsAbnormal($merit)
    {
        $merit['ex'] = collect($merit['ex'])->map(function ($v) use ($merit) {
            if (!$merit['value']) {
                $v['status'] = null;
                return $v;
            }
            switch ($v['symbol']) {
                case 'eq':
                    $v['status'] = $merit['value'] == $v['values'];
                    break;
                case 'gt':
                    $v['status'] = floatval($merit['value']) > floatval($v['values']);
                    break;
                case 'gte':
                    $v['status'] = floatval($merit['value']) >= floatval($v['values']);
                    break;
                case 'lt':
                    $v['status'] = floatval($merit['value']) < floatval($v['values']);
                    break;
                case 'lte':
                    $v['status'] = floatval($merit['value']) <= floatval($v['values']);
                    break;
                case 'range':
                    $cmp = explode(',', strval($v['values']));
                    if (count($cmp) != 2) {
                        $v['status'] = null;
                        break;
                    }
                    $v['status'] = floatval($cmp[0]) <= floatval($merit['value']) && floatval($merit['value']) <= floatval($cmp[1]);
                    break;
                case 'rangeout':
                    $cmp = explode(',', strval($v['values']));
                    if (count($cmp) != 2) {
                        $v['status'] = null;
                        break;
                    }
                    $v['status'] = floatval($cmp[0]) > floatval($merit['value']) && floatval($merit['value']) < floatval($cmp[1]);
                    break;
                case 'match':
                    $v['status'] = Str::contains(strval($v['values']), strval($merit['value']));
                    break;
                default:
                    $v['status'] = null;
                    break;
            }
            return $v;
        });
        return $merit;
    }

    protected function medicalPlanMerits(array $merits)
    {
        return collect($merits)->map(function ($v) {
            $merit          = ConfigMerit::where('id', $v['id'])->value('expression');
            $merit['value'] = $v['value'];
            return $this->meritsAbnormal($merit);
        });
    }

    protected function medicalPlanKinds(array $kinds)
    {
        return collect($kinds)->map(function ($v) {
            $kind           = ConfigKind::find($v['id']) ?? new \stdClass();
            $kind->id       = $v['id'];
            $kind->projects = collect($v['projects'])->map(function ($v) {
                $project           = ConfigProject::find($v['id']) ?? new \stdClass();
                $project->id       = $v['id'];
                $project->subjects = collect($v['subjects'])->map(function ($v) {
                    $subject           = ConfigSubject::find($v['id']) ?? new \stdClass();
                    $subject->id       = $v['id'];
                    $subject->original = $v['original'];
                    $subject->date     = $v['date'];
                    $subject->merits   = collect($v['merits'])->map(function ($v) {
                        $merit        = ConfigMerit::find($v['id']) ?? new \stdClass();
                        $merit->id    = $v['id'];
                        $merit->value = $v['value'];
                        return $this->meritsAbnormal($merit);
                    });
                    return $subject;
                });
                return $project;
            });
            return $kind;
        });
    }

    public function makeMedicalPlanKinds(array $kinds)
    {
        return collect($kinds)->map(function ($kind) {
            return [
                'id'       => $kind['id'],
                'projects' => collect($kind['projects'])->map(function ($project) use ($kind) {
                    ConfigProject::where([
                        'id'             => $project['id'],
                        'config_kind_id' => $kind['id'],
                    ])->firstOrFail();
                    return [
                        'id'       => $project['id'],
                        'subjects' => collect($project['subjects'])->map(function ($subject) use ($project) {
                            ConfigSubject::where([
                                'id'                => $subject['id'],
                                'config_project_id' => $project['id'],
                            ])->firstOrFail();
                            return [
                                'id'       => $subject['id'],
                                'original' => optional($subject)['original'],
                                'date'     => optional($subject)['date'],
                                'merits'   => collect($subject['merits'])->map(function ($merit) use ($subject) {
                                    ConfigMerit::where([
                                        'id'                => $merit['id'],
                                        'config_subject_id' => $subject['id'],
                                    ])->firstOrFail();
                                    return [
                                        'id'    => $merit['id'],
                                        'value' => optional($merit)['value'],
                                    ];
                                })
                            ];
                        })
                    ];
                }),
            ];
        });
    }
}
